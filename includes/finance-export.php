<?php
declare(strict_types=1);
require_once __DIR__.'/finance-report.php';
function financeCsv(array $rows,array $columns): string {
    $s=fopen('php://temp','w+');fwrite($s,"\xEF\xBB\xBF");fputcsv($s,array_values($columns),';','"','');
    foreach($rows as $row)fputcsv($s,array_map(fn($key)=>financeCsvCell($row[$key]??''),array_keys($columns)),';','"','');rewind($s);$text=stream_get_contents($s);fclose($s);return $text;
}
/** Store-only ZIP: no optional server extension required; names are application-generated. */
function financeZip(array $entries): string {
    $body='';$directory='';$offset=0;$count=0;
    foreach($entries as $name=>$bytes){
        if(!preg_match('~^[a-zA-Z0-9_./-]+$~D',$name)||str_contains($name,'..'))throw new RuntimeException('Ungültiger Exportname.');
        $size=strlen($bytes);if(strlen($body)+$size>24*1024*1024)throw new RuntimeException('Export über 24 MB. Bitte einen kürzeren Zeitraum wählen.');
        $crc=crc32($bytes);$nameLength=strlen($name);
        $entry=pack('VvvvvvVVVvv',0x04034b50,20,0,0,0,33,$crc,$size,$size,$nameLength,0).$name.$bytes;
        $directory.=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0,0,0,33,$crc,$size,$size,$nameLength,0,0,0,0,0,$offset).$name;
        $body.=$entry;$offset+=strlen($entry);$count++;
    }
    return $body.$directory.pack('VvvvvVVv',0x06054b50,0,0,$count,$count,strlen($directory),strlen($body),0);
}
function financeExport(PDO $db,string $from,string $to): string {
    $from=financeDate($from);$to=financeDate($to);if($from>$to)throw new InvalidArgumentException('Ungültiger Zeitraum.');
    $db->beginTransaction();try{
        $report=financeReport($db,['from'=>$from,'to'=>$to]);
        $entries=[];$totalBytes=0;
        $add=function(string $name,string $bytes)use(&$entries,&$totalBytes){$totalBytes+=strlen($bytes);if($totalBytes>24*1024*1024)throw new RuntimeException('Export über 24 MB. Bitte einen kürzeren Zeitraum wählen.');$entries[$name]=$bytes;};
        $add('Rechnungen.csv',financeCsv($report['invoices'],['number'=>'Belegnummer','kind'=>'Belegart','original_invoice_id'=>'Originalrechnung ID','reservation_id'=>'Auftrag','customer_name'=>'Kunde','email'=>'E-Mail','issue_date'=>'Datum','due_date'=>'Faelligkeit','net_cents'=>'Netto Rappen CHF','tax_cents'=>'MWST Rappen CHF','total_cents'=>'Brutto Rappen CHF','paid_cents'=>'Zahlungen aktuell Rappen CHF','balance_cents'=>'Offen aktuell Rappen CHF','status'=>'Status aktuell']));
        $add('Zahlungen.csv',financeCsv($report['payments'],['id'=>'Zahlung ID','invoice_number'=>'Rechnung','reservation_id'=>'Auftrag','customer_name'=>'Kunde','date'=>'Datum','amount_cents'=>'Betrag Rappen CHF','method'=>'Zahlungsart','reference'=>'Referenz','refund_of'=>'Erstattung zu Zahlung ID']));
        $add('Ausgaben.csv',financeCsv($report['expenses'],['id'=>'Ausgabe ID','supplier'=>'Lieferant','description'=>'Beschreibung','date'=>'Datum','category'=>'Kategorie','amount_cents'=>'Betrag Rappen CHF','payment_method'=>'Zahlungsart','has_receipt'=>'Beleg vorhanden']));
        $add('Auftraege.csv',financeCsv($report['reservations'],['id'=>'Auftrag','customer_name'=>'Kunde','email'=>'E-Mail','machine_name'=>'Maschine','start_date'=>'Mietbeginn','end_date'=>'Mietende','total_price'=>'Reservationsbetrag CHF','status'=>'Auftragsstatus','payment_status'=>'Reservationszahlungsstatus','payment_preference'=>'Zahlungswunsch']));
        $add('Offene-Posten-aktuell.csv',financeCsv(array_values(array_filter($report['all_invoices'],fn($r)=>$r['balance_cents']>0||$r['refund_due_cents']>0)),['number'=>'Belegnummer','customer_name'=>'Kunde','due_date'=>'Faelligkeit','balance_cents'=>'Offen aktuell Rappen CHF','refund_due_cents'=>'Zu erstatten Rappen CHF']));
        $q=$db->prepare('SELECT id,number,pdf_blob,pdf_sha256 FROM fin_invoices WHERE issue_date BETWEEN ? AND ? ORDER BY id');$q->execute([$from,$to]);
        while($r=$q->fetch()){if(!hash_equals($r['pdf_sha256'],hash('sha256',$r['pdf_blob'])))throw new RuntimeException('Archivbeleg konnte nicht geprüft werden.');$add('Rechnungen/'.$r['number'].'.pdf',$r['pdf_blob']);}
        // Also include originals linked to period payments/credits, even when issued earlier.
        $linked=[];foreach($report['payments'] as $p)$linked[(int)$p['invoice_id']]=true;foreach($report['invoices'] as $i)if($i['original_invoice_id'])$linked[(int)$i['original_invoice_id']]=true;
        foreach(array_keys($linked) as $id){$r=financeInvoice($db,$id);$path='Rechnungen/'.$r['number'].'.pdf';if(!isset($entries[$path])){if(!hash_equals($r['pdf_sha256'],hash('sha256',$r['pdf_blob'])))throw new RuntimeException('Archivbeleg konnte nicht geprüft werden.');$add($path,$r['pdf_blob']);}}
        $q=$db->prepare('SELECT id,receipt_blob,receipt_mime FROM fin_expenses WHERE date BETWEEN ? AND ? AND receipt_blob IS NOT NULL');$q->execute([$from,$to]);while($r=$q->fetch()){$ext=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'][$r['receipt_mime']]??'bin';$add('Ausgaben/Beleg-'.$r['id'].'.'.$ext,$r['receipt_blob']);}
        $protocols=[];
        if(is_file(__DIR__.'/finance-protocols.php')){
            require_once __DIR__.'/finance-protocols.php';
            $q=$db->prepare('SELECT id,reservation_id,type,status,finalized_at FROM fin_protocols WHERE finalized_at >= ? AND finalized_at < DATE_ADD(?, INTERVAL 1 DAY) ORDER BY id');$q->execute([$from,$to]);$protocols=$q->fetchAll();
            foreach($protocols as $p)$add('Protokolle/Protokoll-'.$p['id'].'.pdf',financeProtocolPdf($db,(int)$p['id']));
        }
        $add('Protokolle.csv',financeCsv($protocols,['id'=>'Protokoll','reservation_id'=>'Auftrag','type'=>'Typ','status'=>'Status','finalized_at'=>'Abgeschlossen am']));
        $q=$db->prepare('SELECT action,entity,entity_id,details_json,created_at FROM fin_events WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY) ORDER BY id');$q->execute([$from,$to]);
        $add('Journal.csv',financeCsv($q->fetchAll(),['created_at'=>'Zeitpunkt','action'=>'Aktion','entity'=>'Bereich','entity_id'=>'ID','details_json'=>'Details']));
        $q=$db->prepare('SELECT invoice_id,recipient,status,created_at FROM fin_deliveries WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)');$q->execute([$from,$to]);$add('Versand.csv',financeCsv($q->fetchAll(),['invoice_id'=>'Rechnung ID','recipient'=>'Empfaenger','status'=>'Mailserverstatus','created_at'=>'Zeitpunkt']));
        $add('Hinweise.txt',"Rent A Bau - Treuhand-Export\r\nZeitraum: $from bis $to\r\nErstellt: ".date('c')."\r\nRechnungen nach Belegdatum, Zahlungen nach Zahlungsdatum, Ausgaben nach Ausgabedatum.\r\nBetragsfelder mit Rappen im Titel sind ganze Rappen (100 = CHF 1.00).\r\nOffene Posten und Zahlungsstatus sind der aktuelle Stand zum Exportzeitpunkt, kein historischer Stichtagsabschluss.\r\nKundenumsatz basiert auf ausgestellten Rechnungen abzueglich Gutschriften; eingegangene Zahlungen sind separat.\r\nReservationszahlungsstatus sind manuelle Altangaben; sie erzeugen keine Zahlungseingaenge.\r\nRechnungspositionen sind Nettopreise plus explizit erfasster MWST.\r\nFotos und Unterschriften sind in abgeschlossenen Protokoll-PDF enthalten.\r\nBankabgleich, Kontenplan, Bilanz, Lohn und Steuerdeklaration sind nicht Bestandteil dieses Exports.\r\n");
        $db->commit();return financeZip($entries);
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
