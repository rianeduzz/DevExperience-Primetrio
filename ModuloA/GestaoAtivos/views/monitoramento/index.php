<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../controllers/MonitoramentoController.php';
$controller = new MonitoramentoController();
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$ativos = $controller->listarAtivosComLocalizacao($categoria);

// Conexão com o banco (ajuste conforme seu ambiente)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = ''; // ajuste se necessário
$dbName = 'gestao_ativos';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao banco: ' . $mysqli->connect_error);
}

// Endpoint simples: retornar JSON de ativos para polling (GET ?fetch=ativos)
if (isset($_GET['fetch']) && $_GET['fetch'] === 'ativos') {
    $categoriaFilter = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? $_GET['categoria'] : null;

    // Busca a última manutenção por ativo (pela data de criação) e junta com ativos, localizações e usuário responsável
    $sql_base = "
        SELECT
            a.id,
            a.nome,
            a.categoria,
            COALESCE(l.descricao, a.localizacao, '') AS localizacao,
            COALESCE(m.responsavel_tecnico, u.nome, '-') AS responsavel,
            COALESCE(m.data_manutencao, m.created_at, a.updated_at) AS updated_at
        FROM ativos a
        LEFT JOIN (
            SELECT m1.*
            FROM manutencoes m1
            INNER JOIN (
                SELECT ativo_id, MAX(created_at) AS max_created
                FROM manutencoes
                GROUP BY ativo_id
            ) m2 ON m1.ativo_id = m2.ativo_id AND m1.created_at = m2.max_created
        ) m ON a.id = m.ativo_id
        LEFT JOIN localizacoes l ON m.localizacao_id = l.id
        LEFT JOIN usuarios u ON a.responsavel_id = u.id
    ";

    if ($categoriaFilter) {
        $stmt = $mysqli->prepare($sql_base . " WHERE a.categoria = ? ORDER BY updated_at DESC");
        if ($stmt) {
            $stmt->bind_param("s", $categoriaFilter);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = false;
        }
    } else {
        $res = $mysqli->query($sql_base . " ORDER BY updated_at DESC");
    }

    $out = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            // padroniza campos para o frontend
            $out[] = [
                'id' => $r['id'],
                'nome' => $r['nome'],
                'categoria' => $r['categoria'],
                'localizacao' => $r['localizacao'],
                'responsavel' => $r['responsavel'],
                'updated_at' => $r['updated_at']
            ];
        }
        if (isset($stmt) && $stmt) $stmt->close();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ativos' => $out]);
    exit;
}

// Endpoint: atualizar localizacao / responsavel via POST AJAX (application/json ou form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['acao_loc']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)) {
    // suporta tanto form-data quanto JSON
    $data = $_POST;
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) $data = array_merge($data, $json);
    }

    if (isset($data['acao_loc']) && $data['acao_loc'] === 'atualizar') {
        $id = (int)($data['id'] ?? 0);
        $localizacao = trim($data['localizacao'] ?? '');
        $responsavel_id = isset($data['responsavel_id']) && $data['responsavel_id'] !== '' ? (int)$data['responsavel_id'] : null;

        if ($id > 0 && $localizacao !== '') {
            if ($responsavel_id) {
                $stmt = $mysqli->prepare("UPDATE ativos SET localizacao = ?, responsavel_id = ? WHERE id = ?");
                $stmt->bind_param("sii", $localizacao, $responsavel_id, $id);
            } else {
                $stmt = $mysqli->prepare("UPDATE ativos SET localizacao = ? WHERE id = ?");
                $stmt->bind_param("si", $localizacao, $id);
            }
            if ($stmt->execute()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Localização atualizada.']);
                $stmt->close();
                exit;
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $stmt->error]);
                $stmt->close();
                exit;
            }
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            exit;
        }
    }
}

// Busca localizacoes para selects
$localizacoes_select = [];
$resLoc = $mysqli->query("SELECT id, descricao FROM localizacoes ORDER BY descricao");
if ($resLoc) {
    while ($l = $resLoc->fetch_assoc()) {
        $localizacoes_select[] = $l;
    }
    $resLoc->free();
}

