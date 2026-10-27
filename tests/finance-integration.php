<?php
require_once __DIR__.'/../includes/finance.php';
require_once __DIR__.'/../includes/finance-report.php';
require_once __DIR__.'/../includes/finance-mail.php';
function verifyFin(bool $ok,string $why):void{if(!$ok)throw new RuntimeException($why);}
function rejectsFin(callable $fn):void{try{$fn();throw new RuntimeException('Expected rejection');}catch(InvalidArgumentException $expected){}}
$server=financeDb();$name='rentabau_finance_test_'.bin2hex(random_bytes(5));
$server->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
try {
 $server->exec('CREATE TABLE `'.$name.'`.reservations LIKE reservations');
 $server->exec('USE `'.$name.'`');
 $server->exec(file_get_contents(__DIR__.'/../database/migrations/006_finance.sql'));
 $server->exec("INSERT INTO reservations(machine_id,machine_name,customer_name,email,phone,start_date,end_date,notes,total_price,days,admin_note,status) VALUES('test','Bagger','Testkunde','test@example.invalid','000','2026-09-01','2026-09-03','',100,3,'','abgeschlossen')");
 $rid=(int)$server->lastInsertId();
 financeSaveSettings($server,['company_name'=>'Rent A Bau Test','address'=>"Teststrasse 1\n8590 Romanshorn",'email'=>'sender@example.invalid','iban'=>'','tax_number'=>'TEST']);
 $data=['reservation_id'=>$rid,'due_date'=>date('Y-m-d',strtotime('+30 days')),'customer_address'=>"Musterstrasse 2\n9000 St. Gallen",'items'=>[['description'=>'Bagger Miete','quantity'=>2,'unit_price'=>'50.00','tax_rate'=>'0']],'notes'=>'Testbeleg – keine echte Rechnung'];
 $id=financeIssue($server,$data);$invoice=financeInvoice($server,$id);$originalPdf=$invoice['pdf_blob'];
 verifyFin($invoice['total_cents']==10000,'Invoice total');verifyFin(str_starts_with($originalPdf,'%PDF-'),'PDF generated');
 file_put_contents(__DIR__.'/finance-sample.pdf',$originalPdf);
 rejectsFin(fn()=>financeIssue($server,$data));
 $payment=['invoice_id'=>$id,'amount'=>'40','date'=>date('Y-m-d'),'method'=>'cash','reference'=>'Teilzahlung','request_key'=>'test-payment-request-0001'];
 $pid=financePayment($server,$payment);verifyFin(financePayment($server,$payment)===$pid,'Idempotent retry');verifyFin(financePaid($server,$id)===4000,'No duplicate payment');
 rejectsFin(fn()=>financePayment($server,array_replace($payment,['amount'=>'41'])));
 rejectsFin(fn()=>financePayment($server,array_replace($payment,['amount'=>'61','request_key'=>null])));
 financePayment($server,array_replace($payment,['amount'=>'60','request_key'=>'test-payment-request-0002']));
 $report=financeReport($server,['from'=>'2026-01-01','to'=>'2026-12-31']);verifyFin($report['summary']['received_cents']===10000,'Receipts summary');verifyFin($report['summary']['outstanding_cents']===0,'Paid balance');verifyFin($report['customers'][0]['reservation_count']===1,'Customer history');
 financeSaveSettings($server,['company_name'=>'Neue Firma','address'=>'Andere Adresse','email'=>'new@example.invalid','iban'=>'','tax_number'=>'']);
 verifyFin(financeInvoice($server,$id)['pdf_blob']===$originalPdf,'Archived PDF immutable after settings change');
 $credit=financeCredit($server,$id,'Testkorrektur');rejectsFin(fn()=>financeCredit($server,$id,'Doppelt'));
 rejectsFin(fn()=>financePayment($server,array_replace($payment,['amount'=>'1','request_key'=>null])));
 financePayment($server,['payment_id'=>$pid,'amount'=>'40','date'=>date('Y-m-d'),'reference'=>'Erstattung'],true);
 rejectsFin(fn()=>financePayment($server,['payment_id'=>$pid,'amount'=>'1','date'=>date('Y-m-d'),'reference'=>'Zu viel'],true));
 financeExpense($server,['supplier'=>'Werkstatt','description'=>'Reparatur','amount'=>'12.50','date'=>date('Y-m-d'),'category'=>'Unterhalt','payment_method'=>'bank']);
 $report=financeReport($server,['from'=>'2026-01-01','to'=>'2026-12-31']);verifyFin($report['summary']['invoiced_cents']===0,'Credit offsets invoice');verifyFin($report['summary']['received_cents']===6000,'Refund reduces receipts');verifyFin($report['summary']['refund_due_cents']===6000,'Remaining refund tracked');verifyFin($report['summary']['expenses_cents']===1250,'Expenses report');
 $mail=getenv('RENTABAU_FINANCE_MAIL_FROM');putenv('RENTABAU_FINANCE_MAIL_FROM');
 try {financeSendCopy($server,$id,'test@example.invalid');throw new LogicException('Mail incorrectly succeeded');}catch(RuntimeException $expected){verifyFin(str_contains($expected->getMessage(),'nicht eingerichtet'),'Unconfigured mail explicit');}finally{if($mail!==false)putenv('RENTABAU_FINANCE_MAIL_FROM='.$mail);}
 verifyFin((int)$server->query('SELECT COUNT(*) FROM fin_events')->fetchColumn()>=8,'Actions logged');
 echo "PASS finance issue, deduplication, payments, overpayment, refunds, credits, PDF archive, customer report and mail guard\n";
} finally {
 $server->exec('USE rentabau');
 if(preg_match('/^rentabau_finance_test_[a-f0-9]{10}$/D',$name))$server->exec('DROP DATABASE `'.$name.'`');
}
