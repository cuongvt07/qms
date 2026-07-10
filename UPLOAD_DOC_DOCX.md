# Upload .doc và .docx - Tự động convert

## ✅ Đã thêm tính năng

Hệ thống giờ đã hỗ trợ upload **cả file .doc và .docx**:

- ✅ Upload file **.docx** → xử lý trực tiếp
- ✅ Upload file **.doc** → **tự động convert** sang .docx trước khi xử lý
- ✅ LibreOffice đã được cài sẵn trong Docker container

## 🚀 Build lại Docker

Vì đã thêm LibreOffice vào Dockerfile, cần rebuild:

```bash
docker-compose build --no-cache
docker-compose up -d
```

## 🧪 Kiểm tra

Sau khi container chạy, kiểm tra LibreOffice đã cài chưa:

```bash
docker-compose exec app php artisan doc:check-converter
```

Kết quả mong đợi:
```
✅ Tìm thấy: LibreOffice
Hệ thống có thể tự động convert file .doc sang .docx
```

## 📝 Cách dùng

1. Vào trang upload form template
2. Chọn file **.doc** hoặc **.docx** 
3. Upload bình thường
4. Nếu là .doc, hệ thống sẽ:
   - Tự động convert sang .docx
   - Đọc placeholders `${...}`
   - Tạo form fields

## ⚠️ Lưu ý

- File .doc vẫn phải có placeholder `${ten_bien}` giống như file .docx
- Nếu convert thất bại, hệ thống sẽ báo lỗi và gợi ý user tự convert trong Word

## 🛠️ Files đã thay đổi

1. **Dockerfile** - Thêm LibreOffice
2. **DocConverterService.php** - Service convert .doc → .docx
3. **FormTemplateUpload.php** - Tự động convert khi upload .doc
4. **CheckDocConverter.php** - Command kiểm tra công cụ convert
