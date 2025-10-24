<?php
// Uso CLI: php restore_db.php path/to/backup.sql.gz
if ($argc < 2) {
    echo "Uso: php restore_db.php caminho/backup.sql.gz\n";
    exit(1);
}

$backupFile = $argv[1];
if (!file_exists($backupFile)) {
    echo "Arquivo não encontrado: $backupFile\n";
    exit(1);
}

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'gestao_ativos';

// caminho para mysql (ajuste se necessário)
$mysqlPath = 'mysql';

$cmd = sprintf('gunzip -c %s | %s --user=%s --password=%s --host=%s %s 2>&1',
    escapeshellarg($backupFile),
    escapeshellcmd($mysqlPath),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbHost),
    escapeshellarg($dbName)
);

echo "Iniciando restore...\n";
exec($cmd, $output, $rv);
if ($rv === 0) {
    echo "Restore concluído com sucesso.\n";
} else {
    echo "Erro no restore. Saída:\n" . implode("\n", $output) . "\n";
}
