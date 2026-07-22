/**
 * Biến <select> thành ô chọn có tìm kiếm (kiểu select2) — thuần JS, không cần thư viện ngoài.
 * Giữ nguyên thẻ <select> gốc nên mọi onchange/value của code cũ vẫn chạy bình thường.
 *
 *   QMSSelect.enhance(document);        // nâng cấp các select hiện có
 *   QMSSelect.auto();                   // tự nâng cấp cả những select được render sau
 */
(function () {
  const S = {};
  const bỏDấu = s => String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

  function label(sel) {
    const o = sel.options[sel.selectedIndex];
    return o ? o.textContent.trim() : '';
  }

  function build(sel) {
    if (sel.multiple || sel.dataset.qs2 || sel.dataset.noQs2 !== undefined) return;
    if (sel.options.length < 5) return;              // ít lựa chọn thì không cần tìm kiếm
    sel.dataset.qs2 = '1';

    const wrap = document.createElement('div');
    wrap.className = 'qs2';
    sel.parentNode.insertBefore(wrap, sel);
    wrap.appendChild(sel);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'qs2-btn';

    const pop = document.createElement('div');
    pop.className = 'qs2-pop';
    pop.innerHTML = '<input class="qs2-search" type="text" placeholder="Gõ để tìm…" autocomplete="off">' +
                    '<div class="qs2-list"></div>';
    wrap.appendChild(btn);
    wrap.appendChild(pop);

    const search = pop.querySelector('.qs2-search');
    const list = pop.querySelector('.qs2-list');
    let hi = -1, items = [];

    const paintBtn = () => {
      const t = label(sel);
      btn.innerHTML = '<span class="' + (t ? '' : 'qs2-ph') + '">' + (t || 'Chọn…') + '</span><i>▾</i>';
      btn.disabled = sel.disabled;
    };

    function paintList() {
      const q = bỏDấu(search.value);
      items = [...sel.options]
        .map((o, i) => ({ i, text: o.textContent.trim(), val: o.value }))
        .filter(o => !q || bỏDấu(o.text).includes(q));
      if (!items.length) { list.innerHTML = '<div class="qs2-empty">Không có kết quả</div>'; return; }
      list.innerHTML = items.map((o, k) =>
        '<div class="qs2-opt' + (o.i === sel.selectedIndex ? ' sel' : '') + (k === hi ? ' hi' : '') +
        '" data-i="' + o.i + '">' + o.text + '</div>').join('');
      list.querySelectorAll('.qs2-opt').forEach(el => {
        el.onmousedown = e => { e.preventDefault(); pick(Number(el.dataset.i)); };
      });
      const h = list.querySelector('.qs2-opt.hi') || list.querySelector('.qs2-opt.sel');
      if (h) h.scrollIntoView({ block: 'nearest' });
    }

    function pick(i) {
      sel.selectedIndex = i;
      sel.dispatchEvent(new Event('input', { bubbles: true }));
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      close();
      paintBtn();
    }

    function open() {
      document.querySelectorAll('.qs2.open').forEach(w => w !== wrap && w.classList.remove('open'));
      wrap.classList.add('open');
      search.value = ''; hi = -1; paintList();
      // mở lên trên nếu gần đáy màn hình
      const r = btn.getBoundingClientRect();
      wrap.classList.toggle('up', window.innerHeight - r.bottom < 260);
      setTimeout(() => search.focus(), 0);
    }
    const close = () => wrap.classList.remove('open');

    btn.onclick = () => (wrap.classList.contains('open') ? close() : open());
    search.oninput = () => { hi = 0; paintList(); };
    search.onkeydown = e => {
      if (e.key === 'Escape') { close(); btn.focus(); return; }
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        hi = Math.max(0, Math.min(items.length - 1, hi + (e.key === 'ArrowDown' ? 1 : -1)));
        paintList(); return;
      }
      if (e.key === 'Enter') {
        e.preventDefault();
        const o = items[hi >= 0 ? hi : 0];
        if (o) pick(o.i);
      }
    };

    sel.addEventListener('change', paintBtn);
    // code cũ có thể thay đổi options bằng innerHTML -> vẽ lại nhãn
    new MutationObserver(paintBtn).observe(sel, { childList: true, subtree: true });
    paintBtn();
  }

  S.enhance = root => (root || document).querySelectorAll('select').forEach(build);

  S.auto = function () {
    S.enhance(document);
    let t = null;
    new MutationObserver(() => { clearTimeout(t); t = setTimeout(() => S.enhance(document), 40); })
      .observe(document.body, { childList: true, subtree: true });
    document.addEventListener('mousedown', e => {
      if (!e.target.closest('.qs2')) document.querySelectorAll('.qs2.open').forEach(w => w.classList.remove('open'));
    });
  };

  window.QMSSelect = S;
})();
