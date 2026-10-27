<?php
require_once __DIR__.'/../includes/finance.php';
function finCheck($ok,$why){if(!$ok)throw new RuntimeException($why);}
finCheck(financeMoney('12.35')===1235,'Decimal money');
finCheck(financeMoney('0.01')===1,'One Rappen');
foreach (['-1','1.001','nan','1e4','1000000000'] as $bad) {
 try {financeMoney($bad);throw new RuntimeException('Invalid amount accepted');}catch(InvalidArgumentException $expected){}
}
$items=financeItems([['description'=>'Miete','quantity'=>'2.5','unit_price'=>'12.35','tax_rate'=>'8.1']]);
finCheck($items['net_cents']===3088,'Line rounding');
finCheck($items['tax_cents']===250,'Tax rounding');
finCheck($items['total_cents']===3338,'Gross amount');
foreach (['2026-02-30','yesterday'] as $bad){try{financeDate($bad);throw new RuntimeException('Invalid date');}catch(InvalidArgumentException $expected){}}
finCheck(financeCsvCell('=1+2')==="'=1+2",'CSV formula protection');
finCheck(financeCsvCell(-1200)==='-1200','Signed numeric money remains numeric in CSV');
echo "PASS finance money, totals, dates and CSV safety\n";
