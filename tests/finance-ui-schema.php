<?php
require_once __DIR__.'/../includes/finance.php';
$db=financeDb();$db->exec('USE rentabau_finance_ui_test');$db->exec(file_get_contents(__DIR__.'/../database/migrations/006_finance.sql'));echo "UI finance schema ready\n";
