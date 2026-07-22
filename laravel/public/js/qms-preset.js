/**
 * Mẫu mặc định cho các form nhập nhiều của module QMS.
 * Dùng: QMSPreset.init({url, csrf}) khi tải trang, rồi trong hàm mở modal gọi
 *   QMSPreset.attach('batch', {host: '.batch-toolbar .push', collect, apply, skip})
 * - collect(): trả về object mô tả trạng thái hiện tại của form
 * - apply(payload): đổ dữ liệu mẫu vào form
 */
(function () {
  const P = { cfg: null, cache: {} };

  P.init = function (cfg) {
    P.cfg = cfg;
    return fetch(cfg.url, { credentials: 'same-origin' })
      .then(r => (r.ok ? r.json() : { presets: {} }))
      .then(j => { P.cache = j.presets || {}; return P.cache; })
      .catch(() => ({}));
  };

  P.get = key => P.cache[key] || null;

  P.save = function (key, payload) {
    P.cache[key] = payload;
    return fetch(P.cfg.url, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': P.cfg.csrf, 'Accept': 'application/json' },
      body: JSON.stringify({ key, payload }),
    }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); });
  };

  P.clear = function (key) {
    delete P.cache[key];
    return fetch(P.cfg.url + '?key=' + encodeURIComponent(key), {
      method: 'DELETE', credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': P.cfg.csrf, 'Accept': 'application/json' },
    }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); });
  };

  function say(msg, type) {
    if (typeof toast === 'function') toast(msg, type || ''); else console.log(msg);
  }

  /** Gắn thanh nút mặc định vào modal + tự đổ mẫu nếu có. */
  P.attach = function (key, opt) {
    const host = typeof opt.host === 'string' ? document.querySelector(opt.host) : opt.host;
    if (!host) return;
    const has = !!P.get(key);

    // đổ mẫu vào form (trừ khi đang sửa dữ liệu cũ)
    if (has && !opt.skip) {
      try { opt.apply(JSON.parse(JSON.stringify(P.get(key)))); } catch (e) { console.error(e); }
    }

    const bar = document.createElement('span');
    bar.className = 'qp-bar';
    render();
    host.appendChild(bar);

    function render() {
      const on = !!P.get(key);
      bar.innerHTML =
        (on && !opt.skip ? '<span class="qp-chip">✓ Đang dùng mẫu mặc định</span>' : '') +
        '<button type="button" class="btn sm qp-set">⭐ ' + (on ? 'Cập nhật mặc định' : 'Đặt làm mặc định') + '</button>' +
        (on ? '<button type="button" class="btn sm qp-del">✕ Bỏ mặc định</button>' : '');
      bar.querySelector('.qp-set').onclick = () => {
        let payload;
        try { payload = opt.collect(); } catch (e) { say('Không đọc được dữ liệu form', 'error'); return; }
        P.save(key, payload)
          .then(() => { say('Đã lưu làm mặc định — lần sau mở lên sẽ có sẵn'); render(); })
          .catch(e => say('Lưu mặc định thất bại: ' + e.message, 'error'));
      };
      const del = bar.querySelector('.qp-del');
      if (del) {
        del.onclick = () => P.clear(key)
          .then(() => { say('Đã bỏ mẫu mặc định'); render(); })
          .catch(e => say('Không bỏ được: ' + e.message, 'error'));
      }
    }
  };

  window.QMSPreset = P;
})();
