<?php
require_once __DIR__ . '/../db.php';
$count = (int)$db->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
assert($count >= 1, "Expected seeded appointments in medcare");
echo "medcare smoke test PASSED (appointments={$count})\n";
