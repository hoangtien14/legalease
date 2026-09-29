# LegalEase v4 – Logic Audit & bổ sung

## Đã bổ sung

1. **Booking an toàn**
   - Chỉ luật sư đã `verified` và tài khoản đang active mới nhận lịch.
   - Khóa availability slot trong transaction để giảm race condition khi hai người cùng đặt.
   - Kiểm tra slot đúng luật sư, còn trống, chưa quá giờ.
   - Chuyên ngành phải thuộc luật sư và đang active.

2. **Luồng trạng thái lịch hẹn**
   - `pending -> confirmed/cancelled`
   - `confirmed -> completed/cancelled/rescheduled`
   - `rescheduled -> confirmed/completed/cancelled`
   - Không cho chuyển từ `completed` hoặc `cancelled`.
   - Không xác nhận lịch đã quá giờ.
   - Không hoàn thành lịch chưa tới giờ.
   - Không hủy lịch đã bắt đầu.
   - Hủy lịch sẽ trả slot về `available`.

3. **Đổi lịch**
   - Chỉ đổi sang slot của cùng luật sư, còn trống và ở tương lai.
   - Slot mới phải khác slot cũ.
   - Không cho khách hàng có hai lịch active cùng thời điểm.
   - Slot cũ được trả lại sau khi đổi.

4. **Availability của luật sư**
   - Không cho tạo/sửa slot bị trùng thời gian.
   - Không sửa slot đã được đặt.
   - Có thể khóa/mở lại slot chưa được đặt.
   - Không xóa slot đã đặt.

5. **Tài khoản & phân quyền**
   - Login chỉ cho tài khoản active.
   - Middleware kiểm tra lại `is_active`, tránh session cũ tiếp tục truy cập sau khi Admin khóa tài khoản.
   - Super Admin chỉ truy cập khi đúng role và active.

6. **Đánh giá**
   - Chỉ khách hàng sở hữu lịch mới được đánh giá.
   - Chỉ đánh giá sau `completed`.
   - Mỗi lịch chỉ có một đánh giá.
   - Rating 1–5.
   - Super Admin có trang ẩn/hiện đánh giá.

7. **Thông báo**
   - Có thông báo khi đặt lịch, xác nhận, hủy, đổi lịch và đánh giá.
   - Có nút đánh dấu tất cả đã đọc.
   - Có command `appointments:remind` và scheduler chạy hàng giờ để nhắc lịch trong 24 giờ tới.

8. **Dữ liệu kinh nghiệm luật sư**
   - Bổ sung `experience_years` dạng số để Search/Sort/Report hoạt động đúng theo số năm.
   - Giữ `experience` để lưu mô tả chi tiết.

9. **Customer/Lawyer/Admin**
   - Customer: lọc lịch theo trạng thái + khoảng ngày.
   - Lawyer: lọc lịch, quản lý slot, khóa/mở slot.
   - Super Admin: lọc lịch, quản lý đánh giá, quản lý user/lawyer/content/report.

## Còn nên hoàn thiện trước khi nộp

- Chạy `php artisan migrate` trên máy XAMPP thật.
- Chạy PHPUnit sau khi PHP có đủ `dom`, `mbstring`, `xmlwriter`.
- Kiểm tra lại toàn bộ màn hình bằng trình duyệt.
- Hoàn thiện tài liệu EProject: Use Case, DFD, Flowchart, ERD, Test Cases, Installation Guide, SQL script, ReadMe và demo video.
