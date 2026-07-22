<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Module nhật ký sử dụng thiết bị</title>
<style>
:root{
  --bg:#f4f7f8;--panel:#fff;--line:#dde6e9;--line2:#edf2f4;
  --text:#14222b;--muted:#6b7d87;--primary:#0d6d77;--primary2:#09545d;
  --primary-soft:#e7f5f5;--green:#23845e;--green-soft:#e9f7f0;
  --amber:#a86d14;--amber-soft:#fff6e5;--red:#bd3f4c;--red-soft:#fff0f2;
  --gray-soft:#f1f4f5;--shadow:0 18px 50px rgba(25,44,51,.10);--radius:15px
}
*{box-sizing:border-box}
html{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text)}
body{margin:0}
button,input,select,textarea{font:inherit}
button{cursor:pointer}
.shell{min-height:100vh;padding:22px}
.app{max-width:1560px;margin:auto}
.breadcrumb{font-size:11px;color:var(--muted);margin-bottom:10px}
.breadcrumb b{color:var(--text)}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:13px}
.page-head h1{font-size:25px;letter-spacing:-.03em;margin:0 0 5px}
.page-head p{font-size:12px;color:var(--muted);margin:0;line-height:1.55}
.actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.btn{height:37px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;padding:0 12px;font-size:11px;font-weight:750;display:inline-flex;gap:7px;align-items:center;justify-content:center;white-space:nowrap}
.btn:hover{border-color:#b6c5ca;box-shadow:0 5px 14px rgba(30,50,58,.07)}
.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}
.btn.soft{background:var(--primary-soft);border-color:#c7e5e7;color:var(--primary2)}
.btn.danger{background:var(--red-soft);border-color:#f0cbd0;color:var(--red)}
.btn.sm{height:30px;font-size:10px;padding:0 9px;border-radius:7px}
.btn.icon{width:31px;padding:0}
.btn:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
.device-bar{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:13px;display:grid;grid-template-columns:minmax(310px,1.25fr) repeat(3,minmax(150px,.55fr)) auto;gap:10px;align-items:center;margin-bottom:11px;box-shadow:0 4px 20px rgba(27,44,52,.035)}
.device-select label,.mini-field label{display:block;font-size:8px;text-transform:uppercase;letter-spacing:.075em;color:var(--muted);margin-bottom:5px}
.device-select select,.mini-field input,.mini-field select{width:100%;height:39px;border:1px solid var(--line);border-radius:9px;background:#fbfcfd;padding:0 10px;font-size:11px;font-weight:700;outline:0}
.device-meta strong{font-size:12px;display:block}.device-meta span{font-size:9px;color:var(--muted);display:block;margin-top:3px}
.notice{display:flex;gap:10px;align-items:flex-start;border:1px solid #cbe4e6;background:linear-gradient(120deg,#eef9f9,#fbfdfd);border-radius:11px;padding:11px 13px;margin-bottom:11px}
.notice strong{font-size:11px;display:block}.notice p{font-size:9px;color:#5e7780;margin:3px 0 0;line-height:1.5}
.notice .push{margin-left:auto;display:flex;gap:7px}
.stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px;margin-bottom:11px}
.stat{background:#fff;border:1px solid var(--line);border-radius:11px;padding:11px 12px;display:flex;gap:10px;align-items:center}
.stat-icon{width:34px;height:34px;border-radius:9px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;font-weight:900}
.stat strong{display:block;font-size:18px;line-height:1.05}.stat span{font-size:9px;color:var(--muted)}
.panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 18px rgba(31,45,61,.035)}
.toolbar{display:flex;align-items:center;gap:7px;flex-wrap:wrap;padding:11px 13px;background:#fbfcfd;border-bottom:1px solid var(--line)}
.toolbar .push{margin-left:auto}
.inline{position:relative}
.inline input,.inline select{height:34px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:0 9px;font-size:10px;outline:0}
.search input{width:260px;padding-left:31px}
.search:before{content:"⌕";position:absolute;left:10px;top:5px;font-size:16px;color:#81939d}
.month-nav{display:flex;align-items:center;gap:5px;border:1px solid var(--line);background:#fff;border-radius:9px;padding:3px}
.month-nav button{width:29px;height:27px;border:0;background:transparent;border-radius:6px}
.month-nav button:hover{background:var(--gray-soft)}
.month-nav strong{font-size:10px;min-width:90px;text-align:center}
.bulk{display:none;align-items:center;gap:8px;padding:9px 13px;background:#eef8f8;border-bottom:1px solid #cce5e6;font-size:10px}
.bulk.show{display:flex}.bulk strong{color:var(--primary)}.bulk .push{margin-left:auto}
.table-wrap{overflow:auto;max-height:calc(100vh - 350px)}
table{width:100%;border-collapse:separate;border-spacing:0;min-width:1280px}
th{position:sticky;top:0;z-index:5;text-align:left;background:#f7fafb;border-bottom:1px solid var(--line);padding:9px 10px;font-size:8px;text-transform:uppercase;letter-spacing:.065em;color:#74868f;white-space:nowrap}
td{padding:9px 10px;border-bottom:1px solid var(--line2);font-size:10px;background:#fff;vertical-align:middle}
tbody tr:hover td{background:#fbfdfd}
tbody tr.selected td{background:#f0f9f9}
tbody tr.off td{background:#fafbfb;color:#89979e}
.check{width:36px;text-align:center}
.main{font-weight:760}.sub{font-size:8.5px;color:var(--muted);margin-top:3px}
.badge{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:9px;font-weight:800;white-space:nowrap}
.badge.confirmed{background:var(--green-soft);color:var(--green)}
.badge.pending{background:var(--amber-soft);color:var(--amber)}
.badge.off{background:var(--gray-soft);color:#77868e}
.badge.changed{background:#edf3fb;color:#366f9b}
.badge.issue{background:var(--red-soft);color:var(--red)}
.hours{display:inline-flex;min-width:42px;justify-content:center;padding:5px 7px;border-radius:7px;background:#f0f5f6;font-weight:800}
.signature{display:flex;align-items:center;gap:7px}
.signature-dot{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#d8ecee,#f6fbfb);border:1px solid #cde0e2;display:grid;place-items:center;color:var(--primary);font-weight:900;font-size:9px}
.note{max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#5f7079}
.row-actions{display:flex;gap:4px;white-space:nowrap}
.pagination{display:flex;align-items:center;justify-content:space-between;padding:10px 13px;background:#fbfcfd;border-top:1px solid var(--line);font-size:9px;color:var(--muted)}
.empty{text-align:center;padding:50px;color:var(--muted)}
.modal-bg{position:fixed;inset:0;background:rgba(13,28,34,.40);backdrop-filter:blur(3px);z-index:80;display:none;align-items:center;justify-content:center;padding:18px}
.modal-bg.show{display:flex}
.modal{width:min(720px,100%);max-height:94vh;overflow:auto;background:#fff;border-radius:16px;box-shadow:var(--shadow)}
.modal-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:12px}
.modal-head h2{font-size:15px;margin:0 0 4px}.modal-head p{font-size:9px;color:var(--muted);margin:0}
.close{width:29px;height:29px;border:0;background:#f0f3f4;border-radius:8px}
.modal-body{padding:18px}.modal-foot{padding:13px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.field{display:grid;gap:5px}.field.full{grid-column:1/-1}
.field label{font-size:9px;font-weight:780;color:#42545e}
.field input,.field select,.field textarea{width:100%;border:1px solid var(--line);border-radius:9px;padding:9px 10px;font-size:10px;outline:0;background:#fff}
.field textarea{min-height:84px;resize:vertical}
.help{font-size:8.5px;color:var(--muted);line-height:1.45}
.settings{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.setting-card{border:1px solid var(--line);border-radius:11px;padding:12px;background:#fbfcfd}
.setting-card h3{font-size:11px;margin:0 0 10px}
.audit-note{padding:10px 12px;border:1px solid #ecdbae;background:var(--amber-soft);color:#72501b;border-radius:9px;font-size:9px;line-height:1.5;margin-bottom:12px}
.toast-wrap{position:fixed;right:20px;bottom:20px;z-index:120;display:grid;gap:7px}
.toast{background:#153039;color:#fff;padding:10px 13px;border-radius:9px;font-size:10px;box-shadow:var(--shadow)}
.toast.error{background:#8f313b}
@media(max-width:1150px){
  .device-bar{grid-template-columns:1fr 1fr 1fr}.device-select{grid-column:1/-1}.device-bar>.btn{width:100%}
  .stats{grid-template-columns:repeat(3,1fr)}.page-head{flex-direction:column}.table-wrap{max-height:none}
}
@media(max-width:700px){
  .shell{padding:13px}.device-bar{grid-template-columns:1fr}.stats{grid-template-columns:1fr 1fr}
  .search,.search input,.inline,.inline input,.inline select{width:100%}.toolbar .push{margin-left:0;width:100%}
  .notice{flex-direction:column}.notice .push{margin-left:0;flex-wrap:wrap}
  .form-grid,.settings{grid-template-columns:1fr}.field.full{grid-column:auto}
}
</style>
<script>window.QMS_USE={state:"{{ route('usage.state') }}",save:"{{ route('usage.save') }}",flow:"{{ route('flow.state') }}",preset:"{{ route('preset.index', 'usage') }}",csrf:"{{ csrf_token() }}"};</script>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=8">
<script src="{{ asset('js/qms-select.js') }}?v=3"></script>
<script src="{{ asset('js/qms-preset.js') }}?v=1"></script>
<script src="{{ asset('js/qms-flow.js') }}?v=1"></script>
</head>
<body class="{{ request()->boolean('embed') ? 'qs-embed' : '' }}">
@unless(request()->boolean('embed'))
@include('modules._sidebar')
@endunless
<div class="shell"><main class="app">
  <div class="breadcrumb">Quản lý trang thiết bị <span>›</span> <b>Nhật ký sử dụng thiết bị</b></div>

  <section class="page-head">
    <div>
      <h1>Nhật ký sử dụng thiết bị</h1>
      <p>Quản lý theo từng thiết bị và từng tháng. Ngày bình thường được xác nhận nhanh; chỉ mở biểu mẫu chi tiết khi số giờ, tình trạng hoặc ghi chú có thay đổi.</p>
    </div>
    <div class="actions">
      <button class="btn" onclick="openSettings()">⚙ Thiết lập thiết bị</button>
      <button class="btn" onclick="exportCsv()">⇩ Xuất nhật ký</button>
      <button class="btn primary" onclick="closeMonth()">✓ Chốt sổ tháng</button>
    </div>
  </section>

  <section class="device-bar">
    <div class="device-select">
      <label>Thiết bị đang xem</label>
      <select id="deviceSelect"></select>
    </div>
    <div class="device-meta"><label>Mã thiết bị</label><strong id="deviceCode"></strong><span id="deviceSerial"></span></div>
    <div class="device-meta"><label>Vị trí</label><strong id="deviceLocation"></strong><span id="deviceName"></span></div>
    <div class="device-meta"><label>Mẫu mặc định</label><strong id="deviceDefault"></strong><span>Chỉ là dữ liệu gợi ý</span></div>
    <button class="btn soft" onclick="openQuickMonthForm()">＋ Thêm nhanh biểu mẫu tháng</button>
  </section>

  <section class="notice">
    <div>ⓘ</div>
    <div>
      <strong>Không tự động tạo bản ghi đã ký</strong>
      <p>Hệ thống chỉ sinh các dòng ở trạng thái “Chờ xác nhận”. Người sử dụng phải xác nhận thực tế; chữ ký, thời gian xác nhận và lịch sử sửa đổi được lưu riêng cho từng ngày.</p>
    </div>
    <div class="push">
      <button class="btn sm" onclick="selectPending()">Chọn ngày chờ xác nhận</button>
      <button class="btn sm primary" onclick="quickConfirm()">Xác nhận nhanh</button>
    </div>
  </section>

  <section class="stats" id="stats"></section>

  <section class="panel">
    <div class="toolbar">
      <div class="month-nav">
        <button onclick="changeMonth(-1)">‹</button>
        <strong id="monthLabel"></strong>
        <button onclick="changeMonth(1)">›</button>
      </div>
      <div class="inline search"><input id="search" placeholder="Tìm người sử dụng, ghi chú..."></div>
      <div class="inline"><select id="statusFilter">
        <option value="">Tất cả trạng thái</option>
        <option value="confirmed">Đã xác nhận</option>
        <option value="pending">Chờ xác nhận</option>
        <option value="changed">Khác mẫu</option>
        <option value="issue">Có bất thường</option>
        <option value="off">Không sử dụng</option>
      </select></div>
      <div class="inline"><select id="userFilter"><option value="">Tất cả người sử dụng</option></select></div>
      <button class="btn sm" onclick="clearFilters()">Xóa lọc</button>
      <span class="push"></span>
      <button class="btn sm" onclick="markOffSelected()">Đánh dấu không sử dụng</button>
      <button class="btn sm primary" onclick="quickConfirm()">Xác nhận các ngày đã chọn</button>
    </div>

    <div class="bulk" id="bulk">
      <strong id="selectedText"></strong>
      <span>Dữ liệu mặc định sẽ được điền nhưng vẫn phải xác nhận.</span>
      <span class="push"></span>
      <button class="btn sm" onclick="clearSelection()">Bỏ chọn</button>
    </div>

    <div class="table-wrap">
      <table>
        <thead><tr>
          <th class="check"><input type="checkbox" id="checkAll"></th>
          <th>Ngày</th>
          <th>Người sử dụng</th>
          <th>Chữ ký</th>
          <th>Tổng số giờ</th>
          <th>Tình trạng thiết bị</th>
          <th>Ghi chú</th>
          <th>Trạng thái bản ghi</th>
          <th>Xác nhận lúc</th>
          <th>Thao tác</th>
        </tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>

    <div class="pagination">
      <span id="rangeText"></span>
      <span>Nhật ký theo tháng · mỗi ngày là một bản ghi độc lập</span>
    </div>
  </section>
</main></div>

<div class="modal-bg" id="modalBg">
  <div class="modal">
    <div class="modal-head">
      <div><h2 id="modalTitle"></h2><p id="modalSub"></p></div>
      <button class="close" onclick="closeModal()">×</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-foot" id="modalFoot"></div>
  </div>
</div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
let devices=[];
let users=[];
const KEY="equipment-usage-log-v4";
let month=new Date().toISOString().slice(0,7),deviceId="",selected=new Set(),filters={search:"",status:"",user:""};
let state=null;

async function load(){const r=await fetch(window.QMS_USE.state,{credentials:'same-origin'});if(!r.ok)throw new Error('Không tải được dữ liệu');const j=await r.json();devices=j.devices||[];users=j.users||[];return {records:j.records||{},closedMonths:j.closedMonths||{}}}
function save(msg){fetch(window.QMS_USE.save,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.QMS_USE.csrf,'Accept':'application/json'},body:JSON.stringify({records:state.records,closedMonths:state.closedMonths,devices:devices})}).then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);if(msg)toast(msg)}).catch(e=>toast('Lưu thất bại: '+e.message,'error'))}
function device(){return devices.find(x=>x.id===deviceId)}
function key(){return deviceId+"-"+month}
function esc(v){return String(v??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}
function pad(n){return String(n).padStart(2,"0")}
function toDate(s){return new Date(s+"T00:00:00")}
function viDate(s){return toDate(s).toLocaleDateString("vi-VN",{weekday:"short",day:"2-digit",month:"2-digit"})}
function nowText(){return new Date().toLocaleString("vi-VN")}
function initials(name){return name.split(" ").slice(-2).map(x=>x[0]).join("").toUpperCase()}
function toast(msg,type=""){const e=document.createElement("div");e.className="toast "+type;e.textContent=msg;document.getElementById("toastWrap").appendChild(e);setTimeout(()=>e.remove(),2600)}
function isWeekend(s){const d=toDate(s).getDay();return d===0||d===6}
function monthDays(monthValue=month){
  const [y,m]=monthValue.split("-").map(Number),n=new Date(y,m,0).getDate();
  return Array.from({length:n},(_,i)=>`${y}-${pad(m)}-${pad(i+1)}`)
}
function records(){
  if(!state.records[key()])state.records[key()]=[];
  return state.records[key()]
}
function record(date){return records().find(x=>x.date===date)}
function isWorkingDate(date,pattern){
  const day=toDate(date).getDay();
  if(pattern==="all")return true;
  if(pattern==="mon-sat")return day!==0;
  return day!==0&&day!==6;
}
function openQuickMonthForm(){
  const d=device();
  openModal(
    "Thêm nhanh biểu mẫu cho một tháng",
    "Sinh đồng loạt các dòng nhật ký trong tháng. Mọi dòng mới đều ở trạng thái chờ xác nhận.",
    `<div class="audit-note">
      Chức năng này chỉ tạo dữ liệu gợi ý. Người sử dụng vẫn phải xác nhận thực tế từng ngày hoặc xác nhận nhanh nhiều ngày.
    </div>
    <div class="qp-host"></div>
    <form class="form-grid" id="quickMonthForm">
      <div class="field">
        <label>Thiết bị *</label>
        <select name="deviceId">
          ${devices.map(x=>`<option value="${x.id}" ${x.id===deviceId?"selected":""}>${esc(x.code)} — ${esc(x.name)}</option>`).join("")}
        </select>
      </div>

      <div class="field">
        <label>Tháng lập biểu mẫu *</label>
        <input name="month" type="month" value="${month}">
      </div>

      <div class="field">
        <label>Người sử dụng mặc định</label>
        <select name="user">
          <option value="">Chưa gán — xác nhận sau</option>
          ${users.map(x=>`<option>${esc(x)}</option>`).join("")}
        </select>
      </div>

      <div class="field">
        <label>Số giờ mặc định *</label>
        <input name="hours" type="number" min="0" step="0.5" value="${d.defaultHours}">
      </div>

      <div class="field">
        <label>Lịch sử dụng dự kiến *</label>
        <select name="workPattern">
          <option value="mon-fri">Thứ Hai đến Thứ Sáu</option>
          <option value="mon-sat">Thứ Hai đến Thứ Bảy</option>
          <option value="all">Tất cả các ngày</option>
        </select>
      </div>

      <div class="field">
        <label>Xử lý ngày ngoài lịch</label>
        <select name="offMode">
          <option value="off">Đánh dấu “Không sử dụng”</option>
          <option value="skip">Không tạo dòng</option>
        </select>
      </div>

      <div class="field full">
        <label>Tình trạng gợi ý *</label>
        <select name="condition">
          <option>Thiết bị hoạt động bình thường</option>
          <option>Thiết bị hoạt động nhưng cần theo dõi</option>
          <option>Thiết bị có bất thường</option>
          <option>Ngừng sử dụng để kiểm tra</option>
        </select>
      </div>

      <div class="field full">
        <label>Khi tháng đã có dữ liệu</label>
        <select name="existingMode">
          <option value="missing">Chỉ thêm những ngày còn thiếu</option>
          <option value="replace">Tạo lại toàn bộ tháng</option>
        </select>
        <span class="help">“Tạo lại toàn bộ tháng” sẽ xóa dữ liệu hiện có của thiết bị trong tháng được chọn.</span>
      </div>

      <div class="field full">
        <label>Ghi chú mặc định</label>
        <textarea name="note" placeholder="Không bắt buộc"></textarea>
      </div>
    </form>`,
    "Tạo biểu mẫu tháng",
    saveQuickMonth
  );

  const deviceField=document.querySelector('#quickMonthForm [name="deviceId"]');
  const hoursField=document.querySelector('#quickMonthForm [name="hours"]');
  deviceField.onchange=()=>{
    const selectedDevice=devices.find(x=>x.id===deviceField.value);
    if(selectedDevice)hoursField.value=selectedDevice.defaultHours
  };
  QMSPreset.attach("month",{host:"#modalBody .qp-host",collect:collect_month,apply:apply_month});
}
function saveQuickMonth(){
  const form=document.getElementById("quickMonthForm");
  const x=Object.fromEntries(new FormData(form).entries());
  if(!x.deviceId||!x.month){toast("Vui lòng chọn thiết bị và tháng","error");return}
  if(x.hours===""||Number(x.hours)<0){toast("Số giờ mặc định không hợp lệ","error");return}

  const targetDevice=devices.find(d=>d.id===x.deviceId);
  const targetKey=`${x.deviceId}-${x.month}`;
  const existing=state.records[targetKey]||[];

  if(x.existingMode==="replace"&&existing.length){
    if(!confirm(`Tạo lại toàn bộ tháng ${x.month.split("-").reverse().join("/")} và xóa ${existing.length} dòng hiện có?`))return;
    state.records[targetKey]=[]
  }else if(!state.records[targetKey]){
    state.records[targetKey]=[]
  }

  const rows=state.records[targetKey];
  const existingDates=new Set(rows.map(r=>r.date));
  let added=0;

  monthDays(x.month).forEach(date=>{
    if(x.existingMode==="missing"&&existingDates.has(date))return;

    const working=isWorkingDate(date,x.workPattern);
    if(!working&&x.offMode==="skip")return;

    const status=working?"pending":"off";
    rows.push({
      id:`${targetKey}-${date}-${Date.now()}-${added}`,
      date,
      user:working?x.user:"",
      hours:working?Number(x.hours):"",
      condition:working?x.condition:"",
      note:working?x.note.trim():"",
      status,
      confirmedAt:"",
      version:1
    });
    added++
  });

  rows.sort((a,b)=>a.date.localeCompare(b.date));
  deviceId=x.deviceId;
  month=x.month;
  selected.clear();
  document.getElementById("deviceSelect").value=deviceId;
  save(`Đã thêm nhanh ${added} dòng cho tháng ${month.split("-").reverse().join("/")}`);
  closeModal();
  render()
}
function filtered(){
  const q=filters.search.toLowerCase();
  return records().filter(r=>{
    const text=[r.user,r.condition,r.note].join(" ").toLowerCase();
    return(!q||text.includes(q))&&(!filters.status||r.status===filters.status)&&(!filters.user||r.user===filters.user)
  })
}
function statusLabel(r){
  const map={
    confirmed:["confirmed","Đã xác nhận"],
    pending:["pending","Chờ xác nhận"],
    off:["off","Không sử dụng"],
    changed:["changed","Khác mẫu"],
    issue:["issue","Có bất thường"]
  };
  return map[r.status]||map.pending
}
function render(){
  const d=device();
  document.getElementById("deviceCode").textContent=d.code;
  document.getElementById("deviceSerial").textContent="Seri: "+d.serial;
  document.getElementById("deviceLocation").textContent=d.location;
  document.getElementById("deviceName").textContent=d.name;
  document.getElementById("deviceDefault").textContent=`${d.defaultHours} giờ · Hoạt động bình thường`;
  const [y,m]=month.split("-");document.getElementById("monthLabel").textContent=`Tháng ${m}/${y}`;

  const all=records(),rows=filtered();
  const confirmed=all.filter(r=>["confirmed","changed","issue"].includes(r.status)).length;
  const pending=all.filter(r=>r.status==="pending").length;
  const changed=all.filter(r=>r.status==="changed").length;
  const issues=all.filter(r=>r.status==="issue").length;
  const totalHours=all.reduce((s,r)=>s+(Number(r.hours)||0),0);
  document.getElementById("stats").innerHTML=[
    ["▦",all.length,"Ngày trong tháng"],
    ["✓",confirmed,"Ngày đã xác nhận"],
    ["⌛",pending,"Ngày chờ xác nhận"],
    ["≠",changed,"Ngày khác mẫu"],
    ["Σ",totalHours,"Tổng giờ sử dụng"]
  ].map(x=>`<article class="stat"><div class="stat-icon">${x[0]}</div><div><strong>${x[1]}</strong><span>${x[2]}</span></div></article>`).join("");

  document.getElementById("tbody").innerHTML=rows.length?rows.map(rowHtml).join(""):`<tr><td colspan="10"><div class="empty"><strong>Tháng này chưa có biểu mẫu</strong><div>Bấm “Thêm nhanh biểu mẫu tháng” để sinh các dòng nhật ký.</div></div></td></tr>`;
  document.getElementById("rangeText").textContent=`${rows.length}/${all.length} ngày`;
  const visibleIds=new Set(rows.map(r=>r.id));
  document.getElementById("checkAll").checked=rows.length>0&&rows.every(r=>selected.has(r.id));
  selected.forEach(id=>{if(!all.some(r=>r.id===id))selected.delete(id)});
  updateBulk()
}
function rowHtml(r){
  const [cls,label]=statusLabel(r),sel=selected.has(r.id),off=r.status==="off";
  return `<tr class="${sel?"selected ":""}${off?"off":""}">
    <td class="check"><input type="checkbox" ${sel?"checked":""} ${off?"disabled":""} onchange="toggle('${r.id}',this.checked)"></td>
    <td><div class="main">${viDate(r.date)}</div><div class="sub">${r.date}</div></td>
    <td><div class="main">${esc(r.user||"—")}</div><div class="sub">${r.user?"Người thực hiện":"Không phát sinh"}</div></td>
    <td>${r.user?`<div class="signature"><span class="signature-dot">${initials(r.user)}</span><span class="sub">Chữ ký hồ sơ</span></div>`:"—"}</td>
    <td><span class="hours">${r.hours!==""?esc(r.hours)+" giờ":"—"}</span></td>
    <td><div class="main">${esc(r.condition||"—")}</div></td>
    <td><div class="note" title="${esc(r.note)}">${esc(r.note||"—")}</div></td>
    <td><span class="badge ${cls}">${label}</span></td>
    <td><div class="main">${esc(r.confirmedAt||"—")}</div><div class="sub">Phiên bản ${r.version||1}</div></td>
    <td><div class="row-actions">
      <button class="btn sm icon" onclick="editRecord('${r.id}')">✎</button>
      ${off?`<button class="btn sm" onclick="activate('${r.id}')">Phát sinh</button>`:`<button class="btn sm icon" onclick="confirmOne('${r.id}')">✓</button>`}
    </div></td>
  </tr>`
}
function updateBulk(){
  const n=selected.size;document.getElementById("bulk").classList.toggle("show",n>0);
  document.getElementById("selectedText").textContent=`${n} ngày đã chọn`
}
function toggle(id,on){on?selected.add(id):selected.delete(id);render()}
function clearSelection(){selected.clear();render()}
function selectPending(){records().filter(r=>r.status==="pending").forEach(r=>selected.add(r.id));render()}
function confirmOne(id){selected.clear();selected.add(id);quickConfirm()}
function quickConfirm(){
  const ids=[...selected];
  if(!ids.length){toast("Hãy chọn ít nhất một ngày","error");return}
  const valid=records().filter(r=>ids.includes(r.id)&&r.status!=="off");
  if(!valid.length){toast("Không có ngày sử dụng để xác nhận","error");return}
  const defaultUser=valid[0].user||users[0];
  openModal("Xác nhận nhanh nhiều ngày","Điền mẫu chung cho các ngày được chọn; mỗi ngày vẫn lưu thành một bản ghi độc lập.",
  `<div class="audit-note">Việc xác nhận sẽ lưu người thao tác, thời điểm và phiên bản. Không dùng chức năng này cho những ngày chưa thực sự sử dụng thiết bị.</div>
   <form class="form-grid" id="confirmForm">
    <div class="field"><label>Người sử dụng *</label><select name="user">${users.map(x=>`<option ${x===defaultUser?"selected":""}>${esc(x)}</option>`).join("")}</select></div>
    <div class="field"><label>Tổng số giờ *</label><input name="hours" type="number" min="0" step="0.5" value="${device().defaultHours}"></div>
    <div class="field full"><label>Tình trạng *</label><select name="condition"><option>Thiết bị hoạt động bình thường</option><option>Thiết bị hoạt động nhưng cần theo dõi</option><option>Thiết bị có bất thường</option><option>Ngừng sử dụng để kiểm tra</option></select></div>
    <div class="field full"><label>Ghi chú chung</label><textarea name="note" placeholder="Không bắt buộc"></textarea><span class="help">Áp dụng cho ${valid.length} ngày được chọn.</span></div>
   </form>`,
  "Xác nhận",()=>{
    const x=Object.fromEntries(new FormData(document.getElementById("confirmForm")).entries());
    valid.forEach(r=>{
      r.user=x.user;r.hours=Number(x.hours);r.condition=x.condition;r.note=x.note.trim();
      r.status=x.condition==="Thiết bị hoạt động bình thường"?(Number(x.hours)===device().defaultHours?"confirmed":"changed"):"issue";
      r.confirmedAt=nowText();r.version=(r.version||1)+1
    });
    selected.clear();save(`Đã xác nhận ${valid.length} ngày`);closeModal();render()
  })
}
function editRecord(id){
  const r=records().find(x=>x.id===id);if(!r)return;
  openModal("Cập nhật ngày "+viDate(r.date),"Chỉ sửa khi dữ liệu thực tế khác mẫu hoặc cần bổ sung ghi chú.",
  `<form class="form-grid" id="editForm">
    <div class="field"><label>Ngày</label><input value="${r.date}" disabled></div>
    <div class="field"><label>Người sử dụng *</label><select name="user">${users.map(x=>`<option ${x===r.user?"selected":""}>${esc(x)}</option>`).join("")}</select></div>
    <div class="field"><label>Tổng số giờ *</label><input name="hours" type="number" min="0" step="0.5" value="${esc(r.hours||device().defaultHours)}"></div>
    <div class="field"><label>Phân loại ngày</label><select name="mode"><option value="use">Có sử dụng</option><option value="off" ${r.status==="off"?"selected":""}>Không sử dụng</option></select></div>
    <div class="field full"><label>Tình trạng thiết bị *</label><select name="condition">
      ${["Thiết bị hoạt động bình thường","Thiết bị hoạt động nhưng cần theo dõi","Thiết bị có bất thường","Ngừng sử dụng để kiểm tra"].map(x=>`<option ${x===r.condition?"selected":""}>${x}</option>`).join("")}
    </select></div>
    <div class="field full"><label>Ghi chú</label><textarea name="note">${esc(r.note)}</textarea></div>
   </form>`,
  "Lưu thay đổi",()=>{
    const x=Object.fromEntries(new FormData(document.getElementById("editForm")).entries());
    if(x.mode==="off"){
      Object.assign(r,{user:"",hours:"",condition:"",note:x.note.trim(),status:"off",confirmedAt:nowText(),version:(r.version||1)+1})
    }else{
      Object.assign(r,{user:x.user,hours:Number(x.hours),condition:x.condition,note:x.note.trim(),
        status:x.condition==="Thiết bị hoạt động bình thường"?(Number(x.hours)===device().defaultHours?"confirmed":"changed"):"issue",
        confirmedAt:nowText(),version:(r.version||1)+1})
    }
    save("Đã cập nhật bản ghi");closeModal();render()
  })
}
function activate(id){
  const r=records().find(x=>x.id===id);if(!r)return;
  r.user=users[0];r.hours=device().defaultHours;r.condition="Thiết bị hoạt động bình thường";r.status="pending";r.confirmedAt="";r.version++;
  save("Đã chuyển sang ngày có sử dụng");render()
}
function markOffSelected(){
  const ids=[...selected];if(!ids.length){toast("Hãy chọn ngày cần đánh dấu","error");return}
  records().filter(r=>ids.includes(r.id)).forEach(r=>Object.assign(r,{user:"",hours:"",condition:"",status:"off",confirmedAt:nowText(),version:(r.version||1)+1}));
  selected.clear();save(`Đã đánh dấu ${ids.length} ngày không sử dụng`);render()
}
function changeMonth(step){
  const [y,m]=month.split("-").map(Number),d=new Date(y,m-1+step,1);
  month=`${d.getFullYear()}-${pad(d.getMonth()+1)}`;selected.clear();render()
}
function clearFilters(){
  document.getElementById("search").value="";document.getElementById("statusFilter").value="";document.getElementById("userFilter").value="";
  filters={search:"",status:"",user:""};render()
}
function openSettings(){
  const d=device();
  openModal("Thiết lập thiết bị","Thông tin này được dùng làm mẫu gợi ý khi tạo sổ tháng.",
  `<div class="settings">
    <div class="setting-card"><h3>Thông tin nhận diện</h3>
      <div class="field"><label>Mã thiết bị</label><input value="${esc(d.code)}" disabled></div>
      <div class="field"><label>Tên thiết bị</label><input value="${esc(d.name)}" disabled></div>
      <div class="field"><label>Seri</label><input value="${esc(d.serial)}" disabled></div>
      <div class="field"><label>Vị trí</label><input value="${esc(d.location)}" disabled></div>
    </div>
    <div class="setting-card"><h3>Mẫu nhập nhanh</h3>
      <div class="field"><label>Số giờ tiêu chuẩn</label><input id="defaultHours" type="number" min="0" step="0.5" value="${d.defaultHours}"></div>
      <div class="field"><label>Tình trạng gợi ý</label><input value="Thiết bị hoạt động bình thường" disabled></div>
      <div class="field"><label>Ngày nghỉ mặc định</label><input value="Thứ Bảy, Chủ nhật" disabled></div>
      <span class="help">Mẫu chỉ giúp điền nhanh, không thay thế xác nhận thực tế.</span>
    </div>
  </div>`,
  "Lưu thiết lập",()=>{
    const v=Number(document.getElementById("defaultHours").value);if(v<0){toast("Số giờ không hợp lệ","error");return}
    d.defaultHours=v;save("Đã cập nhật thiết lập");closeModal();render()
  })
}
function closeMonth(){
  const all=records(),pending=all.filter(r=>r.status==="pending");
  if(pending.length){toast(`Còn ${pending.length} ngày chưa xác nhận`,"error");selectPending();return}
  const issues=all.filter(r=>r.status==="issue").length;
  openModal("Chốt sổ tháng","Sau khi chốt, dữ liệu nên được khóa và chỉ mở lại bởi người có quyền.",
  `<div class="audit-note">Tháng ${month.split("-").reverse().join("/")} có ${all.length} ngày, ${issues} ngày bất thường và không còn ngày chờ xác nhận.</div>
   <div class="field"><label>Người duyệt</label><select id="approver">${users.map(x=>`<option>${esc(x)}</option>`).join("")}</select></div>`,
  "Xác nhận chốt sổ",()=>{
    state.closedMonths[key()]={at:nowText(),by:document.getElementById("approver").value};
    save("Đã chốt sổ tháng");closeModal();render()
  })
}
function exportCsv(){
  const d=device(),data=[
    ["Mã thiết bị",d.code],["Tên thiết bị",d.name],["Seri",d.serial],["Vị trí",d.location],["Tháng",month],[],
    ["Ngày","Người sử dụng","Tổng số giờ","Tình trạng thiết bị","Ghi chú","Trạng thái","Xác nhận lúc"],
    ...records().map(r=>[r.date,r.user,r.hours,r.condition,r.note,statusLabel(r)[1],r.confirmedAt])
  ];
  const csv="\ufeff"+data.map(row=>row.map(v=>`"${String(v??"").replace(/"/g,'""')}"`).join(",")).join("\n");
  const blob=new Blob([csv],{type:"text/csv;charset=utf-8"}),a=document.createElement("a");
  a.href=URL.createObjectURL(blob);a.download=`nhat-ky-${d.code.replace(/\s+/g,"-")}-${month}.csv`;a.click();URL.revokeObjectURL(a.href)
}
function openModal(title,sub,body,label,handler){
  document.getElementById("modalTitle").textContent=title;document.getElementById("modalSub").textContent=sub||"";
  document.getElementById("modalBody").innerHTML=body;document.getElementById("modalFoot").innerHTML=`<button class="btn" onclick="closeModal()">Hủy</button><button class="btn primary" id="modalSave">${label}</button>`;
  document.getElementById("modalSave").onclick=handler;document.getElementById("modalBg").classList.add("show")
}
function closeModal(){document.getElementById("modalBg").classList.remove("show")}
document.getElementById("modalBg").addEventListener("click",e=>{if(e.target.id==="modalBg")closeModal()});

function init(){
  if(!deviceId&&devices.length)deviceId=devices[0].id;
  const ds=document.getElementById("deviceSelect");
  ds.innerHTML=devices.map(d=>`<option value="${d.id}">${d.code} — ${d.name}</option>`).join("");
  ds.value=deviceId;ds.onchange=()=>{deviceId=ds.value;selected.clear();render()};
  const uf=document.getElementById("userFilter");
  uf.innerHTML=`<option value="">Tất cả người sử dụng</option>`+users.map(x=>`<option>${esc(x)}</option>`).join("");
  document.getElementById("search").oninput=e=>{filters.search=e.target.value.trim();render()};
  document.getElementById("statusFilter").onchange=e=>{filters.status=e.target.value;render()};
  document.getElementById("userFilter").onchange=e=>{filters.user=e.target.value;render()};
  document.getElementById("checkAll").onchange=e=>{filtered().filter(r=>r.status!=="off").forEach(r=>e.target.checked?selected.add(r.id):selected.delete(r.id));render()};
  render()
}
(async()=>{try{state=await load();init();QMSSelect.auto();await QMSPreset.init({url:window.QMS_USE.preset,csrf:window.QMS_USE.csrf});QMSFlow.init({url:window.QMS_USE.flow,module:'usage',openers:{month:()=>openQuickMonthForm(),confirm:()=>selectPending()||quickConfirm()}})}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();
/* ==== bản mẫu mặc định cho popup tạo sổ tháng ==== */
function collect_month(){
 const f=document.getElementById("quickMonthForm"),g=n=>f.elements[n]?f.elements[n].value:"";
 return {deviceId:g("deviceId"),user:g("user"),hours:g("hours"),workPattern:g("workPattern"),
  condition:g("condition"),offMode:g("offMode"),existingMode:g("existingMode"),note:g("note")};
}
function apply_month(p){
 const f=document.getElementById("quickMonthForm");if(!f)return;
 Object.entries(p||{}).forEach(([k,v])=>{if(f.elements[k]&&v!==null&&v!==undefined&&k!=="month")f.elements[k].value=v});
 QMSSelect.refresh();
}
</script>
</body>
</html>