<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/bootstrap.php';requireAdmin();
require_once __DIR__.'/../includes/finance.php';
header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');
try {
    $db=financeDb();
    if(isset($_GET['expense_id'])){
        $q=$db->prepare('SELECT receipt_blob,receipt_mime FROM fin_expenses WHERE id=?');$q->execute([(int)$_GET['expense_id']]);$r=$q->fetch();
        if(!$r||$r['receipt_blob']===null)throw new InvalidArgumentException('Beleg nicht gefunden.');
        $bytes=$r['receipt_blob'];$mime=$r['receipt_mime'];$ext=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'][$mime]??'bin';$name='Ausgabenbeleg-'.(int)$_GET['expense_id'].'.'.$ext;
    }else{
        $r=financeInvoice($db,(int)($_GET['id']??0));$bytes=$r['pdf_blob'];$mime='application/pdf';$name=$r['number'].'.pdf';
        if(!hash_equals($r['pdf_sha256'],hash('sha256',$bytes)))throw new RuntimeException('Belegprüfung fehlgeschlagen.');
    }
    header('Content-Type: '.$mime);header('Content-Disposition: attachment; filename="'.$name.'"');header('Content-Length: '.strlen($bytes));echo $bytes;
}catch(InvalidArgumentException $e){http_response_code(404);echo 'Beleg nicht gefunden.';}
catch(Throwable $e){error_log('Finance document: '.$e->getMessage());http_response_code(503);echo 'Beleg kann momentan nicht geladen werden.';}
