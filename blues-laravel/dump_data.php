<?php
// Standalone script — no Laravel bootstrap needed, direct SQLite access
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

$out = [];
foreach ($tables as $table) {
    $rows = $db->query("SELECT * FROM \"$table\"")->fetchAll(PDO::FETCH_ASSOC);
    $out[$table] = $rows;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
