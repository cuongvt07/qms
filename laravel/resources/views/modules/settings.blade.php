<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cấu hình chung — QMS</title>
<style>
:root{--bg:#f4f7fa;--card:#fff;--line:#e3e9ef;--text:#16242f;--muted:#5f7488;--brand:#0f766e;--brand2:#14b8a6}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 "Segoe UI",system-ui,-apple-system,sans-serif}
.shell{padding:20px 24px 40px;max-width:1280px}
.breadcrumb{font-size:11px;color:var(--muted);margin-bottom:6px}
.breadcrumb b{color:var(--text)}
.page-head{display:flex;align-items:center;gap:16px;margin-bottom:14px}
.page-head h1{font-size:21px;margin:0;letter-spacing:-.2px}
.page-head p{margin:2px 0 0;color:var(--muted);font-size:11.5px}
.page-head .right{margin-left:auto;display:flex;align-items:center;gap:8px}
.btn{height:34px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;padding:0 13px;
 font-size:11.5px;font-weight:750;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-family:inherit}
.btn:hover{border-color:#c9d6e2}
.btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
.btn.primary:hover{background:#0c5f59}
.btn.sm{height:28px;padding:0 9px;font-size:11px}
.btn.danger{color:#b42318;border-color:#f0cfcb;background:#fff6f5}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.tab{height:32px;padding:0 13px;border-radius:8px;border:1px solid var(--line);background:#fff;color:var(--muted);
 font-size:11.5px;font-weight:750;cursor:pointer;font-family:inherit}
.tab.on{background:var(--brand2);border-color:var(--brand2);color:#04222c}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;margin-bottom:14px}
.card h2{font-size:13px;margin:0 0 3px}
.card .sub{color:var(--muted);font-size:11px;margin:0 0 12px}
table{width:100%;border-collapse:collapse}
th{font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);text-align:left;
 padding:8px 8px;border-bottom:1px solid var(--line);font-weight:800;white-space:nowrap}
td{padding:5px 8px;border-bottom:1px solid #f0f4f8;vertical-align:middle}
tr:last-child td{border-bottom:0}
input[type=text],input[type=number],input[type=time],select{width:100%;height:32px;border:1px solid var(--line);
 border-radius:8px;padding:0 9px;font:inherit;font-size:12px;background:#fff;color:var(--text)}
input:focus,select:focus{outline:2px solid #cdeee9;border-color:var(--brand2)}
.grid{display:grid;gap:12px}
.g2{grid-template-columns:repeat(2,minmax(0,1fr))}
.g3{grid-template-columns:repeat(3,minmax(0,1fr))}
.g4{grid-template-columns:repeat(4,minmax(0,1fr))}
label.f{display:block}
label.f span{display:block;font-size:10.5px;color:var(--muted);font-weight:700;margin-bottom:4px}
.chk{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--muted);white-space:nowrap}
.tag{display:inline-block;padding:2px 8px;border-radius:20px;background:#eef7f5;color:var(--brand);font-size:10.5px;font-weight:750}
.hint{background:#f0f9f7;border:1px solid #cfe9e3;border-radius:10px;padding:10px 12px;color:#2b5c56;font-size:11.5px;margin-bottom:14px}
.rowbar{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
.count{color:var(--muted);font-size:11px}
.toast-wrap{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:8px;z-index:80}
.toast{background:#10303f;color:#eaf4f8;padding:10px 14px;border-radius:10px;font-size:12px;box-shadow:0 8px 24px rgba(10,32,48,.25)}
.toast.error{background:#8a1f16}
.hidden{display:none}
@media(max-width:900px){.g2,.g3,.g4{grid-template-columns:1fr}.shell{padding:16px 12px 40px}
 .page-head{flex-wrap:wrap}.page-head .right{width:100%;margin-left:0}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=7">
<script>window.QMS_CFG={state:"{{ route('config.state') }}",save:"{{ route('config.save') }}",csrf:"{{ csrf_token() }}"};</script>

</head>
<body>
@include('modules._sidebar')

<div class="shell">
  <div class="breadcrumb">Quản trị › <b>Cấu hình chung</b></div>
  <div class="page-head">
    <div>
      <h1>Cấu hình chung</h1>
      <p>Khai báo một lần — cả 3 module dùng chung, không phải tạo lại.</p>
    </div>
    <div class="right">
      <button class="btn" onclick="reload()">↻ Tải lại</button>
      <button class="btn primary" onclick="saveAll()">💾 Lưu cấu hình</button>
    </div>
  </div>

  <div class="tabs" id="tabs"></div>

  <!-- NHÂN SỰ -->
  <section class="card hidden" data-pane="staff">
    <h2>Nhân sự</h2>
    <p class="sub">Danh sách người thực hiện / người kiểm tra dùng chung cho cả 3 module.</p>
    <table><thead><tr>
      <th style="width:26%">Họ tên</th><th style="width:18%">Vai trò</th><th style="width:26%">Khoa / phòng ban</th>
      <th style="width:12%">Trạng thái</th><th style="width:8%"></th>
    </tr></thead><tbody id="tbStaff"></tbody></table>
    <div class="rowbar"><button class="btn sm" onclick="addStaff()">＋ Thêm người</button>
      <span class="count" id="cStaff"></span></div>
  </section>

  <!-- PHÒNG BAN -->
  <section class="card hidden" data-pane="dep">
    <h2>Khoa / phòng ban</h2>
    <p class="sub">Dùng cho nhân sự, trang thiết bị và phần chung của nhật ký rác thải.</p>
    <table><thead><tr><th>Tên khoa / phòng ban</th><th style="width:14%">Đang dùng</th><th style="width:8%"></th></tr></thead>
      <tbody id="tbDep"></tbody></table>
    <div class="rowbar"><button class="btn sm" onclick="addDep()">＋ Thêm phòng ban</button>
      <span class="count" id="cDep"></span></div>
  </section>

  <!-- THIẾT BỊ -->
  <section class="card hidden" data-pane="dev">
    <h2>Trang thiết bị</h2>
    <p class="sub">Module theo dõi khử nhiễm chọn thiết bị từ danh sách này.</p>
    <table><thead><tr>
      <th style="width:13%">Mã</th><th style="width:26%">Tên thiết bị</th><th style="width:13%">Số seri</th>
      <th style="width:20%">Vị trí</th><th style="width:16%">Khoa</th><th style="width:8%">Dùng</th><th style="width:6%"></th>
    </tr></thead><tbody id="tbDev"></tbody></table>
    <div class="rowbar"><button class="btn sm" onclick="addDev()">＋ Thêm thiết bị</button>
      <span class="count" id="cDev"></span></div>
  </section>

  <!-- NGƯỠNG NHIỆT ĐỘ / ĐỘ ẨM -->
  <section class="card hidden" data-pane="env">
    <h2>Nhiệt độ, độ ẩm &amp; vệ sinh</h2>
    <p class="sub">Thiết bị đo và ngưỡng cho phép — vượt ngưỡng sẽ bị cảnh báo trong nhật ký.</p>
    <div class="grid g4">
      <label class="f"><span>Tên thiết bị đo</span><input type="text" id="eDevice"></label>
      <label class="f"><span>Vị trí đặt</span><input type="text" id="eLoc"></label>
      <label class="f"><span>Số seri</span><input type="text" id="eSerial"></label>
      <label class="f"><span>Người xem xét</span><input type="text" id="eReviewer"></label>
      <label class="f"><span>Nhiệt độ tối thiểu (°C)</span><input type="number" step="0.1" id="eTmin"></label>
      <label class="f"><span>Nhiệt độ tối đa (°C)</span><input type="number" step="0.1" id="eTmax"></label>
      <label class="f"><span>Độ ẩm tối thiểu (%)</span><input type="number" step="0.1" id="eHmin"></label>
      <label class="f"><span>Độ ẩm tối đa (%)</span><input type="number" step="0.1" id="eHmax"></label>
      <label class="f"><span>Giờ đo lần 1</span><input type="time" id="eT1"></label>
      <label class="f"><span>Giờ đo lần 2</span><input type="time" id="eT2"></label>
    </div>
  </section>

  <!-- RÁC THẢI -->
  <section class="card hidden" data-pane="waste">
    <h2>Nhật ký xử lý rác thải</h2>
    <p class="sub">Thông tin biểu mẫu và các danh mục chọn nhanh khi nhập dòng.</p>
    <div class="grid g4" style="margin-bottom:14px">
      <label class="f"><span>Mã tài liệu</span><input type="text" id="wCode"></label>
      <label class="f"><span>Phiên bản</span><input type="text" id="wVer"></label>
      <label class="f"><span>Ngày hiệu lực</span><input type="text" id="wDate" placeholder="dd/mm/yyyy"></label>
      <label class="f"><span>Khoa / đơn vị</span><select id="wDep"></select></label>
    </div>
    <div class="grid g3">
      <div><label class="f"><span>Loại chất thải</span></label><div id="lsWaste"></div>
        <button class="btn sm" onclick="addCat('wasteTypes')">＋ Thêm</button></div>
      <div><label class="f"><span>Biện pháp xử lý</span></label><div id="lsTreat"></div>
        <button class="btn sm" onclick="addCat('treatments')">＋ Thêm</button></div>
      <div><label class="f"><span>Vị trí tập kết</span></label><div id="lsLoc"></div>
        <button class="btn sm" onclick="addCat('locations')">＋ Thêm</button></div>
    </div>
  </section>

  <!-- LUỒNG NHẬP LIỆU -->
  <section class="card hidden" data-pane="flow">
    <h2>Luồng nhập liệu nối tiếp</h2>
    <p class="sub">Khai báo thứ tự các popup cần nhập trong ngày: lưu xong bước này sẽ tự mở bước kế tiếp.
      Bước nào đã có dữ liệu hôm nay sẽ được bỏ qua.</p>
    <label class="chk" style="margin-bottom:12px">
      <input type="checkbox" id="flowAuto"> Tự mở popup của bước đầu tiên ngay khi đăng nhập
    </label>
    <table><thead><tr>
      <th style="width:6%">Bước</th><th style="width:24%">Module</th><th style="width:24%">Popup</th>
      <th style="width:28%">Nhãn hiển thị</th><th style="width:10%">Bật</th><th style="width:8%"></th>
    </tr></thead><tbody id="tbFlow"></tbody></table>
    <div class="rowbar"><button class="btn sm" onclick="addFlow()">＋ Thêm bước</button>
      <span class="count" id="cFlow"></span></div>
  </section>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
const TABS=[["staff","👤 Nhân sự"],["dep","🏥 Phòng ban"],["dev","🛠 Trang thiết bị"],
            ["env","🌡 Ngưỡng nhiệt độ / độ ẩm"],["waste","🗑 Danh mục rác thải"],["flow","🔁 Luồng nhập liệu"]];
let S=null, tab="staff", seq=0;

function esc(v){return String(v??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}
function toast(m,t=""){const e=document.createElement("div");e.className="toast "+t;e.textContent=m;
  document.getElementById("toastWrap").appendChild(e);setTimeout(()=>e.remove(),2800)}
function depOptions(sel){return '<option value="">— chọn —</option>'+
  (S.departments||[]).map(d=>`<option value="${esc(d.name)}" ${d.name===sel?"selected":""}>${esc(d.name)}</option>`).join("")}
function roleOptions(sel){return Object.entries(S.roles).map(([k,v])=>
  `<option value="${k}" ${k===sel?"selected":""}>${esc(v)}</option>`).join("")}

function renderTabs(){
  document.getElementById("tabs").innerHTML=TABS.map(([k,l])=>
    `<button class="tab ${k===tab?"on":""}" onclick="go('${k}')">${l}</button>`).join("");
  document.querySelectorAll("[data-pane]").forEach(p=>p.classList.toggle("hidden",p.dataset.pane!==tab));
}
function go(k){tab=k;renderTabs()}

function renderStaff(){
  document.getElementById("tbStaff").innerHTML=(S.staff||[]).map((u,i)=>`<tr>
    <td><input type="text" value="${esc(u.name)}" oninput="S.staff[${i}].name=this.value"></td>
    <td><select onchange="S.staff[${i}].role=this.value">${roleOptions(u.role)}</select></td>
    <td><select onchange="S.staff[${i}].department=this.value">${depOptions(u.department)}</select></td>
    <td><label class="chk"><input type="checkbox" ${u.active?"checked":""}
        onchange="S.staff[${i}].active=this.checked"> Đang dùng</label></td>
    <td style="text-align:right"><button class="btn sm danger" onclick="S.staff.splice(${i},1);renderStaff()">Xoá</button></td>
  </tr>`).join("");
  document.getElementById("cStaff").textContent=(S.staff||[]).length+" người";
}
function addStaff(){S.staff.push({id:"new-"+(++seq),name:"",role:"technician",department:"",active:true});renderStaff()}

function renderDep(){
  document.getElementById("tbDep").innerHTML=(S.departments||[]).map((d,i)=>`<tr>
    <td><input type="text" value="${esc(d.name)}" oninput="S.departments[${i}].name=this.value"></td>
    <td><label class="chk"><input type="checkbox" ${d.active?"checked":""}
        onchange="S.departments[${i}].active=this.checked"> Đang dùng</label></td>
    <td style="text-align:right"><button class="btn sm danger" onclick="S.departments.splice(${i},1);renderDep();renderStaff();renderDev();renderWaste()">Xoá</button></td>
  </tr>`).join("");
  document.getElementById("cDep").textContent=(S.departments||[]).length+" phòng ban";
}
function addDep(){S.departments.push({name:"",active:true});renderDep()}

function renderDev(){
  document.getElementById("tbDev").innerHTML=(S.devices||[]).map((d,i)=>`<tr>
    <td><input type="text" value="${esc(d.code)}" oninput="S.devices[${i}].code=this.value"></td>
    <td><input type="text" value="${esc(d.name)}" oninput="S.devices[${i}].name=this.value"></td>
    <td><input type="text" value="${esc(d.serial)}" oninput="S.devices[${i}].serial=this.value"></td>
    <td><input type="text" value="${esc(d.location)}" oninput="S.devices[${i}].location=this.value"></td>
    <td><select onchange="S.devices[${i}].department=this.value">${depOptions(d.department)}</select></td>
    <td><label class="chk"><input type="checkbox" ${d.active?"checked":""}
        onchange="S.devices[${i}].active=this.checked"></label></td>
    <td style="text-align:right"><button class="btn sm danger" onclick="S.devices.splice(${i},1);renderDev()">Xoá</button></td>
  </tr>`).join("");
  document.getElementById("cDev").textContent=(S.devices||[]).length+" thiết bị";
}
function addDev(){S.devices.push({id:"new-"+(++seq),code:"",name:"",serial:"",location:"",department:"",active:true});renderDev()}

function catBox(kind,elId){
  const list=S.waste.catalogs[kind]||[];
  document.getElementById(elId).innerHTML=list.map((v,i)=>`<div style="display:flex;gap:6px;margin-bottom:6px">
    <input type="text" value="${esc(v)}" oninput="S.waste.catalogs['${kind}'][${i}]=this.value">
    <button class="btn sm danger" onclick="S.waste.catalogs['${kind}'].splice(${i},1);renderWaste()">×</button></div>`).join("");
}
function addCat(kind){(S.waste.catalogs[kind]=S.waste.catalogs[kind]||[]).push("");renderWaste()}

function renderEnv(){
  const e=S.env;
  const set=(id,v)=>document.getElementById(id).value=(v??"");
  set("eDevice",e.deviceName);set("eLoc",e.location);set("eSerial",e.serial);set("eReviewer",e.reviewer);
  set("eTmin",e.temperatureMin);set("eTmax",e.temperatureMax);set("eHmin",e.humidityMin);set("eHmax",e.humidityMax);
  set("eT1",e.time1);set("eT2",e.time2);
}
function renderWaste(){
  const w=S.waste;
  document.getElementById("wCode").value=w.documentCode??"";
  document.getElementById("wVer").value=w.formVersion??"";
  document.getElementById("wDate").value=w.effectiveDate??"";
  document.getElementById("wDep").innerHTML=depOptions(w.department);
  catBox("wasteTypes","lsWaste");catBox("treatments","lsTreat");catBox("locations","lsLoc");
}

const MODNAME={env:"Nhiệt độ, độ ẩm & vệ sinh",device:"Khử nhiễm trang thiết bị",waste:"Nhật ký xử lý rác thải"};
function actionOptions(module,sel){const a=(S.flow.actions||{})[module]||{};
  return Object.entries(a).map(([k,v])=>`<option value="${k}" ${k===sel?"selected":""}>${esc(v)}</option>`).join("")}
function moduleOptions(sel){return Object.entries(MODNAME).map(([k,v])=>
  `<option value="${k}" ${k===sel?"selected":""}>${esc(v)}</option>`).join("")}
function setFlowModule(i,v){S.flow.steps[i].module=v;
  const first=Object.keys((S.flow.actions||{})[v]||{})[0]||"";S.flow.steps[i].action=first;renderFlow()}
function renderFlow(){
  document.getElementById("flowAuto").checked=!!S.flow.autoOpen;
  document.getElementById("tbFlow").innerHTML=(S.flow.steps||[]).map((st,i)=>`<tr>
    <td style="color:var(--muted);font-weight:800">${i+1}</td>
    <td><select onchange="setFlowModule(${i},this.value)">${moduleOptions(st.module)}</select></td>
    <td><select onchange="S.flow.steps[${i}].action=this.value">${actionOptions(st.module,st.action)}</select></td>
    <td><input type="text" value="${esc(st.label)}" placeholder="để trống = tên mặc định"
        oninput="S.flow.steps[${i}].label=this.value"></td>
    <td><label class="chk"><input type="checkbox" ${st.active?"checked":""}
        onchange="S.flow.steps[${i}].active=this.checked"></label></td>
    <td style="text-align:right;white-space:nowrap">
      <button class="btn sm" onclick="moveFlow(${i},-1)" title="Lên">↑</button>
      <button class="btn sm" onclick="moveFlow(${i},1)" title="Xuống">↓</button>
      <button class="btn sm danger" onclick="S.flow.steps.splice(${i},1);renderFlow()">Xoá</button></td>
  </tr>`).join("");
  document.getElementById("cFlow").textContent=(S.flow.steps||[]).length+" bước";
}
function moveFlow(i,d){const a=S.flow.steps,j=i+d;if(j<0||j>=a.length)return;[a[i],a[j]]=[a[j],a[i]];renderFlow()}
function addFlow(){const m="env";S.flow.steps.push({module:m,action:Object.keys((S.flow.actions||{})[m]||{})[0]||"daily",
  label:"",active:true});renderFlow()}

function renderAll(){renderTabs();renderStaff();renderDep();renderDev();renderEnv();renderWaste();renderFlow()}

function collect(){
  const g=id=>document.getElementById(id).value;
  S.env={deviceName:g("eDevice"),location:g("eLoc"),serial:g("eSerial"),reviewer:g("eReviewer"),
    temperatureMin:g("eTmin"),temperatureMax:g("eTmax"),humidityMin:g("eHmin"),humidityMax:g("eHmax"),
    time1:g("eT1"),time2:g("eT2")};
  S.waste.documentCode=g("wCode");S.waste.formVersion=g("wVer");S.waste.effectiveDate=g("wDate");
  S.waste.department=g("wDep");
  S.flow.autoOpen=document.getElementById('flowAuto').checked;
  return {staff:S.staff,departments:S.departments,devices:S.devices,env:S.env,waste:S.waste,
          flow:{autoOpen:S.flow.autoOpen,steps:S.flow.steps}};
}
async function saveAll(){
  try{
    const r=await fetch(window.QMS_CFG.save,{method:"POST",credentials:"same-origin",
      headers:{"Content-Type":"application/json","X-CSRF-TOKEN":window.QMS_CFG.csrf,"Accept":"application/json"},
      body:JSON.stringify(collect())});
    if(!r.ok)throw new Error("HTTP "+r.status);
    toast("Đã lưu cấu hình — các module sẽ dùng dữ liệu mới");
    await reload(true);
  }catch(e){toast("Lưu thất bại: "+e.message,"error")}
}
async function reload(quiet){
  const r=await fetch(window.QMS_CFG.state,{credentials:"same-origin"});
  if(!r.ok){toast("Không tải được cấu hình","error");return}
  S=await r.json();renderAll();if(!quiet)toast("Đã tải lại")
}
(async()=>{try{await reload(true)}catch(e){toast("Lỗi tải dữ liệu: "+e.message,"error");console.error(e)}})();
</script>
</body>
</html>
