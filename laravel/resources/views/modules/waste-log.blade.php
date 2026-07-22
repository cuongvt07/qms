<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Module nhật ký xử lý rác thải</title>
<style>
:root{--bg:#f5f7fb;--panel:#fff;--line:#e4e9ef;--line2:#edf1f5;--text:#182433;--muted:#66758a;--primary:#176b87;--primary2:#10536a;--primary-soft:#eaf6f9;--red:#c43e4c;--red-soft:#fff0f2;--amber:#a86c17;--amber-soft:#fff6e5;--shadow:0 15px 42px rgba(27,42,54,.12);--radius:14px}
*{box-sizing:border-box}html{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}body{margin:0}button,input,select,textarea{font:inherit}
.shell{min-height:100vh;padding:24px}.module{max-width:1650px;margin:auto}.breadcrumb{display:flex;gap:7px;align-items:center;color:var(--muted);font-size:11px;margin-bottom:10px}.breadcrumb b{color:var(--text)}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:13px}.page-head h1{font-size:25px;letter-spacing:-.025em;margin:0 0 5px}.page-head p{font-size:12px;color:var(--muted);margin:0;line-height:1.5}
.actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.btn{height:36px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;padding:0 12px;font-size:11px;font-weight:750;display:inline-flex;align-items:center;justify-content:center;gap:7px;cursor:pointer;transition:.15s;white-space:nowrap}.btn:hover{border-color:#bdc8d3;box-shadow:0 5px 15px rgba(31,45,61,.07)}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}.btn.primary:hover{background:var(--primary2)}.btn.danger{color:var(--red);background:var(--red-soft);border-color:#f1c8ce}.btn.sm{height:29px;padding:0 8px;font-size:10px;border-radius:7px}.btn.icon{width:29px;padding:0}.btn:disabled{opacity:.42;cursor:not-allowed;box-shadow:none}
.userbox{display:flex;align-items:center;gap:7px;padding:4px 8px;border:1px solid var(--line);background:#fff;border-radius:9px}.userbox span{font-size:9px;color:var(--muted)}.userbox select{border:0;background:transparent;outline:0;font-size:10px;font-weight:750;max-width:175px}
.notice{display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border:1px solid #cfe4e9;background:linear-gradient(120deg,#f0f9fb,#fbfdfe);border-radius:11px;margin-bottom:10px}.notice strong{display:block;font-size:11px}.notice p{font-size:9px;color:#5d7480;margin:3px 0 0;line-height:1.5}.notice .push{margin-left:auto;display:flex;gap:7px}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-bottom:11px}.stat{background:#fff;border:1px solid var(--line);border-radius:11px;padding:11px 13px;display:flex;align-items:center;gap:10px}.stat-icon{width:33px;height:33px;border-radius:9px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;font-weight:900}.stat strong{display:block;font-size:17px}.stat span{font-size:9px;color:var(--muted)}
.panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 18px rgba(31,45,61,.035)}.filters{display:flex;align-items:center;gap:7px;flex-wrap:wrap;padding:12px 13px;background:#fbfcfd;border-bottom:1px solid var(--line)}.inline{position:relative}.inline input,.inline select{height:35px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:0 9px;font-size:10px;color:var(--text);outline:0}.search input{width:280px;padding-left:31px}.search:before{content:"⌕";position:absolute;left:10px;top:6px;font-size:16px;color:#8796a8}.filter-meta{margin-left:auto;font-size:9px;color:var(--muted)}
.bulk{display:none;align-items:center;gap:8px;padding:9px 13px;background:#eef8fa;border-bottom:1px solid #cde4ea;font-size:10px}.bulk.show{display:flex}.bulk strong{color:var(--primary)}.bulk .push{margin-left:auto}
.table-wrap{overflow:auto;max-height:calc(100vh - 320px)}table{width:100%;border-collapse:separate;border-spacing:0;min-width:1480px}.data th{position:sticky;top:0;z-index:4;text-align:left;padding:9px 10px;background:#f8fafc;border-bottom:1px solid var(--line);font-size:8.3px;letter-spacing:.065em;text-transform:uppercase;color:#748397;white-space:nowrap}.data td{padding:9px 10px;border-bottom:1px solid var(--line2);font-size:10px;background:#fff;vertical-align:middle}.data tbody tr:hover td{background:#fbfdfe}.data tbody tr.selected td{background:#f2fafc}.check{width:36px;text-align:center}.maincell{font-weight:750}.subcell{font-size:8.5px;color:var(--muted);margin-top:3px}.waste{display:inline-flex;max-width:220px;padding:5px 7px;border-radius:6px;background:#f0f5f7;color:#315164;font-size:8.7px;font-weight:750;line-height:1.35}.textcell{max-width:220px;line-height:1.4}.note-cell{max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#5f6e7f}.row-actions{display:flex;gap:4px;white-space:nowrap}
.pagination{display:flex;align-items:center;justify-content:space-between;padding:11px 13px;background:#fbfcfd;border-top:1px solid var(--line);font-size:9px;color:var(--muted)}.page-left,.page-right{display:flex;align-items:center;gap:7px}.pagination select{border:1px solid var(--line);background:#fff;border-radius:7px;padding:4px 6px;font-size:9px}
.empty{text-align:center;padding:48px;color:var(--muted)}.empty strong{display:block;color:var(--text);margin-bottom:5px}.empty .ico{font-size:31px;margin-bottom:8px}
.drawer-bg{position:fixed;inset:0;background:rgba(13,27,36,.32);z-index:60;display:none}.drawer-bg.show{display:block}.drawer{position:absolute;right:0;top:0;bottom:0;width:min(620px,100%);background:#fff;box-shadow:-18px 0 55px rgba(17,30,40,.2);display:flex;flex-direction:column}.drawer-head{padding:18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}.drawer-head h2{font-size:16px;margin:0 0 4px}.drawer-head p{font-size:9px;color:var(--muted);margin:0}.close{width:29px;height:29px;border:0;background:#f0f3f6;border-radius:8px;cursor:pointer}.drawer-body{padding:18px;overflow:auto}.drawer-foot{margin-top:auto;border-top:1px solid var(--line);padding:13px 18px;display:flex;justify-content:flex-end;gap:8px}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px}.detail{background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:10px}.detail.full{grid-column:1/-1}.detail label{display:block;font-size:8px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:5px}.detail strong{font-size:11px;line-height:1.5}
.modal-bg{position:fixed;inset:0;background:rgba(12,25,34,.42);backdrop-filter:blur(3px);z-index:80;display:none;align-items:center;justify-content:center;padding:18px}.modal-bg.show{display:flex}.modal{width:min(700px,100%);max-height:94vh;background:#fff;border-radius:16px;overflow:auto;box-shadow:var(--shadow)}.modal.wide{width:min(1320px,100%)}.modal-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}.modal-head h2{font-size:15px;margin:0 0 4px}.modal-head p{font-size:9px;color:var(--muted);margin:0}.modal-body{padding:18px}.modal-foot{padding:13px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{display:grid;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:9px;font-weight:750;color:#425266}.field input,.field select,.field textarea{border:1px solid var(--line);border-radius:9px;padding:9px 10px;font-size:10px;outline:0;background:#fff}.field textarea{min-height:80px;resize:vertical}.help{font-size:8.5px;color:var(--muted)}
.session-head{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:11px;border:1px solid var(--line);border-radius:10px;background:#f8fafc;margin-bottom:10px}.batch-toolbar{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:9px}.batch-toolbar .push{margin-left:auto}.entry-wrap{overflow:auto;border:1px solid var(--line);border-radius:10px}.entry-table{min-width:1280px;border-collapse:collapse}.entry-table th{background:#f8fafc;font-size:8px;text-transform:uppercase;color:#748397;border-bottom:1px solid var(--line);padding:8px;text-align:left}.entry-table td{padding:7px;border-bottom:1px solid var(--line);vertical-align:top}.entry-table tr:last-child td{border-bottom:0}.entry-table input,.entry-table select,.entry-table textarea{width:100%;border:1px solid var(--line);border-radius:7px;padding:7px;font-size:9px;outline:0}.entry-table textarea{min-height:48px;resize:vertical}.entry-table .date-col{min-width:130px}.entry-table .time-col{min-width:92px}.entry-table .waste-col{min-width:220px}.entry-table .treatment-col{min-width:210px}.entry-table .location-col{min-width:195px}.entry-table .person-col{min-width:155px}.entry-table .note-col{min-width:180px}
.catalog-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}.catalog-grid textarea{min-height:230px;line-height:1.55}.settings-note{padding:10px 12px;border:1px solid #efdfba;background:var(--amber-soft);color:#76511c;border-radius:9px;font-size:9px;line-height:1.5;margin-bottom:12px}
.toast-wrap{position:fixed;right:20px;bottom:20px;z-index:120;display:grid;gap:7px}.toast{background:#172d38;color:#fff;padding:10px 13px;border-radius:9px;font-size:10px;box-shadow:var(--shadow)}.toast.error{background:#8d303a}
@media(max-width:1080px){.stats{grid-template-columns:repeat(2,1fr)}.page-head{flex-direction:column}.userbox{display:none}.table-wrap{max-height:none}}
@media(max-width:650px){.shell{padding:14px}.stats{grid-template-columns:1fr}.search,.search input,.inline{width:100%}.inline input,.inline select{width:100%}.form-grid,.detail-grid,.catalog-grid,.session-head{grid-template-columns:1fr}.field.full,.detail.full{grid-column:auto}.notice{flex-direction:column}.notice .push{margin-left:0}}
</style>
<script>window.QMS_WASTE={state:"{{ route('waste.state') }}",save:"{{ route('waste.save') }}",flow:"{{ route('flow.state') }}",preset:"{{ route('preset.index', 'waste') }}",csrf:"{{ csrf_token() }}"};</script>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=8">
<script src="{{ asset('js/qms-preset.js') }}?v=1"></script>
<script src="{{ asset('js/qms-flow.js') }}?v=1"></script>
<script src="{{ asset('js/qms-dup.js') }}?v=1"></script>
<script src="{{ asset('js/qms-select.js') }}?v=2"></script>
</head>
<body class="{{ request()->boolean('embed') ? 'qs-embed' : '' }}">
@unless(request()->boolean('embed'))
@include('modules._sidebar')
@endunless
<div class="shell"><main class="module">
  <div class="breadcrumb"><span>Quản lý chất thải</span><span>›</span><b>Nhật ký xử lý rác thải</b></div>

  <section class="page-head">
    <div>
      <h1>Nhật ký xử lý rác thải</h1>
      <p>Mỗi dòng là một phát sinh độc lập. Trong cùng một phiên, ngày, giờ, loại rác, cách xử lý, vị trí và người thực hiện đều có thể khác nhau.</p>
    </div>
    <div class="actions">
      <div class="userbox"><span>Người thao tác</span><select id="currentUser"></select></div>
      <button class="btn" onclick="openCatalogs()">⚙ Danh mục</button>
      <button class="btn" onclick="exportCsv()">⇩ Xuất dữ liệu</button>
      <button class="btn primary" onclick="openBatchEntry()">＋ Nhập nhiều dòng</button>
    </div>
  </section>

  <section class="notice">
    <div>ⓘ</div>
    <div>
      <strong>Không dùng thông tin mặc định cho toàn bộ bảng</strong>
      <p>Phần chung chỉ còn khoa/đơn vị và ghi chú phiên. Mỗi dòng tự chọn ngày, giờ, loại rác, xử lý, vị trí tập kết, người thực hiện và ghi chú.</p>
    </div>
    <div class="push"><button class="btn sm" onclick="copyLatestBatch()">Sao chép phiên gần nhất</button><button class="btn sm" onclick="openSingleForm()">Thêm một dòng</button></div>
  </section>

  <section class="stats" id="stats"></section>

  <section class="panel">
    <div class="filters">
      <div class="inline search"><input id="fSearch" placeholder="Tìm loại rác, xử lý, vị trí, người thực hiện..."></div>
      <div class="inline"><input id="fFrom" type="date"></div>
      <div class="inline"><input id="fTo" type="date"></div>
      <div class="inline"><select id="fWaste"><option value="">Tất cả loại rác</option></select></div>
      <div class="inline"><select id="fLocation"><option value="">Tất cả vị trí</option></select></div>
      <div class="inline"><select id="fPerformer"><option value="">Tất cả người thực hiện</option></select></div>
      <button class="btn sm" onclick="clearFilters()">Xóa lọc</button>
      <div class="filter-meta" id="countText"></div>
    </div>

    <div class="bulk" id="bulkbar">
      <strong id="selectedText"></strong>
      <button class="btn sm" onclick="duplicateSelected()">Nhân bản sang hôm nay</button><button class="btn sm" onclick="dupSelectedDays()">🗓 Nhân bản sang nhiều ngày</button>
      <button class="btn sm danger" onclick="deleteSelected()">Xóa đã chọn</button>
      <span class="push"></span><button class="btn sm" onclick="clearSelection()">Bỏ chọn</button>
    </div>

    <div class="table-wrap">
      <table class="data">
        <thead><tr>
          <th class="check"><input id="checkAll" type="checkbox"></th>
          <th>Ngày / giờ</th><th>Loại rác</th><th>Xử lý</th><th>Vị trí tập kết</th><th>Người thực hiện</th><th>Ghi chú</th><th>Phiên nhập</th><th>Cập nhật</th><th>Thao tác</th>
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

<div class="drawer-bg" id="drawerBg"><aside class="drawer"><div class="drawer-head"><div><h2 id="drawerTitle"></h2><p id="drawerSub"></p></div><button class="close" onclick="closeDrawer()">×</button></div><div class="drawer-body" id="drawerBody"></div><div class="drawer-foot" id="drawerFoot"></div></aside></div>
<div class="modal-bg" id="modalBg"><div class="modal" id="modalBox"><div class="modal-head"><div><h2 id="modalTitle"></h2><p id="modalSub"></p></div><button class="close" onclick="closeModal()">×</button></div><div class="modal-body" id="modalBody"></div><div class="modal-foot" id="modalFoot"></div></div></div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
const SEED=null,KEY="waste-treatment-log-module-v2";
let state=null,ui={page:1,size:20,selected:new Set(),filters:{search:"",from:"",to:"",waste:"",location:"",performer:""}},entryCounter=0;

function clone(v){return JSON.parse(JSON.stringify(v))}
async function load(){const r=await fetch(window.QMS_WASTE.state,{credentials:'same-origin'});if(!r.ok)throw new Error('Không tải được dữ liệu');return await r.json()}
function save(msg){fetch(window.QMS_WASTE.save,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_WASTE.csrf,'Accept':'application/json'},body:JSON.stringify(state)}).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);if(msg)toast(msg)}).catch(e=>toast('Lưu thất bại: '+e.message,'error'))}
function esc(v){return String(v??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}
function dmy(v){return v?new Date(v+"T00:00:00").toLocaleDateString("vi-VN"):"—"}
function dt(v){return v?new Date(v).toLocaleString("vi-VN"):"—"}
function today(){return new Date().toISOString().slice(0,10)}
function user(id){return state.users.find(x=>x.id===id)}
function batch(id){return state.batches.find(x=>x.id===id)}
function toast(msg,type=""){const e=document.createElement("div");e.className="toast "+type;e.textContent=msg;document.getElementById("toastWrap").appendChild(e);setTimeout(()=>e.remove(),2800)}
function options(items,selected="",blank=""){return (blank?`<option value="">${blank}</option>`:"")+items.map(x=>`<option value="${esc(x)}" ${x===selected?"selected":""}>${esc(x)}</option>`).join("")}
function userOptions(selected=""){return state.users.filter(u=>u.id!=="u-admin").map(u=>`<option value="${u.id}" ${u.id===selected?"selected":""}>${esc(u.name)}</option>`).join("")}

function init(){
 const cu=document.getElementById("currentUser");cu.innerHTML=state.users.map(u=>`<option value="${u.id}" ${u.id===state.currentUserId?"selected":""}>${esc(u.name)}</option>`).join("");cu.onchange=()=>{state.currentUserId=cu.value;save();render()};
 refreshFilters();
 ["fSearch","fFrom","fTo","fWaste","fLocation","fPerformer"].forEach(id=>document.getElementById(id).addEventListener(id==="fSearch"?"input":"change",readFilters));
 document.getElementById("pageSize").onchange=e=>{ui.size=Number(e.target.value);ui.page=1;render()};
 document.getElementById("prev").onclick=()=>{ui.page--;render()};
 document.getElementById("next").onclick=()=>{ui.page++;render()};
 document.getElementById("checkAll").onchange=e=>{pagedRows().forEach(r=>e.target.checked?ui.selected.add(r.id):ui.selected.delete(r.id));render()};
 render()
}
function refreshFilters(){
 document.getElementById("fWaste").innerHTML=`<option value="">Tất cả loại rác</option>`+state.catalogs.wasteTypes.map(x=>`<option value="${esc(x)}">${esc(x)}</option>`).join("");
 document.getElementById("fLocation").innerHTML=`<option value="">Tất cả vị trí</option>`+state.catalogs.locations.map(x=>`<option value="${esc(x)}">${esc(x)}</option>`).join("");
 document.getElementById("fPerformer").innerHTML=`<option value="">Tất cả người thực hiện</option>`+userOptions()
}
function readFilters(){ui.filters={search:document.getElementById("fSearch").value.trim(),from:document.getElementById("fFrom").value,to:document.getElementById("fTo").value,waste:document.getElementById("fWaste").value,location:document.getElementById("fLocation").value,performer:document.getElementById("fPerformer").value};ui.page=1;render()}
function clearFilters(){["fSearch","fFrom","fTo","fWaste","fLocation","fPerformer"].forEach(id=>document.getElementById(id).value="");ui.filters={search:"",from:"",to:"",waste:"",location:"",performer:""};ui.page=1;render()}
function filtered(){
 const f=ui.filters,q=f.search.toLowerCase();
 return state.rows.filter(r=>{const text=[r.wasteType,r.treatment,r.location,user(r.performerId)?.name,r.note].join(" ").toLowerCase();return(!q||text.includes(q))&&(!f.from||r.date>=f.from)&&(!f.to||r.date<=f.to)&&(!f.waste||r.wasteType===f.waste)&&(!f.location||r.location===f.location)&&(!f.performer||r.performerId===f.performer)}).sort((a,b)=>(b.date+b.time+b.updatedAt).localeCompare(a.date+a.time+a.updatedAt))
}
function pagedRows(){const rows=filtered(),pages=Math.max(1,Math.ceil(rows.length/ui.size));ui.page=Math.max(1,Math.min(ui.page,pages));return rows.slice((ui.page-1)*ui.size,ui.page*ui.size)}
function render(){
 const rows=filtered(),page=pagedRows(),pages=Math.max(1,Math.ceil(rows.length/ui.size));
 const sessions=new Set(rows.map(r=>r.batchId)).size,types=new Set(rows.map(r=>r.wasteType)).size,people=new Set(rows.map(r=>r.performerId)).size;
 document.getElementById("stats").innerHTML=[["≣",rows.length,"Dòng nhật ký"],["▦",sessions,"Phiên nhập"],["♻",types,"Loại rác"],["♙",people,"Người thực hiện"]].map(x=>`<article class="stat"><div class="stat-icon">${x[0]}</div><div><strong>${x[1]}</strong><span>${x[2]}</span></div></article>`).join("");
 document.getElementById("countText").textContent=`${rows.length} dòng`;
 document.getElementById("tbody").innerHTML=page.length?page.map(rowHtml).join(""):`<tr><td colspan="10"><div class="empty"><div class="ico">⌕</div><strong>Không có dữ liệu</strong><span>Thử thay đổi bộ lọc hoặc nhập phiên mới.</span></div></td></tr>`;
 const start=rows.length?(ui.page-1)*ui.size+1:0,end=Math.min(ui.page*ui.size,rows.length);document.getElementById("rangeText").textContent=`${start}–${end} / ${rows.length} dòng`;document.getElementById("pageText").textContent=`Trang ${ui.page}/${pages}`;document.getElementById("prev").disabled=ui.page<=1;document.getElementById("next").disabled=ui.page>=pages;document.getElementById("pageSize").value=String(ui.size);
 document.getElementById("checkAll").checked=!!(page.length&&page.every(r=>ui.selected.has(r.id)));updateBulk()
}
function rowHtml(r){
 const b=batch(r.batchId),selected=ui.selected.has(r.id);
 return `<tr class="${selected?"selected":""}"><td class="check"><input type="checkbox" ${selected?"checked":""} onchange="toggleSelect('${r.id}',this.checked)"></td><td><div class="maincell">${dmy(r.date)} · ${esc(r.time)}</div><div class="subcell">${esc(r.id)}</div></td><td><span class="waste">${esc(r.wasteType)}</span></td><td><div class="textcell">${esc(r.treatment)}</div></td><td><div class="maincell">${esc(r.location)}</div></td><td><div class="maincell">${esc(user(r.performerId)?.name||"—")}</div></td><td><div class="note-cell" title="${esc(r.note)}">${esc(r.note||"—")}</div></td><td><button class="btn sm" onclick="openBatchDetail('${r.batchId}')">${state.rows.filter(x=>x.batchId===r.batchId).length} dòng</button><div class="subcell">${esc(b?.department||"")}</div></td><td><div class="maincell">${dmy(r.updatedAt.slice(0,10))}</div><div class="subcell">${new Date(r.updatedAt).toLocaleTimeString("vi-VN",{hour:"2-digit",minute:"2-digit"})}</div></td><td><div class="row-actions"><button class="btn sm icon" onclick="openDetail('${r.id}')">◉</button><button class="btn sm icon" onclick="openSingleForm('${r.id}')">✎</button><button class="btn sm icon" title="Nhân bản sang hôm nay" onclick="duplicateOne('${r.id}')">⧉</button><button class="btn sm icon" title="Nhân bản sang nhiều ngày" onclick="dupDays('${r.id}')">🗓</button><button class="btn sm icon danger" onclick="deleteOne('${r.id}')">×</button></div></td></tr>`
}
function toggleSelect(id,on){on?ui.selected.add(id):ui.selected.delete(id);render()}
function clearSelection(){ui.selected.clear();render()}
function updateBulk(){const n=ui.selected.size;document.getElementById("bulkbar").classList.toggle("show",n>0);document.getElementById("selectedText").textContent=`${n} dòng đã chọn`}

function entryRow(data={}){
 entryCounter++;
 return `<tr data-entry="${entryCounter}"><td><button class="btn sm icon danger" type="button" onclick="this.closest('tr').remove();updateSaveLabel()">×</button></td><td class="date-col"><input class="e-date" type="date" value="${data.date||today()}"></td><td class="time-col"><input class="e-time" type="time" value="${data.time||new Date().toTimeString().slice(0,5)}"></td><td class="waste-col"><select class="e-waste">${options(state.catalogs.wasteTypes,data.wasteType||"","Chọn loại rác")}</select></td><td class="treatment-col"><select class="e-treatment">${options(state.catalogs.treatments,data.treatment||"","Chọn xử lý")}</select></td><td class="location-col"><select class="e-location">${options(state.catalogs.locations,data.location||"","Chọn vị trí")}</select></td><td class="person-col"><select class="e-person">${userOptions(data.performerId||"")}</select></td><td class="note-col"><textarea class="e-note" placeholder="Không bắt buộc">${esc(data.note||"")}</textarea></td><td><button class="btn sm icon" type="button" onclick="duplicateEntryRow(this)">⧉</button></td></tr>`
}
function addRows(n=1,data=[]){const body=document.getElementById("entryBody");for(let i=0;i<n;i++)body.insertAdjacentHTML("beforeend",entryRow(data[i]||{}));updateSaveLabel()}
function duplicateEntryRow(button){const tr=button.closest("tr"),d={date:tr.querySelector(".e-date").value,time:tr.querySelector(".e-time").value,wasteType:tr.querySelector(".e-waste").value,treatment:tr.querySelector(".e-treatment").value,location:tr.querySelector(".e-location").value,performerId:tr.querySelector(".e-person").value,note:tr.querySelector(".e-note").value};tr.insertAdjacentHTML("afterend",entryRow(d));updateSaveLabel()}
function fillTodayBlank(){document.querySelectorAll("#entryBody .e-date").forEach(i=>{if(!i.value)i.value=today()});toast("Đã điền ngày hôm nay cho các dòng trống")}
function updateSaveLabel(){const n=document.querySelectorAll("#entryBody tr").length,b=document.getElementById("modalSave");if(b)b.textContent=`Lưu ${n} dòng`}
function collectRows(){return [...document.querySelectorAll("#entryBody tr")].map(tr=>({date:tr.querySelector(".e-date").value,time:tr.querySelector(".e-time").value,wasteType:tr.querySelector(".e-waste").value,treatment:tr.querySelector(".e-treatment").value,location:tr.querySelector(".e-location").value,performerId:tr.querySelector(".e-person").value,note:tr.querySelector(".e-note").value.trim()})).filter(r=>Object.values(r).some(Boolean))}

function openBatchEntry(editId="",prefill=null){
 const b=editId?batch(editId):null,source=prefill||(editId?state.rows.filter(r=>r.batchId===editId).sort((a,b)=>a.date.localeCompare(b.date)||a.time.localeCompare(b.time)):[]);
 openModal(b?"Chỉnh sửa phiên nhập":"Nhập nhiều dòng độc lập","Mỗi dòng tự có ngày, giờ, loại rác, cách xử lý, vị trí, người thực hiện và ghi chú.",`<div class="session-head"><div class="field"><label>Khoa / đơn vị</label><input id="sessionDepartment" value="${esc(b?.department||state.form.department)}"></div><div class="field"><label>Ghi chú phiên</label><input id="sessionNote" value="${esc(b?.note||"")}"></div></div><div class="batch-toolbar"><button class="btn sm" type="button" onclick="addRows(1)">＋ Thêm 1 dòng</button><button class="btn sm" type="button" onclick="addRows(5)">＋ Thêm 5 dòng</button><button class="btn sm" type="button" onclick="fillTodayBlank()">Điền hôm nay cho dòng trống</button><button class="btn sm" type="button" onclick="loadLatestRows()">Sao chép phiên gần nhất</button><span class="push"></span><span class="help">Mỗi dòng hoàn toàn độc lập.</span></div><div class="entry-wrap"><table class="entry-table"><thead><tr><th></th><th>Ngày *</th><th>Giờ *</th><th>Loại rác *</th><th>Xử lý *</th><th>Vị trí *</th><th>Người thực hiện *</th><th>Ghi chú</th><th>Nhân dòng</th></tr></thead><tbody id="entryBody"></tbody></table></div>`,source.length?`Lưu ${source.length} dòng`:"Lưu phiên",()=>saveBatch(editId),true);
 addRows(source.length||3,source.length?source:[{},{},{}]);
 QMSPreset.attach("batch",{host:".batch-toolbar .push",collect:collectWastePreset,apply:applyWastePreset,skip:!!editId||!!prefill});
}
function loadLatestRows(){
 const latest=[...state.batches].sort((a,b)=>b.createdAt.localeCompare(a.createdAt))[0];if(!latest){toast("Chưa có phiên trước","error");return}
 const rows=state.rows.filter(r=>r.batchId===latest.id).sort((a,b)=>a.time.localeCompare(b.time)).map(r=>({...clone(r),date:today()}));document.getElementById("entryBody").innerHTML="";addRows(rows.length,rows);toast(`Đã sao chép ${rows.length} dòng`)
}
function saveBatch(editId=""){
 const department=document.getElementById("sessionDepartment").value.trim(),note=document.getElementById("sessionNote").value.trim(),entries=collectRows();
 if(!department){toast("Vui lòng nhập khoa/đơn vị","error");return}
 if(!entries.length){toast("Chưa có dòng dữ liệu","error");return}
 const bad=entries.findIndex(r=>!r.date||!r.time||!r.wasteType||!r.treatment||!r.location||!r.performerId);if(bad>=0){toast(`Dòng ${bad+1} chưa đủ trường bắt buộc`,"error");return}
 const now=new Date().toISOString(),batchId=editId||`batch-${Date.now()}`;
 if(editId){const b=batch(editId);Object.assign(b,{department,note,updatedAt:now});state.rows=state.rows.filter(r=>r.batchId!==editId)}else state.batches.unshift({id:batchId,department,note,createdBy:state.currentUserId,createdAt:now,updatedAt:now});
 entries.forEach((r,i)=>state.rows.unshift({id:`row-${Date.now()}-${i}`,batchId,...r,createdAt:now,updatedAt:now,version:1,history:[]}));
 save(`Đã lưu ${entries.length} dòng độc lập`);closeModal();render();QMSFlow.done()
}
function copyLatestBatch(){const latest=[...state.batches].sort((a,b)=>b.createdAt.localeCompare(a.createdAt))[0];if(!latest){openBatchEntry();return}const rows=state.rows.filter(r=>r.batchId===latest.id).map(r=>({...clone(r),date:today()}));openBatchEntry("",rows)}
function copyBatch(id){const rows=state.rows.filter(r=>r.batchId===id).map(r=>({...clone(r),date:today()}));closeDrawer();openBatchEntry("",rows)}

function openSingleForm(id="",prefill={}){
 const old=id?state.rows.find(x=>x.id===id):null,r=old||{date:prefill.date||today(),time:prefill.time||new Date().toTimeString().slice(0,5),wasteType:prefill.wasteType||"",treatment:prefill.treatment||"",location:prefill.location||"",performerId:prefill.performerId||"",note:prefill.note||""};
 openModal(old?"Sửa dòng nhật ký":"Thêm một dòng","Một dòng có toàn bộ thông tin riêng.",`<form class="form-grid" id="singleForm"><div class="field"><label>Ngày *</label><input name="date" type="date" value="${r.date}"></div><div class="field"><label>Giờ *</label><input name="time" type="time" value="${r.time}"></div><div class="field full"><label>Loại rác *</label><select name="wasteType">${options(state.catalogs.wasteTypes,r.wasteType,"Chọn loại rác")}</select></div><div class="field full"><label>Xử lý *</label><select name="treatment">${options(state.catalogs.treatments,r.treatment,"Chọn xử lý")}</select></div><div class="field"><label>Vị trí *</label><select name="location">${options(state.catalogs.locations,r.location,"Chọn vị trí")}</select></div><div class="field"><label>Người thực hiện *</label><select name="performerId">${userOptions(r.performerId)}</select></div><div class="field full"><label>Ghi chú</label><textarea name="note">${esc(r.note)}</textarea></div></form>`,old?"Lưu thay đổi":"Thêm dòng",()=>{
  const x=Object.fromEntries(new FormData(document.getElementById("singleForm")).entries());if(!x.date||!x.time||!x.wasteType||!x.treatment||!x.location||!x.performerId){toast("Vui lòng nhập đủ trường bắt buộc","error");return}
  const now=new Date().toISOString();if(old)Object.assign(old,x,{updatedAt:now,version:(old.version||1)+1});else{const batchId=`batch-${Date.now()}`;state.batches.unshift({id:batchId,department:state.form.department,note:"Tạo từ một dòng",createdBy:state.currentUserId,createdAt:now,updatedAt:now});state.rows.unshift({id:`row-${Date.now()}`,batchId,...x,createdAt:now,updatedAt:now,version:1,history:[]})}
  save(old?"Đã cập nhật dòng":"Đã thêm dòng");closeModal();render();if(!old)QMSFlow.done()
 })
}

function openDetail(id){
 const r=state.rows.find(x=>x.id===id);if(!r)return;document.getElementById("drawerTitle").textContent=r.wasteType;document.getElementById("drawerSub").textContent=`${r.id} · ${r.batchId}`;document.getElementById("drawerBody").innerHTML=`<div class="detail-grid"><div class="detail"><label>Ngày / giờ</label><strong>${dmy(r.date)} · ${esc(r.time)}</strong></div><div class="detail"><label>Người thực hiện</label><strong>${esc(user(r.performerId)?.name||"—")}</strong></div><div class="detail full"><label>Loại rác</label><strong>${esc(r.wasteType)}</strong></div><div class="detail full"><label>Xử lý</label><strong>${esc(r.treatment)}</strong></div><div class="detail full"><label>Vị trí tập kết</label><strong>${esc(r.location)}</strong></div><div class="detail full"><label>Ghi chú</label><strong>${esc(r.note||"Không có ghi chú")}</strong></div></div>`;document.getElementById("drawerFoot").innerHTML=`<button class="btn" onclick="duplicateOne('${id}')">⧉ Nhân bản</button><button class="btn primary" onclick="closeDrawer();openSingleForm('${id}')">✎ Chỉnh sửa</button>`;document.getElementById("drawerBg").classList.add("show")
}
function openBatchDetail(id){
 const b=batch(id),rows=state.rows.filter(r=>r.batchId===id).sort((a,b)=>a.date.localeCompare(b.date)||a.time.localeCompare(b.time));document.getElementById("drawerTitle").textContent=`Phiên ${id}`;document.getElementById("drawerSub").textContent=`${rows.length} dòng độc lập · ${esc(b?.department||"")}`;document.getElementById("drawerBody").innerHTML=`<div class="detail-grid"><div class="detail full"><label>Ghi chú phiên</label><strong>${esc(b?.note||"Không có ghi chú")}</strong></div></div>${rows.map(r=>`<div class="detail" style="margin-bottom:7px"><label>${dmy(r.date)} · ${esc(r.time)}</label><strong>${esc(r.wasteType)}</strong><div class="subcell">${esc(r.treatment)} · ${esc(r.location)} · ${esc(user(r.performerId)?.name||"—")}</div></div>`).join("")}`;document.getElementById("drawerFoot").innerHTML=`<button class="btn" onclick="copyBatch('${id}')">⧉ Sao chép phiên</button><button class="btn primary" onclick="closeDrawer();openBatchEntry('${id}')">✎ Sửa phiên</button>`;document.getElementById("drawerBg").classList.add("show")
}
function closeDrawer(){document.getElementById("drawerBg").classList.remove("show")}
document.getElementById("drawerBg").addEventListener("click",e=>{if(e.target.id==="drawerBg")closeDrawer()});

function duplicateOne(id){const r=state.rows.find(x=>x.id===id);if(r)openSingleForm("",{...clone(r),date:today(),time:new Date().toTimeString().slice(0,5)})}
function duplicateSelected(){
 const src=state.rows.filter(r=>ui.selected.has(r.id));if(!src.length)return;const now=new Date().toISOString(),bid=`batch-${Date.now()}`;state.batches.unshift({id:bid,department:state.form.department,note:"Nhân bản hàng loạt",createdBy:state.currentUserId,createdAt:now,updatedAt:now});src.forEach((r,i)=>state.rows.unshift({...clone(r),id:`row-${Date.now()}-${i}`,batchId:bid,date:today(),createdAt:now,updatedAt:now,version:1,history:[]}));ui.selected.clear();save(`Đã nhân bản ${src.length} dòng`);render()
}
function deleteOne(id){const r=state.rows.find(x=>x.id===id);if(!r)return;if(confirm("Xóa dòng này?")){const bid=r.batchId;state.rows=state.rows.filter(x=>x.id!==id);if(!state.rows.some(x=>x.batchId===bid))state.batches=state.batches.filter(x=>x.id!==bid);save("Đã xóa dòng");closeDrawer();render()}}
function deleteSelected(){const n=ui.selected.size;if(n&&confirm(`Xóa ${n} dòng?`)){const bids=new Set(state.rows.filter(r=>ui.selected.has(r.id)).map(r=>r.batchId));state.rows=state.rows.filter(r=>!ui.selected.has(r.id));state.batches=state.batches.filter(b=>!bids.has(b.id)||state.rows.some(r=>r.batchId===b.id));ui.selected.clear();save(`Đã xóa ${n} dòng`);render()}}

function openCatalogs(){
 openModal("Danh mục dữ liệu","Mỗi dòng trong ô là một lựa chọn dropdown.",`<div class="settings-note">Danh mục giúp từng dòng vẫn khác nhau nhưng dữ liệu được chuẩn hóa, không bị sai chính tả.</div><form class="catalog-grid" id="catalogForm"><div class="field"><label>Loại rác</label><textarea name="wasteTypes">${esc(state.catalogs.wasteTypes.join("\n"))}</textarea></div><div class="field"><label>Cách xử lý</label><textarea name="treatments">${esc(state.catalogs.treatments.join("\n"))}</textarea></div><div class="field"><label>Vị trí tập kết</label><textarea name="locations">${esc(state.catalogs.locations.join("\n"))}</textarea></div></form>`,`Lưu danh mục`,()=>{const x=Object.fromEntries(new FormData(document.getElementById("catalogForm")).entries()),parse=v=>[...new Set(v.split("\n").map(s=>s.trim()).filter(Boolean))];state.catalogs={wasteTypes:parse(x.wasteTypes),treatments:parse(x.treatments),locations:parse(x.locations)};save("Đã cập nhật danh mục");closeModal();refreshFilters();render()})
}
function openModal(title,sub,body,label,handler,wide=false){document.getElementById("modalTitle").textContent=title;document.getElementById("modalSub").textContent=sub||"";document.getElementById("modalBody").innerHTML=body;document.getElementById("modalFoot").innerHTML=`<button class="btn" onclick="closeModal()">Hủy</button>${label?`<button class="btn primary" id="modalSave">${label}</button>`:""}`;document.getElementById("modalBox").classList.toggle("wide",wide);document.getElementById("modalBg").classList.add("show");if(label)document.getElementById("modalSave").onclick=handler}
function closeModal(){document.getElementById("modalBg").classList.remove("show")}
document.getElementById("modalBg").addEventListener("click",e=>{if(e.target.id==="modalBg")closeModal()});
function csv(v){return `"${String(v??"").replace(/"/g,'""')}"`}
function exportCsv(){const data=[["Ngày","Giờ","Loại rác","Xử lý","Vị trí","Người thực hiện","Ghi chú"],...filtered().map(r=>[r.date,r.time,r.wasteType,r.treatment,r.location,user(r.performerId)?.name,r.note])],blob=new Blob(["\ufeff"+data.map(r=>r.map(csv).join(",")).join("\n")],{type:"text/csv;charset=utf-8"}),a=document.createElement("a");a.href=URL.createObjectURL(blob);a.download="nhat-ky-xu-ly-rac-thai.csv";a.click();URL.revokeObjectURL(a.href)}
(async()=>{try{state=await load();await QMSPreset.init({url:window.QMS_WASTE.preset,csrf:window.QMS_WASTE.csrf});init();QMSSelect.auto();QMSFlow.init({url:window.QMS_WASTE.flow,module:'waste',openers:{batch:()=>openBatchEntry(),single:()=>openSingleForm()}})}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();

/* ==== mẫu mặc định cho form nhập nhiều dòng rác thải ==== */
function collectWastePreset(){
 return {department:document.getElementById("sessionDepartment").value.trim(),
  note:document.getElementById("sessionNote").value.trim(),
  rows:collectRows().map(r=>({time:r.time,wasteType:r.wasteType,treatment:r.treatment,
    location:r.location,performerId:r.performerId,note:r.note}))};
}
function applyWastePreset(p){
 if(p.department!=null)document.getElementById("sessionDepartment").value=p.department;
 if(p.note!=null)document.getElementById("sessionNote").value=p.note;
 const rows=(p.rows||[]).map(r=>Object.assign({},r,{date:today()}));
 if(rows.length){document.getElementById("entryBody").innerHTML="";addRows(rows.length,rows)}
 QMSSelect.refresh();
}

/* ==== nhân bản sang nhiều ngày ==== */
function dupDays(id){
 const r=state.rows.find(x=>x.id===id);if(!r)return;
 QMSDup.open({title:"Nhân bản dòng sang nhiều ngày",
  sub:`Nguồn: ${dmy(r.date)} ${esc(r.time)} · ${esc(r.wasteType)} — tích chọn ngày cần tạo bản sao.`,
  existing:state.rows.map(x=>x.date),
  onSave:(dates,opt)=>applyDupDays([r],dates,opt)});
}
function dupSelectedDays(){
 const rows=state.rows.filter(r=>ui.selected.has(r.id));
 if(!rows.length){toast("Chưa chọn dòng nào","error");return}
 QMSDup.open({title:`Nhân bản ${rows.length} dòng sang nhiều ngày`,
  sub:"Mỗi ngày được chọn sẽ tạo bản sao của các dòng đang tick.",
  existing:state.rows.map(x=>x.date),
  onSave:(dates,opt)=>applyDupDays(rows,dates,opt)});
}
function applyDupDays(src,dates,opt){
 const now=new Date().toISOString(),bid=`batch-${Date.now()}`;
 state.batches.unshift({id:bid,department:state.form.department,note:`Nhân bản ${src.length} dòng sang ${dates.length} ngày`,
   createdBy:state.currentUserId,createdAt:now,updatedAt:now});
 let them=0;
 dates.forEach((d,di)=>{
   if(opt.overwrite)state.rows=state.rows.filter(x=>!(x.date===d&&src.some(s=>s.wasteType===x.wasteType&&s.time===x.time)));
   src.forEach((r,i)=>{state.rows.unshift({...clone(r),id:`row-${Date.now()}-${di}-${i}`,batchId:bid,date:d,
     createdAt:now,updatedAt:now,version:1,history:[]});them++});
 });
 ui.selected.clear();
 save(`Đã nhân bản ${them} dòng sang ${dates.length} ngày`);
 closeModal();render();
}
</script>
</body>
</html>