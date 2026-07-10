import './bootstrap';

// Lưu ý: KHÔNG import/khởi động Alpine ở đây.
// Livewire 3 đã đóng gói sẵn Alpine và tự gọi Alpine.start().
// Nếu import Alpine lần nữa sẽ gây double-Alpine -> lỗi UploadManager
// khi upload file (Cannot read properties of undefined (reading 'name')).
