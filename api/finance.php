<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/bootstrap.php';
requireAdmin();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
require_once __DIR__.'/../includes/finance.php';
function financeReply(array $body,int $status=200): never {http_response_code($status);echo json_encode($body,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;}
try {
    $db=financeDb();$method=$_SERVER['REQUEST_METHOD']??'GET';
    if($method==='GET'){
        if(!financeReady($db))financeReply(['error'=>'Finanzbereich muss einmalig eingerichtet werden.','code'=>'migration_required'],503);
        require_once __DIR__.'/../includes/finance-report.php';financeReply(financeReport($db,$_GET));
    }
    if($method!=='POST')financeReply(['error'=>'Methode nicht erlaubt.'],405);
    $input=str_contains($_SERVER['CONTENT_TYPE']??'','application/json')?json_decode(file_get_contents('php://input'),true,64,JSON_THROW_ON_ERROR):$_POST;
    if(!is_array($input))throw new InvalidArgumentException('Ungültige Anfrage.');
    $token=$input['csrf_token']??null;if(!is_string($token)||empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$token))financeReply(['error'=>'Sitzung abgelaufen. Bitte Seite neu laden.'],419);
    $action=$input['action']??'';
    if($action==='setup'){financeSetup($db);financeReply(['success'=>true]);}
    if(!financeReady($db))financeReply(['error'=>'Finanzbereich zuerst einrichten.','code'=>'migration_required'],503);
    $id=null;
    switch($action){
        case 'settings':financeSaveSettings($db,$input);break;
        case 'invoice':$id=financeIssue($db,$input);break;
        case 'payment':$id=financePayment($db,$input);break;
        case 'refund':$id=financePayment($db,$input,true);break;
        case 'credit':$id=financeCredit($db,(int)($input['invoice_id']??0),(string)($input['reason']??''));break;
        case 'expense':
            $receipt=null;$file=$_FILES['receipt']??null;
            if($file&&($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
                if($file['error']!==UPLOAD_ERR_OK||$file['size']>8*1024*1024||!is_uploaded_file($file['tmp_name']))throw new InvalidArgumentException('Beleg bitte als PDF, JPG oder PNG bis 8 MB hochladen.');
                $bytes=file_get_contents($file['tmp_name']);$mime=(new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
                if(!in_array($mime,['application/pdf','image/jpeg','image/png'],true))throw new InvalidArgumentException('Erlaubte Belege: PDF, JPG und PNG.');
                if($mime!=='application/pdf'){$info=@getimagesizefromstring($bytes);if(!$info||$info[0]*$info[1]>24000000)throw new InvalidArgumentException('Belegbild ungültig oder zu gross.');}
                $receipt=['bytes'=>$bytes,'mime'=>$mime,'name'=>mb_substr(basename($file['name']),0,200)];
            }
            $id=financeExpense($db,$input,$receipt);break;
        case 'send_copy':require_once __DIR__.'/../includes/finance-mail.php';financeSendCopy($db,(int)($input['invoice_id']??0),(string)($input['recipient']??''));break;
        default:throw new InvalidArgumentException('Unbekannte Aktion.');
    }
    financeReply(['success'=>true,'id'=>$id]);
}catch(InvalidArgumentException|JsonException $e){financeReply(['error'=>$e->getMessage()],422);}
catch(Throwable $e){error_log('Finance: '.$e->getMessage());financeReply(['error'=>$e instanceof RuntimeException&&!$e instanceof PDOException?$e->getMessage():'Finanzdaten konnten nicht verarbeitet werden. Bitte erneut versuchen.'],503);}
