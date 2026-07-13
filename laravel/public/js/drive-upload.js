/* Upload chunk (chia mảnh) cho ổ tài liệu — tải file lớn, chịu mạng yếu (retry từng mảnh). */
window.QFDrive = (function () {
  const CHUNK = 4 * 1024 * 1024;   // 4MB / mảnh

  async function sendChunk(ctx, id, index, total, blob) {
    let tries = 0;
    while (true) {
      try {
        const fd = new FormData();
        fd.append('upload_id', id);
        fd.append('index', index);
        fd.append('total', total);
        fd.append('chunk', blob, 'c');
        const r = await fetch(ctx.chunkUrl, {
          method: 'POST', credentials: 'same-origin',
          headers: { 'X-CSRF-TOKEN': ctx.csrf }, body: fd,
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return;
      } catch (e) {
        if (++tries >= 4) throw e;
        await new Promise(s => setTimeout(s, 500 * tries));   // chờ tăng dần rồi thử lại
      }
    }
  }

  async function uploadOne(file, ctx, onProgress) {
    const id = 'u' + String(file.size) + '_' + (file.lastModified || 0) + '_' + Math.round(performance.now()) + '_' + (file.name.length);
    const total = Math.max(1, Math.ceil(file.size / CHUNK));
    for (let i = 0; i < total; i++) {
      const blob = file.slice(i * CHUNK, Math.min(file.size, (i + 1) * CHUNK));
      await sendChunk(ctx, id, i, total, blob);
      onProgress(Math.min(0.99, (i + 1) / total));
    }
    const fd = new FormData();
    fd.append('upload_id', id);
    fd.append('total', total);
    fd.append('name', file.name);
    fd.append('category_id', ctx.categoryId);
    if (ctx.folderId) fd.append('folder_id', ctx.folderId);
    fd.append('mime', file.type || '');
    const r = await fetch(ctx.finalizeUrl, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': ctx.csrf }, body: fd,
    });
    if (!r.ok) throw new Error('Ghép file lỗi (' + r.status + ')');
    onProgress(1);
  }

  return { uploadOne };
})();

/* Thành phần Alpine quản lý hàng đợi + thanh tiến độ, gọi khi chọn/kéo-thả file. */
window.driveUploader = function (categoryId, folderId, csrf, chunkUrl, finalizeUrl) {
  return {
    over: false,
    items: [],           // {name, pct, err}
    busy: false,
    async add(fileList) {
      const files = [...(fileList || [])];
      if (!files.length || !categoryId) return;
      const ctx = { categoryId, folderId, csrf, chunkUrl, finalizeUrl };
      const rows = files.map(f => ({ name: f.name, pct: 0, err: false }));
      this.items.push(...rows);
      this.busy = true;
      for (let k = 0; k < files.length; k++) {
        const row = rows[k];
        try {
          await window.QFDrive.uploadOne(files[k], ctx, p => { row.pct = Math.round(p * 100); });
        } catch (e) {
          row.err = true; row.msg = (e && e.message) || 'lỗi';
        }
      }
      this.busy = false;
      // tải xong -> làm mới lưới
      if (this.$wire) this.$wire.$refresh();
      // dọn các dòng đã xong sau 1.5s (giữ lại dòng lỗi)
      setTimeout(() => { this.items = this.items.filter(x => x.err); }, 1500);
    },
  };
};
