const {request}=require('C:/Users/Nedim/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict');
(async()=>{
 const base='http://127.0.0.1:8099';const anon=await request.newContext({baseURL:base});
 for(const url of ['/api/finance.php','/api/finance-document.php?id=1','/api/finance-export.php','/api/finance-protocols.php','/api/finance-protocols.php?action=file&id=1','/api/finance-protocols.php?action=pdf&id=1'])assert.equal((await anon.get(url)).status(),401,url+' must reject anonymous');
 const ctx=await request.newContext({baseURL:base});const login=await ctx.get('/login.php');const html=await login.text();const token=html.match(/name="csrf_token" value="([^"]+)"/)[1];
 const signed=await ctx.post('/login.php',{form:{email:'finance-admin@example.invalid',password:'Finance-Test-Only-2026!',csrf_token:token}});assert.equal(signed.status(),200);assert.match(await signed.text(),/Finance Test Admin/);
 let response=await ctx.get('/api/finance.php');assert.equal(response.status(),200,await response.text());const report=await response.json();assert.ok(report.reservations.some(r=>r.customer_name==='Musterkunde'));
 if(!report.all_invoices.length){
  const post=async data=>{const r=await ctx.post('/api/finance.php',{data:{csrf_token:token,...data}});assert.equal(r.status(),200,await r.text());return r.json();};
  await post({action:'settings',company_name:'Testfirma',address:'Teststrasse 1',email:'sender@example.invalid',iban:'',tax_number:''});
  await post({action:'invoice',reservation_id:1,due_date:'2026-12-31',customer_address:'Musterstrasse 2',items:[{description:'Testmiete',quantity:1,unit_price:'150.00',tax_rate:'0'}],notes:'Nur Test'});
 }
 const guarded=await ctx.patch('/api/reservations.php',{data:{id:1,status:'bestätigt',paymentStatus:'bezahlt'}});assert.equal(guarded.status(),409,await guarded.text());
 assert.equal((await ctx.post('/api/finance.php',{data:{action:'settings',company_name:'Wrong'}})).status(),419);
 assert.equal((await ctx.post('/api/finance.php',{data:{csrf_token:token,action:'invoice',reservation_id:1,items:[]}})).status(),422);
 const customer=await request.newContext({baseURL:base});const chtml=await(await customer.get('/login.php')).text();const ct=chtml.match(/name="csrf_token" value="([^"]+)"/)[1];await customer.post('/login.php',{form:{email:'finance-customer@example.invalid',password:'Finance-Test-Only-2026!',csrf_token:ct}});
 for(const url of ['/api/finance.php','/api/finance-document.php?id=1','/api/finance-export.php','/api/finance-protocols.php','/api/finance-protocols.php?action=file&id=1','/api/finance-protocols.php?action=pdf&id=1'])assert.equal((await customer.get(url)).status(),403,url+' must reject customer');
 await anon.dispose();await ctx.dispose();await customer.dispose();console.log('PASS HTTP admin/customer/anonymous permissions, CSRF and invalid invoices');
})().catch(e=>{console.error(e);process.exit(1)});
