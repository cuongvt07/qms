/* Điền trực tiếp: render .docx bằng docx-preview rồi chèn ô nhập vào chỗ ${key}.
   Giữ đúng cấu trúc vals cũ: phẳng vals[key] (+ chk true/false), bảng vals.t[tkey][i][col]. */
window.QFInline = (function () {
  let WIRE = null, ROOT = null, dirty = false, autosaveT = null, kbBound = false;
  let CONFIG = false;              // chế độ cấu hình ẩn ô
  let HIDDEN = new Set();          // key placeholder bị ẩn
  let ADD = false;                 // chế độ "thêm ô" đang bật (click để đặt ô)
  let PLACEMENT = 'inline';        // 'inline' = cùng dòng (sau chữ) | 'below' = dòng dưới
  let ADDED = new Set();           // key các ô do người dùng thêm inline
  let addClickBound = false;

  function setStatus(html) { const el = document.getElementById('qf-status'); if (el) el.innerHTML = html; }
  function markDirty() { dirty = true; setStatus('<span style="color:#d97706">● Đang sửa…</span>'); }
  function markSaved() {
    dirty = false;
    const t = new Date(), p = n => String(n).padStart(2, '0');
    setStatus('<span style="color:#16a34a">✓ Đã lưu ' + p(t.getHours()) + ':' + p(t.getMinutes()) + '</span>');
  }
  function scheduleAutosave() {
    clearTimeout(autosaveT);
    autosaveT = setTimeout(() => {
      if (!WIRE) return;
      const r = WIRE.save(collectAll(), true);   // true = im lặng (không flash/không nhật ký)
      (r && r.then) ? r.then(markSaved) : markSaved();
    }, 1500);
  }

  const cleanLabel = s => String(s || '').replace(/\$\{[a-z0-9_]+\}/ig, '').trim();
  const humanize   = k => String(k || '').replace(/[_-]+/g, ' ').trim();
  const isSmall    = k => /^(stt|tt|so_?tt|sott)$/i.test(k) || /(^|_)(ngay|thang|nam)$/i.test(k);
  // Loại ô ngày/tháng/năm (điền tay): theo NHÃN (đuôi ngày/tháng/năm) rồi type=date, cuối cùng theo key.
  function dateKind(key, info) {
    const l = String((info && info.label) || '').toLowerCase().trim();
    if (/ngày$/.test(l)) return 'day';
    if (/tháng$/.test(l)) return 'month';
    if (/năm$/.test(l)) return 'year';
    if (info && info.type === 'date') return 'vndate';
    if (/(^|_)ngay$/i.test(key)) return 'day';
    if (/(^|_)thang$/i.test(key)) return 'month';
    if (/(^|_)nam$/i.test(key)) return 'year';
    return null;
  }
  const autosize   = el => { el.style.width = Math.max(2.4, (el.value || '').length + 0.6) + 'ch'; };
  const isStt = col => {
    const k = (col.key || '').toLowerCase(), l = (col.label || '').toLowerCase();
    return ['stt', 'tt', 'so_tt', 'sott'].includes(k) || /^(stt|tt|số\s*tt|#)$/.test(l);
  };

  function buildMeta(fields) {
    const meta = {}, tables = [], tableCols = new Set();
    for (const f of (fields || [])) {
      const type = f.type || 'text', hid = !!f.hidden, grp = cleanLabel(f.label || '');
      if (type === 'repeatable_table') {
        const cols = f.columns || [];
        if (!hid) tables.push({ key: f.key, label: grp, columns: cols });
        for (const c of cols) { tableCols.add(c.key); meta[c.key] = { type: c.type || 'text', label: cleanLabel(c.label || ''), group: grp, hidden: hid, table: f.key }; }
      } else if (f.option_ph) {
        for (const opt of Object.keys(f.option_ph)) meta[f.option_ph[opt]] = { type: 'chk', label: cleanLabel(opt), group: grp, hidden: hid };
      } else {
        meta[f.key] = { type, label: cleanLabel(f.label || ''), group: '', hidden: hid };
      }
    }
    return { meta, tables, tableCols };
  }

  function mkInput(key, meta, val) {
    const info = meta[key] || { type: key.startsWith('chk_') ? 'chk' : 'text', label: humanize(key), hidden: false };
    if (info.hidden) return document.createTextNode('');
    if (key.startsWith('chk_')) {
      const c = document.createElement('input');
      c.type = 'checkbox'; c.className = 'qf-chk'; c.dataset.path = key;
      c.title = ((info.group ? info.group + ' · ' : '') + (info.label || '')).trim();
      if (val) c.checked = true;
      return c;
    }
    const i = document.createElement('input');
    i.type = 'text'; i.className = 'qf-in'; i.dataset.path = key; i.title = info.label || key;
    const dk = dateKind(key, info);
    if (dk) {
      // Ngày/tháng/năm gõ tay (không dùng date-picker) + đánh dấu để qf-date.js kiểm
      i.setAttribute('data-datekind', dk);
      i.inputMode = 'numeric';
      if (dk === 'vndate') { i.classList.add('qf-date'); i.placeholder = 'dd/mm/yyyy'; i.maxLength = 10; }
      else { i.classList.add('qf-sm'); i.maxLength = (dk === 'year' ? 4 : 2); i.addEventListener('input', () => autosize(i)); }
    } else if (isSmall(key)) {
      i.classList.add('qf-sm'); i.addEventListener('input', () => autosize(i));
    } else if (info.type === 'number') {
      i.type = 'number'; i.inputMode = 'decimal';
    }
    if (val != null && val !== '') i.value = val;
    if (i.classList.contains('qf-sm')) autosize(i);
    return i;
  }

  /* ---- Bảng lặp: nhân bản dòng mẫu + nút +/✕ ---- */
  function firstTextNodeWith(root, token) {
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
    while (w.nextNode()) if (w.currentNode.nodeValue.includes(token)) return w.currentNode;
    return null;
  }
  function rowContaining(root, token) {
    const n = firstTextNodeWith(root, token);
    let el = n && n.parentNode;
    while (el && el.tagName !== 'TR') el = el.parentNode;
    return el;
  }
  function fillRow(tr, ctx, i, rowData) {
    ctx.columns.forEach(col => {
      const token = '${' + col.key + '}';
      const node = firstTextNodeWith(tr, token);
      if (!node) return;
      let repl;
      if (isStt(col)) { repl = document.createElement('span'); repl.className = 'qf-stt'; repl.textContent = (i + 1); }
      else {
        repl = document.createElement('input');
        repl.className = 'qf-in'; repl.dataset.path = 't.' + ctx.key + '.' + i + '.' + col.key;
        repl.title = cleanLabel(col.label || col.key);
        if (col.type === 'date') { repl.type = 'date'; repl.classList.add('qf-date'); }
        else if (col.type === 'number') { repl.type = 'number'; repl.inputMode = 'decimal'; }
        else repl.type = 'text';
        const v = rowData ? rowData[col.key] : '';
        if (v != null && v !== '') repl.value = v;
      }
      const idx = node.nodeValue.indexOf(token);
      const after = node.splitText(idx);
      after.nodeValue = after.nodeValue.slice(token.length);
      node.parentNode.insertBefore(repl, after);
    });
    const tds = tr.querySelectorAll('td');
    if (tds.length) {
      const del = document.createElement('button');
      del.type = 'button'; del.className = 'qf-del'; del.textContent = '✕'; del.title = 'Xoá dòng';
      del.onclick = () => { collectTable(ctx); ctx.rows.splice(i, 1); if (!ctx.rows.length) ctx.rows.push({}); renderTable(ctx); };
      tds[tds.length - 1].appendChild(del);
    }
  }
  function renderTable(ctx) {
    (ctx._rowEls || []).forEach(r => r.remove());
    ctx._rowEls = [];
    ctx.rows.forEach((rowData, i) => {
      const tmp = document.createElement('table');
      tmp.innerHTML = '<tbody>' + ctx.tplHTML + '</tbody>';
      const tr = tmp.querySelector('tr');
      fillRow(tr, ctx, i, rowData);
      ctx.parent.insertBefore(tr, ctx.ref);
      ctx._rowEls.push(tr);
    });
  }
  function collectTable(ctx) {
    const rows = [];
    (ctx._rowEls || []).forEach(tr => {
      const row = {};
      tr.querySelectorAll('[data-path]').forEach(el => {
        const col = el.dataset.path.split('.')[3];
        row[col] = el.type === 'checkbox' ? el.checked : el.value;
      });
      rows.push(row);
    });
    ctx.rows = rows.length ? rows : [{}];
  }
  function setupTables(root, tables, vals) {
    const unhandled = [];   // cột bảng không tìm được dòng mẫu -> trả về để render thành ô nhập thường
    for (const t of tables) {
      if (!t.columns.length) continue;
      const tplRow = rowContaining(root, '${' + t.columns[0].key + '}');
      if (!tplRow) { t.columns.forEach(c => unhandled.push(c.key)); continue; }
      const existing = (vals.t && vals.t[t.key]) ? vals.t[t.key] : [];
      const ctx = {
        key: t.key, columns: t.columns, parent: tplRow.parentNode, ref: tplRow.nextSibling,
        tplHTML: tplRow.outerHTML, rows: existing.length ? existing.slice() : [{}], _rowEls: [],
      };
      tplRow.remove();
      renderTable(ctx);
      const table = ctx.parent.closest ? ctx.parent.closest('table') : null;
      const wrap = document.createElement('div');
      wrap.className = 'qf-addrow';
      const add = document.createElement('button');
      add.type = 'button'; add.textContent = '+ Thêm dòng';
      add.onclick = () => { collectTable(ctx); ctx.rows.push({}); renderTable(ctx); };
      wrap.appendChild(add);
      if (table && table.parentNode) table.parentNode.insertBefore(wrap, table.nextSibling);
    }
    return unhandled;
  }

  /* ---- Placeholder phẳng còn lại ---- */
  function walkReplace(root, meta, tableCols, vals) {
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
    const hits = [];
    while (w.nextNode()) if (w.currentNode.nodeValue.indexOf('${') > -1) hits.push(w.currentNode);
    hits.forEach(node => {
      const parts = node.nodeValue.split(/(\$\{[a-z0-9_]+\})/i);
      if (parts.length === 1) return;
      const frag = document.createDocumentFragment();
      for (const p of parts) {
        const m = p.match(/^\$\{([a-z0-9_]+)\}$/i);
        if (!m) { frag.appendChild(document.createTextNode(p)); continue; }
        if (tableCols.has(m[1])) { frag.appendChild(document.createTextNode(p)); continue; }
        if (HIDDEN.has(m[1])) { continue; }   // ô đã cấu hình ẩn -> bỏ hẳn (để trống như bản gốc)
        frag.appendChild(mkInput(m[1], meta, vals[m[1]]));
      }
      node.parentNode.replaceChild(frag, node);
    });
  }

  /* ---- Chế độ cấu hình: mỗi ô -> chip có nút ✕ ẩn/hiện ---- */
  function updateCfgCount() {
    const el = document.getElementById('qf-cfg-count');
    if (el) el.textContent = HIDDEN.size;
  }
  function configReplace(root, meta, tableCols) {
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
    const hits = [];
    while (w.nextNode()) if (w.currentNode.nodeValue.indexOf('${') > -1) hits.push(w.currentNode);
    hits.forEach(node => {
      const parts = node.nodeValue.split(/(\$\{[a-z0-9_]+\})/i);
      if (parts.length === 1) return;
      const frag = document.createDocumentFragment();
      for (const p of parts) {
        const m = p.match(/^\$\{([a-z0-9_]+)\}$/i);
        if (!m) { frag.appendChild(document.createTextNode(p)); continue; }
        if (tableCols.has(m[1])) { frag.appendChild(document.createTextNode(p)); continue; }
        frag.appendChild(configChip(m[1], meta));
      }
      node.parentNode.replaceChild(frag, node);
    });
    updateCfgCount();
  }
  function configChip(key, meta) {
    const info = meta[key] || { type: key.startsWith('chk_') ? 'chk' : 'text', label: humanize(key) };
    const span = document.createElement('span');
    span.className = 'qf-cfg';
    if (HIDDEN.has(key)) span.classList.add('qf-cfg-hidden');
    let inp;
    if (key.startsWith('chk_')) { inp = document.createElement('input'); inp.type = 'checkbox'; inp.className = 'qf-chk'; }
    else {
      inp = document.createElement('input'); inp.type = 'text'; inp.className = 'qf-in';
      inp.placeholder = info.label || key;
      if (isSmall(key)) inp.classList.add('qf-sm');
    }
    inp.disabled = true; inp.tabIndex = -1;
    const x = document.createElement('button');
    x.type = 'button'; x.className = 'qf-x';
    x.textContent = HIDDEN.has(key) ? '＋' : '✕';
    x.title = 'Bấm để ẩn/hiện ô này';
    x.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      if (HIDDEN.has(key)) HIDDEN.delete(key); else HIDDEN.add(key);
      const h = HIDDEN.has(key);
      span.classList.toggle('qf-cfg-hidden', h);
      x.textContent = h ? '＋' : '✕';
      updateCfgCount();
    });
    span.appendChild(inp); span.appendChild(x);
    if (ADDED.has(key)) {
      span.classList.add('qf-cfg-added');
      const tr = document.createElement('button');
      tr.type = 'button'; tr.className = 'qf-trash'; tr.textContent = '🗑';
      tr.title = 'Xoá ô đã thêm này';
      tr.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        toast('Đang xoá ô…');
        if (WIRE) WIRE.removeAddedField(key);
      });
      span.appendChild(tr);
    }
    return span;
  }

  /* ---- Chế độ "Thêm ô": click vào chỗ trống trên bản gốc để đặt 1 ô nhập ---- */
  function caretFromPoint(x, y) {
    if (document.caretRangeFromPoint) { const r = document.caretRangeFromPoint(x, y); return r ? { node: r.startContainer, offset: r.startOffset } : null; }
    if (document.caretPositionFromPoint) { const p = document.caretPositionFromPoint(x, y); return p ? { node: p.offsetNode, offset: p.offset } : null; }
    return null;
  }
  function toast(msg) {
    let t = document.getElementById('qf-toast');
    if (!t) { t = document.createElement('div'); t.id = 'qf-toast'; document.body.appendChild(t); }
    t.textContent = msg; t.style.opacity = '1';
    clearTimeout(t._h); t._h = setTimeout(() => { t.style.opacity = '0'; }, 2400);
  }
  function setAddUI() {
    if (ROOT) ROOT.classList.toggle('qf-adding', ADD);
    const bi = document.getElementById('qf-add-inline'), bb = document.getElementById('qf-add-below');
    if (bi) bi.classList.toggle('on', ADD && PLACEMENT === 'inline');
    if (bb) bb.classList.toggle('on', ADD && PLACEMENT === 'below');
  }
  function toggleAdd(mode) {
    mode = (mode === 'below') ? 'below' : 'inline';
    if (ADD && PLACEMENT === mode) { ADD = false; }
    else { ADD = true; PLACEMENT = mode; }
    setAddUI();
    if (ADD) toast(PLACEMENT === 'below' ? 'Bấm vào DÒNG cần đặt ô xuống dưới (vd “CHỦ NHIỆM KHOA”).' : 'Bấm vào NGAY SAU đoạn chữ cần đặt ô.');
  }
  // Chữ tĩnh của 1 đoạn (bỏ text trong chip ✕/🗑, ô disabled) — để khớp đoạn ở file gốc
  function paraStaticText(pEl) {
    let s = ''; const w = document.createTreeWalker(pEl, NodeFilter.SHOW_TEXT, null);
    while (w.nextNode()) { const c = w.currentNode; if (c.parentElement && c.parentElement.closest('.qf-cfg')) continue; s += c.nodeValue; }
    return s.replace(/\s+/g, ' ').trim();
  }
  // Đoạn CHỮ gần nhất phía TRÊN điểm bấm (để đặt ô ở dòng dưới nó)
  function paraAbove(x, y) {
    const ps = ROOT.querySelectorAll('section.docx p');
    let best = null, bestB = -1;
    const scan = needOverlap => {
      ps.forEach(p => {
        const rc = p.getBoundingClientRect();
        if (rc.height === 0 || !paraStaticText(p)) return;
        if (rc.bottom > y + 6) return;
        if (needOverlap && (x < rc.left - 6 || x > rc.right + 6)) return;
        if (rc.bottom > bestB) { bestB = rc.bottom; best = p; }
      });
    };
    scan(true); if (!best) scan(false);
    return best;
  }
  function lastStaticText(pEl) {
    let last = ''; const wk = document.createTreeWalker(pEl, NodeFilter.SHOW_TEXT, null);
    while (wk.nextNode()) { const c = wk.currentNode; if (c.parentElement && c.parentElement.closest('.qf-cfg')) continue; if ((c.nodeValue || '').trim()) last = c.nodeValue; }
    return last;
  }
  function onAddClick(e) {
    if (!ADD) return;
    if (e.target.closest('.qf-cfg')) return;         // bấm vào chip/✕/🗑 -> để nguyên
    const r = caretFromPoint(e.clientX, e.clientY);
    const node = r && r.node;
    const txt = (node && node.nodeType === 3) ? (node.nodeValue || '') : '';

    if (PLACEMENT === 'below') {
      // DÒNG DƯỚI: neo vào đoạn đã bấm (nếu trúng chữ) hoặc đoạn gần nhất phía trên
      let pEl = (node && node.nodeType === 3 && txt.trim()) ? (node.parentElement && node.parentElement.closest('p')) : null;
      if (!pEl) pEl = paraAbove(e.clientX, e.clientY);
      if (!pEl) { toast('Hãy bấm vào một dòng có chữ để đặt ô xuống dưới nó.'); return; }
      e.preventDefault(); e.stopPropagation();
      ADD = false; setAddUI(); toast('Đang thêm ô ở dòng dưới…');
      if (WIRE) WIRE.addField({ placement: 'below', paraText: paraStaticText(pEl), nodeText: lastStaticText(pEl) });
    } else {
      // CÙNG DÒNG: chèn ô NGAY SAU vị trí bấm — cần bấm trúng chữ
      if (!txt.trim()) { toast('Hãy bấm TRÚNG đoạn chữ (chế độ cùng dòng).'); return; }
      const pEl = node.parentElement && node.parentElement.closest('p');
      if (!pEl) { toast('Không xác định được vị trí ở đây.'); return; }
      let occ = 0; const wk = document.createTreeWalker(pEl, NodeFilter.SHOW_TEXT, null);
      while (wk.nextNode()) { if (wk.currentNode === node) break; if (wk.currentNode.nodeValue === txt) occ++; }
      e.preventDefault(); e.stopPropagation();
      ADD = false; setAddUI(); toast('Đang thêm ô…');
      if (WIRE) WIRE.addField({ placement: 'inline', paraText: paraStaticText(pEl), nodeText: txt, nodeOffset: r.offset, nodeOccur: occ });
    }
  }
  function bindAddMode() {
    if (addClickBound) return; addClickBound = true;
    ROOT.addEventListener('click', onAddClick, true);   // capture để chặn trước khi caret focus
  }

  /* Vừa bề ngang màn: tờ giấy khổ cố định > màn (mobile) thì thu nhỏ cho khỏi cuộn ngang */
  let resizeBound = false;
  function fitWidth() {
    try {
      if (!ROOT) return;
      const wrap = ROOT.querySelector('.docx-wrapper') || ROOT.firstElementChild;
      const page = ROOT.querySelector('section.docx');
      if (!wrap || !page) return;
      wrap.style.zoom = '';                       // reset để đo khổ thật
      ROOT.querySelectorAll('section.docx').forEach(p => { p.style.width = ''; });
      const cs = getComputedStyle(ROOT);
      const avail = ROOT.clientWidth - parseFloat(cs.paddingLeft || 0) - parseFloat(cs.paddingRight || 0);
      // Độ rộng THẬT của toàn bộ nội dung (gồm bảng tràn ra ngoài + padding) = scrollWidth của wrapper.
      // Đo trực tiếp thay vì cộng dồn từng bảng (tránh sai số làm zoom hụt -> tràn ngang).
      const contentW = Math.max(wrap.scrollWidth, page.offsetWidth);
      // Tờ giấy NỞ đủ rộng ôm trọn nội dung -> bảng tràn không thò ra nền xám
      if (contentW > page.offsetWidth) {
        ROOT.querySelectorAll('section.docx').forEach(p => { p.style.width = contentW + 'px'; });
      }
      // Lấp đầy khung nếu vừa/gần vừa; form QUÁ RỘNG thì giữ cỡ đọc được và CHO CUỘN NGANG.
      if (contentW > 0 && avail > 0) {
        const MIN = 0.7;                 // không thu nhỏ dưới mức này
        const ideal = avail / contentW;
        if (ideal >= MIN) {
          // vừa/gần vừa -> fit khít khung (+ bù overflow theo đo thật)
          wrap.style.zoom = Math.min(1.5, ideal).toFixed(4);
          for (let i = 0; i < 2; i++) {
            const over = ROOT.scrollWidth - ROOT.clientWidth;
            if (over <= 2) break;
            const cur = parseFloat(wrap.style.zoom) || 1;
            wrap.style.zoom = (cur * ROOT.clientWidth / ROOT.scrollWidth).toFixed(4);
          }
        } else {
          // quá rộng -> giữ ~70% cỡ, phần dư cuộn ngang (qf-scroll overflow-x:auto)
          wrap.style.zoom = String(MIN);
        }
      }
    } catch (e) { /* fit chỉ là cosmetic — không bao giờ để lỗi phá form */ }
  }
  function scheduleFit() {
    const raf = window.requestAnimationFrame || (f => setTimeout(f, 16));
    raf(() => raf(fitWidth));
    setTimeout(fitWidth, 120);
    setTimeout(fitWidth, 450);
  }

  function collectAll() {
    const vals = { t: {} };
    ROOT.querySelectorAll('[data-path]').forEach(el => {
      const path = el.dataset.path;
      const v = el.type === 'checkbox' ? el.checked : el.value;
      if (path.startsWith('t.')) {
        const s = path.split('.'), tk = s[1], i = +s[2], col = s[3];
        (vals.t[tk] = vals.t[tk] || [])[i] = vals.t[tk][i] || {};
        vals.t[tk][i][col] = v;
      } else vals[path] = v;
    });
    for (const k in vals.t) vals.t[k] = vals.t[k].filter(Boolean);
    return vals;
  }

  async function init(cfg) {
    ROOT = document.getElementById(cfg.rootId);
    if (!ROOT || ROOT.dataset.qfInit) return;
    ROOT.dataset.qfInit = '1';
    WIRE = cfg.wire;
    CONFIG = !!cfg.config;
    HIDDEN = new Set(cfg.hidden || []);
    ADDED  = new Set(cfg.added || []);
    const { meta, tables, tableCols } = buildMeta(cfg.fields);
    const vals = cfg.vals || {};
    try {
      const buf = await fetch(cfg.docxUrl, { credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error('tải docx lỗi ' + r.status); return r.arrayBuffer(); });
      const holder = document.createElement('div');
      await Promise.race([
        window.docx.renderAsync(buf, holder, null, {
          className: 'docx', inWrapper: true, ignoreWidth: false, ignoreHeight: false,
          breakPages: true, experimental: true, trimXmlDeclaration: true,
          useBase64URL: true, renderHeaders: true, renderFooters: true,
        }),
        new Promise((_, rej) => setTimeout(() => rej(new Error('render quá lâu')), 15000)),
      ]);
      ROOT.innerHTML = '';
      ROOT.appendChild(holder);
      if (CONFIG) {
        // Chế độ cấu hình: bảng render bình thường, ô phẳng/tích -> chip có nút ✕; + cho phép thêm ô
        setupTables(holder, tables, vals);
        configReplace(holder, meta, tableCols);
        bindAddMode();
        setAddUI();
        if (WIRE && WIRE.on) WIRE.on('inline-changed', () => window.location.reload());   // dựng lại docx có ô mới/đã xoá
      } else {
        const unhandled = setupTables(holder, tables, vals);
        unhandled.forEach(k => tableCols.delete(k));   // bảng bố-trí-bằng-tab -> ô nhập thường
        walkReplace(holder, meta, tableCols, vals);
      }
      scheduleFit();   // đo lại nhiều lần vì docx-preview layout/nạp font xong sau khi append
      // Bảng lớn layout xong muộn -> đo lại khi kích thước bảng thay đổi (không loop vì không đổi width bảng)
      if (window.ResizeObserver) {
        let roT;
        const ro = new ResizeObserver(() => { clearTimeout(roT); roT = setTimeout(fitWidth, 80); });
        holder.querySelectorAll('section.docx table').forEach(t => ro.observe(t));
      }
      if (!resizeBound) {
        resizeBound = true;
        window.addEventListener('resize', fitWidth);
        window.addEventListener('load', fitWidth);
      }
      if (CONFIG) { return; }   // cấu hình: không gắn autosave/phím tắt
      // Tự động lưu khi gõ/tích trong tờ giấy
      ROOT.addEventListener('input', () => { markDirty(); scheduleAutosave(); });
      ROOT.addEventListener('change', () => { markDirty(); scheduleAutosave(); });
      if (!kbBound) {
        kbBound = true;
        window.addEventListener('keydown', e => {
          if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            if (WIRE) { WIRE.save(collectAll()); markSaved(); }   // Ctrl+S = lưu rõ ràng (có nhật ký)
          }
        });
        window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
      }
    } catch (e) {
      ROOT.innerHTML = '<div class="qf-err">Không hiển thị được bản gốc (' + (e && e.message || e) +
        ').<br>Biểu mẫu này hãy nhập bằng nút <b>“Dạng phiếu”</b> ở góc trên.</div>';
    }
  }

  function save() {
    if (!WIRE) return;
    if (window.QFDate && ROOT && window.QFDate.anyBad(ROOT)) {
      window.QFDate.passAll(ROOT);
      toast('Còn ô ngày/tháng/năm nhập sai (viền đỏ) — sửa lại rồi lưu.');
      return;
    }
    WIRE.save(collectAll());
  }

  function saveConfig() {
    if (WIRE) WIRE.saveConfig([...HIDDEN]);
  }

  return { init, save, saveConfig, toggleAdd };
})();
