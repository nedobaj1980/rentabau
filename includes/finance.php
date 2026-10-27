<?php
declare(strict_types=1);
require_once __DIR__.'/../config/database.php';
function financeDb(): PDO { return databaseConnection(); }
function financeReady(PDO $db): bool {
    try {$db->query('SELECT id FROM fin_settings LIMIT 1');return true;}catch(PDOException $e){if($e->getCode()==='42S02')return false;throw $e;}
}
function financeSetup(PDO $db): void {
    foreach (['006_finance.sql','007_finance_protocols.sql'] as $name) {
        $path=__DIR__.'/../database/migrations/'.$name;
        if(!is_file($path))throw new RuntimeException('Installation noch unvollständig.');
        $sql=preg_replace('/^\xEF\xBB\xBF/','',file_get_contents($path));
        $db->exec($sql);
    }
}
function financeEvent(PDO $db,string $action,string $entity,int $id,array $details=[]): void {
    $user=function_exists('currentUser')?currentUser():null;
    $details['actor_id']=$user['id']??null;$details['actor_name']=$user['name']??'System';
    $db->prepare('INSERT INTO fin_events(action,entity,entity_id,details_json) VALUES(?,?,?,?)')->execute([$action,$entity,$id,json_encode($details,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)]);
}
function financeText(mixed $value,int $max=255,bool $required=true): string {
    if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('Ungültiger Text.');
    $text=trim((string)$value);
    if(($required&&$text==='')||mb_strlen($text)>$max||str_contains($text,"\0"))throw new InvalidArgumentException('Bitte Textfelder vollständig und in erlaubter Länge ausfüllen.');
    return $text;
}
function financeMoney(mixed $value): int {
    if(!is_string($value)&&!is_int($value)&&!is_float($value))throw new InvalidArgumentException('Ungültiger Betrag.');
    $value=trim((string)$value);
    if(!preg_match('/^\d{1,8}(?:[.,]\d{1,2})?$/D',$value))throw new InvalidArgumentException('Betrag mit höchstens zwei Nachkommastellen eingeben.');
    $parts=explode('.',str_replace(',','.',$value));
    return ((int)$parts[0])*100+(int)str_pad($parts[1]??'',2,'0');
}
function financeDate(string $value): string {
    $d=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
    if(!$d||$d->format('Y-m-d')!==$value)throw new InvalidArgumentException('Ungültiges Datum.');
    return $value;
}
function financeCsvCell(mixed $value): string {
    if(is_int($value)||(is_float($value)&&is_finite($value)))return (string)$value;
    $s=(string)($value??'');return preg_match('/^[\s]*[=+@-]|^[\t\r\n]/u',$s)?"'".$s:$s;
}
function financeItems(array $rows): array {
    if(count($rows)<1||count($rows)>50)throw new InvalidArgumentException('Bitte 1 bis 50 Rechnungspositionen erfassen.');
    $items=[];$net=0;$tax=0;
    foreach($rows as $row){
        if(!is_array($row))throw new InvalidArgumentException('Ungültige Rechnungsposition.');
        $description=financeText($row['description']??'',500);
        $quantity=financeMoney($row['quantity']??'');
        if($quantity<1||$quantity>1000000)throw new InvalidArgumentException('Menge muss zwischen 0.01 und 10000 liegen.');
        $unit=financeMoney($row['unit_price']??'');$rate=financeMoney($row['tax_rate']??'0');
        if($rate>10000)throw new InvalidArgumentException('Ungültiger Steuersatz.');
        $lineNet=intdiv($unit*$quantity+50,100);$lineTax=intdiv($lineNet*$rate+5000,10000);
        $net+=$lineNet;$tax+=$lineTax;
        if($net+$tax>9999999999)throw new InvalidArgumentException('Rechnungsbetrag zu hoch.');
        $items[]=['description'=>$description,'quantity'=>$quantity/100,'unit_price'=>$unit/100,'unit_cents'=>$unit,'tax_rate'=>$rate/100,'net_cents'=>$lineNet,'tax_cents'=>$lineTax,'total_cents'=>$lineNet+$lineTax];
    }
    if($net+$tax<=0)throw new InvalidArgumentException('Rechnungsbetrag muss positiv sein.');
    return ['items'=>$items,'net_cents'=>$net,'tax_cents'=>$tax,'total_cents'=>$net+$tax];
}
function financeSettings(PDO $db): array {
    $json=$db->query('SELECT settings_json FROM fin_settings WHERE id=1')->fetchColumn();
    $settings=array_merge(['company_name'=>'Sdd GmbH','address'=>'','email'=>'','iban'=>'','tax_number'=>''], $json?json_decode($json,true,512,JSON_THROW_ON_ERROR):[]);
    $settings['mail_ready']=filter_var(getenv('RENTABAU_FINANCE_MAIL_FROM')?:'',FILTER_VALIDATE_EMAIL)!==false;
    return $settings;
}
function financeSaveSettings(PDO $db,array $input): void {
    $s=[];foreach(['company_name'=>255,'address'=>1000,'email'=>255,'iban'=>50,'tax_number'=>80] as $key=>$max)$s[$key]=financeText($input[$key]??'',$max,in_array($key,['company_name','address','email'],true));
    if(!filter_var($s['email'],FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Bitte gültige Firmen-E-Mail eingeben.');
    $db->beginTransaction();try{
        $db->prepare('INSERT INTO fin_settings(id,settings_json) VALUES(1,?) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json)')->execute([json_encode($s,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)]);
        financeEvent($db,'settings','settings',1,$s);$db->commit();
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function financeNumber(PDO $db,string $kind): string {
    $prefix=$kind==='credit'?'GS':'RE';$key=$prefix.'-'.date('Y');
    $db->prepare('INSERT IGNORE INTO fin_sequences(sequence_key,last_number) VALUES(?,0)')->execute([$key]);
    $q=$db->prepare('SELECT last_number FROM fin_sequences WHERE sequence_key=? FOR UPDATE');$q->execute([$key]);$next=(int)$q->fetchColumn()+1;
    $db->prepare('UPDATE fin_sequences SET last_number=? WHERE sequence_key=?')->execute([$next,$key]);
    return $key.'-'.str_pad((string)$next,5,'0',STR_PAD_LEFT);
}
function financeCustomerKey(array $r): string {return !empty($r['user_id'])?'user:'.$r['user_id']:'email:'.mb_strtolower(trim($r['email']));}
function financeInvoice(PDO $db,int $id,bool $lock=false): array {
    $q=$db->prepare('SELECT * FROM fin_invoices WHERE id=?'.($lock?' FOR UPDATE':''));$q->execute([$id]);$r=$q->fetch();
    if(!$r)throw new InvalidArgumentException('Rechnung nicht gefunden.');return $r;
}
function financePaid(PDO $db,int $id): int {$q=$db->prepare('SELECT COALESCE(SUM(amount_cents),0) FROM fin_payments WHERE invoice_id=?');$q->execute([$id]);return (int)$q->fetchColumn();}
function financeCredited(PDO $db,int $id): bool {$q=$db->prepare('SELECT id FROM fin_invoices WHERE original_invoice_id=?');$q->execute([$id]);return (bool)$q->fetchColumn();}
function financeInsertInvoice(PDO $db,array $record): int {
    require_once __DIR__.'/finance-pdf.php';
    $pdf=financeInvoicePdf($record);
    $q=$db->prepare('INSERT INTO fin_invoices(number,reservation_id,source_key,kind,original_invoice_id,customer_key,customer_name,email,customer_address,issue_date,due_date,net_cents,tax_cents,total_cents,items_json,seller_json,reservation_json,notes,pdf_blob,pdf_sha256) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute([$record['number'],$record['reservation_id'],$record['source_key'],$record['kind'],$record['original_invoice_id'],$record['customer_key'],$record['customer_name'],$record['email'],$record['customer_address'],$record['issue_date'],$record['due_date'],$record['net_cents'],$record['tax_cents'],$record['total_cents'],json_encode($record['items'],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE),json_encode($record['seller'],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE),json_encode($record['reservation'],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE),$record['notes'],$pdf,hash('sha256',$pdf)]);
    return (int)$db->lastInsertId();
}
function financeIssue(PDO $db,array $input): int {
    $totals=financeItems($input['items']??[]);$due=financeDate((string)($input['due_date']??''));
    if($due<date('Y-m-d'))throw new InvalidArgumentException('Fälligkeit darf nicht vor dem Rechnungsdatum liegen.');
    $address=financeText($input['customer_address']??'',1000);$notes=financeText($input['notes']??'',3000,false);
    $seller=financeSettings($db);foreach(['company_name','address','email'] as $key)if(trim($seller[$key])==='')throw new InvalidArgumentException('Bitte zuerst Firmenangaben unter Einstellungen vervollständigen.');
    if($totals['tax_cents']>0&&$seller['tax_number']==='')throw new InvalidArgumentException('Für Rechnungen mit MWST bitte zuerst die MWST-Nummer hinterlegen.');
    $db->beginTransaction();try{
        $q=$db->prepare('SELECT * FROM reservations WHERE id=? FOR UPDATE');$q->execute([(int)($input['reservation_id']??0)]);$r=$q->fetch();
        if(!$r||$r['status']==='storniert')throw new InvalidArgumentException('Reservation fehlt oder ist storniert.');
        $q=$db->prepare('SELECT id FROM fin_invoices WHERE source_key=?');$q->execute(['reservation:'.$r['id']]);
        if($q->fetchColumn())throw new InvalidArgumentException('Für diese Reservation existiert bereits eine Rechnung.');
        $record=$totals+['number'=>financeNumber($db,'invoice'),'reservation_id'=>$r['id'],'source_key'=>'reservation:'.$r['id'],'kind'=>'invoice','original_invoice_id'=>null,'customer_key'=>financeCustomerKey($r),'customer_name'=>$r['customer_name'],'email'=>$r['email'],'customer_address'=>$address,'issue_date'=>date('Y-m-d'),'due_date'=>$due,'seller'=>$seller,'reservation'=>$r,'notes'=>$notes];
        $id=financeInsertInvoice($db,$record);financeEvent($db,'issue','invoice',$id,['number'=>$record['number'],'reservation_id'=>$r['id'],'total_cents'=>$totals['total_cents']]);$db->commit();return $id;
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function financeCredit(PDO $db,int $id,string $reason): int {
    $reason=financeText($reason,1000);$db->beginTransaction();try{
        $r=financeInvoice($db,$id,true);if($r['kind']!=='invoice'||financeCredited($db,$id))throw new InvalidArgumentException('Diese Rechnung kann nicht erneut gutgeschrieben werden.');
        $record=$r;unset($record['id']);
        $record['number']=financeNumber($db,'credit');$record['kind']='credit';$record['source_key']=null;$record['original_invoice_id']=$id;
        $record['issue_date']=date('Y-m-d');$record['due_date']=date('Y-m-d');$record['notes']='Gutschrift zu '.$r['number'].': '.$reason;
        $record['seller']=json_decode($r['seller_json'],true);$record['reservation']=json_decode($r['reservation_json'],true);$record['items']=json_decode($r['items_json'],true);
        foreach(['net_cents','tax_cents','total_cents'] as $key)$record[$key]=-(int)$r[$key];
        foreach($record['items'] as &$item)foreach(['net_cents','tax_cents','total_cents'] as $key)$item[$key]=-$item[$key];unset($item);
        $credit=financeInsertInvoice($db,$record);financeEvent($db,'credit','invoice',$credit,['original_invoice_id'=>$id,'reason'=>$reason]);$db->commit();return $credit;
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function financeRequestKey(array $input): ?string {
    $key=trim((string)($input['request_key']??''));if($key==='')return null;
    if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key))throw new InvalidArgumentException('Ungültige Anfragekennung.');return $key;
}
function financePayment(PDO $db,array $input,bool $refund=false): int {
    $amount=financeMoney($input['amount']??'');if($amount<1)throw new InvalidArgumentException('Betrag muss positiv sein.');
    $date=financeDate((string)($input['date']??''));if($date>date('Y-m-d'))throw new InvalidArgumentException('Zahlungsdatum darf nicht in der Zukunft liegen.');
    $ref=financeText($input['reference']??'',250,false);$requestKey=financeRequestKey($input);
    $db->beginTransaction();try{
        $original=null;$invoiceId=(int)($input['invoice_id']??0);
        if($refund){$q=$db->prepare('SELECT * FROM fin_payments WHERE id=?');$q->execute([(int)($input['payment_id']??0)]);$original=$q->fetch();if(!$original||$original['amount_cents']<=0)throw new InvalidArgumentException('Zahlung nicht gefunden.');$invoiceId=(int)$original['invoice_id'];}
        $invoice=financeInvoice($db,$invoiceId,true);
        if($requestKey){$q=$db->prepare('SELECT * FROM fin_payments WHERE request_key=?');$q->execute([$requestKey]);if($existing=$q->fetch()){if((int)$existing['invoice_id']!==$invoiceId||(int)$existing['amount_cents']!==($refund?-$amount:$amount)||$existing['date']!==$date||$existing['reference']!==$ref||(int)($existing['refund_of']??0)!==(int)($original['id']??0)||(!$refund&&$existing['method']!==($input['method']??'')))throw new InvalidArgumentException('Anfragekennung wurde bereits für eine andere Zahlung verwendet.');$db->commit();return (int)$existing['id'];}}
        if($invoice['kind']!=='invoice')throw new InvalidArgumentException('Zahlungen nur auf Rechnungen buchen.');
        if($date<$invoice['issue_date'])throw new InvalidArgumentException('Zahlungsdatum darf nicht vor dem Rechnungsdatum liegen.');
        if($refund){
            if($date<$original['date'])throw new InvalidArgumentException('Erstattung darf nicht vor dem Zahlungseingang liegen.');
            $q=$db->prepare('SELECT COALESCE(SUM(amount_cents),0) FROM fin_payments WHERE refund_of=?');$q->execute([$original['id']]);
            if($amount>(int)$original['amount_cents']+(int)$q->fetchColumn())throw new InvalidArgumentException('Erstattung übersteigt die verbleibende Zahlung.');
            $method=$original['method'];$amount=-$amount;
        }else{
            if(financeCredited($db,$invoiceId)||$amount>(int)$invoice['total_cents']-financePaid($db,$invoiceId))throw new InvalidArgumentException('Betrag übersteigt die offene Rechnung oder Rechnung wurde gutgeschrieben.');
            $method=financeText($input['method']??'',50);
            if(!in_array($method,['bank','cash','twint','card','other'],true))throw new InvalidArgumentException('Bitte gültige Zahlungsart wählen.');
        }
        $db->prepare('INSERT INTO fin_payments(invoice_id,refund_of,amount_cents,date,method,reference,request_key) VALUES(?,?,?,?,?,?,?)')->execute([$invoiceId,$original['id']??null,$amount,$date,$method,$ref,$requestKey]);
        $id=(int)$db->lastInsertId();financeEvent($db,$refund?'refund':'payment','payment',$id,['invoice_id'=>$invoiceId,'amount_cents'=>$amount,'date'=>$date]);
        $paid=financePaid($db,$invoiceId);$state=$paid<=0?($refund?'zurueckerstattet':'offen'):($paid>=(int)$invoice['total_cents']?'bezahlt':'teilweise');
        $db->prepare('UPDATE reservations SET payment_status=?,paid_at=? WHERE id=?')->execute([$state,$state==='bezahlt'?$date.' 00:00:00':null,$invoice['reservation_id']]);
        $db->commit();return $id;
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function financeExpense(PDO $db,array $input,?array $receipt=null): int {
    $supplier=financeText($input['supplier']??'');$description=financeText($input['description']??'',2000);$amount=financeMoney($input['amount']??'');
    if($amount<1)throw new InvalidArgumentException('Ausgabe muss positiv sein.');$date=financeDate((string)($input['date']??''));
    if($date>date('Y-m-d'))throw new InvalidArgumentException('Ausgabedatum darf nicht in der Zukunft liegen.');
    $category=financeText($input['category']??'',100);$method=financeText($input['payment_method']??'',50);$key=financeRequestKey($input);
    $db->beginTransaction();try{
        if($key){$q=$db->prepare('SELECT * FROM fin_expenses WHERE request_key=?');$q->execute([$key]);if($existing=$q->fetch()){if($existing['supplier']!==$supplier||$existing['description']!==$description||(int)$existing['amount_cents']!==$amount||$existing['date']!==$date||$existing['category']!==$category||$existing['payment_method']!==$method||$existing['receipt_blob']!==($receipt['bytes']??null))throw new InvalidArgumentException('Anfragekennung wurde bereits für eine andere Ausgabe verwendet.');$db->commit();return (int)$existing['id'];}}
        $db->prepare('INSERT INTO fin_expenses(supplier,description,amount_cents,date,category,payment_method,receipt_blob,receipt_mime,receipt_name,request_key) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$supplier,$description,$amount,$date,$category,$method,$receipt['bytes']??null,$receipt['mime']??null,$receipt['name']??null,$key]);
        $id=(int)$db->lastInsertId();financeEvent($db,'expense','expense',$id,['amount_cents'=>$amount,'supplier'=>$supplier]);$db->commit();return $id;
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
