# LegalEase - Authentication

Đã bổ sung đăng ký, đăng nhập và đăng xuất cho khách hàng.

Routes:
- GET /login
- POST /login
- GET /register
- POST /register
- POST /logout

Đăng ký tự động tạo role `customer` và `customer_profile`.
Chỉ tài khoản `is_active = true` được đăng nhập.

Tài khoản seed:
customer@legalease.test / 12345678
lawyer1@legalease.test / 12345678
lawyer2@legalease.test / 12345678
lawyer3@legalease.test / 12345678
lawyer4@legalease.test / 12345678

Lệnh:
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan route:list
php artisan serve
