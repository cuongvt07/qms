<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sổ soạn tiêu bản &amp; hóa mô miễn dịch — QMS</title>
<style>
:root{--bg:#f4f7fb;--line:#dfe6ee;--line2:#edf1f5;--text:#1e293b;--muted:#64748b;
 --primary:#0f6b7a;--primary2:#0b5662;--soft:#e8f5f7;--green:#15803d;--green-soft:#ecfdf3;
 --amber:#a16207;--amber-soft:#fff8e6;--red:#b42318;--red-soft:#fff1f0;--blue:#1d4ed8;--blue-soft:#eef4ff;
 --purple:#6d28d9;--purple-soft:#f3eeff}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 Inter,"Segoe UI",system-ui,sans-serif}
button,input,select,textarea{font:inherit}button{cursor:pointer}
.shell{padding:16px 20px 40px;max-width:1760px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:4px}.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:flex-start;gap:14px;margin-bottom:10px}
.head h1{font-size:21px;margin:0}.head p{margin:2px 0 0;color:var(--muted);font-size:11px;max-width:820px}
.head .right{margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.btn{height:32px;padding:0 12px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;
 font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none}
.btn:hover{border-color:#bdc8d4}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}.btn:disabled{opacity:.45;cursor:not-allowed}
.btn.green{background:var(--green);border-color:var(--green);color:#fff}
.btn.red{background:#fff;color:var(--red);border-color:#f1c7c3}
.btn.sm{height:27px;padding:0 9px;font-size:10px;border-radius:7px}
.tabs{display:flex;gap:6px;background:#e8eef4;padding:4px;border-radius:12px;margin-bottom:12px;flex-wrap:wrap}
.tab{height:32px;padding:0 14px;border:0;background:transparent;border-radius:9px;font-size:11.5px;font-weight:800;color:#5b6b7d;
 display:inline-flex;align-items:center;gap:6px}
.tab.on{background:#fff;box-shadow:0 2px 7px rgba(15,23,42,.1);color:var(--primary)}
.view{display:none}.view.on{display:block}
.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:12px}
.pbar{padding:10px 12px;border-bottom:1px solid var(--line);background:#fbfcfd;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.pbar h2{font-size:12.5px;margin:0}.pbar .push{margin-left:auto}
.pbar input,.pbar select{height:31px;border:1px solid var(--line);border-radius:8px;padding:0 9px;font-size:10.5px;background:#fff}
.hint{font-size:9.5px;color:var(--muted)}
.lbl{font-size:9px;font-weight:800;color:#475569;display:grid;gap:3px}
.tiles{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px;margin-bottom:12px}
.tile{background:#fff;border:1px solid var(--line);border-radius:12px;padding:11px 12px;cursor:pointer}
.tile.on{border-color:var(--primary);box-shadow:0 0 0 2px #d3eaee}
.tile .k{font-size:8.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;font-weight:800}
.tile .v{font-size:21px;font-weight:800;line-height:1.15;margin-top:2px;font-variant-numeric:tabular-nums}
.twrap{overflow:auto;max-height:calc(100vh - 320px)}
table{width:100%;border-collapse:separate;border-spacing:0}
th{position:sticky;top:0;z-index:3;background:#f8fafc;padding:8px 9px;border-bottom:1px solid var(--line);
 text-align:left;font-size:8.5px;text-transform:uppercase;letter-spacing:.05em;color:#728197;white-space:nowrap}
td{padding:5px 9px;border-bottom:1px solid var(--line2);font-size:10.5px;vertical-align:middle;background:#fff}
tbody tr:hover td{background:#fbfdfe}
tr.sel td{background:#f1f9fb}tr.dirty td:first-child{box-shadow:inset 3px 0 0 var(--amber)}
tr.blank td{background:#fcfdfe}
.num{text-align:right;font-variant-numeric:tabular-nums}.ctr{text-align:center}
.main{font-weight:800}.sub{font-size:8.7px;color:var(--muted)}
.empty{text-align:center;padding:44px;color:var(--muted);font-size:11px}
.badge{display:inline-flex;padding:3px 8px;border-radius:999px;font-size:8.5px;font-weight:800;white-space:nowrap}
.b-soan{background:var(--blue-soft);color:var(--blue)}.b-doc{background:var(--green-soft);color:var(--green)}
.b-ihc{background:var(--purple-soft);color:var(--purple)}.b-hc{background:var(--amber-soft);color:var(--amber)}
.b-xong{background:var(--green-soft);color:var(--green)}.b-off{background:#f1f5f9;color:#64748b}
/* lưới sổ soạn */
.grid-t{min-width:1180px;table-layout:fixed}
.grid-t td{padding:0;height:32px}          /* chiều cao cố định để cuộn ảo tính đúng vị trí */
.grid-t td.pad{padding:5px 9px}
.grid-t tr.spacer td{padding:0;border:0;background:#fff}
#gridScroll{max-height:calc(100vh - 300px)}
.grid-t td input{width:100%;height:29px;border:1px solid transparent;border-radius:0;padding:0 8px;
 font-size:10.5px;background:transparent;color:var(--text)}
.grid-t td input:hover{border-color:var(--line)}
.grid-t td input:focus{border-color:var(--primary);background:#fff;outline:2px solid #d6ecef;position:relative;z-index:2}
.grid-t .chk{width:32px;text-align:center}.grid-t .chk input{width:14px;height:14px;accent-color:var(--primary)}
.grid-t .code{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.bulk{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:9px 12px;background:var(--soft);border-bottom:1px solid #cbe6ea}
.bulk.off{display:none}.bulk b{font-size:11px;color:var(--primary2)}
.bulk input{height:29px;border:1px solid #bfe0e5;border-radius:7px;padding:0 8px;font-size:10.5px;background:#fff}
.saveState{font-size:9.5px;font-weight:800}
.saveState.dirty{color:var(--amber)}.saveState.ok{color:var(--green)}
/* giá */
.racks{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:9px;padding:12px}
.rack{border:1px solid var(--line);border-radius:12px;padding:11px;background:#fff;cursor:pointer;display:flex;flex-direction:column;gap:3px}
.rack:hover{border-color:#a9c6cc;box-shadow:0 4px 14px rgba(15,23,42,.07)}
.rack.on{border-color:var(--primary);box-shadow:0 0 0 2px #cfe8ec}
.rack .no{font-size:19px;font-weight:800;line-height:1.1}.rack .no small{font-size:9.5px;color:var(--muted);margin-left:3px}
.rack .m{font-size:9.5px;color:var(--muted)}
/* marker */
.mk-chips{display:flex;gap:5px;flex-wrap:wrap}
.chip{display:inline-flex;align-items:center;gap:5px;background:var(--purple-soft);color:var(--purple);
 border:1px solid #ddd0f7;border-radius:999px;padding:3px 5px 3px 9px;font-size:9.5px;font-weight:800}
.chip button{border:0;background:transparent;color:var(--purple);font-size:12px;line-height:1;padding:0 2px}
.chip.ro{padding:3px 9px}
.mk-list{max-height:230px;overflow:auto;border:1px solid var(--line);border-radius:10px}
.mk-row{display:flex;align-items:center;gap:9px;padding:7px 10px;border-bottom:1px solid var(--line2);cursor:pointer}
.mk-row:last-child{border-bottom:0}.mk-row:hover{background:#f6fafb}.mk-row.on{background:var(--purple-soft)}
.mk-row .t{font-weight:800;font-size:11px}.mk-row .c{font-size:9px;color:var(--muted);margin-left:auto;text-align:right}
.panels{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:9px}
.pnl{height:26px;padding:0 10px;border:1px solid #ddd0f7;background:#faf7ff;color:var(--purple);border-radius:999px;font-size:10px;font-weight:800}
.pnl:hover{background:var(--purple-soft)}
/* tiến trình */
.tl{display:grid;gap:0;padding:4px 0}
.tl-i{display:grid;grid-template-columns:96px 18px 1fr;gap:10px;align-items:start;padding:7px 0}
.tl-i .d{font-size:10px;color:var(--muted);text-align:right;padding-top:2px;font-variant-numeric:tabular-nums}
.tl-i .dot{position:relative;display:flex;justify-content:center}
.tl-i .dot i{width:10px;height:10px;border-radius:50%;background:var(--primary);margin-top:5px;z-index:1}
.tl-i .dot:before{content:"";position:absolute;top:0;bottom:-14px;width:2px;background:var(--line)}
.tl-i:last-child .dot:before{display:none}
.tl-i.soan .dot i{background:var(--blue)}.tl-i.doc .dot i{background:var(--green)}
.tl-i.ihc .dot i,.tl-i.nhuom .dot i,.tl-i.kq .dot i{background:var(--purple)}
.tl-i.hc .dot i{background:var(--amber)}.tl-i.chot .dot i{background:var(--green)}
.tl-i .b{font-size:11.5px;font-weight:800}.tl-i .c{font-size:11px;line-height:1.5}
.tl-i .w{font-size:9.5px;color:var(--muted)}
/* hội chẩn */
.hc-wrap{display:grid;grid-template-columns:290px 1fr}
.hc-list{border-right:1px solid var(--line);max-height:calc(100vh - 300px);overflow:auto}
.hc-item{padding:10px 12px;border-bottom:1px solid var(--line2);cursor:pointer}
.hc-item:hover{background:#fbfdfe}.hc-item.on{background:var(--soft);box-shadow:inset 3px 0 0 var(--primary)}
.hc-item .c{font-size:11.5px;font-weight:800}.hc-item .s{font-size:9px;color:var(--muted);margin-top:2px}
.hc-body{padding:14px 16px;max-height:calc(100vh - 300px);overflow:auto}
.yk{display:grid;gap:7px;margin:10px 0}
.yk-i{border:1px solid var(--line);border-radius:11px;padding:9px 11px}
.yk-i .t{display:flex;align-items:center;gap:7px;font-size:10px;margin-bottom:3px}
.yk-i .t b{font-size:11px}.yk-i .t .d{margin-left:auto;color:var(--muted);font-size:9px}
.yk-i .n{font-size:11px;line-height:1.55;white-space:pre-wrap}
.yk-add{display:grid;gap:7px;border:1px dashed #c5dde2;border-radius:11px;padding:10px 11px;background:#f8fbfc}
.yk-add textarea{border:1px solid var(--line);border-radius:8px;padding:8px 9px;font-size:11px;min-height:56px;resize:vertical}
.yk-add .r{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.yk-add input[type=text]{height:30px;border:1px solid var(--line);border-radius:8px;padding:0 9px;font-size:10.5px}
.imgs{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin:8px 0}
.img-i{position:relative;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#f1f5f9;aspect-ratio:4/3}
.img-i img{width:100%;height:100%;object-fit:cover;display:block;cursor:zoom-in}
.img-i .x{position:absolute;right:4px;top:4px;width:22px;height:22px;border:0;border-radius:50%;
 background:rgba(255,255,255,.92);color:var(--red);font-size:12px;line-height:1;box-shadow:0 2px 6px rgba(15,23,42,.2)}
.img-i .cap{position:absolute;left:0;right:0;bottom:0;background:rgba(15,23,42,.6);color:#fff;font-size:8.5px;padding:3px 5px;
 white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.warn-anh{background:var(--amber-soft);border:1px solid #f0dfae;color:#7a5b12;border-radius:9px;padding:8px 10px;font-size:10px;margin:8px 0}
.chot{margin-top:12px;border:1px solid #bfe0d0;background:var(--green-soft);border-radius:12px;padding:11px 13px}
.chot h4{margin:0 0 5px;font-size:11px;color:var(--green);text-transform:uppercase;letter-spacing:.05em}
.chot .n{font-size:12px;line-height:1.6;font-weight:700;white-space:pre-wrap}
.chot .s{font-size:9.5px;color:#4a7358;margin-top:5px}
/* modal */
.mbg{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;padding:16px;z-index:100}
.mbg.show{display:flex}
.mdl{width:min(760px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:15px;box-shadow:0 18px 55px rgba(15,23,42,.2)}
.mdl-h{padding:14px 17px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-start}
.mdl-h h2{font-size:14px;margin:0 0 3px}.mdl-h p{margin:0;font-size:9.5px;color:var(--muted)}
.mdl-h .x{width:28px;height:28px;border:0;border-radius:8px;background:#eef2f6}
.mdl-b{padding:15px 17px}.mdl-f{padding:12px 17px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:8px}
.f{display:grid;gap:4px;margin-bottom:10px}.f label{font-size:9px;font-weight:800;color:#475569}
.f input,.f select,.f textarea{border:1px solid var(--line);border-radius:8px;padding:8px 9px;font-size:11px;background:#fff;width:100%}
.f3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}.f2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#lightbox{position:fixed;inset:0;background:rgba(9,16,24,.9);display:none;align-items:center;justify-content:center;z-index:140;padding:20px}
#lightbox.show{display:flex}#lightbox img{max-width:100%;max-height:100%;border-radius:8px}
.toast-wrap{position:fixed;right:18px;bottom:18px;display:grid;gap:8px;z-index:150}
.toast{background:#18333d;color:#fff;padding:10px 13px;border-radius:9px;font-size:10.5px;max-width:340px}
.toast.err{background:#8a1f16}
@media(max-width:1250px){.tiles{grid-template-columns:repeat(3,1fr)}.hc-wrap{grid-template-columns:1fr}
 .hc-list{border-right:0;border-bottom:1px solid var(--line);max-height:210px}}
@media(max-width:700px){.shell{padding:12px 10px 30px}.tiles{grid-template-columns:repeat(2,1fr)}
 .head{flex-wrap:wrap}.f3,.f2{grid-template-columns:1fr}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script>window.SLIDE={
  state:"{{ route('slide.state') }}", save:"{{ route('slide.save') }}",
  reader:"{{ route('slide.reader') }}", mark:"{{ route('slide.mark') }}",
  status:"{{ route('slide.status') }}", trace:"{{ route('slide.trace') }}",
  ihc:"{{ route('slide.ihc') }}", ihcSave:"{{ route('slide.ihc.save') }}",
  ihcStep:"{{ route('slide.ihc.step', ['ihc' => '__ID__']) }}",
  consult:"{{ route('slide.consult') }}", cOpen:"{{ route('slide.consult.open') }}",
  cNote:"{{ route('slide.consult.note') }}", cUpload:"{{ route('slide.consult.upload') }}",
  cImgDel:"{{ route('slide.consult.image.del', ['image' => '__ID__']) }}",
  cClose:"{{ route('slide.consult.close') }}", patient:"{{ route('slide.patient') }}",
  export:"{{ route('slide.export') }}", ihcExport:"{{ route('slide.ihc.export') }}",
  csrf:"{{ csrf_token() }}"};</script>
</head>
<body>
@include('modules._sidebar')

<div class="shell">
  <div class="breadcrumb">Giải phẫu bệnh › <b>Sổ soạn tiêu bản &amp; hóa mô miễn dịch</b></div>
  <div class="head">
    <div>
      <h1>Sổ soạn tiêu bản &amp; hóa mô miễn dịch</h1>
      <p>Mã tiêu bản gồm 2 số cuối năm + chữ cái + 4 chữ số (26C2472), mỗi mã là duy nhất và có thể có nhiều block, nhiều lam.
         Sổ chia theo đầu mã; ngày soạn và người soạn tự điền theo ngày nhập và tài khoản đang đăng nhập.</p>
    </div>
    <div class="right"><span class="hint" id="whoAmI"></span></div>
  </div>

  <div class="tabs">
    <button class="tab on" data-v="soan" onclick="go('soan')">🔬 Sổ soạn tiêu bản</button>
    <button class="tab" data-v="doc" onclick="go('doc')">🩺 Bác sĩ đọc theo giá</button>
    <button class="tab" data-v="hmmd" onclick="go('hmmd')">🧬 Hóa mô miễn dịch</button>
    <button class="tab" data-v="tra" onclick="go('tra')">🔎 Tra cứu &amp; tiến trình</button>
    <button class="tab" data-v="hoichan" onclick="go('hoichan')">👥 Hội chẩn</button>
  </div>

  <!-- ===== 1. Sổ soạn ===== -->
  <section class="view on" id="v-soan">
    <div class="panel">
      <div class="pbar">
        <label class="lbl">Đầu mã<select id="prefix" style="width:150px"></select></label>
        <label class="lbl">Nhảy tới mã<input id="jump" placeholder="VD: 2472" style="width:110px"></label>
        <label class="lbl" style="align-self:end"><span style="display:flex;gap:5px;align-items:center;height:31px">
          <input type="checkbox" id="onlyFilled" style="width:14px;height:14px;accent-color:var(--primary)">
          <span class="hint" style="font-weight:800">Chỉ hiện dòng đã nhập</span></span></label>
        <span class="push"></span>
        <button class="btn sm" onclick="moDan()">📋 Dán từ Excel</button>
        <a class="btn sm" id="btnXuat" href="#">⇩ Xuất Excel</a>
        <span class="saveState" id="saveState"></span>
        <button class="btn sm primary" id="btnSave" onclick="luuNgay()">Lưu</button>
      </div>
      <div class="bulk off" id="bulkBar">
        <b id="bulkCount"></b>
        <label class="lbl">Giá số<input id="bGia" style="width:80px"></label>
        <label class="lbl">KTV cắt<input id="bCat" list="ktvList" style="width:130px"></label>
        <label class="lbl">KTV soạn<input id="bSoan" list="ktvList" style="width:130px"></label>
        <label class="lbl">BS đọc<input id="bBs" list="ktvList" style="width:130px"></label>
        <button class="btn sm primary" onclick="apBulk()">Áp cho dòng đã chọn</button>
        <button class="btn sm" onclick="boChon()">Bỏ chọn</button>
      </div>
      <div class="twrap" id="gridScroll"><table class="grid-t"><thead><tr>
        <th class="chk"><input type="checkbox" id="chkAll" onclick="chonHet(this.checked)"></th>
        <th>Mã tiêu bản</th><th>Số block</th><th>Số tiêu bản</th><th>Ngày soạn</th><th>Giá</th>
        <th>KTV cắt</th><th>KTV soạn</th><th>BS đọc</th><th>Ghi chú</th>
      </tr></thead><tbody id="soanBody"></tbody></table></div>
      <div class="pbar"><span class="hint" id="soanFoot"></span></div>
    </div>
  </section>

  <!-- ===== 2. Bác sĩ đọc theo giá ===== -->
  <section class="view" id="v-doc">
    <div class="panel">
      <div class="pbar"><h2>Chọn giá tiêu bản</h2>
        <span class="hint">Mỗi ô là một giá đã soạn. Chọn giá để đọc và ghi kết quả cho từng mã.</span></div>
      <div class="racks" id="racks"></div>
    </div>
    <div class="panel" id="docPanel" style="display:none">
      <div class="pbar">
        <h2 id="docTitle"></h2>
        <span class="push"></span>
        <label class="lbl">BS đọc<input id="bsAll" list="bsList" placeholder="Tên bác sĩ" style="width:170px"></label>
        <button class="btn sm" onclick="dienBs()">Điền cho cả giá</button>
        <button class="btn sm primary" onclick="danhDau(true)">✓ Đã đọc kết quả</button>
        <button class="btn sm" onclick="danhDau(false)">Bỏ đánh dấu</button>
      </div>
      <div class="twrap"><table><thead><tr>
        <th class="chk"><input type="checkbox" id="chkAllDoc" onclick="chonHetDoc(this.checked)"></th>
        <th>Mã tiêu bản</th><th class="ctr">Block</th><th class="ctr">Lam</th>
        <th style="min-width:280px">Kết quả / tình trạng</th><th>BS đọc</th>
        <th>Marker HMMD</th><th class="ctr">Hội chẩn</th><th class="ctr">Trạng thái</th>
      </tr></thead><tbody id="docBody"></tbody></table></div>
      <div class="pbar"><span class="hint" id="docFoot"></span>
        <span class="push"></span><button class="btn sm primary" onclick="luuDoc()">Lưu kết quả đã nhập</button></div>
    </div>
  </section>

  <!-- ===== 3. Hóa mô miễn dịch ===== -->
  <section class="view" id="v-hmmd">
    <div class="panel">
      <div class="pbar"><h2>Sổ hóa mô miễn dịch</h2>
        <span class="hint">Sinh từ chỉ định của bác sĩ trên mã tiêu bản — chỉ ca làm HMMD mới cần nhập thông tin bệnh nhân.</span>
        <span class="push"></span>
        <select id="hmTt" style="width:170px">
          <option value="">Tất cả</option><option value="cho">Chờ nhuộm</option>
          <option value="nhuom">Đã nhuộm</option><option value="doc">Đã đọc kết quả</option>
        </select>
        <a class="btn sm" href="{{ route('slide.ihc.export') }}">⇩ Xuất Excel</a>
        <button class="btn sm primary" onclick="moMk('')">＋ Thêm chỉ định</button>
      </div>
      <div class="twrap"><table><thead><tr>
        <th>Mã tiêu bản</th><th>Mã BN</th><th>Họ và tên</th><th class="ctr">Năm sinh</th><th>Đối tượng</th><th>Khoa</th>
        <th>Vị trí lấy mẫu</th><th>Chẩn đoán lâm sàng</th><th class="ctr">SL block</th><th>Marker chỉ định</th>
        <th>BS đọc KQ</th><th>Lấy mẫu</th><th>Nhận mẫu</th><th>Đọc KQ</th><th></th>
      </tr></thead><tbody id="hmBody"></tbody></table></div>
    </div>
  </section>

  <!-- ===== 4. Tra cứu & tiến trình ===== -->
  <section class="view" id="v-tra">
    <div class="panel">
      <div class="pbar"><h2>Tiến trình của một mã</h2>
        <input id="traCode" placeholder="Nhập mã tiêu bản, VD: 26C2472" style="width:230px">
        <button class="btn sm primary" onclick="xemTien()">Xem tiến trình</button>
        <span class="hint">Hiện toàn bộ mã đó đã làm những gì: soạn, đọc, hóa mô miễn dịch, hội chẩn.</span></div>
      <div id="traBox" style="padding:14px 16px"><div class="empty">Nhập mã để xem tiến trình.</div></div>
    </div>

    <div class="tiles" id="ttTiles"></div>
    <div class="panel">
      <div class="pbar"><h2>Tra cứu tình trạng</h2>
        <input id="ttQ" placeholder="Tìm mã, giá, bác sĩ, KTV, bệnh nhân, marker..." style="width:300px">
        <button class="btn sm" onclick="xoaLocTt()">Xóa lọc</button>
        <span class="push hint" id="ttCount"></span></div>
      <div class="twrap"><table><thead><tr>
        <th>Mã tiêu bản</th><th class="ctr">Block</th><th class="ctr">Lam</th><th>Ngày soạn</th><th class="ctr">Giá</th>
        <th>KTV soạn</th><th>BS đọc</th><th>Kết quả / tình trạng</th><th>Bệnh nhân (HMMD)</th><th>Marker</th><th>Tình trạng</th>
      </tr></thead><tbody id="ttBody"></tbody></table></div>
    </div>
  </section>

  <!-- ===== 5. Hội chẩn ===== -->
  <section class="view" id="v-hoichan">
    <div class="panel">
      <div class="pbar"><h2>Hội chẩn</h2>
        <input id="hcCode" placeholder="Mã tiêu bản cần mở hội chẩn" style="width:210px">
        <button class="btn sm primary" onclick="moHc()">Mở phiên hội chẩn</button>
        <span class="hint">Ảnh đính kèm sẽ được xóa tự động ngay khi chốt kết luận.</span></div>
      <div class="hc-wrap">
        <div class="hc-list" id="hcList"></div>
        <div class="hc-body" id="hcBody"></div>
      </div>
    </div>
  </section>
</div>

<datalist id="ktvList"></datalist>
<datalist id="bsList"></datalist>

<!-- popup chỉ định marker + thông tin bệnh nhân -->
<div class="mbg" id="mkModal"><div class="mdl">
  <div class="mdl-h"><div><h2>Chỉ định hóa mô miễn dịch</h2><p id="mkSub"></p></div>
    <button class="x" onclick="dongM('mkModal')">×</button></div>
  <div class="mdl-b">
    <div class="f2">
      <div class="f"><label>Mã tiêu bản *</label><input id="mkCode" placeholder="VD: 26C2472"></div>
      <div class="f"><label>Mã bệnh nhân / số hồ sơ</label><input id="mkMaBn" placeholder="Nhập để nối các mã của cùng một người">
        <span class="hint" id="mkBnInfo"></span></div>
    </div>
    <div class="f3">
      <div class="f"><label>Họ và tên bệnh nhân</label><input id="mkTen"></div>
      <div class="f"><label>Năm sinh</label><input id="mkNam" placeholder="1953"></div>
      <div class="f"><label>Đối tượng</label><input id="mkDt" list="dtList" placeholder="BHYT / Dịch vụ..."></div>
    </div>
    <div class="f3">
      <div class="f"><label>Khoa</label><input id="mkKhoa"></div>
      <div class="f"><label>Vị trí lấy mẫu</label><input id="mkViTri"></div>
      <div class="f"><label>Số lượng block</label><input id="mkBlock" type="number" min="0"></div>
    </div>
    <div class="f2">
      <div class="f"><label>Ngày lấy mẫu</label><input id="mkLay" type="date"></div>
      <div class="f"><label>Ngày nhận mẫu</label><input id="mkNhan" type="date"></div>
    </div>
    <div class="f"><label>Chẩn đoán lâm sàng</label><input id="mkCd"></div>
    <div class="hint" style="margin-bottom:7px">Bộ marker gợi ý theo loại u — bấm để thêm cả bộ:</div>
    <div class="panels" id="mkPanels"></div>
    <div class="f"><label>Marker đã chọn</label><div class="mk-chips" id="mkChips"></div></div>
    <div class="f"><label>Tìm marker <span class="hint" id="mkTotal"></span></label>
      <input id="mkQ" placeholder="Gõ tên marker: TTF, Napsin, CK7, ER..." oninput="veMkList()"></div>
    <div class="mk-list" id="mkList"></div>
  </div>
  <div class="mdl-f"><button class="btn" onclick="dongM('mkModal')">Hủy</button>
    <button class="btn primary" id="mkSaveBtn" onclick="luuMk()">Lưu chỉ định</button></div>
</div></div>

<!-- popup dán từ Excel -->
<div class="mbg" id="danModal"><div class="mdl">
  <div class="mdl-h"><div><h2>Dán từ Excel</h2>
    <p>Sao chép vùng ô trong Excel rồi dán vào ô bên dưới. Ngày soạn và người soạn vẫn tự điền.</p></div>
    <button class="x" onclick="dongM('danModal')">×</button></div>
  <div class="mdl-b">
    <div class="f2">
      <div class="f"><label>Cột đầu tiên trong vùng dán là</label>
        <select id="danMode" onchange="veDanGoiY()">
          <option value="ma">Mã tiêu bản (26C2472) hoặc số thứ tự (2472)</option>
          <option value="seq">Số block — điền liên tiếp từ một mã</option>
        </select></div>
      <div class="f" id="danStartWrap" style="display:none"><label>Bắt đầu từ mã</label>
        <input id="danStart" placeholder="VD: 2472 hoặc 26C2472"></div>
    </div>
    <div class="f"><label>Thứ tự cột</label>
      <div class="hint" id="danCols"></div></div>
    <div class="f"><textarea id="danBox" style="min-height:180px;font-family:ui-monospace,Consolas,monospace;font-size:10.5px"></textarea></div>
    <div class="hint">Dòng nào để trống cột nào thì cột đó giữ nguyên giá trị cũ. Mã không thuộc đầu mã đang mở sẽ bị bỏ qua.</div>
  </div>
  <div class="mdl-f"><button class="btn" onclick="dongM('danModal')">Hủy</button>
    <button class="btn primary" onclick="danVao()">Đưa vào sổ</button></div>
</div></div>

<datalist id="dtList"><option value="BHYT"><option value="Dịch vụ"><option value="Giám định"></datalist>
<div id="lightbox" onclick="this.classList.remove('show')"><img id="lightboxImg" alt=""></div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
/* Bộ marker hay dùng theo loại u */
const PANELS={
 'Phổi':['TTF-1','Napsin A','P40','CK7'],
 'Vú':['ER','PR','Her2','Ki67','E-cadherin'],
 'Đại trực tràng':['CK20','CDX2','SATB2','CK7'],
 'Dạ dày':['CK7','CK20','Her2','CDX2'],
 'Lympho':['CD20','CD3','CD5','CD10','Bcl-2','Bcl-6','Ki67'],
 'Tiền liệt tuyến':['PSA','P504S','P63'],
 'Phần mềm':['Vimentin','Desmin','Actin','S100','CD34'],
 'Cổ tử cung':['P16','Ki67'],
 'Gan':['HepPar-1','Arginase-1','Glypican-3','AFP'],
 'Không rõ nguồn gốc':['CK7','CK20','TTF-1','CDX2','P63'],
};
const TT={soan:['soan','Đã soạn'],doc:['doc','Bác sĩ đã đọc'],ihc:['ihc','Đang hóa mô'],
  hc:['hc','Đang hội chẩn'],xong:['xong','Đã xong']};

let D={rows:{},from:1,to:250,prefix:'',dauMa:[],me:'',ktv:[]};
let dirty=new Set(), sel=new Set(), lastIdx=-1, saveTimer=null;
let docRows=[], docSel=new Set(), curGia='';
let MK=[], mkPick=[], mkEditId=null;
let hcCur=null, hcDs=[];

const $=id=>document.getElementById(id);
const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const viDate=s=>/^\d{4}-\d{2}-\d{2}$/.test(s||'')?s.slice(8,10)+'/'+s.slice(5,7)+'/'+s.slice(0,4):'';
const pad4=n=>String(n).padStart(4,'0');
const maCua=seq=>D.prefix+pad4(seq);
function toast(m,err){const e=document.createElement('div');e.className='toast'+(err?' err':'');e.innerHTML=m;
  $('toastWrap').appendChild(e);setTimeout(()=>e.remove(),err?5200:2800)}
const H={'Content-Type':'application/json','X-CSRF-TOKEN':window.SLIDE.csrf,'Accept':'application/json'};
async function post(url,body){
  const r=await fetch(url,{method:'POST',credentials:'same-origin',headers:H,body:JSON.stringify(body)});
  const j=await r.json().catch(()=>({}));
  if(!r.ok)throw new Error((j.errors||['HTTP '+r.status]).join(', '));
  return j;
}
async function get(url){const r=await fetch(url,{credentials:'same-origin',headers:{'Accept':'application/json'}});
  if(!r.ok)throw new Error('HTTP '+r.status);return r.json()}

function go(v){
  document.querySelectorAll('.tab').forEach(b=>b.classList.toggle('on',b.dataset.v===v));
  document.querySelectorAll('.view').forEach(s=>s.classList.toggle('on',s.id==='v-'+v));
  if(v==='doc')taiDoc();
  if(v==='hmmd')taiHmmd();
  if(v==='tra')taiTinhTrang();
  if(v==='hoichan')taiHc();
}

/* ===== 1. Sổ soạn ===== */
async function taiSoan(){
  const p=$('prefix').value||'';
  D=await get(`${window.SLIDE.state}?prefix=${encodeURIComponent(p)}`);
  $('whoAmI').textContent=D.me?('Đang đăng nhập: '+D.me):'';
  $('ktvList').innerHTML=(D.ktv||[]).map(x=>`<option value="${esc(x)}">`).join('');
  $('btnXuat').href=window.SLIDE.export+'?prefix='+encodeURIComponent(D.prefix);
  veDauMa();veSoan(true);
}
function veDauMa(){
  const cur=$('prefix').value||D.prefix;
  $('prefix').innerHTML=D.dauMa.map(x=>`<option value="${x.prefix}" ${x.prefix===cur?'selected':''}>
    ${x.prefix}${x.n?` — ${x.n} mã`:' — sổ trống'}</option>`).join('');
}
/* ---- Cuộn ảo: sổ trải đủ 9999 dòng nhưng chỉ dựng những dòng đang nhìn thấy ---- */
const ROW_H=32;
let vStart=-1,vEnd=-1;
function dsSeq(){
  return $('onlyFilled').checked
    ? Object.keys(D.rows).map(Number).sort((a,b)=>a-b)
    : Array.from({length:D.soDong||9999},(_,i)=>i+1);
}
function veSoan(epLai){
  const list=dsSeq(), sc=$('gridScroll');
  const vh=sc.clientHeight||620;
  const start=Math.max(0,Math.floor(sc.scrollTop/ROW_H)-6);
  const end=Math.min(list.length,start+Math.ceil(vh/ROW_H)+12);
  if(!epLai&&start===vStart&&end===vEnd)return;   // cùng khung nhìn thì khỏi dựng lại (giữ con trỏ đang gõ)
  vStart=start;vEnd=end;

  let html=start>0?`<tr class="spacer"><td colspan="10" style="height:${start*ROW_H}px"></td></tr>`:'';
  for(let k=start;k<end;k++){
    const s=list[k],r=D.rows[s];
    const v=f=>esc(r?(r[f]??''):'');
    const i=(f,type)=>`<input data-seq="${s}" data-f="${f}" type="${type||'text'}" value="${v(f)}"
      ${f==='ktvCat'||f==='ktvSoan'?'list="ktvList"':''}>`;
    html+=`<tr class="${sel.has(s)?'sel ':''}${dirty.has(s)?'dirty ':''}${r?'':'blank'}" data-seq="${s}">
      <td class="chk"><input type="checkbox" ${sel.has(s)?'checked':''} onclick="chonDong(${s},${k},event)"></td>
      <td class="pad code">${maCua(s)}</td>
      <td>${i('soBlock','number')}</td><td>${i('soTieuBan','number')}</td>
      <td>${i('ngaySoan','date')}</td><td>${i('giaSo')}</td>
      <td>${i('ktvCat')}</td><td>${i('ktvSoan')}</td><td>${i('bsDoc')}</td><td>${i('ghiChu')}</td></tr>`;
  }
  const con=list.length-end;
  if(con>0)html+=`<tr class="spacer"><td colspan="10" style="height:${con*ROW_H}px"></td></tr>`;
  if(!list.length)html='<tr><td colspan="10" class="empty">Chưa có mã nào được nhập trong sổ này.</td></tr>';
  $('soanBody').innerHTML=html;

  const daNhap=Object.keys(D.rows).length;
  $('soanFoot').innerHTML=`Đầu mã <b>${D.prefix}</b> · sổ trải ${D.soDong} dòng · <b>${daNhap}</b> mã đã nhập
    · đang dựng dòng ${list.length?list[start]:0}–${list.length?list[end-1]:0}`;
  $('bulkBar').classList.toggle('off',!sel.size);
  $('bulkCount').textContent=`Đang chọn ${sel.size} dòng`;
  capNhatSave();
}
let rafId=null;
function onCuon(){if(rafId)return;rafId=requestAnimationFrame(()=>{rafId=null;veSoan()})}
function nhayToi(seq){
  $('gridScroll').scrollTop=Math.max(0,(seq-1)*ROW_H-ROW_H*3);
  veSoan(true);
}
function capNhatSave(){
  const el=$('saveState');
  el.className='saveState'+(dirty.size?' dirty':'');
  el.textContent=dirty.size?`${dirty.size} dòng chưa lưu`:'';
  $('btnSave').disabled=!dirty.size;
}
function chonDong(seq,idx,e){
  if(e.shiftKey&&lastIdx>=0){                 // giữ Shift để quét cả dải, kể cả phần chưa dựng
    const list=dsSeq();
    const [a,b]=[Math.min(lastIdx,idx),Math.max(lastIdx,idx)];
    for(let i=a;i<=b;i++)if(list[i])sel.add(list[i]);
  }else{sel.has(seq)?sel.delete(seq):sel.add(seq)}
  lastIdx=idx;veSoan(true);
}
/** Chọn hết = chọn mọi mã đã có dữ liệu (chọn cả 9999 dòng trống là vô nghĩa). */
function chonHet(on){
  sel=on?new Set(Object.keys(D.rows).map(Number)):new Set();
  veSoan(true);
}
function boChon(){sel=new Set();veSoan(true)}
function datO(seq,f,val){
  D.rows[seq]??={seq,soBlock:'',soTieuBan:'',ngaySoan:'',giaSo:'',ktvCat:'',ktvSoan:'',bsDoc:'',ghiChu:''};
  D.rows[seq][f]=val;dirty.add(seq);
}
/* nhập số block / số tiêu bản thì ngày soạn + người soạn tự điền */
function tuDien(seq){
  const r=D.rows[seq];if(!r)return;
  if(r.soBlock!==''&&r.soBlock!=null||r.soTieuBan!==''&&r.soTieuBan!=null){
    if(!r.ngaySoan)r.ngaySoan=D.today;
    if(!r.ktvSoan)r.ktvSoan=D.me;
  }
}
document.addEventListener('input',e=>{
  const el=e.target;if(!el.dataset||!el.dataset.seq||!el.dataset.f)return;
  const seq=Number(el.dataset.seq);
  datO(seq,el.dataset.f,el.value);
  if(el.dataset.f==='soBlock'||el.dataset.f==='soTieuBan'){
    tuDien(seq);
    const tr=el.closest('tr');
    const d=tr.querySelector('[data-f=ngaySoan]'),k=tr.querySelector('[data-f=ktvSoan]');
    if(d&&!d.value)d.value=D.rows[seq].ngaySoan||'';
    if(k&&!k.value)k.value=D.rows[seq].ktvSoan||'';
  }
  capNhatSave();
  clearTimeout(saveTimer);saveTimer=setTimeout(luuNgay,1200);
});
async function luuNgay(){
  if(!dirty.size)return;
  const rows=[...dirty].map(s=>({seq:s,...(D.rows[s]||{})}));
  const gui=new Set(dirty);dirty=new Set();capNhatSave();
  try{
    const j=await post(window.SLIDE.save,{prefix:D.prefix,rows});
    $('saveState').className='saveState ok';$('saveState').textContent='Đã lưu';
    setTimeout(()=>{if(!dirty.size)$('saveState').textContent=''},1800);
    if(j.removed)await taiSoan();
  }catch(e){gui.forEach(s=>dirty.add(s));capNhatSave();toast('Lưu thất bại: '+e.message,true)}
}
function apBulk(){
  if(!sel.size)return;
  const v={giaSo:$('bGia').value.trim(),ktvCat:$('bCat').value.trim(),
    ktvSoan:$('bSoan').value.trim(),bsDoc:$('bBs').value.trim()};
  const dat=Object.entries(v).filter(([,x])=>x);
  if(!dat.length)return toast('Nhập ít nhất một ô để áp',true);
  sel.forEach(s=>{dat.forEach(([k,x])=>datO(s,k,x));tuDien(s)});
  veSoan(true);luuNgay();
  toast(`Đã áp cho <b>${sel.size}</b> mã tiêu bản`);
}

/* dán từ Excel — cột hay dùng nhất là số block + số tiêu bản */
const COT_MA =['Mã tiêu bản','Số block','Số tiêu bản','Giá','KTV cắt','KTV soạn','BS đọc','Ghi chú'];
const COT_SEQ=['Số block','Số tiêu bản','Giá','KTV cắt','KTV soạn','BS đọc','Ghi chú'];
const F_SAU=['soBlock','soTieuBan','giaSo','ktvCat','ktvSoan','bsDoc','ghiChu'];
function moDan(){
  $('danBox').value='';$('danStart').value='';$('danMode').value='ma';
  veDanGoiY();$('danModal').classList.add('show');setTimeout(()=>$('danBox').focus(),60);
}
function veDanGoiY(){
  const m=$('danMode').value;
  $('danStartWrap').style.display=m==='seq'?'grid':'none';
  $('danCols').innerHTML=(m==='ma'?COT_MA:COT_SEQ).map((c,i)=>`<b>${i+1}.</b> ${c}`).join(' &nbsp;·&nbsp; ');
  $('danBox').placeholder=m==='ma'?'2472\t2\t3\t20\tĐức\tHuệ':'2\t3\t20\tĐức\tHuệ';
}
/** "26C2472" hoặc "2472" -> số thứ tự, null nếu khác đầu mã đang mở */
function raSeq(v){
  const s=String(v||'').trim().toUpperCase();
  if(/^\d{1,4}$/.test(s))return Number(s);
  const m=/^(\d{2}[A-Z])(\d{1,4})$/.exec(s);
  if(!m)return null;
  return m[1]===D.prefix?Number(m[2]):null;
}
function danVao(){
  const mode=$('danMode').value;
  const lines=$('danBox').value.split(/\r?\n/).map(l=>l.replace(/\s+$/,'')).filter(l=>l.trim());
  if(!lines.length)return toast('Chưa có nội dung để dán',true);
  let seq=null;
  if(mode==='seq'){
    seq=raSeq($('danStart').value);
    if(!seq)return toast('Nhập mã bắt đầu hợp lệ thuộc đầu mã '+D.prefix,true);
  }
  let n=0,boQua=0,dau=null;
  lines.forEach(l=>{
    const c=l.split('\t').map(x=>x.trim());
    let s,val;
    if(mode==='ma'){s=raSeq(c[0]);val=c.slice(1)}
    else{s=seq++;val=c}
    if(!s||s<1||s>9999){boQua++;return}
    F_SAU.forEach((f,i)=>{if(val[i]!==undefined&&val[i]!=='')datO(s,f,val[i])});
    tuDien(s);
    dau??=s;n++;
  });
  dongM('danModal');
  veSoan(true);
  if(dau!==null)nhayToi(dau);                    // cuộn thẳng tới dòng vừa dán
  luuNgay();
  toast(`Đã đưa <b>${n}</b> dòng vào sổ ${D.prefix}${boQua?` · bỏ qua ${boQua} dòng sai mã`:''}`);
}

/* ===== 2. Bác sĩ đọc ===== */
async function taiDoc(){
  const d=await get(window.SLIDE.reader+(curGia?'?gia='+encodeURIComponent(curGia):''));
  $('bsList').innerHTML=(d.bs||[]).map(x=>`<option value="${esc(x)}">`).join('');
  $('racks').innerHTML=d.giaList.length?d.giaList.map(r=>`<div class="rack ${curGia===r.gia?'on':''}"
    onclick="chonGia('${esc(r.gia)}')">
    <div class="no">Giá ${esc(r.gia)}<small>${r.n} mã</small></div>
    <div class="m">Soạn ${viDate(r.ngaySoan)||'—'}</div>
    <div class="m">${r.daDoc>=r.n?'đã đọc hết':`còn ${r.n-r.daDoc} mã chưa đọc`}</div></div>`).join('')
    :'<div class="empty">Chưa có giá nào. Sang tab Sổ soạn để nhập giá cho tiêu bản.</div>';
  docRows=d.rows||[];
  $('docPanel').style.display=curGia?'block':'none';
  if(curGia)veDoc();
}
function chonGia(g){curGia=g;docSel=new Set();taiDoc()}
function veDoc(){
  $('docTitle').textContent=`Giá ${curGia} — ${docRows.length} mã tiêu bản`;
  $('docBody').innerHTML=docRows.map(r=>`<tr class="${docSel.has(r.code)?'sel':''}">
    <td class="chk"><input type="checkbox" ${docSel.has(r.code)?'checked':''} onclick="chonDoc('${r.code}')"></td>
    <td class="main">${r.code}</td><td class="ctr">${r.soBlock??'—'}</td><td class="ctr">${r.soTieuBan??'—'}</td>
    <td><input data-code="${r.code}" data-df="ketQua" value="${esc(r.ketQua)}" placeholder="Kết quả / tình trạng..."
      style="width:100%;height:28px;border:1px solid var(--line);border-radius:7px;padding:0 7px;font-size:10.5px"></td>
    <td><input data-code="${r.code}" data-df="bsDoc" list="bsList" value="${esc(r.bsDoc)}"
      style="width:120px;height:28px;border:1px solid var(--line);border-radius:7px;padding:0 7px;font-size:10.5px"></td>
    <td>${r.markers.length?`<div class="mk-chips">${r.markers.map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')}
        <button class="btn sm" onclick="moMk('${r.code}')">Sửa</button></div>`
      :`<button class="btn sm" onclick="moMk('${r.code}')">＋ Chỉ định</button>`}</td>
    <td class="ctr">${r.hoiChan?`<span class="badge ${r.hoiChan==='chot'?'b-xong':'b-hc'}">${r.hoiChan==='chot'?'đã chốt':'đang mở'}</span>`
      :`<button class="btn sm" onclick="moHcTu('${r.code}')">Mở</button>`}</td>
    <td class="ctr">${r.daDoc?`<span class="badge b-doc">đã đọc ${viDate(r.ngayDoc)}</span>`:'<span class="badge b-off">chưa đọc</span>'}</td>
  </tr>`).join('')||'<tr><td colspan="9" class="empty">Giá này chưa có mã nào.</td></tr>';
  $('docFoot').textContent=`Đang chọn ${docSel.size} mã · ${docRows.filter(r=>r.daDoc).length}/${docRows.length} mã đã đọc`;
  $('chkAllDoc').checked=docRows.length>0&&docSel.size===docRows.length;
}
function chonDoc(code){docSel.has(code)?docSel.delete(code):docSel.add(code);veDoc()}
function chonHetDoc(on){docSel=on?new Set(docRows.map(r=>r.code)):new Set();veDoc()}
document.addEventListener('input',e=>{
  const el=e.target;if(!el.dataset||!el.dataset.code||!el.dataset.df)return;
  const r=docRows.find(x=>x.code===el.dataset.code);if(r)r[el.dataset.df]=el.value;
});
async function luuDoc(){
  const byPrefix={};
  docRows.forEach(r=>{
    const p=r.code.slice(0,3),seq=Number(r.code.slice(3));
    (byPrefix[p]??=[]).push({seq,soBlock:r.soBlock,soTieuBan:r.soTieuBan,ngaySoan:r.ngaySoan,
      giaSo:r.giaSo,ktvCat:r.ktvCat,ktvSoan:r.ktvSoan,bsDoc:r.bsDoc,ketQua:r.ketQua,ghiChu:r.ghiChu});
  });
  try{
    for(const [p,rows] of Object.entries(byPrefix))await post(window.SLIDE.save,{prefix:p,rows});
    toast('Đã lưu kết quả đọc');await taiDoc();
  }catch(e){toast('Lưu thất bại: '+e.message,true)}
}
function dienBs(){
  const bs=$('bsAll').value.trim();
  if(!bs)return toast('Nhập tên bác sĩ trước',true);
  docRows.forEach(r=>r.bsDoc=bs);veDoc();
  toast(`Đã điền <b>${esc(bs)}</b> cho ${docRows.length} mã — bấm “Lưu kết quả đã nhập” để ghi lại`);
}
async function danhDau(on){
  if(!docSel.size)return toast('Tích chọn ít nhất một mã',true);
  try{
    await luuDoc0();
    const j=await post(window.SLIDE.mark,{codes:[...docSel],daDoc:on,bsDoc:$('bsAll').value.trim()||null});
    docSel=new Set();await taiDoc();
    toast(on?`Đã đánh dấu <b>${j.n}</b> mã là đã đọc kết quả`:`Đã bỏ đánh dấu ${j.n} mã`);
  }catch(e){toast('Không ghi được: '+e.message,true)}
}
async function luuDoc0(){
  const byPrefix={};
  docRows.filter(r=>docSel.has(r.code)).forEach(r=>{
    const p=r.code.slice(0,3),seq=Number(r.code.slice(3));
    (byPrefix[p]??=[]).push({seq,soBlock:r.soBlock,soTieuBan:r.soTieuBan,ngaySoan:r.ngaySoan,
      giaSo:r.giaSo,ktvCat:r.ktvCat,ktvSoan:r.ktvSoan,bsDoc:r.bsDoc,ketQua:r.ketQua,ghiChu:r.ghiChu});
  });
  for(const [p,rows] of Object.entries(byPrefix))await post(window.SLIDE.save,{prefix:p,rows});
}

/* ===== 3. Hóa mô miễn dịch ===== */
async function taiHmmd(){
  const tt=$('hmTt').value;
  const d=await get(window.SLIDE.ihc+(tt?'?tt='+tt:''));
  MK=d.markers||[];
  $('hmBody').innerHTML=d.rows.length?d.rows.map(h=>`<tr>
    <td class="main">${esc(h.code)}</td><td>${esc(h.maBn)||'<span class="sub">—</span>'}</td>
    <td>${esc(h.benhNhan)||'<span class="sub">—</span>'}</td>
    <td class="ctr">${esc(h.namSinh)}</td><td>${esc(h.doiTuong)}</td><td>${esc(h.khoa)}</td>
    <td>${esc(h.viTri)}</td><td>${esc(h.cdLamSang)}</td><td class="ctr">${h.soBlock??''}</td>
    <td><div class="mk-chips">${h.markers.map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')}</div></td>
    <td>${esc(h.bsDocKq)||'<span class="sub">—</span>'}</td>
    <td>${viDate(h.ngayLayMau)}</td><td>${viDate(h.ngayNhanMau)}</td><td>${viDate(h.ngayDocKq)}</td>
    <td><div style="display:flex;gap:5px;white-space:nowrap">
      ${h.trangThai==='cho'?`<button class="btn sm primary" onclick="buoc(${h.id},'nhuom')">Đã nhuộm</button>`
        :h.trangThai==='nhuom'?`<button class="btn sm green" onclick="buoc(${h.id},'doc')">Đã đọc KQ</button>`
        :'<span class="badge b-xong">xong</span>'}
      <button class="btn sm" onclick="moMk('${esc(h.code)}')">Sửa</button></div></td></tr>`).join('')
    :'<tr><td colspan="15" class="empty">Chưa có chỉ định hóa mô miễn dịch nào.</td></tr>';
}
async function buoc(id,b){
  try{await post(window.SLIDE.ihcStep.replace('__ID__',id),{buoc:b});await taiHmmd();toast('Đã cập nhật bước')}
  catch(e){toast('Không cập nhật được: '+e.message,true)}
}

/* popup marker */
async function moMk(code){
  if(!MK.length){const d=await get(window.SLIDE.ihc);MK=d.markers||[]}
  mkEditId=code||'';
  $('mkCode').value=code||'';$('mkCode').readOnly=!!code;
  ['mkTen','mkNam','mkDt','mkKhoa','mkViTri','mkBlock','mkCd','mkLay','mkNhan','mkMaBn'].forEach(i=>$(i).value='');
  $('mkBnInfo').textContent='';
  mkPick=[];
  if(code){
    const d=await get(window.SLIDE.ihc);
    const h=(d.rows||[]).find(x=>x.code===code);
    if(h){mkPick=h.markers.slice();$('mkTen').value=h.benhNhan;$('mkNam').value=h.namSinh;$('mkMaBn').value=h.maBn||'';
      $('mkDt').value=h.doiTuong;$('mkKhoa').value=h.khoa;$('mkViTri').value=h.viTri;
      $('mkBlock').value=h.soBlock??'';$('mkCd').value=h.cdLamSang;
      $('mkLay').value=h.ngayLayMau;$('mkNhan').value=h.ngayNhanMau}
  }
  $('mkSub').textContent=code?('Mã tiêu bản '+code):'Nhập mã tiêu bản đã có trong sổ soạn';
  $('mkTotal').textContent=`(${MK.length} marker trong danh mục)`;
  $('mkPanels').innerHTML=Object.keys(PANELS).map(k=>`<button class="pnl" onclick="themBo('${esc(k)}')">${esc(k)}</button>`).join('');
  $('mkQ').value='';veMkChips();veMkList();
  $('mkModal').classList.add('show');
}
/** Gõ mã bệnh nhân đã có thì tự điền lại thông tin, khỏi nhập hai lần. */
async function traBn(){
  const ma=$('mkMaBn').value.trim();
  if(!ma){$('mkBnInfo').textContent='';return}
  try{
    const d=await get(window.SLIDE.patient+'?ma='+encodeURIComponent(ma));
    if(!d.found){$('mkBnInfo').textContent='Mã mới — sẽ tạo bệnh nhân khi lưu';return}
    const b=d.bn;
    if(!$('mkTen').value)$('mkTen').value=b.hoTen;
    if(!$('mkNam').value)$('mkNam').value=b.namSinh;
    if(!$('mkDt').value)$('mkDt').value=b.doiTuong;
    if(!$('mkKhoa').value)$('mkKhoa').value=b.khoa;
    $('mkBnInfo').textContent=`${b.hoTen} — đã có ${b.soMa} mã tiêu bản`;
  }catch(e){$('mkBnInfo').textContent=''}
}
function themBo(k){(PANELS[k]||[]).forEach(m=>{if(!mkPick.includes(m))mkPick.push(m)});veMkChips();veMkList()}
function chonMk(n){const i=mkPick.indexOf(n);i>=0?mkPick.splice(i,1):mkPick.push(n);veMkChips();veMkList()}
function veMkChips(){
  $('mkChips').innerHTML=mkPick.length?mkPick.map(m=>`<span class="chip">${esc(m)}
    <button onclick="chonMk('${esc(m).replace(/'/g,"\\'")}')">×</button></span>`).join('')
    :'<span class="hint">Chưa chọn marker nào.</span>';
}
function veMkList(){
  const q=$('mkQ').value.trim().toLowerCase();
  const rows=MK.filter(m=>!q||m.ten.toLowerCase().includes(q)||(m.clone||'').toLowerCase().includes(q)).slice(0,120);
  $('mkList').innerHTML=rows.length?rows.map(m=>`<div class="mk-row ${mkPick.includes(m.ten)?'on':''}"
    onclick="chonMk('${esc(m.ten).replace(/'/g,"\\'")}')"><span class="t">${esc(m.ten)}</span>
    <span class="c">${esc(m.clone||'')}${m.hang?' · '+esc(m.hang):''}</span></div>`).join('')
    :'<div class="empty" style="padding:20px">Không có marker khớp.</div>';
}
async function luuMk(){
  const code=$('mkCode').value.trim().toUpperCase();
  if(!code)return toast('Nhập mã tiêu bản',true);
  $('mkSaveBtn').disabled=true;
  try{
    await post(window.SLIDE.ihcSave,{code,markers:mkPick,maBn:$('mkMaBn').value.trim(),
      benhNhan:$('mkTen').value.trim(),namSinh:$('mkNam').value.trim(),doiTuong:$('mkDt').value.trim(),
      khoa:$('mkKhoa').value.trim(),viTri:$('mkViTri').value.trim(),cdLamSang:$('mkCd').value.trim(),
      soBlock:$('mkBlock').value||null,ngayLayMau:$('mkLay').value||null,ngayNhanMau:$('mkNhan').value||null});
    dongM('mkModal');
    toast(mkPick.length?`Đã lưu chỉ định ${mkPick.length} marker cho ${esc(code)}`:'Đã hủy chỉ định');
    await taiHmmd();if(curGia)await taiDoc();
  }catch(e){toast('Lưu thất bại: '+e.message,true)}finally{$('mkSaveBtn').disabled=false}
}

/* ===== 4. Tra cứu & tiến trình ===== */
async function xemTien(){
  const code=$('traCode').value.trim().toUpperCase();
  if(!code)return;
  const d=await get(window.SLIDE.trace+'?code='+encodeURIComponent(code));
  if(!d.found){
    $('traBox').innerHTML=`<div class="empty">Không tìm thấy mã <b>${esc(code)}</b>.
      ${d.goiY&&d.goiY.length?'<div style="margin-top:8px">Có phải: '+d.goiY.map(c=>`<button class="btn sm" onclick="$('traCode').value='${c}';xemTien()">${c}</button>`).join(' ')+'</div>':''}</div>`;
    return;
  }
  const r=d.record,bn=d.benhNhan;
  $('traBox').innerHTML=`
    ${bn?`<div style="background:var(--soft);border:1px solid #cbe6ea;border-radius:11px;padding:9px 12px;margin-bottom:10px">
      <b style="font-size:12px">${esc(bn.hoTen)}</b>
      <span class="hint"> · mã BN ${esc(bn.maBn)}${bn.namSinh?' · '+esc(bn.namSinh):''}${bn.khoa?' · '+esc(bn.khoa):''}${bn.doiTuong?' · '+esc(bn.doiTuong):''}</span>
      ${d.maKhac.length?`<div style="margin-top:5px" class="hint">Mã khác của bệnh nhân này:
        ${d.maKhac.map(c=>`<button class="btn sm" style="margin:2px 3px 0 0" onclick="$('traCode').value='${c}';xemTien()">${c}</button>`).join('')}</div>`
        :'<div style="margin-top:4px" class="hint">Bệnh nhân này mới có một mã tiêu bản.</div>'}
    </div>`:''}
    <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:baseline;margin-bottom:10px">
      <div style="font-size:17px;font-weight:800">${esc(r.code)}</div>
      <div class="hint">${r.soBlock??'—'} block · ${r.soTieuBan??'—'} lam · giá ${esc(r.giaSo)||'—'}
        · soạn ${viDate(r.ngaySoan)||'—'} · KTV ${esc(r.ktvSoan)||'—'} · BS ${esc(r.bsDoc)||'—'}</div>
      ${d.ihc.length?`<span class="badge b-ihc">${d.ihc.length} phiếu hóa mô</span>`:''}
      ${d.hoiChan?`<span class="badge ${d.hoiChan.ketLuan?'b-xong':'b-hc'}">hội chẩn ${d.hoiChan.ketLuan?'đã chốt':'đang mở'}</span>`:''}
    </div>
    <div class="tl">${d.moc.map(m=>`<div class="tl-i ${m.loai}">
      <div class="d">${viDate(m.ngay)||'—'}</div><div class="dot"><i></i></div>
      <div><div class="b">${esc(m.tieuDe)}</div>
        ${m.chiTiet?`<div class="c">${esc(m.chiTiet)}</div>`:''}
        ${m.nguoi?`<div class="w">${esc(m.nguoi)}</div>`:''}</div></div>`).join('')
      ||'<div class="empty">Mã này chưa có mốc nào.</div>'}</div>`;
}
async function taiTinhTrang(){
  const q=$('ttQ').value.trim(),tt=window.__tt||'';
  const d=await get(`${window.SLIDE.status}?q=${encodeURIComponent(q)}&tt=${encodeURIComponent(tt)}`);
  $('ttTiles').innerHTML=[['soan','Đã soạn'],['doc','Bác sĩ đã đọc'],['ihc','Đang hóa mô'],
    ['hc','Đang hội chẩn'],['xong','Đã xong']].map(([k,l])=>`<div class="tile ${tt===k?'on':''}"
    onclick="window.__tt='${tt===k?'':k}';taiTinhTrang()"><div class="k">${l}</div><div class="v">${d.dem[k]||0}</div></div>`).join('');
  $('ttBody').innerHTML=d.rows.length?d.rows.map(r=>{const s=TT[r.trangThai]||TT.soan;
    return `<tr><td class="main">${esc(r.code)}</td><td class="ctr">${r.soBlock??'—'}</td><td class="ctr">${r.soTieuBan??'—'}</td>
    <td>${viDate(r.ngaySoan)||'—'}</td><td class="ctr">${esc(r.giaSo)||'—'}</td>
    <td>${esc(r.ktvSoan)||'—'}</td><td>${esc(r.bsDoc)||'—'}</td>
    <td>${esc(r.ketQua)||'<span class="sub">—</span>'}</td>
    <td>${esc(r.benhNhan)||'<span class="sub">—</span>'}${r.khoa?`<div class="sub">${esc(r.khoa)}</div>`:''}</td>
    <td><div class="mk-chips">${r.markers.map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')}</div></td>
    <td><span class="badge b-${s[0]}">${s[1]}</span></td></tr>`}).join('')
    :'<tr><td colspan="11" class="empty">Không có mã nào phù hợp.</td></tr>';
  $('ttCount').textContent=`${d.rows.length}/${d.tong} mã`;
}
function xoaLocTt(){$('ttQ').value='';window.__tt='';taiTinhTrang()}

/* ===== 5. Hội chẩn ===== */
async function taiHc(){
  const d=await get(window.SLIDE.consult+(hcCur?'?code='+encodeURIComponent(hcCur):''));
  hcDs=d.danhSach||[];
  $('hcList').innerHTML=hcDs.length?hcDs.map(c=>`<div class="hc-item ${hcCur===c.code?'on':''}" onclick="chonHc('${c.code}')">
    <div class="c">${esc(c.code)}</div>
    <div class="s">${c.soYKien} ý kiến${c.soAnh?` · ${c.soAnh} ảnh`:''}</div>
    <div class="s"><span class="badge ${c.chot?'b-xong':'b-hc'}">${c.chot?'đã chốt':'đang mở'}</span></div></div>`).join('')
    :'<div class="empty" style="padding:24px">Chưa có phiên hội chẩn nào.</div>';
  veHc(d.phien,d.me);
}
function chonHc(code){hcCur=code;taiHc()}
function veHc(p,me){
  if(!p){$('hcBody').innerHTML='<div class="empty">Chọn một mã bên trái, hoặc mở phiên mới ở trên.</div>';return}
  $('hcBody').innerHTML=`
    <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap">
      <h3 style="margin:0;font-size:15px">${esc(p.code)}</h3>
      <span class="badge ${p.ketLuan?'b-xong':'b-hc'}">${p.ketLuan?'đã chốt':'đang mở'}</span>
      <button class="btn sm" onclick="$('traCode').value='${esc(p.code)}';go('tra');xemTien()">Xem tiến trình mã này</button>
    </div>
    <div class="yk">${p.yKien.length?p.yKien.map(y=>`<div class="yk-i">
      <div class="t"><b>${esc(y.bs)}</b><span class="d">${esc(y.luc)}</span></div>
      <div class="n">${esc(y.noiDung)}</div></div>`).join('')
      :'<div class="hint">Chưa có ý kiến nào.</div>'}</div>
    ${p.ketLuan?`<div class="chot"><h4>Kết luận hội chẩn đã chốt</h4><div class="n">${esc(p.ketLuan)}</div>
        <div class="s">Chốt bởi <b>${esc(p.bsChot)}</b> ngày ${viDate(p.ngayChot)} · ảnh đính kèm đã được dọn</div></div>`
      :`<div class="yk-add">
        <div class="r"><input type="text" id="hcBs" placeholder="Tên bác sĩ" list="bsList" value="${esc(me||'')}" style="width:190px">
          <span class="hint">Nhiều bác sĩ cùng ghi nhận xét cho mã này.</span></div>
        <textarea id="hcNd" placeholder="Nhận xét cho mã tiêu bản này..."></textarea>
        <div class="r"><button class="btn primary" onclick="guiYk()">Gửi nhận xét</button>
          <label class="btn" style="cursor:pointer">📷 Thêm ảnh
            <input type="file" id="hcAnh" accept="image/*" multiple style="display:none" onchange="taiAnh()"></label>
        </div>
        ${p.anh.length?`<div class="imgs">${p.anh.map(a=>`<div class="img-i">
            <img src="${a.url}" alt="${esc(a.ten||'')}" onclick="phongTo('${a.url}')">
            <button class="x" title="Xóa ảnh" onclick="xoaAnh(${a.id})">×</button>
            <div class="cap">${esc(a.ten||'')}</div></div>`).join('')}</div>
          <div class="warn-anh">Có <b>${p.anh.length}</b> ảnh đính kèm. Khi chốt kết luận, toàn bộ ảnh sẽ bị xóa khỏi máy chủ cho nhẹ.</div>`:''}
        <div class="r"><textarea id="hcKl" placeholder="Kết luận cuối cùng của hội chẩn..." style="flex:1"></textarea></div>
        <div class="r"><button class="btn green" onclick="chotHc()">✓ Chốt kết luận cuối</button>
          <span class="hint">Chốt xong sẽ khóa phiên và xóa ảnh.</span></div>
      </div>`}`;
}
async function moHc(){
  const code=$('hcCode').value.trim().toUpperCase();
  if(!code)return toast('Nhập mã tiêu bản',true);
  try{await post(window.SLIDE.cOpen,{code});hcCur=code;$('hcCode').value='';await taiHc();toast('Đã mở phiên hội chẩn '+esc(code))}
  catch(e){toast('Không mở được: '+e.message,true)}
}
async function moHcTu(code){
  try{await post(window.SLIDE.cOpen,{code});hcCur=code;go('hoichan');await taiHc();toast('Đã mở phiên hội chẩn '+esc(code))}
  catch(e){toast('Không mở được: '+e.message,true)}
}
async function guiYk(){
  const bs=$('hcBs').value.trim(),nd=$('hcNd').value.trim();
  if(!bs||!nd)return toast('Nhập tên bác sĩ và nội dung',true);
  try{await post(window.SLIDE.cNote,{code:hcCur,bs,noiDung:nd});await taiHc();toast('Đã ghi nhận xét')}
  catch(e){toast('Không ghi được: '+e.message,true)}
}
async function taiAnh(){
  const inp=$('hcAnh');if(!inp.files.length)return;
  const fd=new FormData();fd.append('code',hcCur);
  [...inp.files].forEach(f=>fd.append('anh[]',f));
  try{
    const r=await fetch(window.SLIDE.cUpload,{method:'POST',credentials:'same-origin',
      headers:{'X-CSRF-TOKEN':window.SLIDE.csrf,'Accept':'application/json'},body:fd});
    const j=await r.json().catch(()=>({}));
    if(!r.ok)throw new Error((j.errors||['HTTP '+r.status]).join(', '));
    await taiHc();toast('Đã tải ảnh lên');
  }catch(e){toast('Tải ảnh thất bại: '+e.message,true)}
}
async function xoaAnh(id){
  if(!confirm('Xóa ảnh này?'))return;
  try{
    const r=await fetch(window.SLIDE.cImgDel.replace('__ID__',id),{method:'DELETE',credentials:'same-origin',headers:H});
    if(!r.ok)throw new Error('HTTP '+r.status);
    await taiHc();toast('Đã xóa ảnh');
  }catch(e){toast('Không xóa được: '+e.message,true)}
}
async function chotHc(){
  const bs=$('hcBs').value.trim(),kl=$('hcKl').value.trim();
  if(!bs||!kl)return toast('Nhập tên bác sĩ chốt và nội dung kết luận',true);
  if(!confirm('Chốt kết luận sẽ khóa phiên và xóa toàn bộ ảnh đính kèm. Tiếp tục?'))return;
  try{
    const j=await post(window.SLIDE.cClose,{code:hcCur,bs,ketLuan:kl});
    await taiHc();toast(`Đã chốt kết luận${j.soAnhDaXoa?` và dọn ${j.soAnhDaXoa} ảnh`:''}`);
  }catch(e){toast('Không chốt được: '+e.message,true)}
}
function phongTo(url){$('lightboxImg').src=url;$('lightbox').classList.add('show')}

/* ===== khởi động ===== */
function dongM(id){$(id).classList.remove('show')}
$('prefix').addEventListener('change',()=>{sel=new Set();$('gridScroll').scrollTop=0;taiSoan()});
$('onlyFilled').addEventListener('change',()=>{$('gridScroll').scrollTop=0;veSoan(true)});
$('gridScroll').addEventListener('scroll',onCuon);
$('jump').addEventListener('change',e=>{
  const n=raSeq(e.target.value);
  if(!n||n<1||n>9999)return toast('Mã không hợp lệ',true);
  if($('onlyFilled').checked)$('onlyFilled').checked=false;
  nhayToi(n);
});
$('ttQ').addEventListener('input',()=>{clearTimeout(window.__tq);window.__tq=setTimeout(taiTinhTrang,320)});
$('hmTt').addEventListener('change',taiHmmd);
$('mkMaBn').addEventListener('change',traBn);
document.querySelectorAll('.mbg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('show')}));
window.addEventListener('beforeunload',e=>{if(dirty.size){e.preventDefault();e.returnValue=''}});
window.addEventListener('resize',()=>veSoan(true));
taiSoan().catch(e=>alert('Lỗi tải dữ liệu: '+e.message));
</script>
</body>
</html>
