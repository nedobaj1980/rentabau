<?php
declare(strict_types=1);
require_once __DIR__.'/../vendor/fpdf/fpdf.php';
function financePdfText(string $text): string {return iconv('UTF-8','Windows-1252//TRANSLIT',$text)?:$text;}
class FinancePdf extends FPDF {
    public function Footer() {
        $this->SetY(-14);$this->SetFont('Helvetica','',8);$this->SetTextColor(110,110,110);
        $this->Cell(0,6,financePdfText('Rent A Bau | Seite '.$this->PageNo().' / {nb}'),0,0,'R');
    }
}
function financeNewPdf(): FPDF {
    $pdf=new FinancePdf('P','mm','A4');$pdf->SetMargins(16,16,16);$pdf->SetAutoPageBreak(true,22);$pdf->AliasNbPages();$pdf->SetFont('Helvetica','',10);return $pdf;
}
function financeInvoicePdf(array $invoice): string {
    $p=financeNewPdf();$p->SetTitle($invoice['number'],true);$p->SetAuthor($invoice['seller']['company_name'],true);$p->AddPage();
    $seller=$invoice['seller'];$p->SetTextColor(235,76,20);$p->SetFont('Helvetica','B',22);$p->MultiCell(178,10,financePdfText($seller['company_name']));
    $p->SetTextColor(45,45,45);$p->SetFont('Helvetica','',10);$p->MultiCell(178,5,financePdfText($seller['address']."\n".$seller['email']));
    if($seller['tax_number']!=='')$p->MultiCell(178,5,financePdfText('MWST-Nr.: '.$seller['tax_number']));
    $p->Ln(10);$p->SetFont('Helvetica','B',18);$p->MultiCell(178,9,financePdfText(($invoice['kind']==='credit'?'Gutschrift ':'Rechnung ').$invoice['number']));
    $p->SetFont('Helvetica','',10);$p->MultiCell(178,6,financePdfText('Datum: '.$invoice['issue_date'].($invoice['kind']==='invoice'?'  |  Zahlbar bis: '.$invoice['due_date']:'')));
    $p->Ln(5);$p->SetFont('Helvetica','B',11);$p->MultiCell(178,6,financePdfText($invoice['customer_name']));$p->SetFont('Helvetica','',10);$p->MultiCell(178,5,financePdfText($invoice['customer_address']));
    $r=$invoice['reservation'];$p->Ln(6);$p->MultiCell(178,6,financePdfText('Auftrag #'.$invoice['reservation_id'].' | '.$r['machine_name']."\nMietzeitraum: ".$r['start_date'].' bis '.$r['end_date']));
    $p->Ln(5);$p->SetFillColor(244,244,242);$p->SetFont('Helvetica','B',10);$p->Cell(178,8,financePdfText('  Leistungen (CHF)'),0,1,'L',true);$p->SetFont('Helvetica','',10);
    foreach($invoice['items'] as $i=>$item){
        if($p->GetY()>245)$p->AddPage();
        $p->Ln(3);$p->SetFont('Helvetica','B',10);$p->MultiCell(178,5,financePdfText(($i+1).'. '.$item['description']));$p->SetFont('Helvetica','',9);
        $line='Menge '.$item['quantity'].'  |  Einzelpreis netto '.number_format($item['unit_cents']/100,2,'.',"'").'  |  MWST '.$item['tax_rate'].' %  |  Total '.number_format($item['total_cents']/100,2,'.',"'");
        $p->MultiCell(178,6,financePdfText($line));$p->SetDrawColor(220,220,220);$p->Line(16,$p->GetY()+1,194,$p->GetY()+1);
    }
    if($p->GetY()>220)$p->AddPage();$p->Ln(8);
    foreach(['net_cents'=>'Netto','tax_cents'=>'MWST','total_cents'=>'Gesamtbetrag CHF'] as $key=>$label){$p->SetFont('Helvetica',$key==='total_cents'?'B':'',$key==='total_cents'?13:10);$p->Cell(120,8,financePdfText($label));$p->Cell(58,8,number_format($invoice[$key]/100,2,'.',"'"),0,1,'R');}
    $rates=[];foreach($invoice['items'] as $item){$rate=(string)$item['tax_rate'];$rates[$rate]??=['net'=>0,'tax'=>0];$rates[$rate]['net']+=$item['net_cents'];$rates[$rate]['tax']+=$item['tax_cents'];}
    $p->SetFont('Helvetica','',9);foreach($rates as $rate=>$total)$p->MultiCell(178,5,financePdfText('MWST '.$rate.' %: Basis CHF '.number_format($total['net']/100,2,'.',"'").', Steuer CHF '.number_format($total['tax']/100,2,'.',"'")));
    $p->Ln(7);$p->SetFont('Helvetica','',10);
    if($invoice['kind']==='invoice'&&$seller['iban']!=='')$p->MultiCell(178,6,financePdfText('Bankverbindung: '.$seller['iban']."\nZahlungsreferenz: ".$invoice['number']));
    if($invoice['notes']!==''){$p->Ln(5);$p->MultiCell(178,6,financePdfText($invoice['notes']));}
    return $p->Output('S');
}
