<?php
require_once __DIR__.'/../config/database.php';
$db=databaseConnection();$name='rentabau_finance_ui_test';
// This fixture database contains only synthetic records; never copy customer rows.
$db->exec('CREATE DATABASE IF NOT EXISTS rentabau_finance_ui_test CHARACTER SET utf8mb4');
foreach(['users','reservations'] as $table)$db->exec('CREATE TABLE IF NOT EXISTS rentabau_finance_ui_test.'.$table.' LIKE rentabau.'.$table);
$db->exec('USE rentabau_finance_ui_test');
$hash=password_hash('Finance-Test-Only-2026!',PASSWORD_DEFAULT);
$q=$db->prepare('INSERT INTO users(name,email,password_hash,role,is_active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)');
$q->execute(['Finance Test Admin','finance-admin@example.invalid',$hash,'admin']);
$q->execute(['Finance Test Customer','finance-customer@example.invalid',$hash,'customer']);
if((int)$db->query('SELECT COUNT(*) FROM reservations')->fetchColumn()===0)$db->exec("INSERT INTO reservations(machine_id,machine_name,customer_name,email,phone,start_date,end_date,notes,total_price,days,admin_note,status) VALUES('test','Minibagger Test','Musterkunde','kunde@example.invalid','000','2026-09-10','2026-09-12','Testauftrag',150,3,'','bestätigt')");
echo "PASS isolated UI fixture ready\n";
