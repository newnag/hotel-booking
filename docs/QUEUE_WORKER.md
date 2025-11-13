# Queue Worker Configuration

## Development

สำหรับการพัฒนา สามารถรัน queue worker แบบง่ายด้วย Artisan command:

```bash
php artisan queue:work
```

หรือใช้ `queue:listen` เพื่อให้ reload code อัตโนมัติ:

```bash
php artisan queue:listen
```

## Production

สำหรับ production แนะนำให้ใช้ Supervisor เพื่อจัดการ queue workers

### 1. ติดตั้ง Supervisor (Ubuntu/Debian)

```bash
sudo apt-get install supervisor
```

### 2. สร้างไฟล์ Configuration

สร้างไฟล์ `/etc/supervisor/conf.d/hotel-booking-worker.conf`:

```ini
[program:hotel-booking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/hotel-booking/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/hotel-booking/storage/logs/worker.log
stopwaitsecs=3600
```

**หมายเหตุ:**
- แก้ไข `/path/to/hotel-booking` เป็น path จริงของโปรเจค
- `numprocs=2` = รัน 2 worker processes (ปรับตามความต้องการ)
- `--max-time=3600` = worker จะ restart ทุก 1 ชั่วโมงเพื่อป้องกัน memory leak
- `--tries=3` = retry job ที่ล้มเหลวสูงสุด 3 ครั้ง

### 3. อัปเดตและเริ่ม Supervisor

```bash
# อ่านไฟล์ config ใหม่
sudo supervisorctl reread

# อัปเดต configuration
sudo supervisorctl update

# เริ่ม worker
sudo supervisorctl start hotel-booking-worker:*
```

### 4. จัดการ Workers

```bash
# ดูสถานะ
sudo supervisorctl status hotel-booking-worker:*

# หยุด workers
sudo supervisorctl stop hotel-booking-worker:*

# รีสตาร์ท workers
sudo supervisorctl restart hotel-booking-worker:*

# ดู logs
sudo tail -f /path/to/hotel-booking/storage/logs/worker.log
```

### 5. หลังจาก Deploy Code ใหม่

ทุกครั้งที่ deploy code ใหม่ ต้อง restart workers:

```bash
php artisan queue:restart
```

หรือใช้ Supervisor:

```bash
sudo supervisorctl restart hotel-booking-worker:*
```

## Queue Configuration

ตรวจสอบการตั้งค่า Queue ใน `.env`:

```env
# Database Queue (แนะนำสำหรับเริ่มต้น)
QUEUE_CONNECTION=database

# หรือใช้ Redis (สำหรับ high-performance)
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## ตรวจสอบ Jobs ใน Queue

```bash
# ดูจำนวน jobs ที่รอ
php artisan queue:monitor database

# ล้าง failed jobs
php artisan queue:flush

# ลอง job ที่ล้มเหลว
php artisan queue:retry all

# ดู failed jobs
php artisan queue:failed
```

## Line Notification Jobs

ระบบใช้ Queue สำหรับการส่ง Line notifications:

- **Job**: `SendLineNotificationJob`
- **Retry**: 3 ครั้ง
- **Backoff**: 60 วินาที
- **Timeout**: ใช้ default (60 วินาที)

### การทำงานของ Notification Flow

1. **Booking Created** → Event `BookingCreated` ถูก dispatch
2. **Event Listener** → `SendLineNotification` listener รับ event
3. **Queue Job** → Dispatch `SendLineNotificationJob` to queue
4. **Worker** → ประมวลผล job และส่ง notification ผ่าน Line API
5. **Retry** → หาก failed จะ retry อัตโนมัติ 3 ครั้ง
6. **Failed** → บันทึกใน `failed_jobs` table

### Monitoring

ดู logs ของ notifications:

```bash
# Laravel logs
tail -f storage/logs/laravel.log | grep "Line notification"

# Worker logs (ถ้าใช้ Supervisor)
tail -f storage/logs/worker.log
```

## Troubleshooting

### Worker ไม่ทำงาน

```bash
# ตรวจสอบสถานะ
sudo supervisorctl status

# ดู logs
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log

# Restart worker
sudo supervisorctl restart hotel-booking-worker:*
```

### Jobs ค้างใน Queue

```bash
# ดูจำนวน jobs
php artisan queue:monitor database

# ประมวลผล jobs ทั้งหมดทันที
php artisan queue:work --once

# ลบ jobs ทั้งหมด (ระวัง!)
php artisan queue:clear
```

### Line API Error

ตรวจสอบ:
1. `.env` มี `LINE_CHANNEL_ACCESS_TOKEN` ถูกต้อง
2. Network สามารถเชื่อมต่อ `https://api.line.me` ได้
3. ดู error message ใน `line_notifications` table
4. ดู logs ใน `storage/logs/laravel.log`

## Production Best Practices

1. **ใช้ Redis** แทน database queue สำหรับ performance ที่ดีกว่า
2. **รัน multiple workers** (numprocs >= 2) สำหรับ high-load
3. **ตั้ง max-time** เพื่อป้องกัน memory leak
4. **Monitor failed jobs** อย่างสม่ำเสมอ
5. **ตั้ง alerts** สำหรับ failed jobs ที่มากเกินไป
6. **Restart workers** หลัง deploy ทุกครั้ง
