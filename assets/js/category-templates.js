(() => {
  const F=window.CategoryFields;
  const make=(tag,text=null,attrs={})=>{const el=document.createElement(tag);if(text!==null)el.textContent=text;Object.entries(attrs).forEach(([key,value])=>el.setAttribute(key,value));return el;};
  const keyFor=label=>label.toLowerCase().replace(/ä/g,'ae').replace(/ö/g,'oe').replace(/ü/g,'ue').replace(/ß/g,'ss').replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
  const definitions=cat=>F.definitions(CATEGORY_SPECS[cat]||[],SYSTEM_SETTINGS.categoryFields?.[cat]||[]);
  let draft=null,dirty=false;
  const markDirty=()=>{dirty=true;document.getElementById('category-template-status')?.replaceChildren('Ungespeicherte Änderungen');};
  const button=(text,handler,style='button button-small button-outline')=>{const b=make('button',text,{type:'button',class:style});b.addEventListener('click',handler);return b;};
  const fieldWrap=(label,input)=>{const wrap=make('label',null,{class:'ct-field'});wrap.append(make('span',label),input);return wrap;};
  const textInput=(value,callback,attrs={})=>{const input=make('input',null,{type:'text',value,...attrs});input.addEventListener('input',()=>callback(input.value));return input;};
  const select=(values,value,callback)=>{const input=make('select');Object.entries(values).forEach(([key,label])=>{const option=make('option',label,{value:key});option.selected=key===value;input.append(option);});input.addEventListener('change',()=>callback(input.value));return input;};
  function machineField(row,mode,removable=true){
    const wrap=make('div',null,{class:'ct-machine-field'});
    const key=make('input',null,{type:'hidden',value:row.key,class:mode==='edit'?'single-spec-key':'ct-key'});
    const stored=make('input',null,{type:'hidden',value:row.value,class:mode==='edit'?'single-spec-val':'new-m-spec-val','data-key':row.key});
    let raw=row.value||'';
    if(row.unit&&raw.endsWith(' '+row.unit))raw=raw.slice(0,-row.unit.length-1);
    let input;
    const sync=value=>{stored.value=F.formatValue(value,row.unit);};
    if(row.type==='select'||row.type==='boolean'){
      const options=row.type==='boolean'?['Ja','Nein']:row.options;
      const values={'':'Bitte wählen',...Object.fromEntries(options.map(value=>[value,value]))};if(raw&&!options.includes(raw))values[raw]=raw+' (vorhandener Wert)';
      input=select(values,raw,sync);
    }else{
      const numeric=raw===''||/^-?\d+(?:[.,]\d+)?$/.test(raw);
      input=textInput(row.type==='number'&&numeric?raw.replace(',','.'):raw,sync,{type:row.type==='number'&&numeric?'number':'text',step:'any',placeholder:row.unit?'Wert in '+row.unit:'Wert eingeben'});
    }
    wrap.append(key,stored,fieldWrap(row.label+(row.unit?' ('+row.unit+')':''),input));
    if(row.help)wrap.append(make('small',row.help));
    if(removable)wrap.append(button('Feld entfernen',()=>wrap.remove(),'ct-remove'));
    return wrap;
  }
  const renderMachineFields=(container,cat,values,mode)=>{container.replaceChildren(...F.machineRows(definitions(cat),values).map(row=>machineField(row,mode)));};
  const collect=container=>{const result={};container.querySelectorAll('.single-spec-val,.new-m-spec-val').forEach(input=>{const key=input.dataset.key;if(key)result[key]=input.value;});return result;};
  const originalRenderMachine=renderEditingSingleProductForm;
  renderEditingSingleProductForm=function(machine){
    originalRenderMachine(machine);
    const container=document.getElementById('single-specs-container');
    renderMachineFields(container,machine.category,machine.specs||{},'edit');
    document.getElementById('edit-category').addEventListener('change',event=>renderMachineFields(container,event.target.value,collect(container),'edit'));
  };
  onNewMachineCategoryChange=function(cat){const container=document.getElementById('new-m-specs-dynamic-container');if(container)renderMachineFields(container,cat,collect(container),'new');};
  const addMachineField=(mode)=>{const label=prompt('Wie heisst das zusätzliche Feld?');if(!label?.trim())return;const key=keyFor(label);if(!key||['__proto__','constructor','prototype'].includes(key))return;const container=document.getElementById(mode==='edit'?'single-specs-container':'new-m-specs-dynamic-container');if(Object.hasOwn(collect(container),key)){alert('Dieses Feld ist bereits vorhanden.');return;}container.append(machineField({...F.field(key,label.trim()),value:''},mode));};
  addSingleSpecRow=()=>addMachineField('edit');
  addNewSpecRowToNewMachineForm=()=>addMachineField('new');

  renderCategoriesManager=function(){
    const container=document.getElementById('categories-grid-cards-container');if(!container)return;container.replaceChildren();
    CATEGORIES.forEach(cat=>{const defs=definitions(cat),card=make('article',null,{class:'category-card'});card.append(make('h3',cat),make('p',`${MACHINES.filter(machine=>machine.category===cat).length} Maschinen · ${defs.length} wiederverwendbare Felder`));const tags=make('div',null,{class:'ct-tags'});defs.slice(0,5).forEach(row=>tags.append(make('span',row.label,{class:'spec-tag'})));card.append(tags,button('Vorlage bearbeiten',()=>openDedicatedCategoryEditor(cat),'button button-small'));container.append(card);});
  };
  createNewCategoryPrompt=function(){openDedicatedCategoryEditor('Neue Kategorie');};

  renderDedicatedCategoryEditorPage=function(cat){
    dirty=false;
    draft={oldName:CATEGORIES.includes(cat)?cat:null,name:CATEGORIES.includes(cat)?cat:'',fields:definitions(cat).map(row=>({...row,options:[...row.options]})),fillEmpty:false};
    const container=document.getElementById('category-dedicated-editor-container');container.replaceChildren();
    const form=make('form',null,{class:'product-detail-card ct-editor'});
    form.append(make('span','Wiederverwendbare Angaben',{class:'eyebrow'}),make('h2',draft.oldName?`Vorlage für ${cat}`:'Neue Kategorie'),make('p','Definiere die Angaben einmal. Bei den Maschinen erscheinen passende Eingabefelder, Einheiten und Vorschlagswerte.'));
    form.append(fieldWrap('Kategoriename',textInput(draft.name,value=>{draft.name=value;markDirty();},{required:'',maxlength:80,id:'ct-category-name'})));
    const toolbar=make('div',null,{class:'ct-toolbar'}),presetSelect=select(Object.fromEntries(Object.keys(F.presets).map(name=>[name,name])),Object.keys(F.presets)[0],()=>{});
    const mergeFields=extra=>{const before=draft.fields.length;draft.fields=F.merge(draft.fields,extra);markDirty();renderRows();renderPreview();document.getElementById('category-template-status').textContent=`${draft.fields.length-before} fehlende Felder ergänzt. Noch nicht gespeichert.`;};
    toolbar.append(fieldWrap('Feldpaket wählen',presetSelect),button('Feldpaket ergänzen',()=>mergeFields(F.presets[presetSelect.value])));
    const others=CATEGORIES.filter(name=>name!==cat);
    if(others.length){const copy=select(Object.fromEntries(others.map(name=>[name,name])),others[0],()=>{});toolbar.append(fieldWrap('Aus anderer Kategorie',copy),button('Felder übernehmen',()=>mergeFields(definitions(copy.value))));}
    form.append(toolbar,make('p','Vorlagen ergänzen fehlende Felder. Bereits konfigurierte Felder bleiben unverändert.',{class:'ct-help'}));
    const grid=make('div',null,{class:'ct-layout'}),rows=make('div',null,{id:'ct-field-list'}),preview=make('aside',null,{class:'ct-preview'});
    preview.append(make('h3','So sieht das Maschinenformular aus'),make('div',null,{id:'ct-form-preview'}));grid.append(rows,preview);form.append(grid);
    form.append(button('+ Eigenes Feld hinzufügen',()=>{draft.fields.push(F.field('',''));markDirty();renderRows();renderPreview();}));
    const machines=MACHINES.filter(machine=>machine.category===cat);
    if(machines.length){
      const apply=make('input',null,{type:'checkbox',id:'ct-fill-empty'});apply.addEventListener('change',()=>{draft.fillEmpty=apply.checked;markDirty();renderPreview();});
      const fillLabel=fieldWrap(`Standardwerte auch bei ${machines.length} bestehenden Maschinen in dieser Kategorie ergänzen`,apply);fillLabel.classList.add('ct-bulk');form.append(fillLabel,make('p','Es werden ausschliesslich leere oder fehlende Angaben ergänzt. Bereits erfasste Werte bleiben erhalten.',{class:'ct-help'}),make('p','',{id:'ct-bulk-count',class:'ct-help'}));
    }
    const footer=make('div',null,{class:'ct-save'}),save=make('button','Kategorievorlage speichern',{type:'submit',class:'button'});
    footer.append(make('span','Keine ungespeicherten Änderungen',{id:'category-template-status',role:'status'}),save);form.append(footer);
    form.addEventListener('submit',async event=>{
      event.preventDefault();
      if(draft.fields.some(row=>!row.key||!row.label.trim())){document.getElementById('category-template-status').textContent='Bitte alle Felder benennen.';return;}
      save.disabled=true;
      try{
        const response=await fetch('api/category-templates.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':document.querySelector('meta[name="category-csrf"]').content},body:JSON.stringify(draft)});
        const result=await response.json();if(!response.ok||!result.success)throw new Error(result.error||'Speichern fehlgeschlagen.');
        dirty=false;await fetchSettings();const savedName=draft.name.trim();openDedicatedCategoryEditor(savedName);renderCategoriesManager();document.getElementById('category-template-status').textContent=`Gespeichert.${result.filled?` ${result.filled} leere Angaben bei ${result.machinesChanged} Maschinen ergänzt.`:''}`;
      }catch(error){document.getElementById('category-template-status').textContent=error.message;}
      finally{save.disabled=false;}
    });
    container.append(form);renderRows();renderPreview();
  };

  function renderRows(){
    const list=document.getElementById('ct-field-list');list.replaceChildren();
    if(!draft.fields.length)list.append(make('p','Starte mit einem Feldpaket oder füge eigene Felder hinzu.',{class:'ct-help'}));
    draft.fields.forEach((row,i)=>{
      const card=make('article',null,{class:'ct-field-card'}),head=make('div',null,{class:'ct-field-head'}),actions=make('div');
      head.append(make('strong',`Feld ${i+1}`),actions);
      const move=delta=>{[draft.fields[i],draft.fields[i+delta]]=[draft.fields[i+delta],draft.fields[i]];markDirty();renderRows();renderPreview();};
      const up=button('↑',()=>move(-1),'ct-icon'),down=button('↓',()=>move(1),'ct-icon');up.disabled=i===0;down.disabled=i===draft.fields.length-1;up.setAttribute('aria-label',`Feld ${i+1} nach oben`);down.setAttribute('aria-label',`Feld ${i+1} nach unten`);
      const remove=button('Entfernen',()=>{draft.fields.splice(i,1);markDirty();renderRows();renderPreview();},'ct-remove');actions.append(up,down,remove);card.append(head);
      const grid=make('div',null,{class:'ct-field-grid'});
      const stableKey=row.key;grid.append(fieldWrap('Bezeichnung',textInput(row.label,value=>{row.label=value;if(!stableKey)row.key=keyFor(value);markDirty();renderPreview();},{required:'',maxlength:80})),fieldWrap('Einheit',textInput(row.unit,value=>{row.unit=value;markDirty();renderPreview();},{maxlength:20,placeholder:'z. B. kg, mm, kW'})));
      grid.append(fieldWrap('Eingabeart',select({text:'Freier Text',number:'Zahl',select:'Auswahlliste',boolean:'Ja / Nein'},row.type,value=>{row.type=value;markDirty();renderRows();renderPreview();})),fieldWrap('Standardwert (optional)',textInput(row.defaultValue,value=>{row.defaultValue=value;markDirty();renderPreview();},{maxlength:200,placeholder:'Nur für gemeinsame Angaben'})));
      if(row.type==='select'){const options=make('textarea',row.options.join('\n'),{rows:3,placeholder:'Ein Auswahlwert pro Zeile'});options.addEventListener('input',()=>{row.options=options.value.split('\n').map(v=>v.trim()).filter(Boolean);markDirty();renderPreview();});grid.append(fieldWrap('Auswahlwerte – einer pro Zeile',options));}
      grid.append(fieldWrap('Ausfüllhilfe (optional)',textInput(row.help,value=>{row.help=value;markDirty();renderPreview();},{maxlength:200,placeholder:'z. B. Betriebsgewicht ohne Anbaugerät'})));
      card.append(grid,make('small','Bestehende Maschinenwerte bleiben auch beim Entfernen eines Vorlagenfeldes erhalten.',{class:'ct-help'}));list.append(card);
    });
  }
  function renderPreview(){
    const container=document.getElementById('ct-form-preview');if(!container)return;
    container.replaceChildren(...F.machineRows(draft.fields).map(row=>machineField(row,'preview',false)));
    const count=document.getElementById('ct-bulk-count');if(count){const machines=MACHINES.filter(m=>m.category===draft.oldName);const fills=machines.reduce((sum,machine)=>sum+draft.fields.filter(row=>row.defaultValue.trim()&&!String(machine.specs?.[row.key]??'').trim()).length,0);count.textContent=draft.fillEmpty?`${fills} leere Angaben werden beim Speichern ergänzt.`:'Die vorhandenen Maschinen werden durch das Speichern der Vorlage nicht geändert.';}
  }
  const originalClose=closeCategoryDedicatedEditor;
  closeCategoryDedicatedEditor=function(){if(dirty&&!confirm('Ungespeicherte Änderungen an der Kategorievorlage verwerfen?'))return;dirty=false;originalClose();};
  window.addEventListener('beforeunload',event=>{if(dirty){event.preventDefault();event.returnValue='';}});
})();
