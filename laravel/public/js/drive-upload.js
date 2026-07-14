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
    async add(fileList) {
      const files = [...(fileList || [])];
      const categoryId = this.$wire.get('categoryId');
      const folderId = this.$wire.get('folderId');
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
      setTimeout(() => { this.items = this.items.filter(x => x.err); }, 1500);
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
    mOpen() { const it = this.menu.item; this.closeMenu(); if (it.type === 'drive') this.$wire.openCategory(it.id); else if (it.isFolder) this.$wire.openFolder(it.id); else window.open(it.url, '_blank'); },
    mDownload() { const it = this.menu.item; this.closeMenu(); window.open(it.url + '?dl=1', '_blank'); },
    mRename() { const it = this.menu.item; this.closeMenu(); this.openDialog('rename', { title: 'Đổi tên', value: it.name, id: it.id }); },
    mNewFolder() { this.closeMenu(); this.openDialog('folder', { title: 'Thư mục mới', value: 'Thư mục mới' }); },
    mUpload() { this.closeMenu(); if (this.$refs.up) this.$refs.up.click(); },
  };
};
