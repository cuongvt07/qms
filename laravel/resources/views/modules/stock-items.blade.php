<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quản lý mã hàng — QMS</title>
<style>
:root{--bg:#f4f7fb;--panel:#fff;--line:#dfe6ee;--line2:#edf1f5;--text:#1e293b;--muted:#64748b;
 --primary:#0f6b7a;--primary2:#0b5662;--soft:#e8f5f7;--green:#15803d;--green-soft:#ecfdf3;
 --amber:#a16207;--amber-soft:#fff8e6;--red:#b42318;--red-soft:#fff1f0;--blue:#1d4ed8;--blue-soft:#eef4ff}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 Inter,"Segoe UI",system-ui,sans-serif}
button,input,select,textarea{font:inherit}button{cursor:pointer}
.shell{padding:18px 22px 40px;max-width:1560px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:4px}.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:center;gap:14px;margin-bottom:12px}
.head h1{font-size:21px;margin:0}.head p{margin:2px 0 0;color:var(--muted);font-size:11px}
.head .right{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.btn{height:33px;padding:0 12px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;
 font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.btn:hover{border-color:#bdc8d4}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}.btn.sm{height:28px;padding:0 9px;font-size:10px;border-radius:7px}
.btn.red{background:#fff;color:var(--red);border-color:#f1c7c3}
.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.filters{padding:11px 12px;border-bottom:1px solid var(--line);background:#fbfcfd;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.filters input,.filters select{height:33px;border:1px solid var(--line);border-radius:8px;padding:0 10px;font-size:11px;background:#fff}
.filters .search{width:258px;flex:0 0 258px}
.filters .qs2{width:166px;flex:0 0 166px}
.filters>select{width:166px}
.meta{margin-left:auto;font-size:10px;color:var(--muted)}
.table-wrap{overflow:auto;max-height:calc(100vh - 300px)}
table{width:100%;border-collapse:separate;border-spacing:0;min-width:1380px}
th{position:sticky;top:0;z-index:3;background:#f8fafc;padding:9px 10px;border-bottom:1px solid var(--line);
 text-align:left;font-size:8.5px;text-transform:uppercase;letter-spacing:.05em;color:#728197;white-space:nowrap}
td{padding:7px 10px;border-bottom:1px solid var(--line2);font-size:10.5px;vertical-align:middle;background:#fff}
tbody tr:hover td{background:#fbfdfe}
.num{text-align:right;font-variant-numeric:tabular-nums}
.main{font-weight:800}.sub{font-size:8.7px;color:var(--muted);margin-top:2px}
.badge{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:8.5px;font-weight:800;white-space:nowrap}
.badge.ok{background:var(--green-soft);color:var(--green)}.badge.low{background:var(--amber-soft);color:var(--amber)}
.badge.out{background:var(--red-soft);color:var(--red)}.badge.high{background:var(--blue-soft);color:var(--blue)}
.badge.off{background:#f1f5f9;color:#64748b}
.badge.exp{background:var(--red-soft);color:var(--red)}.badge.soon{background:var(--amber-soft);color:var(--amber)}
.badge.far{background:var(--green-soft);color:var(--green)}
.exp-cell{white-space:nowrap}.exp-cell .d{font-variant-numeric:tabular-nums;font-weight:800}
.exp-cell .badge{margin-top:2px}
.card-link{display:inline-flex;align-items:center;gap:5px;font-weight:800;color:var(--primary);
 background:var(--soft);border:1px solid #cbe6ea;border-radius:7px;padding:4px 8px;font-size:9.5px;text-decoration:none}
.card-link:hover{background:#d8eef1}
.row-actions{display:flex;gap:5px;white-space:nowrap}
.empty{text-align:center;padding:50px;color:var(--muted)}
.foot{padding:10px 12px;background:#fbfcfd;border-top:1px solid var(--line);font-size:9.5px;color:var(--muted);
 display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
.modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.42);display:none;align-items:center;justify-content:center;padding:18px;z-index:90}
.modal-bg.show{display:flex}
.modal{width:min(760px,100%);max-height:92vh;background:#fff;border-radius:16px;overflow:auto;box-shadow:0 18px 55px rgba(15,23,42,.18)}
.modal-head{padding:15px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}
.modal-head h2{font-size:15px;margin:0 0 3px}.modal-head p{font-size:9.5px;color:var(--muted);margin:0}
.close{width:29px;height:29px;border:0;border-radius:8px;background:#eef2f6}
.modal-body{padding:16px 18px}.modal-foot{padding:12px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.field{display:grid;gap:4px}.field.full{grid-column:1/-1}
.field label{font-size:9px;font-weight:800;color:#475569}
.field input,.field select,.field textarea{border:1px solid var(--line);border-radius:8px;padding:8px 9px;font-size:11px;background:#fff}
.field textarea{min-height:64px;resize:vertical}
.hint{background:#f2fafb;border:1px solid #cfe5ea;border-radius:9px;padding:10px 12px;font-size:10px;color:#49636c;margin-bottom:12px;line-height:1.55}
.toast-wrap{position:fixed;right:18px;bottom:18px;display:grid;gap:8px;z-index:120}
.toast{background:#18333d;color:#fff;padding:10px 13px;border-radius:9px;font-size:10.5px}
@media(max-width:900px){.shell{padding:12px 10px 30px}.head{flex-wrap:wrap}.head .right{width:100%}
 .filters .search{width:100%}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script src="{{ asset('js/qms-select.js') }}?v=3"></script>
<script src="{{ asset('js/qms-date.js') }}?v=1"></script>
<script>window.QMS_ITEM={state:"{{ route('item.state') }}",save:"{{ route('item.save') }}",
 card:"{{ route('stock.page') }}",dash:"{{ route('stock.dash') }}",csrf:"{{ csrf_token() }}"};</script>
</head>
<body>
@include('modules._sidebar')

<div class="shell">
  <div class="breadcrumb">Kho vật tư › <b>Quản lý mã hàng</b></div>
  <div class="head">
    <div>
      <h1>Quản lý mã hàng</h1>
      <p>Mỗi mã hàng gắn với đúng một thẻ kho — tạo mã hàng là hệ thống tự sinh thẻ kho tương ứng.</p>
    </div>
    <div class="right">
      <a class="btn" href="{{ route('entry.page') }}">🛒 Nhập / xuất nhanh</a><a class="btn" href="{{ route('stock.dash') }}">📊 Tổng quan kho</a>
      <a class="btn" href="{{ route('item.export') }}">⇩ Xuất Excel</a>
      <button class="btn" onclick="exportCsv()">⇩ CSV</button>
      <button class="btn primary" onclick="openItem()">＋ Thêm mã hàng</button>
    </div>
  </div>

  <div class="panel">
    <div class="filters">
      <input class="search" id="q" placeholder="Tìm mã hàng, tên, nhà cung cấp...">
      <select id="fGroup"></select>
      <select id="fStatus">
        <option value="">Tất cả tồn kho</option>
        <option value="ok">Còn hàng</option>
        <option value="low">Sắp hết</option>
        <option value="out">Hết hàng</option>
        <option value="high">Vượt tối đa</option>
      </select>
      <select id="fActive">
        <option value="">Tất cả trạng thái</option>
        <option value="1">Đang dùng</option>
        <option value="0">Ngừng dùng</option>
      </select>
      <select id="fExp">
        <option value="">Tất cả hạn dùng</option>
        <option value="exp">Đã quá hạn</option>
        <option value="30">Hết hạn trong 30 ngày</option>
        <option value="90">Hết hạn trong 90 ngày</option>
        <option value="none">Chưa khai báo hạn</option>
      </select>
      <button class="btn sm" onclick="clearFilters()">Xóa lọc</button>
      <span class="meta" id="count"></span>
    </div>
    <div class="table-wrap"><table><thead><tr>
      <th>Mã hàng</th><th>Tên hàng hóa</th><th>Nhóm hàng</th><th>ĐVT / Quy cách</th><th>Nhà cung cấp</th>
      <th>Hạn hóa chất / vật tư</th>
      <th class="num">Tồn</th><th class="num">Min</th><th class="num">Max</th><th>Tồn kho</th>
      <th>Thẻ kho</th><th class="num">Phát sinh</th><th>Thao tác</th>
    </tr></thead><tbody id="tbody"></tbody></table></div>
    <div class="foot"><span id="rangeText"></span>
      <span>Xoá mã hàng sẽ xoá luôn thẻ kho và toàn bộ phát sinh của mã đó.</span></div>
  </div>
</div>

<div class="modal-bg" id="itemModal"><div class="modal">
  <div class="modal-head"><div><h2 id="itemTitle">Thêm mã hàng</h2><p id="itemSub">Thẻ kho sẽ được tự sinh sau khi lưu</p></div>
    <button class="close" onclick="closeModal()">×</button></div>
  <div class="modal-body">
    <div class="hint" id="itemHint">Mã hàng là khóa duy nhất. Sau khi lưu, hệ thống cấp một số thẻ kho (TK-xxxxx) gắn vĩnh viễn với mã hàng này.</div>
    <form class="form-grid" id="itemForm">
      <input type="hidden" id="iId">
      <div class="field"><label>Mã hàng hóa *</label><input id="iCode" required></div>
      <div class="field"><label>Tên hàng hóa *</label><input id="iName" required></div>
      <div class="field"><label>Nhóm hàng</label><input id="iGroup" list="groupList" placeholder="Hóa chất, Vật tư tiêu hao..."><datalist id="groupList"></datalist></div>
      <div class="field"><label>Đơn vị tính *</label><input id="iUnit" required placeholder="Kg, Cái, Chai..."></div>
      <div class="field"><label>Quy cách đóng gói</label><input id="iPacking" placeholder="1 Kg/Túi"></div>
      <div class="field"><label>Công ty cung cấp</label><input id="iSupplier"></div>
      <div class="field"><label>Hạn hóa chất / vật tư</label><input id="iExpiry" type="date">
        <span class="sub">Để trống nếu mã hàng này không có hạn dùng.</span></div>
      <div class="field"><label>Lượng lưu kho tối thiểu</label><input id="iMin" type="number" min="0" step="0.01" value="0"></div>
      <div class="field"><label>Lượng lưu kho tối đa</label><input id="iMax" type="number" min="0" step="0.01" value="0"></div>
      <div class="field"><label>Trạng thái</label><select id="iActive"><option value="1">Đang dùng</option><option value="0">Ngừng dùng</option></select></div>
      <div class="field full"><label>Ghi chú</label><textarea id="iNote"></textarea></div>
    </form>
  </div>
  <div class="modal-foot"><button class="btn" onclick="closeModal()">Hủy</button>
    <button class="btn primary" onclick="saveItem()">Lưu mã hàng</button></div>
</div></div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
let DATA={items:[],groups:[]};
const $=id=>document.getElementById(id);
const fmt=n=>new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(Number(n||0));
const viDate=s=>s?s.slice(8,10)+'/'+s.slice(5,7)+'/'+s.slice(0,4):'—';
const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const ST={ok:['ok','Còn hàng'],low:['low','Sắp hết'],out:['out','Hết hàng'],high:['high','Vượt tối đa']};
/** Số ngày còn lại tới hạn dùng (âm = đã quá hạn), null khi chưa khai báo. */
function expDays(s){
  if(!s)return null;
  const d=new Date(s+'T00:00:00'),t=new Date();t.setHours(0,0,0,0);
  return Math.round((d-t)/86400000);
}
/** Nhãn tình trạng hạn dùng để hiện badge trên trang tính. */
function expBadge(s){
  const n=expDays(s);
  if(n===null)return null;
  if(n<0)return['exp','quá hạn '+(-n)+' ngày'];
  if(n===0)return['exp','hết hạn hôm nay'];
  if(n<=90)return['soon','còn '+n+' ngày'];
  return['far','còn '+n+' ngày'];
}
function toast(m){const e=document.createElement('div');e.className='toast';e.textContent=m;$('toastWrap').appendChild(e);setTimeout(()=>e.remove(),2600)}

function matchExp(x,f){
  if(!f)return true;
  const n=expDays(x.expiry);
  if(f==='none')return n===null;
  if(n===null)return false;
  if(f==='exp')return n<0;
  return n>=0&&n<=Number(f);
}
function filtered(){
  const q=$('q').value.trim().toLowerCase(),g=$('fGroup').value,st=$('fStatus').value,
        ac=$('fActive').value,ex=$('fExp').value;
  return DATA.items.filter(x=>
    (!q||(x.code+' '+x.name+' '+x.supplier).toLowerCase().includes(q))&&
    (!g||x.group===g)&&(!st||x.status===st)&&(ac===''||String(x.active?1:0)===ac)&&matchExp(x,ex));
}
function render(){
  const rows=filtered();
  $('tbody').innerHTML=rows.length?rows.map(x=>{const s=ST[x.status],e=expBadge(x.expiry);return `<tr>
    <td><div class="main">${esc(x.code)}</div>${x.active?'':'<div class="sub"><span class="badge off">Ngừng dùng</span></div>'}</td>
    <td><div class="main">${esc(x.name)}</div>${x.note?`<div class="sub">${esc(x.note)}</div>`:''}</td>
    <td>${esc(x.group||'—')}</td>
    <td>${esc(x.unit)}<div class="sub">${esc(x.packing||'—')}</div></td>
    <td>${esc(x.supplier||'—')}</td>
    <td class="exp-cell">${e?`<div class="d">${viDate(x.expiry)}</div><span class="badge ${e[0]}">${e[1]}</span>`:'—'}</td>
    <td class="num main">${fmt(x.balance)}</td>
    <td class="num">${fmt(x.min)}</td><td class="num">${fmt(x.max)}</td>
    <td><span class="badge ${s[0]}">${s[1]}</span></td>
    <td><a class="card-link" href="${window.QMS_ITEM.card}?product=${encodeURIComponent(x.id)}">🏷 ${esc(x.cardNo)}</a></td>
    <td class="num">${x.txCount}<div class="sub">${viDate(x.lastDate)}</div></td>
    <td><div class="row-actions">
      <a class="btn sm primary" href="${window.QMS_ITEM.card}?product=${encodeURIComponent(x.id)}">Mở thẻ kho</a>
      <button class="btn sm" onclick="openItem('${x.id}')">Sửa</button>
      <button class="btn sm red" onclick="removeItem('${x.id}')">Xóa</button></div></td></tr>`}).join('')
    :`<tr><td colspan="13" class="empty">Không có mã hàng phù hợp.</td></tr>`;
  $('count').textContent=`${rows.length} mã hàng`;
  const qh=DATA.items.filter(x=>{const n=expDays(x.expiry);return n!==null&&n<0}).length;
  const sh=DATA.items.filter(x=>{const n=expDays(x.expiry);return n!==null&&n>=0&&n<=90}).length;
  $('rangeText').textContent=`Hiển thị ${rows.length}/${DATA.items.length} mã hàng · ${DATA.items.filter(x=>x.active).length} đang dùng`
    +(qh?` · ${qh} mã quá hạn`:'')+(sh?` · ${sh} mã hết hạn trong 90 ngày`:'');
  const gs=[...new Set(DATA.items.map(x=>x.group).filter(Boolean))].sort();
  const cur=$('fGroup').value;
  $('fGroup').innerHTML='<option value="">Tất cả nhóm hàng</option>'+gs.map(g=>`<option ${g===cur?'selected':''}>${esc(g)}</option>`).join('');
  $('groupList').innerHTML=gs.map(g=>`<option value="${esc(g)}">`).join('');
}
function clearFilters(){$('q').value='';$('fGroup').value='';$('fStatus').value='';$('fActive').value='';$('fExp').value='';render()}

function openItem(id=''){
  const x=DATA.items.find(i=>i.id===id);
  $('itemTitle').textContent=x?'Cập nhật mã hàng':'Thêm mã hàng';
  $('itemSub').textContent=x?`Thẻ kho: ${x.cardNo} · ${x.txCount} phát sinh`:'Thẻ kho sẽ được tự sinh sau khi lưu';
  $('itemHint').innerHTML=x
    ? `Mã hàng này đang dùng thẻ kho <b>${esc(x.cardNo)}</b>. Đổi mã hàng không làm đổi số thẻ kho.`
    : 'Mã hàng là khóa duy nhất. Sau khi lưu, hệ thống cấp một số thẻ kho (TK-xxxxx) gắn vĩnh viễn với mã hàng này.';
  $('iId').value=x?.id||'';$('iCode').value=x?.code||'';$('iName').value=x?.name||'';
  $('iGroup').value=x?.group||'';$('iUnit').value=x?.unit||'';$('iPacking').value=x?.packing||'';
  $('iSupplier').value=x?.supplier||'';$('iExpiry').value=x?.expiry||'';
  $('iMin').value=x?.min??0;$('iMax').value=x?.max??0;
  $('iActive').value=x?(x.active?'1':'0'):'1';$('iNote').value=x?.note||'';
  $('itemModal').classList.add('show');QMSSelect.refresh();
}
function closeModal(){$('itemModal').classList.remove('show')}

async function saveItem(){
  const code=$('iCode').value.trim();
  if(!code||!$('iName').value.trim()||!$('iUnit').value.trim())return toast('Nhập đủ mã hàng, tên và đơn vị tính');
  const id=$('iId').value;
  if(DATA.items.some(x=>x.code.toLowerCase()===code.toLowerCase()&&x.id!==id))
    return toast(`Mã hàng "${code}" đã tồn tại`);
  const obj={id:id||'',code,name:$('iName').value.trim(),group:$('iGroup').value.trim(),
    unit:$('iUnit').value.trim(),packing:$('iPacking').value.trim(),supplier:$('iSupplier').value.trim(),
    expiry:$('iExpiry').value||'',
    min:Number($('iMin').value||0),max:Number($('iMax').value||0),
    active:$('iActive').value==='1',note:$('iNote').value.trim()};
  const i=DATA.items.findIndex(x=>x.id===id);
  i>=0?Object.assign(DATA.items[i],obj):DATA.items.push({...obj,cardNo:'(đang cấp)',balance:0,status:'out',txCount:0,lastDate:''});
  await push();closeModal();
  toast(i>=0?'Đã cập nhật mã hàng':'Đã thêm mã hàng & tự sinh thẻ kho');
}
async function removeItem(id){
  const x=DATA.items.find(i=>i.id===id);if(!x)return;
  if(!confirm(`Xóa mã hàng ${x.code}?\nThẻ kho ${x.cardNo} và ${x.txCount} phát sinh sẽ bị xóa theo.`))return;
  DATA.items=DATA.items.filter(i=>i.id!==id);await push();toast('Đã xóa mã hàng và thẻ kho')
}
async function push(){
  const r=await fetch(window.QMS_ITEM.save,{method:'POST',credentials:'same-origin',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_ITEM.csrf,'Accept':'application/json'},
    body:JSON.stringify({items:DATA.items})});
  if(!r.ok){toast('Lưu thất bại: HTTP '+r.status);return}
  await load();
}
async function load(){
  const r=await fetch(window.QMS_ITEM.state,{credentials:'same-origin'});
  if(!r.ok)throw new Error('Không tải được danh mục');
  DATA=await r.json();render();
}
function exportCsv(){
  const rows=[['Mã hàng','Tên hàng hóa','Nhóm hàng','ĐVT','Quy cách','Nhà cung cấp','Hạn hóa chất / vật tư','Tình trạng hạn','Tồn','Min','Max','Trạng thái tồn','Số thẻ kho','Phát sinh','Trạng thái'],
    ...filtered().map(x=>{const e=expBadge(x.expiry);
      return [x.code,x.name,x.group,x.unit,x.packing,x.supplier,x.expiry?viDate(x.expiry):'',e?e[1]:'',
        x.balance,x.min,x.max,ST[x.status][1],x.cardNo,x.txCount,x.active?'Đang dùng':'Ngừng dùng']})];
  const csv='﻿'+rows.map(r=>r.map(v=>`"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
  const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
  a.download='danh-muc-ma-hang.csv';a.click();URL.revokeObjectURL(a.href);toast('Đã xuất danh mục')
}
['q','fGroup','fStatus','fActive','fExp'].forEach(id=>$(id).addEventListener(id==='q'?'input':'change',render));
$('itemModal').addEventListener('click',e=>{if(e.target.id==='itemModal')closeModal()});
(async()=>{try{await load();QMSSelect.auto();QMSDate.auto()}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();
</script>
</body>
</html>
