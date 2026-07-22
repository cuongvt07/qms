/**
 * Luồng nhập liệu nối tiếp giữa các module.
 * Mỗi trang module gọi:
 *   QMSFlow.init({url:'/qms/luong', module:'env', openers:{daily:openForm, month:openMonthEntry}})
 * và gọi QMSFlow.done() ngay sau khi lưu thành công popup của bước đó.
 *
 * Quy tắc:
 *  - Vào trang có ?flow=1 (hoặc bật tự mở khi đăng nhập) -> mở popup của bước đang tới.
 *  - Đóng popup mà chưa lưu -> bỏ qua trong phiên làm việc này, bấm "Tiếp tục luồng" để mở lại.
 *  - Bước nào đã có dữ liệu hôm nay thì tự bỏ qua, nhảy sang bước chưa nhập.
 */
(function () {
  const F = { cfg: null, data: null };
  const SKIP = 'qms_flow_skip';

  const skipped = () => { try { return JSON.parse(sessionStorage.getItem(SKIP) || '[]'); } catch (e) { return []; } };
  const skip = id => { const s = skipped(); if (!s.includes(id)) { s.push(id); sessionStorage.setItem(SKIP, JSON.stringify(s)); } };
  const clearSkip = () => sessionStorage.removeItem(SKIP);

  function fetchState() {
    return fetch(F.cfg.url, { credentials: 'same-origin' })
      .then(r => (r.ok ? r.json() : null)).then(j => { F.data = j; return j; }).catch(() => null);
  }

  /** Bước tiếp theo còn phải nhập (bỏ qua bước đã tắt trong phiên này). */
  function nextStep(honorSkip) {
    if (!F.data || !F.data.steps) return null;
    const s = skipped();
    return F.data.steps.find(x => !x.done && (!honorSkip || !s.includes(x.id))) || null;
  }

  function goto(step) {
    location.href = step.url + '?flow=1';
  }

  /** Mở popup của bước nếu đang ở đúng module, ngược lại chuyển trang. */
  function run(step) {
    if (!step) { say('Hôm nay đã nhập đủ các bước trong luồng'); return; }
    if (step.module !== F.cfg.module) { goto(step); return; }
    const fn = F.cfg.openers[step.action];
    if (typeof fn !== 'function') return;
    F.current = step;
    setTimeout(() => { fn(); mountBar(step); }, 60);
  }

  function say(msg, type) {
    if (typeof toast === 'function') toast(msg, type || ''); else console.log(msg);
  }

  /** Dải nhắc bước trong modal: "Bước 2/3 · Bỏ qua bước này". */
  function mountBar(step) {
    const head = document.querySelector('#modalBox .modal-head') || document.querySelector('.modal-head');
    if (!head || head.querySelector('.qf-step')) return;
    const i = F.data.steps.findIndex(x => x.id === step.id) + 1;
    const el = document.createElement('div');
    el.className = 'qf-step';
    el.innerHTML = '<span class="qf-step-n">Luồng nhập liệu · bước ' + i + '/' + F.data.steps.length + '</span>' +
      '<button type="button" class="btn sm qf-skip">Bỏ qua bước này ›</button>';
    head.appendChild(el);
    el.querySelector('.qf-skip').onclick = () => {
      skip(step.id);
      if (typeof closeModal === 'function') closeModal();
      const n = nextStep(true);
      if (n) run(n); else say('Đã bỏ qua các bước còn lại');
    };
  }

  F.init = function (cfg) {
    F.cfg = cfg;
    const auto = new URLSearchParams(location.search).get('flow') === '1';
    return fetchState().then(d => {
      if (!d || !d.enabled) return;
      renderResume();
      if (auto || d.autoOpen) {
        const step = nextStep(true);
        if (step && (auto || step.module === cfg.module)) run(step);
      }
    });
  };

  /** Gọi sau khi lưu xong popup của bước hiện tại -> nhảy bước kế tiếp. */
  F.done = function () {
    if (!F.data || !F.data.enabled) return;
    setTimeout(() => {
      fetchState().then(() => {
        const n = nextStep(true);
        renderResume();
        if (!n) { say('Đã hoàn thành luồng nhập liệu hôm nay'); return; }
        if (n.module === F.cfg.module) { run(n); return; }
        say('Chuyển sang: ' + n.label);
        setTimeout(() => goto(n), 900);
      });
    }, 700);   // chờ module lưu xong xuống CSDL
  };

  /** Nút "Tiếp tục luồng" ở góc màn hình khi còn bước chưa nhập. */
  function renderResume() {
    let el = document.getElementById('qfResume');
    const n = nextStep(false);
    if (!n) { if (el) el.remove(); return; }
    if (!el) {
      el = document.createElement('button');
      el.id = 'qfResume';
      el.className = 'qf-resume';
      el.onclick = () => { clearSkip(); fetchState().then(() => run(nextStep(false))); };
      document.body.appendChild(el);
    }
    const rest = F.data.steps.filter(x => !x.done).length;
    el.innerHTML = '▶ Tiếp tục luồng <b>' + n.label + '</b> <span>còn ' + rest + ' bước</span>';
  }

  window.QMSFlow = F;
})();
