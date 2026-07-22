<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Module theo dõi khử nhiễm trang thiết bị</title>
<style>
:root{
  --bg:#f5f7fb;--panel:#fff;--line:#e4e9ef;--line2:#edf1f5;--text:#182433;--muted:#66758a;
  --primary:#176b87;--primary2:#10536a;--primary-soft:#eaf6f9;--green:#16845b;--green-soft:#e9f8f1;
  --amber:#a86c17;--amber-soft:#fff6e5;--red:#c43e4c;--red-soft:#fff0f2;--violet:#6268b8;--violet-soft:#eff0ff;
  --blue:#3577b8;--blue-soft:#eaf3fb;--shadow:0 15px 42px rgba(27,42,54,.12);--radius:14px
}
*{box-sizing:border-box}html{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
body{margin:0}button,input,select,textarea{font:inherit}.shell{padding:24px;min-height:100vh}.module{max-width:1600px;margin:auto}
.breadcrumb{font-size:11px;color:var(--muted);display:flex;gap:7px;align-items:center;margin-bottom:10px}.breadcrumb b{color:var(--text)}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:15px}.page-head h1{font-size:25px;letter-spacing:-.025em;margin:0 0 5px}.page-head p{font-size:12px;color:var(--muted);margin:0;max-width:760px;line-height:1.5}
.actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.btn{border:1px solid var(--line);background:#fff;color:var(--text);height:36px;padding:0 12px;border-radius:9px;cursor:pointer;font-size:11px;font-weight:750;display:inline-flex;align-items:center;justify-content:center;gap:7px;transition:.15s;white-space:nowrap}
.btn:hover{border-color:#bbc8d3;box-shadow:0 5px 15px rgba(31,45,61,.07)}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}.btn.primary:hover{background:var(--primary2)}
.btn.danger{background:var(--red-soft);border-color:#f1c8ce;color:var(--red)}.btn.sm{height:29px;padding:0 8px;font-size:10px;border-radius:7px}.btn.icon{width:29px;padding:0}.btn:disabled{opacity:.42;cursor:not-allowed;box-shadow:none}
.userbox{display:flex;align-items:center;gap:7px;padding:4px 8px;border:1px solid var(--line);background:#fff;border-radius:9px}.userbox span{font-size:9px;color:var(--muted)}.userbox select{border:0;background:transparent;outline:0;font-size:10px;font-weight:750;max-width:175px}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-bottom:11px}.stat{background:#fff;border:1px solid var(--line);border-radius:11px;padding:11px 13px;display:flex;align-items:center;gap:10px}
.stat-ico{width:33px;height:33px;display:grid;place-items:center;border-radius:9px;background:var(--primary-soft);color:var(--primary);font-weight:900}.stat strong{font-size:17px;display:block}.stat span{font-size:9px;color:var(--muted)}
.panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 18px rgba(31,45,61,.035)}
.filters{display:flex;align-items:center;gap:7px;flex-wrap:wrap;padding:12px 13px;background:#fbfcfd;border-bottom:1px solid var(--line)}
.field-inline{position:relative}.field-inline input,.field-inline select{height:35px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:0 9px;font-size:10px;color:var(--text);outline:0}
.field-inline input:focus,.field-inline select:focus{border-color:#69a9bc;box-shadow:0 0 0 3px rgba(23,107,135,.08)}.search input{width:275px;padding-left:31px}.search:before{content:"⌕";position:absolute;left:10px;top:6px;font-size:16px;color:#8897a8}
.filter-meta{margin-left:auto;display:flex;align-items:center;gap:8px;color:var(--muted);font-size:9px}.filter-chip{display:none;background:var(--primary-soft);color:var(--primary);font-weight:750;border-radius:99px;padding:5px 8px}
.bulk{display:none;align-items:center;gap:8px;padding:9px 13px;background:#eef8fa;border-bottom:1px solid #cde4ea;font-size:10px}.bulk.show{display:flex}.bulk strong{color:var(--primary)}.bulk .push{margin-left:auto}
.table-wrap{overflow:auto;max-height:calc(100vh - 300px)}table{width:100%;border-collapse:separate;border-spacing:0;min-width:1390px}.data th{position:sticky;top:0;z-index:4;text-align:left;padding:9px 10px;background:#f8fafc;border-bottom:1px solid var(--line);font-size:8.5px;letter-spacing:.065em;text-transform:uppercase;color:#748397;white-space:nowrap}
.data td{padding:10px;border-bottom:1px solid var(--line2);font-size:10px;background:#fff;vertical-align:middle}.data tbody tr:hover td{background:#fbfdfe}.data tbody tr.selected td{background:#f2fafc}.check{width:36px;text-align:center}
.maincell{font-weight:750}.subcell{font-size:8.7px;color:var(--muted);margin-top:3px}.code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#eef3f6;color:#314557;border-radius:6px;padding:4px 6px;font-size:9.5px}
.reason{max-width:380px;line-height:1.45;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}.note{max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#5f6e7f}
.badge{display:inline-flex;align-items:center;gap:5px;padding:5px 7px;border-radius:99px;font-size:8.5px;font-weight:800;white-space:nowrap}.badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.badge.normal{background:var(--green-soft);color:var(--green)}.badge.monitor{background:var(--amber-soft);color:var(--amber)}.badge.incident{background:var(--red-soft);color:var(--red)}.badge.pending{background:#f0f3f6;color:#6b7787}
.type{display:inline-flex;padding:5px 7px;border-radius:6px;font-size:8.5px;font-weight:750;white-space:nowrap;background:var(--blue-soft);color:var(--blue)}.type.repair{background:var(--red-soft);color:var(--red)}.type.maintenance{background:var(--violet-soft);color:var(--violet)}.type.decontamination{background:var(--green-soft);color:var(--green)}.type.training{background:#fff0e4;color:#a75a17}.type.software{background:#f3edff;color:#7951b5}.type.relocation{background:#eef0f3;color:#526174}.type.installation{background:#e8f7fb;color:#17738b}.type.inspection{background:#eef7ee;color:#39763e}.type.other{background:#f2f4f6;color:#697586}
.row-actions{display:flex;gap:4px;white-space:nowrap}.pagination{display:flex;align-items:center;justify-content:space-between;padding:11px 13px;background:#fbfcfd;border-top:1px solid var(--line);font-size:9px;color:var(--muted)}
.page-left,.page-right{display:flex;align-items:center;gap:7px}.pagination select{border:1px solid var(--line);background:#fff;border-radius:7px;padding:4px 6px;font-size:9px}.empty{text-align:center;padding:48px;color:var(--muted)}.empty strong{display:block;color:var(--text);margin-bottom:5px}.empty .ico{font-size:31px;margin-bottom:8px}
.drawer-bg{position:fixed;inset:0;background:rgba(13,27,36,.32);z-index:60;display:none}.drawer-bg.show{display:block}.drawer{position:absolute;right:0;top:0;bottom:0;width:min(560px,100%);background:#fff;box-shadow:-18px 0 55px rgba(17,30,40,.2);display:flex;flex-direction:column}
.drawer-head{padding:18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}.drawer-head h2{font-size:16px;margin:0 0 4px}.drawer-head p{font-size:9px;color:var(--muted);margin:0}.close{width:29px;height:29px;border:0;background:#f0f3f6;border-radius:8px;cursor:pointer}
.drawer-body{padding:18px;overflow:auto}.drawer-foot{margin-top:auto;border-top:1px solid var(--line);padding:13px 18px;display:flex;justify-content:flex-end;gap:8px}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px}.detail{border:1px solid var(--line);background:#f8fafc;border-radius:10px;padding:10px}.detail.full{grid-column:1/-1}.detail label{display:block;font-size:8px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:5px}.detail strong{font-size:11px;line-height:1.55}.longtext{white-space:pre-wrap;font-size:11px;line-height:1.65;color:#344457}
.timeline{border-left:2px solid #e4e9ee;margin-left:6px;padding-left:16px;display:grid;gap:14px}.timeline-item{position:relative;font-size:9px;color:var(--muted)}.timeline-item:before{content:"";position:absolute;left:-22px;top:2px;width:8px;height:8px;border-radius:50%;background:var(--primary);box-shadow:0 0 0 4px var(--primary-soft)}.timeline-item strong{display:block;color:var(--text);margin-bottom:3px}
.modal-bg{position:fixed;inset:0;background:rgba(12,25,34,.42);backdrop-filter:blur(3px);z-index:80;display:none;align-items:center;justify-content:center;padding:18px}.modal-bg.show{display:flex}.modal{width:min(660px,100%);max-height:92vh;background:#fff;border-radius:16px;overflow:auto;box-shadow:var(--shadow)}.modal.wide{width:min(1120px,100%)}
.modal-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}.modal-head h2{font-size:15px;margin:0 0 4px}.modal-head p{font-size:9px;color:var(--muted);margin:0}.modal-body{padding:18px}.modal-foot{padding:13px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{display:grid;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:9px;font-weight:750;color:#425266}.field input,.field select,.field textarea{border:1px solid var(--line);border-radius:9px;padding:9px 10px;font-size:10px;outline:0;background:#fff}.field textarea{min-height:105px;resize:vertical;line-height:1.5}.field input:focus,.field select:focus,.field textarea:focus{border-color:#69a9bc;box-shadow:0 0 0 3px rgba(23,107,135,.08)}.help{font-size:8.5px;color:var(--muted)}
.form-tools{display:flex;justify-content:flex-end;gap:6px;margin-top:-3px}.quick-head{display:flex;gap:10px;align-items:end;padding:12px;border:1px solid var(--line);border-radius:10px;background:#f8fafc;margin-bottom:12px}.quick-head .field{min-width:180px}.quick-head .push{margin-left:auto}
.batch-wrap{overflow:auto}.batch{min-width:900px;border-collapse:collapse}.batch th{font-size:8px;text-transform:uppercase;color:#748397;background:#f8fafc;border:1px solid var(--line);padding:8px;text-align:left}.batch td{border:1px solid var(--line);padding:7px;font-size:9px}.batch input,.batch select,.batch textarea{width:100%;border:1px solid var(--line);border-radius:7px;padding:7px;font-size:9px}.batch textarea{min-height:54px;resize:vertical}.device-cell{min-width:220px}.device-cell strong{display:block}.device-cell span{display:block;color:var(--muted);font-size:8px;margin-top:3px}
.toast-wrap{position:fixed;right:20px;bottom:20px;z-index:120;display:grid;gap:7px}.toast{background:#172d38;color:#fff;padding:10px 13px;border-radius:9px;font-size:10px;box-shadow:var(--shadow);animation:tin .18s ease}.toast.error{background:#8d303a}@keyframes tin{from{opacity:0;transform:translateY(7px)}}
@media(max-width:1000px){.stats{grid-template-columns:1fr 1fr}.page-head{flex-direction:column}.filter-meta{margin-left:0}.userbox{display:none}.table-wrap{max-height:none}}
@media(max-width:580px){.shell{padding:14px}.stats{grid-template-columns:1fr}.search,.search input,.field-inline{width:100%}.field-inline input,.field-inline select{width:100%}.form-grid{grid-template-columns:1fr}.field.full,.detail.full{grid-column:auto}.detail-grid{grid-template-columns:1fr}.quick-head{flex-direction:column;align-items:stretch}.quick-head .push{margin-left:0}}
</style>
<script>window.QMS_DEV={state:"{{ route('dev.state') }}",save:"{{ route('dev.save') }}",preset:"{{ route('preset.index', 'dev') }}",csrf:"{{ csrf_token() }}"};</script>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=4">
<script src="{{ asset('js/qms-preset.js') }}?v=1"></script>
</head>
<body>
@include('modules._sidebar')
<div class="shell"><main class="module">
  <div class="breadcrumb"><span>Quản lý trang thiết bị</span><span>›</span><b>Theo dõi khử nhiễm</b></div>
  <section class="page-head">
    <div><h1>Theo dõi khử nhiễm trang thiết bị</h1><p>Quản lý các sự kiện khử nhiễm, vệ sinh, bảo dưỡng, sửa chữa và tình trạng thiết bị sau xử lý trong một danh sách dùng chung.</p></div>
    <div class="actions">
      <div class="userbox"><span>Người thao tác</span><select id="currentUser"></select></div>
      <button class="btn" onclick="exportCsv()">⇩ Xuất dữ liệu</button>
      <button class="btn" onclick="openBatch()">▦ Nhập nhiều thiết bị</button>
      <button class="btn primary" onclick="openForm()">＋ Thêm sự kiện</button>
    </div>
  </section>

  <section class="stats" id="stats"></section>

  <section class="panel">
    <div class="filters">
      <div class="field-inline search"><input id="fSearch" placeholder="Tìm mã máy, lý do, tình trạng, ghi chú..."></div>
      <div class="field-inline"><input id="fFrom" type="date" title="Từ ngày"></div>
      <div class="field-inline"><input id="fTo" type="date" title="Đến ngày"></div>
      <div class="field-inline"><select id="fDevice"><option value="">Tất cả thiết bị</option></select></div>
      <div class="field-inline"><select id="fLocation"><option value="">Tất cả vị trí</option></select></div>
      <div class="field-inline"><select id="fType"><option value="">Tất cả hoạt động</option></select></div>
      <div class="field-inline"><select id="fCondition"><option value="">Tất cả tình trạng</option></select></div>
      <button class="btn sm" onclick="clearFilters()">Xóa lọc</button>
      <div class="filter-meta"><span id="countText">0 bản ghi</span><span class="filter-chip" id="filterChip"></span></div>
    </div>

    <div class="bulk" id="bulkbar">
      <strong id="selectedText">0 bản ghi đã chọn</strong>
      <button class="btn sm" onclick="duplicateSelected()">Nhân bản sang hôm nay</button>
      <button class="btn sm danger" onclick="deleteSelected()">Xóa đã chọn</button>
      <span class="push"></span><button class="btn sm" onclick="clearSelection()">Bỏ chọn</button>
    </div>

    <div class="table-wrap">
      <table class="data">
        <thead><tr>
          <th class="check"><input id="checkAll" type="checkbox"></th>
          <th>Ngày thực hiện</th><th>Thiết bị</th><th>Bộ phận / vị trí</th><th>Loại hoạt động</th>
          <th>Lý do / nội dung thực hiện</th><th>Tình trạng sau xử lý</th><th>Ghi chú</th><th>Cập nhật</th><th>Thao tác</th>
        </tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>

    <div class="pagination">
      <div class="page-left"><span id="rangeText"></span><span>Hiển thị</span><select id="pageSize"><option>20</option><option>50</option><option>100</option></select><span>dòng/trang</span></div>
      <div class="page-right"><button class="btn sm" id="prev">‹ Trước</button><span id="pageText"></span><button class="btn sm" id="next">Sau ›</button></div>
    </div>
  </section>
</main></div>

<div class="drawer-bg" id="drawerBg"><aside class="drawer">
  <div class="drawer-head"><div><h2 id="drawerTitle"></h2><p id="drawerSub"></p></div><button class="close" onclick="closeDrawer()">×</button></div>
  <div class="drawer-body" id="drawerBody"></div><div class="drawer-foot" id="drawerFoot"></div>
</aside></div>

<div class="modal-bg" id="modalBg"><div class="modal" id="modalBox">
  <div class="modal-head"><div><h2 id="modalTitle"></h2><p id="modalSub"></p></div><button class="close" onclick="closeModal()">×</button></div>
  <div class="modal-body" id="modalBody"></div><div class="modal-foot" id="modalFoot"></div>
</div></div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
const SEED=null;
const KEY="decontamination-equipment-module-v1";
const TYPES={
 decontamination:["Khử nhiễm / vệ sinh","decontamination"],
 maintenance:["Bảo dưỡng","maintenance"],
 repair:["Sửa chữa / khắc phục","repair"],
 installation:["Lắp đặt / kết nối","installation"],
 training:["Hướng dẫn / đào tạo","training"],
 relocation:["Di chuyển thiết bị","relocation"],
 software:["Phần mềm / ứng dụng","software"],
 inspection:["Kiểm tra / hiệu chuẩn","inspection"],
 other:["Hoạt động khác","other"]
};
const CONDITIONS={
 normal:["Hoạt động bình thường","normal"],
 monitor:["Cần tiếp tục theo dõi","monitor"],
 incident:["Chưa hoạt động / có sự cố","incident"],
 pending:["Chưa đánh giá","pending"]
};
let state=null;
let ui={page:1,size:20,selected:new Set(),filters:{search:"",from:"",to:"",device:"",location:"",type:"",condition:""}};

function clone(x){return JSON.parse(JSON.stringify(x))}
async function load(){const r=await fetch(window.QMS_DEV.state,{credentials:'same-origin'});if(!r.ok)throw new Error('Không tải được dữ liệu');return await r.json()}
function save(msg){fetch(window.QMS_DEV.save,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_DEV.csrf,'Accept':'application/json'},body:JSON.stringify(state)}).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);if(msg)toast(msg)}).catch(e=>toast('Lưu thất bại: '+e.message,'error'))}
function esc(v){return String(v??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}
function fmtDate(v){return v?new Date(v+"T00:00:00").toLocaleDateString("vi-VN"):"—"}
function fmtDateTime(v){return v?new Date(v).toLocaleString("vi-VN"):"—"}
function today(){return new Date().toISOString().slice(0,10)}
function device(id){return state.devices.find(x=>x.id===id)}
function user(id){return state.users.find(x=>x.id===id)}
function currentUser(){return user(state.currentUserId)||state.users[0]}
function typeTag(t){const x=TYPES[t]||TYPES.other;return `<span class="type ${x[1]}">${x[0]}</span>`}
function conditionTag(c){const x=CONDITIONS[c]||CONDITIONS.pending;return `<span class="badge ${x[1]}">${x[0]}</span>`}
function toast(msg,type=""){const e=document.createElement("div");e.className="toast "+type;e.textContent=msg;document.getElementById("toastWrap").appendChild(e);setTimeout(()=>e.remove(),2900)}
function addHistory(e,action,detail){e.history=e.history||[];e.history.unshift({time:new Date().toISOString(),userId:state.currentUserId,action,detail});e.history=e.history.slice(0,20)}
function typeOptions(selected=""){return Object.entries(TYPES).map(([k,v])=>`<option value="${k}" ${k===selected?"selected":""}>${v[0]}</option>`).join("")}
function conditionOptions(selected=""){return Object.entries(CONDITIONS).map(([k,v])=>`<option value="${k}" ${k===selected?"selected":""}>${v[0]}</option>`).join("")}

function init(){
 const cu=document.getElementById("currentUser");
 cu.innerHTML=state.users.map(u=>`<option value="${u.id}" ${u.id===state.currentUserId?"selected":""}>${esc(u.name)}</option>`).join("");
 cu.onchange=()=>{state.currentUserId=cu.value;save();render()};
 document.getElementById("fDevice").innerHTML=`<option value="">Tất cả thiết bị</option>`+state.devices.map(d=>`<option value="${d.id}">${esc(d.code)} — ${esc(d.name)}</option>`).join("");
 const locations=[...new Set(state.devices.map(d=>d.location).filter(Boolean))].sort();
 document.getElementById("fLocation").innerHTML=`<option value="">Tất cả vị trí</option>`+locations.map(x=>`<option value="${esc(x)}">${esc(x)}</option>`).join("");
 document.getElementById("fType").innerHTML=`<option value="">Tất cả hoạt động</option>`+typeOptions();
 document.getElementById("fCondition").innerHTML=`<option value="">Tất cả tình trạng</option>`+conditionOptions();
 ["fSearch","fFrom","fTo","fDevice","fLocation","fType","fCondition"].forEach(id=>{
   document.getElementById(id).addEventListener(id==="fSearch"?"input":"change",readFilters)
 });
 document.getElementById("pageSize").onchange=e=>{ui.size=Number(e.target.value);ui.page=1;render()};
 document.getElementById("prev").onclick=()=>{ui.page--;render();scrollTopTable()};
 document.getElementById("next").onclick=()=>{ui.page++;render();scrollTopTable()};
 document.getElementById("checkAll").onchange=e=>{paged().forEach(x=>e.target.checked?ui.selected.add(x.id):ui.selected.delete(x.id));render()};
 render();
}
function readFilters(){
 ui.filters={
   search:document.getElementById("fSearch").value.trim(),
   from:document.getElementById("fFrom").value,
   to:document.getElementById("fTo").value,
   device:document.getElementById("fDevice").value,
   location:document.getElementById("fLocation").value,
   type:document.getElementById("fType").value,
   condition:document.getElementById("fCondition").value
 };ui.page=1;render()
}
function clearFilters(){
 ["fSearch","fFrom","fTo","fDevice","fLocation","fType","fCondition"].forEach(id=>document.getElementById(id).value="");
 ui.filters={search:"",from:"",to:"",device:"",location:"",type:"",condition:""};ui.page=1;render()
}
function filtered(){
 const f=ui.filters,q=f.search.toLowerCase();
 return state.events.filter(e=>{
   const d=device(e.deviceId);
   const text=[d?.code,d?.name,d?.serial,d?.location,e.reason,e.conditionText,e.note,e.performedBy].join(" ").toLowerCase();
   return (!q||text.includes(q))&&(!f.from||e.date>=f.from)&&(!f.to||e.date<=f.to)&&(!f.device||e.deviceId===f.device)
     &&(!f.location||d?.location===f.location)&&(!f.type||e.activityType===f.type)&&(!f.condition||e.condition===f.condition);
 }).sort((a,b)=>(b.date+b.updatedAt).localeCompare(a.date+a.updatedAt))
}
function paged(){
 const rows=filtered(),pages=Math.max(1,Math.ceil(rows.length/ui.size));ui.page=Math.max(1,Math.min(ui.page,pages));
 return rows.slice((ui.page-1)*ui.size,ui.page*ui.size)
}
function renderStats(rows){
 const repair=rows.filter(e=>["repair","maintenance"].includes(e.activityType)).length;
 const pending=rows.filter(e=>e.condition!=="normal").length;
 const devs=new Set(rows.map(e=>e.deviceId)).size;
 document.getElementById("stats").innerHTML=[
   ["≣",rows.length,"Sự kiện đang hiển thị"],
   ["▣",devs,"Thiết bị có dữ liệu"],
   ["⚙",repair,"Bảo dưỡng / sửa chữa"],
   ["!",pending,"Cần đánh giá / theo dõi"]
 ].map(x=>`<article class="stat"><div class="stat-ico">${x[0]}</div><div><strong>${x[1]}</strong><span>${x[2]}</span></div></article>`).join("")
}
function render(){
 const rows=filtered(),pageRows=paged(),pages=Math.max(1,Math.ceil(rows.length/ui.size));
 renderStats(rows);
 document.getElementById("countText").textContent=`${rows.length} bản ghi`;
 const active=Object.values(ui.filters).filter(Boolean).length,chip=document.getElementById("filterChip");
 chip.style.display=active?"inline-flex":"none";chip.textContent=`${active} điều kiện lọc`;
 document.getElementById("tbody").innerHTML=pageRows.length?pageRows.map(rowHtml).join(""):`<tr><td colspan="10"><div class="empty"><div class="ico">⌕</div><strong>Không tìm thấy dữ liệu</strong><span>Thử thay đổi bộ lọc hoặc thêm sự kiện mới.</span></div></td></tr>`;
 const start=rows.length?(ui.page-1)*ui.size+1:0,end=Math.min(ui.page*ui.size,rows.length);
 document.getElementById("rangeText").textContent=`${start}–${end} / ${rows.length} bản ghi`;
 document.getElementById("pageText").textContent=`Trang ${ui.page}/${pages}`;
 document.getElementById("prev").disabled=ui.page<=1;document.getElementById("next").disabled=ui.page>=pages;
 document.getElementById("pageSize").value=String(ui.size);
 const all=pageRows.length&&pageRows.every(x=>ui.selected.has(x.id));document.getElementById("checkAll").checked=!!all;
 updateBulk()
}
function rowHtml(e){
 const d=device(e.deviceId),selected=ui.selected.has(e.id);
 return `<tr class="${selected?"selected":""}">
  <td class="check"><input type="checkbox" ${selected?"checked":""} onchange="toggleSelect('${e.id}',this.checked)"></td>
  <td><div class="maincell">${fmtDate(e.date)}</div><div class="subcell">${esc(e.id)}</div></td>
  <td><span class="code">${esc(d?.code||"—")}</span><div class="subcell">${esc(d?.name||"")}</div></td>
  <td><div class="maincell">${esc(d?.location||"—")}</div><div class="subcell">S/N: ${esc(d?.serial||"—")}</div></td>
  <td>${typeTag(e.activityType)}</td>
  <td><div class="reason" title="${esc(e.reason)}">${esc(e.reason||"—")}</div></td>
  <td>${conditionTag(e.condition)}<div class="subcell">${esc(e.conditionText||"Chưa có mô tả")}</div></td>
  <td><div class="note" title="${esc(e.note)}">${esc(e.note||"—")}</div></td>
  <td><div class="maincell">${fmtDate(e.updatedAt.slice(0,10))}</div><div class="subcell">${new Date(e.updatedAt).toLocaleTimeString("vi-VN",{hour:"2-digit",minute:"2-digit"})}</div></td>
  <td><div class="row-actions"><button class="btn sm icon" title="Xem chi tiết" onclick="openDetail('${e.id}')">◉</button><button class="btn sm icon" title="Chỉnh sửa" onclick="openForm('${e.id}')">✎</button><button class="btn sm icon" title="Nhân bản" onclick="duplicateOne('${e.id}')">⧉</button><button class="btn sm icon danger" title="Xóa" onclick="deleteOne('${e.id}')">×</button></div></td>
 </tr>`
}
function toggleSelect(id,on){on?ui.selected.add(id):ui.selected.delete(id);render()}
function clearSelection(){ui.selected.clear();render()}
function updateBulk(){const n=ui.selected.size;document.getElementById("bulkbar").classList.toggle("show",n>0);document.getElementById("selectedText").textContent=`${n} bản ghi đã chọn`}
function scrollTopTable(){document.querySelector(".table-wrap").scrollTo({top:0,behavior:"smooth"})}

function openDetail(id){
 const e=state.events.find(x=>x.id===id);if(!e)return;const d=device(e.deviceId);
 document.getElementById("drawerTitle").textContent=`${d?.code||"Chi tiết sự kiện"}`;
 document.getElementById("drawerSub").textContent=`${e.id} · Phiên bản ${e.version||1}`;
 document.getElementById("drawerBody").innerHTML=`
  <div class="detail-grid">
   <div class="detail"><label>Ngày thực hiện</label><strong>${fmtDate(e.date)}</strong></div>
   <div class="detail"><label>Loại hoạt động</label>${typeTag(e.activityType)}</div>
   <div class="detail full"><label>Thiết bị</label><strong>${esc(d?.code)} — ${esc(d?.name)}</strong><div class="subcell">S/N: ${esc(d?.serial)} · ${esc(d?.location)} · ${esc(d?.department)}</div></div>
   <div class="detail full"><label>Lý do / nội dung thực hiện</label><div class="longtext">${esc(e.reason||"Không có nội dung")}</div></div>
   <div class="detail"><label>Tình trạng chuẩn hóa</label>${conditionTag(e.condition)}</div>
   <div class="detail"><label>Người thực hiện</label><strong>${esc(e.performedBy||"Chưa ghi nhận")}</strong></div>
   <div class="detail full"><label>Mô tả tình trạng sau xử lý</label><div class="longtext">${esc(e.conditionText||"Chưa đánh giá")}</div></div>
   <div class="detail full"><label>Ghi chú</label><div class="longtext">${esc(e.note||"Không có ghi chú")}</div></div>
  </div>
  <h3 style="font-size:11px;margin:0 0 12px">Lịch sử bản ghi</h3>
  <div class="timeline">${(e.history||[]).length?(e.history||[]).map(h=>`<div class="timeline-item"><strong>${esc(h.action)} · ${esc(user(h.userId)?.name||"Hệ thống")}</strong><span>${fmtDateTime(h.time)} — ${esc(h.detail)}</span></div>`).join(""):`<div class="timeline-item"><strong>Nhập dữ liệu ban đầu</strong><span>${fmtDateTime(e.createdAt)} — Chuẩn hóa từ biểu mẫu Excel.</span></div>`}</div>`;
 document.getElementById("drawerFoot").innerHTML=`<button class="btn" onclick="duplicateOne('${id}')">⧉ Nhân bản</button><button class="btn primary" onclick="closeDrawer();openForm('${id}')">✎ Chỉnh sửa</button>`;
 document.getElementById("drawerBg").classList.add("show")
}
function closeDrawer(){document.getElementById("drawerBg").classList.remove("show")}
document.getElementById("drawerBg").addEventListener("click",e=>{if(e.target.id==="drawerBg")closeDrawer()});

function openForm(id="",prefill={}){
 const old=id?state.events.find(x=>x.id===id):null;
 const e=old||{date:prefill.date||today(),deviceId:prefill.deviceId||"",activityType:prefill.activityType||"decontamination",reason:prefill.reason||"",condition:prefill.condition||"normal",conditionText:prefill.conditionText||"Hoạt động bình thường",note:prefill.note||"",performedBy:prefill.performedBy||""};
 openModal(old?"Cập nhật sự kiện":"Thêm sự kiện khử nhiễm","Thông tin thiết bị tự lấy từ danh mục, không cần nhập lại tên, mã, seri và vị trí.",`
  <form class="form-grid" id="eventForm">
   <div class="field"><label>Ngày thực hiện *</label><input name="date" type="date" required value="${e.date}"></div>
   <div class="field"><label>Thiết bị *</label><select name="deviceId" id="formDevice" required><option value="">Chọn thiết bị</option>${state.devices.map(d=>`<option value="${d.id}" ${d.id===e.deviceId?"selected":""}>${esc(d.code)} — ${esc(d.name)}</option>`).join("")}</select></div>
   <div class="field"><label>Loại hoạt động *</label><select name="activityType">${typeOptions(e.activityType)}</select></div>
   <div class="field"><label>Người thực hiện</label><input name="performedBy" value="${esc(e.performedBy)}" placeholder="Kỹ thuật viên / đơn vị thực hiện"></div>
   <div class="field full"><label>Lý do / nội dung thực hiện *</label><textarea name="reason" id="formReason" required placeholder="Mô tả công việc đã thực hiện...">${esc(e.reason)}</textarea>
    <div class="form-tools"><button class="btn sm" type="button" onclick="copyLatestReason()">Dùng nội dung gần nhất của thiết bị</button></div></div>
   <div class="field"><label>Tình trạng chuẩn hóa *</label><select name="condition" id="formCondition" onchange="syncConditionText()">${conditionOptions(e.condition)}</select></div>
   <div class="field"><label>Mô tả tình trạng sau xử lý</label><input name="conditionText" id="formConditionText" value="${esc(e.conditionText)}" placeholder="Ví dụ: Hoạt động bình thường"></div>
   <div class="field full"><label>Ghi chú</label><textarea name="note" style="min-height:70px" placeholder="Vật tư thay thế, nội dung cần tiếp tục theo dõi...">${esc(e.note)}</textarea><span class="help">Trường ghi chú dùng cho nội dung phát sinh; không cần lặp lại lý do thực hiện.</span></div>
  </form>`,old?"Lưu thay đổi":"Thêm sự kiện",()=>{
   const x=Object.fromEntries(new FormData(document.getElementById("eventForm")).entries());
   if(!x.date||!x.deviceId||!x.reason.trim()){toast("Vui lòng nhập đủ ngày, thiết bị và nội dung thực hiện","error");return}
   if(old){Object.assign(old,x,{updatedAt:new Date().toISOString(),version:(old.version||1)+1});addHistory(old,"UPDATE","Cập nhật thông tin sự kiện.")}
   else{const n={id:"evt-"+Date.now(),...x,createdBy:state.currentUserId,createdAt:new Date().toISOString(),updatedAt:new Date().toISOString(),version:1,history:[]};addHistory(n,"CREATE","Tạo sự kiện mới.");state.events.unshift(n)}
   save(old?"Đã cập nhật sự kiện":"Đã thêm sự kiện");closeModal();render()
 })
}
function syncConditionText(){
 const c=document.getElementById("formCondition").value,input=document.getElementById("formConditionText");
 if(!input.value.trim()||Object.values(CONDITIONS).some(x=>x[0]===input.value))input.value=CONDITIONS[c][0]
}
function copyLatestReason(){
 const did=document.getElementById("formDevice").value;if(!did){toast("Chọn thiết bị trước","error");return}
 const latest=state.events.filter(e=>e.deviceId===did).sort((a,b)=>b.date.localeCompare(a.date))[0];
 if(!latest){toast("Thiết bị chưa có lịch sử","error");return}
 document.getElementById("formReason").value=latest.reason;
 document.getElementById("formCondition").value=latest.condition;
 document.getElementById("formConditionText").value=latest.conditionText;
 toast("Đã lấy nội dung gần nhất")
}
function deleteOne(id){
 const e=state.events.find(x=>x.id===id);if(!e)return;
 if(confirm(`Xóa sự kiện ngày ${fmtDate(e.date)} của ${device(e.deviceId)?.code}?`)){state.events=state.events.filter(x=>x.id!==id);ui.selected.delete(id);save("Đã xóa sự kiện");closeDrawer();render()}
}
function duplicateOne(id){
 const e=state.events.find(x=>x.id===id);if(!e)return;
 openForm("",{...clone(e),date:today()});closeDrawer()
}
function duplicateSelected(){
 const rows=state.events.filter(e=>ui.selected.has(e.id));if(!rows.length)return;
 const now=Date.now();rows.forEach((e,i)=>{const n={...clone(e),id:`evt-${now}-${i}`,date:today(),createdBy:state.currentUserId,createdAt:new Date().toISOString(),updatedAt:new Date().toISOString(),version:1,history:[]};addHistory(n,"CREATE","Nhân bản hàng loạt từ "+e.id);state.events.unshift(n)});
 ui.selected.clear();save(`Đã nhân bản ${rows.length} sự kiện sang hôm nay`);render()
}
function deleteSelected(){
 const n=ui.selected.size;if(!n)return;
 if(confirm(`Xóa ${n} sự kiện đã chọn?`)){state.events=state.events.filter(e=>!ui.selected.has(e.id));ui.selected.clear();save(`Đã xóa ${n} sự kiện`);render()}
}

function openBatch(){
 openModal("Nhập nhanh nhiều thiết bị","Phù hợp khi thực hiện khử nhiễm hoặc bảo dưỡng định kỳ cho nhiều máy trong cùng một ngày.",`
  <div class="quick-head">
   <div class="field"><label>Ngày thực hiện</label><input id="batchDate" type="date" value="${today()}"></div>
   <div class="field"><label>Loại hoạt động chung</label><select id="batchType">${typeOptions("decontamination")}</select></div>
   <div class="field"><label>Tình trạng chung</label><select id="batchCondition">${conditionOptions("normal")}</select></div>
   <span class="push"><button class="btn" type="button" onclick="fillBatchCommon()">Áp dụng nội dung chung</button></span>
  </div>
  <div class="batch-wrap"><table class="batch"><thead><tr><th style="width:40px">Nhập</th><th>Thiết bị</th><th>Loại hoạt động</th><th>Lý do / nội dung</th><th>Tình trạng sau xử lý</th><th>Ghi chú</th></tr></thead><tbody>
   ${state.devices.map(d=>`<tr data-device="${d.id}"><td><input class="b-check" type="checkbox"></td><td class="device-cell"><strong>${esc(d.code)}</strong><span>${esc(d.name)} · ${esc(d.location)}</span></td><td><select class="b-type">${typeOptions("decontamination")}</select></td><td><textarea class="b-reason" placeholder="Nội dung thực hiện..."></textarea></td><td><select class="b-condition">${conditionOptions("normal")}</select></td><td><input class="b-note" placeholder="Không bắt buộc"></td></tr>`).join("")}
  </tbody></table></div>`,`Lưu các dòng đã chọn`,saveBatch,true);
 QMSPreset.attach("batch",{host:".quick-head .push",collect:collectDevicePreset,apply:applyDevicePreset});
}
function fillBatchCommon(){
 const type=document.getElementById("batchType").value,condition=document.getElementById("batchCondition").value;
 document.querySelectorAll(".batch tbody tr").forEach(tr=>{tr.querySelector(".b-type").value=type;tr.querySelector(".b-condition").value=condition});
 toast("Đã áp dụng loại hoạt động và tình trạng chung")
}
function saveBatch(){
 const date=document.getElementById("batchDate").value;
 const rows=[...document.querySelectorAll(".batch tbody tr")].filter(tr=>tr.querySelector(".b-check").checked);
 if(!date||!rows.length){toast("Chọn ngày và ít nhất một thiết bị","error");return}
 let created=0;
 for(const [i,tr] of rows.entries()){
   const reason=tr.querySelector(".b-reason").value.trim();
   if(!reason){toast(`Thiết bị ${device(tr.dataset.device)?.code} chưa có nội dung thực hiện`,"error");return}
   const condition=tr.querySelector(".b-condition").value;
   const n={id:`evt-${Date.now()}-${i}`,date,deviceId:tr.dataset.device,activityType:tr.querySelector(".b-type").value,reason,condition,conditionText:CONDITIONS[condition][0],note:tr.querySelector(".b-note").value.trim(),performedBy:"",createdBy:state.currentUserId,createdAt:new Date().toISOString(),updatedAt:new Date().toISOString(),version:1,history:[]};
   addHistory(n,"CREATE","Tạo bằng chức năng nhập nhiều thiết bị.");state.events.unshift(n);created++
 }
 save(`Đã thêm ${created} sự kiện`);closeModal();render()
}

function openModal(title,sub,body,saveLabel,handler,wide=false){
 document.getElementById("modalTitle").textContent=title;document.getElementById("modalSub").textContent=sub||"";
 document.getElementById("modalBody").innerHTML=body;document.getElementById("modalFoot").innerHTML=`<button class="btn" onclick="closeModal()">Hủy</button>${saveLabel?`<button class="btn primary" id="modalSave">${saveLabel}</button>`:""}`;
 document.getElementById("modalBox").classList.toggle("wide",wide);document.getElementById("modalBg").classList.add("show");if(saveLabel)document.getElementById("modalSave").onclick=handler
}
function closeModal(){document.getElementById("modalBg").classList.remove("show")}
document.getElementById("modalBg").addEventListener("click",e=>{if(e.target.id==="modalBg")closeModal()});

function csv(v){return `"${String(v??"").replace(/"/g,'""')}"`}
function exportCsv(){
 const rows=[["Ngày","Mã thiết bị","Tên thiết bị","Seri","Vị trí","Loại hoạt động","Lý do/Nội dung","Tình trạng chuẩn hóa","Tình trạng sau xử lý","Người thực hiện","Ghi chú"],...filtered().map(e=>{const d=device(e.deviceId);return[e.date,d?.code,d?.name,d?.serial,d?.location,TYPES[e.activityType]?.[0],e.reason,CONDITIONS[e.condition]?.[0],e.conditionText,e.performedBy,e.note]})];
 const blob=new Blob(["\ufeff"+rows.map(r=>r.map(csv).join(",")).join("\n")],{type:"text/csv;charset=utf-8"});const a=document.createElement("a");a.href=URL.createObjectURL(blob);a.download="theo-doi-khu-nhiem-trang-thiet-bi.csv";a.click();URL.revokeObjectURL(a.href);toast(`Đã xuất ${rows.length-1} bản ghi`)
}
window.addEventListener("storage",e=>{if(e.key===KEY&&e.newValue){try{state=JSON.parse(e.newValue);render();toast("Dữ liệu vừa được cập nhật từ tab khác")}catch(_){}}});
(async()=>{try{state=await load();await QMSPreset.init({url:window.QMS_DEV.preset,csrf:window.QMS_DEV.csrf});init()}catch(e){document.getElementById('toastWrap')&&toast('Lỗi tải dữ liệu: '+e.message,'error');console.error(e)}})();

/* ==== mẫu mặc định cho form nhập nhiều thiết bị ==== */
function collectDevicePreset(){
 return {type:document.getElementById("batchType").value,
  condition:document.getElementById("batchCondition").value,
  rows:[...document.querySelectorAll(".batch tbody tr")].map(tr=>({
    device:tr.dataset.device, on:tr.querySelector(".b-check").checked,
    type:tr.querySelector(".b-type").value, reason:tr.querySelector(".b-reason").value,
    condition:tr.querySelector(".b-condition").value, note:tr.querySelector(".b-note").value}))};
}
function applyDevicePreset(p){
 if(p.type)document.getElementById("batchType").value=p.type;
 if(p.condition)document.getElementById("batchCondition").value=p.condition;
 (p.rows||[]).forEach(r=>{
   const tr=document.querySelector('.batch tbody tr[data-device="'+r.device+'"]');if(!tr)return;
   tr.querySelector(".b-check").checked=!!r.on;
   if(r.type)tr.querySelector(".b-type").value=r.type;
   if(r.condition)tr.querySelector(".b-condition").value=r.condition;
   tr.querySelector(".b-reason").value=r.reason||"";
   tr.querySelector(".b-note").value=r.note||"";
 });
}
</script>
</body>
</html>