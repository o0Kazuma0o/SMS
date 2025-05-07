<?php
session_start();
require('../database.php');

// Ensure only admins can access this script
if ($_SESSION['role'] !== 'Admin') {
    die('Unauthorized access');
}

$backupDir = __DIR__ . '/../backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Command to export the database
$dbHost = "localhost";
$dbUser = "admi_caps";
$dbPass = "re^AKBzarIgoqxka";
$dbName = "admi_bcp_sms3_admission";

$command = "mysqldump --host=$dbHost --user=$dbUser --password=$dbPass $dbName > $backupFile";

exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "Backup successful! File saved at: $backupFile";
} else {
    echo "Backup failed. Please check your configuration.";
}
?>