<?php
require_once 'includes/config.php';

try {
    $sql = file_get_contents('migrations/2026_update_status_enum.sql');
    $pdo->exec($sql);
    echo "Migration 2 (Enum Update) completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>