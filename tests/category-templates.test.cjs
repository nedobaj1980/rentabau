const test=require('node:test');
const assert=require('node:assert/strict');
const fieldModule={exports:{}};
new Function('module',require('node:fs').readFileSync(require('node:path').join(__dirname,'../assets/js/category-fields.js'),'utf8'))(fieldModule);
const fields=fieldModule.exports;
test('legacy keys remain stable with readable labels',()=>{
  const result=fields.definitions(['gewicht','grabtiefe'],[]);
  assert.deepEqual(result.map(f=>f.key),['gewicht','grabtiefe']);
  assert.equal(result[0].label,'Gewicht');
});
test('merging templates adds missing fields without overwriting current definitions',()=>{
  const a=fields.definitions(['gewicht'],[]);a[0].unit='t';
  const merged=fields.merge(a,[{...a[0],unit:'kg'},{...a[0],key:'breite',label:'Breite'}]);
  assert.equal(merged.length,2);assert.equal(merged[0].unit,'t');assert.equal(a.length,1);
});
test('machine form keeps values and extra fields, defaults only fill absent fields',()=>{
  const defs=fields.definitions(['gewicht','breite'],[]);defs[0].defaultValue='100';defs[0].unit='kg';defs[1].defaultValue='90';defs[1].unit='cm';
  const result=fields.machineRows(defs,{gewicht:"1'800 kg",notiz:'Spezialmaschine'});
  assert.equal(result.find(r=>r.key==='gewicht').value,"1'800 kg");
  assert.equal(result.find(r=>r.key==='breite').value,'90 cm');
  assert.equal(result.find(r=>r.key==='notiz').value,'Spezialmaschine');
});
test('units are not appended twice and empty values stay empty',()=>{
  assert.equal(fields.formatValue('1800 kg','kg'),'1800 kg');assert.equal(fields.formatValue('','kg'),'');
});
