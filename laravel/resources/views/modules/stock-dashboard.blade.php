<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tổng quan kho — QMS</title>
<style>
:root{--bg:#f4f7fb;--line:#dfe6ee;--line2:#edf1f5;--text:#1e293b;--muted:#64748b;
 --primary:#0f6b7a;--primary2:#0b5662;--soft:#e8f5f7;
 --green:#15803d;--green-soft:#ecfdf3;--amber:#a16207;--amber-soft:#fff8e6;
 --red:#b42318;--red-soft:#fff1f0;--blue:#1d4ed8;--blue-soft:#eef4ff}
/* Bảng màu biểu đồ — đã chạy validator: CVD ΔE 24.7, normal ΔE 33.6, tương phản ≥3:1 */
.viz{--surface-1:#ffffff;--text-primary:#1e293b;--text-secondary:#64748b;
 --series-1:#2a78d6;   /* Nhập kho */
 --series-2:#eb6834;   /* Xuất dùng */
 --grid:#e8edf3;--axis:#94a3b8}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 Inter,"Segoe UI",system-ui,sans-serif}
button,select,input{font:inherit}button{cursor:pointer}
.shell{padding:18px 22px 40px;max-width:1560px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:4px}.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:center;gap:14px;margin-bottom:12px}
.head h1{font-size:21px;margin:0}.head p{margin:2px 0 0;color:var(--muted);font-size:11px}
.head .right{margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.btn{height:33px;padding:0 12px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;
 font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap}
.btn:hover{border-color:#bdc8d4}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
select.pick{height:33px;border:1px solid var(--line);border-radius:9px;padding:0 10px;font-size:11px;background:#fff;min-width:150px}
.tiles{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:12px}
.tile{background:#fff;border:1px solid var(--line);border-radius:12px;padding:12px 13px}
.tile .k{font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;font-weight:800}
.tile .v{font-size:23px;font-weight:800;line-height:1.15;margin-top:3px;font-variant-numeric:tabular-nums}
.tile .s{font-size:9.5px;color:var(--muted);margin-top:2px}
.tile.warn .v{color:var(--amber)}.tile.bad .v{color:var(--red)}.tile.good .v{color:var(--green)}
.grid2{display:grid;grid-template-columns:1.45fr 1fr;gap:12px;margin-bottom:12px}
.card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px 16px}
.card h2{font-size:13px;margin:0 0 2px}.card .sub{font-size:10px;color:var(--muted);margin:0 0 10px}
.legend{display:flex;gap:14px;flex-wrap:wrap;font-size:10px;color:var(--text-secondary,#64748b);margin-bottom:6px}
.legend i{width:9px;height:9px;border-radius:2px;display:inline-block;margin-right:5px;vertical-align:baseline}
.chart{width:100%;overflow:visible}
.chart text{font-family:inherit}
.tt{position:fixed;z-index:200;background:#10303f;color:#fff;border-radius:8px;padding:7px 9px;font-size:10px;
 pointer-events:none;opacity:0;transition:opacity .1s;box-shadow:0 8px 22px rgba(9,30,45,.28);white-space:nowrap}
.tt.on{opacity:1}
.tt b{font-size:11px}.tt .r{display:flex;gap:8px;align-items:center;margin-top:3px}
.tt .r i{width:8px;height:8px;border-radius:2px;display:inline-block}
table{width:100%;border-collapse:separate;border-spacing:0}
th{text-align:left;font-size:8.5px;text-transform:uppercase;letter-spacing:.05em;color:#728197;
 padding:7px 8px;border-bottom:1px solid var(--line);white-space:nowrap;background:#f8fafc}
td{padding:7px 8px;border-bottom:1px solid var(--line2);font-size:10.5px;vertical-align:middle}
tbody tr:last-child td{border-bottom:0}
.num{text-align:right;font-variant-numeric:tabular-nums}
.main{font-weight:800}.sub2{font-size:8.7px;color:var(--muted)}
.bar{height:7px;border-radius:4px;background:#eef2f7;overflow:hidden;min-width:60px}
.bar>i{display:block;height:100%;border-radius:4px;background:#2a78d6}
.badge{display:inline-flex;padding:3px 7px;border-radius:999px;font-size:8.5px;font-weight:800;white-space:nowrap}
.badge.ok{background:var(--green-soft);color:var(--green)}.badge.low{background:var(--amber-soft);color:var(--amber)}
.badge.out{background:var(--red-soft);color:var(--red)}.badge.high{background:var(--blue-soft);color:var(--blue)}
.badge.exp{background:var(--red-soft);color:var(--red)}.badge.soon{background:var(--amber-soft);color:var(--amber)}
.link{color:var(--primary);font-weight:800;text-decoration:none}.link:hover{text-decoration:underline}
.tabs{display:flex;gap:6px;margin-bottom:8px}
.tab{height:26px;padding:0 10px;border:1px solid var(--line);background:#fff;border-radius:7px;font-size:10px;font-weight:750;color:var(--muted)}
.tab.on{background:var(--primary);border-color:var(--primary);color:#fff}
.empty{text-align:center;padding:26px;color:var(--muted);font-size:10.5px}
@media(max-width:1150px){.tiles{grid-template-columns:repeat(2,1fr)}.grid2{grid-template-columns:1fr}}
@media(max-width:650px){.shell{padding:12px 10px 30px}.tiles{grid-template-columns:1fr}.head{flex-wrap:wrap}.head .right{width:100%}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script src="{{ asset('js/qms-select.js') }}?v=3"></script>
<script>window.QMS_DASH={data:"{{ route('stock.dash.data') }}",card:"{{ route('stock.page') }}",items:"{{ route('item.page') }}"};</script>
</head>
<body>
@include('modules._sidebar')

<div class="shell viz">
  <div class="breadcrumb">Kho vật tư › <b>Tổng quan</b></div>
  <div class="head">
    <div>
      <h1>Tổng quan kho</h1>
      <p>Theo dõi tồn kho và lượng sử dụng theo tháng của từng mã hàng.</p>
    </div>
    <div class="right">
      <select class="pick" id="year"></select>
      <select class="pick" id="item"></select>
      <a class="btn" href="{{ route('item.page') }}">📦 Quản lý mã hàng</a>
      <a class="btn primary" id="openCard" href="#">🏷 Mở thẻ kho</a>
    </div>
  </div>

  <section class="tiles" id="tiles"></section>

  <section class="grid2">
    <div class="card">
      <h2 id="chartTitle">Nhập kho và lượng sử dụng theo tháng</h2>
      <p class="sub" id="chartSub"></p>
      <div class="legend">
        <span><i style="background:var(--series-1)"></i>Nhập kho</span>
        <span><i style="background:var(--series-2)"></i>Xuất dùng</span>
      </div>
      <div id="barChart"></div>
    </div>
    <div class="card">
      <h2>Tồn kho cuối mỗi tháng</h2>
      <p class="sub" id="lineSub"></p>
      <div id="lineChart"></div>
    </div>
  </section>

  <section class="grid2">
    <div class="card">
      <h2>Mã hàng dùng nhiều nhất trong năm</h2>
      <p class="sub">Tổng lượng xuất dùng + hủy, xếp giảm dần.</p>
      <div id="topTable"></div>
    </div>
    <div class="card">
      <div class="tabs">
        <button class="tab on" id="tabWarn" onclick="setTab('warn')">Cần chú ý</button>
        <button class="tab" id="tabExp" onclick="setTab('exp')">Lô sắp hết hạn</button>
      </div>
      <div id="sideTable"></div>
    </div>
  </section>
</div>

<div class="tt" id="tt"></div>

<script>
let D=null,curItem='',tab='warn';
const $=id=>document.getElementById(id);
const fmt=n=>new Intl.NumberFormat('vi-VN',{maximumFractionDigits:2}).format(Number(n||0));
const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const viDate=s=>s?s.slice(8,10)+'/'+s.slice(5,7)+'/'+s.slice(0,4):'—';
const MON=['Th1','Th2','Th3','Th4','Th5','Th6','Th7','Th8','Th9','Th10','Th11','Th12'];
const ST={ok:['ok','Còn hàng'],low:['low','Sắp hết'],out:['out','Hết hàng'],high:['high','Vượt tối đa']};

/* Gộp số liệu: 1 mã hàng cụ thể hoặc toàn kho */
function series(){
  const list=curItem?D.items.filter(x=>x.id===curItem):D.items;
  const add=(k)=>Array.from({length:12},(_,i)=>list.reduce((s,x)=>s+Number(x[k][i]||0),0));
  return {imported:add('imported'),used:add('used'),
    stockEnd:curItem?(list[0]?.stockEnd||Array(12).fill(0)):add('stockEnd'),
    unit:curItem?(list[0]?.unit||''):'', list};
}

/* ===== Biểu đồ cột nhóm: Nhập vs Xuất dùng ===== */
function barChart(el,imported,used,unit){
  const W=680,H=250,P={t:12,r:12,b:26,l:46};
  const max=Math.max(1,...imported,...used);
  const iw=(W-P.l-P.r)/12, bw=Math.min(13,(iw-8)/2), gap=2;
  const y=v=>H-P.b-(v/max)*(H-P.t-P.b);
  const ticks=[0,max/2,max];
  let g='';
  ticks.forEach(t=>{g+=`<line x1="${P.l}" x2="${W-P.r}" y1="${y(t)}" y2="${y(t)}" stroke="var(--grid)" stroke-width="1"/>
    <text x="${P.l-7}" y="${y(t)+3.5}" text-anchor="end" font-size="9" fill="var(--text-secondary)">${fmt(Math.round(t))}</text>`});
  let bars='';
  for(let i=0;i<12;i++){
    const cx=P.l+iw*i+iw/2;
    const x1=cx-bw-gap/2,x2=cx+gap/2;
    const h1=Math.max(imported[i]>0?2:0,H-P.b-y(imported[i])),h2=Math.max(used[i]>0?2:0,H-P.b-y(used[i]));
    if(h1)bars+=`<rect x="${x1}" y="${H-P.b-h1}" width="${bw}" height="${h1}" rx="3" fill="var(--series-1)"/>`;
    if(h2)bars+=`<rect x="${x2}" y="${H-P.b-h2}" width="${bw}" height="${h2}" rx="3" fill="var(--series-2)"/>`;
    bars+=`<rect class="hit" x="${P.l+iw*i}" y="${P.t}" width="${iw}" height="${H-P.t-P.b}" fill="transparent"
      data-m="${i}" data-a="${imported[i]}" data-b="${used[i]}"/>`;
    bars+=`<text x="${cx}" y="${H-P.b+15}" text-anchor="middle" font-size="9" fill="var(--text-secondary)">${MON[i]}</text>`;
  }
  el.innerHTML=`<svg class="chart" viewBox="0 0 ${W} ${H}" role="img" aria-label="Nhập kho và xuất dùng theo tháng">
    ${g}<line x1="${P.l}" x2="${W-P.r}" y1="${H-P.b}" y2="${H-P.b}" stroke="var(--axis)" stroke-width="1"/>${bars}</svg>`;
  el.querySelectorAll('.hit').forEach(r=>{
    r.addEventListener('mousemove',e=>showTip(e,`<b>Tháng ${Number(r.dataset.m)+1}/${D.year}</b>
      <div class="r"><i style="background:var(--series-1)"></i>Nhập: ${fmt(r.dataset.a)} ${esc(unit)}</div>
      <div class="r"><i style="background:var(--series-2)"></i>Xuất dùng: ${fmt(r.dataset.b)} ${esc(unit)}</div>`));
    r.addEventListener('mouseleave',hideTip);
  });
}

/* ===== Biểu đồ đường: tồn cuối tháng ===== */
function lineChart(el,vals,unit){
  const W=470,H=250,P={t:12,r:14,b:26,l:46};
  const max=Math.max(1,...vals),min=0;
  const x=i=>P.l+((W-P.l-P.r)/11)*i, y=v=>H-P.b-((v-min)/(max-min||1))*(H-P.t-P.b);
  let g='';[0,max/2,max].forEach(t=>{g+=`<line x1="${P.l}" x2="${W-P.r}" y1="${y(t)}" y2="${y(t)}" stroke="var(--grid)"/>
    <text x="${P.l-7}" y="${y(t)+3.5}" text-anchor="end" font-size="9" fill="var(--text-secondary)">${fmt(Math.round(t))}</text>`});
  const pts=vals.map((v,i)=>`${x(i)},${y(v)}`).join(' ');
  const area=`${P.l},${H-P.b} ${pts} ${x(11)},${H-P.b}`;
  let dots='',hits='';
  vals.forEach((v,i)=>{
    dots+=`<circle cx="${x(i)}" cy="${y(v)}" r="3.4" fill="var(--series-1)" stroke="var(--surface-1)" stroke-width="2"/>`;
    hits+=`<rect class="hit2" x="${x(i)-16}" y="${P.t}" width="32" height="${H-P.t-P.b}" fill="transparent" data-i="${i}" data-v="${v}"/>`;
    if(i%2===0)g+=`<text x="${x(i)}" y="${H-P.b+15}" text-anchor="middle" font-size="9" fill="var(--text-secondary)">${MON[i]}</text>`;
  });
  el.innerHTML=`<svg class="chart" viewBox="0 0 ${W} ${H}" role="img" aria-label="Tồn kho cuối mỗi tháng">
    ${g}<polygon points="${area}" fill="var(--series-1)" opacity=".08"/>
    <polyline points="${pts}" fill="none" stroke="var(--series-1)" stroke-width="2" stroke-linejoin="round"/>
    ${dots}<line x1="${P.l}" x2="${W-P.r}" y1="${H-P.b}" y2="${H-P.b}" stroke="var(--axis)"/>${hits}</svg>`;
  el.querySelectorAll('.hit2').forEach(r=>{
    r.addEventListener('mousemove',e=>showTip(e,`<b>Cuối tháng ${Number(r.dataset.i)+1}/${D.year}</b>
      <div class="r"><i style="background:var(--series-1)"></i>Tồn: ${fmt(r.dataset.v)} ${esc(unit)}</div>`));
    r.addEventListener('mouseleave',hideTip);
  });
}
function showTip(e,html){const t=$('tt');t.innerHTML=html;t.classList.add('on');
  t.style.left=Math.min(window.innerWidth-t.offsetWidth-12,e.clientX+14)+'px';
  t.style.top=Math.max(8,e.clientY-t.offsetHeight-10)+'px'}
function hideTip(){$('tt').classList.remove('on')}

/* ===== Bảng ===== */
function topTable(){
  const rows=[...D.items].filter(x=>x.usedYear>0).sort((a,b)=>b.usedYear-a.usedYear).slice(0,8);
  if(!rows.length){$('topTable').innerHTML='<div class="empty">Chưa có phát sinh xuất dùng trong năm này.</div>';return}
  const max=rows[0].usedYear;
  $('topTable').innerHTML=`<table><thead><tr><th>Mã hàng</th><th>Nhóm</th><th class="num">Đã dùng</th><th style="width:150px">Tỷ trọng</th><th class="num">Tồn</th></tr></thead><tbody>
   ${rows.map(x=>`<tr><td><a class="link" href="${window.QMS_DASH.card}?product=${encodeURIComponent(x.id)}">${esc(x.code)}</a>
     <div class="sub2">${esc(x.name)}</div></td><td>${esc(x.group||'—')}</td>
     <td class="num main">${fmt(x.usedYear)} <span class="sub2">${esc(x.unit)}</span></td>
     <td><div class="bar"><i style="width:${Math.round(x.usedYear/max*100)}%"></i></div></td>
     <td class="num">${fmt(x.balance)}</td></tr>`).join('')}</tbody></table>`;
}
function setTab(t){tab=t;$('tabWarn').classList.toggle('on',t==='warn');$('tabExp').classList.toggle('on',t==='exp');sideTable()}
function sideTable(){
  if(tab==='warn'){
    const rows=D.items.filter(x=>x.status!=='ok').sort((a,b)=>a.balance-b.balance);
    $('sideTable').innerHTML=rows.length?`<table><thead><tr><th>Mã hàng</th><th class="num">Tồn</th><th class="num">Min / Max</th><th>Tình trạng</th></tr></thead><tbody>
      ${rows.map(x=>{const s=ST[x.status];return `<tr><td><a class="link" href="${window.QMS_DASH.card}?product=${encodeURIComponent(x.id)}">${esc(x.code)}</a>
        <div class="sub2">${esc(x.name)}</div></td><td class="num main">${fmt(x.balance)}</td>
        <td class="num sub2">${fmt(x.min)} / ${fmt(x.max)}</td><td><span class="badge ${s[0]}">${s[1]}</span></td></tr>`}).join('')}</tbody></table>`
      :'<div class="empty">Mọi mã hàng đều trong ngưỡng cho phép.</div>';
  }else{
    const rows=D.batches||[];
    $('sideTable').innerHTML=rows.length?`<table><thead><tr><th>Mã hàng</th><th>Số lô</th><th class="num">Còn</th><th>Hạn dùng</th></tr></thead><tbody>
      ${rows.map(b=>`<tr><td><div class="main">${esc(b.code)}</div><div class="sub2">${esc(b.name)}</div></td>
        <td>${esc(b.batch)}</td><td class="num">${fmt(b.qty)} <span class="sub2">${esc(b.unit||'')}</span></td>
        <td>${viDate(b.expiry)} <span class="badge ${b.days<0?'exp':'soon'}">${b.days<0?'quá hạn':'còn '+b.days+' ngày'}</span></td></tr>`).join('')}</tbody></table>`
      :'<div class="empty">Không có lô nào hết hạn trong 180 ngày tới.</div>';
  }
}

function renderTiles(){
  const s=series();
  const tong=D.items.reduce((a,x)=>a+x.balance,0);
  const dung=s.used.reduce((a,b)=>a+b,0), nhap=s.imported.reduce((a,b)=>a+b,0);
  const t=D.totals;
  const scope=curItem?(D.items.find(x=>x.id===curItem)?.code||''):'toàn kho';
  $('tiles').innerHTML=`
   <div class="tile"><div class="k">Mã hàng đang quản lý</div><div class="v">${t.items}</div><div class="s">${t.tx} phát sinh nhập/xuất</div></div>
   <div class="tile"><div class="k">Nhập trong năm ${D.year}</div><div class="v">${fmt(nhap)}</div><div class="s">${esc(scope)}${s.unit?' · '+esc(s.unit):''}</div></div>
   <div class="tile"><div class="k">Xuất dùng năm ${D.year}</div><div class="v">${fmt(dung)}</div><div class="s">${esc(scope)}${s.unit?' · '+esc(s.unit):''}</div></div>
   <div class="tile ${t.low?'warn':''}"><div class="k">Mã hàng sắp hết</div><div class="v">${t.low}</div><div class="s">tồn ≤ mức tối thiểu</div></div>
   <div class="tile ${t.out?'bad':'good'}"><div class="k">Mã hàng hết hàng</div><div class="v">${t.out}</div><div class="s">${t.high} mã vượt tối đa</div></div>`;
}
function renderAll(){
  const s=series();
  const it=curItem?D.items.find(x=>x.id===curItem):null;
  $('chartTitle').textContent=it?`Nhập & sử dụng — ${it.code}`:'Nhập kho và lượng sử dụng theo tháng';
  $('chartSub').textContent=it?`${it.name} · đơn vị ${it.unit||'—'} · năm ${D.year}`:`Cộng dồn toàn bộ mã hàng · năm ${D.year}`;
  $('lineSub').textContent=it?`Tồn cuối tháng của ${it.code}`:'Cộng dồn tồn của tất cả mã hàng';
  $('openCard').href=window.QMS_DASH.card+(curItem?`?product=${encodeURIComponent(curItem)}`:'');
  renderTiles();
  barChart($('barChart'),s.imported,s.used,s.unit);
  lineChart($('lineChart'),s.stockEnd,s.unit);
  topTable();sideTable();
}
async function load(year){
  const r=await fetch(window.QMS_DASH.data+(year?'?year='+year:''),{credentials:'same-origin'});
  if(!r.ok)throw new Error('Không tải được số liệu');
  D=await r.json();
  const ys=[...new Set([...(D.years||[]),D.year,new Date().getFullYear()])].sort();
  $('year').innerHTML=ys.map(y=>`<option value="${y}" ${y===D.year?'selected':''}>Năm ${y}</option>`).join('');
  $('item').innerHTML='<option value="">Toàn kho (tất cả mã hàng)</option>'+
    D.items.map(x=>`<option value="${x.id}" ${x.id===curItem?'selected':''}>${esc(x.code)} — ${esc(x.name)}</option>`).join('');
  renderAll();QMSSelect.refresh();
}
$('year').addEventListener('change',e=>load(e.target.value));
$('item').addEventListener('change',e=>{curItem=e.target.value;renderAll()});
(async()=>{try{await load();QMSSelect.auto()}catch(e){console.error(e);alert('Lỗi tải dữ liệu: '+e.message)}})();
</script>
</body>
</html>
