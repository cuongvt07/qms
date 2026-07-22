/**
 * Nhân bản dữ liệu sang nhiều ngày: mở lịch, tích chọn ngày rồi lưu.
 * Dùng chung cho các module QMS (mỗi module đều có openModal/closeModal/toast giống nhau).
 *
 *   QMSDup.open({
 *     title, sub,
 *     existing: ['2026-07-01', ...],   // ngày đã có dữ liệu -> đánh dấu sẵn
 *     onSave: (dates, opts) => {...}   // opts.overwrite = có ghi đè ngày đã có không
 *   })
 */
(function () {
  const D = {};
  const pad = n => String(n).padStart(2, '0');
  const iso = (y, m, d) => y + '-' + pad(m) + '-' + pad(d);
  const todayISO = () => { const t = new Date(); return iso(t.getFullYear(), t.getMonth() + 1, t.getDate()); };
  const DOW = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

  let sel = new Set(), cur = { y: 0, m: 0 }, existing = new Set(), cfg = null;

  function daysInMonth(y, m) { return new Date(y, m, 0).getDate(); }

  function grid() {
    const y = cur.y, m = cur.m, n = daysInMonth(y, m);
    const first = new Date(y, m - 1, 1).getDay();          // 0 = CN
    let html = '<div class="qd-dow">' + DOW.map(d => '<span>' + d + '</span>').join('') + '</div><div class="qd-grid">';
    for (let i = 0; i < first; i++) html += '<span class="qd-empty"></span>';
    for (let d = 1; d <= n; d++) {
      const v = iso(y, m, d);
      const cls = ['qd-day'];
      if (sel.has(v)) cls.push('on');
      if (existing.has(v)) cls.push('has');
      if (v === todayISO()) cls.push('today');
      html += '<button type="button" class="' + cls.join(' ') + '" data-d="' + v + '">' +
        '<b>' + d + '</b>' + (existing.has(v) ? '<i>đã có</i>' : '') + '</button>';
    }
    return html + '</div>';
  }

  function paint() {
    const box = document.getElementById('qdBody');
    if (!box) return;
    box.innerHTML = grid();
    box.querySelectorAll('.qd-day').forEach(b => {
      b.onclick = () => { const v = b.dataset.d; sel.has(v) ? sel.delete(v) : sel.add(v); paint(); };
    });
    document.getElementById('qdMonth').textContent = 'Tháng ' + pad(cur.m) + '/' + cur.y;
    const n = sel.size;
    document.getElementById('qdCount').textContent = n ? ('Đã chọn ' + n + ' ngày') : 'Chưa chọn ngày nào';
    const btn = document.getElementById('modalSave');
    if (btn) { btn.textContent = n ? ('Lưu ' + n + ' ngày') : 'Lưu'; btn.disabled = !n; }
  }

  D.move = function (delta) {
    let m = cur.m + delta, y = cur.y;
    if (m < 1) { m = 12; y--; } if (m > 12) { m = 1; y++; }
    cur = { y, m }; paint();
  };
  D.all = function () { const n = daysInMonth(cur.y, cur.m); for (let d = 1; d <= n; d++) sel.add(iso(cur.y, cur.m, d)); paint(); };
  D.work = function () {
    const n = daysInMonth(cur.y, cur.m);
    for (let d = 1; d <= n; d++) { const w = new Date(cur.y, cur.m - 1, d).getDay(); if (w !== 0) sel.add(iso(cur.y, cur.m, d)); }
    paint();
  };
  D.none = function () { const n = daysInMonth(cur.y, cur.m); for (let d = 1; d <= n; d++) sel.delete(iso(cur.y, cur.m, d)); paint(); };

  D.open = function (opt) {
    cfg = opt;
    sel = new Set();
    existing = new Set(opt.existing || []);
    const t = new Date();
    cur = { y: t.getFullYear(), m: t.getMonth() + 1 };

    const body =
      '<div class="qd-wrap">' +
      '  <div class="qd-bar">' +
      '    <button type="button" class="btn sm" onclick="QMSDup.move(-1)">‹</button>' +
      '    <b id="qdMonth"></b>' +
      '    <button type="button" class="btn sm" onclick="QMSDup.move(1)">›</button>' +
      '    <span class="qd-sp"></span>' +
      '    <button type="button" class="btn sm" onclick="QMSDup.all()">Chọn cả tháng</button>' +
      '    <button type="button" class="btn sm" onclick="QMSDup.work()">Trừ chủ nhật</button>' +
      '    <button type="button" class="btn sm" onclick="QMSDup.none()">Bỏ chọn tháng này</button>' +
      '  </div>' +
      '  <div id="qdBody"></div>' +
      '  <div class="qd-foot">' +
      '    <span id="qdCount"></span>' +
      '    <label class="qd-ow"><input type="checkbox" id="qdOverwrite"> Ghi đè nếu ngày đó đã có dữ liệu</label>' +
      '  </div>' +
      '</div>';

    openModal(opt.title || 'Nhân bản sang nhiều ngày',
      opt.sub || 'Tích chọn các ngày cần tạo bản sao, có thể chọn ở nhiều tháng khác nhau.',
      body, 'Lưu', function () {
        const dates = [...sel].sort();
        if (!dates.length) { if (typeof toast === 'function') toast('Chưa chọn ngày nào', 'error'); return; }
        cfg.onSave(dates, { overwrite: document.getElementById('qdOverwrite').checked });
      }, true);
    setTimeout(paint, 0);
  };

  window.QMSDup = D;
})();
