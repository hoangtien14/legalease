# LegalEase v4 – Demo Data

Dữ liệu của `DemoEProject/js/data.js` đã được chuyển từ localStorage mock sang MySQL thông qua `DatabaseSeeder`.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Super Admin | admin@legalease.com | admin123 |
| Luật sư | lawyer@legalease.com | 123456 |
| Khách hàng | customer@legalease.com | 123456 |

Ngoài ra seeder tạo thêm các tài khoản luật sư và khách hàng trong bản demo.

## Nạp lại toàn bộ dữ liệu demo

```bash
php artisan migrate:fresh --seed
```

> Lệnh trên sẽ xóa dữ liệu hiện có trong database `legalease` rồi tạo lại cấu trúc và dữ liệu demo.

## Nội dung được chuyển

- 3 nhóm quyền: super_admin, lawyer, customer
- 31 users
- 10 hồ sơ luật sư
- 20 hồ sơ khách hàng
- 4 chuyên ngành pháp luật
- 5 khu vực/địa điểm
- 15 lịch hẹn mẫu
- Availability slots tương ứng với lịch hẹn
- 5 tin tức/thông báo pháp lý
- 6 câu hỏi thường gặp

Dữ liệu vẫn chạy bằng Laravel Eloquent/MySQL, không dùng localStorage cho nghiệp vụ chính.
