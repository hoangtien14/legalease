# LegalEase - Laravel 12

Bản đóng gói sạch cho dự án LegalEase, dùng MySQL/MariaDB và Laravel 12.

## Cài đặt trên XAMPP

1. Giải nén dự án thành `C:\xampp\htdocs\legalease`.
2. Tạo database MySQL tên `legalease`.
3. Copy `.env.example` thành `.env`.
4. Kiểm tra `.env`: `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=legalease`, `DB_USERNAME=root`.
5. Mở PowerShell tại thư mục dự án và chạy:

```powershell
composer install
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan serve
```

6. Mở `http://127.0.0.1:8000`.

## Tài khoản mẫu

- Admin: `admin@legalease.test` / `password`
- Luật sư: `lawyer1@legalease.test` / `password`
- Khách hàng: `customer1@legalease.test` / `password`

## Route chính

- `/` - Trang chủ
- `/lawyers` - Danh sách và tìm kiếm luật sư
- `/lawyers/{id}` - Chi tiết luật sư

Route trang chủ có cả tên `home` và `home.index` để tránh lỗi `Route [home] not defined`.

## Database

Migrations trong bản này chỉ tạo một lần cho mỗi bảng. Bảng `users` dùng `is_active`, bảng `locations` dùng `province/district/address`, bảng liên kết `lawyer_specializations` dùng khóa ngắn `lawyer_spec_unique` để tránh lỗi tên index quá dài.

## Business Logic v4
See `BUSINESS_LOGIC.md` for the complete actor, validation, authorization, appointment-state, availability, review, and notification rules.


## Logic hardening – v4

Bản v4 hiện có thêm kiểm soát trạng thái lịch hẹn, chống trùng availability, kiểm tra luật sư verified/active, quản lý slot, lọc lịch, kiểm duyệt review và nhắc lịch tự động. Xem `LOGIC_AUDIT_V4.md`.

### Sau khi copy vào XAMPP

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Nếu muốn tạo lại dữ liệu demo trên database test/local, dùng `php artisan migrate:fresh --seed`. Không chạy lệnh này trên database đang chứa dữ liệu cần giữ.

### Nhắc lịch

Scheduler đã đăng ký `appointments:remind` chạy mỗi giờ. Có thể chạy thử thủ công:

```bash
php artisan appointments:remind
```

## Final v4 logic hardening

The current v4 build includes a full business-logic hardening pass covering authentication, active-account enforcement, public lawyer visibility, specialization/location integrity, availability overlap protection, transactional booking, appointment state transitions, rescheduling, review eligibility, notification ownership, reminder deduplication, Super Admin protection, history preservation, and publication rules. See `LOGIC_AUDIT_V4_FINAL.md` for the detailed rules and test scope.

### Local final verification

From the project root:

```powershell
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
```

The last three commands should be run in the user's XAMPP/PHP environment with the required PHP extensions and MySQL connection enabled.
