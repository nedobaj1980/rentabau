<?php
require_once __DIR__.'/../includes/finance.php';$db=financeDb();$db->exec('USE rentabau_finance_ui_test');financeSetup($db);
$db->exec("INSERT INTO reservations(machine_id,machine_name,customer_name,email,phone,start_date,end_date,notes,total_price,days,admin_note,status) VALUES('ui-test','Mobilbagger UI Test','UI Musterkunde','ui-kunde@example.invalid','000','2026-09-10','2026-09-12','Browser-Test',150,3,'','bestätigt')");
file_put_contents(__DIR__.'/finance-fixture-state.json',json_encode(['reservation_id'=>(int)$db->lastInsertId()]));echo "Browser fixture prepared\n";
