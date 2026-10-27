<?php
require_once __DIR__.'/../includes/finance-protocols.php';
require_once __DIR__.'/../includes/finance-export.php';
function checkProtocol($ok,$why){if(!$ok)throw new RuntimeException($why);}
function rejectsProtocol(callable $fn){try{$fn();throw new RuntimeException('Expected rejection');}catch(InvalidArgumentException $expected){}}
$db=financeDb();$db->exec('USE rentabau_finance_ui_test');
$rid=(int)json_decode(file_get_contents(__DIR__.'/finance-fixture-state.json'),true)['reservation_id'];
$final=$db->query("SELECT id FROM fin_protocols WHERE status='final' ORDER BY id DESC LIMIT 1")->fetchColumn();checkProtocol($final,'Browser final exists');
$original=financeProtocolPdf($db,(int)$final);$detail=financeProtocolDetail($db,(int)$final);
rejectsProtocol(fn()=>financeProtocolSave($db,(int)$final,(int)$detail['revision'],$detail['data']));
$draft=financeProtocolCreate($db,['reservation_id'=>$rid,'type'=>'return']);
$saved=financeProtocolSave($db,(int)$draft['id'],(int)$draft['revision'],array_replace($draft['data'],['operator_name'=>'Tester']));
rejectsProtocol(fn()=>financeProtocolSave($db,(int)$draft['id'],(int)$draft['revision'],$draft['data']));
$im=imagecreatetruecolor(240,120);imagefill($im,0,0,imagecolorallocate($im,255,255,255));ob_start();imagepng($im);$blank=ob_get_clean();imagedestroy($im);
$url='data:image/png;base64,'.base64_encode($blank);rejectsProtocol(fn()=>financeProtocolFinalize($db,(int)$draft['id'],(int)$saved['revision'],$url,$url));
checkProtocol(financeProtocolRow($db,(int)$draft['id'])['status']==='draft','Blank signature rollback');
$correction=financeProtocolCreate($db,['reservation_id'=>$detail['reservation_id'],'type'=>$detail['type'],'supersedes_id'=>$final]);checkProtocol((int)$correction['supersedes_id']===(int)$final,'Correction linked');checkProtocol($original===financeProtocolPdf($db,(int)$final),'Original unchanged');
$zip=financeExport($db,'2026-01-01','2026-12-31');file_put_contents(__DIR__.'/finance-full-export.zip',$zip);checkProtocol(str_starts_with($zip,'PK'),'Export ZIP');
echo "PASS final immutability, revision conflict, blank signature rollback, correction link and full export\n";
