<?php
session_start();
if (!isset($_SESSION['user_id'])) {
	header('Location: ../auth/login.php');
	exit;
}

require_once __DIR__ . '/../../controllers/PrevisaoController.php';
$ctrl = new PrevisaoController();
try {
	$previsoes = $ctrl->listarPrevisoes();
} catch (Exception $e) {
	$previsoes = [];
	$error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<title>Previsão de Manutenção (IA)</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<?php include '../includes/navbar.php'; ?>

	<div class="container mt-4">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h2>Previsão de Manutenção (IA)</h2>
			<div>
				<button id="btnGerar" class="btn btn-primary">Gerar Previsões</button>
				<a href="../historico/index.php" class="btn btn-outline-secondary">Ir para Histórico</a>
			</div>
		</div>

		<?php if (!empty($error)): ?>
			<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<div id="feedback"></div>

		<div class="table-responsive mt-3">
			<table class="table table-striped" id="tablePrevisoes">
				<thead>
					<tr>
						<th>#</th>
						<th>Ativo</th>
						<th>Probabilidade (%)</th>
						<th>Data Previsão</th>
						<th>Razões / Observações</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($previsoes)): ?>
						<tr><td colspan="5" class="text-center">Nenhuma previsão encontrada.</td></tr>
					<?php else: ?>
						<?php foreach ($previsoes as $p): ?>
							<tr>
								<td><?= htmlspecialchars($p['ativo_id']) ?></td>
								<td><?= htmlspecialchars($p['nome_ativo'] ?? ('#'.$p['ativo_id'])) ?></td>
								<td><?= htmlspecialchars(number_format($p['probabilidade_falha'],2,',','.')) ?></td>
								<td><?= htmlspecialchars($p['data_previsao']) ?></td>
								<td style="max-width:400px;white-space:pre-wrap;"><?= htmlspecialchars($p['razoes']) ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

<script>
document.getElementById('btnGerar').addEventListener('click', function(){
	if (!confirm('Gerar previsões agora? Isso processará o histórico e poderá criar alertas.')) return;
	const btn = this;
	btn.disabled = true;
	document.getElementById('feedback').innerHTML = '<div class="alert alert-info">Processando previsões...</div>';
	fetch('../../controllers/previsao_generate.php', { method: 'POST', headers: {'Content-Type':'application/json'} })
		.then(r=>r.json())
		.then(data=>{
			if (data.success) {
				document.getElementById('feedback').innerHTML = '<div class="alert alert-success">'+(data.message || 'Previsões geradas.')+'</div>';
			} else {
				document.getElementById('feedback').innerHTML = '<div class="alert alert-danger">'+(data.message || 'Erro ao gerar previsões.')+'</div>';
			}
			// recarrega a página para listar previsões atualizadas
			setTimeout(()=> location.reload(), 900);
		})
		.catch(err=>{
			document.getElementById('feedback').innerHTML = '<div class="alert alert-danger">Erro na requisição.</div>';
			btn.disabled = false;
		});
});
</script>

</body>
</html>
