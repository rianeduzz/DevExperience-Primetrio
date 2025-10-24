<?php
// Uso CLI: php backup_db.php
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'gestao_ativos';

// Caminho para mysqldump (ajuste em Windows XAMPP se necessário)
$mysqldumpPath = 'mysqldump'; // se não estiver no PATH, coloque "C:\xampp\mysql\bin\mysqldump.exe"

$backupDir = __DIR__ . '/../storage/backups';
@mkdir($backupDir, 0755, true);

$timestamp = date('Ymd_His');
$filename = "{$backupDir}/backup_{$dbName}_{$timestamp}.sql.gz";

$cmd = sprintf('%s --user=%s --password=%s --host=%s %s 2>&1 | gzip > %s',
    escapeshellcmd($mysqldumpPath),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbHost),
    escapeshellarg($dbName),
    escapeshellarg($filename)
);

echo "Executando backup...\n";
exec($cmd, $output, $rv);
if ($rv === 0) {
    echo "Backup salvo em: $filename\n";
} else {
    echo "Erro ao gerar backup. Saída:\n" . implode("\n", $output) . "\n";
}
