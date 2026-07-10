# QMS Forms — Hệ thống số hóa biểu mẫu động

Hệ thống quản lý chất lượng (QMS) cho phép admin upload file `.docx` → AI gợi ý schema field → admin duyệt → người dùng nhập liệu qua form HTML động.

---

## Kiến trúc

```
qms-forms/
├── laravel/              # App chính: Laravel 11 + Livewire 3
│   ├── app/
│   │   ├── Livewire/
│   │   │   ├── Admin/
│   │   │   │   ├── FormTemplateUpload.php   # Upload .docx + dispatch job
│   │   │   │   └── SchemaReview.php         # Admin tự xây dựng schema (xem raw structure bên cạnh)
│   │   │   ├── DynamicFormRenderer.php      # Render form + nhập liệu (dùng chung mọi BM)
│   │   │   └── Dashboard.php               # Dashboard nhắc việc hàng ngày
│   │   ├── Models/
│   │   │   ├── DocumentCategory.php         # Mục tài liệu (TL)
│   │   │   ├── FormTemplate.php             # Biểu mẫu (BM)
│   │   │   ├── FormTemplateVersion.php      # Schema đã duyệt, có versioning + diff
│   │   │   ├── FormSubmission.php           # 1 lần nhập liệu
│   │   │   ├── FormSubmissionRow.php        # Dữ liệu repeatable_table
│   │   │   └── DailyChecklist.php          # Nhắc việc theo user/BM/ngày
│   │   ├── Services/
│   │   │   ├── DocxExtractionService.php    # Gọi Python service
│   │   │   ├── FormValidationService.php    # Build validation rules động
│   │   │   └── DocxExportService.php        # Xuất .docx đã điền (PHPWord)
│   │   └── Jobs/
│   │       └── ProcessDocxUpload.php        # Xử lý bất đồng bộ: extract cấu trúc thô
│   └── database/migrations/                 # 7 migrations
│
├── python-service/       # Extraction service: FastAPI + python-docx
│   ├── main.py           # FastAPI app, endpoint POST /extract
│   └── extractor.py      # Đọc paragraph + tables + gridSpan + vMerge
│
└── docker-compose.yml    # 4 services: app, extract-service, db, queue-worker
```

---

## Cài đặt & chạy

### Yêu cầu
- Docker + Docker Compose
- (Dev local) PHP 8.2+, Composer, Python 3.11+, Node.js

### Chạy bằng Docker

```bash
# 1. Clone / vào thư mục
cd qms-forms

# 2. Copy .env
cp laravel/.env.example laravel/.env

# 3. Điền CLAUDE_API_KEY vào laravel/.env

# 4. Build & start
docker-compose up -d --build

# 5. Setup Laravel (chạy 1 lần)
docker exec qms_laravel php artisan key:generate
docker exec qms_laravel php artisan migrate --seed
docker exec qms_laravel php artisan storage:link
```

Truy cập: http://localhost

### Chạy local (dev)

**Python service:**
```bash
cd python-service
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

**Laravel:**
```bash
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run dev
php artisan serve
# Queue worker (terminal riêng):
php artisan queue:work
```

---

## Tài khoản mặc định (sau seed)

| Role  | Email             | Password  |
|-------|-------------------|-----------|
| Admin | admin@qms.local   | password  |
| User  | user@qms.local    | password  |

---

## Luồng sử dụng

### Admin
1. Đăng nhập → `Admin > Quản lý BM > Upload biểu mẫu mới`
2. Chọn mục TL, nhập mã BM, tên BM, upload file `.docx`
3. Hệ thống xử lý bất đồng bộ (Queue job): Python extract cấu trúc thô
4. Vào `Duyệt schema` → xem cấu trúc thô bên cạnh → **tự xây dựng field** → bấm **Xuất bản**

### Người dùng
1. Vào Dashboard → thấy danh sách BM cần nhập hôm nay
2. Nhấn **Nhập liệu** → form render động
3. Điền dữ liệu → **Lưu nháp** hoặc **Hoàn thành & Lưu**
4. Tùy chọn: **Xuất .docx** để in/lưu trữ

---

## API Python service

| Method | Endpoint   | Mô tả                           |
|--------|------------|---------------------------------|
| GET    | /health    | Health check                    |
| POST   | /extract   | Upload .docx → JSON cấu trúc thô |

---

## Database schema

```
document_categories → form_templates → form_template_versions
                                              ↓
                                      form_submissions → form_submission_rows
users → daily_checklists → form_templates
users → form_submissions
```

---

## Lộ trình phát triển

| Phase | Trạng thái | Nội dung |
|-------|-----------|----------|
| 1 | ✅ Scaffold | Migration DB, Models, CRUD cơ bản |
| 2 | ✅ Scaffold | Python extraction service |
| 3 | ✅ Scaffold | Claude API + Admin duyệt schema |
| 4 | ✅ Scaffold | DynamicFormRenderer + validate động |
| 5 | ⬜ TODO | Export .docx đã điền hoàn chỉnh |
| 6 | ⬜ TODO | Dashboard thống kê, báo cáo Đạt/Không đạt |
