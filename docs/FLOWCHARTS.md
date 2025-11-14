# 🔄 Flowcharts - ระบบจองห้องประชุม

## 📖 สารบัญ

1. [การสมัครสมาชิกและเข้าสู่ระบบ](#1-การสมัครสมาชิกและเข้าสู่ระบบ)
2. [การจองห้องประชุม](#2-การจองห้องประชุม)
3. [การเชื่อมต่อบัญชี LINE](#3-การเชื่อมต่อบัญชี-line)
4. [การส่งการแจ้งเตือน LINE](#4-การส่งการแจ้งเตือน-line)
5. [การยกเลิกการจอง](#5-การยกเลิกการจอง)
6. [การจัดการการจอง (Staff)](#6-การจัดการการจอง-staff)
7. [การตรวจสอบความพร้อมของห้อง](#7-การตรวจสอบความพร้อมของห้อง)
8. [ระบบ Queue และ Job Processing](#8-ระบบ-queue-และ-job-processing)

---

## 1. การสมัครสมาชิกและเข้าสู่ระบบ

### 1.1 User Registration Flow

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌─────────────────────┐
│ User visits         │
│ /register page      │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Fill registration   │
│ form:               │
│ - Name              │
│ - Email             │
│ - Password          │
│ - Confirm Password  │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Submit form         │
└─────────┬───────────┘
          │
          ▼
     ┌────────┐
     │Validate│
     │ Input? │
     └───┬────┘
         │
    ┌────┴────┐
    │         │
   No        Yes
    │         │
    │         ▼
    │    ┌─────────────────────┐
    │    │ Check email unique? │
    │    └─────────┬───────────┘
    │              │
    │         ┌────┴────┐
    │         │         │
    │        No        Yes
    │         │         │
    │         │         ▼
    │         │    ┌─────────────────────┐
    │         │    │ Create user record  │
    │         │    │ - Hash password     │
    │         │    │ - Default role:guest│
    │         │    └─────────┬───────────┘
    │         │              │
    │         │              ▼
    │         │    ┌─────────────────────┐
    │         │    │ Send verification   │
    │         │    │ email               │
    │         │    └─────────┬───────────┘
    │         │              │
    │         │              ▼
    │         │    ┌─────────────────────┐
    │         │    │ Auto-login user     │
    │         │    │ Create session      │
    │         │    └─────────┬───────────┘
    │         │              │
    │         │              ▼
    │         │    ┌─────────────────────┐
    │         │    │ Redirect to         │
    │         │    │ /dashboard          │
    │         │    └─────────┬───────────┘
    │         │              │
    ▼         ▼              ▼
┌─────────────────────┐  ┌─────┐
│ Show error message  │  │ END │
│ Return to form      │  └─────┘
└─────────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

### 1.2 User Login Flow

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌─────────────────────┐
│ User visits         │
│ /login page         │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Enter credentials:  │
│ - Email             │
│ - Password          │
│ - Remember me?      │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Submit form         │
└─────────┬───────────┘
          │
          ▼
     ┌────────────┐
     │Credentials │
     │ Valid?     │
     └─────┬──────┘
           │
      ┌────┴────┐
      │         │
     No        Yes
      │         │
      │         ▼
      │    ┌─────────────────────┐
      │    │ Email verified?     │
      │    └─────────┬───────────┘
      │              │
      │         ┌────┴────┐
      │         │         │
      │        No        Yes
      │         │         │
      │         │         ▼
      │         │    ┌─────────────────────┐
      │         │    │ Create session      │
      │         │    │ Regenerate session  │
      │         │    │ ID (security)       │
      │         │    └─────────┬───────────┘
      │         │              │
      │         │              ▼
      │         │    ┌─────────────────────┐
      │         │    │ Check user role     │
      │         │    └─────────┬───────────┘
      │         │              │
      │         │      ┌───────┼───────┐
      │         │      │       │       │
      │         │    Admin   Staff  Guest
      │         │      │       │       │
      │         │      ▼       ▼       ▼
      │         │    /admin  /staff  /dashboard
      │         │      │       │       │
      │         │      └───────┴───────┘
      │         │              │
      │         │              ▼
      │         │         ┌─────┐
      │         │         │ END │
      │         │         └─────┘
      │         ▼
      │    ┌─────────────────────┐
      │    │ Redirect to email   │
      │    │ verification notice │
      │    └─────────────────────┘
      │              │
      ▼              ▼
┌─────────────────────┐
│ Show error:         │
│ Invalid credentials │
│ Return to login     │
└─────────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

---

## 2. การจองห้องประชุม

### 2.1 Complete Booking Flow

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌─────────────────────┐
│ User clicks         │
│ "Search Rooms"      │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────────────┐
│ Fill search form:           │
│ ┌─────────────────────────┐ │
│ │ Attendee count          │ │
│ │ Start date/time         │ │
│ │ End date/time           │ │
│ │ [Quick selects]         │ │
│ └─────────────────────────┘ │
└─────────┬───────────────────┘
          │
          ▼
┌─────────────────────┐
│ Validate input      │
└─────────┬───────────┘
          │
     ┌────┴────┐
     │ Valid?  │
     └────┬────┘
          │
     ┌────┴────┐
     │         │
    No        Yes
     │         │
     │         ▼
     │    ┌──────────────────────────┐
     │    │ RoomAvailabilityService  │
     │    │ searchAvailableRooms()   │
     │    └─────────┬────────────────┘
     │              │
     │              ▼
     │    ┌──────────────────────────┐
     │    │ Query database:          │
     │    │ SELECT rooms WHERE       │
     │    │ - capacity >= attendees  │
     │    │ - status = 'active'      │
     │    │ AND NOT EXISTS (         │
     │    │   conflicting bookings   │
     │    │ )                        │
     │    └─────────┬────────────────┘
     │              │
     │              ▼
     │         ┌─────────┐
     │         │ Rooms   │
     │         │ Found?  │
     │         └────┬────┘
     │              │
     │         ┌────┴────┐
     │         │         │
     │        No        Yes
     │         │         │
     │         │         ▼
     │         │    ┌──────────────────────┐
     │         │    │ Display available    │
     │         │    │ rooms in cards:      │
     │         │    │ - Room name          │
     │         │    │ - Capacity           │
     │         │    │ - Location           │
     │         │    │ - [Book] button      │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ User selects room    │
     │         │    │ Click "Book"         │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Show booking form:   │
     │         │    │ - Confirm details    │
     │         │    │ - Select theme       │
     │         │    │ - Add notes          │
     │         │    │ - Accept terms       │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ User submits form    │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ BookingService       │
     │         │    │ createBooking()      │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ BEGIN TRANSACTION    │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Re-check room        │
     │         │    │ availability         │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │         ┌────┴────┐
     │         │         │         │
     │         │    Available  Not Available
     │         │         │         │
     │         │         │         ▼
     │         │         │    ┌──────────────┐
     │         │         │    │ ROLLBACK     │
     │         │         │    │ Show error   │
     │         │         │    └──────────────┘
     │         │         │         │
     │         │         ▼         │
     │         │    ┌──────────────────────┐
     │         │    │ Generate booking_ref │
     │         │    │ (BK-YYYYMMDD-XXX)    │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Create booking       │
     │         │    │ record in DB         │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ COMMIT TRANSACTION   │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Dispatch Event:      │
     │         │    │ BookingCreated       │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Listener triggers:   │
     │         │    │ SendLineNotification │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Queue 2 jobs:        │
     │         │    │ 1. Guest notification│
     │         │    │ 2. Staff notification│
     │         │    └─────────┬────────────┘
     │         │              │
     │         │              ▼
     │         │    ┌──────────────────────┐
     │         │    │ Redirect to booking  │
     │         │    │ details page with    │
     │         │    │ success message      │
     │         │    └─────────┬────────────┘
     │         │              │
     │         ▼              ▼
     │    ┌──────────────────────┐
     │    │ Show "No rooms       │
     │    │ available" message   │
     │    │ Suggest different    │
     │    │ time/capacity        │
     │    └──────────────────────┘
     │              │
     ▼              ▼
┌──────────────────────┐
│ Show validation      │
│ errors, return       │
│ to search form       │
└──────────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

### 2.2 Room Availability Check Algorithm

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌──────────────────────────────────┐
│ Input:                           │
│ - room_id                        │
│ - start_datetime                 │
│ - end_datetime                   │
│ - exclude_booking_id (optional)  │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ Query existing bookings:         │
│                                  │
│ SELECT * FROM bookings WHERE     │
│   room_id = ?                    │
│   AND status != 'cancelled'      │
│   AND id != ? (if excluding)     │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ For each existing booking:       │
│ Check overlap conditions         │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────────────┐
│ Overlap exists if ANY of these is true: │
│                                          │
│ 1. New booking starts during existing:  │
│    new_start >= existing_start AND      │
│    new_start < existing_end              │
│                                          │
│ 2. New booking ends during existing:    │
│    new_end > existing_start AND         │
│    new_end <= existing_end               │
│                                          │
│ 3. New booking wraps existing:          │
│    new_start <= existing_start AND      │
│    new_end >= existing_end               │
└─────────┬────────────────────────────────┘
          │
     ┌────┴────┐
     │ Overlap │
     │ Found?  │
     └────┬────┘
          │
     ┌────┴────┐
     │         │
    Yes       No
     │         │
     ▼         ▼
┌─────────┐ ┌─────────┐
│ Return  │ │ Return  │
│ FALSE   │ │ TRUE    │
│(Not     │ │(Room    │
│Available│ │Available│
└────┬────┘ └────┬────┘
     │           │
     └─────┬─────┘
           ▼
       ┌─────┐
       │ END │
       └─────┘
```

---

## 3. การเชื่อมต่อบัญชี LINE

### 3.1 LINE OAuth Flow

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ User in Profile page     │
│ Clicks "Connect LINE"    │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ LineAuthController       │
│ redirect()               │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Generate random:         │
│ - state (CSRF token)     │
│ - nonce (ID token)       │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Store in session:        │
│ - line_oauth_state       │
│ - line_oauth_nonce       │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────────────────┐
│ Build LINE OAuth URL:                │
│ https://access.line.me/oauth2/v2.1/  │
│ authorize?                           │
│ - response_type=code                 │
│ - client_id={CHANNEL_ID}             │
│ - redirect_uri={CALLBACK_URL}        │
│ - state={state}                      │
│ - scope=profile openid               │
│ - nonce={nonce}                      │
│ - bot_prompt=normal                  │
└─────────┬────────────────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Redirect user to         │
│ LINE Login page          │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ User on LINE Login page  │
│ - Sees app name          │
│ - Sees permissions       │
└─────────┬────────────────┘
          │
          ▼
     ┌────────────┐
     │ User       │
     │ Approves?  │
     └─────┬──────┘
           │
      ┌────┴────┐
      │         │
     No        Yes
      │         │
      │         ▼
      │    ┌─────────────────────────┐
      │    │ LINE redirects back to: │
      │    │ {CALLBACK_URL}?         │
      │    │ code={auth_code}&       │
      │    │ state={state}           │
      │    └─────────┬───────────────┘
      │              │
      │              ▼
      │    ┌─────────────────────────┐
      │    │ LineAuthController      │
      │    │ callback()              │
      │    └─────────┬───────────────┘
      │              │
      │              ▼
      │    ┌─────────────────────────┐
      │    │ Verify state matches    │
      │    │ session state           │
      │    └─────────┬───────────────┘
      │              │
      │         ┌────┴────┐
      │         │ Valid?  │
      │         └────┬────┘
      │              │
      │         ┌────┴────┐
      │         │         │
      │        No        Yes
      │         │         │
      │         │         ▼
      │         │    ┌──────────────────────┐
      │         │    │ Exchange code for    │
      │         │    │ access token:        │
      │         │    │ POST to LINE         │
      │         │    │ /oauth2/v2.1/token   │
      │         │    └─────────┬────────────┘
      │         │              │
      │         │              ▼
      │         │    ┌──────────────────────┐
      │         │    │ Get LINE user        │
      │         │    │ profile:             │
      │         │    │ GET /v2/profile      │
      │         │    │ with access token    │
      │         │    └─────────┬────────────┘
      │         │              │
      │         │              ▼
      │         │    ┌──────────────────────┐
      │         │    │ Extract LINE User ID │
      │         │    │ (U1234567890...)     │
      │         │    └─────────┬────────────┘
      │         │              │
      │         │              ▼
      │         │    ┌──────────────────────┐
      │         │    │ Update user record:  │
      │         │    │ - line_user_id       │
      │         │    │ - line_linked_at     │
      │         │    └─────────┬────────────┘
      │         │              │
      │         │              ▼
      │         │    ┌──────────────────────┐
      │         │    │ Send welcome message │
      │         │    │ via LINE             │
      │         │    └─────────┬────────────┘
      │         │              │
      │         │              ▼
      │         │    ┌──────────────────────┐
      │         │    │ Redirect to          │
      │         │    │ dashboard with       │
      │         │    │ success message      │
      │         │    └─────────┬────────────┘
      │         │              │
      ▼         ▼              ▼
┌──────────────────────┐   ┌─────┐
│ LINE redirects with  │   │ END │
│ error parameter      │   └─────┘
│ Show error to user   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Show CSRF error      │
│ Redirect to profile  │
└──────────┬───────────┘
           │
           ▼
       ┌─────┐
       │ END │
       └─────┘
```

---

## 4. การส่งการแจ้งเตือน LINE

### 4.1 LINE Notification Flow

```
┌─────────┐
│  START  │
│ (Event  │
│Triggered│
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ Event: BookingCreated    │
│ - booking object         │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Listener:                │
│ SendLineNotification     │
│ handle()                 │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Dispatch 2 Jobs:         │
│                          │
│ 1. For Guest (booker)    │
│ 2. For Staff (all)       │
└─────────┬────────────────┘
          │
     ┌────┴────┐
     │         │
     ▼         ▼
┌─────────┐ ┌─────────┐
│ Job 1   │ │ Job 2   │
│ Guest   │ │ Staff   │
└────┬────┘ └────┬────┘
     │           │
     │           ├─────────────────┐
     │           │                 │
     ▼           ▼                 ▼
┌─────────────────────────────────────────────┐
│ SendLineNotificationJob::handle()           │
└─────────┬───────────────────────────────────┘
          │
     ┌────┴────┐
     │         │
  Guest      Staff
     │         │
     ▼         ▼
┌──────────┐ ┌──────────────────┐
│sendBook  │ │sendBookingCreated│
│ingConfir │ │Notification()    │
│mation()  │ │(to all staff)    │
└────┬─────┘ └────┬─────────────┘
     │            │
     │            ├───────────┐
     │            │           │
     └────────┬───┘           │
              │               │
              ▼               ▼
┌──────────────────────────────────┐
│ LineNotificationService          │
│ createAndSendNotification()      │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ Create LineNotification record:  │
│ - booking_id                     │
│ - recipient_id                   │
│ - recipient_type                 │
│ - notification_type              │
│ - message                        │
│ - status = 'pending'             │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ Check: Does recipient have       │
│ line_user_id?                    │
└─────────┬────────────────────────┘
          │
     ┌────┴────┐
     │         │
    No        Yes
     │         │
     │         ▼
     │    ┌──────────────────────────┐
     │    │ sendLineMessage()        │
     │    └─────────┬────────────────┘
     │              │
     │              ▼
     │    ┌──────────────────────────┐
     │    │ POST to LINE API:        │
     │    │ https://api.line.me/v2/  │
     │    │ bot/message/push         │
     │    │                          │
     │    │ Headers:                 │
     │    │ - Authorization: Bearer  │
     │    │   {access_token}         │
     │    │                          │
     │    │ Body:                    │
     │    │ {                        │
     │    │   "to": "{line_user_id}" │
     │    │   "messages": [{         │
     │    │     "type": "text",      │
     │    │     "text": "{message}"  │
     │    │   }]                     │
     │    │ }                        │
     │    └─────────┬────────────────┘
     │              │
     │              ▼
     │         ┌─────────┐
     │         │ Success?│
     │         └────┬────┘
     │              │
     │         ┌────┴────┐
     │         │         │
     │        No        Yes
     │         │         │
     │         │         ▼
     │         │    ┌──────────────────┐
     │         │    │ Update record:   │
     │         │    │ - status = 'sent'│
     │         │    │ - sent_at = now()│
     │         │    └──────────┬───────┘
     │         │               │
     │         ▼               │
     │    ┌──────────────────┐ │
     │    │ Update record:   │ │
     │    │ - status='failed'│ │
     │    │ - error_message  │ │
     │    └──────────┬───────┘ │
     │               │         │
     ▼               ▼         ▼
┌──────────────────────────────────┐
│ Mark notification as failed:     │
│ "User has no Line ID"            │
└──────────────────────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

### 4.2 Message Format Examples

**Booking Confirmation (Guest):**
```
✅ ยืนยันการจองห้องประชุม

สวัสดีคุณ John Doe

การจองของคุณได้รับการยืนยันแล้ว

📌 รายละเอียดการจอง:
🏢 ห้อง: Conference Room A
📅 วันที่: 15/11/2025
⏰ เวลา: 09:00 - 11:00
👥 จำนวน: 20 คน
🎨 ธีม: ธีมธุรกิจ - ทางการ

ขอบคุณที่ใช้บริการ
```

**New Booking (Staff):**
```
🔔 การจองใหม่

👤 ผู้จอง: John Doe
🏢 ห้อง: Conference Room A
📅 วันที่: 15/11/2025
⏰ เวลา: 09:00 - 11:00
👥 จำนวน: 20 คน
🎨 ธีม: ธีมธุรกิจ - ทางการ
📝 สถานะ: ยืนยันแล้ว
```

---

## 5. การยกเลิกการจอง

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ User in booking details  │
│ page or history          │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Clicks "Cancel Booking"  │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Show confirmation dialog │
│ "Are you sure?"          │
└─────────┬────────────────┘
          │
     ┌────┴────┐
     │ User    │
     │Confirms?│
     └────┬────┘
          │
     ┌────┴────┐
     │         │
    No        Yes
     │         │
     │         ▼
     │    ┌──────────────────────┐
     │    │ BookingController    │
     │    │ cancel()             │
     │    └─────────┬────────────┘
     │              │
     │              ▼
     │    ┌──────────────────────┐
     │    │ Check ownership:     │
     │    │ booking.user_id ==   │
     │    │ auth.user.id?        │
     │    └─────────┬────────────┘
     │              │
     │         ┌────┴────┐
     │         │ Owner?  │
     │         └────┬────┘
     │              │
     │         ┌────┴────┐
     │         │         │
     │        No        Yes
     │         │         │
     │         │         ▼
     │         │    ┌──────────────────────┐
     │         │    │ Check timing:        │
     │         │    │ start_datetime >     │
     │         │    │ now()?               │
     │         │    └─────────┬────────────┘
     │         │              │
     │         │         ┌────┴────┐
     │         │         │ Future? │
     │         │         └────┬────┘
     │         │              │
     │         │         ┌────┴────┐
     │         │         │         │
     │         │        No        Yes
     │         │         │         │
     │         │         │         ▼
     │         │         │    ┌──────────────────┐
     │         │         │    │ BookingService   │
     │         │         │    │ cancelBooking()  │
     │         │         │    └─────────┬────────┘
     │         │         │              │
     │         │         │              ▼
     │         │         │    ┌──────────────────┐
     │         │         │    │ Update booking:  │
     │         │         │    │ status='cancelled│
     │         │         │    └─────────┬────────┘
     │         │         │              │
     │         │         │              ▼
     │         │         │    ┌──────────────────┐
     │         │         │    │ Send LINE        │
     │         │         │    │ notification     │
     │         │         │    └─────────┬────────┘
     │         │         │              │
     │         │         │              ▼
     │         │         │    ┌──────────────────┐
     │         │         │    │ Redirect with    │
     │         │         │    │ success message  │
     │         │         │    └─────────┬────────┘
     │         │         │              │
     │         ▼         ▼              │
     │    ┌──────────────────────┐     │
     │    │ Return 403 Forbidden │     │
     │    │ "Cannot cancel past  │     │
     │    │ bookings"            │     │
     │    └──────────────────────┘     │
     │              │                  │
     ▼              ▼                  ▼
┌──────────────────────┐          ┌─────┐
│ Do nothing, return   │          │ END │
│ to current page      │          └─────┘
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Return 403 Forbidden │
│ "Unauthorized"       │
└──────────┬───────────┘
           │
           ▼
       ┌─────┐
       │ END │
       └─────┘
```

---

## 6. การจัดการการจอง (Staff)

### 6.1 Staff Approve/Reject Booking

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ Staff views all bookings │
│ Filter by status         │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Selects a booking        │
│ Click "View Details"     │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Review booking details:  │
│ - User info              │
│ - Room info              │
│ - Date/Time              │
│ - Attendee count         │
│ - Theme                  │
│ - Notes                  │
└─────────┬────────────────┘
          │
     ┌────┴────┐
     │ Staff   │
     │Decision?│
     └────┬────┘
          │
   ┌──────┴──────┐
   │             │
Approve       Cancel
   │             │
   ▼             ▼
┌─────────┐  ┌──────────┐
│ Click   │  │ Click    │
│"Confirm"│  │"Cancel"  │
└────┬────┘  └────┬─────┘
     │            │
     ▼            ▼
┌─────────────┐ ┌──────────────┐
│ Update      │ │ Update       │
│ status =    │ │ status =     │
│ 'confirmed' │ │ 'cancelled'  │
└─────┬───────┘ └──────┬───────┘
      │                │
      ▼                ▼
┌──────────────────────────┐
│ Send LINE notification   │
│ to guest                 │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Show success message     │
│ Return to bookings list  │
└─────────┬────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

---

## 7. การตรวจสอบความพร้อมของห้อง

### 7.1 Room Status Display Flow

```
┌─────────┐
│  START  │
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ User visits              │
│ /room-status or          │
│ /room-status/{id}        │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ RoomStatusBoardController│
│ index() or showRoom()    │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Get current datetime     │
│ (Asia/Bangkok)           │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ Query rooms with bookings:       │
│                                  │
│ SELECT rooms.*, bookings.*       │
│ FROM meeting_rooms rooms         │
│ LEFT JOIN bookings ON            │
│   bookings.room_id = rooms.id    │
│   AND bookings.status = 'confirmed'│
│ WHERE rooms.status = 'active'    │
└─────────┬────────────────────────┘
          │
          ▼
┌──────────────────────────────────┐
│ For each room:                   │
│ Calculate status                 │
└─────────┬────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Status Logic:                           │
│                                         │
│ IF current booking exists THEN          │
│   IF now >= start AND now < end THEN    │
│     status = 'occupied' (Red)           │
│   ELSE IF start - now <= 30 minutes THEN│
│     status = 'starting-soon' (Yellow)   │
│   ELSE                                  │
│     status = 'reserved' (Blue)          │
│   END IF                                │
│ ELSE                                    │
│   status = 'available' (Green)          │
│ END IF                                  │
└─────────┬───────────────────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Get next booking         │
│ (if exists)              │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Get today's schedule     │
│ (all bookings today)     │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Render view with:        │
│ - Room details           │
│ - Current status         │
│ - Current booking user   │
│ - Next booking time      │
│ - Today's schedule       │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Auto-refresh every       │
│ 30 seconds (JavaScript)  │
└─────────┬────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

---

## 8. ระบบ Queue และ Job Processing

### 8.1 Queue Job Processing Flow

```
┌─────────┐
│  START  │
│ (Job    │
│Dispatch)│
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ SendLineNotificationJob  │
│ dispatched               │
└─────────┬────────────────┘
          │
          ▼
     ┌────────────┐
     │ Queue      │
     │ Driver?    │
     └─────┬──────┘
           │
      ┌────┴────┐
      │         │
    Sync     Database
      │         │
      │         ▼
      │    ┌──────────────────┐
      │    │ Insert into      │
      │    │ 'jobs' table     │
      │    └─────────┬────────┘
      │              │
      │              ▼
      │    ┌──────────────────┐
      │    │ Queue worker     │
      │    │ picks up job     │
      │    │ (background)     │
      │    └─────────┬────────┘
      │              │
      └──────┬───────┘
             │
             ▼
┌──────────────────────────┐
│ Job::handle() executes   │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Try to send LINE message │
└─────────┬────────────────┘
          │
     ┌────┴────┐
     │Success? │
     └────┬────┘
          │
     ┌────┴────┐
     │         │
    Yes       No
     │         │
     │         ▼
     │    ┌──────────────────┐
     │    │ Check attempts:  │
     │    │ < max_tries (3)? │
     │    └─────────┬────────┘
     │              │
     │         ┌────┴────┐
     │         │         │
     │        Yes       No
     │         │         │
     │         │         ▼
     │         │    ┌──────────────┐
     │         │    │ Call failed()│
     │         │    │ method       │
     │         │    └──────┬───────┘
     │         │           │
     │         │           ▼
     │         │    ┌──────────────┐
     │         │    │ Log permanent│
     │         │    │ failure      │
     │         │    └──────┬───────┘
     │         │           │
     │         ▼           │
     │    ┌──────────────┐ │
     │    │ Re-queue job │ │
     │    │ with backoff │ │
     │    │ (60 seconds) │ │
     │    └──────┬───────┘ │
     │           │         │
     ▼           ▼         ▼
┌──────────────────────────┐
│ Delete job from queue    │
└─────────┬────────────────┘
          │
          ▼
      ┌─────┐
      │ END │
      └─────┘
```

### 8.2 Retry Mechanism

```
Attempt 1: Immediate execution
   ↓ (fails)
   Wait 60 seconds
   ↓
Attempt 2: Retry
   ↓ (fails)
   Wait 60 seconds
   ↓
Attempt 3: Final retry
   ↓ (fails)
   ↓
Mark as permanently failed
Log error to database
```

---

## 9. System State Diagram

### 9.1 Booking Status Lifecycle

```
         ┌─────────────┐
         │   Created   │
         │  (pending)  │
         └──────┬──────┘
                │
         (Auto-confirm)
                │
                ▼
         ┌─────────────┐
    ┌────│  Confirmed  │────┐
    │    └─────────────┘    │
    │                       │
(Cancel)              (Time passes)
    │                       │
    ▼                       ▼
┌──────────┐         ┌─────────────┐
│Cancelled │         │  Completed  │
│ (final)  │         │   (final)   │
└──────────┘         └─────────────┘

Status Transitions:
- pending → confirmed (automatic on creation)
- confirmed → cancelled (user/staff action)
- confirmed → completed (when end_datetime passes)
```

### 9.2 User Session States

```
         ┌──────────┐
         │  Guest   │
         │(No Auth) │
         └─────┬────┘
               │
        (Login/Register)
               │
               ▼
         ┌──────────┐
         │Authenticated│
         │   User    │
         └─────┬────┘
               │
          ┌────┴────┐
          │         │
      (Logout)  (Timeout)
          │         │
          └────┬────┘
               │
               ▼
         ┌──────────┐
         │  Guest   │
         └──────────┘
```

### 9.3 LINE Connection States

```
         ┌────────────┐
         │Not Connected│
         └──────┬─────┘
                │
        (Click Connect)
                │
                ▼
         ┌────────────┐
         │ OAuth Flow │
         └──────┬─────┘
                │
           ┌────┴────┐
           │         │
       (Success) (Failure)
           │         │
           ▼         ▼
    ┌──────────┐  ┌────────────┐
    │Connected │  │Not Connected│
    └─────┬────┘  └────────────┘
          │
    (Click Unlink)
          │
          ▼
    ┌────────────┐
    │Not Connected│
    └────────────┘
```

---

## 10. Error Handling Flow

```
┌─────────┐
│ Error   │
│Occurred │
└────┬────┘
     │
     ▼
┌──────────────┐
│ Type of      │
│ Error?       │
└──────┬───────┘
       │
   ┌───┴────────────┬──────────┬────────────┐
   │                │          │            │
Validation   Database   External   Authentication
 Error        Error      API Error    Error
   │            │          │            │
   ▼            ▼          ▼            ▼
┌────────┐  ┌────────┐ ┌────────┐  ┌────────┐
│ Return │  │  Log   │ │ Retry  │  │Redirect│
│ to form│  │  error │ │ 3 times│  │to login│
│ with   │  │  Show  │ │ then   │  │  page  │
│ errors │  │ 500    │ │ fail   │  └────────┘
└────────┘  │  page  │ └────────┘
            └────────┘      │
                           ▼
                    ┌────────────┐
                    │ Log error  │
                    │ Mark as    │
                    │ failed     │
                    └────────────┘
```

---

## Legend

```
┌──────────┐
│ Process  │  - Process or Action
└──────────┘

┌──────────┐
│          │
│ Decision │  - Decision Point
│    ?     │
└──────────┘

    ▼         - Flow Direction

┌──────────┐
│  START   │  - Start Point
└──────────┘

┌──────────┐
│   END    │  - End Point
└──────────┘
```

---

**Document Information:**
- **Version:** 1.0.0
- **Last Updated:** November 13, 2025
- **Purpose:** System Flowcharts Documentation
- **Status:** Complete

---

© 2025 Smart Meeting Room Booking System. All rights reserved.
