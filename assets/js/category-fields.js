(function(root,factory){if(typeof module==='object'&&module.exports)module.exports=factory();else root.CategoryFields=factory();})(typeof window!=='undefined'?window:this,()=>{
  const labels={gewicht:'Gewicht',grabtiefe:'Grabtiefe',breite:'Breite',laenge:'Länge',hoehe:'Höhe',leistung:'Leistung',nutzlast:'Nutzlast',muldeninhalt:'Muldeninhalt',antrieb:'Antrieb',fliehkraft:'Fliehkraft',arbeitsbreite:'Arbeitsbreite',kraftstoff:'Kraftstoff',einsatzgewicht:'Einsatzgewicht',oelmenge:'Ölmenge',arbeitsdruck:'Arbeitsdruck',traegergeraet:'Trägergerät',gesamtgewicht:'Gesamtgewicht',ladeflaeche:'Ladefläche',kupplung:'Kupplung'};
  const field=(key,label=labels[key]||key,unit='',type='text',options=[])=>({key,label,unit,type,options,defaultValue:'',help:''});
  const definitions=(keys,stored)=>keys.map(key=>({...field(key),...(stored.find(item=>item.key===key)||{})}));
  const merge=(current,extra)=>[...current.map(row=>({...row,options:[...row.options]})),...extra.filter(row=>!current.some(item=>item.key===row.key)).map(row=>({...row,options:[...row.options]}))];
  const formatValue=(value,unit)=>{const text=String(value??'').trim();return !text?'':unit&&!text.endsWith(' '+unit)?text+' '+unit:text;};
  const machineRows=(defs,values={})=>{const rows=defs.map(row=>({...row,value:Object.prototype.hasOwnProperty.call(values,row.key)?String(values[row.key]??''):formatValue(row.defaultValue,row.unit)}));Object.entries(values).filter(([key])=>!defs.some(row=>row.key===key)).forEach(([key,value])=>rows.push({...field(key),value:String(value??'')}));return rows;};
  const presets={
    'Masse & Gewicht':[field('gewicht','Gewicht','kg','number'),field('breite','Breite','mm','number'),field('laenge','Länge','mm','number'),field('hoehe','Höhe','mm','number')],
    'Motor & Antrieb':[field('leistung','Motorleistung','kW','number'),field('kraftstoff','Kraftstoff','','select',['Diesel','Benzin','Elektro','Hybrid']),field('antrieb','Antrieb','','select',['Kette','Allrad','Zweirad']),field('tankinhalt','Tankinhalt','l','number')],
    'Bagger':[field('grabtiefe','Grabtiefe','m','number'),field('reichweite','Reichweite','m','number'),field('ausschuetthoehe','Ausschütthöhe','m','number'),field('schnellwechsler','Schnellwechsler','','boolean'),field('zusatzhydraulik','Zusatzhydraulik','','boolean')],
    'Transport & Anhänger':[field('nutzlast','Nutzlast','kg','number'),field('gesamtgewicht','Gesamtgewicht','kg','number'),field('ladeflaeche','Ladefläche'),field('kupplung','Kupplung','','select',['Kugelkopf','Maulkupplung','Andere']),field('fuehrerschein','Führerscheinklasse','','select',['B','BE','C1','C1E','C','CE'])],
    'Verdichtung':[field('fliehkraft','Fliehkraft','kN','number'),field('arbeitsbreite','Arbeitsbreite','mm','number'),field('frequenz','Frequenz','Hz','number'),field('reversierbar','Reversierbar','','boolean')],
    'Hydraulik & Abbruch':[field('einsatzgewicht','Einsatzgewicht','kg','number'),field('oelmenge','Ölmenge','l/min','number'),field('arbeitsdruck','Arbeitsdruck','bar','number'),field('traegergeraet','Passendes Trägergerät')],
    'Ausstattung & Zubehör':[field('kabine','Kabine','','select',['Geschlossene Kabine','Schutzdach','Offen']),field('klimaanlage','Klimaanlage','','boolean'),field('zubehoer','Enthaltenes Zubehör'),field('anschluss','Anschluss / Aufnahme'),field('hinweis','Besonderheiten')]
  };
  return {field,definitions,merge,formatValue,machineRows,presets};
});
