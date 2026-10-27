# PHÂN TÍCH HIỆN TRẠNG & KẾ HOẠCH TRIỂN KHAI TIME SYNCHRONIZATION (NTP) TRÊN OTSA

---

## 1. HIỆN TRẠNG MÃ NGUỒN OTSA

### 1.1. Cơ chế vận hành hiện tại
- **Mã nguồn liên quan:**
  - Web GUI: `src/www/services_ntpd.php` (Menu: *Network Services > Time Synchronization > General*).
  - Backend Engine: `src/etc/inc/plugins.inc.d/ntpd.inc` (sinh cấu hình `/var/etc/ntpd.conf` và quản lý daemon FreeBSD `ntpd`).
  - Giám sát: `src/opnsense/scripts/ntpd/ntpd_status.php` (lấy dữ liệu từ `ntpq -pnw` hiển thị lên `/ui/ntpd/status`).
- **Nguyên lý hoạt động:**
  - OTSA sử dụng daemon chuẩn `ntpd` của FreeBSD.
  - Tự động lấy thời gian từ các Upstream NTP Servers (`$config['system']['timeservers']`).
  - Lắng nghe trên các interface được chọn (`$config['ntpd']['interface']`) để cấp thời gian qua cổng UDP 123.
  - Tích hợp Kea DHCPv4 Option 42 (`192.168.10.1`, `192.168.20.1`) để tự động gán địa chỉ NTP Gateway xuống máy trạm IT/OT.

### 1.2. Đánh giá tính phù hợp & Bất cập

| Tiêu chí | Đánh giá | Chi tiết thực tế trong mã nguồn & kiến trúc |
| :--- | :---: | :--- |
| **Thuật toán đồng bộ đa nguồn** | **Phù hợp** | `ntpd` dùng thuật toán Marzullo tự động lọc falseticker (nguồn sai lệch) và tổng hợp truechimer. Tùy chọn `prefer` đóng vai trò trọng số gợi ý, không ép cứng một nguồn duy nhất. |
| **Dự phòng ngắt mạng (Orphan mode)** | **Phù hợp** | Khi đứt WAN, `tos orphan 12` duy trì đồng hồ nội bộ OTSA để tiếp tục giữ nhịp thời gian nhất quán cho PLC/SCADA trong mạng cô lập. |
| **Phân định vai trò Client / Server** | **Chưa phù hợp** | Giao diện hiện tại chưa thể hiện rõ OTSA đang đóng vai trò gì. Tùy chọn `clientmode` (chạy `ntpd -q`) nếu vô tình bật sẽ làm tắt daemon ngay sau khi sync, dẫn đến **sập dịch vụ cấp giờ cho toàn bộ mạng OT/IT**. |
| **Hệ thống Help / Trợ giúp Web UI** | **Chưa phù hợp** | Nút gạt **Full Help** (`#show_all_help_page`) bị vô hiệu hóa ở hầu hết các trường nâng cao do dùng icon tĩnh (`text-muted`), thiếu thẻ giải thích `.showhelp` và `div.hidden`. Quản trị viên không nắm được ý nghĩa và rủi ro của các cờ bảo mật. |

---

## 2. KẾ HOẠCH TRIỂN KHAI (DÀN Ý & HƯỚNG GIẢI QUYẾT)

### 2.1. Hướng giải quyết trọng tâm
1. **Chuẩn hóa kiến trúc thời gian:** Khẳng định OTSA là **Stratum 2 Local Authoritative NTP Server** cho toàn bộ vùng OT/IT/DMZ. Không tạo trường "Root Time = 0" (gây hiểu nhầm về Epoch và phá vỡ cơ chế redundancy của NTP).
2. **Khuyến nghị cấu hình nguồn:** Cấu hình $\ge 3$ upstream servers bên ngoài (hoặc 1 nguồn GPS/GNSS chuyên dụng cho OT) để thuật toán Marzullo hoạt động tối ưu.
3. **Bổ sung Full Help UI:** Cập nhật `services_ntpd.php`, biến các icon tĩnh thành liên kết trợ giúp tương tác, tích hợp script help tiếng Anh súc tích cho 100% các trường trong Advanced mode.
4. **Kiểm thử liên vùng:** Xác thực luồng đồng bộ từ Client IT/OT qua UDP 123 và khả năng duy trì giờ của Orphan mode khi rút cáp WAN.

