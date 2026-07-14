/* Ổ tài liệu: upload chunk (chia mảnh) + menu chuột phải + dialog. */
window.QFDrive = (function () {
  const CHUNK = 4 * 1024 * 1024;   // 4MB / mảnh

  async function sendChunk(ctx, id, index, total, blob) {
    let tries = 0;
    while (true) {
      try {
        const fd = new FormData();
        fd.append('upload_id', id); fd.append('index', index);
        fd.append('total', total); fd.append('chunk', blob, 'c');
        const r = await fetch(ctx.chunkUrl, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': ctx.csrf }, body: fd });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return;
      } catch (e) {
        if (++tries >= 4) throw e;
        await new Promise(s => setTimeout(s, 500 * tries));
      }
    }
  }

  async function uploadOne(file, ctx, onProgress) {
    const id = 'u' + String(file.size) + '_' + (file.lastModified || 0) + '_' + Math.round(performance.now()) + '_' + (file.name.length);
    const total = Math.max(1, Math.ceil(file.size / CHUNK));
    for (let i = 0; i < total; i++) {
      await sendChunk(ctx, id, i, total, file.slice(i * CHUNK, Math.min(file.size, (i + 1) * CHUNK)));
      onProgress(Math.min(0.99, (i + 1) / total));
    }
    const fd = new FormData();
    fd.append('upload_id', id); fd.append('total', total); fd.append('name', file.name);
    fd.append('category_id', ctx.categoryId);
    if (ctx.folderId) fd.append('folder_id', ctx.folderId);
    fd.append('mime', file.type || '');
    const r = await fetch(ctx.finalizeUrl, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': ctx.csrf }, body: fd });
    if (!r.ok) throw new Error('Ghép file lỗi (' + r.status + ')');
    onProgress(1);
  }

  return { uploadOne };
})();

/* Alpine: quản lý upload + menu chuột phải + dialog cho cả trang ổ tài liệu.
   Đọc categoryId/folderId từ $wire (luôn mới sau khi điều hướng thư mục). */
window.driveApp = function (csrf, chunkUrl, finalizeUrl) {
  return {
    // ---- Upload ----
    over: false, items: [], busy: false,
    // opts: {folderId, categoryId} — để kéo-thả vào 1 thư mục/ổ cụ thể; không có thì dùng vị trí hiện tại.
    async add(fileList, opts) {
      opts = opts || {};
      const files = [...(fileList || [])];
      const categoryId = (opts.categoryId != null) ? opts.categoryId : this.$wire.get('categoryId');
      const folderId = ('folderId' in opts) ? opts.folderId : this.$wire.get('folderId');
      if (!files.length || !categoryId) return;
      const ctx = { categoryId, folderId, csrf, chunkUrl, finalizeUrl };
      const rows = files.map(f => ({ name: f.name, pct: 0, err: false }));
      this.items.push(...rows); this.busy = true;
      for (let k = 0; k < files.length; k++) {
        try { await window.QFDrive.uploadOne(files[k], ctx, p => { rows[k].pct = Math.round(p * 100); }); }
        catch (e) { rows[k].err = true; }
      }
      this.busy = false;
      if (this.$wire) this.$wire.$refresh();
      setTimeout(() => { this.items = this.items.filter(x => x.err); }, 2500);
    },
    uploadTitle() {
      const total = this.items.length;
      const done = this.items.filter(x => x.pct >= 100 || x.err).length;
      return done >= total ? ('Đã xong ' + total + ' tệp') : ('Đang tải ' + done + '/' + total + ' tệp…');
    },

    // ---- Menu chuột phải ----
    menu: { show: false, x: 0, y: 0, type: 'blank', item: null, mobile: false },
    openMenu(e, type, item) { if (e && e.preventDefault) e.preventDefault(); this.openMenuAt((e && e.clientX) || 40, (e && e.clientY) || 40, type, item); },
    openMenuAt(x, y, type, item) {
      const mobile = window.innerWidth < 640;
      this.menu = { show: true, x: 0, y: 0, type, item, mobile };
      if (mobile) return;   // điện thoại: bottom-sheet, khỏi định vị theo con trỏ
      this.$nextTick(() => {
        const el = this.$refs.menu; if (!el) return;
        this.menu.x = Math.min(x, window.innerWidth - el.offsetWidth - 8);
        this.menu.y = Math.min(y, window.innerHeight - el.offsetHeight - 8);
      });
    },
    closeMenu() { this.menu.show = false; },

    // Chạm-giữ (mobile) mở menu
    lpTimer: null, lpXY: { x: 0, y: 0 },
    lpStart(e, type, item) { const t = e.touches ? e.touches[0] : e; this.lpXY = { x: t.clientX, y: t.clientY }; clearTimeout(this.lpTimer); this.lpTimer = setTimeout(() => this.openMenuAt(this.lpXY.x, this.lpXY.y, type, item), 500); },
    lpCancel() { clearTimeout(this.lpTimer); },

    // ---- Dialog (tạo/đổi tên) ----
    dlg: { show: false, mode: '', title: '', label: '', value: '', id: null },
    openDialog(mode, opts) {
      opts = opts || {};
      this.dlg = { show: true, mode, title: opts.title || '', label: opts.label || 'Tên', value: opts.value || '', id: opts.id || null };
      this.$nextTick(() => { if (this.$refs.dlgInput) { this.$refs.dlgInput.focus(); this.$refs.dlgInput.select(); } });
    },
    submitDialog() {
      const v = (this.dlg.value || '').trim();
      if (v) {
        if (this.dlg.mode === 'folder') this.$wire.createFolder(v);
        else if (this.dlg.mode === 'rename') this.$wire.renameNode(this.dlg.id, v);
        else if (this.dlg.mode === 'drive') this.$wire.createDrive(v);
      }
      this.dlg.show = false;
    },

    // ---- Confirm xoá ----
    conf: { show: false, item: null },
    askDelete(item) { this.closeMenu(); this.conf = { show: true, item }; },
    doDelete() { if (this.conf.item) this.$wire.deleteNode(this.conf.item.id); this.conf.show = false; },

    // ---- Hành động từ menu ----
    mOpen() { const it = this.menu.item; this.closeMenu(); if (it.type === 'drive') this.$wire.openCategory(it.id); else if (it.isFolder) this.$wire.openFolder(it.id); else this.openPreview(it); },

    // ---- Xem file online (như Google Drive) ----
    preview: { show: false, name: '', url: '', kind: '', mime: '' },
    openPreview(f) {
      this.preview = { show: true, name: f.name || '', url: f.url || '', kind: f.kind || '', mime: f.mime || '' };
      this.$nextTick(() => this.renderPreview());
    },
    closePreview() { this.preview.show = false; if (this.$refs.pvbody) this.$refs.pvbody.innerHTML = ''; },
    async renderPreview() {
      const body = this.$refs.pvbody; if (!body) return;
      const { url, name, mime } = this.preview;
      const ext = (String(name).split('.').pop() || '').toLowerCase();
      body.innerHTML = '<div style="padding:2rem;text-align:center;color:#9ca3af">Đang tải…</div>';
      try {
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext) || (mime && mime.startsWith('image/'))) {
          body.innerHTML = ''; const img = new Image(); img.src = url;
          img.style = 'display:block;margin:auto;max-width:100%;height:auto'; body.appendChild(img); return;
        }
        if (ext === 'pdf' || (mime && mime.includes('pdf'))) {
          body.innerHTML = '<iframe src="' + url + '" style="width:100%;height:100%;border:0;background:#fff"></iframe>'; return;
        }
        if (ext === 'docx' || ext === 'doc') {
          if (!window.docx) { throw new Error('Chưa nạp trình xem Word'); }
          const buf = await fetch(url, { credentials: 'same-origin' }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.arrayBuffer(); });
          body.innerHTML = '<div class="qf-pv-doc" style="background:#fff;padding:16px"></div>';
          await window.docx.renderAsync(buf, body.querySelector('.qf-pv-doc'), null, { inWrapper: true, className: 'docx', breakPages: true, ignoreWidth: false });
          return;
        }
        if (['txt', 'csv', 'log', 'md', 'json', 'xml'].includes(ext)) {
          const t = await fetch(url, { credentials: 'same-origin' }).then(r => r.text());
          const pre = document.createElement('pre');
          pre.style = 'padding:1rem;white-space:pre-wrap;word-break:break-word;font-size:13px;background:#fff;margin:0;min-height:100%';
          pre.textContent = t; body.innerHTML = ''; body.appendChild(pre); return;
        }
        body.innerHTML = '<div style="padding:2.5rem;text-align:center;color:#6b7280">Không xem trực tiếp được loại tệp này.<br><a href="' + url + '?dl=1" style="color:#0d9488;text-decoration:underline">Tải xuống</a></div>';
      } catch (e) {
        body.innerHTML = '<div style="padding:2.5rem;text-align:center;color:#ef4444">Không mở được tệp (' + ((e && e.message) || e) + ').<br><a href="' + url + '?dl=1" style="color:#0d9488;text-decoration:underline">Tải xuống</a></div>';
      }
    },
    mDownload() { const it = this.menu.item; this.closeMenu(); window.open(it.url + '?dl=1', '_blank'); },
    mRename() { const it = this.menu.item; this.closeMenu(); this.openDialog('rename', { title: 'Đổi tên', value: it.name, id: it.id }); },
    mNewFolder() { this.closeMenu(); this.openDialog('folder', { title: 'Thư mục mới', value: 'Thư mục mới' }); },
    mUpload() { this.closeMenu(); if (this.$refs.up) this.$refs.up.click(); },
  };
};
