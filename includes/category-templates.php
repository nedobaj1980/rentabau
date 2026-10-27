<?php
declare(strict_types=1);

function categoryTemplateUpdate(array $current,array $input):array
{
    $name=$input['name']??null; $old=$input['oldName']??null;
    if(!is_string($name)||trim($name)===''||strlen($name)>160||preg_match('/[<>\x00-\x1f]/',$name)||in_array($name,['__proto__','constructor','prototype'],true))throw new InvalidArgumentException('Bitte einen gültigen Kategorienamen eingeben.');
    $name=trim($name);
    if($old!==null&&!is_string($old))throw new InvalidArgumentException('Ungültige ursprüngliche Kategorie.');
    $categories=$current['categories']??[];
    if($old!==null&&!in_array($old,$categories,true))throw new InvalidArgumentException('Die Kategorie existiert nicht mehr. Bitte neu laden.');
    if($old!==$name&&in_array($name,$categories,true))throw new InvalidArgumentException('Eine Kategorie mit diesem Namen existiert bereits.');
    $raw=$input['fields']??null;
    if(!is_array($raw)||count($raw)>60)throw new InvalidArgumentException('Bis zu 60 Felder pro Kategorie sind möglich.');
    $fields=[];$keys=[];
    foreach($raw as $row){
        if(!is_array($row))throw new InvalidArgumentException('Ungültiges Feld.');
        foreach(['key'=>100,'label'=>160,'unit'=>40,'defaultValue'=>800,'help'=>800] as $key=>$length){if(!is_string($row[$key]??null)||strlen($row[$key])>$length)throw new InvalidArgumentException('Ungültige oder zu lange Feldangabe: '.$key);}
        if(trim($row['key'])===''||trim($row['label'])===''||preg_match('/[<>"\x27\x00-\x1f]/',$row['key'])||in_array($row['key'],['__proto__','constructor','prototype'],true)||in_array($row['key'],$keys,true))throw new InvalidArgumentException('Felder benötigen eindeutige Namen und gültige Bezeichnungen.');
        $type=$row['type']??'text';
        if(!in_array($type,['text','number','select','boolean'],true))throw new InvalidArgumentException('Ungültige Eingabeart.');
        $options=$row['options']??[];
        if(!is_array($options)||count($options)>40)throw new InvalidArgumentException('Bis zu 40 Auswahlwerte pro Feld sind möglich.');
        foreach($options as $option){if(!is_string($option)||trim($option)===''||strlen($option)>160)throw new InvalidArgumentException('Ungültiger Auswahlwert.');}
        $options=array_values(array_unique(array_map('trim',$options)));
        if($type==='select'&&count($options)===0)throw new InvalidArgumentException('Bitte Auswahlwerte für „'.$row['label'].'“ eintragen.');
        $value=trim($row['defaultValue']);
        if($value!==''&&$type==='number'&&!is_numeric(str_replace(',','.',$value)))throw new InvalidArgumentException('Der Standardwert für „'.$row['label'].'“ muss eine Zahl sein.');
        if($value!==''&&$type==='select'&&!in_array($value,$options,true))throw new InvalidArgumentException('Der Standardwert muss in der Auswahlliste vorkommen.');
        if($value!==''&&$type==='boolean'&&!in_array($value,['Ja','Nein'],true))throw new InvalidArgumentException('Für Ja/Nein-Felder ist der Standardwert Ja oder Nein.');
        $keys[]=$row['key'];
        $fields[]=['key'=>$row['key'],'label'=>trim($row['label']),'unit'=>trim($row['unit']),'type'=>$type,'options'=>$options,'defaultValue'=>$value,'help'=>trim($row['help'])];
    }
    if(!is_bool($input['fillEmpty']??false))throw new InvalidArgumentException('Ungültige Übernahmeoption.');
    if($old===null)$categories[]=$name;else $categories=array_map(fn($category)=>$category===$old?$name:$category,$categories);
    $current['categories']=$categories;
    if($old!==null&&$old!==$name){unset($current['categorySpecs'][$old],$current['categoryFields'][$old]);}
    $current['categorySpecs'][$name]=$keys;$current['categoryFields'][$name]=$fields;
    $filled=0;$machinesChanged=0;
    foreach($current['machines']??[] as $id=>$machine){
        if(($machine['category']??null)!==($old??$name))continue;
        $changed=false;
        $current['machines'][$id]['category']=$name;
        if(!empty($input['fillEmpty']))foreach($fields as $field){
            if($field['defaultValue']===''||trim((string)($machine['specs'][$field['key']]??''))!=='')continue;
            $value=$field['defaultValue'];
            if($field['unit']!==''&&!str_ends_with($value,' '.$field['unit']))$value.=' '.$field['unit'];
            $current['machines'][$id]['specs'][$field['key']]=$value;$filled++;$changed=true;
        }
        if($changed)$machinesChanged++;
    }
    return ['settings'=>$current,'filled'=>$filled,'machinesChanged'=>$machinesChanged];
}
