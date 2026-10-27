<?php
require __DIR__ . '/slider-config.php';
$custom=sliderDefaults();
$custom['slides'][0]['mobile']=['positionX'=>20,'positionY'=>80,'zoom'=>180];
$custom['slides'][0]['zoom']=125;
$path=sys_get_temp_dir().'/slider-responsive-'.bin2hex(random_bytes(6)).'.json';
try {
    sliderSave($custom,$path);
    check(sliderLoad($path)===$custom,'Desktop and mobile crops must survive saving independently.');
} finally {if(is_file($path))unlink($path);}
foreach ([['positionX'=>-1,'positionY'=>50,'zoom'=>100],['positionX'=>50,'positionY'=>101,'zoom'=>100],['positionX'=>50,'positionY'=>50,'zoom'=>301], 'invalid'] as $mobile) {
    $bad=$custom;$bad['slides'][0]['mobile']=$mobile;
    try {sliderValidate($bad);throw new RuntimeException('Invalid mobile crop accepted.');} catch(InvalidArgumentException $expected){}
}
$sliderConfig=$custom;
ob_start();include __DIR__.'/../includes/hero-slider.php';$html=ob_get_clean();
check(str_contains($html,'--slide-mobile-position:20% 80%'),'Mobile position must be rendered.');
check(str_contains($html,'--slide-mobile-transform:scale(1.8)'),'Mobile zoom must be rendered.');
$sliderConfig=sliderDefaults();$sliderConfig['slides'][0]['positionX']=35;$sliderConfig['slides'][0]['zoom']=150;
ob_start();include __DIR__.'/../includes/hero-slider.php';$html=ob_get_clean();
check(str_contains($html,'--slide-mobile-position:35% 50%'),'Legacy mobile position must inherit existing crop.');
check(str_contains($html,'--slide-mobile-transform:scale(1.5)'),'Legacy mobile zoom must inherit existing crop.');
echo "PASS: independent desktop/mobile persistence, validation, rendering and legacy fallback.\n";