// Trata ações de manutenção (registrar / editar) - atualizado para incluir localizacao_id
$mensagem_manut = '';
$tipoMensagem_manut = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_manut'])) {
    if ($_POST['acao_manut'] === 'registrar_manutencao') {
        $ativo_id = (int)($_POST['ativo_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $data_manutencao = $_POST['data_manutencao'] ?? '';
        $responsavel_tecnico = $_POST['responsavel_tecnico'] ?? '';
        $custo = $_POST['custo'] !== '' ? (float)$_POST['custo'] : null;
        $descricao = $_POST['descricao'] ?? '';
        $localizacao_id = isset($_POST['localizacao_id']) && $_POST['localizacao_id'] !== '' ? (int)$_POST['localizacao_id'] : null;

        if ($ativo_id > 0 && in_array($tipo, ['preventiva','corretiva']) && $data_manutencao) {
            $stmt = $mysqli->prepare("INSERT INTO manutencoes (ativo_id, tipo, data_manutencao, responsavel_tecnico, custo, descricao, localizacao_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'agendada')");
            if ($stmt) {
                $stmt->bind_param("isssdsi", $ativo_id, $tipo, $data_manutencao, $responsavel_tecnico, $custo, $descricao, $localizacao_id);
                if ($stmt->execute()) {
                    $mensagem_manut = 'Manutenção registrada com sucesso.';
                    $tipoMensagem_manut = 'success';
                } else {
                    $mensagem_manut = 'Erro ao salvar manutenção: ' . $stmt->error;
                    $tipoMensagem_manut = 'danger';
                }
                $stmt->close();
            } else {
                $mensagem_manut = 'Erro na preparação da query: ' . $mysqli->error;
                $tipoMensagem_manut = 'danger';
            }
        } else {
            $mensagem_manut = 'Dados inválidos para registrar manutenção.';
            $tipoMensagem_manut = 'warning';
        }
    }

    if ($_POST['acao_manut'] === 'editar_manutencao') {
        $id = (int)($_POST['id'] ?? 0);
        $ativo_id = (int)($_POST['ativo_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $data_manutencao = $_POST['data_manutencao'] ?? '';
        $responsavel_tecnico = $_POST['responsavel_tecnico'] ?? '';
        $custo = $_POST['custo'] !== '' ? (float)$_POST['custo'] : null;
        $descricao = $_POST['descricao'] ?? '';
        $status = $_POST['status'] ?? 'agendada';
        $localizacao_id = isset($_POST['localizacao_id']) && $_POST['localizacao_id'] !== '' ? (int)$_POST['localizacao_id'] : null;

        if ($id > 0 && $ativo_id > 0 && in_array($tipo, ['preventiva','corretiva']) && $data_manutencao) {
            $stmt = $mysqli->prepare("UPDATE manutencoes SET ativo_id = ?, tipo = ?, data_manutencao = ?, responsavel_tecnico = ?, custo = ?, descricao = ?, status = ?, localizacao_id = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("isssdssii", $ativo_id, $tipo, $data_manutencao, $responsavel_tecnico, $custo, $descricao, $status, $localizacao_id, $id);
                if ($stmt->execute()) {
                    $mensagem_manut = 'Manutenção atualizada com sucesso.';
                    $tipoMensagem_manut = 'success';
                } else {
                    $mensagem_manut = 'Erro ao atualizar manutenção: ' . $stmt->error;
                    $tipoMensagem_manut = 'danger';
                }
                $stmt->close();
            } else {
                $mensagem_manut = 'Erro na preparação da query de atualização: ' . $mysqli->error;
                $tipoMensagem_manut = 'danger';
            }
        } else {
            $mensagem_manut = 'Dados inválidos para atualização.';
            $tipoMensagem_manut = 'warning';
        }
    }
}

// Busca manutenções para exibir no monitoramento (agora traz localizacao_id e descrição)
$manutencoes = [];
$res = $mysqli->query("SELECT m.*, COALESCE(a.nome,'—') AS ativo_nome, m.localizacao_id, COALESCE(l.descricao,'—') AS manut_localizacao FROM manutencoes m LEFT JOIN ativos a ON m.ativo_id = a.id LEFT JOIN localizacoes l ON m.localizacao_id = l.id ORDER BY m.created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $manutencoes[] = $row;
    }
    $res->free();
}

// Busca ativos para selects e usuários para responsavel
$ativos_select = [];
$res2 = $mysqli->query("SELECT id, nome FROM ativos WHERE status <> 'descartado' ORDER BY nome");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        $ativos_select[] = $r;
    }
    $res2->free();
}
$usuarios_select = [];
$res3 = $mysqli->query("SELECT id, nome FROM usuarios ORDER BY nome");
if ($res3) {
    while ($u = $res3->fetch_assoc()) {
        $usuarios_select[] = $u;
    }
    $res3->free();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Monitoramento - Gestão de Ativos</title>
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
        
        h2, h3, h5, h6 {
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
        
        .status-em_andamento {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .asset-item {
            border-left: 4px solid var(--secondary-color);
            transition: all 0.2s;
        }
        
        .asset-item:hover {
            background-color: var(--light-gray);
            transform: translateX(2px);
        }
        
        .map-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            height: 300px;
            overflow-y: auto;
        }
        
        .location-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 8px;
            padding: 12px;
            transition: all 0.2s;
        }
        
        .location-item:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .location-badge {
            background-color: var(--secondary-color);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .last-update {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .empty-state {
            color: #6c757d;
            text-align: center;
            padding: 40px 20px;
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
                <i class="fas fa-tools me-2"></i>Gestão de Ativos
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="../dashboard/index.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="../manutencao/index.php">
                    <i class="fas fa-wrench me-1"></i>Manutenções
                </a>
                <a class="nav-link active" href="index.php">
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
            <h2><i class="fas fa-heartbeat me-2"></i>Monitoramento de Ativos</h2>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="busca" class="form-label">Buscar Ativo</label>
                        <input type="text" id="busca" class="form-control" placeholder="Digite o nome do ativo..." oninput="loadAtivosRealtime()">
                    </div>
                    <div class="col-md-4">
                        <label for="categoria" class="form-label">Filtrar por Categoria</label>
                        <select id="categoria" class="form-control" onchange="loadAtivosRealtime()">
                            <option value="">Todas as Categorias</option>
                            <option value="TI">TI</option>
                            <option value="Produção">Produção</option>
                            <option value="Escritório">Escritório</option>
                            <option value="Laboratório">Laboratório</option>
                            <option value="Logística">Logística</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" onclick="loadAtivosRealtime(true)">
                            <i class="fas fa-sync-alt me-1"></i>Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visualização em tempo real -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Visão Geral do Mapa</span>
                        <span class="badge bg-primary" id="totalAtivos">0 ativos</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="map-container p-3" id="mapList">
                            <!-- Conteúdo do mapa será carregado aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Ativos em Tempo Real</span>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>Atualiza a cada 10s
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div id="listaAtivosRealtime" class="list-group list-group-flush" style="max-height:300px; overflow:auto;">
                            <!-- itens carregados via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção Manutenções -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-wrench me-2"></i>Manutenções</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarManut">
                <i class="fas fa-plus me-1"></i>Registrar Manutenção
            </button>
        </div>

        <?php if ($mensagem_manut): ?>
            <div class="alert alert-<?= htmlspecialchars($tipoMensagem_manut ?: 'info') ?> d-flex align-items-center">
                <i class="fas fa-<?= $tipoMensagem_manut === 'success' ? 'check-circle' : ($tipoMensagem_manut === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                <?= htmlspecialchars($mensagem_manut) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Manutenções</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ativo</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Localização</th>
                                <th>Responsável</th>
                                <th>Custo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($manutencoes) === 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
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
                                        <td><?= htmlspecialchars($m['manut_localizacao']) ?></td>
                                        <td><?= htmlspecialchars($m['responsavel_tecnico']) ?></td>
                                        <td class="fw-bold <?= $m['custo'] > 0 ? 'text-success' : 'text-muted' ?>">
                                            <?= $m['custo'] !== null ? 'R$ ' . number_format((float)$m['custo'],2,',','.') : '—' ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $m['status'] ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_',' ',$m['status']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary btn-editar-manut"
                                                data-id="<?= htmlspecialchars($m['id']) ?>"
                                                data-ativo_id="<?= htmlspecialchars($m['ativo_id']) ?>"
                                                data-tipo="<?= htmlspecialchars($m['tipo']) ?>"
                                                data-data="<?= htmlspecialchars($m['data_manutencao']) ?>"
                                                data-localizacao_id="<?= htmlspecialchars($m['localizacao_id']) ?>"
                                                data-responsavel="<?= htmlspecialchars($m['responsavel_tecnico']) ?>"
                                                data-custo="<?= htmlspecialchars($m['custo']) ?>"
                                                data-descricao="<?= htmlspecialchars($m['descricao']) ?>"
                                                data-status="<?= htmlspecialchars($m['status']) ?>"
                                            >
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Atualizar Localização do Ativo -->
    <div class="modal fade" id="modalAtualizarAtivo" tabindex="-1" aria-labelledby="modalAtualizarAtivoLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="formAtualizarAtivo" onsubmit="return submitAtualizarAtivo(event)">
            <div class="modal-header">
              <h5 class="modal-title" id="modalAtualizarAtivoLabel">
                <i class="fas fa-map-marker-alt me-2"></i>Atualizar Localização
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="at_id" name="id" value="">
                <div class="mb-3">
                    <label for="at_localizacao" class="form-label">Localização</label>
                    <input type="text" id="at_localizacao" name="localizacao" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="at_responsavel" class="form-label">Responsável</label>
                    <select id="at_responsavel" name="responsavel_id" class="form-control">
                        <option value="">-- Nenhum --</option>
                        <?php foreach ($usuarios_select as $u): ?>
                            <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
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

    <!-- Modal Registrar Manutenção -->
    <div class="modal fade" id="modalRegistrarManut" tabindex="-1" aria-labelledby="modalRegistrarManutLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" action="index.php">
            <input type="hidden" name="acao_manut" value="registrar_manutencao">
            <div class="modal-header">
              <h5 class="modal-title" id="modalRegistrarManutLabel">
                <i class="fas fa-plus-circle me-2"></i>Registrar Manutenção
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="r_ativo_id" class="form-label">Ativo</label>
                        <select id="r_ativo_id" name="ativo_id" class="form-control" required>
                            <option value="">Selecione o Ativo</option>
                            <?php foreach ($ativos_select as $a): ?>
                                <option value="<?= htmlspecialchars($a['id']) ?>"><?= htmlspecialchars($a['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="r_tipo" class="form-label">Tipo</label>
                        <select id="r_tipo" name="tipo" class="form-control" required>
                            <option value="preventiva">Preventiva</option>
                            <option value="corretiva">Corretiva</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="r_data_manutencao" class="form-label">Data</label>
                        <input type="date" id="r_data_manutencao" name="data_manutencao" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="r_localizacao_id" class="form-label">Localização</label>
                        <select id="r_localizacao_id" name="localizacao_id" class="form-control">
                            <option value="">-- Selecionar local --</option>
                            <?php foreach ($localizacoes_select as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['id']) ?>"><?= htmlspecialchars($loc['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="r_responsavel_tecnico" class="form-label">Responsável Técnico</label>
                        <input type="text" id="r_responsavel_tecnico" name="responsavel_tecnico" class="form-control" placeholder="Nome do responsável">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="r_custo" class="form-label">Custo (R$)</label>
                        <input type="number" step="0.01" id="r_custo" name="custo" class="form-control" placeholder="0,00">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="r_descricao" class="form-label">Descrição</label>
                    <textarea id="r_descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva os detalhes da manutenção..."></textarea>
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

    <!-- Modal Editar Manutenção -->
    <div class="modal fade" id="modalEditarManut" tabindex="-1" aria-labelledby="modalEditarManutLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" action="index.php" id="formEditarManut">
            <input type="hidden" name="acao_manut" value="editar_manutencao">
            <input type="hidden" name="id" id="e_id" value="">
            <div class="modal-header">
              <h5 class="modal-title" id="modalEditarManutLabel">
                <i class="fas fa-edit me-2"></i>Editar Manutenção
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="e_ativo_id" class="form-label">Ativo</label>
                        <select id="e_ativo_id" name="ativo_id" class="form-control" required>
                            <option value="">Selecione o Ativo</option>
                            <?php foreach ($ativos_select as $a): ?>
                                <option value="<?= htmlspecialchars($a['id']) ?>"><?= htmlspecialchars($a['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="e_tipo" class="form-label">Tipo</label>
                        <select id="e_tipo" name="tipo" class="form-control" required>
                            <option value="preventiva">Preventiva</option>
                            <option value="corretiva">Corretiva</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="e_data_manutencao" class="form-label">Data</label>
                        <input type="date" id="e_data_manutencao" name="data_manutencao" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="e_localizacao_id" class="form-label">Localização</label>
                        <select id="e_localizacao_id" name="localizacao_id" class="form-control">
                            <option value="">-- Selecionar local --</option>
                            <?php foreach ($localizacoes_select as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['id']) ?>"><?= htmlspecialchars($loc['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="e_responsavel_tecnico" class="form-label">Responsável Técnico</label>
                        <input type="text" id="e_responsavel_tecnico" name="responsavel_tecnico" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="e_custo" class="form-label">Custo (R$)</label>
                        <input type="number" step="0.01" id="e_custo" name="custo" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="e_descricao" class="form-label">Descrição</label>
                    <textarea id="e_descricao" name="descricao" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="e_status" class="form-label">Status</label>
                    <select id="e_status" name="status" class="form-control" required>
                        <option value="agendada">Agendada</option>
                        <option value="em_andamento">Em andamento</option>
                        <option value="concluida">Concluída</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i>Cancelar
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Atualizar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para carregar ativos via endpoint JSON e popular a lista e o "mapPlaceholder"
        let pollingInterval = 10000; // 10s
        let pollingTimer = null;

        async function loadAtivosRealtime(force=false) {
            const busca = document.getElementById('busca').value.trim();
            const categoria = document.getElementById('categoria').value;
            const params = new URLSearchParams();
            params.set('fetch','ativos');
            if (categoria) params.set('categoria', categoria);

            try {
                const res = await fetch('index.php?' + params.toString());
                const data = await res.json();
                const list = document.getElementById('listaAtivosRealtime');
                const mapList = document.getElementById('mapList');
                const totalAtivos = document.getElementById('totalAtivos');
                
                list.innerHTML = '';
                mapList.innerHTML = '';

                let filteredAtivos = data.ativos;
                if (busca) {
                    filteredAtivos = data.ativos.filter(a => 
                        a.nome.toLowerCase().includes(busca.toLowerCase())
                    );
                }

                totalAtivos.textContent = `${filteredAtivos.length} ativo${filteredAtivos.length !== 1 ? 's' : ''}`;

                // Agrupar ativos por localização para o mapa
                const ativosPorLocalizacao = {};
                filteredAtivos.forEach(a => {
                    if (!ativosPorLocalizacao[a.localizacao]) {
                        ativosPorLocalizacao[a.localizacao] = [];
                    }
                    ativosPorLocalizacao[a.localizacao].push(a);
                });

                // Criar itens do mapa
                Object.keys(ativosPorLocalizacao).forEach(localizacao => {
                    if (localizacao && localizacao !== '') {
                        const locationDiv = document.createElement('div');
                        locationDiv.className = 'location-item';
                        
                        const ativosCount = ativosPorLocalizacao[localizacao].length;
                        const ativosList = ativosPorLocalizacao[localizacao].map(a => 
                            `<div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">${escapeHtml(a.nome)}</span>
                                <span class="location-badge">${escapeHtml(a.categoria)}</span>
                            </div>`
                        ).join('');
                        
                        locationDiv.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    ${escapeHtml(localizacao)}
                                </h6>
                                <span class="badge bg-secondary">${ativosCount} ativo${ativosCount !== 1 ? 's' : ''}</span>
                            </div>
                            ${ativosList}
                        `;
                        mapList.appendChild(locationDiv);
                    }
                });

                // Criar itens da lista
                filteredAtivos.forEach(a => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item asset-item';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0">${escapeHtml(a.nome)}</h6>
                                    <span class="badge bg-light text-dark">${escapeHtml(a.categoria)}</span>
                                </div>
                                <div class="mb-1">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                    <small>${escapeHtml(a.localizacao)}</small>
                                </div>
                                <div class="mb-1">
                                    <i class="fas fa-user text-muted me-1"></i>
                                    <small>${escapeHtml(a.responsavel)}</small>
                                </div>
                                <div class="last-update">
                                    <i class="fas fa-clock text-muted me-1"></i>
                                    ${escapeHtml(formatDate(a.updated_at))}
                                </div>
                            </div>
                        </div>
                    `;
                    list.appendChild(item);
                });

                // Estados vazios
                if (filteredAtivos.length === 0) {
                    list.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>Nenhum ativo encontrado</p>
                        </div>
                    `;
                    mapList.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                            <p>Nenhum ativo para exibir no mapa</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('Erro ao carregar ativos:', e);
                const mapList = document.getElementById('mapList');
                mapList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i>
                        <p>Erro ao carregar dados do mapa</p>
                    </div>
                `;
            }

            if (force) {
                restartPolling();
            }
        }

        function escapeHtml(s){
            if (s === null || s === undefined) return '';
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(dateString) {
            if (!dateString) return 'Nunca';
            const date = new Date(dateString);
            return date.toLocaleString('pt-BR');
        }

        function abrirModalAtualizar(id, localizacao, responsavelNome){
            const modalEl = document.getElementById('modalAtualizarAtivo');
            const bsModal = new bootstrap.Modal(modalEl);
            document.getElementById('at_id').value = id;
            document.getElementById('at_localizacao').value = localizacao || '';
            // tenta selecionar por nome (se houver), caso contrário limpa
            const sel = document.getElementById('at_responsavel');
            let found = false;
            for (let i=0;i<sel.options.length;i++){
                if (sel.options[i].text === responsavelNome) { sel.selectedIndex = i; found = true; break; }
            }
            if (!found) sel.value = '';
            bsModal.show();
        }

        async function submitAtualizarAtivo(e){
            e.preventDefault();
            const id = document.getElementById('at_id').value;
            const localizacao = document.getElementById('at_localizacao').value;
            const responsavel_id = document.getElementById('at_responsavel').value;
            const payload = { acao_loc: 'atualizar', id: id, localizacao: localizacao, responsavel_id: responsavel_id };

            try {
                const res = await fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    // fechar modal e recarregar lista
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAtualizarAtivo'));
                    if (modal) modal.hide();
                    loadAtivosRealtime(true);
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Erro ao enviar atualização.');
            }
            return false;
        }

        function startPolling() {
            if (pollingTimer) clearInterval(pollingTimer);
            pollingTimer = setInterval(() => loadAtivosRealtime(), pollingInterval);
        }
        function restartPolling() {
            if (pollingTimer) clearInterval(pollingTimer);
            startPolling();
        }

        // init
        document.addEventListener('DOMContentLoaded', function(){
            loadAtivosRealtime();
            startPolling();
        });

        // abrir modal de editar manutenção e popular campos (inclui localizacao)
        document.querySelectorAll('.btn-editar-manut').forEach(btn => {
            btn.addEventListener('click', function () {
                const modalEl = document.getElementById('modalEditarManut');
                const bsModal = new bootstrap.Modal(modalEl);

                document.getElementById('e_id').value = this.dataset.id || '';
                document.getElementById('e_ativo_id').value = this.dataset.ativo_id || '';
                document.getElementById('e_tipo').value = this.dataset.tipo || 'preventiva';
                document.getElementById('e_data_manutencao').value = this.dataset.data || '';
                document.getElementById('e_localizacao_id').value = this.dataset.localizacao_id || '';
                document.getElementById('e_responsavel_tecnico').value = this.dataset.responsavel || '';
                document.getElementById('e_custo').value = this.dataset.custo !== 'NULL' ? this.dataset.custo : '';
                document.getElementById('e_descricao').value = this.dataset.descricao || '';
                document.getElementById('e_status').value = this.dataset.status || 'agendada';

                bsModal.show();
            });
        });
    </script>

    <?php include __DIR__ . '/../includes/chatbot.php'; ?>
</body>
</html>