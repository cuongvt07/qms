/**
 * Hiển thị mọi ô ngày theo định dạng dd/mm/yyyy (thay vì mm/dd/yyyy của trình duyệt).
 * Giữ nguyên thẻ <input type="date"> gốc (ẩn đi, vẫn mang giá trị ISO yyyy-mm-dd)
 * nên toàn bộ code cũ đọc .value hay bắt sự kiện change đều chạy y như trước.
 *
 *   QMSDate.auto();   // nâng cấp cả những ô được render sau
 */
(function () {
  const D = {};

  const toVi = iso => (/^\d{4}-\d{2}-\d{2}$/.test(iso || '')
    ? iso.slice(8, 10) + '/' + iso.slice(5, 7) + '/' + iso.slice(0, 4) : '');

  /** dd/mm/yyyy -> yyyy-mm-dd (rỗng nếu ngày không hợp lệ). */
  const toIso = v => {
    const m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(String(v || '').trim());
    if (!m) return '';
    const d = +m[1], mo = +m[2], y = +m[3];
    if (mo < 1 || mo > 12 || d < 1 || d > new Date(y, mo, 0).getDate()) return '';
    return y + '-' + String(mo).padStart(2, '0') + '-' + String(d).padStart(2, '0');
  };

  function build(inp) {
    if (inp.dataset.qd2 || inp.dataset.noQd2 !== undefined) return;
    inp.dataset.qd2 = '1';

    const wrap = document.createElement('span');
    wrap.className = 'qd2';
    inp.parentNode.insertBefore(wrap, inp);
    wrap.appendChild(inp);
    inp.classList.add('qd2-native');

    const txt = document.createElement('input');
    txt.type = 'text';
    txt.className = 'qd2-txt ' + (inp.className.replace('qd2-native', '').trim());
    txt.placeholder = 'dd/mm/yyyy';
    txt.inputMode = 'numeric';
    txt.maxLength = 10;
    if (inp.disabled) txt.disabled = true;

    const cal = document.createElement('button');
    cal.type = 'button';
    cal.className = 'qd2-cal';
    cal.tabIndex = -1;
    cal.title = 'Chọn từ lịch';
    cal.textContent = '🗓';

    wrap.appendChild(txt);
    wrap.appendChild(cal);

    const paint = () => { txt.value = toVi(inp.value); };

    /** Đẩy giá trị từ ô hiển thị xuống ô gốc + báo cho code cũ. */
    function push() {
      const iso = toIso(txt.value);
      if (txt.value.trim() === '') {
        if (inp.value === '') return;
        inp.value = '';
      } else if (!iso) {
        txt.classList.add('qd2-bad');
        return;
      } else {
        if (inp.value === iso) { txt.classList.remove('qd2-bad'); return; }
        inp.value = iso;
      }
      txt.classList.remove('qd2-bad');
      inp.dispatchEvent(new Event('input', { bubbles: true }));
      inp.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // gõ số tự chèn dấu /
    txt.addEventListener('input', () => {
      let v = txt.value.replace(/[^\d/]/g, '');
      const digits = v.replace(/\D/g, '').slice(0, 8);
      if (!v.includes('/') || v.length < txt.dataset.prev?.length) {
        v = digits.length > 4 ? digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4)
          : digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2)
            : digits;
      }
      txt.value = v;
      txt.dataset.prev = v;
      if (v.length === 10) push();
    });
    txt.addEventListener('blur', push);
    txt.addEventListener('keydown', e => { if (e.key === 'Enter') { push(); } });

    cal.addEventListener('click', () => {
      try { inp.showPicker(); } catch (e) { inp.focus(); inp.click(); }
    });
    inp.addEventListener('change', paint);

    paint();
  }

  D.enhance = root => (root || document).querySelectorAll('input[type="date"]').forEach(build);

  D.auto = function () {
    D.enhance(document);
    let t = null;
    new MutationObserver(() => { clearTimeout(t); t = setTimeout(() => D.enhance(document), 40); })
      .observe(document.body, { childList: true, subtree: true });
  };

  window.QMSDate = D;
})();
