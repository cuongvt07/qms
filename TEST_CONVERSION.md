# Hướng dẫn test chức năng convert .doc → .docx

## 🎯 Mục tiêu

Test việc upload file .doc và tự động convert sang .docx trước khi xử lý.

## 📋 Chuẩn bị file test

### File .doc mẫu với placeholder

Tạo file **test-form.doc** trong Microsoft Word với nội dung:

```
PHIẾU ĐĂNG KÝ KIỂM TRA

Họ tên: ${ho_ten}
Ngày sinh: ${ngay_sinh}
Số điện thoại: ${so_dien_thoai}
Địa chỉ: ${dia_chi}
Nội dung kiểm tra: ${noi_dung}
Ngày đăng ký: ${ngay_dang_ky}

Người đăng ký
(Ký và ghi rõ họ tên)
```

**Lưu ý:** Phải lưu ở định dạng **.doc** (Word 97-2003 Document), không phải .docx!

## 🧪 Test case

### 1. Build và start container

```bash
# Build với LibreOffice
docker-compose build --no-cache

# Start container
docker-compose up -d

# Kiểm tra logs
docker-compose logs -f app
```

### 2. Kiểm tra LibreOffice trong container

```bash
docker-compose exec app php artisan doc:check-converter
```

**Kết quả mong đợi:**
```
🔍 Đang kiểm tra các công cụ convert .doc sang .docx...

✅ Tìm thấy: LibreOffice
Hệ thống có thể tự động convert file .doc sang .docx
```

### 3. Test upload file .doc

1. Truy cập: http://localhost:8088/admin/form-templates/create
2. Điền thông tin:
   - **Mục TL**: Chọn một category
   - **Mã BM**: TEST-DOC-01
   - **Tên BM**: Test Upload File DOC
3. Chọn file **test-form.doc** đã tạo
4. Click **Upload**

**Kết quả mong đợi:**
- ✅ File được convert tự động từ .doc → .docx
- ✅ Hệ thống đọc được 6 placeholders: ho_ten, ngay_sinh, so_dien_thoai, dia_chi, noi_dung, ngay_dang_ky
- ✅ Redirect đến trang review để chỉnh label/type
- ✅ Message: "Đã đọc TEST-DOC-01: tìm thấy 6 ô nhập. Đặt lại Nhãn/Kiểu cho dễ dùng."

### 4. Test upload file .docx

Lặp lại bước 3 nhưng dùng file **.docx** (lưu test-form.doc thành test-form.docx)

**Kết quả mong đợi:**
- ✅ Không cần convert, xử lý trực tiếp
- ✅ Kết quả giống như test .doc

### 5. Test file .doc không có placeholder

Tạo file **empty.doc** với nội dung thường (không có ${...})

Upload file này.

**Kết quả mong đợi:**
- ❌ Báo lỗi: "File chưa có placeholder nào. Mở file trong Word, gõ ${ten_bien} ..."

### 6. Test file không phải .doc/.docx

Upload file .pdf hoặc .txt

**Kết quả mong đợi:**
- ❌ Validation error: "The docx file field must be a file of type: ..."

## 🔍 Debug

### Kiểm tra file đã convert

```bash
# Vào container
docker-compose exec app bash

# List files
ls -la storage/app/docx-templates/

# Kiểm tra file convert bằng LibreOffice CLI
soffice --version
```

### Kiểm tra logs convert

Nếu convert thất bại, check logs:

```bash
docker-compose logs app | grep -i "convert"
```

### Test convert thủ công trong container

```bash
docker-compose exec app bash

# Tạo test file
echo "Test ${placeholder}" > /tmp/test.doc

# Convert
soffice --headless --convert-to docx --outdir /tmp /tmp/test.doc

# Check result
ls -la /tmp/test.docx
```

## ✅ Checklist

- [ ] LibreOffice đã cài trong container
- [ ] Command `doc:check-converter` báo thành công
- [ ] Upload file .doc có placeholder → convert OK
- [ ] Upload file .docx → xử lý trực tiếp OK
- [ ] Upload file .doc không có placeholder → báo lỗi
- [ ] Upload file không phải doc/docx → validation error
- [ ] Placeholder được đọc chính xác
- [ ] File gốc .docx được lưu vào storage (sau convert)

## 🐛 Troubleshooting

### Lỗi: "Không tìm thấy công cụ convert"

**Nguyên nhân:** LibreOffice chưa được cài trong container

**Giải pháp:**
1. Check Dockerfile có dòng: `libreoffice-writer libreoffice-core`
2. Rebuild: `docker-compose build --no-cache`

### Lỗi: "Cannot convert .doc to .docx"

**Nguyên nhân:** File .doc bị lỗi hoặc không đúng định dạng

**Giải pháp:**
1. Mở file trong Word
2. Save As → chọn **.docx**
3. Upload file .docx

### LibreOffice timeout

**Nguyên nhân:** File quá lớn hoặc phức tạp

**Giải pháp:**
1. Tăng timeout trong DocConverterService
2. Hoặc chia nhỏ file
