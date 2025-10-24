<?php
session_start();

// Segurança: headers HTTP para reduzir XSS/Clickjacking/MIME sniffing (aplicar antes de qualquer saída)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https:; connect-src 'self' https://api.openai.com; frame-ancestors 'self';");
header('Permissions-Policy: geolocation=(), microphone=()');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../controllers/AlertaController.php';
require_once '../../controllers/PrevisaoController.php';

$alertaController = new AlertaController();
$previsaoController = new PrevisaoController();

$alertas = $alertaController->listarAlertasPendentes();
try {
    $previsoes = $previsaoController->listarPrevisoes();
} catch (Exception $e) {
    $previsoes = [];
}

// Conexão direta para consultas de métricas (ajuste credenciais se necessário)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'gestao_ativos';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    // manter a página, mas endpoints falharão com mensagem simples
}

// Endpoint JSON para métricas (adicionado cache simples)
if (isset($_GET['fetch']) && $_GET['fetch'] === 'metrics') {
    $from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : null;
    $to = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : null;
    if (!$from || !$to) {
        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime('-11 months', strtotime($to)));
    }

    // cache file por range (vida 15s)
    $cacheDir = __DIR__ . '/../../storage/cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $cacheFile = $cacheDir . '/metrics_' . md5($from . '|' . $to) . '.json';
    $cacheTtl = 15; // segundos

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
        header('Content-Type: application/json; charset=utf-8');
        echo file_get_contents($cacheFile);
        exit;
    }

    // ativos por categoria
    $cats = [];
    $res = $mysqli->query("SELECT categoria, COUNT(*) AS total FROM ativos GROUP BY categoria");
    if ($res) { while ($r = $res->fetch_assoc()) $cats[] = $r; $res->free(); }

    // ativos por status
    $status = [];
    $res = $mysqli->query("SELECT status, COUNT(*) AS total FROM ativos GROUP BY status");
    if ($res) { while ($r = $res->fetch_assoc()) $status[] = $r; $res->free(); }

    // custos totais e média por manut no período
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(custo),0) AS total_custo, COALESCE(AVG(custo),0) AS avg_custo, COUNT(*) AS total_manut FROM manutencoes WHERE data_manutencao BETWEEN ? AND ?");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $res = $stmt->get_result();
    $custo_info = $res->fetch_assoc() ?: ['total_custo'=>0,'avg_custo'=>0,'total_manut'=>0];
    $stmt->close();

    // custo por mês (formato YYYY-MM)
    $months = [];
    $stmt = $mysqli->prepare("SELECT DATE_FORMAT(data_manutencao,'%Y-%m') AS ym, COALESCE(SUM(custo),0) AS total FROM manutencoes WHERE data_manutencao BETWEEN ? AND ? GROUP BY ym ORDER BY ym");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $months[] = $r;
    $stmt->close();

    // manutenções por tipo no período
    $types = [];
    $stmt = $mysqli->prepare("SELECT tipo, COUNT(*) AS total FROM manutencoes WHERE data_manutencao BETWEEN ? AND ? GROUP BY tipo");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $types[] = $r;
    $stmt->close();

    $payload = json_encode([
        'from' => $from,
        'to' => $to,
        'ativos_por_categoria' => $cats,
        'ativos_por_status' => $status,
        'custo_info' => $custo_info,
        'custo_por_mes' => $months,
        'manutencoes_por_tipo' => $types
    ]);

    // grava cache (não bloquear se falhar)
    @file_put_contents($cacheFile, $payload);

    header('Content-Type: application/json; charset=utf-8');
    echo $payload;
    exit;
}

