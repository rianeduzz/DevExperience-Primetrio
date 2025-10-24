<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Geração de CSRF token se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Conexão com o banco (ajuste usuário/senha conforme seu ambiente)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = ''; // ajuste se necessário
$dbName = 'gestao_ativos';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao banco: ' . $mysqli->connect_error);
}

// Processa submissão do formulário de registro de manutenção (POST simples)
$mensagem = '';
$tipoMensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'registrar') {
    // Verifica CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $mensagem = 'Token inválido. Recarregue a página e tente novamente.';
        $tipoMensagem = 'danger';
    } else {
        $ativo_id = (int)($_POST['ativo_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $data_manutencao = $_POST['data_manutencao'] ?? '';
        $responsavel_tecnico = $_POST['responsavel_tecnico'] ?? '';
        $custo = $_POST['custo'] !== '' ? (float)$_POST['custo'] : null;
        $descricao = $_POST['descricao'] ?? '';
        // agora lemos o texto da localizacao em vez do select
        $localizacao_text = trim($_POST['localizacao_text'] ?? '');
        $localizacao_id = null;

        // se foi informado texto, buscar na tabela localizacoes ou inserir novo registro
        if ($localizacao_text !== '') {
            $stmtLoc = $mysqli->prepare("SELECT id FROM localizacoes WHERE descricao = ? LIMIT 1");
            if ($stmtLoc) {
                $stmtLoc->bind_param("s", $localizacao_text);
                $stmtLoc->execute();
                $resLoc = $stmtLoc->get_result();
                if ($rowLoc = $resLoc->fetch_assoc()) {
                    $localizacao_id = (int)$rowLoc['id'];
                }
                $stmtLoc->close();
            }
            // se não encontrou, inserir novo local
            if ($localizacao_id === null) {
                $insLoc = $mysqli->prepare("INSERT INTO localizacoes (descricao) VALUES (?)");
                if ($insLoc) {
                    $insLoc->bind_param("s", $localizacao_text);
                    if ($insLoc->execute()) {
                        $localizacao_id = (int)$insLoc->insert_id;
                    }
                    $insLoc->close();
                }
            }
        }

        if ($ativo_id > 0 && in_array($tipo, ['preventiva','corretiva']) && $data_manutencao) {
            // insere incluindo localizacao_id quando disponível
            if ($localizacao_id !== null) {
                $stmt = $mysqli->prepare("INSERT INTO manutencoes (ativo_id, tipo, data_manutencao, responsavel_tecnico, custo, descricao, localizacao_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'agendada')");
                if ($stmt) {
                    $stmt->bind_param("isssdis", $ativo_id, $tipo, $data_manutencao, $responsavel_tecnico, $custo, $descricao, $localizacao_id);
                }
            } else {
                $stmt = $mysqli->prepare("INSERT INTO manutencoes (ativo_id, tipo, data_manutencao, responsavel_tecnico, custo, descricao, status) VALUES (?, ?, ?, ?, ?, ?, 'agendada')");
                if ($stmt) {
                    $stmt->bind_param("isssds", $ativo_id, $tipo, $data_manutencao, $responsavel_tecnico, $custo, $descricao);
                }
            }

            if ($stmt) {
                if ($stmt->execute()) {
                    $mensagem = 'Manutenção registrada com sucesso.';
                    $tipoMensagem = 'success';
                } else {
                    $mensagem = 'Erro ao salvar: ' . $stmt->error;
                    $tipoMensagem = 'danger';
                }
                $stmt->close();
            } else {
                $mensagem = 'Erro na preparação da query: ' . $mysqli->error;
                $tipoMensagem = 'danger';
            }
        } else {
            $mensagem = 'Dados inválidos. Verifique os campos obrigatórios.';
            $tipoMensagem = 'warning';
        }
    }
}

// Paginação: page param
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Busca manutenções existentes (JOIN ativos e localizacoes) com LIMIT/OFFSET
$manutencoes = [];
$stmt = $mysqli->prepare("SELECT m.id, m.ativo_id, COALESCE(a.nome,'—') AS ativo_nome, m.tipo, m.data_manutencao, m.responsavel_tecnico, m.custo, m.descricao, m.status, COALESCE(l.descricao,'—') AS localizacao FROM manutencoes m LEFT JOIN ativos a ON m.ativo_id = a.id LEFT JOIN localizacoes l ON m.localizacao_id = l.id ORDER BY m.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $manutencoes[] = $row;
$stmt->close();

// total para paginação
$resTotal = $mysqli->query("SELECT COUNT(*) AS total FROM manutencoes");
$totalMan = $resTotal ? (int)$resTotal->fetch_assoc()['total'] : 0;
$totalPages = (int)ceil($totalMan / $limit);

// Busca ativos e localizacoes para selects
$ativos = [];
$res2 = $mysqli->query("SELECT id, nome FROM ativos WHERE status <> 'descartado' ORDER BY nome");
if ($res2) {
    while ($r = $res2->fetch_assoc()) $ativos[] = $r;
    $res2->free();
}
$localizacoes = [];
$res3 = $mysqli->query("SELECT id, descricao FROM localizacoes ORDER BY descricao");
if ($res3) {
    while ($l = $res3->fetch_assoc()) $localizacoes[] = $l;
    $res3->free();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Manutenções - Youtan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background-color: var(--light-gray);
        }
        
        .table td {
            vertical-align: middle;
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
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-agendada {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-concluida {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-cancelada {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .pagination .page-link {
            color: var(--secondary-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard/index.php">
            <i class="fas fa-tools me-2"></i>Youtan
        </a>
        <div class="navbar-nav">
            <a class="nav-link" href="../dashboard/index.php">
                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
            <a class="nav-link active" href="index.php">
                <i class="fas fa-wrench me-1"></i>Manutenções
            </a>
            <a class="nav-link" href="../monitoramento/index.php">
                <i class="fas fa-heartbeat me-1"></i>Monitoramento
            </a>
            <a class="nav-link" href="../historico/index.php">
                <i class="fas fa-history me-1"></i>Histórico
            </a>
            <a class="nav-link" href="../auth/logout.php">
                <i class="fas fa-sign-out-alt me-1"></i>Sair
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="page-title">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-wrench me-2"></i>Manutenções</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrar">
                <i class="fas fa-plus me-1"></i>Registrar Manutenção
            </button>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensagem ?: 'info') ?> mt-3 d-flex align-items-center" role="alert">
            <i class="fas fa-<?= $tipoMensagem === 'success' ? 'check-circle' : ($tipoMensagem === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lista de Manutenções</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" role="table" aria-label="Tabela de manutenções">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Ativo</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Data</th>
                            <th scope="col">Localização</th>
                            <th scope="col">Responsável</th>
                            <th scope="col">Custo</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($manutencoes) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Nenhuma manutenção encontrada.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($manutencoes as $m): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($m['id']) ?></td>
                                <td><?= htmlspecialchars($m['ativo_nome']) ?></td>
                                <td>
                                    <span class="badge <?= $m['tipo'] === 'preventiva' ? 'bg-primary' : 'bg-warning' ?>">
                                        <?= htmlspecialchars(ucfirst($m['tipo'])) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($m['data_manutencao'])) ?></td>
                                <td><?= htmlspecialchars($m['localizacao']) ?></td>
                                <td><?= htmlspecialchars($m['responsavel_tecnico']) ?></td>
                                <td class="fw-bold <?= $m['custo'] > 0 ? 'text-success' : 'text-muted' ?>">
                                    <?= $m['custo'] !== null ? 'R$ ' . number_format((float)$m['custo'],2,',','.') : '—' ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $m['status'] ?>">
                                        <?= htmlspecialchars(ucfirst(str_replace('_',' ',$m['status']))) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginação acessível -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Paginação de manutenções" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="index.php?page=<?= $page - 1 ?>" aria-label="Página anterior">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="index.php?page=<?= $p ?>" aria-current="<?= $p === $page ? 'page' : 'false' ?>">
                        <?= $p ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="index.php?page=<?= $page + 1 ?>" aria-label="Próxima página">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Modal Registrar -->
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-labelledby="modalRegistrarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="index.php" aria-label="Formulário registrar manutenção">
        <input type="hidden" name="acao" value="registrar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRegistrarLabel">
            <i class="fas fa-plus-circle me-2"></i>Registrar Manutenção
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ativo_id" class="form-label">Ativo</label>
                    <select id="ativo_id" name="ativo_id" class="form-control" required>
                        <option value="">Selecione o Ativo</option>
                        <?php foreach ($ativos as $a): ?>
                            <option value="<?= htmlspecialchars($a['id']) ?>"><?= htmlspecialchars($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="preventiva">Preventiva</option>
                        <option value="corretiva">Corretiva</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="data_manutencao" class="form-label">Data</label>
                    <input type="date" id="data_manutencao" name="data_manutencao" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="localizacao_text" class="form-label">Localização</label>
                    <input type="text" id="localizacao_text" name="localizacao_text" class="form-control" placeholder="Digite a localização (ex: Almoxarifado Principal)">
                    <div class="form-text">Digite uma nova localização ou o nome de uma já existente.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="responsavel_tecnico" class="form-label">Responsável Técnico</label>
                    <input type="text" id="responsavel_tecnico" name="responsavel_tecnico" class="form-control" placeholder="Nome do responsável">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="custo" class="form-label">Custo (R$)</label>
                    <input type="number" step="0.01" id="custo" name="custo" class="form-control" placeholder="0,00">
                </div>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva os detalhes da manutenção..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/../includes/chatbot.php'; ?>
</body>
</html>