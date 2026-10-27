<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/bootstrap.php';requireAdmin();require_once __DIR__.'/../includes/finance-export.php';
header('Cache-Control: private, no-store');
try {
    $from=financeDate((string)($_GET['from']??date('Y-01-01')));$to=financeDate((string)($_GET['to']??date('Y-12-31')));
    $zip=financeExport(financeDb(),$from,$to);
    header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="Treuhand-'.$from.'-'.$to.'.zip"');header('Content-Length: '.strlen($zip));echo $zip;
}catch(InvalidArgumentException $e){http_response_code(422);echo e($e->getMessage());}
catch(Throwable $e){error_log('Finance export: '.$e->getMessage());http_response_code(503);echo e($e instanceof RuntimeException&&!$e instanceof PDOException?$e->getMessage():'Export nicht verfügbar. Bitte später erneut versuchen.');}
