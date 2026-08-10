<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nhập / xuất nhanh — QMS</title>
<style>
:root{--bg:#f4f7fb;--line:#dfe6ee;--line2:#edf1f5;--text:#1e293b;--muted:#64748b;
 --primary:#0f6b7a;--primary2:#0b5662;--soft:#e8f5f7;
 --green:#15803d;--green-soft:#ecfdf3;--amber:#a16207;--amber-soft:#fff8e6;
 --red:#b42318;--red-soft:#fff1f0;--blue:#1d4ed8;--blue-soft:#eef4ff}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 Inter,"Segoe UI",system-ui,sans-serif}
button,input,select,textarea{font:inherit}button{cursor:pointer}
.shell{padding:16px 20px 40px;max-width:1640px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:4px}.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:center;gap:14px;margin-bottom:10px}
.head h1{font-size:21px;margin:0}.head p{margin:2px 0 0;color:var(--muted);font-size:11px}
.head .right{margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.btn{height:33px;padding:0 12px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;
 font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none}
.btn:hover{border-color:#bdc8d4}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}.btn.primary:disabled{opacity:.45;cursor:not-allowed}
.btn.sm{height:27px;padding:0 8px;font-size:10px;border-radius:7px}
.btn.ghost{background:transparent;border-color:transparent;color:var(--muted)}
/* chọn loại phát sinh */
.modes{display:flex;gap:6px;background:#e8eef4;padding:4px;border-radius:11px}
.mode{height:30px;padding:0 14px;border:0;background:transparent;border-radius:8px;font-size:11.5px;font-weight:800;color:#5b6b7d}
.mode.on{background:#fff;box-shadow:0 2px 7px rgba(15,23,42,.1)}
.mode.on[data-t="import"]{color:var(--green)}.mode.on[data-t="export"]{color:var(--primary)}
.mode.on[data-t="destroy"]{color:var(--red)}
.layout{display:grid;grid-template-columns:1fr 380px;gap:12px;align-items:start}
.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.filters{padding:10px 12px;border-bottom:1px solid var(--line);background:#fbfcfd;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.filters input,.filters select{height:33px;border:1px solid var(--line);border-radius:8px;padding:0 10px;font-size:11px;background:#fff}
.filters .search{flex:1 1 240px;min-width:180px}
.filters .qs2{width:180px;flex:0 0 180px}
.chips{display:flex;gap:6px;flex-wrap:wrap;padding:9px 12px;border-bottom:1px solid var(--line2)}
.chip{height:26px;padding:0 11px;border:1px solid var(--line);background:#fff;border-radius:999px;font-size:10.5px;font-weight:750;color:var(--muted)}
.chip.on{background:var(--primary);border-color:var(--primary);color:#fff}
/* lưới thẻ hàng */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(212px,1fr));gap:10px;padding:12px;
 max-height:calc(100vh - 250px);overflow:auto}
.card{border:1px solid var(--line);border-radius:12px;padding:11px;background:#fff;position:relative;
 display:flex;flex-direction:column;gap:5px;cursor:pointer;transition:border-color .12s,box-shadow .12s}
.card:hover{border-color:#a9c6cc;box-shadow:0 4px 14px rgba(15,23,42,.07)}
.card.on{border-color:var(--primary);box-shadow:0 0 0 2px #cfe8ec}
.card .thumb{width:100%;height:104px;border-radius:9px;background:#eef3f7 center/cover no-repeat;
 display:grid;place-items:center;color:#93a6b8;font-size:20px;font-weight:800;margin-bottom:2px;position:relative;overflow:hidden}
.card .thumb .cam{position:absolute;right:5px;bottom:5px;width:22px;height:22px;border-radius:50%;border:0;
 background:rgba(255,255,255,.92);font-size:11px;line-height:1;display:grid;place-items:center;box-shadow:0 2px 6px rgba(15,23,42,.2)}
.card .code{font-size:12px;font-weight:800;letter-spacing:-.01em}
.card .name{font-size:10.5px;color:#425466;line-height:1.35;
 display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:28px}
.card .meta{font-size:9px;color:var(--muted)}
.card .row{display:flex;align-items:center;gap:6px;margin-top:auto;padding-top:5px}
.card .bal{font-size:14px;font-weight:800;font-variant-numeric:tabular-nums}
.card .bal small{font-size:9px;font-weight:700;color:var(--muted);margin-left:2px}
.add{margin-left:auto;width:30px;height:30px;border-radius:50%;border:0;background:var(--primary);color:#fff;
 font-size:16px;line-height:1;display:grid;place-items:center;box-shadow:0 3px 9px rgba(15,107,122,.28)}
.card.on .add{background:var(--green)}
.qty-pill{margin-left:auto;display:inline-flex;align-items:center;gap:5px;background:var(--soft);
 border:1px solid #bfe0e5;border-radius:999px;padding:2px 4px}
.qty-pill button{width:22px;height:22px;border:0;border-radius:50%;background:#fff;color:var(--primary);font-weight:800;font-size:13px;line-height:1}
.qty-pill input{width:46px;border:0;background:transparent;text-align:center;font-size:11.5px;font-weight:800;
 font-variant-numeric:tabular-nums;color:var(--primary)}
.badge{display:inline-flex;padding:2px 7px;border-radius:999px;font-size:8.5px;font-weight:800;white-space:nowrap}
.badge.ok{background:var(--green-soft);color:var(--green)}.badge.low{background:var(--amber-soft);color:var(--amber)}
.badge.out{background:var(--red-soft);color:var(--red)}.badge.high{background:var(--blue-soft);color:var(--blue)}
.empty{grid-column:1/-1;text-align:center;padding:44px;color:var(--muted);font-size:11px}
/* giỏ */
.cart{position:sticky;top:14px}
.cart-head{padding:12px 14px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:8px}
.cart-head h2{font-size:13px;margin:0}.cart-head .n{margin-left:auto;font-size:10px;color:var(--muted)}
.cart-form{padding:11px 14px;border-bottom:1px solid var(--line2);display:grid;gap:9px}
.f{display:grid;gap:4px}.f label{font-size:9px;font-weight:800;color:#475569}
.f input,.f select,.f textarea{border:1px solid var(--line);border-radius:8px;padding:8px 9px;font-size:11px;background:#fff;width:100%}
.f textarea{min-height:46px;resize:vertical}
.f2{display:grid;grid-template-columns:1fr 1fr;gap:9px}
.who{background:#f6fafb;border:1px dashed #c5dde2;border-radius:9px;padding:8px 10px;font-size:9.5px;color:#4d6a71}
.lines{max-height:min(42vh,380px);overflow:auto}
.line{padding:9px 14px;border-bottom:1px solid var(--line2);display:grid;gap:6px}
.line-top{display:flex;align-items:center;gap:8px}
.line-top .c{font-size:11px;font-weight:800}.line-top .u{font-size:9px;color:var(--muted)}
.line-top .x{margin-left:auto;border:0;background:transparent;color:#b42318;font-size:13px;line-height:1;padding:2px 4px}
.line-sub{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.line-sub input,.line-sub select{height:29px;border:1px solid var(--line);border-radius:7px;padding:0 8px;font-size:10.5px;background:#fff}
.line-sub .b{flex:1 1 120px;min-width:110px}
.warn{color:var(--red);font-size:9.5px;font-weight:700}
.cart-foot{padding:12px 14px;display:grid;gap:8px;background:#fbfcfd;border-top:1px solid var(--line)}
.sum{display:flex;justify-content:space-between;font-size:11px;color:var(--muted)}
.sum b{color:var(--text);font-size:13px}
.cart-empty{padding:34px 14px;text-align:center;color:var(--muted);font-size:11px}
.recent{margin-top:12px}
.recent h3{font-size:11px;margin:0 0 6px;color:var(--muted);font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.rec{display:flex;gap:8px;align-items:center;padding:6px 0;border-bottom:1px solid var(--line2);font-size:10px}
.rec .t{width:52px;font-weight:800}.rec .t.import{color:var(--green)}.rec .t.export{color:var(--primary)}.rec .t.destroy{color:var(--red)}
.rec .n{margin-left:auto;color:var(--muted);font-size:9px}
/* popup tạo thẻ kho nhanh */
.mbg{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;padding:16px;z-index:100}
.mbg.show{display:flex}
.mdl{width:min(620px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:15px;box-shadow:0 18px 55px rgba(15,23,42,.2)}
.mdl-h{padding:14px 17px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}
.mdl-h h2{font-size:14px;margin:0 0 3px}.mdl-h p{margin:0;font-size:9.5px;color:var(--muted)}
.mdl-h .x{width:28px;height:28px;border:0;border-radius:8px;background:#eef2f6}
.mdl-b{padding:15px 17px;display:grid;grid-template-columns:150px 1fr;gap:13px}
.mdl-f{padding:12px 17px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.pick-img{border:1px dashed #b9cbd6;border-radius:11px;background:#f8fbfc center/cover no-repeat;
 aspect-ratio:1/1;display:grid;place-items:center;text-align:center;color:#6c8595;font-size:10px;padding:8px;cursor:pointer}
.pick-img span{display:block;font-size:22px;margin-bottom:3px}
.qgrid{display:grid;grid-template-columns:1fr 1fr;gap:10px;align-content:start}
.qgrid .f.full{grid-column:1/-1}
@media(max-width:650px){.mdl-b{grid-template-columns:1fr}.qgrid{grid-template-columns:1fr}}
.toast-wrap{position:fixed;right:18px;bottom:18px;display:grid;gap:8px;z-index:130}
.toast{background:#18333d;color:#fff;padding:10px 13px;border-radius:9px;font-size:10.5px;max-width:330px}
.toast.err{background:#8a1f16}
@media(max-width:1150px){.layout{grid-template-columns:1fr}.cart{position:static}
 .grid{max-height:none}.lines{max-height:none}}
@media(max-width:650px){.shell{padding:12px 10px 30px}.head{flex-wrap:wrap}.head .right{width:100%}
 .grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));padding:9px;gap:8px}.f2{grid-template-columns:1fr}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script src="{{ asset('js/qms-select.js') }}?v=3"></script>
<script src="{{ asset('js/qms-date.js') }}?v=1"></script>
<script>window.QMS_ENTRY={state:"{{ route('entry.state') }}",save:"{{ route('entry.save') }}",
 card:"{{ route('stock.page') }}",quick:"{{ route('entry.quick') }}",
 image:"{{ route('item.image.set', ['product' => '__ID__']) }}",csrf:"{{ csrf_token() }}"};</script>
</head>
<body>
@include('modules._sidebar')

<div class="shell">
  <div class="breadcrumb">Kho vật tư › <b>Nhập / xuất nhanh</b></div>
  <div class="head">
    <div>
      <h1>Nhập / xuất nhanh</h1>
      <p>Chọn thẻ hàng cần ghi rồi nhập số lượng — hệ thống tự ghi vào thẻ kho của từng mã.</p>
    </div>
    <div class="right">
      <div class="modes" id="modes">
        <button class="mode on" data-t="import" onclick="setMode('import')">＋ Nhập kho</button>
        <button class="mode" data-t="export" onclick="setMode('export')">− Xuất kho</button>
        <button class="mode" data-t="destroy" onclick="setMode('destroy')">Hủy / quá hạn</button>
      </div>
      <a class="btn" href="{{ route('stock.page') }}">🏷 Thẻ kho</a>
    </div>
  </div>

  <div class="layout">
    <div class="panel">
      <div class="filters">
        <input class="search" id="q" placeholder="Tìm mã hàng hoặc tên hàng hóa...">
        <select id="fStatus">
          <option value="">Tất cả tồn kho</option>
          <option value="ok">Còn hàng</option>
          <option value="low">Sắp hết</option>
          <option value="out">Hết hàng</option>
          <option value="high">Vượt tối đa</option>
        </select>
        <button class="btn sm" onclick="clearAll()">Bỏ chọn hết</button>
        <button class="btn sm primary" onclick="openQuick()">＋ Tạo thẻ kho</button>
      </div>
      <div class="chips" id="chips"></div>
      <div class="grid" id="grid"></div>
    </div>

    <div class="panel cart">
      <div class="cart-head"><h2 id="cartTitle">Phiếu nhập kho</h2><span class="n" id="cartCount"></span></div>
      <div class="cart-form">
        <div class="f2">
          <div class="f"><label>Ngày phát sinh *</label><input type="date" id="eDate"></div>
          <div class="f" id="destWrap"><label>Nơi nhận</label><input id="eDest" placeholder="Bộ phận sử dụng"></div>
        </div>
        <div class="f2">
          <div class="f"><label>Người giao</label><input id="eDeliver" list="staffList"></div>
          <div class="f"><label>Người nhận</label><input id="eReceive" list="staffList"></div>
        </div>
        <datalist id="staffList"></datalist>
        <div class="who" id="whoNote"></div>
        <div class="f"><label>Ghi chú chung</label><textarea id="eNote" placeholder="Không bắt buộc"></textarea></div>
      </div>
      <div class="lines" id="lines"></div>
      <div class="cart-foot">
        <div class="sum"><span>Số mã hàng đã chọn</span><b id="sumItems">0</b></div>
        <div class="sum"><span>Tổng số lượng</span><b id="sumQty">0</b></div>
        <button class="btn primary" id="btnSave" onclick="submitEntry()" disabled>Lưu phát sinh</button>
      </div>
    </div>
  </div>

  <div class="recent panel" style="padding:12px 14px">
    <h3>Phát sinh gần đây</h3>
    <div id="recent"></div>
  </div>
</div>

<div class="mbg" id="quickModal"><div class="mdl">
  <div class="mdl-h"><div><h2>Tạo thẻ kho nhanh</h2>
    <p>Nhập thông tin cơ bản — hệ thống tự cấp số thẻ kho cho mã hàng này.</p></div>
    <button class="x" onclick="closeQuick()">×</button></div>
  <div class="mdl-b">
    <div>
      <div class="pick-img" id="qImgBox" onclick="document.getElementById('qImg').click()">
        <span>📷</span>Chọn ảnh<br>(không bắt buộc)</div>
      <input type="file" id="qImg" accept="image/*" style="display:none" onchange="previewImg(this)">
      <button class="btn sm" style="width:100%;margin-top:7px;justify-content:center" onclick="clearImg()">Bỏ ảnh</button>
    </div>
    <div class="qgrid">
      <div class="f"><label>Mã hàng *</label><input id="qCode" placeholder="VD: 994646"></div>
      <div class="f"><label>Đơn vị tính *</label><input id="qUnit" placeholder="Kg, Cái, Chai..."></div>
      <div class="f full"><label>Tên hàng hóa *</label><input id="qName"></div>
      <div class="f"><label>Nhóm hàng</label><input id="qGroup" list="groupList2"><datalist id="groupList2"></datalist></div>
      <div class="f"><label>Quy cách</label><input id="qPacking" placeholder="1 Kg/Túi"></div>
      <div class="f full"><label>Hạn hóa chất / vật tư</label><input id="qExpiry" type="date"></div>
      <div class="f"><label>Tồn tối thiểu</label><input id="qMin" type="number" min="0" step="0.01" value="0"></div>
      <div class="f"><label>Tồn tối đa</label><input id="qMax" type="number" min="0" step="0.01" value="0"></div>
    </div>
  </div>
  <div class="mdl-f"><button class="btn" onclick="closeQuick()">Hủy</button>
    <button class="btn primary" id="qSave" onclick="saveQuick()">Tạo thẻ kho</button></div>
</div></div>

<input type="file" id="imgPicker" accept="image/*" style="display:none">

<div class="toast-wrap" id="toastWrap"></div>

<script>
let D={items:[],staff:[],recent:[]},mode='import',cart={},group='';
const $=id=>document.getElementById(id);
const fmt=n=>new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(Number(n||0));
const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const viDate=s=>s?s.slice(8,10)+'/'+s.slice(5,7)+'/'+s.slice(0,4):'';
const ST={ok:['ok','Còn hàng'],low:['low','Sắp hết'],out:['out','Hết hàng'],high:['high','Vượt tối đa']};
const TYPE={import:'nhập kho',export:'xuất kho',destroy:'hủy / quá hạn'};
function toast(m,err){const e=document.createElement('div');e.className='toast'+(err?' err':'');e.innerHTML=m;
  $('toastWrap').appendChild(e);setTimeout(()=>e.remove(),err?5200:2800)}

function setMode(m){
  mode=m;cart={};
  document.querySelectorAll('.mode').forEach(b=>b.classList.toggle('on',b.dataset.t===m));
  $('cartTitle').textContent='Phiếu '+TYPE[m];
  $('destWrap').style.display=m==='import'?'none':'grid';
  fillWho();render();
}
/** Người đăng nhập tự điền vào cột phù hợp: nhập kho -> người nhận, xuất/hủy -> người giao. */
function fillWho(){
  const me=D.me||'';
  if(mode==='import'){$('eReceive').value=me;$('eDeliver').value=$('eDeliver').value||'';
    $('whoNote').innerHTML=`Bạn đang nhập kho → tên bạn (<b>${esc(me)}</b>) được ghi vào cột <b>Người nhận</b> của thẻ kho.`}
  else{$('eDeliver').value=me;
    $('whoNote').innerHTML=`Bạn đang ${TYPE[mode]} → tên bạn (<b>${esc(me)}</b>) được ghi vào cột <b>Người giao</b> của thẻ kho.`}
}
function filtered(){
  const q=$('q').value.trim().toLowerCase(),st=$('fStatus').value;
  return D.items.filter(x=>(!q||(x.code+' '+x.name).toLowerCase().includes(q))
    &&(!group||x.group===group)&&(!st||x.status===st));
}
function renderChips(){
  const gs=D.groups||[];
  $('chips').innerHTML=[`<button class="chip ${group?'':'on'}" onclick="setGroup('')">Tất cả (${D.items.length})</button>`]
    .concat(gs.map(g=>`<button class="chip ${group===g?'on':''}" onclick="setGroup('${esc(g)}')">${esc(g)}</button>`)).join('');
  $('chips').style.display=gs.length?'flex':'none';
}
function setGroup(g){group=g;renderChips();renderGrid()}

function renderGrid(){
  const rows=filtered();
  $('grid').innerHTML=rows.length?rows.map(x=>{
    const s=ST[x.status],c=cart[x.id];
    return `<div class="card ${c?'on':''}" onclick="pick('${x.id}',event)">
      <div class="thumb" style="${x.image?`background-image:url('${x.image}')`:''}">${x.image?'':esc(x.code.slice(0,2).toUpperCase())}
        <button class="cam" title="Chọn ảnh cho mã hàng" onclick="event.stopPropagation();pickImage('${x.id}')">📷</button></div>
      <div class="code">${esc(x.code)}</div>
      <div class="name">${esc(x.name)}</div>
      <div class="meta">${esc(x.unit)}${x.packing?' · '+esc(x.packing):''} · <span class="badge ${s[0]}">${s[1]}</span></div>
      <div class="row"><span class="bal">${fmt(x.balance)}<small>${esc(x.unit)}</small></span>
        ${c?`<span class="qty-pill" onclick="event.stopPropagation()">
             <button onclick="bump('${x.id}',-1)">−</button>
             <input type="text" inputmode="decimal" value="${c.qty}" onchange="setQty('${x.id}',this.value)" onclick="this.select()">
             <button onclick="bump('${x.id}',1)">＋</button></span>`
          :`<button class="add" title="Chọn mã này">＋</button>`}
      </div></div>`}).join('')
    :'<div class="empty">Không có mã hàng phù hợp.</div>';
}
function pick(id,e){
  if(e&&e.target.closest('.qty-pill'))return;
  if(cart[id]){delete cart[id]}else{
    const it=D.items.find(x=>x.id===id);
    const b=(it.batches||[])[0];
    cart[id]={qty:1,batch:mode==='import'?'':(b?b.batch:''),expiry:mode==='import'?'':(b?b.expiry:'')};
  }
  render();
}
function bump(id,d){const c=cart[id];if(!c)return;c.qty=Math.max(0.01,Math.round((Number(c.qty)+d)*100)/100);render()}
function setQty(id,v){const c=cart[id];if(!c)return;const n=Number(String(v).replace(',','.'));c.qty=n>0?n:1;render()}
function setLine(id,k,v){const c=cart[id];if(!c)return;c[k]=v;
  if(k==='batch'){const it=D.items.find(x=>x.id===id);const b=(it.batches||[]).find(x=>x.batch===v);if(b)c.expiry=b.expiry}
  renderCart()}
function clearAll(){cart={};render()}

function renderCart(){
  const ids=Object.keys(cart);
  $('cartCount').textContent=ids.length?`${ids.length} mã hàng`:'';
  if(!ids.length){$('lines').innerHTML='<div class="cart-empty">Chưa chọn mã hàng nào.<br>Bấm vào thẻ hàng bên trái để thêm.</div>'}
  else{
    $('lines').innerHTML=ids.map(id=>{
      const it=D.items.find(x=>x.id===id),c=cart[id];
      const over=(mode!=='import')&&Number(c.qty)>it.balance;
      const bs=it.batches||[];
      let sub='';
      if(mode==='import'){
        sub=`<input class="b" placeholder="Số lô" value="${esc(c.batch||'')}" onchange="setLine('${id}','batch',this.value)">
             <input type="date" value="${esc(c.expiry||'')}" onchange="setLine('${id}','expiry',this.value)" title="Hạn sử dụng">`;
      }else if(bs.length){
        sub=`<select class="b" onchange="setLine('${id}','batch',this.value)" data-no-qs2>
              ${bs.map(b=>`<option value="${esc(b.batch)}" ${b.batch===c.batch?'selected':''}>Lô ${esc(b.batch)} · còn ${fmt(b.qty)}${b.expiry?' · HSD '+viDate(b.expiry):''}</option>`).join('')}
             </select>`;
      }else{sub='<span class="u">Không có số lô</span>'}
      return `<div class="line">
        <div class="line-top"><span class="c">${esc(it.code)}</span>
          <span class="u">${esc(it.name).slice(0,26)}${it.name.length>26?'…':''}</span>
          <button class="x" onclick="delete cart['${id}'];render()" title="Bỏ">✕</button></div>
        <div class="line-sub">
          <span class="qty-pill"><button onclick="bump('${id}',-1)">−</button>
            <input type="text" inputmode="decimal" value="${c.qty}" onchange="setQty('${id}',this.value)" onclick="this.select()">
            <button onclick="bump('${id}',1)">＋</button></span>
          <span class="u">${esc(it.unit)} · tồn ${fmt(it.balance)}</span>${sub}
        </div>
        ${over?`<div class="warn">Vượt tồn hiện có (${fmt(it.balance)} ${esc(it.unit)})</div>`:''}</div>`;
    }).join('');
  }
  const tong=ids.reduce((s,id)=>s+Number(cart[id].qty||0),0);
  $('sumItems').textContent=ids.length;$('sumQty').textContent=fmt(tong);
  const bad=ids.some(id=>{const it=D.items.find(x=>x.id===id);return mode!=='import'&&Number(cart[id].qty)>it.balance});
  $('btnSave').disabled=!ids.length||bad;
  $('btnSave').textContent=ids.length?`Lưu ${ids.length} phát sinh ${TYPE[mode]}`:'Lưu phát sinh';
}
function renderRecent(){
  $('recent').innerHTML=(D.recent||[]).length?D.recent.map(r=>`<div class="rec">
    <span class="t ${r.type}">${r.type==='import'?'Nhập':r.type==='export'?'Xuất':'Hủy'}</span>
    <span><b>${esc(r.code)}</b> ${fmt(r.qty)} ${esc(r.unit||'')}</span>
    <span class="n">${viDate(r.date)}${r.by?' · '+esc(r.by):''}</span></div>`).join('')
    :'<div class="cart-empty">Chưa có phát sinh nào.</div>';
}
function render(){renderGrid();renderCart()}

/* ==== tạo thẻ kho nhanh + ảnh mã hàng ==== */
function openQuick(){
  ['qCode','qName','qUnit','qGroup','qPacking','qExpiry'].forEach(i=>$(i).value='');
  $('qMin').value=0;$('qMax').value=0;clearImg();
  $('groupList2').innerHTML=(D.groups||[]).map(g=>`<option value="${esc(g)}">`).join('');
  $('quickModal').classList.add('show');setTimeout(()=>$('qCode').focus(),60);
}
function closeQuick(){$('quickModal').classList.remove('show')}
function previewImg(inp){
  const f=inp.files&&inp.files[0];if(!f)return;
  $('qImgBox').style.backgroundImage=`url('${URL.createObjectURL(f)}')`;$('qImgBox').innerHTML='';
}
function clearImg(){$('qImg').value='';$('qImgBox').style.backgroundImage='';
  $('qImgBox').innerHTML='<span>📷</span>Chọn ảnh<br>(không bắt buộc)'}
async function saveQuick(){
  const code=$('qCode').value.trim(),name=$('qName').value.trim(),unit=$('qUnit').value.trim();
  if(!code||!name||!unit)return toast('Nhập đủ mã hàng, tên và đơn vị tính',true);
  const fd=new FormData();
  fd.append('code',code);fd.append('name',name);fd.append('unit',unit);
  fd.append('group',$('qGroup').value.trim());fd.append('packing',$('qPacking').value.trim());
  if($('qExpiry').value)fd.append('expiry',$('qExpiry').value);
  fd.append('min',$('qMin').value||0);fd.append('max',$('qMax').value||0);
  if($('qImg').files[0])fd.append('image',$('qImg').files[0]);
  $('qSave').disabled=true;
  try{
    const r=await fetch(window.QMS_ENTRY.quick,{method:'POST',credentials:'same-origin',
      headers:{'X-CSRF-TOKEN':window.QMS_ENTRY.csrf,'Accept':'application/json'},body:fd});
    const j=await r.json().catch(()=>({}));
    if(!r.ok){toast((j.errors||['Tạo thất bại']).join('<br>'),true);return}
    closeQuick();await load();
    toast(`Đã tạo thẻ kho <b>${esc(j.cardNo)}</b> cho mã ${esc(code)}`);
  }catch(e){toast('Tạo thất bại: '+e.message,true)}finally{$('qSave').disabled=false}
}
/** Chọn / đổi ảnh cho mã hàng đã có. */
function pickImage(id){
  const inp=$('imgPicker');inp.value='';
  inp.onchange=async()=>{
    const f=inp.files&&inp.files[0];if(!f)return;
    const fd=new FormData();fd.append('image',f);
    const it=D.items.find(x=>x.id===id);
    try{
      const r=await fetch(window.QMS_ENTRY.image.replace('__ID__',encodeURIComponent(id)),
        {method:'POST',credentials:'same-origin',
         headers:{'X-CSRF-TOKEN':window.QMS_ENTRY.csrf,'Accept':'application/json'},body:fd});
      if(!r.ok)throw new Error('HTTP '+r.status);
      await load();toast(`Đã cập nhật ảnh cho ${esc(it?it.code:'')}`);
    }catch(e){toast('Không lưu được ảnh: '+e.message,true)}
  };
  inp.click();
}

async function submitEntry(){
  const lines=Object.entries(cart).map(([id,c])=>({id,qty:Number(c.qty),batch:c.batch||'',expiry:c.expiry||''}));
  if(!lines.length)return;
  if(!$('eDate').value)return toast('Chọn ngày phát sinh',true);
  const body={type:mode,date:$('eDate').value,deliverer:$('eDeliver').value.trim(),
    receiver:$('eReceive').value.trim(),destination:$('eDest').value.trim(),note:$('eNote').value.trim(),lines};
  $('btnSave').disabled=true;
  try{
    const r=await fetch(window.QMS_ENTRY.save,{method:'POST',credentials:'same-origin',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_ENTRY.csrf,'Accept':'application/json'},
      body:JSON.stringify(body)});
    const j=await r.json().catch(()=>({}));
    if(!r.ok){toast((j.errors||['Lưu thất bại (HTTP '+r.status+')']).join('<br>'),true);$('btnSave').disabled=false;return}
    const n=j.saved;cart={};$('eNote').value='';
    await load();
    toast(`Đã ghi ${n} phát sinh ${TYPE[mode]} vào thẻ kho`);
  }catch(e){toast('Lưu thất bại: '+e.message,true);$('btnSave').disabled=false}
}
async function load(){
  const r=await fetch(window.QMS_ENTRY.state,{credentials:'same-origin'});
  if(!r.ok)throw new Error('Không tải được dữ liệu');
  D=await r.json();
  if(!$('eDate').value)$('eDate').value=D.today;
  $('staffList').innerHTML=(D.staff||[]).map(s=>`<option value="${esc(s)}">`).join('');
  renderChips();render();renderRecent();fillWho();
}
$('quickModal').addEventListener('click',e=>{if(e.target.id==='quickModal')closeQuick()});
['q','fStatus'].forEach(id=>$(id).addEventListener(id==='q'?'input':'change',renderGrid));
(async()=>{try{await load();QMSSelect.auto();QMSDate.auto()}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();
</script>
</body>
</html>
