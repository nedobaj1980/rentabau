<?php
declare(strict_types=1);
require_once __DIR__.'/finance.php';
function financeProtocolJson(array $value):string{return json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);}
function financeProtocolRow(PDO $db,int $id,bool $lock=false):array{
 $q=$db->prepare('SELECT * FROM fin_protocols WHERE id=?'.($lock?' FOR UPDATE':''));$q->execute([$id]);$r=$q->fetch();if(!$r)throw new InvalidArgumentException('Protokoll nicht gefunden.');return $r;
}
function financeProtocolReservation(PDO $db,int $id):array{
 $q=$db->prepare('SELECT id,machine_id,machine_name,customer_name,email,phone,start_date,end_date,total_price,days FROM reservations WHERE id=?');$q->execute([$id]);$r=$q->fetch();if(!$r)throw new InvalidArgumentException('Reservation nicht gefunden.');return $r;
}
function financeProtocolData(array $input):array{
 $result=[];foreach(['meter'=>100,'fuel'=>100,'accessories'=>4000,'damages'=>6000,'notes'=>6000,'operator_name'=>200,'customer_name'=>200] as $key=>$limit)$result[$key]=financeText($input[$key]??'',$limit,false);
 $result['date']=financeDate((string)($input['date']??date('Y-m-d')));$checklist=$input['checklist']??[];
 if(!is_array($checklist)||count($checklist)>40)throw new InvalidArgumentException('Ungültige Checkliste.');$result['checklist']=[];
 foreach($checklist as $item){if(!is_array($item))throw new InvalidArgumentException('Ungültiger Checklisten-Eintrag.');$state=$item['state']??'unchecked';if(!in_array($state,['unchecked','ok','defect','na'],true))throw new InvalidArgumentException('Ungültiger Prüfstatus.');$result['checklist'][]=['label'=>financeText($item['label']??'',200),'state'=>$state,'note'=>financeText($item['note']??'',500,false)];}
 return $result;
}
function financeProtocolMutable(array $row,int $revision):void{
 if($row['status']!=='draft')throw new InvalidArgumentException('Abgeschlossene Protokolle können nicht geändert werden.');
 if((int)$row['revision']!==$revision)throw new InvalidArgumentException('Das Protokoll wurde inzwischen geändert. Bitte neu laden.');
}
function financeProtocolTransaction(PDO $db,callable $work):mixed{
 $db->beginTransaction();try{$result=$work();$db->commit();return $result;}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
}
function financeProtocolDetail(PDO $db,int $id):array{
 $r=financeProtocolRow($db,$id);$r['data']=json_decode($r['data_json'],true,512,JSON_THROW_ON_ERROR);
 $r['reservation']=$r['snapshot_json']?json_decode($r['snapshot_json'],true,512,JSON_THROW_ON_ERROR)['reservation']:financeProtocolReservation($db,(int)$r['reservation_id']);
 unset($r['data_json'],$r['snapshot_json'],$r['pdf_blob']);$q=$db->prepare('SELECT id,kind,mime,width,height,sha256,created_at FROM fin_protocol_files WHERE protocol_id=? ORDER BY id');$q->execute([$id]);$r['files']=$q->fetchAll();
 $r['signatures']=[];$q=$db->prepare("SELECT kind,mime,image_blob FROM fin_protocol_files WHERE protocol_id=? AND kind<>'photo'");$q->execute([$id]);foreach($q->fetchAll() as $file)$r['signatures'][$file['kind']]='data:'.$file['mime'].';base64,'.base64_encode($file['image_blob']);return $r;
}
function financeProtocolList(PDO $db,?int $reservationId=null):array{
 $sql='SELECT id,reservation_id,type,status,revision,supersedes_id,created_at,updated_at,finalized_at FROM fin_protocols';$args=[];if($reservationId!==null){$sql.=' WHERE reservation_id=?';$args[]=$reservationId;}$q=$db->prepare($sql.' ORDER BY id DESC LIMIT 500');$q->execute($args);return $q->fetchAll();
}
function financeProtocolCreate(PDO $db,array $input):array{
 $rid=(int)($input['reservation_id']??0);$type=$input['type']??'';if(!in_array($type,['handover','return'],true))throw new InvalidArgumentException('Ungültiger Protokolltyp.');$r=financeProtocolReservation($db,$rid);$supersedes=(int)($input['supersedes_id']??0);
 $data=financeProtocolData(['customer_name'=>$r['customer_name']]);
 if($supersedes){$previous=financeProtocolRow($db,$supersedes);if($previous['status']!=='final'||(int)$previous['reservation_id']!==$rid||$previous['type']!==$type)throw new InvalidArgumentException('Korrektur muss ein abgeschlossenes Protokoll desselben Auftrags betreffen.');$data=json_decode($previous['data_json'],true);$data['date']=date('Y-m-d');}
 $id=financeProtocolTransaction($db,function()use($db,$rid,$type,$supersedes,$data){$db->prepare('INSERT INTO fin_protocols(reservation_id,type,supersedes_id,data_json) VALUES(?,?,?,?)')->execute([$rid,$type,$supersedes?:null,financeProtocolJson($data)]);$id=(int)$db->lastInsertId();financeEvent($db,'create','protocol',$id,['supersedes_id'=>$supersedes?:null]);return $id;});return financeProtocolDetail($db,$id);
}
function financeProtocolSave(PDO $db,int $id,int $revision,array $data):array{
 $data=financeProtocolData($data);financeProtocolTransaction($db,function()use($db,$id,$revision,$data){financeProtocolMutable(financeProtocolRow($db,$id,true),$revision);$db->prepare('UPDATE fin_protocols SET data_json=?,revision=revision+1,updated_at=NOW() WHERE id=?')->execute([financeProtocolJson($data),$id]);financeEvent($db,'save','protocol',$id,['revision'=>$revision+1]);});return financeProtocolDetail($db,$id);
}
function financeProtocolImage(string $bytes,bool $signature=false):array{
 if($bytes===''||strlen($bytes)>($signature?1048576:5242880))throw new InvalidArgumentException('Bild zu gross (Foto max. 5 MB, Unterschrift max. 1 MB).');
 $info=@getimagesizefromstring($bytes);if(!$info||!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG],true))throw new InvalidArgumentException('Bitte JPG oder PNG verwenden.');[$w,$h]=$info;
 if($w<1||$h<1||$w>12000||$h>12000||$w*$h>($signature?4000000:24000000))throw new InvalidArgumentException('Bildabmessungen zu gross.');if($signature&&$info[2]!==IMAGETYPE_PNG)throw new InvalidArgumentException('Unterschrift muss PNG sein.');
 if(!function_exists('imagecreatefromstring'))throw new RuntimeException('Bildverarbeitung auf dem Server fehlt.');$source=@imagecreatefromstring($bytes);if(!$source)throw new InvalidArgumentException('Bild nicht lesbar.');
 $scale=min(1,1800/max($w,$h));$outW=max(1,(int)round($w*$scale));$outH=max(1,(int)round($h*$scale));$target=imagecreatetruecolor($outW,$outH);
 try{imagefill($target,0,0,imagecolorallocate($target,255,255,255));imagealphablending($target,true);imagecopyresampled($target,$source,0,0,0,0,$outW,$outH,$w,$h);
  if($signature){$ink=0;$minX=$outW;$maxX=-1;$minY=$outH;$maxY=-1;for($y=0;$y<$outH;$y++)for($x=0;$x<$outW;$x++){$rgb=imagecolorat($target,$x,$y);if((($rgb>>16)&255)<210&&(($rgb>>8)&255)<210&&($rgb&255)<210){$ink++;$minX=min($minX,$x);$maxX=max($maxX,$x);$minY=min($minY,$y);$maxY=max($maxY,$y);}}if($ink<30||$maxX-$minX<12||$maxY-$minY<3)throw new InvalidArgumentException('Bitte beide Unterschriften vollständig zeichnen.');}
  ob_start();try{$ok=$signature?imagepng($target):imagejpeg($target,null,85);$normalized=ob_get_contents();}finally{ob_end_clean();}if(!$ok||!$normalized)throw new RuntimeException('Bild konnte nicht gespeichert werden.');
  return ['bytes'=>$normalized,'mime'=>$signature?'image/png':'image/jpeg','width'=>$outW,'height'=>$outH,'sha256'=>hash('sha256',$normalized)];
 }finally{imagedestroy($source);imagedestroy($target);}
}
function financeProtocolSignature(string $url):array{
 if(strlen($url)>1400000||!str_starts_with($url,'data:image/png;base64,'))throw new InvalidArgumentException('Ungültige Unterschrift.');$bytes=base64_decode(substr($url,22),true);if($bytes===false)throw new InvalidArgumentException('Ungültige Unterschrift.');return financeProtocolImage($bytes,true);
}
function financeProtocolInsertImage(PDO $db,int $id,string $kind,array $image):void{$db->prepare('INSERT INTO fin_protocol_files(protocol_id,kind,mime,image_blob,sha256,width,height) VALUES(?,?,?,?,?,?,?)')->execute([$id,$kind,$image['mime'],$image['bytes'],$image['sha256'],$image['width'],$image['height']]);}
function financeProtocolUpload(PDO $db,int $id,int $revision,array $file):array{
 if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']??''))throw new InvalidArgumentException('Foto konnte nicht hochgeladen werden.');if(filesize($file['tmp_name'])>5242880)throw new InvalidArgumentException('Foto darf höchstens 5 MB gross sein.');$image=financeProtocolImage(file_get_contents($file['tmp_name']));
 financeProtocolTransaction($db,function()use($db,$id,$revision,$image){financeProtocolMutable(financeProtocolRow($db,$id,true),$revision);$q=$db->prepare("SELECT COUNT(*) FROM fin_protocol_files WHERE protocol_id=? AND kind='photo'");$q->execute([$id]);if((int)$q->fetchColumn()>=12)throw new InvalidArgumentException('Maximal 12 Fotos pro Protokoll.');financeProtocolInsertImage($db,$id,'photo',$image);$db->prepare('UPDATE fin_protocols SET revision=revision+1,updated_at=NOW() WHERE id=?')->execute([$id]);financeEvent($db,'upload','protocol',$id,['sha256'=>$image['sha256']]);});return financeProtocolDetail($db,$id);
}
function financeProtocolFile(PDO $db,int $fileId):array{
 $q=$db->prepare('SELECT mime,image_blob,sha256 FROM fin_protocol_files WHERE id=?');$q->execute([$fileId]);$file=$q->fetch();if(!$file)throw new InvalidArgumentException('Bild nicht gefunden.');if(!hash_equals($file['sha256'],hash('sha256',$file['image_blob'])))throw new RuntimeException('Bildprüfung fehlgeschlagen.');return $file;
}
function financeProtocolRenderPdf(PDO $db,array $row,array $snapshot):string{
 require_once __DIR__.'/finance-pdf.php';$pdf=financeNewPdf();$pdf->AddPage();$pdf->SetFont('Helvetica','B',17);$title=$row['type']==='handover'?'Übergabeprotokoll':'Rücknahme / Abnahme';$pdf->MultiCell(178,9,financePdfText($title.' #'.$row['id']));$pdf->SetFont('Helvetica','',10);
 $pdf->MultiCell(178,6,financePdfText(($row['status']==='final'?'Abgeschlossen: '.$row['finalized_at']:'ENTWURF – noch nicht abgeschlossen').($row['supersedes_id']?' | Korrektur zu #'.$row['supersedes_id']:'')));
 $r=$snapshot['reservation'];$d=$snapshot['data'];foreach(['Auftrag'=>$r['id'],'Maschine'=>$r['machine_name'],'Kunde'=>$r['customer_name'],'Mietbeginn'=>$r['start_date'],'Mietende'=>$r['end_date'],'Protokolldatum'=>$d['date'],'Zählerstand'=>$d['meter'],'Tankstand / Akku'=>$d['fuel'],'Zubehör'=>$d['accessories'],'Schäden'=>$d['damages'],'Bemerkungen'=>$d['notes']] as $label=>$value)$pdf->MultiCell(178,6,financePdfText($label.': '.$value));
 $labels=['unchecked'=>'Nicht geprüft','ok'=>'In Ordnung','defect'=>'Mangel','na'=>'Nicht zutreffend'];foreach($d['checklist'] as $item)$pdf->MultiCell(178,6,financePdfText($item['label'].': '.$labels[$item['state']].($item['note']!==''?' – '.$item['note']:'')));
 $q=$db->prepare('SELECT * FROM fin_protocol_files WHERE protocol_id=? ORDER BY id');$q->execute([$row['id']]);while($file=$q->fetch()){
  $photo=$file['kind']==='photo';$label=$photo?'Foto #'.$file['id']:($file['kind']==='customer_signature'?'Kunde: '.$d['customer_name']:'Mitarbeiter: '.$d['operator_name']);$w=min($photo?178:120,$file['width']*(($photo?105:40)/$file['height']));$h=$w*$file['height']/$file['width'];
  if($pdf->GetY()+$h+17>275)$pdf->AddPage();$pdf->Ln(3);$pdf->MultiCell(178,6,financePdfText($label));$temp=tempnam(sys_get_temp_dir(),'finproto_');if($temp===false)throw new RuntimeException('PDF-Bild konnte nicht vorbereitet werden.');
  try{if(file_put_contents($temp,$file['image_blob'])===false)throw new RuntimeException('PDF-Bild konnte nicht geschrieben werden.');$y=$pdf->GetY();$pdf->Image($temp,16,$y,$w,$h,$file['mime']==='image/png'?'PNG':'JPEG');$pdf->SetY($y+$h+4);}finally{if(is_file($temp))unlink($temp);}
 }
 return $pdf->Output('S');
}
function financeProtocolFinalize(PDO $db,int $id,int $revision,string $customer,string $operator):array{
 financeProtocolTransaction($db,function()use($db,$id,$revision,$customer,$operator){$row=financeProtocolRow($db,$id,true);if($row['status']==='final')return;financeProtocolMutable($row,$revision);$data=json_decode($row['data_json'],true,512,JSON_THROW_ON_ERROR);if(trim($data['customer_name'])===''||trim($data['operator_name'])==='')throw new InvalidArgumentException('Bitte beide Namen speichern.');
  financeProtocolInsertImage($db,$id,'customer_signature',financeProtocolSignature($customer));financeProtocolInsertImage($db,$id,'operator_signature',financeProtocolSignature($operator));$q=$db->prepare('SELECT id,kind,mime,sha256,width,height FROM fin_protocol_files WHERE protocol_id=? ORDER BY id');$q->execute([$id]);$snapshot=['reservation'=>financeProtocolReservation($db,(int)$row['reservation_id']),'data'=>$data,'files'=>$q->fetchAll()];$row['status']='final';$row['finalized_at']=$db->query('SELECT NOW()')->fetchColumn();$pdf=financeProtocolRenderPdf($db,$row,$snapshot);$hash=hash('sha256',$pdf);
  $db->prepare("UPDATE fin_protocols SET status='final',revision=revision+1,snapshot_json=?,pdf_blob=?,pdf_sha256=?,finalized_at=?,updated_at=NOW() WHERE id=?")->execute([financeProtocolJson($snapshot),$pdf,$hash,$row['finalized_at'],$id]);financeEvent($db,'finalize','protocol',$id,['pdf_sha256'=>$hash]);
 });return financeProtocolDetail($db,$id);
}
function financeProtocolPdf(PDO $db,int $id):string{
 $row=financeProtocolRow($db,$id);if($row['status']==='final'){if(!$row['pdf_blob']||!hash_equals($row['pdf_sha256'],hash('sha256',$row['pdf_blob'])))throw new RuntimeException('Archiviertes PDF konnte nicht geprüft werden.');return $row['pdf_blob'];}
 return financeProtocolRenderPdf($db,$row,['reservation'=>financeProtocolReservation($db,(int)$row['reservation_id']),'data'=>json_decode($row['data_json'],true,512,JSON_THROW_ON_ERROR)]);
}
