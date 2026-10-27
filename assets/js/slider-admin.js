(() => {
  const initial=JSON.parse(document.getElementById('slider-initial').textContent);
  let config=structuredClone(initial.config), dirty=false, uploading=0;
  const initializeMobile=(slide)=>{slide.mobile??={positionX:slide.positionX,positionY:slide.positionY,zoom:slide.zoom??100};return slide;};
  config.slides.forEach(initializeMobile);
  const editingMobile=()=>document.getElementById('slider-preview-size').value==='mobile';
  const cropFor=(slide)=>editingMobile()?(slide.mobile??slide):slide;
  const form=document.getElementById('slider-editor'), list=document.getElementById('slider-slide-list');
  const preview=document.querySelector('#slider-preview [data-hero-slider]');
  let selectedIndex=0;
  const imagePicker=document.getElementById('slider-preview-image');
  const cropX=document.getElementById('slider-crop-x'), cropY=document.getElementById('slider-crop-y'), cropZoom=document.getElementById('slider-crop-zoom');
  const applyCrop=(img,slide)=>{slide=cropFor(slide);img.style.objectPosition=`${slide.positionX}% ${slide.positionY}%`;img.style.transformOrigin=img.style.objectPosition;img.style.transform=`scale(${(slide.zoom??100)/100})`;};
  const syncCropControls=()=>{
    const slide=cropFor(config.slides[selectedIndex]);if(!slide)return;
    document.getElementById('slider-crop-mode').textContent=editingMobile()?'Bildausschnitt für Mobil':'Bildausschnitt für Desktop';
    imagePicker.value=String(selectedIndex);cropX.value=slide.positionX;cropY.value=slide.positionY;cropZoom.value=slide.zoom??100;
    document.getElementById('slider-zoom-value').value=(slide.zoom??100)+' %';
    document.getElementById('slider-zoom-out').disabled=(slide.zoom??100)<=100;
    document.getElementById('slider-zoom-in').disabled=(slide.zoom??100)>=300;
    [...list.children].forEach((card,i)=>card.classList.toggle('is-editing',i===selectedIndex));
  };
  const selectSlide=(i)=>{selectedIndex=Math.max(0,Math.min(config.slides.length-1,i));preview.querySelectorAll('[data-slider-dot]')[selectedIndex]?.click();syncCropControls();};
  const restartPreview=()=>{preview.dataset.autoplay='false';window.initHeroSlider(preview);selectSlide(selectedIndex);};
  const status=document.getElementById('slider-editor-status');
  const fields=[...form.querySelectorAll('[data-config]')];
  const setStatus=(text)=>{status.textContent=text;};
  const markDirty=()=>{dirty=true;setStatus('Ungespeicherte Änderungen');};
  const make=(tag,text,attributes={})=>{const el=document.createElement(tag);if(text!==null)el.textContent=text;Object.entries(attributes).forEach(([key,value])=>el.setAttribute(key,value));return el;};
  const refreshPreview=(imagesChanged=false)=>{
    ['titleSize','mobileTitleSize','bodySize','eyebrowSize','contentWidth','minHeight'].forEach(key=>preview.style.setProperty(`--slider-${key}`,`${config[key]}px`));
    ['titleColor','accentColor','bodyColor','buttonColor','buttonTextColor','fontWeight','fontStyle'].forEach(key=>preview.style.setProperty(`--slider-${key}`,config[key]));
    preview.style.setProperty('--slider-overlay',config.overlay/100);preview.style.setProperty('--slider-transition',`${config.transition}ms`);
    preview.style.setProperty('--slider-font',{system:'-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif',arial:'Arial,sans-serif',georgia:'Georgia,serif',verdana:'Verdana,sans-serif'}[config.fontFamily]);
    preview.dataset.alignment=config.alignment;preview.dataset.autoplay='false';preview.dataset.interval=config.interval;
    preview.querySelectorAll('[data-slider-text]').forEach(el=>el.textContent=config[el.dataset.sliderText]);
    preview.querySelectorAll('[data-slider-button]').forEach(el=>{const key=el.dataset.sliderButton;el.textContent=config[key+'Label'];el.hidden=!config[key+'Label'];});
    const facts=preview.querySelector('.hero-facts');facts.replaceChildren(...config.facts.split('\n').filter(Boolean).map(text=>make('span','✓ '+text)));
    preview.querySelector('.slider-controls').hidden=config.slides.length<2;
    preview.querySelector('[data-slider-prev]').hidden=!config.showArrows;preview.querySelector('[data-slider-next]').hidden=!config.showArrows;
    preview.querySelector('.slider-dots').hidden=!config.showDots;preview.querySelector('[data-slider-pause]').hidden=true;
    if(imagesChanged){
      preview.querySelector('.slider-images').replaceChildren(...config.slides.map((slide,i)=>{const img=make('img',null,{class:'hero-bg slider-image'+(i===0?' is-active':''),src:slide.image,alt:slide.alt});applyCrop(img,slide);return img;}));
      preview.querySelector('.slider-dots').replaceChildren(...config.slides.map((slide,i)=>make('button',null,{type:'button','data-slider-dot':i,'aria-label':`Bild ${i+1} anzeigen`,'aria-pressed':String(i===0)})));
    }else preview.querySelectorAll('.slider-image').forEach((img,i)=>{applyCrop(img,config.slides[i]);img.alt=config.slides[i].alt;});
  };
  const renderSlides=()=>{
    list.replaceChildren();
    imagePicker.replaceChildren(...config.slides.map((slide,i)=>make('option',`Bild ${i+1} – ${slide.image.split('/').pop()}`,{value:i})));
    config.slides.forEach((slide,i)=>{
      const card=make('article',null,{class:'slider-slide-card'}), header=make('header',null), actions=make('div',null,{class:'slider-slide-actions'});
      header.append(make('strong',`Bild ${i+1}`),actions);card.append(header);
      const thumb=make('img',null,{src:slide.image,alt:slide.alt});applyCrop(thumb,slide);const thumbFrame=make('div',null,{class:'slider-slide-thumb'});thumbFrame.append(thumb);card.append(thumbFrame);
      const action=(label,aria,disabled,handler)=>{const b=make('button',label,{type:'button','aria-label':aria});b.disabled=disabled;b.addEventListener('click',handler);actions.append(b);};
      const rerender=()=>{selectedIndex=Math.max(0,config.slides.indexOf(slide));markDirty();renderSlides();refreshPreview(true);restartPreview();};
      action('Bearbeiten',`Bild ${i+1} in der Vorschau bearbeiten`,false,()=>selectSlide(i));
      action('↑',`Bild ${i+1} nach vorne`,i===0,()=>{[config.slides[i-1],config.slides[i]]=[config.slides[i],config.slides[i-1]];rerender();});
      action('↓',`Bild ${i+1} nach hinten`,i===config.slides.length-1,()=>{[config.slides[i+1],config.slides[i]]=[config.slides[i],config.slides[i+1]];rerender();});
      action('×',`Bild ${i+1} entfernen`,config.slides.length===1,()=>{config.slides.splice(i,1);rerender();});
      const field=(label,control)=>{const wrap=make('label',null,{class:'slider-field'});wrap.append(make('span',label),control);card.append(wrap);};
      const select=make('select',null);initial.images.forEach(path=>{const option=make('option',path.split('/').pop(),{value:path});option.selected=path===slide.image;select.append(option);});
      select.addEventListener('change',()=>{slide.image=select.value;rerender();});field('Vorhandenes Bild',select);
      const upload=make('input',null,{type:'file',accept:'image/jpeg,image/png,image/webp'});
      upload.addEventListener('change',async()=>{
        if(!upload.files[0])return;uploading++;document.getElementById('slider-save').disabled=true;setStatus('Bild wird hochgeladen …');
        const data=new FormData();data.set('action','upload');data.set('csrf_token',form.elements.csrf_token.value);data.set('image',upload.files[0]);
        try{const response=await fetch('admin-slider.php',{method:'POST',body:data});const result=await response.json();if(!response.ok||!result.image)throw new Error(result.error||'Upload fehlgeschlagen.');initial.images.push(result.image);if(config.slides.includes(slide))slide.image=result.image;rerender();}
        catch(error){setStatus(error.message);}finally{uploading--;document.getElementById('slider-save').disabled=uploading>0;}
      });field('Eigenes Bild hochladen (max. 8 MB)',upload);
      const alt=make('input',null,{type:'text',value:slide.alt,maxlength:'250'});alt.addEventListener('input',()=>{slide.alt=alt.value;thumb.alt=alt.value;markDirty();refreshPreview();});field('Bildbeschreibung',alt);
      [['positionX','Bildausschnitt horizontal (%)'],['positionY','Bildausschnitt vertikal (%)']].forEach(([key,label])=>{const input=make('input',null,{type:'range',min:0,max:100,value:cropFor(slide)[key],'data-slide-position':key});input.addEventListener('input',()=>{cropFor(slide)[key]=Number(input.value);applyCrop(thumb,slide);selectSlide(i);markDirty();refreshPreview();});field(label+ (editingMobile()?' – Mobil':' – Desktop'),input);});
      list.append(card);
    });
    document.getElementById('slider-add').disabled=config.slides.length>=12;
    syncCropControls();
  };
  fields.forEach(field=>field.addEventListener('input',()=>{config[field.dataset.config]=field.type==='checkbox'?field.checked:field.type==='number'?Number(field.value):field.value;markDirty();refreshPreview();if(['autoplay','interval','transition'].includes(field.dataset.config))restartPreview();}));
  document.getElementById('slider-add').addEventListener('click',()=>{if(config.slides.length>=12)return;config.slides.push(initializeMobile(structuredClone(initial.defaults.slides[0])));selectedIndex=config.slides.length-1;markDirty();renderSlides();refreshPreview(true);restartPreview();});
  document.getElementById('slider-reset-style').addEventListener('click',()=>{const keep=['slides','eyebrow','title','accent','description','facts','primaryLabel','primaryUrl','secondaryLabel','secondaryUrl'];Object.keys(config).filter(key=>!keep.includes(key)).forEach(key=>config[key]=initial.defaults[key]);fields.forEach(field=>{if(field.type==='checkbox')field.checked=config[field.dataset.config];else field.value=config[field.dataset.config];});markDirty();refreshPreview();restartPreview();});
  const previewBox=document.getElementById('slider-preview');
  const sizePreview=()=>{const mobile=document.getElementById('slider-preview-size').value==='mobile';const width=mobile?390:1366;previewBox.toggleAttribute('data-mobile',mobile);previewBox.style.width=width+'px';previewBox.style.zoom=Math.min(1,previewBox.parentElement.clientWidth/width);};
  document.getElementById('slider-preview-size').addEventListener('change',()=>{sizePreview();renderSlides();refreshPreview();});
  new ResizeObserver(sizePreview).observe(previewBox.parentElement);
  preview.addEventListener('click',event=>{if(event.target.closest('a'))event.preventDefault();});
  form.addEventListener('submit',event=>{if(uploading){event.preventDefault();return;}document.getElementById('slider-config').value=JSON.stringify(config);dirty=false;setStatus('Wird gespeichert …');});
  window.addEventListener('beforeunload',event=>{if(dirty){event.preventDefault();event.returnValue='';}});
  const changeCrop=(values)=>{
    const slide=cropFor(config.slides[selectedIndex]);Object.assign(slide,values);
    slide.positionX=Math.max(0,Math.min(100,slide.positionX));slide.positionY=Math.max(0,Math.min(100,slide.positionY));slide.zoom=Math.max(100,Math.min(300,slide.zoom??100));
    markDirty();refreshPreview();syncCropControls();
    const card=list.children[selectedIndex];applyCrop(card.querySelector('img'),slide);
    card.querySelectorAll('[data-slide-position]').forEach(input=>input.value=slide[input.dataset.slidePosition]);
  };
  imagePicker.addEventListener('change',()=>selectSlide(Number(imagePicker.value)));
  cropX.addEventListener('input',()=>changeCrop({positionX:Number(cropX.value)}));
  cropY.addEventListener('input',()=>changeCrop({positionY:Number(cropY.value)}));
  cropZoom.addEventListener('input',()=>changeCrop({zoom:Number(cropZoom.value)}));
  document.querySelectorAll('[data-crop-move]').forEach(button=>button.addEventListener('click',()=>{
    const slide=cropFor(config.slides[selectedIndex]),delta={left:[2,0],right:[-2,0],up:[0,2],down:[0,-2]}[button.dataset.cropMove];
    changeCrop({positionX:slide.positionX+delta[0],positionY:slide.positionY+delta[1]});
  }));
  document.getElementById('slider-zoom-out').addEventListener('click',()=>changeCrop({zoom:(cropFor(config.slides[selectedIndex]).zoom??100)-5}));
  document.getElementById('slider-zoom-in').addEventListener('click',()=>changeCrop({zoom:(cropFor(config.slides[selectedIndex]).zoom??100)+5}));
  document.getElementById('slider-crop-reset').addEventListener('click',()=>changeCrop({positionX:50,positionY:50,zoom:100}));
  new MutationObserver(()=>{
    const active=[...preview.querySelectorAll('.slider-image')].findIndex(img=>img.classList.contains('is-active'));
    if(active>=0&&active!==selectedIndex){selectedIndex=active;syncCropControls();}
  }).observe(preview.querySelector('.slider-images'),{subtree:true,attributes:true,attributeFilter:['class']});
  renderSlides();refreshPreview();restartPreview();sizePreview();
})();
