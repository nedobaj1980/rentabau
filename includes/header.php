<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'Rent A Bau – Baumaschinenvermietung';
$assetPrefix = $assetPrefix ?? '';
?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="icon" href="<?= e($assetPrefix) ?>favicon.svg">
  <link rel="stylesheet" href="<?= e($assetPrefix) ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
