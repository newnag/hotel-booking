# คู่มือการตั้งค่า LINE Login และ Messaging API

## ขั้นตอนการตั้งค่า LINE Developers Console

### 1. สร้าง LINE Developers Account
1. ไปที่ https://developers.line.biz/
2. ล็อกอินด้วยบัญชี LINE ของคุณ
3. คลิก "Create a new provider" (ถ้ายังไม่มี)
   - ใส่ชื่อ Provider เช่น "Hotel Booking System"

### 2. สร้าง LINE Login Channel
1. เลือก Provider ที่สร้างไว้
2. คลิก "Create a new channel"
3. เลือกประเภท **"LINE Login"**
4. กรอกข้อมูล:
   - **Channel name**: ระบบจองห้องประชุม
   - **Channel description**: ระบบจองห้องประชุมออนไลน์
   - **App types**: เลือก **Web app**
   - **Email address**: อีเมลผู้ดูแลระบบ
5. ยอมรับข้อตกลง และกด "Create"

### 3. ตั้งค่า LINE Login Channel
1. ในหน้า Channel settings:
   - คัดลอก **Channel ID** → ใส่ใน `.env` ที่ `LINE_CHANNEL_ID`
   - คัดลอก **Channel secret** → ใส่ใน `.env` ที่ `LINE_CHANNEL_SECRET`

2. ไปที่แท็บ **"LINE Login"**:
   - **Callback URL**: ใส่ `http://127.0.0.1:8000/line/callback`
     - สำหรับ production: `https://yourdomain.com/line/callback`
   - กด "Add" และ "Update"

3. ไปที่แท็บ **"Scopes"**:
   - เลือก: ✅ `profile`
   - เลือก: ✅ `openid`

### 4. สร้าง Messaging API Channel (สำหรับส่งข้อความแจ้งเตือน)
1. กลับไปที่ Provider
2. คลิก "Create a new channel" อีกครั้ง
3. เลือกประเภท **"Messaging API"**
4. กรอกข้อมูล:
   - **Channel name**: ระบบจองห้องประชุม Notification
   - **Channel description**: การแจ้งเตือนการจอง
   - **Category**: Lifestyle
   - **Subcategory**: Service
5. ยอมรับข้อตกลง และกด "Create"

### 5. ตั้งค่า Messaging API Channel
1. ในหน้า Channel settings:
   - ไปที่แท็บ **"Messaging API"**
   - หา **Channel access token (long-lived)**
   - คลิก "Issue" → คัดลอก token → ใส่ใน `.env` ที่ `LINE_CHANNEL_ACCESS_TOKEN`

2. ตั้งค่าเพิ่มเติม:
   - **Use webhooks**: ปิด (ถ้าไม่ใช้)
   - **Allow bot to join group chats**: ปิด
   - **Auto-reply messages**: ปิด
   - **Greeting messages**: ปิด

### 6. เชื่อม LINE Login กับ Messaging API
1. ไปที่ LINE Login Channel
2. แท็บ **"Linked OA"**
3. คลิก "Link" และเลือก Messaging API channel ที่สร้างไว้

## การตั้งค่าในระบบ

### ไฟล์ `.env`
```env
# LINE Messaging API Configuration
LINE_CHANNEL_ACCESS_TOKEN=your_messaging_api_channel_access_token_here
LINE_CHANNEL_SECRET=your_line_login_channel_secret_here
LINE_CHANNEL_ID=your_line_login_channel_id_here
```

### ตัวอย่างค่าที่ต้องกรอก:
```env
LINE_CHANNEL_ACCESS_TOKEN=abc123xyz789very_long_token_here
LINE_CHANNEL_SECRET=1234567890abcdef
LINE_CHANNEL_ID=1234567890
```

## การทดสอบระบบ

### 1. ทดสอบ LINE Login
1. เข้าระบบในเว็บไซต์
2. ไปที่หน้า "โปรไฟล์"
3. คลิกปุ่ม "เชื่อมต่อกับ LINE"
4. ล็อกอินด้วย LINE
5. อนุญาตให้เข้าถึงข้อมูล
6. ตรวจสอบว่า LINE User ID ถูกบันทึกในฐานข้อมูล

### 2. ทดสอบการส่งข้อความ
1. สร้างการจองห้องประชุม
2. ตรวจสอบว่าได้รับข้อความแจ้งเตือนผ่าน LINE หรือไม่

## Troubleshooting

### ปัญหา: "Invalid callback URL"
- ตรวจสอบว่า Callback URL ตรงกับที่ตั้งใน LINE Developers Console
- สำหรับ localhost: `http://127.0.0.1:8000/line/callback`
- สำหรับ production: `https://yourdomain.com/line/callback`

### ปัญหา: "Invalid client_id or client_secret"
- ตรวจสอบว่าคัดลอก Channel ID และ Channel Secret ถูกต้อง
- ตรวจสอบไฟล์ `.env` ว่ามีค่าครบถ้วน
- รัน `php artisan config:clear` เพื่อล้าง cache

### ปัญหา: "Cannot send message"
- ตรวจสอบว่าผู้ใช้ได้เพิ่มบัญชี Messaging API เป็นเพื่อนแล้ว
- ตรวจสอบ Channel Access Token ว่าถูกต้อง
- ดูที่หน้า "Messaging API" ตรวจสอบว่าถึง quota หรือไม่ (Free tier: 500 messages/month)

## Webhook URL (Optional)
ถ้าต้องการรับข้อความจากผู้ใช้:
- Webhook URL: `https://yourdomain.com/line/webhook`
- ต้องเป็น HTTPS
- ต้องตอบกลับ 200 OK ภายใน 30 วินาที

## ข้อมูลเพิ่มเติม
- LINE Login Documentation: https://developers.line.biz/en/docs/line-login/
- Messaging API Documentation: https://developers.line.biz/en/docs/messaging-api/
- LINE Developers Console: https://developers.line.biz/console/

## สรุป Flow การทำงาน

```
1. User คลิก "เชื่อมต่อกับ LINE"
   ↓
2. Redirect ไป LINE Login
   ↓
3. User อนุญาตให้เข้าถึงข้อมูล
   ↓
4. LINE redirect กลับมาพร้อม authorization code
   ↓
5. ระบบแลก code เป็น access token
   ↓
6. ดึง LINE User ID จาก Profile API
   ↓
7. บันทึก line_user_id ลงฐานข้อมูล
   ↓
8. ตอนส่งข้อความ ใช้ Messaging API + LINE User ID
```

## Security Best Practices
- ✅ เก็บ Channel Secret ใน `.env` ห้ามคอมมิทใน Git
- ✅ ใช้ HTTPS ใน production
- ✅ ตรวจสอบ state parameter (CSRF protection)
- ✅ จำกัดจำนวน request ด้วย rate limiting
- ✅ Log ทุก error สำหรับ debugging
