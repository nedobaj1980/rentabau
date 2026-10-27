<?php
declare(strict_types=1);
require_once __DIR__.'/finance.php';
function financeSendCopy(PDO $db,int $invoiceId,string $recipient): void {
    $from=getenv('RENTABAU_FINANCE_MAIL_FROM')?:'';
    if(!filter_var($from,FILTER_VALIDATE_EMAIL))throw new RuntimeException('E-Mail-Versand ist noch nicht eingerichtet. Bitte die Rechnungskopie als PDF herunterladen.');
    if(!filter_var($recipient,FILTER_VALIDATE_EMAIL)||preg_match('/[\r\n]/',$recipient.$from))throw new InvalidArgumentException('Ungültige Empfängeradresse.');
    $invoice=financeInvoice($db,$invoiceId);
    if(!hash_equals($invoice['pdf_sha256'],hash('sha256',$invoice['pdf_blob'])))throw new RuntimeException('Archivbeleg konnte nicht geprüft werden.');
    $boundary='fin_'.bin2hex(random_bytes(18));$number=$invoice['number'];
    $subject='Rechnungskopie '.$number;
    $headers=['From'=>$from,'MIME-Version'=>'1.0','Content-Type'=>'multipart/mixed; boundary="'.$boundary.'"'];
    $body='--'.$boundary."\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode("Guten Tag\n\nAnbei die angeforderte Kopie des Belegs ".$number.".\n\nFreundliche Grüsse\n".json_decode($invoice['seller_json'],true)['company_name']))."\r\n--".$boundary."\r\nContent-Type: application/pdf; name=\"".$number.".pdf\"\r\nContent-Disposition: attachment; filename=\"".$number.".pdf\"\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($invoice['pdf_blob']))."\r\n--".$boundary."--\r\n";
    $db->prepare('INSERT INTO fin_deliveries(invoice_id,recipient,status) VALUES(?,?,?)')->execute([$invoiceId,$recipient,'pending']);$id=(int)$db->lastInsertId();
    $ok=false;try{$ok=mail($recipient,$subject,$body,$headers);}finally{$db->prepare('UPDATE fin_deliveries SET status=? WHERE id=?')->execute([$ok?'accepted':'failed',$id]);financeEvent($db,'copy_delivery','invoice',$invoiceId,['recipient'=>$recipient,'status'=>$ok?'accepted':'failed']);}
    if(!$ok)throw new RuntimeException('Der Mailserver hat den Versand nicht angenommen. Die PDF-Kopie kann heruntergeladen werden.');
}
