<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/bootstrap.php';
requireAdmin();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../includes/category-templates.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['error'=>'Methode nicht erlaubt.']);exit;}
$token=$_SERVER['HTTP_X_CSRF_TOKEN']??'';
if($token===''||empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$token)){http_response_code(419);echo json_encode(['error'=>'Bitte die Adminseite neu laden und erneut speichern.']);exit;}
$handle=null;
try{
    $raw=file_get_contents('php://input');
    if(strlen($raw)>200000)throw new InvalidArgumentException('Die Kategorievorlage ist zu gross.');
    $input=json_decode($raw,true,64,JSON_THROW_ON_ERROR);
    if(!is_array($input))throw new InvalidArgumentException('Ungültige Eingabe.');
    $handle=fopen(__DIR__.'/../database/settings.json','c+');
    if(!$handle||!flock($handle,LOCK_EX))throw new RuntimeException('Einstellungen konnten nicht geöffnet werden.');
    $current=json_decode(stream_get_contents($handle),true,64,JSON_THROW_ON_ERROR);
    if(!is_array($current))throw new RuntimeException('Einstellungen konnten nicht gelesen werden.');
    $result=categoryTemplateUpdate($current,$input);
    $json=json_encode($result['settings'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    rewind($handle);
    if(fwrite($handle,$json)!==strlen($json)||!ftruncate($handle,strlen($json))||!fflush($handle))throw new RuntimeException('Speichern fehlgeschlagen.');
    auditLogSafe('update', 'categories', 'category_template', (string)($input['category'] ?? ''), 'Kategorievorlage und zugehörige Maschinen aktualisiert', $current, $result['settings']);
    echo json_encode(['success'=>true,'filled'=>$result['filled'],'machinesChanged'=>$result['machinesChanged']]);
}catch(InvalidArgumentException|JsonException $error){http_response_code(400);echo json_encode(['error'=>$error->getMessage()]);}
catch(RuntimeException $error){http_response_code(500);echo json_encode(['error'=>$error->getMessage()]);}
finally{if(is_resource($handle)){flock($handle,LOCK_UN);fclose($handle);}}
