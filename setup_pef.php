<?php
require __DIR__ . '/fonctions.php';
$pdo = getDB();
$sql = file_get_contents(__DIR__ . '/create_pef.sql');
$pdo->exec($sql);
echo "Table pef_articles créée ou déjà existante.\n";