// Export CSV simples com as métricas
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : date('Y-m-d', strtotime('-11 months'));
    $to = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : date('Y-m-d');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_indicadores_'.$from.'_a_'.$to.'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['Indicador','Valor']);

    // total ativos
    $res = $mysqli->query("SELECT COUNT(*) AS total FROM ativos");
    $tot = $res ? $res->fetch_assoc()['total'] : 0;
    fputcsv($out, ['Total de ativos', $tot]);

    // ativos por categoria
    fputcsv($out, []);
    fputcsv($out, ['Ativos por categoria','']);
    $res = $mysqli->query("SELECT categoria, COUNT(*) AS total FROM ativos GROUP BY categoria");
    if ($res) { while ($r = $res->fetch_assoc()) fputcsv($out, [$r['categoria'], $r['total']]); }

    // custos no periodo
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(custo),0) AS total_custo, COALESCE(AVG(custo),0) AS avg_custo, COUNT(*) AS total_manut FROM manutencoes WHERE data_manutencao BETWEEN ? AND ?");
    $stmt->bind_param("ss",$from,$to); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    fputcsv($out, []);
    fputcsv($out, ['Custo total no periodo', $r['total_custo']]);
    fputcsv($out, ['Custo médio por manutenção', $r['avg_custo']]);
    fputcsv($out, ['Total manutenções no periodo', $r['total_manut']]);

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #1abc9c;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        h2, h5, h6 {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .page-title {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .metric-card {
            text-align: center;
            padding: 15px;
        }
        
        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="page-title">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Painel Gerencial</h2>
                <div>
                    <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimir / PDF
                    </button>
                    <button id="exportCsv" class="btn btn-success">
                        <i class="fas fa-file-export me-1"></i>Exportar CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtros de período -->
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Filtrar por Período</h6>
                <form id="filtrosForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="from" class="form-label">Data Inicial</label>
                        <input type="date" id="from" name="from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="to" class="form-label">Data Final</label>
                        <input type="date" id="to" name="to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" onclick="atualizarIndicadores()">
                            <i class="fas fa-filter me-1"></i>Aplicar Filtro
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Indicadores Rápidos -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Indicadores Rápidos</h5>
                    </div>
                    <div class="card-body">
                        <div id="indicadoresRapidos" class="row text-center">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Ativos por Categoria</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCategoria" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Ativos por Status</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartStatus" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Custo -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Custo de Manutenções (por mês)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCustoMes" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previsões de Manutenção -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Previsão de Manutenção (IA)</h5>
                <div>
                    <button id="btnGerarPrevisoes" class="btn btn-primary btn-sm me-2" onclick="gerarPrevisoes()">
                        <i class="fas fa-sync-alt me-1"></i>Gerar Previsões
                    </button>
                    <a href="../previsao/index.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Abrir Módulo IA
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div id="previsaoFeedback"></div>
                <?php if (empty($previsoes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <p class="text-muted">Sistema de previsões em manutenção. Tente novamente mais tarde.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ativo</th>
                                    <th>Probabilidade</th>
                                    <th>Previsão</th>
                                    <th>Razões</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($previsoes as $previsao): ?>
                                    <tr class="<?= ($previsao['probabilidade_falha'] > 80) ? 'table-danger' : 'table-warning' ?>">
                                        <td><?= htmlspecialchars($previsao['nome_ativo']) ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?= ($previsao['probabilidade_falha'] > 80) ? 'bg-danger' : 'bg-warning' ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= $previsao['probabilidade_falha'] ?>%;"
                                                     aria-valuenow="<?= $previsao['probabilidade_falha'] ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?= number_format($previsao['probabilidade_falha'],1) ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($previsao['data_previsao'])) ?></td>
                                        <td style="max-width:400px; white-space:pre-wrap;"><?= htmlspecialchars($previsao['razoes']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    let chartCategoria, chartStatus, chartCustoMes;

    function atualizarIndicadores() {
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        const url = `index.php?fetch=metrics&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        
        fetch(url)
            .then(r => r.json())
            .then(data => {
                renderCategoria(data.ativos_por_categoria || []);
                renderStatus(data.ativos_por_status || []);
                renderCustoMes(data.custo_por_mes || []);
                renderIndicadoresRapidos(data.custo_info || {});
                
                // ajustar botão export
                const params = `from=${encodeURIComponent(data.from)}&to=${encodeURIComponent(data.to)}`;
                document.getElementById('exportCsv').onclick = () => { window.location = `index.php?export=csv&${params}`; };
            })
            .catch(err => console.error(err));
    }

    function renderCategoria(items) {
        const labels = items.map(i => i.categoria);
        const values = items.map(i => parseInt(i.total));
        const ctx = document.getElementById('chartCategoria').getContext('2d');
        
        if (chartCategoria) chartCategoria.destroy();
        
        chartCategoria = new Chart(ctx, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: generateColors(values.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }

    function renderStatus(items) {
        const labels = items.map(i => i.status);
        const values = items.map(i => parseInt(i.total));
        const ctx = document.getElementById('chartStatus').getContext('2d');
        
        if (chartStatus) chartStatus.destroy();
        
        chartStatus = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: generateColors(values.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }

    function renderCustoMes(items) {
        const labels = items.map(i => i.ym);
        const values = items.map(i => parseFloat(i.total));
        const ctx = document.getElementById('chartCustoMes').getContext('2d');
        
        if (chartCustoMes) chartCustoMes.destroy();
        
        chartCustoMes = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Custo (R$)',
                    data: values,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    function renderIndicadoresRapidos(info) {
        const container = document.getElementById('indicadoresRapidos');
        container.innerHTML = '';
        
        const tpl = (title, value, icon) => `
            <div class="col-md-4 mb-3">
                <div class="metric-card">
                    <div class="metric-value">${value}</div>
                    <div class="metric-label">${title}</div>
                </div>
            </div>
        `;
        
        container.innerHTML += tpl('Custo Total', info.total_custo ? `R$ ${Number(info.total_custo).toFixed(2)}` : 'R$ 0,00');
        container.innerHTML += tpl('Custo Médio', info.avg_custo ? `R$ ${Number(info.avg_custo).toFixed(2)}` : 'R$ 0,00');
        container.innerHTML += tpl('Total de Manutenções', info.total_manut ? info.total_manut : 0);
    }

    function generateColors(n) {
        const palette = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#34495e'];
        const out = [];
        for(let i = 0; i < n; i++) out.push(palette[i % palette.length]);
        return out;
    }

    // Inicialização: preenche com período padrão (últimos 12 meses)
    document.addEventListener('DOMContentLoaded', function() {
        const to = new Date();
        const from = new Date();
        from.setMonth(from.getMonth() - 11);
        
        document.getElementById('to').value = to.toISOString().slice(0, 10);
        document.getElementById('from').value = from.toISOString().slice(0, 10);
        
        atualizarIndicadores();
    });

    function gerarPrevisoes() {
        const btn = document.getElementById('btnGerarPrevisoes');
        const feedback = document.getElementById('previsaoFeedback');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processando...';
        
        feedback.innerHTML = '<div class="alert alert-info">Processando previsões... aguarde.</div>';

        fetch('../../controllers/previsao_generate.php', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    feedback.innerHTML = '<div class="alert alert-success">' + (data.message || 'Previsões geradas com sucesso.') + '</div>';
                } else {
                    feedback.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Erro ao gerar previsões.') + '</div>';
                }
                // atualiza a página para refletir novas previsões/alertas
                setTimeout(() => location.reload(), 900);
            })
            .catch(err => {
                feedback.innerHTML = '<div class="alert alert-danger">Erro na requisição.</div>';
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Gerar Previsões';
            });
    }
    </script>

    <?php include __DIR__ . '/../includes/chatbot.php'; ?>
</body>
</html>