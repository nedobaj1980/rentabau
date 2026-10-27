<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/bootstrap.php';requireAdmin();require_once __DIR__.'/../includes/finance-protocols.php';
header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');
function protocolApiJson(array $data,int $status=200):never{http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);exit;}
try{
 $db=financeDb();if(!financeReady($db))protocolApiJson(['error'=>'Bitte zuerst Finanzverwaltung einrichten.'],503);$method=$_SERVER['REQUEST_METHOD']??'GET';
 if($method==='GET'){$action=(string)($_GET['action']??'');$id=(int)($_GET['id']??0);
  if($action==='pdf'){$bytes=financeProtocolPdf($db,$id);header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="protokoll-'.$id.'.pdf"');header('Content-Length: '.strlen($bytes));echo $bytes;exit;}
  if($action==='file'){$file=financeProtocolFile($db,$id);header('Content-Type: '.$file['mime']);header('Content-Length: '.strlen($file['image_blob']));echo $file['image_blob'];exit;}
  if($id>0)protocolApiJson(['protocol'=>financeProtocolDetail($db,$id)]);
  $rid=isset($_GET['reservation_id'])?(int)$_GET['reservation_id']:null;protocolApiJson(['protocols'=>financeProtocolList($db,$rid),'reservation'=>$rid!==null?financeProtocolReservation($db,$rid):null]);
 }
 if($method!=='POST')protocolApiJson(['error'=>'Methode nicht erlaubt.'],405);
 if(str_contains($_SERVER['CONTENT_TYPE']??'','application/json')){$raw=file_get_contents('php://input',false,null,0,3000001);if(strlen($raw)>3000000)throw new InvalidArgumentException('Anfrage zu gross.');$input=json_decode($raw,true,64,JSON_THROW_ON_ERROR);if(!is_array($input))throw new InvalidArgumentException('Ungültige Anfrage.');}else $input=$_POST;
 $token=$input['csrf_token']??null;if(!is_string($token)||empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$token))protocolApiJson(['error'=>'Sitzung abgelaufen. Bitte neu laden.'],419);
 $id=(int)($input['protocol_id']??0);$revision=(int)($input['revision']??0);
 switch($input['action']??''){
  case 'create':$protocol=financeProtocolCreate($db,$input);break;
  case 'save':if(!is_array($input['data']??null))throw new InvalidArgumentException('Protokolldaten fehlen.');$protocol=financeProtocolSave($db,$id,$revision,$input['data']);break;
  case 'upload':$protocol=financeProtocolUpload($db,$id,$revision,$_FILES['file']??[]);break;
  case 'finalize':$protocol=financeProtocolFinalize($db,$id,$revision,(string)($input['customer_signature']??''),(string)($input['operator_signature']??''));break;
  default:throw new InvalidArgumentException('Unbekannte Aktion.');
 }protocolApiJson(['success'=>true,'protocol'=>$protocol]);
}catch(InvalidArgumentException|JsonException $e){protocolApiJson(['error'=>$e->getMessage()],422);}catch(Throwable $e){error_log('Finance protocol: '.$e->getMessage());protocolApiJson(['error'=>'Protokoll konnte nicht verarbeitet werden.'],503);}
