<?php
declare(strict_types=1);
require_once __DIR__.'/finance.php';
function financeReport(PDO $db,array $filters=[]): array {
    $from=($filters['from']??'')!==''?financeDate((string)$filters['from']):date('Y-01-01');
    $to=($filters['to']??'')!==''?financeDate((string)$filters['to']):date('Y-12-31');
    if($from>$to)throw new InvalidArgumentException('Startdatum liegt nach dem Enddatum.');
    $customer=mb_strtolower(financeText($filters['customer']??'',120,false));
    $matches=fn(array $r)=>$customer===''||((str_starts_with($customer,'user:')||str_starts_with($customer,'email:'))?mb_strtolower($r['customer_key']??financeCustomerKey($r))===$customer:str_contains(mb_strtolower(($r['customer_name']??'').' '.($r['email']??'')),$customer));
    $inPeriod=fn(string $date)=>$date>=$from&&$date<=$to;
    $reservations=$db->query('SELECT id,user_id,company,customer_name,email,phone,machine_id,machine_name,start_date,end_date,total_price,days,handover,payment_status,payment_preference,status FROM reservations ORDER BY start_date DESC,id DESC')->fetchAll();
    $invoices=$db->query('SELECT id,number,reservation_id,kind,original_invoice_id,customer_key,customer_name,email,customer_address,issue_date,due_date,net_cents,tax_cents,total_cents,items_json,notes,created_at FROM fin_invoices ORDER BY id DESC')->fetchAll();
    $payments=$db->query('SELECT p.*,i.number AS invoice_number,i.customer_name,i.email,i.customer_key,i.reservation_id FROM fin_payments p JOIN fin_invoices i ON i.id=p.invoice_id ORDER BY p.date DESC,p.id DESC')->fetchAll();
    $paid=[];$refunded=[];foreach($payments as &$p){foreach(['id','invoice_id','amount_cents'] as $k)$p[$k]=(int)$p[$k];$paid[$p['invoice_id']]=($paid[$p['invoice_id']]??0)+$p['amount_cents'];if($p['refund_of'])$refunded[$p['refund_of']]=($refunded[$p['refund_of']]??0)-$p['amount_cents'];}unset($p);
    foreach($payments as &$p)$p['refundable_cents']=max(0,$p['amount_cents']-($refunded[$p['id']]??0));unset($p);
    $credited=[];foreach($invoices as $i)if($i['original_invoice_id'])$credited[$i['original_invoice_id']]=true;
    $byReservation=[];$summary=['invoiced_cents'=>0,'received_cents'=>0,'outstanding_cents'=>0,'overdue_cents'=>0,'expenses_cents'=>0,'completed_orders'=>0,'refund_due_cents'=>0];
    foreach($invoices as &$i){
        foreach(['id','reservation_id','net_cents','tax_cents','total_cents'] as $k)$i[$k]=(int)$i[$k];
        $i['items']=json_decode($i['items_json'],true);unset($i['items_json']);
        $i['paid_cents']=$paid[$i['id']]??0;$i['balance_cents']=$i['kind']==='credit'||isset($credited[$i['id']])?0:max(0,$i['total_cents']-$i['paid_cents']);
        $i['status']=$i['kind']==='credit'||isset($credited[$i['id']])?'gutgeschrieben':($i['balance_cents']===0?'bezahlt':($i['paid_cents']>0?'teilweise':'offen'));
        $i['refund_due_cents']=isset($credited[$i['id']])?$i['paid_cents']:0;
        $byReservation[$i['reservation_id']][]=['id'=>$i['id'],'number'=>$i['number'],'status'=>$i['status'],'kind'=>$i['kind']];
        if($matches($i)){$summary['outstanding_cents']+=$i['balance_cents'];$summary['refund_due_cents']+=$i['refund_due_cents'];if($i['due_date']<date('Y-m-d'))$summary['overdue_cents']+=$i['balance_cents'];if($inPeriod($i['issue_date']))$summary['invoiced_cents']+=$i['total_cents'];}
    }unset($i);
    $customers=[];$filteredReservations=[];
    foreach($reservations as $r){
        $r['id']=(int)$r['id'];$r['invoices']=$byReservation[$r['id']]??[];$r['customer_key']=financeCustomerKey($r);
        if(!$matches($r))continue;
        $key=$r['customer_key'];$customers[$key]??=['key'=>$key,'customer_name'=>$r['customer_name'],'email'=>$r['email'],'invoiced_cents'=>0,'paid_cents'=>0,'reservation_count'=>0,'reservations'=>[]];
        $customers[$key]['reservations'][]=$r;$customers[$key]['reservation_count']++;
        if($r['end_date']>=$from&&$r['start_date']<=$to){$filteredReservations[]=$r;if($r['status']==='abgeschlossen')$summary['completed_orders']++;}
    }
    $filteredInvoices=[];foreach($invoices as $i){if(!$matches($i))continue;if(!isset($customers[$i['customer_key']]))$customers[$i['customer_key']]=['key'=>$i['customer_key'],'customer_name'=>$i['customer_name'],'email'=>$i['email'],'invoiced_cents'=>0,'paid_cents'=>0,'reservation_count'=>0,'reservations'=>[]];if($inPeriod($i['issue_date'])){$filteredInvoices[]=$i;$customers[$i['customer_key']]['invoiced_cents']+=$i['total_cents'];}}
    $filteredPayments=[];foreach($payments as $p){if($matches($p)&&$inPeriod($p['date'])){$filteredPayments[]=$p;$summary['received_cents']+=$p['amount_cents'];if(isset($customers[$p['customer_key']]))$customers[$p['customer_key']]['paid_cents']+=$p['amount_cents'];}}
    $q=$db->prepare('SELECT id,supplier,description,amount_cents,date,category,payment_method,receipt_blob IS NOT NULL AS has_receipt,created_at FROM fin_expenses WHERE date BETWEEN ? AND ? ORDER BY date DESC,id DESC');$q->execute([$from,$to]);$expenses=$q->fetchAll();
    foreach($expenses as &$e){$e['id']=(int)$e['id'];$e['amount_cents']=(int)$e['amount_cents'];$e['has_receipt']=(bool)$e['has_receipt'];$summary['expenses_cents']+=$e['amount_cents'];}unset($e);
    $protocols=[];try{$protocols=$db->query('SELECT p.id,p.reservation_id,p.type,p.status,p.created_at,p.finalized_at,r.customer_name,r.email,r.user_id,r.machine_name FROM fin_protocols p JOIN reservations r ON r.id=p.reservation_id ORDER BY p.id DESC')->fetchAll();$protocols=array_values(array_filter($protocols,fn($p)=>$matches($p)));}catch(PDOException $e){if($e->getCode()!=='42S02')throw $e;}
    $deliveries=$db->query('SELECT d.id,d.invoice_id,d.recipient,d.status,d.created_at,i.number AS invoice_number FROM fin_deliveries d JOIN fin_invoices i ON i.id=d.invoice_id ORDER BY d.id DESC LIMIT 500')->fetchAll();
    return ['deliveries'=>$deliveries,'settings'=>financeSettings($db),'reservations'=>$filteredReservations,'invoices'=>$filteredInvoices,'payments'=>$filteredPayments,'expenses'=>$expenses,'customers'=>array_values($customers),'summary'=>$summary,'protocols'=>$protocols,'from'=>$from,'to'=>$to,'all_invoices'=>array_values(array_filter($invoices,$matches))];
}
