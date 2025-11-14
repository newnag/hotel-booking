# 📐 เอกสารการออกแบบระบบจองห้องประชุม

## 📖 สารบัญ

1. [ภาพรวมระบบ](#ภาพรวมระบบ)
2. [สถาปัตยกรรมระบบ](#สถาปัตยกรรมระบบ)
3. [ฐานข้อมูล](#ฐานข้อมูล)
4. [API Design](#api-design)
5. [User Interface Design](#user-interface-design)
6. [Security](#security)
7. [Performance](#performance)
8. [Deployment](#deployment)

---

## ภาพรวมระบบ

### วัตถุประสงค์

ระบบจองห้องประชุมอัจฉริยะ (Smart Meeting Room Booking System) ออกแบบมาเพื่อ:
- อำนวยความสะดวกในการจองห้องประชุมแบบออนไลน์
- ลดความซับซ้อนในการจัดการห้องประชุม
- เพิ่มประสิทธิภาพการใช้งานห้องประชุม
- แจ้งเตือนอัตโนมัติผ่าน LINE
- รายงานและสถิติการใช้งานแบบเรียลไทม์

### คุณสมบัติหลัก

**สำหรับผู้ใช้ทั่วไป (Guest):**
- ค้นหาห้องว่างตามเวลาและจำนวนผู้เข้าร่วม
- จองห้องประชุมออนไลน์
- เลือกธีมการตกแต่งห้อง
- ดูประวัติการจอง
- รับการแจ้งเตือนผ่าน LINE
- ยกเลิกการจอง

**สำหรับเจ้าหน้าที่ (Staff):**
- ดูและจัดการการจองทั้งหมด
- ยืนยัน/ยกเลิกการจอง
- ดูปฏิทินการจองแบบรวม
- ดูสถานะห้องประชุมแบบเรียลไทม์

**สำหรับผู้ดูแลระบบ (Admin):**
- จัดการห้องประชุม (เพิ่ม/แก้ไข/ลบ)
- จัดการผู้ใช้และสิทธิ์การเข้าถึง
- จัดการธีมการตกแต่ง
- ดูรายงานและสถิติการใช้งาน
- ตั้งค่าระบบ
- ตั้งค่า LINE Notification

### เทคโนโลยีที่ใช้

**Backend:**
- **Framework:** Laravel 12.x (PHP 8.2+)
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Breeze (Session-based)
- **Queue:** Database Queue Driver (Sync for development)

**Frontend:**
- **Template Engine:** Blade
- **CSS Framework:** Bootstrap 5.x
- **Admin Template:** AdminLTE 3.x
- **JavaScript:** Vanilla JS, Alpine.js (for interactivity)
- **Build Tool:** Vite

**Third-Party Services:**
- **LINE Messaging API:** Push notifications
- **LINE Login API:** OAuth 2.0 authentication

**Server:**
- **Web Server:** LiteSpeed / Apache / Nginx
- **PHP:** 8.2+
- **Timezone:** Asia/Bangkok

---

## สถาปัตยกรรมระบบ

### System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  Web Browser (Chrome, Firefox, Edge, Safari)                    │
│  - Guest Interface (Booking, Profile)                           │
│  - Staff Interface (Management, Calendar)                       │
│  - Admin Interface (System Settings)                            │
│  - Room Status Display (Public)                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │ HTTPS
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Presentation Layer                          │
├─────────────────────────────────────────────────────────────────┤
│  Web Server (LiteSpeed/Apache/Nginx)                            │
│  - Route Handling                                                │
│  - Session Management                                            │
│  - Static Assets (CSS, JS, Images)                              │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Application Layer                           │
├─────────────────────────────────────────────────────────────────┤
│  Laravel Framework                                               │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ Controllers                                                │ │
│  │  - AuthController                                          │ │
│  │  - Guest/BookingController                                 │ │
│  │  - Guest/DashboardController                               │ │
│  │  - Admin/RoomController                                    │ │
│  │  - Admin/UserController                                    │ │
│  │  - LineAuthController                                      │ │
│  └───────────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ Services                                                   │ │
│  │  - BookingService                                          │ │
│  │  - RoomAvailabilityService                                 │ │
│  │  - LineNotificationService                                 │ │
│  └───────────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ Models (Eloquent ORM)                                      │ │
│  │  - User                                                    │ │
│  │  - MeetingRoom                                             │ │
│  │  - Booking                                                 │ │
│  │  - LineNotification                                        │ │
│  └───────────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ Middleware                                                 │ │
│  │  - Authentication (auth, verified)                         │ │
│  │  - RoleMiddleware (admin, staff, guest)                    │ │
│  │  - CheckBookingOwnership                                   │ │
│  └───────────────────────────────────────────────────────────┘ │
└────────────────────────┬────────────────────────────────────────┘
                         │
            ┌────────────┴────────────┐
            ▼                         ▼
┌──────────────────────┐   ┌──────────────────────┐
│   Data Layer         │   │  External Services   │
├──────────────────────┤   ├──────────────────────┤
│  MySQL Database      │   │  LINE Platform       │
│  - users             │   │  - LINE Login API    │
│  - meeting_rooms     │   │  - Messaging API     │
│  - bookings          │   │                      │
│  - line_notifications│   │                      │
│  - sessions          │   │                      │
│  - jobs              │   │                      │
│  - cache             │   │                      │
└──────────────────────┘   └──────────────────────┘
```

### Component Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         Frontend Components                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Guest UI   │  │  Staff UI    │  │  Admin UI    │         │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤         │
│  │ - Search     │  │ - Dashboard  │  │ - Rooms Mgmt │         │
│  │ - Booking    │  │ - Bookings   │  │ - Users Mgmt │         │
│  │ - History    │  │ - Calendar   │  │ - Settings   │         │
│  │ - Profile    │  │ - Room Status│  │ - Reports    │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              Shared Components                            │  │
│  │  - Navigation, Footer, Modals, Forms, Tables             │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        Backend Components                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                     Core Services                         │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  BookingService                                           │  │
│  │  - createBooking()                                        │  │
│  │  - cancelBooking()                                        │  │
│  │  - isRoomAvailable()                                      │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  RoomAvailabilityService                                  │  │
│  │  - searchAvailableRooms()                                 │  │
│  │  - checkAvailability()                                    │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  LineNotificationService                                  │  │
│  │  - sendBookingConfirmation()                              │  │
│  │  - sendBookingCancellation()                              │  │
│  │  - sendWelcomeMessage()                                   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                 Event & Job System                        │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  Events                                                   │  │
│  │  - BookingCreated                                         │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  Listeners                                                │  │
│  │  - SendLineNotification                                   │  │
│  ├──────────────────────────────────────────────────────────┤  │
│  │  Jobs                                                     │  │
│  │  - SendLineNotificationJob                                │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Data Flow

**การจองห้องประชุม:**

```
1. User เข้าหน้าค้นหาห้อง
   ↓
2. กรอกข้อมูล (จำนวนคน, วันที่, เวลา)
   ↓
3. BookingController::searchResults()
   ↓
4. RoomAvailabilityService::searchAvailableRooms()
   ↓
5. Query Database (MeetingRoom + Booking)
   ↓
6. แสดงห้องที่ว่าง
   ↓
7. User เลือกห้องและกรอกรายละเอียด
   ↓
8. BookingController::store()
   ↓
9. BookingService::createBooking()
   ├─ Validate availability
   ├─ Create Booking record
   └─ Dispatch BookingCreated event
   ↓
10. SendLineNotification listener
    ↓
11. SendLineNotificationJob (Queue)
    ├─ Guest: sendBookingConfirmation()
    └─ Staff: sendBookingCreatedNotification()
    ↓
12. LineNotificationService::sendLineMessage()
    ↓
13. LINE Messaging API
    ↓
14. User receives LINE notification
```

**LINE Account Linking:**

```
1. User clicks "Connect with LINE"
   ↓
2. LineAuthController::redirect()
   ↓
3. Redirect to LINE Login (OAuth 2.0)
   ↓
4. User authorizes on LINE
   ↓
5. LINE redirects back with authorization code
   ↓
6. LineAuthController::callback()
   ↓
7. Exchange code for access token
   ↓
8. Get LINE user profile
   ↓
9. Save line_user_id to database
   ↓
10. Send welcome message
    ↓
11. Redirect to dashboard with success message
```

---

## ฐานข้อมูล

### Entity Relationship Diagram (ERD)

```
┌─────────────────────┐
│       users         │
├─────────────────────┤
│ id (PK)             │
│ name                │
│ email (unique)      │
│ password            │
│ role (enum)         │◄─────────┐
│ phone               │          │
│ line_user_id        │          │
│ line_linked_at      │          │
│ email_verified_at   │          │
│ deleted_at          │          │
│ created_at          │          │
│ updated_at          │          │
└─────────────────────┘          │
         │                       │
         │                       │
         │ 1:N                   │
         ▼                       │
┌─────────────────────┐          │
│      bookings       │          │
├─────────────────────┤          │
│ id (PK)             │          │
│ booking_ref (unique)│          │
│ user_id (FK)        │──────────┘
│ room_id (FK)        │──────────┐
│ start_datetime      │          │
│ end_datetime        │          │
│ attendee_count      │          │
│ decoration_theme    │          │
│ status (enum)       │          │
│ notes               │          │
│ created_at          │          │
│ updated_at          │          │
└─────────────────────┘          │
         │                       │
         │ 1:N                   │
         ▼                       │
┌─────────────────────┐          │
│ line_notifications  │          │
├─────────────────────┤          │
│ id (PK)             │          │
│ booking_id (FK)     │          │
│ recipient_id (FK)   │──────────┤
│ recipient_type      │          │
│ notification_type   │          │
│ message             │          │
│ status (enum)       │          │
│ error_message       │          │
│ sent_at             │          │
│ created_at          │          │
│ updated_at          │          │
└─────────────────────┘          │
                                 │
         ┌───────────────────────┘
         │ 1:N
         ▼
┌─────────────────────┐
│   meeting_rooms     │
├─────────────────────┤
│ id (PK)             │
│ name                │
│ max_capacity        │
│ location            │
│ description         │
│ status (enum)       │
│ deleted_at          │
│ created_at          │
│ updated_at          │
└─────────────────────┘

┌─────────────────────┐
│      sessions       │
├─────────────────────┤
│ id (PK)             │
│ user_id (FK)        │
│ ip_address          │
│ user_agent          │
│ payload             │
│ last_activity       │
└─────────────────────┘

┌─────────────────────┐
│        jobs         │
├─────────────────────┤
│ id (PK)             │
│ queue               │
│ payload             │
│ attempts            │
│ reserved_at         │
│ available_at        │
│ created_at          │
└─────────────────────┘

┌─────────────────────┐
│        cache        │
├─────────────────────┤
│ key (PK)            │
│ value               │
│ expiration          │
└─────────────────────┘
```

### Database Tables

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'staff', 'admin') DEFAULT 'guest',
    phone VARCHAR(20) NULL,
    line_user_id VARCHAR(255) NULL UNIQUE,
    line_linked_at TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_line_user_id (line_user_id)
);
```

#### meeting_rooms
```sql
CREATE TABLE meeting_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    max_capacity INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_capacity (max_capacity)
);
```

#### bookings
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    attendee_count INT NOT NULL,
    decoration_theme VARCHAR(100) NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES meeting_rooms(id) ON DELETE CASCADE,
    INDEX idx_booking_ref (booking_ref),
    INDEX idx_user_id (user_id),
    INDEX idx_room_id (room_id),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_status (status),
    INDEX idx_datetime_range (start_datetime, end_datetime)
);
```

#### line_notifications
```sql
CREATE TABLE line_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    recipient_type VARCHAR(50) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_status (status)
);
```

---

## API Design

### Authentication

**Session-based authentication** using Laravel Breeze

```
POST /register
POST /login
POST /logout
POST /forgot-password
POST /reset-password
POST /email/verification-notification
GET  /verify-email/{id}/{hash}
```

### LINE Integration

```
GET  /line/redirect       - Redirect to LINE Login
GET  /line/callback       - Handle OAuth callback
POST /line/unlink         - Unlink LINE account
```

### Guest Routes (Authenticated)

**Dashboard:**
```
GET /dashboard            - Guest dashboard
```

**Booking:**
```
GET  /guest/booking/search              - Search form
GET  /guest/booking/results             - Search results
GET  /guest/booking/create/{room}       - Booking form
POST /guest/booking                     - Create booking
GET  /guest/booking/history             - Booking history
GET  /guest/booking/{id}                - Booking details
POST /guest/booking/{id}/cancel         - Cancel booking
```

**Profile:**
```
GET    /profile           - Profile page
PATCH  /profile           - Update profile
DELETE /profile           - Delete account
```

### Staff Routes (Authenticated + Role)

```
GET /staff/dashboard                    - Staff dashboard
GET /staff/bookings                     - All bookings
GET /staff/bookings/{id}                - Booking details
POST /staff/bookings/{id}/confirm       - Confirm booking
POST /staff/bookings/{id}/cancel        - Cancel booking
POST /staff/bookings/{id}/complete      - Mark as completed
GET /staff/calendar                     - Calendar view
```

### Admin Routes (Authenticated + Role)

**Rooms:**
```
GET    /admin/rooms                - List rooms
GET    /admin/rooms/create         - Create form
POST   /admin/rooms                - Store room
GET    /admin/rooms/{id}/edit      - Edit form
PUT    /admin/rooms/{id}           - Update room
DELETE /admin/rooms/{id}           - Delete room
```

**Users:**
```
GET    /admin/users                - List users
GET    /admin/users/{id}/edit      - Edit form
PUT    /admin/users/{id}           - Update user
POST   /admin/users/{id}/suspend   - Suspend user
```

**Settings:**
```
GET  /admin/settings               - Settings page
POST /admin/settings               - Save settings
```

**Reports:**
```
GET  /admin/reports/rooms          - Room usage report
GET  /admin/reports/bookings       - Booking report
POST /admin/reports/export         - Export data
```

### Public Routes

```
GET /                              - Welcome page
GET /room-status                   - All rooms status board
GET /room-status/{id}              - Single room status
GET /api/room-status               - API: All rooms status (JSON)
GET /api/room-status/{id}          - API: Single room status (JSON)
```

### Request/Response Examples

**Create Booking:**

Request:
```http
POST /guest/booking
Content-Type: application/x-www-form-urlencoded

room_id=1
&attendee_count=10
&start_datetime=2025-11-15 09:00:00
&end_datetime=2025-11-15 11:00:00
&decoration_theme=business
&notes=Important meeting
```

Response (Redirect):
```http
302 Found
Location: /guest/booking/{id}
Session: success="Booking created successfully! Reference: BK-20251115-001"
```

**Search Available Rooms:**

Request:
```http
GET /guest/booking/results
  ?attendee_count=20
  &start_datetime=2025-11-15 14:00:00
  &end_datetime=2025-11-15 16:00:00
```

Response (HTML):
```html
<!-- Blade view with available rooms -->
```

**Room Status API:**

Request:
```http
GET /api/room-status
Accept: application/json
```

Response:
```json
{
  "rooms": [
    {
      "id": 1,
      "name": "Conference Room A",
      "capacity": 50,
      "location": "Building A, 2F",
      "status": "available",
      "current_booking": null,
      "next_booking": {
        "start_datetime": "2025-11-15 14:00:00",
        "end_datetime": "2025-11-15 16:00:00",
        "user": "John Doe"
      }
    },
    {
      "id": 2,
      "name": "Meeting Room B",
      "capacity": 20,
      "location": "Building A, 3F",
      "status": "occupied",
      "current_booking": {
        "start_datetime": "2025-11-15 09:00:00",
        "end_datetime": "2025-11-15 11:00:00",
        "user": "Jane Smith"
      },
      "next_booking": null
    }
  ],
  "timestamp": "2025-11-15T10:30:00+07:00"
}
```

---

## User Interface Design

### Design Principles

1. **ความเรียบง่าย (Simplicity)**: UI ออกแบบให้ใช้งานง่าย เข้าใจได้ทันที
2. **ความสอดคล้อง (Consistency)**: ใช้ pattern เดียวกันทั้งระบบ
3. **การตอบสนอง (Responsiveness)**: รองรับทุกขนาดหน้าจอ
4. **การเข้าถึง (Accessibility)**: ออกแบบให้ทุกคนใช้งานได้

### Color Scheme

**Primary Colors:**
- Primary: `#007bff` (Blue)
- Success: `#28a745` (Green)
- Warning: `#ffc107` (Yellow)
- Danger: `#dc3545` (Red)
- Info: `#17a2b8` (Cyan)

**LINE Brand:**
- LINE Green: `#00B900`
- LINE Green Light: `#00C300`

**Status Colors:**
- Available: `#28a745` (Green)
- Reserved: `#17a2b8` (Blue)
- Starting Soon: `#ffc107` (Yellow)
- Occupied: `#dc3545` (Red)
- Cancelled: `#6c757d` (Gray)

### Typography

- **Font Family:** 
  - Thai: "Sarabun", "Noto Sans Thai", sans-serif
  - English: "Roboto", "Helvetica Neue", sans-serif
- **Headings:** Bold, 1.5-2.5rem
- **Body:** Regular, 1rem
- **Small Text:** 0.875rem

### Layout Structure

**Guest Layout:**
```
┌─────────────────────────────────────────────┐
│              Navigation Bar                  │
│  [Logo] [Home] [Search] [History] [Profile] │
└─────────────────────────────────────────────┘
│                                              │
│              Main Content                    │
│  ┌──────────────────────────────────────┐  │
│  │                                       │  │
│  │         Page Content                  │  │
│  │                                       │  │
│  └──────────────────────────────────────┘  │
│                                              │
└─────────────────────────────────────────────┘
│              Footer                          │
│  © 2025 Smart Meeting Room Booking System   │
└─────────────────────────────────────────────┘
```

**Admin Layout (AdminLTE):**
```
┌─────────────────────────────────────────────┐
│              Top Navigation                  │
│  [Logo] [Toggle] ... [User] [Notifications] │
├─────────┬───────────────────────────────────┤
│         │                                    │
│ Side    │      Main Content                 │
│ bar     │  ┌─────────────────────────────┐  │
│         │  │                              │  │
│ [Menu1] │  │      Page Content            │  │
│ [Menu2] │  │                              │  │
│ [Menu3] │  └─────────────────────────────┘  │
│         │                                    │
└─────────┴───────────────────────────────────┘
```

### UI Components

**Search Form:**
- Date/Time pickers
- Number input for attendees
- Quick select buttons (5, 10, 20, 50 people)
- Duration quick select (1, 2, 3, 4 hours)

**Room Cards:**
- Room image/icon
- Room name and capacity
- Location
- Status badge
- Book button

**Booking Timeline:**
- Visual timeline with color-coded events
- Hover effects for details
- Click to view full details

**Status Board:**
- Grid layout for multiple rooms
- Large, clear status indicators
- Auto-refresh every 30 seconds
- Full-screen mode option

**LINE Connection Card:**
- Gradient background (LINE brand colors)
- Connection status
- Connect/Disconnect button
- Benefits list
- LINE User ID display

---

## Security

### Authentication & Authorization

**Authentication:**
- Session-based authentication (Laravel Breeze)
- Email verification required
- Password requirements: minimum 8 characters
- CSRF protection on all forms
- Remember me functionality

**Authorization:**
- Role-based access control (RBAC)
- Roles: admin, staff, guest
- Middleware protection on routes
- Policy-based resource authorization

**Password Security:**
- Hashed using Bcrypt (default rounds: 12)
- Password reset via email token
- Token expiration: 60 minutes

### LINE OAuth Security

**OAuth 2.0 Flow:**
- State parameter for CSRF protection
- Nonce parameter for ID token validation
- Secure token exchange
- Session-based state storage

**API Security:**
- LINE Channel Secret stored in environment variables
- HTTPS required for OAuth callback
- Token validation on every request

### Data Protection

**Encryption:**
- Sensitive data encrypted at rest
- HTTPS for data in transit
- Environment variables for secrets

**Database Security:**
- Prepared statements (Eloquent ORM)
- SQL injection prevention
- User input validation and sanitization

**Session Security:**
- Secure session cookies (httpOnly, secure, sameSite)
- Session lifetime: 120 minutes
- Session regeneration on login

### Input Validation

**Form Validation:**
- Server-side validation (Laravel Request classes)
- Client-side validation (HTML5 + JavaScript)
- Sanitization of user inputs

**File Upload:**
- File type validation
- File size limits
- Virus scanning (if applicable)

### Rate Limiting

**API Rate Limits:**
- Login attempts: 5 per minute
- Password reset: 2 per minute
- API requests: 60 per minute per user

### Logging & Monitoring

**Security Logs:**
- Failed login attempts
- Permission denials
- Suspicious activities
- API errors

**Monitoring:**
- Laravel log files (storage/logs/laravel.log)
- Database query logging (in development)
- Error tracking

---

## Performance

### Optimization Strategies

**Database Optimization:**
- Proper indexing on frequently queried columns
- Eager loading to prevent N+1 queries
- Query result caching
- Connection pooling

**Caching:**
- **Config cache:** `php artisan config:cache`
- **Route cache:** `php artisan route:cache`
- **View cache:** `php artisan view:cache`
- **Database cache:** For frequently accessed data

**Asset Optimization:**
- Vite for asset bundling and minification
- CSS/JS minification
- Image optimization
- Lazy loading for images

**Code Optimization:**
- Composer autoload optimization: `--optimize-autoloader`
- OPcache enabled (PHP)
- Queue jobs for time-consuming tasks

### Performance Targets

- **Page Load Time:** < 2 seconds
- **Time to Interactive:** < 3 seconds
- **Database Query Time:** < 100ms
- **API Response Time:** < 200ms

### Monitoring

**Metrics to Track:**
- Response time
- Database query performance
- Memory usage
- CPU usage
- Error rates

**Tools:**
- Laravel Telescope (development)
- Laravel Debugbar (development)
- Server monitoring tools (production)

---

## Deployment

### Server Requirements

**Minimum Requirements:**
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer 2.x
- Node.js 18+ and npm
- Web Server (LiteSpeed/Apache/Nginx)
- 512 MB RAM (minimum)
- 1 GB Disk Space

**Recommended:**
- PHP 8.3
- MySQL 8.0+
- 2 GB RAM
- 5 GB Disk Space
- SSD Storage

### Environment Setup

**Production Environment Variables:**
```env
APP_NAME="ระบบจองห้องประชุม"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kankoon.ddstudioex.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=prod_user
DB_PASSWORD=secure_password

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

LINE_CHANNEL_ID=xxxxx
LINE_CHANNEL_SECRET=xxxxx
LINE_CHANNEL_ACCESS_TOKEN=xxxxx
```

### Deployment Steps

**1. Server Setup:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip

# Install MySQL
sudo apt install mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

**2. Application Deployment:**
```bash
# Clone repository
cd /var/www
git clone https://github.com/yourusername/hotel-booking.git
cd hotel-booking

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Setup environment
cp .env.example .env
nano .env  # Edit configuration

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Setup storage
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

**3. Web Server Configuration:**

**LiteSpeed:**
```apache
<VirtualHost *:80>
    ServerName kankoon.ddstudioex.com
    DocumentRoot /var/www/hotel-booking/public

    <Directory /var/www/hotel-booking/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**4. Optimization:**
```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# General optimization
php artisan optimize
```

**5. SSL Setup (Let's Encrypt):**
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d kankoon.ddstudioex.com
```

### Continuous Deployment

**Deployment Script (deploy.sh):**
```bash
#!/bin/bash

echo "🚀 Starting deployment..."

# Maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache

# End maintenance mode
php artisan up

echo "✅ Deployment completed!"
```

### Backup Strategy

**Database Backup:**
```bash
# Daily backup
mysqldump -u user -p database_name > backup_$(date +%Y%m%d).sql

# Automated backup (crontab)
0 2 * * * /path/to/backup-script.sh
```

**File Backup:**
- Storage files
- Environment files
- Configuration files

### Monitoring & Maintenance

**Regular Tasks:**
- Monitor error logs: `tail -f storage/logs/laravel.log`
- Check disk space: `df -h`
- Monitor server resources: `htop`
- Database optimization: Weekly
- Security updates: Monthly

**Health Checks:**
- Application status: `/up`
- Database connectivity
- External API availability (LINE)

---

## Scalability Considerations

### Horizontal Scaling

**Load Balancing:**
- Multiple web server instances
- Database read replicas
- Redis for session sharing

**Queue Workers:**
- Separate queue worker servers
- Multiple workers for parallel processing
- Failed job handling

### Vertical Scaling

**Database:**
- Increase MySQL resources
- Optimize queries and indexes
- Partitioning for large tables

**Application:**
- Increase PHP memory limit
- Optimize OPcache settings
- More CPU cores for concurrent requests

### Caching Strategy

**Levels:**
1. **OPcache:** PHP bytecode caching
2. **Application Cache:** Config, routes, views
3. **Database Cache:** Query results
4. **CDN:** Static assets (future)

---

## Future Enhancements

### Planned Features

1. **Calendar Integration:**
   - Google Calendar sync
   - Outlook Calendar sync
   - iCal export

2. **Advanced Notifications:**
   - Email notifications
   - SMS notifications
   - Push notifications

3. **Mobile Application:**
   - iOS app
   - Android app
   - Progressive Web App (PWA)

4. **Analytics Dashboard:**
   - Room utilization charts
   - User behavior analytics
   - Booking trends

5. **Recurring Bookings:**
   - Weekly meetings
   - Monthly meetings
   - Custom recurrence patterns

6. **Resource Management:**
   - Equipment booking (projector, whiteboard)
   - Catering requests
   - Room setup preferences

7. **Integration APIs:**
   - Public API for third-party integrations
   - Webhooks for external systems
   - SSO (Single Sign-On)

---

## Appendix

### Glossary

- **Booking Reference:** รหัสอ้างอิงการจอง (เช่น BK-20251115-001)
- **Decoration Theme:** ธีมการตกแต่งห้องประชุม
- **Guest:** ผู้ใช้งานทั่วไป (บทบาทพื้นฐาน)
- **Staff:** เจ้าหน้าที่ (สามารถจัดการการจอง)
- **Admin:** ผู้ดูแลระบบ (มีสิทธิ์เต็ม)
- **LINE User ID:** รหัสผู้ใช้ LINE (เช่น U1234567890abcdef...)
- **OAuth 2.0:** โปรโตคอลการยืนยันตัวตนแบบเปิด

### References

- Laravel Documentation: https://laravel.com/docs
- Bootstrap Documentation: https://getbootstrap.com/docs
- AdminLTE Documentation: https://adminlte.io/docs
- LINE Developers: https://developers.line.biz/
- LINE Messaging API: https://developers.line.biz/en/docs/messaging-api/
- LINE Login API: https://developers.line.biz/en/docs/line-login/

### Version History

| Version | Date       | Author    | Changes                          |
|---------|------------|-----------|----------------------------------|
| 1.0.0   | 2025-11-13 | Dev Team  | Initial system design document   |

---

**Document Information:**
- **Version:** 1.0.0
- **Last Updated:** November 13, 2025
- **Authors:** Development Team
- **Status:** Final

---

© 2025 Smart Meeting Room Booking System. All rights reserved.
