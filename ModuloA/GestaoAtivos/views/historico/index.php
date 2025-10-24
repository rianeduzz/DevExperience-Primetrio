<?php
session_start();
if (!isset($_SESSION['user_id'])) {
	header('Location: ../auth/login.php');
	exit;
}

// Conexão simples (ajuste credenciais se necessário)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'gestao_ativos';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
	die('Erro ao conectar ao banco: ' . $mysqli->connect_error);
}

// Paginacao
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Carrega ativos para o select
$ativos = [];
$resAt = $mysqli->query("SELECT id, nome FROM ativos ORDER BY nome");
if ($resAt) {
	while ($r = $resAt->fetch_assoc()) $ativos[] = $r;
	$resAt->free();
}

// Busca manutenções (filtra por ativo se informado) com LIMIT/OFFSET
$ativo_id = isset($_GET['ativo_id']) && $_GET['ativo_id'] !== '' ? (int)$_GET['ativo_id'] : null;
$manutencoes = [];

if ($ativo_id) {
	$stmt = $mysqli->prepare("SELECT m.id, m.data_manutencao, m.tipo, COALESCE(a.nome,'-') AS ativo, COALESCE(l.descricao,'-') AS localizacao, m.responsavel_tecnico, m.custo, m.descricao, m.status
								FROM manutencoes m
								LEFT JOIN ativos a ON m.ativo_id = a.id
								LEFT JOIN localizacoes l ON m.localizacao_id = l.id
								WHERE m.ativo_id = ?
								ORDER BY m.data_manutencao DESC, m.created_at DESC
								LIMIT ? OFFSET ?");
	$stmt->bind_param("iii", $ativo_id, $limit, $offset);
	$stmt->execute();
	$res = $stmt->get_result();
} else {
	$stmt = $mysqli->prepare("SELECT m.id, m.data_manutencao, m.tipo, COALESCE(a.nome,'-') AS ativo, COALESCE(l.descricao,'-') AS localizacao, m.responsavel_tecnico, m.custo, m.descricao, m.status
								FROM manutencoes m
								LEFT JOIN ativos a ON m.ativo_id = a.id
								LEFT JOIN localizacoes l ON m.localizacao_id = l.id
								ORDER BY m.data_manutencao DESC, m.created_at DESC
								LIMIT ? OFFSET ?");
	$stmt->bind_param("ii", $limit, $offset);
	$stmt->execute();
	$res = $stmt->get_result();
}
if ($res) {
	while ($r = $res->fetch_assoc()) $manutencoes[] = $r;
	$stmt->close();
}

// total para paginação (respeita filtro)
if ($ativo_id) {
	$stmtT = $mysqli->prepare("SELECT COUNT(*) AS total FROM manutencoes WHERE ativo_id = ?");
	$stmtT->bind_param("i", $ativo_id);
	$stmtT->execute();
	$total = $stmtT->get_result()->fetch_assoc()['total'];
	$stmtT->close();
} else {
	$totalRes = $mysqli->query("SELECT COUNT(*) AS total FROM manutencoes");
	$total = $totalRes ? (int)$totalRes->fetch_assoc()['total'] : 0;
}
$totalPages = (int)ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<title>Histórico - Gestão de Ativos</title>
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
		
		.status-em_andamento {
			background-color: #fff3e0;
			color: #f57c00;
		}
		
		.pagination .page-link {
			color: var(--secondary-color);
		}
		
		.pagination .page-item.active .page-link {
			background-color: var(--secondary-color);
			border-color: var(--secondary-color);
		}
		
		@media print {
			.no-print { display: none !important; }
			.card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
			.table { border: 1px solid #dee2e6 !important; }
		}
	</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark no-print">
	<div class="container-fluid">
		<a class="navbar-brand" href="../dashboard/index.php">
			<i class="fas fa-tools me-2"></i>Youtan
		</a>
		<div class="navbar-nav">
			<a class="nav-link" href="../dashboard/index.php">
				<i class="fas fa-tachometer-alt me-1"></i>Dashboard
			</a>
			<a class="nav-link" href="../manutencao/index.php">
				<i class="fas fa-wrench me-1"></i>Manutenções
			</a>
			<a class="nav-link" href="../monitoramento/index.php">
				<i class="fas fa-heartbeat me-1"></i>Monitoramento
			</a>
			<a class="nav-link active" href="index.php">
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
			<h2><i class="fas fa-history me-2"></i>Histórico de Manutenções</h2>
			<div class="no-print">
				<button class="btn btn-outline-secondary me-2" onclick="window.print()">
					<i class="fas fa-print me-1"></i>Imprimir
				</button>
				<a href="index.php?export=csv<?= $ativo_id ? '&ativo_id=' . $ativo_id : '' ?>" class="btn btn-success">
					<i class="fas fa-file-export me-1"></i>Exportar CSV
				</a>
			</div>
		</div>
	</div>

	<!-- Filtros -->
	<div class="card no-print">
		<div class="card-header">
			<h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
		</div>
		<div class="card-body">
			<form method="GET" class="row g-3 align-items-end">
				<input type="hidden" name="fetch" value="">
				<div class="col-md-6">
					<label for="ativo_id" class="form-label">Filtrar por Ativo</label>
					<select id="ativo_id" name="ativo_id" class="form-select">
						<option value="">Todos os Ativos</option>
						<?php foreach ($ativos as $a): ?>
							<option value="<?= htmlspecialchars($a['id']) ?>" <?= $ativo_id == $a['id'] ? 'selected' : '' ?>>
								<?= htmlspecialchars($a['nome']) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-3">
					<button type="submit" class="btn btn-primary w-100">
						<i class="fas fa-search me-1"></i>Aplicar Filtro
					</button>
				</div>
				<div class="col-md-3">
					<a href="index.php" class="btn btn-outline-secondary w-100">
						<i class="fas fa-times me-1"></i>Limpar Filtro
					</a>
				</div>
			</form>
		</div>
	</div>

	<!-- Resumo -->
	<div class="card no-print">
		<div class="card-body">
			<div class="row text-center">
				<div class="col-md-3">
					<div class="border rounded p-3">
						<div class="h4 text-primary mb-1"><?= $total ?></div>
						<div class="text-muted small">Total de Manutenções</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="border rounded p-3">
						<div class="h4 text-success mb-1">
							<?php
							$resConcluidas = $mysqli->query("SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'concluida'");
							$concluidas = $resConcluidas ? $resConcluidas->fetch_assoc()['total'] : 0;
							echo $concluidas;
							?>
						</div>
						<div class="text-muted small">Concluídas</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="border rounded p-3">
						<div class="h4 text-warning mb-1">
							<?php
							$resAgendadas = $mysqli->query("SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'agendada'");
							$agendadas = $resAgendadas ? $resAgendadas->fetch_assoc()['total'] : 0;
							echo $agendadas;
							?>
						</div>
						<div class="text-muted small">Agendadas</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="border rounded p-3">
						<div class="h4 text-info mb-1">
							<?php
							$resAndamento = $mysqli->query("SELECT COUNT(*) AS total FROM manutencoes WHERE status = 'em_andamento'");
							$andamento = $resAndamento ? $resAndamento->fetch_assoc()['total'] : 0;
							echo $andamento;
							?>
						</div>
						<div class="text-muted small">Em Andamento</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Tabela de Histórico -->
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">Registros de Manutenção</h5>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover mb-0" role="table" aria-label="Tabela histórico de manutenções">
					<thead>
						<tr>
							<th scope="col">ID</th>
							<th scope="col">Data</th>
							<th scope="col">Tipo</th>
							<th scope="col">Ativo</th>
							<th scope="col">Localização</th>
							<th scope="col">Técnico</th>
							<th scope="col">Custo</th>
							<th scope="col">Status</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($manutencoes)): ?>
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
									<td><?= date('d/m/Y', strtotime($m['data_manutencao'])) ?></td>
									<td>
										<span class="badge <?= $m['tipo'] === 'preventiva' ? 'bg-primary' : 'bg-warning' ?>">
											<?= htmlspecialchars(ucfirst($m['tipo'])) ?>
										</span>
									</td>
									<td><?= htmlspecialchars($m['ativo']) ?></td>
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

	<!-- Paginação -->
	<?php if ($totalPages > 1): ?>
	<nav aria-label="Paginação do histórico" class="mt-4 no-print">
		<ul class="pagination justify-content-center">
			<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
				<a class="page-link" href="index.php?ativo_id=<?= $ativo_id ?>&page=<?= $page - 1 ?>" aria-label="Página anterior">
					<i class="fas fa-chevron-left"></i>
				</a>
			</li>
			
			<?php for ($p = 1; $p <= $totalPages; $p++): ?>
				<li class="page-item <?= $p === $page ? 'active' : '' ?>">
					<a class="page-link" href="index.php?ativo_id=<?= $ativo_id ?>&page=<?= $p ?>" aria-current="<?= $p === $page ? 'page' : 'false' ?>">
						<?= $p ?>
					</a>
				</li>
			<?php endfor; ?>
			
			<li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
				<a class="page-link" href="index.php?ativo_id=<?= $ativo_id ?>&page=<?= $page + 1 ?>" aria-label="Próxima página">
					<i class="fas fa-chevron-right"></i>
				</a>
			</li>
		</ul>
	</nav>
	<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/../includes/chatbot.php'; ?>
</body>
</html>