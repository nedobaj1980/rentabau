const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync(require('node:path').join(__dirname,'../assets/js/slider.js'),'utf8');

function setup({autoplay=false,reduced=false,count=3}={}) {
  class Element extends EventTarget {
    constructor(){super();this.dataset={};this.attrs={};this.hidden=false;this.textContent='';this.classes=new Set();this.classList={toggle:(key,on)=>on?this.classes.add(key):this.classes.delete(key)};}
    setAttribute(k,v){this.attrs[k]=v;}
    matches(){return false;}
    contains(){return false;}
    closest(){return null;}
  }
  const root=new Element(), doc=new Element(), motion=new Element();
  const images=Array.from({length:count},()=>new Element()), dots=Array.from({length:count},()=>new Element());
  const controls=Object.fromEntries(['prev','next','pause','status'].map(name=>[name,new Element()]));
  root.dataset={autoplay:String(autoplay),interval:6};
  root.querySelectorAll=selector=>selector==='.slider-image'?images:dots;
  root.querySelector=selector=>controls[selector.match(/slider-(\w+)/)[1]];
  doc.querySelectorAll=()=>[root];doc.hidden=false;
  motion.matches=reduced;
  const timers=new Map();let serial=0;
  const context={window:{},document:doc,matchMedia:()=>motion,AbortController,setInterval:fn=>{timers.set(++serial,fn);return serial;},clearInterval:id=>timers.delete(id),setTimeout:fn=>fn()};
  vm.runInNewContext(source,context);
  const fire=(element,type,props={})=>{const event=new Event(type,{cancelable:true});Object.entries(props).forEach(([key,value])=>Object.defineProperty(event,key,{value}));element.dispatchEvent(event);};
  const active=()=>images.findIndex(img=>img.classes.has('is-active'));
  return {root,doc,motion,images,dots,controls,timers,fire,active,init:()=>context.window.initHeroSlider(root)};
}
test('manual controls wrap, update accessible state and never start a timer',()=>{
  const s=setup();assert.equal(s.active(),0);s.fire(s.controls.prev,'click');assert.equal(s.active(),2);assert.equal(s.dots[2].attrs['aria-pressed'],'true');assert.equal(s.controls.status.textContent,'Bild 3 von 3');s.fire(s.controls.next,'click');assert.equal(s.active(),0);assert.equal(s.timers.size,0);
});
test('horizontal touch swipe changes image, vertical scroll does not',()=>{
  const s=setup();s.fire(s.root,'pointerdown',{pointerType:'touch',clientX:200,clientY:100});s.fire(s.root,'pointerup',{clientX:100,clientY:110});assert.equal(s.active(),1);
  s.fire(s.root,'pointerdown',{pointerType:'touch',clientX:200,clientY:100});s.fire(s.root,'pointerup',{clientX:190,clientY:250});assert.equal(s.active(),1);
});
test('autoplay pauses, respects reduced motion and cleans up on reinitialisation',()=>{
  const s=setup({autoplay:true});assert.equal(s.timers.size,1);[...s.timers.values()][0]();assert.equal(s.active(),1);s.fire(s.controls.pause,'click');assert.equal(s.timers.size,0);s.fire(s.controls.pause,'click');assert.equal(s.timers.size,1);s.init();assert.equal(s.timers.size,1);s.root._sliderCleanup();assert.equal(s.timers.size,0);
  assert.equal(setup({autoplay:true,reduced:true}).timers.size,0);assert.equal(setup({autoplay:true,count:1}).timers.size,0);
});
