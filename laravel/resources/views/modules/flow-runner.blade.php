<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nhập liệu theo luồng — QMS</title>
<style>
:root{--bg:#f4f7fa;--card:#fff;--line:#e3e9ef;--text:#16242f;--muted:#5f7488;--brand:#0f766e;--brand2:#14b8a6}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 "Segoe UI",system-ui,-apple-system,sans-serif}
.shell{display:flex;flex-direction:column;height:100vh;padding:16px 20px 18px;max-width:1280px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:4px}
.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:center;gap:14px;margin-bottom:10px}
.head h1{font-size:20px;margin:0}
.head p{margin:2px 0 0;color:var(--muted);font-size:11px}
.head .right{margin-left:auto;display:flex;gap:8px;align-items:center}
.btn{height:32px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;padding:0 12px;
 font-size:11.5px;font-weight:750;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-family:inherit}
.btn:hover{border-color:#c9d6e2}
.btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
.btn.primary:hover{background:#0c5f59}
.btn:disabled{opacity:.5;cursor:not-allowed}
.steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
.step{display:flex;align-items:center;gap:8px;border:1px solid var(--line);background:#fff;border-radius:11px;
 padding:7px 12px;cursor:pointer;font-size:11.5px;font-weight:700;color:var(--muted);font-family:inherit}
.step .n{width:20px;height:20px;border-radius:50%;background:#eef2f6;color:#54697d;display:flex;
 align-items:center;justify-content:center;font-size:10px;font-weight:800;flex:none}
.step.on{border-color:var(--brand2);background:#effbf8;color:#0b4f4a}
.step.on .n{background:var(--brand2);color:#04222c}
.step.done{color:#12705f}
.step.done .n{background:#d7f5ec;color:#0d6b58}
.step .mod{font-weight:600;color:#8a9db0;font-size:10.5px}
.qr-star{color:#d99b1c;font-size:11px}
.frame{flex:1 1 0%;min-height:0;background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;
 display:flex;flex-direction:column;position:relative}
.frame iframe{flex:1 1 0%;width:100%;border:0;min-height:0}
.done-box{flex:1 1 0%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--muted);text-align:center;padding:24px}
.done-box .big{font-size:34px}
.done-box h2{margin:0;font-size:16px;color:var(--text)}
.bar{display:flex;align-items:center;gap:8px;padding:9px 14px;border-top:1px solid var(--line);background:#fbfdfe}
.bar .who{color:var(--muted);font-size:11px;margin-right:auto}
.toast-wrap{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:8px;z-index:80}
.toast{background:#10303f;color:#eaf4f8;padding:10px 14px;border-radius:10px;font-size:12px;box-shadow:0 8px 24px rgba(10,32,48,.25)}
.toast.error{background:#8a1f16}
@media(max-width:980px){.shell{padding:12px 10px 14px}.head{flex-wrap:wrap}.head .right{width:100%;margin-left:0}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=7">
<script>window.QMS_RUN={state:"{{ route('flow.state') }}",config:"{{ route('config.page') }}",home:"{{ route('env.page') }}"};</script>
</head>
<body>
@include('modules._sidebar')

<div class="shell">
  <div class="breadcrumb">Nhập liệu › <b>Theo luồng</b></div>
  <div class="head">
    <div>
      <h1>Nhập liệu theo luồng</h1>
      <p id="sub">Các biểu mẫu hiện lần lượt ngay trên trang này — không phải chuyển qua lại giữa các module.</p>
    </div>
    <div class="right">
      <button class="btn" onclick="reloadFlow()">↻ Làm mới</button>
      <a class="btn" href="{{ route('config.page') }}">⚙ Sửa luồng</a>
    </div>
  </div>

  <div class="steps" id="steps"></div>

  <div class="frame" id="frame">
    <iframe id="pane" title="Biểu mẫu của bước hiện tại"></iframe>
    <div class="bar">
      <span class="who" id="who"></span>
      <button class="btn" id="btnSkip" onclick="skipStep()">Bỏ qua bước này ›</button>
      <button class="btn" id="btnReopen" onclick="openStep(cur,true)">↻ Mở lại biểu mẫu</button>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
let FLOW=null, cur=null, skipped=[];

function toast(m,t=""){const e=document.createElement("div");e.className="toast "+t;e.textContent=m;
  document.getElementById("toastWrap").appendChild(e);setTimeout(()=>e.remove(),2800)}
function esc(v){return String(v??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]))}
const MODNAME={env:"Nhiệt độ, độ ẩm",device:"Trang thiết bị",waste:"Xử lý rác thải",usage:"Sử dụng thiết bị"};

function renderSteps(){
  document.getElementById("steps").innerHTML=(FLOW.steps||[]).map((s,i)=>{
    const cls=s.done?"done":(cur&&s.id===cur.id?"on":"");
    return `<button class="step ${cls}" onclick="openStep(FLOW.steps[${i}],true)">
      <span class="n">${s.done?"✓":i+1}</span>
      <span>${esc(s.label)}${s.preset?' <b class="qr-star" title="Có bản mẫu mặc định — sẽ tự điền sẵn">★</b>':''}<br><span class="mod">${esc(MODNAME[s.module]||s.module)}</span></span></button>`;
  }).join("");
}

/** Nạp biểu mẫu của 1 bước vào khung, không rời trang. */
function openStep(step,manual){
  if(!step){finish();return}
  cur=step;
  document.getElementById("frame").querySelector("iframe").src=
    step.url+"?flow=1&embed=1&step="+step.id+(manual?"&r="+Date.now():"");
  document.getElementById("who").textContent="Đang nhập: "+step.label+" — "+(MODNAME[step.module]||step.module)+(step.preset?" · đã điền sẵn theo bản mẫu":"");
  document.getElementById("btnSkip").disabled=false;
  renderSteps();
}

function nextPending(){return (FLOW.steps||[]).find(s=>!s.done&&!skipped.includes(s.id))||null}

function skipStep(){
  if(cur&&!skipped.includes(cur.id))skipped.push(cur.id);
  const n=nextPending();
  if(n)openStep(n,true); else finish("Đã bỏ qua các bước còn lại.");
}

function finish(msg){
  cur=null;renderSteps();
  const done=(FLOW.steps||[]).filter(s=>s.done).length,total=(FLOW.steps||[]).length;
  document.getElementById("frame").innerHTML=
    `<div class="done-box"><div class="big">✅</div><h2>${msg?esc(msg):"Đã xong luồng nhập liệu hôm nay"}</h2>
     <p>${done}/${total} bước đã có dữ liệu của hôm nay.</p>
     <div style="display:flex;gap:8px">
       <button class="btn" onclick="reloadFlow()">↻ Kiểm tra lại</button>
       <a class="btn primary" href="${window.QMS_RUN.home}">Về màn hình chính</a></div></div>`;
}

async function reloadFlow(keep){
  const r=await fetch(window.QMS_RUN.state,{credentials:"same-origin"});
  if(!r.ok){toast("Không tải được luồng","error");return}
  FLOW=await r.json();
  if(!FLOW.enabled||!(FLOW.steps||[]).length){
    document.getElementById("steps").innerHTML="";
    document.getElementById("frame").innerHTML=
      `<div class="done-box"><div class="big">🔁</div><h2>Chưa khai báo luồng nhập liệu</h2>
       <p>Vào Cấu hình chung › Luồng nhập liệu để chọn thứ tự các biểu mẫu cần nhập mỗi ngày.</p>
       <a class="btn primary" href="${window.QMS_RUN.config}">⚙ Khai báo luồng</a></div>`;
    return;
  }
  renderSteps();
  if(!keep)openStep(nextPending(),true);
}

// nhận tín hiệu từ biểu mẫu trong khung
window.addEventListener("message",async e=>{
  const d=e.data||{};
  if(!d.qmsFlow)return;
  if(d.qmsFlow==="toast"){toast(d.message);return}
  if(d.qmsFlow==="saved"){
    toast("Đã lưu — chuyển sang bước tiếp theo");
    const id=cur&&cur.id;
    for(let i=0;i<4;i++){                       // chờ CSDL ghi xong rồi mới đọc lại trạng thái
      await reloadFlow(true);
      if((FLOW.steps||[]).some(s=>s.id===id&&s.done))break;
      await new Promise(r=>setTimeout(r,800));
    }
    const n=nextPending();
    if(n&&(!cur||n.id!==cur.id))openStep(n,true); else if(!n)finish();
    return;
  }
  if(d.qmsFlow==="mismatch"||d.qmsFlow==="empty"){await reloadFlow(true);const n=nextPending();if(n)openStep(n,true);else finish()}
});

reloadFlow();
</script>
</body>
</html>
