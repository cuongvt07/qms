/* Kiểm tra ngày/tháng/năm gõ tay — dùng chung cho Dạng phiếu và Giống bản gốc.
   Ô cần kiểm có thuộc tính data-datekind = day | month | year | vndate. */
window.QFDate = (function () {
  function valid(kind, v) {
    v = String(v == null ? '' : v).trim();
    if (v === '') return true;                       // trống = hợp lệ (bắt buộc tính riêng)
    if (kind === 'day')   return /^\d{1,2}$/.test(v) && +v >= 1 && +v <= 31;
    if (kind === 'month') return /^\d{1,2}$/.test(v) && +v >= 1 && +v <= 12;
    if (kind === 'year')  return /^\d{4}$/.test(v)   && +v >= 1900 && +v <= 2100;
    if (kind === 'vndate') {
      var m = v.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
      if (!m) return false;
      var d = +m[1], mo = +m[2], y = +m[3];
      if (mo < 1 || mo > 12 || y < 1900 || y > 2100) return false;
      var leap = (y % 4 === 0 && (y % 100 !== 0 || y % 400 === 0));
      var dim = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      return d >= 1 && d <= dim[mo - 1];
    }
    return true;
  }
  function msg(kind) {
    return ({ day: 'Ngày 1–31', month: 'Tháng 1–12', year: 'Năm 4 chữ số (vd 2026)', vndate: 'Nhập dạng dd/mm/yyyy (vd 07/12/2026)' })[kind] || 'Không hợp lệ';
  }
  // Lọc ký tự khi gõ + tô đỏ nếu sai
  function apply(el) {
    var kind = el.getAttribute('data-datekind');
    if (!kind) return;
    var v = el.value;
    var nv = kind === 'vndate' ? v.replace(/[^\d\/]/g, '') : v.replace(/\D/g, '');
    if (nv !== v) { el.value = nv; v = nv; }
    var ok = valid(kind, v);
    el.classList.toggle('qf-bad', !ok);
    el.title = ok ? '' : msg(kind);
    return ok;
  }
  function passAll(root) { (root || document).querySelectorAll('[data-datekind]').forEach(apply); }
  // có ô nào sai không (để chặn lưu phía client nếu cần)
  function anyBad(root) {
    var bad = false;
    (root || document).querySelectorAll('[data-datekind]').forEach(function (el) { if (!valid(el.getAttribute('data-datekind'), el.value)) bad = true; });
    return bad;
  }
  document.addEventListener('input', function (e) {
    var t = e.target;
    if (t && t.getAttribute && t.getAttribute('data-datekind')) apply(t);
  }, true);
  document.addEventListener('DOMContentLoaded', function () { passAll(); });
  document.addEventListener('livewire:navigated', function () { passAll(); });
  return { valid: valid, msg: msg, apply: apply, passAll: passAll, anyBad: anyBad };
})();
