<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/category-templates.php';
function ensure(bool $pass,string $message):void{if(!$pass)throw new RuntimeException($message);}
$field=['key'=>'gewicht','label'=>'Gewicht','unit'=>'kg','type'=>'number','options'=>[],'defaultValue'=>'100','help'=>'Betriebsgewicht'];
$current=['categories'=>['Bagger'],'categorySpecs'=>['Bagger'=>['gewicht']],'machines'=>['a'=>['category'=>'Bagger','specs'=>['gewicht'=>"1'800 kg"]],'b'=>['category'=>'Bagger','specs'=>[]],'c'=>['category'=>'Andere','specs'=>[]]],'paymentConfig'=>['untouched'=>true]];
$input=['oldName'=>'Bagger','name'=>'Minibagger','fields'=>[$field],'fillEmpty'=>true];
$result=categoryTemplateUpdate($current,$input);
ensure($result['settings']['machines']['a']['specs']['gewicht']==="1'800 kg",'Existing value overwritten');
ensure($result['settings']['machines']['b']['specs']['gewicht']==='100 kg','Default not filled');
ensure($result['settings']['machines']['c']['specs']===[],'Other category changed');
ensure($result['settings']['paymentConfig']===$current['paymentConfig'],'Unrelated data changed');
ensure($result['filled']===1,'Wrong filled count');
foreach([array_replace($field,['key'=>'__proto__']),array_replace($field,['type'=>'html']),array_replace($field,['type'=>'select','options'=>['Diesel'],'defaultValue'=>'Benzin'])] as $invalid){
  try{categoryTemplateUpdate($current,array_replace($input,['fields'=>[$invalid]]));throw new RuntimeException('Invalid field accepted');}catch(InvalidArgumentException $expected){}
}
echo "PASS: category validation, rename and additive default application.\n";