### 2.2. Dàn ý các bước thực hiện
- **Bước 1: Cập nhật giao diện `services_ntpd.php`**
  - Gắn class `showhelp` và id trợ giúp tương ứng cho các trường nâng cao.
  - Nhúng các khối `<div class="hidden" data-for="...">` chứa script help chuẩn hóa.
  - Bổ sung đường dẫn nhanh từ trang cấu hình sang trang kiểm tra trạng thái `/ui/ntpd/status`.
- **Bước 2: Xác thực cú pháp và logic cấu hình**
  - Kiểm tra tính tương thích của mảng cấu hình `$config['ntpd']` trong `ntpd.inc`.
  - Đảm bảo các cờ bảo mật đảo ngược (`empty($config['ntpd'][...])`) trong PHP không bị xáo trộn.
- **Bước 3: Kiểm thử thực nghiệm trên Lab OTSA**
  - Kiểm tra Web UI: bật toggle Full Help, xác nhận hiển thị trợ giúp đầy đủ.
  - Kiểm tra file `/var/etc/ntpd.conf` trên máy ảo OTSA.
  - Sử dụng máy trạm `Client-LAN-IT` và `Client-LAN-OT` chạy lệnh truy vấn (`ntpdate -q` / `sntp`).
  - Kiểm tra tính năng Orphan Mode khi ngắt kết nối card mạng WAN (`vtnet0`).

---

## 3. BẢNG TỔNG HỢP MENU HELP CẦN BỔ SUNG (ADVANCED MODE)

> Toàn bộ các trường dưới đây hiện đang ở dạng icon tĩnh (`<i class="fa fa-info-circle text-muted"></i>`) hoặc thiếu thẻ mô tả chi tiết khi kích hoạt chế độ **Full Help**.

| Trường cấu hình (Field Name) | Vị trí trên UI | Mã biến cấu hình | Script Help đề xuất (Tiếng Anh súc tích) |
| :--- | :--- | :--- | :--- |
| **Client mode** | General Form | `clientmode` | Synchronize time once at boot and exit immediately (`ntpd -q`). **Warning:** Disables continuous time keeping and prevents OTSA from serving time to local IT/OT clients. Keep unchecked for NTP server operation. |
| **NTP graphs** | General Form | `statsgraph` | Enable continuous generation of RRD performance graphs for NTP clock offset, frequency drift, and jitter. |
| **Clock stats** | Statistics Logging (Advanced) | `clockstats` | Record reference clock driver statistics to daily log files in `/var/log/ntp/clockstats.YYYYMMDD`. Useful for GPS/PPS diagnostic. |
| **Loop stats** | Statistics Logging (Advanced) | `loopstats` | Record internal clock discipline and frequency adjustment statistics to daily log files in `/var/log/ntp/loopstats.YYYYMMDD`. |
| **Peer stats** | Statistics Logging (Advanced) | `peerstats` | Record individual peer status, offset, delay, and jitter to `/var/log/ntp/peerstats.YYYYMMDD`. Helps identify erratic upstream servers. |
| **Kiss-o'-death (KoD)** | Access Restrictions (Advanced) | `kod` | Send Kiss-o'-Death packets to rate-limited or misbehaving clients, requesting them to immediately reduce query frequency. |
| **Rate limiting** | Access Restrictions (Advanced) | `limited` | Enforce query rate limiting on incoming client requests to protect against NTP amplification and DoS attacks. |
| **Deny modifications** | Access Restrictions (Advanced) | `nomodify` | Reject runtime state and configuration modifications requested via `ntpq` and `ntpdc` utilities. Highly recommended for security. |
| **Disable queries** | Access Restrictions (Advanced) | `noquery` | Prevent external clients from querying server status and peer associations via `ntpq`/`ntpdc`. Reduces reconnaissance exposure. |
| **Disable time serving** | Access Restrictions (Advanced) | `noserve` | Reject all normal NTP time sync queries while still allowing informational queries. **Warning:** Local clients will not be able to synchronize time if enabled. |
| **Deny peer association** | Access Restrictions (Advanced) | `nopeer` | Reject packets that attempt to mobilize dynamic or ephemeral peer associations. Protects against rogue peering. |
| **Deny trap service** | Access Restrictions (Advanced) | `notrap` | Deny NTP Mode 6 trap logging service, preventing unwanted remote logging and notification bindings. |
| **Leap seconds** | Leap seconds (Advanced) | `leapsec` | Provide NIST leap-seconds raw definition table. Only applicable if OTSA operates as a Stratum 1 reference server to broadcast leap second schedule. |
| **Custom options** | Advanced Options | `custom_options` | Instate raw custom directives directly into `/var/etc/ntpd.conf`. Use with caution as syntax errors can cause ntpd service startup failure. |
