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
 --purple:#6d28d9;--purple-soft:#f3eeff;--rowh:32px;--phrowh:34px}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:13px/1.5 Inter,"Segoe UI",system-ui,sans-serif}
button,input,select,textarea{font:inherit}button{cursor:pointer}
.shell{padding:11px 16px 14px;max-width:1760px}
.breadcrumb{font-size:10.5px;color:var(--muted);margin-bottom:2px}.breadcrumb b{color:var(--text)}
.head{display:flex;align-items:baseline;gap:12px;margin-bottom:7px;flex-wrap:wrap}
.head h1{font-size:17px;margin:0;line-height:1.25}
.head p{margin:0;color:var(--muted);font-size:10.5px}
.head .right{margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
/* hướng dẫn dài gập lại, mở ra khi cần — khỏi ăn chỗ của bảng mỗi lần vào trang */
.hd{font-size:10.5px;color:var(--muted)}
.hd summary{cursor:pointer;color:var(--primary);font-weight:800;list-style:none}
.hd summary::-webkit-details-marker{display:none}
.hd summary:before{content:"？ ";font-weight:700}
.hd[open] summary:before{content:"× "}
.hd .n{background:#fff;border:1px solid var(--line);border-radius:11px;padding:9px 12px;margin-top:6px;line-height:1.65;max-width:1000px}
.btn{height:32px;padding:0 12px;border:1px solid var(--line);background:#fff;color:var(--text);border-radius:9px;
 font-size:11px;font-weight:750;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none}
.btn:hover{border-color:#bdc8d4}.btn.primary{background:var(--primary);border-color:var(--primary);color:#fff}
.btn.primary:hover{background:var(--primary2)}.btn:disabled{opacity:.45;cursor:not-allowed}
.btn.green{background:var(--green);border-color:var(--green);color:#fff}
.btn.red{background:#fff;color:var(--red);border-color:#f1c7c3}
.btn.sm{height:27px;padding:0 9px;font-size:10px;border-radius:7px}
.tabs{display:flex;gap:5px;background:#e8eef4;padding:3px;border-radius:11px;margin-bottom:8px;flex-wrap:wrap}
.tab{height:29px;padding:0 12px;border:0;background:transparent;border-radius:8px;font-size:11.5px;font-weight:800;color:#5b6b7d;
 display:inline-flex;align-items:center;gap:6px}
.tab.on{background:#fff;box-shadow:0 2px 7px rgba(15,23,42,.1);color:var(--primary)}
.view{display:none}.view.on{display:block}
.panel{background:#fff;border:1px solid var(--line);border-radius:13px;overflow:hidden;margin-bottom:9px}
.pbar{padding:7px 10px;border-bottom:1px solid var(--line);background:#fbfcfd;display:flex;gap:7px;flex-wrap:wrap;align-items:center}
.pbar h2{font-size:12.5px;margin:0}.pbar .push{margin-left:auto}
.pbar input,.pbar select{height:29px;border:1px solid var(--line);border-radius:8px;padding:0 9px;font-size:10.5px;background:#fff}
.hint{font-size:9.5px;color:var(--muted)}
/* nhãn nằm cạnh ô nhập cho thấp, thay vì xếp trên làm thanh công cụ cao gấp đôi */
.lbl{font-size:9px;font-weight:800;color:#475569;display:flex;align-items:center;gap:5px;white-space:nowrap}
.tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(126px,1fr));gap:7px;margin-bottom:9px}
.tile{background:#fff;border:1px solid var(--line);border-radius:11px;padding:7px 10px;cursor:pointer;
 display:flex;align-items:baseline;gap:7px}
.tile.on{border-color:var(--primary);box-shadow:0 0 0 2px #d3eaee}
.tile .k{font-size:8.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;font-weight:800;line-height:1.3}
.tile .v{font-size:16px;font-weight:800;line-height:1.15;margin-left:auto;font-variant-numeric:tabular-nums}
/* ô tra tiến trình lúc chưa nhập mã thì đừng chiếm cả khoảng trống to */
#traBox .empty{padding:12px}
.twrap{overflow:auto;max-height:calc(100vh - 250px)}
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
.b-late{background:var(--red-soft);color:var(--red)}
/* lưới sổ soạn — chỉ đọc, dữ liệu vào sổ qua popup khởi tạo phiên */
.grid-t{min-width:1010px;table-layout:fixed}
.grid-t td{padding:5px 9px;height:var(--rowh)}   /* chiều cao cố định để cuộn ảo tính đúng vị trí */
.grid-t tr.spacer td{padding:0;border:0;background:#fff}
#gridScroll{max-height:calc(100vh - 218px);max-height:calc(100dvh - 218px)}
/* hai cột đầu ghim lại để kéo ngang vẫn thấy mã tiêu bản */
.grid-t .chk{width:32px;text-align:center;padding:0;position:sticky;left:0;z-index:2}
.grid-t .chk input{width:14px;height:14px;accent-color:var(--primary)}
.grid-t .code{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap;
 position:sticky;left:32px;z-index:2;box-shadow:1px 0 0 var(--line2)}
.grid-t th.chk,.grid-t th.code{z-index:5}
.grid-t td.cell{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* mã đã đủ thông tin thì khóa lại, muốn đổi phải qua nút Sửa */
.grid-t td.code.xong{background:#e9eef4;color:#5b6b7d}
/* ghi chú luôn gõ được, kể cả mã chưa ra block / tiêu bản — để ghi lý do */
.grid-t td.note{padding:3px 6px}
/* ô nhập phải thấp hơn dòng, không thì dòng cao thêm 1px và cuộn ảo trôi dần */
.grid-t td.note input{width:100%;height:calc(var(--rowh) - 10px);border:1px solid transparent;border-radius:6px;
 padding:0 7px;font-size:10.5px;background:transparent;color:var(--text)}
.grid-t td.note input:hover{border-color:var(--line);background:#fff}
.grid-t td.note input:focus{border-color:var(--primary);background:#fff;outline:2px solid #d6ecef;position:relative;z-index:3}
.grid-t td.note input::placeholder{color:#a9b4c2}
.grid-t .act{width:78px;text-align:center;padding:0}
/* nút phải thấp hơn chiều cao dòng, không thì cuộn ảo tính lệch vị trí */
.grid-t .act .btn{height:22px;padding:0 8px;font-size:9.5px;border-radius:6px}
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
.pbar h2 .so{display:inline-flex;align-items:center;justify-content:center;width:17px;height:17px;border-radius:50%;
 background:var(--primary);color:#fff;font-size:10px;margin-right:5px;vertical-align:1px}
.rack.xong{border-color:#bfe0d0;background:var(--green-soft)}
.rack .chot{margin-top:6px}
.rack.pick{border-color:var(--primary);background:var(--soft);box-shadow:0 0 0 2px #cfe8ec}
.rack{position:relative}
.rack .tick{position:absolute;right:9px;top:9px;display:flex;padding:3px}
.rack .tick input{width:16px;height:16px;accent-color:var(--primary);cursor:pointer}
.rack .no{font-size:19px;font-weight:800;line-height:1.1}.rack .no small{font-size:9.5px;color:var(--muted);margin-left:3px}
.rack .m{font-size:9.5px;color:var(--muted)}
.rack .st{display:flex;gap:4px;flex-wrap:wrap;margin-top:3px}
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
.hc-list{border-right:1px solid var(--line);max-height:calc(100vh - 250px);overflow:auto}
.hc-item{padding:10px 12px;border-bottom:1px solid var(--line2);cursor:pointer}
.hc-item:hover{background:#fbfdfe}.hc-item.on{background:var(--soft);box-shadow:inset 3px 0 0 var(--primary)}
.hc-item .c{font-size:11.5px;font-weight:800}.hc-item .s{font-size:9px;color:var(--muted);margin-top:2px}
.hc-body{padding:14px 16px;max-height:calc(100vh - 250px);overflow:auto}
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
/* popup khởi tạo phiên soạn */
.steps{display:flex;gap:8px;margin-bottom:13px}
.step{flex:1;border:1px solid var(--line);border-radius:11px;padding:8px 11px;background:#fbfcfd}
.step.on{border-color:var(--primary);background:var(--soft)}
.step .k{font-size:8.5px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.step .t{font-size:11px;font-weight:800;margin-top:2px}.step.on .t{color:var(--primary2)}
.ph-wrap{max-height:46vh;overflow:auto;border:1px solid var(--line);border-radius:11px}
/* danh sách mã chưa soạn dài cả nghìn dòng nên phải cuộn ảo — dòng bắt buộc cao đúng --phrowh */
#phWrap{height:46vh;max-height:none;overflow:auto}
.ph-t td{padding:3px 8px;height:var(--phrowh)}
.ph-t tr.sp td{padding:0;border:0;height:auto}
.ph-t tr.gap td{background:#fffaf5}
.ph-t td.ma{font-weight:800;font-variant-numeric:tabular-nums;font-size:11.5px;white-space:nowrap}
.ph-t input{width:100%;height:calc(var(--phrowh) - 10px);border:1px solid var(--line);border-radius:7px;padding:0 8px;font-size:11px;background:#fff}
.ph-t input:focus{border-color:var(--primary);outline:2px solid #d6ecef}
.ph-t .del{width:26px;height:26px;border:0;border-radius:7px;background:#f1f5f9;color:var(--red);font-size:13px;line-height:1}
.cho-t tr.qua td{background:var(--red-soft)}
.cho-t tr.qua td.ma{color:var(--red)}
.btn.red{font-weight:800}
.ph-sum{background:var(--soft);border:1px solid #cbe6ea;border-radius:11px;padding:10px 12px;margin-bottom:12px;font-size:11px;line-height:1.7}
.ph-sum b{font-size:12px}
.ph-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:9px}
.ph-err{color:var(--red);font-size:10px;font-weight:800;margin-right:auto;align-self:center}
/* chi tiết một lượt đã hoàn tất */
.ls-sec{border-top:1px solid var(--line2);margin-top:9px;padding-top:9px}
.ls-sec h4{margin:0 0 5px;font-size:9px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}
.ls-kv{font-size:11px;line-height:1.65}
.dan-box{border:1px dashed #c5dde2;background:#f8fbfc;border-radius:11px;padding:11px 12px;margin-bottom:11px}
.dan-box textarea{width:100%;min-height:150px;border:1px solid var(--line);border-radius:8px;padding:8px 9px;
 font-family:ui-monospace,Consolas,monospace;font-size:10.5px;background:#fff;resize:vertical}
.dan-box textarea:focus{border-color:var(--primary);outline:2px solid #d6ecef}
#lightbox{position:fixed;inset:0;background:rgba(9,16,24,.9);display:none;align-items:center;justify-content:center;z-index:140;padding:20px}
#lightbox.show{display:flex}#lightbox img{max-width:100%;max-height:100%;border-radius:8px}
.toast-wrap{position:fixed;right:18px;bottom:18px;display:grid;gap:8px;z-index:150}
.toast{background:#18333d;color:#fff;padding:10px 13px;border-radius:9px;font-size:10.5px;max-width:340px}
.toast.err{background:#8a1f16}
/* nút mở menu là nút nổi ở góc trái — chừa chỗ cho nó, không thì đè lên tiêu đề */
@media(max-width:980px){.breadcrumb,.head{padding-left:44px}}
@media(max-width:1250px){.tiles{grid-template-columns:repeat(3,1fr)}.hc-wrap{grid-template-columns:1fr}
 .hc-list{border-right:0;border-bottom:1px solid var(--line);max-height:210px}}
@media(max-width:700px){.shell{padding:10px 10px 16px}.tiles{grid-template-columns:repeat(2,1fr)}
 .head{flex-wrap:wrap}.f3,.f2{grid-template-columns:1fr}
 /* màn hẹp: mô tả đã nằm trong Hướng dẫn, và hàng tab kéo ngang thay vì xuống 3 dòng */
 .head p,.head .right{display:none}
 .tabs{flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none}
 .tabs::-webkit-scrollbar{display:none}.tab{flex:0 0 auto}
 /* iOS tự phóng to trang khi focus ô có cỡ chữ < 16px — phóng xong lại co về, nhìn như nháy.
    Ép 16px cho mọi ô nhập trên điện thoại để Safari không zoom. */
 :root{--rowh:38px}
 .ph-t input,.f input,.f select,.f textarea,.pbar input,.pbar select,
 .bulk input,.yk-add input,.yk-add textarea,.dan-box textarea,.grid-t td.note input{font-size:16px}
 #gridScroll{max-height:calc(100dvh - 240px)}
 .grid-t td.code{font-size:12px}
 .steps{flex-direction:column}
 /* điện thoại: để cả popup cuộn một mạch, tránh cuộn lồng trong bảng mã */
 #choModal .ph-wrap{max-height:none;overflow:visible}
 :root{--phrowh:44px}#phWrap{height:52vh}}
</style>
<link rel="stylesheet" href="{{ asset('css/qms-shell.css') }}?v=9">
<script>window.SLIDE={
  state:"{{ route('slide.state') }}", save:"{{ route('slide.save') }}",
  phienMa:"{{ route('slide.session.ma') }}", phienLuu:"{{ route('slide.session.save') }}",
  cho:"{{ route('slide.pending') }}", choLuu:"{{ route('slide.pending.save') }}",
  reader:"{{ route('slide.reader') }}", mark:"{{ route('slide.mark') }}", take:"{{ route('slide.take') }}",
  chotGia:"{{ route('slide.finish.rack') }}", hcXong:"{{ route('slide.consult.done') }}",
  status:"{{ route('slide.status') }}", trace:"{{ route('slide.trace') }}",
  finish:"{{ route('slide.finish') }}", history:"{{ route('slide.history') }}",
  historyExport:"{{ route('slide.history.export') }}",
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
    <h1>Sổ soạn tiêu bản &amp; hóa mô miễn dịch</h1>
    <p>Mã 26C2472 = 2 số cuối năm + chữ cái + 4 chữ số · sổ ghi theo phiên · ngày soạn lấy theo ngày lưu phiên.</p>
    <div class="right"><span class="hint" id="whoAmI"></span></div>
  </div>
  <details class="hd" id="hdBox"><summary>Hướng dẫn</summary>
    <div class="n">
      <b>Sổ soạn</b> — bấm <b>＋ Khởi tạo phiên soạn</b> để nhập số block / số tiêu bản cho các mã còn trống, rồi gán
      giá và kỹ thuật viên; hoặc <b>📋 Dán từ Excel</b> nếu có sẵn danh sách. Lưới chỉ đọc: mã đã đủ thông tin thì nền
      mã chuyển xám và bấm được nút <b>✎ Sửa</b>. Riêng cột <b>Ghi chú</b> luôn gõ thẳng trên lưới, kể cả mã chưa ra
      block / tiêu bản — dùng ô <b>Nhảy tới mã</b> để mở đúng dòng rồi ghi lý do.<br>
      <b>Bác sĩ đọc theo giá</b> — tích chọn giá rồi <b>✋ Nhận</b>, cả giá tự gán tên bác sĩ và chuyển sang
      <i>đã nhận</i>. Mã đi qua ba trạng thái: chưa đọc → đã nhận → đã đọc. Nhập kết quả xong bấm
      <b>🏁 Hoàn tất</b> để chốt ca vào lịch sử và trả mã về sổ soạn dùng lại.
    </div>
  </details>

  <div class="tabs">
    <button class="tab on" data-v="soan" onclick="go('soan')">🔬 Sổ soạn tiêu bản</button>
    <button class="tab" data-v="doc" onclick="go('doc')">🩺 Bác sĩ đọc theo giá</button>
    <button class="tab" data-v="hmmd" onclick="go('hmmd')">🧬 Hóa mô miễn dịch</button>
    <button class="tab" data-v="tra" onclick="go('tra')">🔎 Tra cứu &amp; tiến trình</button>
    <button class="tab" data-v="hoichan" onclick="go('hoichan')">👥 Hội chẩn</button>
    <button class="tab" data-v="hcxong" onclick="go('hcxong')">🤝 Kết quả hội chẩn</button>
  </div>

  <!-- ===== 1. Sổ soạn ===== -->
  <section class="view on" id="v-soan">
    <div class="panel">
      <div class="pbar">
        <label class="lbl">Đầu mã<select id="prefix" style="width:150px"></select></label>
        <label class="lbl">Nhảy tới mã<input id="jump" placeholder="VD: 2472" style="width:110px"></label>
        <label class="lbl" style="align-self:end"><span style="display:flex;gap:5px;align-items:center;height:31px">
          <input type="checkbox" id="onlyFilled" checked style="width:14px;height:14px;accent-color:var(--primary)">
          <span class="hint" style="font-weight:800">Chỉ hiện dòng đã nhập</span></span></label>
        <span class="push"></span>
        <button class="btn sm" id="btnCho" onclick="moCho()">⏳ Chờ xử lý</button>
        <button class="btn sm" onclick="moPhien(true)">📋 Dán từ Excel</button>
        <a class="btn sm" id="btnXuat" href="#">⇩ Xuất Excel</a>
        <span class="saveState" id="saveState"></span>
        <button class="btn sm" id="btnSave" onclick="luuNgay()" style="display:none">Lưu</button>
        <button class="btn sm primary" onclick="moPhien()">＋ Khởi tạo phiên soạn</button>
      </div>
      <div class="bulk off" id="bulkBar">
        <b id="bulkCount"></b>
        <label class="lbl">Giá số<input id="bGia" list="giaList" style="width:80px"></label>
        <label class="lbl">KTV cắt<input id="bCat" list="ktvList" style="width:130px"></label>
        <label class="lbl">KTV soạn<input id="bSoan" list="ktvList" style="width:130px"></label>
        <label class="lbl">BS đọc<input id="bBs" list="bsList" style="width:130px"></label>
        <button class="btn sm primary" onclick="apBulk()">Sửa cho dòng đã chọn</button>
        <button class="btn sm" onclick="boChon()">Bỏ chọn</button>
        <span class="hint">Dùng khi cần sửa lại giá / KTV của phiên đã lưu.</span>
      </div>
      <div class="twrap" id="gridScroll"><table class="grid-t">
        <!-- cột số cho hẹp, phần rộng dồn cho tên người và ghi chú -->
        <colgroup><col style="width:32px"><col style="width:92px"><col style="width:70px"><col style="width:78px">
          <col style="width:92px"><col style="width:56px"><col style="width:124px"><col style="width:124px">
          <col style="width:124px"><col><col style="width:74px"></colgroup>
        <thead><tr>
        <th class="chk"><input type="checkbox" id="chkAll" onclick="chonHet(this.checked)"></th>
        <th class="code">Mã tiêu bản</th><th>Số block</th><th>Số tiêu bản</th><th>Ngày soạn</th><th>Giá</th>
        <th>KTV cắt</th><th>KTV soạn</th><th>BS đọc</th><th>Ghi chú</th><th class="act">Thao tác</th>
      </tr></thead><tbody id="soanBody"></tbody></table></div>
      <div class="pbar"><span class="hint" id="soanFoot"></span></div>
    </div>
  </section>

  <!-- ===== 2. Bác sĩ đọc theo giá ===== -->
  <section class="view" id="v-doc">
    <!-- phần 1: giá kỹ thuật viên đã soạn, bác sĩ chưa nhận -->
    <div class="panel">
      <div class="pbar"><h2><span class="so">1</span> Giá đã soạn</h2>
        <label class="lbl">Bác sĩ đọc<input id="bsNhan" list="bsList" placeholder="Tên bác sĩ" style="width:170px"></label>
        <button class="btn sm primary" onclick="nhanGia()">✋ Nhận các giá đã chọn</button>
        <button class="btn sm" onclick="boChonGia()">Bỏ chọn</button>
        <span class="push"></span>
        <input id="giaGo" list="giaList" placeholder="Gõ số giá rồi Enter" style="width:150px"
               onkeydown="if(event.key==='Enter'){event.preventDefault();moGiaGo()}">
        <button class="btn sm" onclick="moGiaGo()">Mở giá</button>
      </div>
      <div class="racks" id="racks1"></div>
    </div>

    <!-- phần 2: giá bác sĩ đã nhận, đang đọc; đọc xong chốt là giá rời khỏi đây -->
    <div class="panel">
      <div class="pbar"><h2><span class="so">2</span> Giá đã nhận</h2>
        <span class="hint">Bác sĩ bấm <b>✓ Đã đọc xong</b> ở khối 3 là mã được chốt vào lịch sử và giá rời khỏi đây.</span>
      </div>
      <div class="racks" id="racks2"></div>
    </div>
    <!-- phần 3: chi tiết từng mã của giá đang mở -->
    <div class="panel" id="docPanel" style="display:none">
      <div class="pbar">
        <h2><span class="so">3</span> <span id="docTitle"></span></h2>
        <span class="push"></span>
        <label class="lbl">BS đọc<input id="bsAll" list="bsList" placeholder="Tên bác sĩ" style="width:150px"></label>
        <button class="btn sm" onclick="doiTt('nhan')">Đã nhận</button>
        <button class="btn sm green" onclick="docXong()">✓ Đã đọc xong</button>
        <button class="btn sm" onclick="doiTt('chua')">↩ Chưa đọc</button>
        <button class="btn sm" onclick="hoanTat()">🏁 Chốt mã còn vướng</button>
      </div>
      <div class="twrap"><table><thead><tr>
        <th class="chk"><input type="checkbox" id="chkAllDoc" onclick="chonHetDoc(this.checked)"></th>
        <th>Mã tiêu bản</th><th class="ctr">Block</th><th class="ctr">Lam</th>
        <th style="min-width:280px">Kết quả / ghi chú (không bắt buộc)</th><th>BS đọc</th>
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

    <div class="panel">
      <div class="pbar"><h2>Lịch sử mã đã hoàn tất</h2>
        <span class="hint">Ca đã đọc xong và được chốt — mã tiêu bản đã trả về sổ soạn để dùng lại.</span>
        <span class="push"></span>
        <input id="lsQ" placeholder="Tìm mã, bệnh nhân, giá, bác sĩ, kết quả, ghi chú..." style="width:260px">
        <a class="btn sm" id="lsXuat" href="{{ route('slide.history.export') }}">⇩ Xuất Excel</a>
        <span class="hint" id="lsCount"></span>
      </div>
      <div class="twrap"><table><thead><tr>
        <th>Mã tiêu bản</th><th class="ctr">Lượt</th><th class="ctr">Block</th><th class="ctr">Lam</th>
        <th>Ngày soạn</th><th class="ctr">Giá</th><th>KTV soạn</th><th>BS đọc</th>
        <th style="min-width:260px">Kết quả / đánh giá</th><th>Bệnh nhân</th><th>Marker HMMD</th>
        <th>Hoàn tất</th><th></th>
      </tr></thead><tbody id="lsBody"></tbody></table></div>
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
        <th>KTV soạn</th><th>BS đọc</th><th>Kết quả / tình trạng</th><th>Bệnh nhân (HMMD)</th><th>Marker</th>
        <th>Hội chẩn</th><th>Tình trạng</th>
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

  <!-- ===== 6. Kết quả hội chẩn đã thống nhất ===== -->
  <section class="view" id="v-hcxong">
    <div class="panel">
      <div class="pbar"><h2>Ca đã thống nhất kết quả hội chẩn</h2>
        <span class="hint">Gồm cả ca đã hoàn tất và trả mã về sổ soạn.</span>
        <span class="push"></span>
        <input id="hxQ" placeholder="Tìm mã, kết luận, bác sĩ, giá..." style="width:250px">
        <span class="hint" id="hxCount"></span>
      </div>
      <div class="twrap"><table><thead><tr>
        <th>Mã tiêu bản</th><th class="ctr">Giá</th><th>Ngày soạn</th><th>Ngày thống nhất</th>
        <th>Người chốt</th><th class="ctr">Ý kiến</th><th style="min-width:320px">Kết luận hội chẩn</th>
        <th class="ctr">Trong sổ</th><th></th>
      </tr></thead><tbody id="hxBody"></tbody></table></div>
    </div>
  </section>

<datalist id="ktvList"></datalist>
<datalist id="giaList"></datalist>
<datalist id="bsList"></datalist>

<!-- popup mã bị phiên soạn nhảy qua, đang chờ xử lý thêm -->
<div class="mbg" id="choModal"><div class="mdl" style="width:min(880px,100%)">
  <div class="mdl-h"><div><h2>Mã đang chờ xử lý</h2>
    <p>Mã nằm giữa dải đã soạn nhưng chưa ra tiêu bản — hệ thống tự dò, không cần đánh dấu.
       Ghi lý do và ngày hẹn để còn đòi kíp xử lý.</p></div>
    <button class="x" onclick="dongM('choModal')">×</button></div>
  <div class="mdl-b">
    <div class="ph-wrap" style="max-height:52vh"><table class="ph-t cho-t"><thead><tr>
      <th style="width:96px">Mã tiêu bản</th><th>Lý do chờ</th>
      <th style="width:130px">Hẹn ra tiêu bản</th><th style="width:120px">Đã chờ</th><th style="width:86px"></th>
    </tr></thead><tbody id="choBody"></tbody></table></div>
    <div class="hint" style="margin-top:8px">Mã nào soạn xong thì tự biến khỏi danh sách này.
      Xóa trắng cả lý do lẫn ngày hẹn thì mã trở lại trống trơn.</div>
  </div>
  <div class="mdl-f"><span class="ph-err" id="choKq"></span>
    <button class="btn" onclick="dongM('choModal')">Đóng</button></div>
</div></div>

<!-- popup xem lại toàn bộ một lượt đã hoàn tất -->
<div class="mbg" id="lsModal"><div class="mdl" style="width:min(720px,100%)">
  <div class="mdl-h"><div><h2>Chi tiết ca đã hoàn tất</h2><p id="lsSub"></p></div>
    <button class="x" onclick="dongM('lsModal')">×</button></div>
  <div class="mdl-b" id="lsBox"></div>
  <div class="mdl-f"><button class="btn" onclick="dongM('lsModal')">Đóng</button></div>
</div></div>

<!-- popup sửa một mã tiêu bản — chỉ mở được khi mã đã đủ thông tin -->
<div class="mbg" id="suaModal"><div class="mdl" style="width:min(680px,100%)">
  <div class="mdl-h"><div><h2 id="suaTitle">Sửa mã tiêu bản</h2><p id="suaSub"></p></div>
    <button class="x" onclick="dongM('suaModal')">×</button></div>
  <div class="mdl-b">
    <div class="f3">
      <div class="f"><label>Số block</label><input id="suBlock" type="number" min="0" inputmode="numeric"></div>
      <div class="f"><label>Số tiêu bản</label><input id="suLam" type="number" min="0" inputmode="numeric"></div>
      <div class="f"><label>Ngày soạn</label><input id="suNgay" type="date"></div>
    </div>
    <div class="f3">
      <div class="f"><label>Giá số</label><input id="suGia" list="giaList"></div>
      <div class="f"><label>KTV cắt</label><input id="suCat" list="ktvList"></div>
      <div class="f"><label>KTV soạn</label><input id="suSoan" list="ktvList"></div>
    </div>
    <div class="f2">
      <div class="f"><label>BS đọc</label><input id="suBs" list="bsList"></div>
      <div class="f"><label>Ghi chú</label><input id="suGhiChu"></div>
    </div>
    <div class="hint">Kết quả đọc và trạng thái đã đọc sửa ở tab <b>Bác sĩ đọc theo giá</b>.
      Xóa trắng hết các ô sẽ gỡ mã khỏi sổ, trừ khi mã đó đã có hóa mô miễn dịch hoặc hội chẩn.</div>
  </div>
  <div class="mdl-f"><span class="ph-err" id="suErr"></span>
    <button class="btn" onclick="dongM('suaModal')">Hủy</button>
    <button class="btn primary" onclick="luuSua()">Lưu thay đổi</button></div>
</div></div>

<!-- popup khởi tạo phiên soạn: bước 1 số block/số tiêu bản, bước 2 giá + KTV -->
<div class="mbg" id="phienModal"><div class="mdl" style="width:min(900px,100%)">
  <div class="mdl-h"><div><h2 id="phTitle">Khởi tạo phiên soạn</h2><p id="phSub"></p></div>
    <button class="x" onclick="dongM('phienModal')">×</button></div>
  <div class="mdl-b">
    <div class="steps">
      <div class="step on" id="phStep1"><div class="k">Giai đoạn 1</div><div class="t">Số block &amp; số tiêu bản</div></div>
      <div class="step" id="phStep2"><div class="k">Giai đoạn 2</div><div class="t">Giá và kỹ thuật viên</div></div>
    </div>

    <!-- ---- giai đoạn 1 ---- -->
    <div id="phB1">
      <div class="ph-row" style="margin:0 0 9px">
        <label class="lbl">Đi đến mã<input id="phTu" placeholder="VD: 2472" style="width:130px;height:29px;
          border:1px solid var(--line);border-radius:8px;padding:0 9px;font-size:10.5px"></label>
        <button class="btn sm" onclick="diDenMa()">↳ Đi đến</button>
        <button class="btn sm" onclick="boDongTrong()">Bỏ các dòng chưa nhập</button>
        <button class="btn sm" id="btnDan" onclick="moKhungDan()">📋 Dán từ Excel</button>
        <span class="hint" id="phDem"></span>
      </div>
      <div class="dan-box" id="phDan" style="display:none">
        <div class="f2">
          <div class="f"><label>Cột đầu tiên trong vùng dán là</label>
            <select id="danMode" onchange="veDanGoiY()">
              <option value="ma">Mã tiêu bản (26C2472) hoặc số thứ tự (2472)</option>
              <option value="seq">Số block — điền liên tiếp từ một mã</option>
            </select></div>
          <div class="f" id="danStartWrap" style="display:none"><label>Bắt đầu từ mã</label>
            <input id="danStart" placeholder="VD: 2472 hoặc 26C2472"></div>
        </div>
        <div class="f"><label>Thứ tự cột</label><div class="hint" id="danCols"></div></div>
        <textarea id="danBox"></textarea>
        <div class="ph-row">
          <button class="btn sm primary" onclick="danVaoPhien()">Đưa vào phiên</button>
          <button class="btn sm" onclick="dongKhungDan()">Đóng khung dán</button>
          <span class="hint" id="danKq"></span>
        </div>
      </div>

      <div class="ph-wrap" id="phWrap"><table class="ph-t"><thead><tr>
        <th>Mã tiêu bản</th><th style="width:170px">Số block</th><th style="width:170px">Số tiêu bản</th><th style="width:46px"></th>
      </tr></thead><tbody id="phBody"></tbody></table></div>
      <div class="hint" style="margin-top:7px" id="phGiaiThich"></div>
    </div>

    <!-- ---- giai đoạn 2 ---- -->
    <div id="phB2" style="display:none">
      <div class="ph-sum" id="phSum"></div>
      <div class="f3">
        <div class="f"><label>Giá số *</label><input id="phGia" list="giaList" placeholder="VD: 20"></div>
        <div class="f"><label>KTV cắt</label><input id="phCat" list="ktvList" placeholder="Tên kỹ thuật viên"></div>
        <div class="f"><label>KTV soạn *</label><input id="phSoan" list="ktvList" placeholder="Tên kỹ thuật viên"></div>
      </div>
      <div class="hint" style="margin-bottom:6px">Giá đang dùng gần đây — bấm để chọn nhanh:</div>
      <div class="panels" id="phGiaNhanh"></div>
      <div class="f"><label>Ghi chú chung cho cả phiên (tùy chọn)</label><input id="phGhiChu"></div>
      <div class="hint">Ngày soạn ghi tự động là <b id="phNgay"></b>.</div>
    </div>
  </div>
  <div class="mdl-f">
    <span class="ph-err" id="phErr"></span>
    <button class="btn" id="phBack" onclick="phQuayLai()" style="display:none">← Quay lại</button>
    <button class="btn" onclick="dongM('phienModal')">Hủy</button>
    <button class="btn primary" id="phNext" onclick="phTiep()">Tiếp tục →</button>
  </div>
</div></div>

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
const TT={chuaSoan:['off','Chưa ra tiêu bản'],soan:['soan','Đã soạn'],nhan:['hc','BS đã nhận'],
  doc:['doc','Bác sĩ đã đọc'],ihc:['ihc','Đang hóa mô'],hc:['hc','Đang hội chẩn'],xong:['xong','Đã xong']};

let D={rows:{},from:1,to:250,prefix:'',dauMa:[],me:'',ktv:[]};
let dirty=new Set(), sel=new Set(), lastIdx=-1, saveTimer=null;
let docRows=[], docSel=new Set(), curGia='', docGia=[], giaSel=new Set();
let MK=[], mkPick=[], mkEditId=null, LS=[];
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
  if(v==='hmmd'){taiHmmd();taiLichSu()}
  if(v==='tra')taiTinhTrang();
  if(v==='hoichan')taiHc();
  if(v==='hcxong')taiHcXong();
}

/* ===== 1. Sổ soạn ===== */
async function taiSoan(){
  const p=$('prefix').value||'';
  D=await get(`${window.SLIDE.state}?prefix=${encodeURIComponent(p)}`);
  $('whoAmI').textContent=D.me?('Đang đăng nhập: '+D.me):'';
  $('ktvList').innerHTML=(D.ktv||[]).map(x=>`<option value="${esc(x)}">`).join('');
  $('bsList').innerHTML=(D.ktv||[]).map(x=>`<option value="${esc(x)}">`).join('');
  veGiaList();
  $('btnXuat').href=window.SLIDE.export+'?prefix='+encodeURIComponent(D.prefix);
  veDauMa();veSoan(true);
  demCho();                                      // đếm mã bị bỏ lại, hiện ngay trên nút
}
/** Gợi ý giá lấy từ chính các mã đã nhập — gõ giá mới vẫn được, đây chỉ là gợi ý. */
function veGiaList(){
  const gs=[...new Set(Object.values(D.rows||{}).map(r=>String(r.giaSo||'').trim()).filter(Boolean))]
    .sort((a,b)=>(Number(a)||1e9)-(Number(b)||1e9)||a.localeCompare(b));
  $('giaList').innerHTML=gs.map(g=>`<option value="${esc(g)}">`).join('');
}
function veDauMa(){
  const cur=$('prefix').value||D.prefix;
  $('prefix').innerHTML=D.dauMa.map(x=>`<option value="${x.prefix}" ${x.prefix===cur?'selected':''}>
    ${x.prefix}${x.n?` — ${x.n} mã`:' — sổ trống'}</option>`).join('');
}
/* ---- Cuộn ảo: sổ trải đủ 9999 dòng nhưng chỉ dựng những dòng đang nhìn thấy ---- */
/* Chiều cao dòng đọc thẳng từ CSS: điện thoại dùng dòng cao hơn cho dễ bấm,
   nếu hằng số trong JS lệch với CSS thì cuộn ảo sẽ tính sai vị trí. */
let ROW_H=32;
function doCaoDong(){
  const v=parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--rowh'));
  if(v>0)ROW_H=v;
}
let vStart=-1,vEnd=-1;
/** Con trỏ đang nằm trong ô ghi chú của lưới? Lúc đó đừng dựng lại tbody, sẽ mất chỗ đang gõ. */
function dangGo(){
  const a=document.activeElement;
  return !!(a&&a.dataset&&a.dataset.seq&&a.closest&&a.closest('#soanBody'));
}
function luuTieuDiem(){
  const a=document.activeElement;
  if(!a||!a.dataset||!a.dataset.seq)return null;
  let s=null,e=null;
  try{s=a.selectionStart;e=a.selectionEnd}catch(_){}
  return {seq:a.dataset.seq,f:a.dataset.f,s,e};
}
function traTieuDiem(t){
  if(!t)return;
  const el=document.querySelector(`#soanBody input[data-seq="${t.seq}"][data-f="${t.f}"]`);
  if(!el)return;
  el.focus({preventScroll:true});
  if(t.s!=null){try{el.setSelectionRange(t.s,t.e)}catch(_){}}
}
function dsSeq(){
  return $('onlyFilled').checked
    ? Object.keys(D.rows).map(Number).sort((a,b)=>a-b)
    : Array.from({length:D.soDong||9999},(_,i)=>i+1);
}
/* Mã coi là đủ thông tin khi có hết các cột trừ ghi chú — lúc đó mới cho bấm Sửa. */
const DU_COT=['soBlock','soTieuBan','ngaySoan','giaSo','ktvCat','ktvSoan','bsDoc'];
const duDl=r=>!!r&&DU_COT.every(f=>String(r[f]??'').trim()!=='');
function veSoan(epLai){
  const list=dsSeq(), sc=$('gridScroll');
  const vh=sc.clientHeight||620;
  const start=Math.max(0,Math.floor(sc.scrollTop/ROW_H)-6);
  const end=Math.min(list.length,start+Math.ceil(vh/ROW_H)+12);
  if(!epLai&&start===vStart&&end===vEnd)return;   // cùng khung nhìn thì khỏi dựng lại
  if(dangGo()&&!epLai)return;                     // đang gõ ghi chú thì hoãn, tránh mất ô đang nhập
  const tieuDiem=luuTieuDiem();
  vStart=start;vEnd=end;

  const m='<span class="sub">—</span>';
  let html=start>0?`<tr class="spacer"><td colspan="11" style="height:${start*ROW_H}px"></td></tr>`:'';
  for(let k=start;k<end;k++){
    const s=list[k],r=D.rows[s];
    const v=f=>esc(r?(r[f]??''):'')||m;
    const du=duDl(r);
    html+=`<tr class="${sel.has(s)?'sel ':''}${dirty.has(s)?'dirty ':''}${r?'':'blank'}" data-seq="${s}">
      <td class="chk">${r?`<input type="checkbox" ${sel.has(s)?'checked':''} onclick="chonDong(${s},${k},event)">`:''}</td>
      <td class="code${du?' xong':''}">${maCua(s)}</td>
      <td class="num ctr">${v('soBlock')}</td><td class="num ctr">${v('soTieuBan')}</td>
      <td>${r?(viDate(r.ngaySoan)||m):m}</td><td class="ctr">${v('giaSo')}</td>
      <td class="cell">${v('ktvCat')}</td><td class="cell">${v('ktvSoan')}</td>
      <td class="cell">${v('bsDoc')}</td>
      <td class="note"><input data-seq="${s}" data-f="ghiChu" value="${r?esc(r.ghiChu??''):''}"
        placeholder="Lý do / ghi chú…"></td>
      <td class="act"><button class="btn sm" onclick="moSua(${s})" ${du?'':'disabled'}
        title="${du?'Sửa lại thông tin mã này':'Mã chưa đủ thông tin — hoàn tất qua phiên soạn và màn bác sĩ đọc đã'}">✎ Sửa</button></td></tr>`;
  }
  const con=list.length-end;
  if(con>0)html+=`<tr class="spacer"><td colspan="11" style="height:${con*ROW_H}px"></td></tr>`;
  if(!list.length)html=`<tr><td colspan="11" class="empty">Chưa có mã nào được nhập trong sổ này.<br>
    Bấm <b>＋ Khởi tạo phiên soạn</b> để bắt đầu.</td></tr>`;
  $('soanBody').innerHTML=html;
  traTieuDiem(tieuDiem);                          // trả con trỏ về đúng ô ghi chú đang gõ

  const daNhap=Object.keys(D.rows).length;
  $('soanFoot').innerHTML=`Đầu mã <b>${D.prefix}</b> · sổ trải ${D.soDong} dòng · <b>${daNhap}</b> mã đã nhập
    · đang dựng dòng ${list.length?list[start]:0}–${list.length?list[end-1]:0}`;
  $('bulkBar').classList.toggle('off',!sel.size);
  $('bulkCount').textContent=`Đang chọn ${sel.size} dòng`;
  // ô tích đầu bảng nằm ngoài tbody nên phải tự đồng bộ, không thì lệch với vùng đang chọn
  $('chkAll').checked=daNhap>0&&sel.size>=daNhap;
  capNhatSave();

  // khung bảng đang rỗng thì thấp, dựng xong mới cao lên — dựng lại một lần cho kín màn hình
  if(sc.clientHeight>vh+8&&!veSoan.lai){
    veSoan.lai=1;veSoan(true);veSoan.lai=0;
  }
}
let rafId=null;
function onCuon(){if(rafId)return;rafId=requestAnimationFrame(()=>{rafId=null;veSoan()})}
function nhayToi(seq){
  const i=dsSeq().indexOf(seq);                  // lọc "chỉ hiện dòng đã nhập" làm lệch vị trí dòng
  $('gridScroll').scrollTop=Math.max(0,(i<0?seq-1:i)*ROW_H-ROW_H*3);
  veSoan(true);
}
function capNhatSave(){
  const el=$('saveState');
  el.className='saveState'+(dirty.size?' dirty':'');
  el.textContent=dirty.size?`${dirty.size} dòng chưa lưu`:'';
  $('btnSave').style.display=dirty.size?'inline-flex':'none';
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
/* Ghi chú gõ thẳng trên lưới: mã chưa ra block / tiêu bản vẫn ghi được lý do.
   Dòng chỉ có ghi chú vẫn nằm trong sổ, và mã vẫn được coi là trống ở phiên soạn. */
document.addEventListener('input',e=>{
  const el=e.target;
  if(!el.dataset||!el.dataset.seq||el.dataset.f!=='ghiChu')return;
  datO(Number(el.dataset.seq),'ghiChu',el.value);
  capNhatSave();
  clearTimeout(saveTimer);saveTimer=setTimeout(luuNgay,1200);
});
document.addEventListener('change',e=>{                 // rời ô thì ghi ngay, khỏi chờ hết giờ chờ
  const el=e.target;
  if(el.dataset&&el.dataset.seq&&el.dataset.f==='ghiChu'){clearTimeout(saveTimer);luuNgay()}
});

/* có số block / số tiêu bản thì ngày soạn + người soạn tự điền */
function tuDien(seq){
  const r=D.rows[seq];if(!r)return;
  if(r.soBlock!==''&&r.soBlock!=null||r.soTieuBan!==''&&r.soTieuBan!=null){
    if(!r.ngaySoan)r.ngaySoan=D.today;
    if(!r.ktvSoan)r.ktvSoan=D.me;
  }
}
async function luuNgay(){
  if(!dirty.size)return;
  const rows=[...dirty].map(s=>({seq:s,...(D.rows[s]||{})}));
  const gui=new Set(dirty);dirty=new Set();capNhatSave();
  try{
    const j=await post(window.SLIDE.save,{prefix:D.prefix,rows});
    veGiaList();
    $('saveState').className='saveState ok';$('saveState').textContent='Đã lưu';
    setTimeout(()=>{if(!dirty.size)$('saveState').textContent=''},1800);
    if(j.removed&&!dangGo())await taiSoan();   // đang gõ thì đừng tải lại, sẽ mất ô đang nhập
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

/** "26C2472" hoặc "2472" -> số thứ tự, null nếu khác đầu mã đang mở */
function raSeq(v){
  const s=String(v||'').trim().toUpperCase();
  if(/^\d{1,4}$/.test(s))return Number(s);
  const m=/^(\d{2}[A-Z])(\d{1,4})$/.exec(s);
  if(!m)return null;
  return m[1]===D.prefix?Number(m[2]):null;
}

/* ===== Mã bị nhảy qua, đang chờ xử lý thêm ===== */
/* Mã trống nằm giữa dải đã soạn = ca phải xử lý thêm mà sổ đã đi tiếp. Tự dò để khỏi quên. */
let CHO=[];
async function demCho(){
  try{
    const d=await get(window.SLIDE.cho);
    CHO=d.rows||[];
    const b=$('btnCho');
    b.innerHTML=`⏳ Chờ xử lý${d.tong?` (${d.tong})`:''}`;
    b.classList.toggle('red',d.quaHen>0);
    b.title=d.tong?`${d.tong} mã chưa ra tiêu bản${d.quaHen?` · ${d.quaHen} mã quá hẹn`:''}`
      :'Không có mã nào bị bỏ lại';
  }catch(e){}
}
async function moCho(){
  $('choKq').textContent='';
  $('choBody').innerHTML='<tr><td colspan="5" class="empty">Đang dò…</td></tr>';
  $('choModal').classList.add('show');
  await demCho();
  veCho();
}
function veCho(){
  $('choBody').innerHTML=CHO.length?CHO.map(x=>`<tr class="${x.quaHen?'qua':''}">
    <td class="ma">${esc(x.code)}</td>
    <td><input data-cho="${esc(x.code)}" data-chof="lyDo" value="${esc(x.lyDo)}" placeholder="VD: chờ khử canxi, khoa gửi bù mẫu…"></td>
    <td><input data-cho="${esc(x.code)}" data-chof="ngayHen" type="date" value="${esc(x.ngayHen)}"></td>
    <td class="ctr">${x.quaHen
      ?`<span class="badge b-late">quá hẹn ${x.treHen} ngày</span>`
      :x.soNgayCho==null?'<span class="sub">—</span>':`<span class="badge b-off">${x.soNgayCho} ngày</span>`}
      ${x.boQuaTu?`<div class="sub">bỏ qua từ ${viDate(x.boQuaTu)}</div>`:''}</td>
    <td class="ctr"><button class="btn sm primary" onclick="soanNgay('${esc(x.code)}')">Soạn ngay</button></td></tr>`).join('')
    :'<tr><td colspan="5" class="empty">Không có mã nào bị bỏ lại giữa chừng.</td></tr>';
}
/** Gõ lý do / ngày hẹn thì ghi luôn, khỏi nút lưu. */
document.addEventListener('change',e=>{
  const el=e.target;
  if(!el.dataset||!el.dataset.cho||!el.dataset.chof)return;
  const x=CHO.find(y=>y.code===el.dataset.cho);   // theo mã, không theo vị trí — danh sách có thể tự làm mới
  if(!x)return;
  x[el.dataset.chof]=el.value;
  post(window.SLIDE.choLuu,{code:x.code,ghiChu:x.lyDo,ngayHen:x.ngayHen||null})
    .then(()=>{$('choKq').style.color='var(--green)';$('choKq').textContent=`Đã ghi cho ${x.code}`;
      demCho().then(()=>{taiSoan().catch(()=>{})})})
    .catch(err=>{$('choKq').style.color='var(--red)';$('choKq').textContent='Không ghi được: '+err.message});
});
/** Mở thẳng phiên soạn bắt đầu từ mã đang chờ. */
async function soanNgay(code){
  dongM('choModal');
  await moPhien();
  $('phTu').value=code;
  diDenMa();
}

/* ===== Sửa một mã đã đủ thông tin ===== */
let suaSeq=null;
function moSua(seq){
  const r=D.rows[seq];
  if(!duDl(r))return toast('Mã này chưa đủ thông tin để sửa',true);
  suaSeq=seq;
  $('suaSub').textContent=`Mã ${maCua(seq)} — sửa xong lưu thẳng vào sổ`;
  $('suBlock').value=r.soBlock??'';$('suLam').value=r.soTieuBan??'';
  $('suNgay').value=r.ngaySoan||'';$('suGia').value=r.giaSo||'';
  $('suCat').value=r.ktvCat||'';$('suSoan').value=r.ktvSoan||'';
  $('suBs').value=r.bsDoc||'';$('suGhiChu').value=r.ghiChu||'';
  $('suErr').textContent='';
  $('suaModal').classList.add('show');
  setTimeout(()=>$('suBlock').focus(),60);
}
async function luuSua(){
  if(!suaSeq)return;
  const v={soBlock:$('suBlock').value.trim(),soTieuBan:$('suLam').value.trim(),ngaySoan:$('suNgay').value,
    giaSo:$('suGia').value.trim(),ktvCat:$('suCat').value.trim(),ktvSoan:$('suSoan').value.trim(),
    bsDoc:$('suBs').value.trim(),ghiChu:$('suGhiChu').value.trim()};
  const ma=maCua(suaSeq);
  Object.entries(v).forEach(([f,x])=>datO(suaSeq,f,x));
  suaSeq=null;
  dongM('suaModal');
  veSoan(true);
  await luuNgay();
  veGiaList();
  toast(`Đã lưu thay đổi cho <b>${ma}</b>`);
}

/* ===== Khởi tạo phiên soạn — một popup, hai giai đoạn ===== */
/* Giai đoạn 1: các mã trống của đầu mã đang mở, nhập số block + số tiêu bản.
   Giai đoạn 2: giá và kỹ thuật viên áp chung cho cả phiên; ngày soạn là ngày lưu. */
let PH={ma:[],buoc:1,gia:[],today:''};
const coSo=x=>String(x.soBlock??'').trim()!==''||String(x.soTieuBan??'').trim()!=='';

async function moPhien(danNgay){
  PH={ma:[],buoc:1,gia:[],today:D.today||'',boQua:new Set()};
  ['phTu','phGia','phCat','phSoan','phGhiChu'].forEach(i=>$(i).value='');
  $('phErr').textContent='';
  $('danMode').value='ma';dongKhungDan();
  $('phSub').textContent=`Đầu mã ${D.prefix} — toàn bộ mã chưa soạn của đầu mã này`;
  $('phienModal').classList.add('show');
  phBuoc(1);
  doCaoDongPh();
  PH.ma=dsMaTrong();                             // dựng cả danh sách trước, khỏi chờ máy chủ
  $('phWrap').scrollTop=0;pvS=pvE=-1;
  vePhBody(true);
  napPhuPhien();                                 // giá / KTV / ngày lấy sau, không chặn màn hình
  if(danNgay)return moKhungDan();
  const o=$('phBody').querySelector('input');if(o)o.focus();
}
/* Khoảng trống dài hơn ngần này mã là đổi dải số (kíp nhảy sang 26A2900 chẳng hạn),
   không phải bỏ quên — nếu không một lần nhảy dải sẽ gắn cờ hàng nghìn mã. */
const TOI_DA_NHAY=50;
/** Toàn bộ mã chưa soạn của đầu mã đang mở — tính tại chỗ từ sổ, không hỏi máy chủ. */
function dsMaTrong(){
  const co=v=>String(v??'').trim()!=='';
  const daSoan=Object.values(D.rows||{})
    .filter(r=>co(r.soBlock)||co(r.soTieuBan)).map(r=>Number(r.seq)).sort((a,b)=>a-b);

  // mã bị phiên soạn nhảy qua = nằm trong khoảng trống ngắn giữa hai mã đã soạn
  PH.boQua=new Set();
  for(let i=1;i<daSoan.length;i++){
    const a=daSoan[i-1],b=daSoan[i];
    if(b-a-1<1||b-a-1>TOI_DA_NHAY)continue;
    for(let s=a+1;s<b;s++)PH.boQua.add(s);
  }
  const ds=[],n=D.soDong||9999;
  for(let s=1;s<=n;s++)if(!daCoDl(s))ds.push({seq:s,code:maCua(s),soBlock:'',soTieuBan:''});
  return ds;
}
/** Giá đang dùng, danh sách KTV và ngày hôm nay — phần phụ của popup. */
async function napPhuPhien(){
  try{
    const d=await get(`${window.SLIDE.phienMa}?prefix=${encodeURIComponent(D.prefix)}&n=1`);
    PH.gia=d.gia||[];PH.today=d.today||'';
    if(d.me&&!$('phSoan').value)$('phSoan').value=d.me;
    $('phNgay').textContent=viDate(d.today)||'hôm nay';
    $('giaList').innerHTML=PH.gia.map(g=>`<option value="${esc(g)}">`).join('');
    $('phGiaNhanh').innerHTML=PH.gia.length?PH.gia.map(g=>`<button class="pnl" onclick="chonGiaPhien('${esc(g)}')">Giá ${esc(g)}</button>`).join('')
      :'<span class="hint">Chưa có giá nào trong sổ — nhập số giá mới ở ô trên.</span>';
  }catch(e){toast('Không lấy được danh sách giá / KTV: '+e.message,true)}
}
/* Cuộn ảo: cả nghìn mã chưa soạn nhưng chỉ dựng những dòng đang nhìn thấy. */
let PHROW=34,pvS=-1,pvE=-1,phRaf=null;
function doCaoDongPh(){
  const v=parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--phrowh'));
  if(v>0)PHROW=v;
}
function vePhBody(epLai){
  const sc=$('phWrap'),list=PH.ma;
  if(!list.length){
    $('phBody').innerHTML='<tr><td colspan="4" class="empty">Đầu mã này không còn mã trống — chọn đầu mã khác.</td></tr>';
    pvS=pvE=-1;demPhien();return;
  }
  const vh=sc.clientHeight||340;
  const start=Math.max(0,Math.floor(sc.scrollTop/PHROW)-5);
  const end=Math.min(list.length,start+Math.ceil(vh/PHROW)+10);
  if(!epLai&&start===pvS&&end===pvE)return;
  const o=luuOPhien();
  pvS=start;pvE=end;

  let html=start>0?`<tr class="sp"><td colspan="4" style="height:${start*PHROW}px"></td></tr>`:'';
  for(let i=start;i<end;i++){
    const x=list[i],boQua=PH.boQua&&PH.boQua.has(x.seq);   // bị phiên soạn nhảy qua
    html+=`<tr class="${boQua?'gap':''}">
      <td class="ma">${x.code}${boQua?' <span class="badge b-hc">bỏ qua</span>':''}</td>
      <td><input data-ph="${i}" data-phf="soBlock" type="number" min="0" inputmode="numeric" value="${esc(x.soBlock)}"></td>
      <td><input data-ph="${i}" data-phf="soTieuBan" type="number" min="0" inputmode="numeric" value="${esc(x.soTieuBan)}"></td>
      <td class="ctr"><button class="del" title="Bỏ mã này khỏi danh sách" onclick="boMaPhien(${i})">×</button></td></tr>`;
  }
  const con=list.length-end;
  if(con>0)html+=`<tr class="sp"><td colspan="4" style="height:${con*PHROW}px"></td></tr>`;
  $('phBody').innerHTML=html;
  traOPhien(o);
  demPhien();
  if(sc.clientHeight>vh+8&&!vePhBody.lai){vePhBody.lai=1;vePhBody(true);vePhBody.lai=0}
}
function demPhien(){
  const n=PH.ma.filter(coSo).length;
  const boQua=PH.boQua?PH.boQua.size:0;
  $('phDem').innerHTML=`Đã nhập <b>${n}</b> mã · tổng <b>${PH.ma.length}</b> mã chưa soạn`
    +(boQua?` · <b>${boQua}</b> mã bị nhảy qua`:'');
  $('phGiaiThich').innerHTML=`Đang mở <b>toàn bộ ${PH.ma.length} mã chưa soạn</b> của đầu mã ${D.prefix} — cuộn hoặc dùng
    <b>Đi đến mã</b> để tới đúng mã cần nhập. Mã bị phiên soạn nhảy qua được đánh dấu <b>bỏ qua</b>.
    Mã để trống cả hai ô sẽ không ghi vào sổ.`;
}
function boMaPhien(i){PH.ma.splice(i,1);vePhBody(true)}
/** Cuộn thẳng tới một mã trong danh sách rồi đặt con trỏ vào ô số block. */
function diDenMa(){
  const v=$('phTu').value.trim();
  const s=v?raSeq(v):0;
  if(!s)return toast('Nhập mã thuộc đầu mã '+D.prefix,true);
  const i=PH.ma.findIndex(x=>x.seq===s);
  if(i<0)return toast(`${maCua(s)} đã soạn rồi nên không có trong danh sách`,true);
  const sc=$('phWrap');
  sc.scrollTop=Math.max(0,i*PHROW-PHROW*2);
  vePhBody(true);
  setTimeout(()=>{
    const el=document.querySelector(`#phBody input[data-ph="${i}"][data-phf="soBlock"]`);
    if(el)el.focus({preventScroll:true});
  },40);
}
function chonGiaPhien(g){$('phGia').value=g;$('phErr').textContent=''}
function boDongTrong(){
  const n=PH.ma.length;
  PH.ma=PH.ma.filter(coSo);
  $('phWrap').scrollTop=0;pvS=pvE=-1;
  vePhBody(true);
  if(n>PH.ma.length)toast(`Đã thu gọn còn <b>${PH.ma.length}</b> mã đã nhập`);
}

/* ---- Dán từ Excel: chỉ nạp số block / số tiêu bản vào danh sách của phiên ---- */
const COT_MA =['Mã tiêu bản','Số block','Số tiêu bản'];
const COT_SEQ=['Số block','Số tiêu bản'];
function moKhungDan(){
  $('phDan').style.display='block';$('btnDan').style.display='none';
  $('danBox').value='';$('danKq').textContent='';
  $('danStart').value=$('danStart').value||$('phTu').value;
  veDanGoiY();setTimeout(()=>$('danBox').focus(),60);
}
function dongKhungDan(){$('phDan').style.display='none';$('btnDan').style.display='inline-flex'}
function veDanGoiY(){
  const m=$('danMode').value;
  $('danStartWrap').style.display=m==='seq'?'grid':'none';
  $('danCols').innerHTML=(m==='ma'?COT_MA:COT_SEQ).map((c,i)=>`<b>${i+1}.</b> ${c}`).join(' &nbsp;·&nbsp; ')
    +' &nbsp;— các cột khác trong vùng dán sẽ bị bỏ qua.';
  $('danBox').placeholder=m==='ma'?'2472\t2\t3\n2473\t1\t2':'2\t3\n1\t2';
}
/** Mã đã soạn rồi (có số block / số tiêu bản) hoặc đã gán giá thì phiên không được đè lên. */
function daCoDl(seq){
  const r=D.rows[seq];
  if(!r)return false;
  const co=v=>String(v??'').trim()!=='';
  return co(r.soBlock)||co(r.soTieuBan)||co(r.giaSo);
}
function danVaoPhien(){
  const mode=$('danMode').value;
  const lines=$('danBox').value.split(/\r?\n/).filter(l=>l.trim());
  if(!lines.length)return toast('Chưa có nội dung để dán',true);
  const so=v=>{const s=String(v??'').trim();return /^\d{1,4}$/.test(s)?s:''};

  let them=0,sai=0,nhay=0;const boQua=[];
  const dat=(seq,b,t)=>{
    if(!seq||seq<1||seq>9999){sai++;return}
    if(daCoDl(seq)){boQua.push(maCua(seq));return}
    if(b===''&&t==='')return;
    let x=PH.ma.find(y=>y.seq===seq);
    if(!x){x={seq,code:maCua(seq),soBlock:'',soTieuBan:''};PH.ma.push(x)}
    if(b!=='')x.soBlock=b;
    if(t!=='')x.soTieuBan=t;
    them++;
  };

  if(mode==='ma'){
    lines.forEach(l=>{const c=l.split('\t').map(x=>x.trim());dat(raSeq(c[0]),so(c[1]),so(c[2]))});
  }else{
    let s=raSeq($('danStart').value);
    if(!s)return toast('Nhập mã bắt đầu hợp lệ thuộc đầu mã '+D.prefix,true);
    // mã đã soạn trong sổ, hoặc vừa nhập ở ngay phiên này, đều phải nhảy qua
    const daCo=x=>{
      if(daCoDl(x))return true;
      const y=PH.ma.find(z=>z.seq===x);
      return !!y&&coSo(y);
    };
    lines.forEach(l=>{
      const c=l.split('\t').map(x=>x.trim());
      while(s<=9999&&daCo(s)){s++;nhay++}
      dat(s,so(c[0]),so(c[1]));s++;
    });
  }

  PH.ma.sort((a,b)=>a.seq-b.seq);
  vePhBody(true);
  const kq=[`đã nạp <b>${them}</b> mã`];
  if(boQua.length)kq.push(`bỏ qua ${boQua.length} mã đã có dữ liệu (${boQua.slice(0,6).join(', ')}${boQua.length>6?'…':''})`);
  if(nhay)kq.push(`nhảy qua ${nhay} mã đã có dữ liệu`);
  if(sai)kq.push(`${sai} dòng sai mã`);
  $('danKq').innerHTML=kq.join(' · ');
  toast('Dán vào phiên: '+kq.join(' · '));
  if(them)$('danBox').value='';
}
function phBuoc(b){
  PH.buoc=b;
  $('phB1').style.display=b===1?'block':'none';
  $('phB2').style.display=b===2?'block':'none';
  $('phStep1').classList.toggle('on',b===1);
  $('phStep2').classList.toggle('on',b===2);
  $('phBack').style.display=b===2?'inline-flex':'none';
  $('phNext').textContent=b===1?'Tiếp tục →':'✓ Hoàn thành & lưu phiên';
  $('phTitle').textContent=b===1?'Khởi tạo phiên — số block & số tiêu bản':'Khởi tạo phiên — giá và kỹ thuật viên';
  $('phErr').textContent='';
  if(b===2){veTomTat();setTimeout(()=>$('phGia').focus(),60)}
}
function phTiep(){PH.buoc===1?sangB2():luuPhien()}
function phQuayLai(){phBuoc(1)}
function sangB2(){
  if(!PH.ma.filter(coSo).length)return $('phErr').textContent='Nhập số block hoặc số tiêu bản cho ít nhất một mã';
  if(!$('phSoan').value)$('phSoan').value=D.me||'';
  phBuoc(2);
}
function veTomTat(){
  const ds=PH.ma.filter(coSo);
  const b=ds.reduce((t,x)=>t+(Number(x.soBlock)||0),0);
  const l=ds.reduce((t,x)=>t+(Number(x.soTieuBan)||0),0);
  $('phSum').innerHTML=`<b>${ds.length} mã tiêu bản</b> · ${ds[0].code} → ${ds[ds.length-1].code}
    · tổng ${b} block · ${l} tiêu bản
    <div class="hint" style="margin-top:4px">${ds.map(x=>`${x.code} <b>${x.soBlock||0}/${x.soTieuBan||0}</b>`).join(' &nbsp;·&nbsp; ')}</div>`;
}
async function luuPhien(){
  const ds=PH.ma.filter(coSo);
  if(!ds.length)return phBuoc(1);
  const gia=$('phGia').value.trim(),soan=$('phSoan').value.trim();
  if(!gia){$('phErr').textContent='Chọn hoặc nhập số giá';return $('phGia').focus()}
  if(!soan){$('phErr').textContent='Nhập tên KTV soạn';return $('phSoan').focus()}
  const so=v=>{const s=String(v??'').trim();return s===''?null:Number(s)};
  $('phNext').disabled=true;$('phErr').textContent='';
  try{
    const j=await post(window.SLIDE.phienLuu,{prefix:D.prefix,giaSo:gia,ktvSoan:soan,
      ktvCat:$('phCat').value.trim()||null,ghiChu:$('phGhiChu').value.trim()||null,
      rows:ds.map(x=>({seq:x.seq,soBlock:so(x.soBlock),soTieuBan:so(x.soTieuBan)}))});
    dongM('phienModal');
    await taiSoan();
    nhayToi(j.seqDau);
    toast(`Đã lưu phiên <b>${j.n}</b> mã (${j.tu} – ${j.den}) · giá ${esc(gia)} · KTV soạn ${esc(soan)}`);
  }catch(e){$('phErr').textContent=e.message}
  finally{$('phNext').disabled=false}
}
document.addEventListener('input',e=>{
  const el=e.target;
  if(!el.dataset||el.dataset.ph===undefined||!el.dataset.phf)return;
  const i=Number(el.dataset.ph), x=PH.ma[i];
  if(!x)return;
  x[el.dataset.phf]=el.value;
  demPhien();
});
/** Dựng lại bảng làm mất chỗ đang gõ, nên phải nhớ và trả con trỏ về đúng ô. */
function luuOPhien(){
  const a=document.activeElement;
  if(!a||!a.dataset||a.dataset.ph===undefined)return null;
  let s=null,e=null;
  try{s=a.selectionStart;e=a.selectionEnd}catch(_){}
  return {i:a.dataset.ph,f:a.dataset.phf,s,e};
}
function traOPhien(t){
  if(!t)return;
  const el=document.querySelector(`#phBody input[data-ph="${t.i}"][data-phf="${t.f}"]`);
  if(!el)return;
  el.focus({preventScroll:true});
  if(t.s!=null){try{el.setSelectionRange(t.s,t.e)}catch(_){}}
}
/* Enter chạy dọc các ô như trên sổ giấy; hết ô cuối thì sang giai đoạn 2 */
document.addEventListener('keydown',e=>{
  if(e.key!=='Enter'||!$('phienModal').classList.contains('show'))return;
  const t=e.target;
  if(t.tagName==='TEXTAREA')return;
  e.preventDefault();
  if(PH.buoc!==1)return phTiep();
  if(t.id==='phTu')return diDenMa();
  if(t.closest&&t.closest('#phDan'))return danVaoPhien();
  if(t.dataset&&t.dataset.ph!==undefined){
    const inps=[...$('phBody').querySelectorAll('input')];
    const i=inps.indexOf(t);
    if(i>=0&&i+1<inps.length)return inps[i+1].focus();
  }
  phTiep();
});

/* ===== 2. Bác sĩ đọc: nhận giá rồi chuyển trạng thái từng mã ===== */
/* Một mã đi qua ba trạng thái: chưa đọc → đã nhận → đã đọc. */
const TTD={chua:['b-off','chưa đọc'],nhan:['b-hc','đã nhận'],doc:['b-doc','đã đọc']};
async function taiDoc(){
  const d=await get(window.SLIDE.reader+(curGia?'?gia='+encodeURIComponent(curGia):''));
  $('bsList').innerHTML=(d.bs||[]).map(x=>`<option value="${esc(x)}">`).join('');
  if(!$('bsNhan').value)$('bsNhan').value=d.me||'';
  docGia=d.giaList||[];
  docRows=d.rows||[];
  // hoàn tất hết cả giá thì giá đó biến mất, quay về màn chọn giá cho khỏi treo bảng rỗng
  if(curGia&&!docGia.some(g=>g.gia===curGia)){curGia='';docRows=[];docSel=new Set()}
  veRacks();
  $('docPanel').style.display=curGia?'block':'none';
  if(curGia)veDoc();
}
/* Giá chia hai khối: chờ nhận (còn mã chưa đọc) và đang đọc (đã nhận / đã đọc chưa chốt). */
function veRacks(){
  const the=(r,nhan)=>`<div class="rack ${curGia===r.gia?'on':''} ${giaSel.has(r.gia)?'pick':''} ${nhan&&r.doc?'xong':''}"
    onclick="chonGia('${esc(r.gia)}')">
    ${nhan?'':`<label class="tick" title="Chọn giá này để nhận" onclick="event.stopPropagation()">
      <input type="checkbox" ${giaSel.has(r.gia)?'checked':''} onchange="tickGia('${esc(r.gia)}')"></label>`}
    <div class="no">Giá ${esc(r.gia)}<small>${r.n} mã</small></div>
    <div class="m">Soạn ${viDate(r.ngaySoan)||'—'}</div>
    <div class="m">${r.bs?esc(r.bs):'chưa có bác sĩ nhận'}</div>
    <div class="st">
      ${r.chua?`<span class="badge b-off">${r.chua} chưa đọc</span>`:''}
      ${r.nhan?`<span class="badge b-hc">${r.nhan} đã nhận</span>`:''}
      ${r.doc?`<span class="badge b-late">${r.doc} đã đọc chờ chốt</span>`:''}
    </div>
    ${nhan&&r.doc?`<div class="chot"><button class="btn sm green" style="width:100%"
      onclick="event.stopPropagation();chotCaGia('${esc(r.gia)}')">🏁 Chốt ${r.doc} mã còn vướng</button></div>`:''}
    </div>`;

  // khối 1: mọi giá KTV đã soạn còn trong sổ · khối 2: giá bác sĩ đã nhận
  const dangDoc=docGia.filter(r=>r.nhan>0||r.doc>0);
  $('racks1').innerHTML=docGia.length?docGia.map(r=>the(r,false)).join('')
    :'<div class="empty">Chưa có giá nào. Sang tab Sổ soạn để nhập giá cho tiêu bản.</div>';
  $('racks2').innerHTML=dangDoc.length?dangDoc.map(r=>the(r,true)).join('')
    :'<div class="empty">Chưa nhận giá nào — chọn giá ở khối 1 rồi bấm Nhận.</div>';
}
/** Đọc xong cả giá: chốt hết vào lịch sử, mã trả về sổ soạn, giá rời khối 2. */
async function chotCaGia(gia){
  if(!confirm(`Chốt các mã đã đọc của giá ${gia}?\n\nChúng được lưu vào lịch sử và mã trả về sổ soạn để dùng lại.`))return;
  try{
    const j=await post(window.SLIDE.chotGia,{gia:[gia]});
    if(curGia===gia){curGia='';docSel=new Set()}
    await taiDoc();
    if(j.n)toast(`Đã chốt <b>${j.n}</b> mã của giá ${esc(gia)} vào lịch sử`);
    if(j.vuong&&j.vuong.length)toast('Chưa chốt được: '+j.vuong.map(esc).join(' · '),true);
  }catch(e){toast('Không chốt được: '+e.message,true)}
}
function tickGia(g){giaSel.has(g)?giaSel.delete(g):giaSel.add(g);veRacks()}
function boChonGia(){giaSel=new Set();veRacks()}
/** Nhận giá: cả giá được gán tên bác sĩ và chuyển sang "đã nhận". */
async function nhanGia(){
  const bs=$('bsNhan').value.trim();
  if(!giaSel.size)return toast('Tích chọn ít nhất một giá ở thẻ bên dưới',true);
  if(!bs)return toast('Nhập tên bác sĩ nhận đọc',true);
  try{
    const j=await post(window.SLIDE.take,{gia:[...giaSel],bs});
    const ds=[...giaSel].join(', ');giaSel=new Set();
    await taiDoc();
    toast(`BS <b>${esc(bs)}</b> đã nhận giá ${esc(ds)} — ${j.n} mã tiêu bản`
      +(j.boQua?` · ${j.boQua} mã đã đọc xong nên giữ nguyên`:''));
  }catch(e){toast('Không nhận được giá: '+e.message,true)}
}
function chonGia(g){curGia=g;docSel=new Set();taiDoc()}
/** Mở giá bằng cách gõ số — không phải giá nào cũng có sẵn thẻ ở dưới. */
function moGiaGo(){
  const g=$('giaGo').value.trim();
  if(!g)return toast('Nhập số giá',true);
  chonGia(g);
}
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
    <td class="ctr">${(t=>`<span class="badge ${t[0]}">${t[1]}${r.ttDoc==='doc'&&r.ngayDoc?' '+viDate(r.ngayDoc):
      r.ttDoc==='nhan'&&r.ngayNhan?' '+viDate(r.ngayNhan):''}</span>`)(TTD[r.ttDoc]||TTD.chua)}</td>
  </tr>`).join('')||'<tr><td colspan="9" class="empty">Giá này chưa có mã nào.</td></tr>';
  const dem=t=>docRows.filter(r=>(r.ttDoc||'chua')===t).length;
  $('docFoot').innerHTML=`Đang chọn <b>${docSel.size}</b> mã · ${dem('chua')} chưa đọc
    · ${dem('nhan')} đã nhận · ${dem('doc')} đã đọc`;
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
/**
 * Bác sĩ đọc xong: chuyển sang đã đọc rồi chốt luôn vào lịch sử trong một lần bấm,
 * mã trả về sổ soạn nên giá tự rời khối 2. Kết quả không bắt buộc.
 */
async function docXong(){
  if(!docSel.size)return toast('Tích chọn mã đã đọc xong',true);
  const ds=docRows.filter(r=>docSel.has(r.code));
  if(!confirm(`Bác sĩ đã đọc xong ${ds.length} mã tiêu bản?\n\n`
    +`Các mã này được chốt vào lịch sử ngay và mã tiêu bản trả về sổ soạn để dùng lại. `
    +`Kết quả không bắt buộc — ca nào cần ghi thì nhập vào ô Kết quả trước khi bấm.`))return;
  const codes=[...docSel];
  try{
    await luuDoc0();                               // giữ kết quả vừa gõ (nếu có)
    await post(window.SLIDE.mark,{codes,trangThai:'doc',bsDoc:$('bsAll').value.trim()||null});
    const j=await post(window.SLIDE.finish,{codes});
    docSel=new Set();await taiDoc();
    if(j.n)toast(`Đã đọc xong và chốt <b>${j.n}</b> mã vào lịch sử`);
    if(j.vuong.length)toast('Đã ghi là đã đọc nhưng chưa chốt được: '+j.vuong.map(esc).join(' · '),true);
  }catch(e){toast('Không ghi được: '+e.message,true)}
}

/**
 * Chốt các mã đã đọc mà còn vướng (phiếu hóa mô chưa đọc KQ, hội chẩn chưa chốt)
 * sau khi đã xử lý xong phần vướng đó.
 */
async function hoanTat(){
  if(!docSel.size)return toast('Tích chọn mã cần chốt',true);
  const ds=docRows.filter(r=>docSel.has(r.code));
  const chuaDu=ds.filter(r=>r.ttDoc!=='doc');
  if(chuaDu.length)return toast(`Chưa chốt được: ${chuaDu.length} mã chưa ở trạng thái đã đọc `
    +`(${chuaDu.slice(0,5).map(r=>esc(r.code)).join(', ')}${chuaDu.length>5?'…':''})`,true);
  if(!confirm(`Chốt ${ds.length} mã tiêu bản vào lịch sử?\n\n`
    +`Cả ca được lưu vào lịch sử ở tab Hóa mô miễn dịch, sau đó dòng trong sổ soạn bị xóa `
    +`để mã quay lại danh sách mã trống. Phiếu hóa mô và hội chẩn của các mã này cũng được dọn theo.`))return;
  try{
    await luuDoc0();                               // ghi nốt kết quả vừa gõ rồi mới chốt
    const j=await post(window.SLIDE.finish,{codes:[...docSel]});
    docSel=new Set();
    await taiDoc();
    if(j.n)toast(`Đã hoàn tất <b>${j.n}</b> mã và trả mã về sổ soạn`);
    if(j.vuong.length)toast('Chưa chốt được: '+j.vuong.map(esc).join(' · '),true);
  }catch(e){toast('Không hoàn tất được: '+e.message,true)}
}

/** Đổi trạng thái đọc cho các mã đang tích chọn (chọn tất cả bằng ô ở đầu bảng). */
async function doiTt(tt){
  if(!docSel.size)return toast('Tích chọn ít nhất một mã tiêu bản',true);
  try{
    await luuDoc0();                               // giữ kết quả vừa gõ trước khi đổi trạng thái
    const j=await post(window.SLIDE.mark,{codes:[...docSel],trangThai:tt,bsDoc:$('bsAll').value.trim()||null});
    docSel=new Set();await taiDoc();
    toast(`Đã chuyển <b>${j.n}</b> mã sang <b>${TTD[tt][1]}</b>`);
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
/* Lịch sử các lượt đã hoàn tất, nằm ngay dưới sổ hóa mô miễn dịch */
async function taiLichSu(){
  const q=$('lsQ').value.trim();
  const d=await get(window.SLIDE.history+(q?'?q='+encodeURIComponent(q):''));
  LS=d.rows||[];
  $('lsXuat').href=window.SLIDE.historyExport+(q?'?q='+encodeURIComponent(q):'');   // xuất đúng phần đang lọc
  $('lsBody').innerHTML=d.rows.length?d.rows.map(h=>`<tr>
    <td class="main">${esc(h.code)}</td>
    <td class="ctr">${h.lan>1?`<span class="badge b-hc">lượt ${h.lan}</span>`:h.lan}</td>
    <td class="ctr">${h.soBlock??'—'}</td><td class="ctr">${h.soTieuBan??'—'}</td>
    <td>${viDate(h.ngaySoan)||'—'}</td><td class="ctr">${esc(h.giaSo)||'—'}</td>
    <td>${esc(h.ktvSoan)||'—'}</td><td>${esc(h.bsDoc)||'—'}</td>
    <td>${esc(h.ketQua)||'<span class="sub">—</span>'}
      ${h.ketLuanHoiChan?`<div class="sub">Hội chẩn: ${esc(h.ketLuanHoiChan)}</div>`:''}</td>
    <td>${esc(h.benhNhan)||'<span class="sub">—</span>'}${h.maBn?`<div class="sub">${esc(h.maBn)}</div>`:''}</td>
    <td><div class="mk-chips">${h.markers.map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')}</div></td>
    <td>${viDate(h.ngayChot)}<div class="sub">${esc(h.nguoiChot)}</div></td>
    <td class="ctr"><button class="btn sm" onclick="xemLs(${h.id})">Chi tiết</button></td></tr>`).join('')
    :'<tr><td colspan="13" class="empty">Chưa có ca nào được hoàn tất.</td></tr>';
  $('lsCount').textContent=`${d.rows.length}/${d.tong} lượt`;
}
/** Toàn bộ ca của một lượt: sổ soạn, kết quả, phiếu hóa mô, hội chẩn. */
function xemLs(id){
  const h=LS.find(x=>x.id===id);if(!h)return;
  $('lsSub').textContent=`${h.code} — lượt ${h.lan} · hoàn tất ${viDate(h.ngayChot)}`;
  $('lsBox').innerHTML=veChiTietLs(h);
  $('lsModal').classList.add('show');
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
    const ls=d.lichSu||[];
    $('traBox').innerHTML=ls.length
      ? `<div class="warn-anh">Mã <b>${esc(code)}</b> đã hoàn tất và được trả về sổ soạn để dùng lại —
           dưới đây là các lượt đã lưu trong lịch sử.</div>${veLichSuMa(ls)}`
      : `<div class="empty">Không tìm thấy mã <b>${esc(code)}</b>.
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
      ||'<div class="empty">Mã này chưa có mốc nào.</div>'}</div>
    ${(d.lichSu||[]).length?`<div style="margin-top:12px">${veLichSuMa(d.lichSu)}</div>`:''}`;
}
/** Các lượt trước của một mã — mã được dùng lại nên có thể có nhiều lượt. */
function veLichSuMa(ls){
  return `<div style="font-size:12px;font-weight:800;margin-bottom:7px">Lịch sử ${ls.length} lượt đã hoàn tất của mã này</div>`
    +ls.map(h=>`<div class="yk-i" style="margin-bottom:8px">${veChiTietLs(h)}</div>`).join('');
}
/** Toàn bộ những gì đã lưu của một lượt — phiếu hóa mô và hội chẩn bị xóa khi chốt nên chỉ còn ở đây. */
function veChiTietLs(h){
  const d=v=>viDate(v)||'—', t=v=>esc(v)||'—';
  const ph=(h.hmmd||[]).map((x,i)=>`<div class="ls-kv" style="margin-top:${i?8:0}px">
    <div class="mk-chips" style="margin-bottom:4px">${(x.markers||[]).map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')
      ||'<span class="sub">không có marker</span>'}</div>
    <div>Chỉ định: <b>${t(x.bsChiDinh)}</b>${x.ngayChiDinh?' · '+d(x.ngayChiDinh):''}${x.soBlock?' · '+x.soBlock+' block':''}</div>
    ${x.cdLamSang?`<div>Chẩn đoán lâm sàng: ${esc(x.cdLamSang)}</div>`:''}
    ${x.viTri?`<div>Vị trí lấy mẫu: ${esc(x.viTri)}</div>`:''}
    <div>Lấy mẫu ${d(x.ngayLayMau)} · nhận mẫu ${d(x.ngayNhanMau)} · nhuộm ${d(x.ngayNhuom)}
      · đọc KQ ${d(x.ngayDocKq)}${x.bsDocKq?' ('+esc(x.bsDocKq)+')':''}</div></div>`).join('');
  const hc=h.hoiChan;
  return `<div class="t"><b>Lượt ${h.lan} — ${esc(h.code)}</b>
      <span class="badge b-xong">hoàn tất ${d(h.ngayChot)}</span><span class="d">${t(h.nguoiChot)}</span></div>
    <div class="ls-kv">${h.soBlock??'—'} block · ${h.soTieuBan??'—'} lam · giá ${t(h.giaSo)}
      · soạn ${d(h.ngaySoan)} · KTV cắt ${t(h.ktvCat)} · KTV soạn ${t(h.ktvSoan)}</div>
    <div class="ls-kv">BS đọc <b>${t(h.bsDoc)}</b> · nhận giá ${d(h.ngayNhan)} · đọc ${d(h.ngayDoc)}</div>
    ${h.ketQua?`<div class="ls-sec"><h4>Kết quả / đánh giá của bác sĩ</h4>
      <div class="ls-kv">${esc(h.ketQua)}</div></div>`:''}
    ${h.benhNhan||h.maBn?`<div class="ls-sec"><h4>Bệnh nhân</h4><div class="ls-kv">${t(h.benhNhan)}
      ${h.maBn?' · mã BN '+esc(h.maBn):''}${h.khoa?' · '+esc(h.khoa):''}${h.viTri?' · '+esc(h.viTri):''}</div></div>`:''}
    ${ph?`<div class="ls-sec"><h4>Hóa mô miễn dịch — ${(h.hmmd||[]).length} phiếu</h4>${ph}</div>`:''}
    ${hc?`<div class="ls-sec"><h4>Hội chẩn</h4>
      ${hc.ketLuan?`<div class="ls-kv"><b>Kết luận:</b> ${esc(hc.ketLuan)}</div>
        <div class="sub">Chốt bởi ${t(hc.bsChot)} ngày ${d(hc.ngayChot)}</div>`:''}
      ${(hc.yKien||[]).map(y=>`<div class="ls-kv" style="margin-top:6px"><b>${esc(y.bs)}</b>
        <span class="sub">${esc(y.luc||'')}</span><div>${esc(y.noiDung)}</div></div>`).join('')}</div>`:''}
    ${h.ghiChu?`<div class="ls-sec"><h4>Ghi chú</h4><div class="ls-kv">${esc(h.ghiChu)}</div></div>`:''}`;
}
async function taiTinhTrang(){
  const q=$('ttQ').value.trim(),tt=window.__tt||'';
  const d=await get(`${window.SLIDE.status}?q=${encodeURIComponent(q)}&tt=${encodeURIComponent(tt)}`);
  $('ttTiles').innerHTML=Object.entries(TT).map(([k,v])=>[k,v[1]]).map(([k,l])=>`<div class="tile ${tt===k?'on':''}"
    onclick="window.__tt='${tt===k?'':k}';taiTinhTrang()"><div class="k">${l}</div><div class="v">${d.dem[k]||0}</div></div>`).join('');
  $('ttBody').innerHTML=d.rows.length?d.rows.map(r=>{const s=TT[r.trangThai]||TT.soan;
    return `<tr><td class="main">${esc(r.code)}</td><td class="ctr">${r.soBlock??'—'}</td><td class="ctr">${r.soTieuBan??'—'}</td>
    <td>${viDate(r.ngaySoan)||'—'}</td><td class="ctr">${esc(r.giaSo)||'—'}</td>
    <td>${esc(r.ktvSoan)||'—'}</td><td>${esc(r.bsDoc)||'—'}</td>
    <td>${esc(r.ketQua)||'<span class="sub">—</span>'}</td>
    <td>${esc(r.benhNhan)||'<span class="sub">—</span>'}${r.khoa?`<div class="sub">${esc(r.khoa)}</div>`:''}</td>
    <td><div class="mk-chips">${r.markers.map(m=>`<span class="chip ro">${esc(m)}</span>`).join('')}</div></td>
    <td>${r.hoiChan==='chot'?`<span class="badge b-xong">đã thống nhất</span>
        ${r.ketLuanHc?`<div class="sub">${esc(r.ketLuanHc)}</div>`:''}`
      :r.hoiChan==='mo'?'<span class="badge b-hc">đang hội chẩn</span>':'<span class="sub">—</span>'}</td>
    <td><span class="badge b-${s[0]}">${s[1]}</span></td></tr>`}).join('')
    :'<tr><td colspan="12" class="empty">Không có mã nào phù hợp.</td></tr>';
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
    :'<div class="empty" style="padding:24px">Không còn ca nào đang hội chẩn.<br>Ca đã thống nhất kết quả nằm ở tab <b>Kết quả hội chẩn</b>.</div>';
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

/* ===== 6. Kết quả hội chẩn đã thống nhất ===== */
async function taiHcXong(){
  const q=$('hxQ').value.trim();
  const d=await get(window.SLIDE.hcXong+(q?'?q='+encodeURIComponent(q):''));
  $('hxBody').innerHTML=d.rows.length?d.rows.map(x=>`<tr>
    <td class="main">${esc(x.code)}${x.lan?` <span class="badge b-off">lượt ${x.lan}</span>`:''}</td>
    <td class="ctr">${esc(x.giaSo)||'—'}</td><td>${viDate(x.ngaySoan)||'—'}</td>
    <td>${viDate(x.ngayChot)||'—'}</td><td>${esc(x.bsChot)||'—'}</td>
    <td class="ctr">${x.soYKien}</td>
    <td>${esc(x.ketLuan)}</td>
    <td class="ctr">${x.trongSo?'<span class="badge b-soan">còn trong sổ</span>'
      :'<span class="badge b-xong">đã hoàn tất</span>'}</td>
    <td class="ctr"><button class="btn sm" onclick="$('traCode').value='${esc(x.code)}';go('tra');xemTien()">Tiến trình</button></td>
  </tr>`).join('')
    :'<tr><td colspan="9" class="empty">Chưa có ca nào thống nhất kết quả hội chẩn.</td></tr>';
  $('hxCount').textContent=`${d.rows.length}/${d.tong} ca`;
}

/* ===== khởi động ===== */
function dongM(id){$(id).classList.remove('show')}
$('prefix').addEventListener('change',()=>{sel=new Set();$('gridScroll').scrollTop=0;taiSoan()});
$('onlyFilled').addEventListener('change',()=>{$('gridScroll').scrollTop=0;veSoan(true)});
$('gridScroll').addEventListener('scroll',onCuon);
$('phWrap').addEventListener('scroll',()=>{
  if(phRaf)return;
  phRaf=requestAnimationFrame(()=>{phRaf=null;vePhBody()});
});
$('jump').addEventListener('change',e=>{
  const n=raSeq(e.target.value);
  if(!n||n<1||n>9999)return toast('Mã không hợp lệ',true);
  if($('onlyFilled').checked)$('onlyFilled').checked=false;
  nhayToi(n);
});
$('ttQ').addEventListener('input',()=>{clearTimeout(window.__tq);window.__tq=setTimeout(taiTinhTrang,320)});
/* gõ mã rồi Enter là chạy luôn, khỏi phải với chuột sang nút */
$('traCode').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();xemTien()}});
$('hcCode').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();moHc()}});
$('hmTt').addEventListener('change',taiHmmd);
$('lsQ').addEventListener('input',()=>{clearTimeout(window.__lsq);window.__lsq=setTimeout(taiLichSu,320)});
$('hxQ').addEventListener('input',()=>{clearTimeout(window.__hxq);window.__hxq=setTimeout(taiHcXong,320)});
$('mkMaBn').addEventListener('change',traBn);
document.querySelectorAll('.mbg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('show')}));
window.addEventListener('beforeunload',e=>{if(dirty.size){e.preventDefault();e.returnValue=''}});
/* Bàn phím ảo bật/tắt cũng phát ra resize — đang gõ ghi chú thì bỏ qua, khỏi nháy */
let rsTimer=null;
window.addEventListener('resize',()=>{
  if(dangGo())return;
  clearTimeout(rsTimer);rsTimer=setTimeout(()=>{doCaoDong();veSoan(true)},160);
});
doCaoDong();
taiSoan().catch(e=>alert('Lỗi tải dữ liệu: '+e.message));
</script>
</body>
</html>
