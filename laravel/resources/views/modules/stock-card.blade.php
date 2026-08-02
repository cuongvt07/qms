<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quản lý thẻ kho</title>
<style>
:root{
  --bg:#f4f7fb;--panel:#fff;--line:#dfe6ee;--line2:#edf1f5;--text:#1e293b;
  --muted:#64748b;--primary:#0f6b7a;--primary2:#0b5662;--soft:#e8f5f7;
  --green:#15803d;--green-soft:#ecfdf3;--amber:#a16207;--amber-soft:#fff8e6;
  --red:#b42318;--red-soft:#fff1f0;--blue:#1d4ed8;--blue-soft:#eef4ff;
  --shadow:0 18px 55px rgba(15,23,42,.12);--radius:14px
}
*{box-sizing:border-box}
html{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
body{margin:0}
button,input,select,textarea{font:inherit}
button{cursor:pointer}
.shell{min-height:100vh;padding:22px}
.app{max-width:1680px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:14px}
.breadcrumb{font-size:11px;color:var(--muted);margin-bottom:8px}
h1{font-size:26px;margin:0 0 5px;letter-spacing:-.025em}
.subtitle{font-size:12px;color:var(--muted);margin:0;line-height:1.55}
.actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.btn{height:36px;padding:0 13px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;font-size:11px;font-weight:750;display:inline-flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap}
.btn:hover{border-color:#bdc8d4;box-shadow:0 5px 16px rgba(15,23,42,.07)}
.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}
.btn.green{background:var(--green);border-color:var(--green);color:#fff}
.btn.red{background:#fff;color:var(--red);border-color:#f1c7c3}
.btn.sm{height:30px;padding:0 9px;font-size:10px;border-radius:7px}
.btn.icon{width:31px;padding:0}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.stat{background:#fff;border:1px solid var(--line);border-radius:12px;padding:13px;display:flex;align-items:center;gap:11px}
.stat-ico{width:38px;height:38px;border-radius:10px;background:var(--soft);color:var(--primary);display:grid;place-items:center;font-weight:900}
.stat strong{font-size:19px;display:block;line-height:1.1}.stat span{font-size:9px;color:var(--muted)}
.panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;box-shadow:0 5px 18px rgba(15,23,42,.035)}
.filters{padding:12px 13px;border-bottom:1px solid var(--line);background:#fbfcfd;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.input{position:relative}.input input,.input select{height:35px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:0 10px;font-size:10px;outline:0;color:var(--text)}
.search input{width:320px;padding-left:31px}.search:before{content:"⌕";position:absolute;left:10px;top:6px;color:#8795a6;font-size:16px}.meta{margin-left:auto;font-size:9px;color:var(--muted)}
.table-wrap{overflow:auto;max-height:calc(100vh - 300px)}
table{width:100%;border-collapse:separate;border-spacing:0}.data{min-width:1180px}
.data th{position:sticky;top:0;z-index:4;background:#f8fafc;padding:9px 10px;border-bottom:1px solid var(--line);text-align:left;font-size:8.5px;text-transform:uppercase;letter-spacing:.055em;color:#728197;white-space:nowrap}
.data td{padding:10px;border-bottom:1px solid var(--line2);font-size:10px;vertical-align:middle;background:#fff}
.data tbody tr:hover td{background:#fbfdfe}.main{font-weight:800}.sub{font-size:8.7px;color:var(--muted);margin-top:3px}.num{text-align:right;font-variant-numeric:tabular-nums}
.badge{display:inline-flex;align-items:center;padding:5px 8px;border-radius:999px;font-size:8.5px;font-weight:800;white-space:nowrap}
.badge.ok{background:var(--green-soft);color:var(--green)}.badge.low{background:var(--amber-soft);color:var(--amber)}.badge.out{background:var(--red-soft);color:var(--red)}.badge.high{background:var(--blue-soft);color:var(--blue)}
.row-actions{display:flex;gap:5px;white-space:nowrap}.pagination{padding:11px 13px;background:#fbfcfd;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;font-size:9px;color:var(--muted)}
.empty{text-align:center;padding:55px;color:var(--muted)}
.modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.42);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:18px;z-index:90}.modal-bg.show{display:flex}
.modal{width:min(780px,100%);max-height:94vh;background:#fff;border-radius:16px;overflow:auto;box-shadow:var(--shadow)}.modal.wide{width:min(1520px,100%)}
.modal-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}.modal-head h2{font-size:16px;margin:0 0 4px}.modal-head p{font-size:9px;color:var(--muted);margin:0}.close{width:30px;height:30px;border:0;border-radius:8px;background:#eef2f6}
.modal-body{padding:18px}.modal-foot{padding:13px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{display:grid;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:9px;font-weight:800;color:#475569}.field input,.field select,.field textarea{border:1px solid var(--line);border-radius:9px;padding:9px 10px;font-size:10px;outline:0;background:#fff}.field textarea{min-height:78px;resize:vertical}.help{font-size:8.5px;color:var(--muted)}
.card-head{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:10px;margin-bottom:12px}.info{border:1px solid var(--line);border-radius:10px;background:#f8fafc;padding:10px}.info label{display:block;font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}.info strong{font-size:11px}.toolbar{display:flex;gap:7px;flex-wrap:wrap;align-items:center;margin-bottom:10px}.toolbar .push{margin-left:auto}
.stock-table-wrap{overflow:auto;border:1px solid var(--line);border-radius:10px}.stock-table{min-width:1450px;border-collapse:collapse}.stock-table th,.stock-table td{border-right:1px solid var(--line);border-bottom:1px solid var(--line);padding:7px 8px;text-align:center;font-size:9px}.stock-table th{background:#f8fafc;font-weight:800}.stock-table tr:last-child td{border-bottom:0}.stock-table th:last-child,.stock-table td:last-child{border-right:0}.stock-table .left{text-align:left}.stock-table .import{background:#f2fbf5}.stock-table .export{background:#fffaf0}.stock-table .destroy{background:#fff4f3}.stock-table .balance{font-weight:900}
.group-title{background:#eef3f7!important;font-weight:900!important;text-transform:uppercase;font-size:8px!important;letter-spacing:.06em}
.notice{padding:10px 12px;border:1px solid #cfe5ea;background:#f2fafb;border-radius:9px;font-size:9px;line-height:1.55;color:#49636c;margin-bottom:12px}
.toast-wrap{position:fixed;right:20px;bottom:20px;z-index:130;display:grid;gap:8px}.toast{background:#18333d;color:#fff;padding:10px 13px;border-radius:9px;font-size:10px;box-shadow:var(--shadow)}
@media(max-width:1050px){.stats{grid-template-columns:repeat(2,1fr)}.topbar{flex-direction:column}.actions{justify-content:flex-start}.table-wrap{max-height:none}.card-head{grid-template-columns:1fr 1fr}}
@media(max-width:650px){.shell{padding:13px}.stats{grid-template-columns:1fr}.search,.search input,.input,.input input,.input select{width:100%}.meta{margin-left:0}.form-grid,.card-head{grid-template-columns:1fr}.field.full{grid-column:auto}}
@media print{
  .qs-side,.qs-burger,.qs-mask{display:none!important}
  body{background:#fff;padding-left:0!important}.shell,.topbar,.stats,.panel{display:none!important}.modal-bg{display:none!important}.modal-bg.show{position:static!important;background:#fff!important;display:block!important;padding:0!important}.modal{box-shadow:none!important;width:100%!important;max-height:none!important;border-radius:0!important}.modal-head,.toolbar,.modal-foot,.notice{display:none!important}.modal-head{justify-content:center;border:0;padding-bottom:8px}.modal-head h2{font-size:18px}.modal-body{padding:0}.stock-table-wrap{border-color:#000}.stock-table th,.stock-table td{border-color:#000;font-family:"Times New Roman",serif;font-size:9px}.info{border:0;background:#fff;padding:4px}.card-head{grid-template-columns:1fr 1fr 1fr 1fr}.notice{display:none}
}

/* ===== Thẻ kho: phiếu in + nhập liệu trực tiếp ===== */
.qc-sheet{border:1px solid var(--line);border-radius:10px;padding:16px 18px;margin-bottom:12px;background:#fff}
.qc-sheet h3{text-align:center;margin:0 0 3px;font-size:15px;letter-spacing:.04em}
.qc-doc{text-align:center;font-size:9px;color:var(--muted);margin-bottom:12px}
.qc-info{display:grid;grid-template-columns:1fr 1fr;gap:6px 26px}
.qc-line{display:flex;gap:8px;font-size:10.5px;align-items:baseline}
.qc-line label{min-width:132px;text-align:right;color:#475569;flex:none}
.qc-line b{font-weight:800}
.qc-months{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:10px}
.qc-y{display:inline-flex;align-items:center;gap:4px;margin-right:6px}
.qc-mbtn{height:26px;padding:0 9px;border:1px solid var(--line);background:#fff;border-radius:7px;font-size:9.5px;font-weight:800;color:var(--muted)}
.qc-mbtn.has{border-color:#9fd0c9;background:#eefaf8;color:#0b5662}
.qc-mbtn.on{background:var(--primary);border-color:var(--primary);color:#fff}
.qc-mhead td{background:#eef3f7!important;font-weight:900;text-align:left!important;font-size:9px;letter-spacing:.05em;cursor:pointer}
.qc-mhead .qc-caret{display:inline-block;width:12px}
.qc-mhead .qc-sum{float:right;font-weight:700;color:#49636c}
.stock-table input,.stock-table select{width:100%;min-width:64px;border:1px solid transparent;border-radius:6px;
  padding:4px 5px;font-size:9px;background:transparent;text-align:center;color:var(--text)}
.stock-table input:hover,.stock-table select:hover{border-color:var(--line)}
.stock-table input:focus,.stock-table select:focus{border-color:var(--primary);background:#fff;outline:2px solid #d6ecef}
.stock-table td.left input{text-align:left}
.stock-table input[type=date]{min-width:104px}
.stock-table .qc-del{width:22px;height:22px;border:1px solid #f1c7c3;background:#fff6f5;color:var(--red);border-radius:6px;font-size:10px;line-height:1}
.qc-newrow td{background:#fbfdff}
.qc-addbar{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:8px}
.qc-hint{font-size:8.7px;color:var(--muted)}
@media print{
  .qc-months,.qc-addbar,.qc-del,.qc-newrow{display:none!important}
  .stock-table input,.stock-table select{border:0!important;background:transparent!important;-webkit-appearance:none;appearance:none}
  .qc-sheet{border:0;padding:0}
  .qc-mhead td{background:#fff!important;border-top:1px solid #000}
}
</style>
<script>window.QMS_STOCK={state:"{{ route('stock.state') }}",save:"{{ route('stock.save') }}",csrf:"{{ csrf_token() }}"};</script>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script src="{{ asset('js/qms-select.js') }}?v=3"></script>
<script src="{{ asset('js/qms-date.js') }}?v=1"></script>
</head>
<body>
@include('modules._sidebar')
<div class="shell"><main class="app">
  <section class="topbar">
    <div><div class="breadcrumb">Quản lý kho › Thẻ kho</div><h1>Quản lý thẻ kho</h1><p class="subtitle">Mỗi sản phẩm có một thẻ kho duy nhất. Các lần nhập, xuất, hủy và kiểm kê được ghi nối tiếp theo thời gian.</p></div>
    <div class="actions"><a class="btn" href="{{ route('stock.dash') }}">📊 Tổng quan kho</a><a class="btn" href="{{ route('item.page') }}">📦 Quản lý mã hàng</a><button class="btn" onclick="load()">↻ Tải lại</button><button class="btn primary" onclick="openProductForm()">＋ Thêm sản phẩm</button></div>
  </section>

  <section class="stats" id="stats"></section>

  <section class="panel">
    <div class="filters">
      <div class="input search"><input id="search" placeholder="Tìm mã hoặc tên sản phẩm..."></div>
      <div class="input"><select id="statusFilter"><option value="">Tất cả trạng thái</option><option value="ok">Còn hàng</option><option value="low">Sắp hết</option><option value="out">Hết hàng</option><option value="high">Vượt tối đa</option></select></div>
      <button class="btn sm" onclick="clearFilters()">Xóa lọc</button><span class="meta" id="countText"></span>
    </div>
    <div class="table-wrap"><table class="data"><thead><tr><th>Mã hàng</th><th>Tên hàng hóa</th><th>ĐVT / Quy cách</th><th>Nhà cung cấp</th><th class="num">Tồn hiện tại</th><th class="num">Tối thiểu</th><th class="num">Tối đa</th><th>Trạng thái</th><th>Cập nhật gần nhất</th><th>Thao tác</th></tr></thead><tbody id="productBody"></tbody></table></div>
    <div class="pagination"><span id="rangeText"></span><span>Dữ liệu lưu trực tiếp trên trình duyệt</span></div>
  </section>
</main></div>

<div class="modal-bg" id="productModal"><div class="modal"><div class="modal-head"><div><h2 id="productTitle">Thêm sản phẩm</h2><p>Tạo một thẻ kho duy nhất cho sản phẩm</p></div><button class="close" onclick="closeModal('productModal')">×</button></div><div class="modal-body"><form id="productForm" class="form-grid">
<input type="hidden" id="pId">
<div class="field"><label>Mã hàng hóa *</label><input id="pCode" required></div><div class="field"><label>Tên hàng hóa *</label><input id="pName" required></div>
<div class="field"><label>Đơn vị tính *</label><input id="pUnit" required placeholder="Kg, Cái, Chai..."></div><div class="field"><label>Quy cách đóng gói</label><input id="pPacking" placeholder="1 Kg/Túi"></div>
<div class="field full"><label>Công ty cung cấp</label><input id="pSupplier"></div>
<div class="field"><label>Lượng lưu kho tối thiểu</label><input id="pMin" type="number" min="0" step="0.01" value="0"></div><div class="field"><label>Lượng lưu kho tối đa</label><input id="pMax" type="number" min="0" step="0.01" value="0"></div>
</form></div><div class="modal-foot"><button class="btn" onclick="closeModal('productModal')">Hủy</button><button class="btn primary" onclick="saveProduct()">Lưu sản phẩm</button></div></div></div>

<div class="modal-bg" id="cardModal"><div class="modal wide"><div class="modal-head"><div><h2 id="cardHeadTitle">Thẻ kho</h2><p id="cardHeadSub"></p></div><button class="close" onclick="closeModal('cardModal')">×</button></div><div class="modal-body">
<div class="notice">Điền trực tiếp vào ô trên bảng — mỗi dòng lưu ngay khi rời ô. <b>Tồn = Tồn trước + Nhập − Xuất − Hủy</b>.</div>
<div class="qc-sheet" id="cardSheet"></div>
<div class="toolbar"><span class="qc-hint">Chọn tháng để mở rộng / rút gọn. Dòng cuối mỗi tháng là dòng trống để thêm phát sinh mới.</span><span class="push"></span><button class="btn" onclick="expandAllMonths(true)">Mở tất cả</button><button class="btn" onclick="expandAllMonths(false)">Rút gọn tất cả</button><button class="btn" onclick="window.print()">In thẻ kho</button></div>
<div class="qc-months" id="cardMonths"></div>
<div class="stock-table-wrap"><table class="stock-table"><thead><tr><th rowspan="2">Ngày, tháng</th><th colspan="3">Nhập</th><th colspan="4">Xuất</th><th rowspan="2">Hàng quá hạn / hủy (c)</th><th rowspan="2">Tồn (d)</th><th rowspan="2">Đếm kho thực tế</th><th rowspan="2">Người giao</th><th rowspan="2">Người nhận</th><th rowspan="2">Ghi chú</th><th rowspan="2"></th></tr><tr><th>Số lượng nhập (a)</th><th>Số lô</th><th>Hạn sử dụng</th><th>Số lượng (b)</th><th>Nơi nhận</th><th>Số lô</th><th>Hạn sử dụng</th></tr></thead><tbody id="cardBody"></tbody></table></div>
</div><div class="modal-foot"><button class="btn" onclick="closeModal('cardModal')">Đóng</button></div></div></div>

<div class="modal-bg" id="txModal"><div class="modal"><div class="modal-head"><div><h2 id="txTitle">Nhập kho</h2><p id="txSub"></p></div><button class="close" onclick="closeModal('txModal')">×</button></div><div class="modal-body"><form id="txForm" class="form-grid">
<div class="field"><label>Ngày phát sinh *</label><input id="tDate" type="date" required></div><div class="field"><label>Số lượng *</label><input id="tQty" type="number" min="0.01" step="0.01" required></div>
<div class="field batch-field"><label>Số lô</label><input id="tBatch"></div><div class="field batch-field"><label>Hạn sử dụng</label><input id="tExpiry" type="date"></div>
<div class="field export-field"><label>Nơi nhận</label><input id="tDestination"></div><div class="field export-field"><label>Chọn lô xuất</label><select id="tBatchSelect"></select><span class="help" id="batchHelp"></span></div>
<div class="field"><label>Người giao</label><input id="tDeliverer"></div><div class="field"><label>Người nhận</label><input id="tReceiver"></div>
<div class="field adjust-field"><label>Đếm kho thực tế</label><input id="tActual" type="number" min="0" step="0.01"></div><div class="field full"><label>Ghi chú</label><textarea id="tNote"></textarea></div>
</form></div><div class="modal-foot"><button class="btn" onclick="closeModal('txModal')">Hủy</button><button class="btn primary" onclick="saveTransaction()">Lưu phát sinh</button></div></div></div>

<div class="toast-wrap" id="toastWrap"></div>
<script>
const KEY='stock_card_template_v1';
let state={products:[],transactions:[]}, currentProductId=null, currentType='import';
let cardYear=new Date().getFullYear(), openMonths=new Set([new Date().getMonth()+1]);
const $=id=>document.getElementById(id);
const uid=()=>Date.now().toString(36)+Math.random().toString(36).slice(2,7);
function nextCardNo(){let m=0;state.products.forEach(p=>{const g=/TK-(\d+)/.exec(p.cardNo||'');if(g)m=Math.max(m,Number(g[1]))});return 'TK-'+String(m+1).padStart(5,'0')}
const today=()=>new Date().toISOString().slice(0,10);
const fmt=n=>new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(Number(n||0));
const fmtDate=s=>s?new Date(s+'T00:00:00').toLocaleDateString('vi-VN'):'';
function seed(){}
async function load(){const r=await fetch(window.QMS_STOCK.state,{credentials:'same-origin'});if(!r.ok)throw new Error('Không tải được dữ liệu');state=await r.json();render()}
function save(){fetch(window.QMS_STOCK.save,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_STOCK.csrf,'Accept':'application/json'},body:JSON.stringify({products:state.products,transactions:state.transactions})}).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status)}).catch(e=>toast('Lưu thất bại: '+e.message))}
function balance(pid){return state.transactions.filter(x=>x.productId===pid).reduce((s,x)=>s+(x.type==='import'?x.qty:x.type==='export'||x.type==='destroy'?-x.qty:x.type==='adjust'?(Number(x.actual)-s):0),0)}
function txSorted(pid){return state.transactions.filter(x=>x.productId===pid).sort((a,b)=>a.date.localeCompare(b.date)||a.id.localeCompare(b.id))}
function status(p){const b=balance(p.id);if(b<=0)return['out','Hết hàng'];if(p.max>0&&b>p.max)return['high','Vượt tối đa'];if(p.min>0&&b<=p.min)return['low','Sắp hết'];return['ok','Còn hàng']}
function lastDate(pid){const a=txSorted(pid);return a.length?a[a.length-1].date:''}
function render(){const q=$('search').value.trim().toLowerCase(),sf=$('statusFilter').value;const rows=state.products.filter(p=>(!q||(p.code+' '+p.name).toLowerCase().includes(q))&&(!sf||status(p)[0]===sf));
 $('productBody').innerHTML=rows.length?rows.map(p=>{const b=balance(p.id),st=status(p);return `<tr><td><div class="main">${esc(p.code)}</div><div class="sub">Thẻ: ${esc(p.cardNo||"—")}</div></td><td><div class="main">${esc(p.name)}</div></td><td>${esc(p.unit)}<div class="sub">${esc(p.packing||'—')}</div></td><td>${esc(p.supplier||'—')}</td><td class="num main">${fmt(b)}</td><td class="num">${fmt(p.min)}</td><td class="num">${fmt(p.max)}</td><td><span class="badge ${st[0]}">${st[1]}</span></td><td>${fmtDate(lastDate(p.id))||'—'}</td><td><div class="row-actions"><button class="btn sm primary" onclick="openCard('${p.id}')">Xem thẻ</button><button class="btn sm" onclick="openProductForm('${p.id}')">Sửa</button><button class="btn sm red" onclick="deleteProduct('${p.id}')">Xóa</button></div></td></tr>`}).join(''):`<tr><td colspan="10" class="empty">Không có sản phẩm phù hợp.</td></tr>`;
 $('countText').textContent=`${rows.length} sản phẩm`;$('rangeText').textContent=`Hiển thị ${rows.length}/${state.products.length} sản phẩm`;renderStats()}
function renderStats(){const vals=state.products.map(p=>status(p)[0]),total=state.products.length,low=vals.filter(x=>x==='low').length,out=vals.filter(x=>x==='out').length,tx=state.transactions.length;$('stats').innerHTML=`<div class="stat"><div class="stat-ico">▦</div><div><strong>${total}</strong><span>Tổng số thẻ kho</span></div></div><div class="stat"><div class="stat-ico">↕</div><div><strong>${tx}</strong><span>Tổng phát sinh nhập/xuất</span></div></div><div class="stat"><div class="stat-ico">!</div><div><strong>${low}</strong><span>Sản phẩm sắp hết</span></div></div><div class="stat"><div class="stat-ico">0</div><div><strong>${out}</strong><span>Sản phẩm hết hàng</span></div></div>`}
function openProductForm(id=''){const p=state.products.find(x=>x.id===id);$('productTitle').textContent=p?'Cập nhật sản phẩm':'Thêm sản phẩm';$('pId').value=p?.id||'';$('pCode').value=p?.code||'';$('pName').value=p?.name||'';$('pUnit').value=p?.unit||'';$('pPacking').value=p?.packing||'';$('pSupplier').value=p?.supplier||'';$('pMin').value=p?.min||0;$('pMax').value=p?.max||0;show('productModal')}
function saveProduct(){const code=$('pCode').value.trim();if(!code||!$('pName').value.trim()||!$('pUnit').value.trim())return toast('Vui lòng nhập đủ mã, tên và đơn vị tính');const id=$('pId').value||uid();const existing=state.products.find(x=>x.id===id);if(!existing&&state.products.some(x=>x.code.toLowerCase()===code.toLowerCase()))return toast('Mã hàng "'+code+'" đã có thẻ kho');const cardNo=existing?.cardNo||nextCardNo();const obj={id,cardNo,code,name:$('pName').value.trim(),unit:$('pUnit').value.trim(),packing:$('pPacking').value.trim(),supplier:$('pSupplier').value.trim(),min:Number($('pMin').value||0),max:Number($('pMax').value||0)};const i=state.products.findIndex(x=>x.id===id);i>=0?state.products[i]=obj:state.products.push(obj);save();closeModal('productModal');render();toast(existing?'Đã cập nhật thẻ kho':'Đã tạo mã hàng & tự sinh thẻ kho '+cardNo)}
function deleteProduct(id){if(!confirm('Xóa sản phẩm và toàn bộ phát sinh của thẻ kho này?'))return;state.products=state.products.filter(x=>x.id!==id);state.transactions=state.transactions.filter(x=>x.productId!==id);save();render();toast('Đã xóa thẻ kho')}
function openCard(id){
  currentProductId=id;
  const p=state.products.find(x=>x.id===id);
  const years=[...new Set(txSorted(id).map(x=>x.date.slice(0,4)))];
  if(years.length&&!years.includes(String(cardYear)))cardYear=Number(years[years.length-1]);
  $('cardHeadTitle').textContent=p.name;
  $('cardHeadSub').textContent=`Mã hàng: ${p.code} · Số thẻ kho: ${p.cardNo||'—'} · ĐVT: ${p.unit||'—'}`;
  renderSheet();renderMonths();renderCard();show('cardModal')
}
/* Phần đầu thẻ kho — giống hệt phiếu in giấy */
function renderSheet(){
  const p=state.products.find(x=>x.id===currentProductId),b=balance(currentProductId),st=status(p);
  $('cardSheet').innerHTML=`<h3>THẺ KHO</h3>
   <div class="qc-doc">Mã số tài liệu: BM.01/QTQL.26 · Phiên bản 2.25 · Số thẻ kho: <b>${esc(p.cardNo||'—')}</b> · Năm ${cardYear}</div>
   <div class="qc-info">
     <div class="qc-line"><label>Tên hàng hóa:</label><b>${esc(p.name)}</b></div>
     <div class="qc-line"><label>Mã hàng hóa:</label><b>${esc(p.code)}</b></div>
     <div class="qc-line"><label>Đơn vị tính:</label><span>${esc(p.unit||'—')}</span></div>
     <div class="qc-line"><label>Quy cách đóng gói:</label><span>${esc(p.packing||'—')}</span></div>
     <div class="qc-line"><label>Công ty cung cấp:</label><span>${esc(p.supplier||'—')}</span></div>
     <div class="qc-line"><label>Tồn hiện tại:</label><b>${fmt(b)} ${esc(p.unit||'')}</b>&nbsp;<span class="badge ${st[0]}">${st[1]}</span></div>
     <div class="qc-line"><label>Lượng lưu kho tối thiểu:</label><span>${fmt(p.min)}</span></div>
     <div class="qc-line"><label>Lượng lưu kho tối đa:</label><span>${fmt(p.max)}</span></div>
   </div>`;
}
/* Dải 12 tháng — bấm để mở rộng / rút gọn */
function renderMonths(){
  const rows=txSorted(currentProductId);
  const years=[...new Set(rows.map(x=>x.date.slice(0,4)).concat(String(cardYear)))].sort();
  const cnt={};rows.filter(x=>x.date.startsWith(cardYear+'-')).forEach(x=>{const m=Number(x.date.slice(5,7));cnt[m]=(cnt[m]||0)+1});
  $('cardMonths').innerHTML=
    `<span class="qc-y"><button class="qc-mbtn" onclick="setCardYear(${cardYear-1})">‹</button>`+
    `<b style="font-size:11px">Năm ${cardYear}</b>`+
    `<button class="qc-mbtn" onclick="setCardYear(${cardYear+1})">›</button></span>`+
    Array.from({length:12},(_,i)=>{const m=i+1,n=cnt[m]||0;
      return `<button class="qc-mbtn ${openMonths.has(m)?'on':(n?'has':'')}" onclick="toggleMonth(${m})">Th${m}${n?` · ${n}`:''}</button>`}).join('')+
    (years.length>1?`<span class="qc-hint">Có dữ liệu: ${years.join(', ')}</span>`:'');
}
function setCardYear(y){cardYear=y;renderSheet();renderMonths();renderCard()}
function toggleMonth(m){openMonths.has(m)?openMonths.delete(m):openMonths.add(m);renderMonths();renderCard()}
function expandAllMonths(on){openMonths=on?new Set([1,2,3,4,5,6,7,8,9,10,11,12]):new Set();renderMonths();renderCard()}
function renderCard(){
  const p=state.products.find(x=>x.id===currentProductId);
  const all=txSorted(currentProductId);
  /* tồn lũy kế tính trên toàn bộ lịch sử để số tồn luôn đúng */
  let bal=0;const balOf={};
  all.forEach(x=>{if(x.type==='import')bal+=Number(x.qty);
    if(x.type==='export'||x.type==='destroy')bal-=Number(x.qty);
    if(x.type==='adjust')bal=Number(x.actual);balOf[x.id]=bal});
  const COLS=15;
  let html='';
  for(let m=1;m<=12;m++){
    const mk=`${cardYear}-${String(m).padStart(2,'0')}`;
    const rows=all.filter(x=>x.date.startsWith(mk));
    const open=openMonths.has(m);
    const nhap=rows.filter(x=>x.type==='import').reduce((s,x)=>s+Number(x.qty),0);
    const xuat=rows.filter(x=>x.type==='export').reduce((s,x)=>s+Number(x.qty),0);
    html+=`<tr class="qc-mhead" onclick="toggleMonth(${m})"><td colspan="${COLS}">
      <span class="qc-caret">${open?'▾':'▸'}</span> Tháng ${String(m).padStart(2,'0')}/${cardYear}
      <span class="qc-sum">${rows.length} dòng${rows.length?` · nhập ${fmt(nhap)} · xuất ${fmt(xuat)}`:''}</span></td></tr>`;
    if(!open)continue;
    rows.forEach(x=>{html+=txRow(x,balOf[x.id],p)});
    html+=newRow(mk,p);
  }
  $('cardBody').innerHTML=html;
}
/* 1 dòng phát sinh — mọi ô đều sửa được tại chỗ */
function txRow(x,bal,p){
  const t=x.type,i=(f,val,type='text',extra='')=>
    `<input data-tx="${x.id}" data-f="${f}" type="${type}" value="${esc(val??'')}" ${extra}>`;
  return `<tr class="${t}">
   <td>${i('date',x.date,'date')}</td>
   <td class="import">${t==='import'?i('qty',x.qty,'number','step="0.01" min="0"'):''}</td>
   <td class="import">${t==='import'?i('batch',x.batch):''}</td>
   <td class="import">${t==='import'?i('expiry',x.expiry,'date'):''}</td>
   <td class="export">${t==='export'?i('qty',x.qty,'number','step="0.01" min="0"'):''}</td>
   <td class="export">${t==='export'?i('destination',x.destination):''}</td>
   <td class="export">${t==='export'?i('batch',x.batch):''}</td>
   <td class="export">${t==='export'?i('expiry',x.expiry,'date'):''}</td>
   <td class="destroy">${t==='destroy'?i('qty',x.qty,'number','step="0.01" min="0"'):''}</td>
   <td class="balance">${fmt(bal)}</td>
   <td>${t==='adjust'?i('actual',x.actual,'number','step="0.01" min="0"'):''}</td>
   <td>${i('deliverer',x.deliverer)}</td>
   <td>${i('receiver',x.receiver)}</td>
   <td class="left">${i('note',x.note)}</td>
   <td><button class="qc-del" title="Xóa dòng" onclick="removeTx('${x.id}')">×</button></td>
  </tr>`;
}
/* Dòng trống cuối mỗi tháng: chọn loại rồi điền là thành phát sinh mới */
function newRow(mk,p){
  const d=`${mk}-01`;
  return `<tr class="qc-newrow"><td colspan="15">
    <div class="qc-addbar">
      <span class="qc-hint">Thêm vào tháng ${mk.slice(5)}/${mk.slice(0,4)}:</span>
      <button class="btn sm green" onclick="addTx('import','${d}')">＋ Nhập kho</button>
      <button class="btn sm primary" onclick="addTx('export','${d}')">− Xuất kho</button>
      <button class="btn sm red" onclick="addTx('destroy','${d}')">Hủy / quá hạn</button>
      <button class="btn sm" onclick="addTx('adjust','${d}')">Kiểm kê</button>
    </div></td></tr>`;
}
/* Thêm dòng trống rồi điền thẳng trên bảng */
function addTx(type,date){
  state.transactions.push({id:uid(),productId:currentProductId,date,type,qty:0,actual:type==='adjust'?0:null,
    batch:'',expiry:'',destination:'',deliverer:'',receiver:'',note:''});
  const m=Number(date.slice(5,7));openMonths.add(m);
  save();renderSheet();renderMonths();renderCard();render();
  setTimeout(()=>{const el=[...document.querySelectorAll('#cardBody input')].pop();el&&el.focus()},30);
}
function removeTx(id){
  if(!confirm('Xóa dòng phát sinh này?'))return;
  state.transactions=state.transactions.filter(x=>x.id!==id);
  save();renderSheet();renderMonths();renderCard();render();toast('Đã xóa dòng')
}
/* Sửa tại chỗ: rời ô là lưu */
function onCellChange(el){
  const tx=state.transactions.find(x=>x.id===el.dataset.tx);if(!tx)return;
  const f=el.dataset.f;
  if(f==='qty'||f==='actual'){tx[f]=Number(el.value||0)}else{tx[f]=el.value}
  if(f==='date'){const m=Number(el.value.slice(5,7));if(m)openMonths.add(m);cardYear=Number(el.value.slice(0,4))||cardYear}
  save();renderSheet();renderMonths();renderCard();render();
}
document.addEventListener('change',e=>{if(e.target.dataset&&e.target.dataset.tx)onCellChange(e.target)});
function batchBalances(pid){const map={};txSorted(pid).forEach(x=>{if(!x.batch)return;map[x.batch]??={batch:x.batch,expiry:x.expiry||'',qty:0};if(x.type==='import')map[x.batch].qty+=Number(x.qty);if(x.type==='export'||x.type==='destroy')map[x.batch].qty-=Number(x.qty)});return Object.values(map).filter(x=>x.qty>0).sort((a,b)=>(a.expiry||'9999').localeCompare(b.expiry||'9999'))}
function openTransaction(type){currentType=type;const p=state.products.find(x=>x.id===currentProductId);$('txTitle').textContent={import:'Nhập kho',export:'Xuất kho',destroy:'Hủy / quá hạn',adjust:'Kiểm kê kho'}[type];$('txSub').textContent=`${p.code} - ${p.name}`;$('tDate').value=today();$('tQty').value='';$('tBatch').value='';$('tExpiry').value='';$('tDestination').value='';$('tDeliverer').value='';$('tReceiver').value='';$('tActual').value='';$('tNote').value='';document.querySelectorAll('.batch-field').forEach(e=>e.style.display=type==='import'?'grid':'none');document.querySelectorAll('.export-field').forEach(e=>e.style.display=type==='export'||type==='destroy'?'grid':'none');document.querySelectorAll('.adjust-field').forEach(e=>e.style.display=type==='adjust'?'grid':'none');$('tQty').closest('.field').style.display=type==='adjust'?'none':'grid';const batches=batchBalances(currentProductId);$('tBatchSelect').innerHTML=batches.length?batches.map((b,i)=>`<option value="${esc(b.batch)}" data-expiry="${esc(b.expiry)}" data-qty="${b.qty}">${esc(b.batch)} · còn ${fmt(b.qty)} · HSD ${fmtDate(b.expiry)||'—'}${i===0?' · Gợi ý FEFO':''}</option>`).join(''):'<option value="">Không còn lô khả dụng</option>';$('batchHelp').textContent=batches.length?'Đã sắp theo hạn sử dụng gần nhất.':'';show('txModal')}
function saveTransaction(){const p=state.products.find(x=>x.id===currentProductId);if(!$('tDate').value)return toast('Vui lòng chọn ngày');if(currentType==='adjust'){if($('tActual').value==='')return toast('Nhập số lượng kiểm kê thực tế');state.transactions.push({id:uid(),productId:p.id,date:$('tDate').value,type:'adjust',actual:Number($('tActual').value),qty:0,deliverer:$('tDeliverer').value.trim(),receiver:$('tReceiver').value.trim(),note:$('tNote').value.trim()})}else{const qty=Number($('tQty').value);if(!(qty>0))return toast('Số lượng phải lớn hơn 0');let batch='',expiry='';if(currentType==='import'){batch=$('tBatch').value.trim();expiry=$('tExpiry').value}else{const opt=$('tBatchSelect').selectedOptions[0];batch=$('tBatchSelect').value;expiry=opt?.dataset.expiry||'';const available=Number(opt?.dataset.qty||0);if(!batch)return toast('Không có lô khả dụng');if(qty>available)return toast(`Lô ${batch} chỉ còn ${fmt(available)} ${p.unit}`);if(qty>balance(p.id))return toast('Số lượng xuất vượt tồn kho hiện tại')}
state.transactions.push({id:uid(),productId:p.id,date:$('tDate').value,type:currentType,qty,batch,expiry,destination:$('tDestination').value.trim(),deliverer:$('tDeliverer').value.trim(),receiver:$('tReceiver').value.trim(),note:$('tNote').value.trim()})}
save();closeModal('txModal');renderSheet();renderMonths();renderCard();render();toast('Đã lưu phát sinh kho')}
function clearFilters(){$('search').value='';$('statusFilter').value='';render()}
function resetDemo(){load()}
function show(id){$(id).classList.add('show')}function closeModal(id){$(id).classList.remove('show')}
function toast(msg){const e=document.createElement('div');e.className='toast';e.textContent=msg;$('toastWrap').appendChild(e);setTimeout(()=>e.remove(),2600)}
function esc(s=''){return String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]))}
$('search').addEventListener('input',render);$('statusFilter').addEventListener('change',render);document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('show')}));(async()=>{try{await load();QMSSelect.auto();const productParam=new URLSearchParams(location.search).get('product');if(productParam&&state.products.some(x=>x.id===productParam))setTimeout(()=>openCard(productParam),120);QMSDate.auto()}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();
</script>
</body>
</html>
