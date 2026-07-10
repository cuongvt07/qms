# Hướng dẫn cài đặt công cụ convert .doc sang .docx

Hệ thống hỗ trợ upload cả file **.doc** (Word 97-2003) và **.docx** (Word 2007+). 

File .doc sẽ được **tự động convert** sang .docx trước khi xử lý.

## 🔍 Kiểm tra công cụ hiện có

Chạy lệnh sau để kiểm tra:

```bash
php artisan doc:check-converter
```

## 📦 Các phương án cài đặt

### ✅ Phương án 1: LibreOffice (Khuyến nghị)

**Tại sao chọn LibreOffice:**
- ✅ Miễn phí, mã nguồn mở
- ✅ Hỗ trợ tốt trên Windows
- ✅ Chất lượng convert cao
- ✅ Không cần cấu hình phức tạp

**Cài đặt:**

1. Download LibreOffice: https://www.libreoffice.org/download/
2. Chạy file cài đặt (mặc định cài vào `C:\Program Files\LibreOffice`)
3. Sau khi cài xong, hệ thống sẽ tự động nhận diện

**Kiểm tra:**
```bash
"C:\Program Files\LibreOffice\program\soffice.exe" --version
```

---

### 🪟 Phương án 2: Microsoft Word (COM)

**Yêu cầu:**
- ✅ Đã cài Microsoft Word trên máy
- ⚠️ Cần enable COM extension trong PHP

**Cài đặt:**

1. Mở file `php.ini` (tìm bằng lệnh `php --ini`)
2. Tìm và bỏ comment dòng sau:
   ```ini
   extension=com_dotnet
   ```
3. Restart web server (Apache/Nginx) hoặc `php artisan serve`

**Kiểm tra:**
```bash
php -m | findstr com_dotnet
```

---

### 🐍 Phương án 3: unoconv

**Lưu ý:** unoconv cần LibreOffice làm backend, nên nếu cài LibreOffice thì dùng trực tiếp LibreOffice (Phương án 1) sẽ đơn giản hơn.

---

## 🧪 Test conversion

Sau khi cài công cụ, test bằng cách:

1. Upload file .doc vào form template
2. Hệ thống sẽ tự động convert và báo thành công

## ⚠️ Nếu không cài công cụ

Hệ thống vẫn hoạt động bình thường, nhưng:
- ❌ Không thể upload file .doc
- ✅ Vẫn upload được file .docx

User sẽ cần tự convert .doc sang .docx trước:
1. Mở file .doc trong Microsoft Word
2. File → Save As
3. Chọn định dạng: **Word Document (*.docx)**
4. Upload file .docx vừa tạo

---

## 🛠️ Troubleshooting

### Lỗi: "Không thể convert file .doc sang .docx"

**Giải pháp:**
1. Chạy `php artisan doc:check-converter` để xem công cụ nào đang có
2. Nếu không có công cụ nào, cài LibreOffice (Phương án 1)
3. Nếu có LibreOffice nhưng vẫn lỗi:
   - Kiểm tra đường dẫn: `C:\Program Files\LibreOffice\program\soffice.exe` có tồn tại không
   - Thử chạy thủ công: 
     ```bash
     "C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to docx --outdir C:\temp C:\temp\test.doc
     ```

### Lỗi: COM object không khởi tạo được

**Giải pháp:**
1. Đảm bảo đã cài Microsoft Word
2. Enable COM extension trong php.ini
3. Restart web server
4. Chạy Word một lần để active license (nếu cần)

---

## 📝 Kết luận

**Khuyến nghị:** Cài **LibreOffice** (Phương án 1) - đơn giản, hiệu quả, miễn phí! 🎉
