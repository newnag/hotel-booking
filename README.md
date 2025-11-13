# Hotel Meeting Room Booking System

A comprehensive web-based meeting room booking system built with Laravel 12, designed for hotel management of meeting room reservations with Line notification integration.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 🌟 Features

### Guest Features
- 🔍 **Room Search** - Search available meeting rooms by date, time, and capacity
- 📅 **Booking Management** - Create, view, and cancel bookings
- 🔔 **Line Notifications** - Automatic booking confirmations via Line
- 👤 **Profile Management** - Update personal information and Line integration
- 📊 **Booking History** - View all past and upcoming reservations

### Staff Features
- 📆 **Calendar View** - Interactive FullCalendar showing all bookings
- 📈 **Status Dashboard** - Real-time room availability and statistics
- 🎯 **Booking Management** - View and update booking status
- 🔔 **Notification Center** - Monitor all Line notifications sent

### Admin Features
- 🏢 **Room Management** - Create, edit, and manage meeting rooms
- 👥 **User Management** - Manage guest accounts and permissions
- 👨‍💼 **Staff Management** - Manage staff and admin accounts
- 📊 **Comprehensive Dashboard** - System-wide statistics and insights
- 🔧 **System Configuration** - Line API settings and notification templates

### Technical Features
- 🔒 **Security** - CSRF protection, XSS prevention, SQL injection protection, rate limiting
- ⚡ **Performance** - Redis caching, database query optimization, eager loading
- 🌐 **Multilingual** - Thai and English language support
- 📱 **Responsive** - Mobile-friendly AdminLTE 3 interface
- 🧪 **Tested** - 200+ automated tests with 99.5%+ passing rate
- 🚀 **Production Ready** - Queue workers, asset optimization, comprehensive documentation

## 📋 Requirements

- **PHP:** 8.2 or higher
- **Database:** MySQL 8.0+ or MariaDB 10.3+
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Composer:** 2.x
- **Node.js:** 18.x or higher (for asset compilation)
- **Redis:** 6.x or higher (recommended for production)
- **Line API:** Line Messaging API credentials (optional)

## 🚀 Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/hotel-booking.git
cd hotel-booking
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seeders

```bash
# Create database tables
php artisan migrate

# Seed with sample data
php artisan db:seed
```

**Default Accounts:**
- Admin: `admin@hotel.com` / `password`
- Staff: `staff1@hotel.com` / `password`
- Guest: `guest1@example.com` / `password`

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

## 🔧 Configuration

### Line Messaging API (Optional)

1. Create Line Messaging API channel at [Line Developers](https://developers.line.biz/)
2. Add credentials to `.env`:

```env
LINE_CHANNEL_ACCESS_TOKEN=your_channel_access_token
LINE_CHANNEL_SECRET=your_channel_secret
```

3. Test configuration in Admin Panel → Notifications → Test

### Redis Cache (Recommended for Production)

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Workers (Required for Notifications)

```bash
# Development
php artisan queue:work database

# Production (see docs/QUEUE_WORKER_SETUP.md)
# Use Supervisor or systemd
```

### File Storage

```bash
# Create storage symlink
php artisan storage:link
```

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[Queue Worker Setup](docs/QUEUE_WORKER_SETUP.md)** - Configure queue workers for production
- **[CSRF Protection](docs/CSRF_PROTECTION.md)** - CSRF security implementation details
- **[Rate Limiting](docs/RATE_LIMITING.md)** - API rate limiting configuration
- **[Input Validation & XSS](docs/INPUT_VALIDATION_XSS.md)** - Security validation report
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment instructions

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Filter specific tests
php artisan test --filter=BookingTest
```

**Test Statistics:**
- Total Tests: 202
- Passing: 201 (99.5%)
- Assertions: 532+
- Coverage: Feature + Unit tests

## 🏗️ Architecture

### Technology Stack

- **Framework:** Laravel 12.x
- **Frontend:** Blade Templates, Bootstrap 5, AdminLTE 3
- **JavaScript:** Vanilla JS, FullCalendar 6.x
- **Database:** MySQL 8.0+ with InnoDB engine
- **Cache:** Redis (production) / File (development)
- **Queue:** Database driver (with Redis support)
- **Build Tool:** Vite 5.x

### Project Structure

```
hotel-booking/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Request handlers
│   │   ├── Middleware/      # Custom middleware
│   │   └── Requests/        # Form validation
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database schema
│   ├── factories/           # Test data factories
│   └── seeders/             # Sample data
├── resources/
│   ├── views/               # Blade templates
│   ├── js/                  # JavaScript files
│   └── css/                 # Stylesheets
├── routes/
│   ├── web.php              # Web routes
│   ├── api.php              # API routes
│   └── auth.php             # Authentication routes
├── tests/
│   ├── Feature/             # Feature tests
│   └── Unit/                # Unit tests
├── docs/                    # Documentation
├── deployment/              # Deployment configs
│   ├── supervisor/          # Supervisor configs
│   └── systemd/             # Systemd services
└── public/                  # Public assets
```

### Key Design Patterns

- **Service Layer** - Business logic separated from controllers
- **Repository Pattern** - Data access abstraction (via Eloquent)
- **Form Requests** - Validation logic encapsulation
- **Events & Listeners** - Decoupled notification system
- **Jobs & Queues** - Asynchronous task processing

## 🔐 Security Features

### Authentication & Authorization
- ✅ Laravel Breeze authentication
- ✅ Role-based access control (Guest, Staff, Admin)
- ✅ Email verification required
- ✅ Password hashing with bcrypt
- ✅ Remember me functionality

### Protection Mechanisms
- ✅ **CSRF Protection** - All forms protected with tokens
- ✅ **XSS Prevention** - Auto-escaping in Blade templates
- ✅ **SQL Injection** - Eloquent ORM parameterized queries
- ✅ **Mass Assignment** - Protected with $fillable arrays
- ✅ **Rate Limiting** - 8 custom rate limiters
- ✅ **Session Security** - Secure cookies, SameSite, HTTP-only
- ✅ **File Upload** - Type and size validation

### Rate Limits
- Login: 5 attempts/minute
- Booking Search: 30 requests/minute
- Booking Creation: 10/min, 50/hour
- Calendar AJAX: 120 requests/minute
- Admin Operations: 100 requests/minute

See [docs/RATE_LIMITING.md](docs/RATE_LIMITING.md) for details.

## 🚀 Deployment

### Production Checklist

#### Server Requirements
- [x] PHP 8.2+ with required extensions
- [x] MySQL 8.0+ database server
- [x] Redis server for caching/queues
- [x] Web server (Apache/Nginx)
- [x] SSL certificate (HTTPS)
- [x] Queue worker process manager (Supervisor/systemd)

#### Configuration
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure proper `APP_URL`
- [ ] Set strong `APP_KEY`
- [ ] Configure database credentials
- [ ] Set up Redis connection
- [ ] Configure Line API credentials
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `QUEUE_CONNECTION=database` or `redis`

#### Deployment Steps

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Build production assets
npm ci
npm run build

# 6. Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Restart queue workers
sudo supervisorctl restart laravel-worker:*
```

#### Queue Workers Setup

Use Supervisor (recommended):

```bash
# Copy config
sudo cp deployment/supervisor/laravel-worker.conf /etc/supervisor/conf.d/

# Update paths in config file
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

See [docs/QUEUE_WORKER_SETUP.md](docs/QUEUE_WORKER_SETUP.md) for detailed instructions.

#### Web Server Configuration

**Nginx Example:**

```nginx
server {
    listen 80;
    server_name booking.example.com;
    root /var/www/hotel-booking/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Example:**

```apache
<VirtualHost *:80>
    ServerName booking.example.com
    DocumentRoot /var/www/hotel-booking/public

    <Directory /var/www/hotel-booking/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/booking-error.log
    CustomLog ${APACHE_LOG_DIR}/booking-access.log combined
</VirtualHost>
```

## 📊 Database Schema

### Core Tables

- **users** - User accounts (guests, staff, admins)
- **meeting_rooms** - Meeting room configurations
- **bookings** - Booking records
- **line_notifications** - Notification log
- **sessions** - User sessions
- **jobs** - Queue jobs
- **failed_jobs** - Failed queue jobs

### Key Relationships

- User has many Bookings
- MeetingRoom has many Bookings
- Booking belongs to User and MeetingRoom
- Booking has many LineNotifications
- User has many LineNotifications

## 🎨 Customization

### Booking Configuration

Edit `config/booking.php`:

```php
return [
    'min_advance_booking' => 24,      // Hours in advance
    'max_advance_booking' => 90,      // Days in advance
    'min_booking_duration' => 60,     // Minutes
    'max_booking_duration' => 480,    // Minutes (8 hours)
    'decoration_themes' => [
        'none' => 'No decoration',
    'corporate' => 'Corporate/Business',
        'seminar' => 'Seminar/Training',
        'meeting' => 'Meeting',
    ],
];
```

### Notification Templates

Templates in `resources/lang/{locale}/notifications.php`:

```php
return [
    'booking_created' => [
        'staff' => 'New booking: :booking_ref...',
        'guest' => 'Booking confirmed: :booking_ref...',
    ],
    // ...
];
```

### Styling

- AdminLTE theme: `public/vendor/adminlte/`
- Custom CSS: `resources/css/app.css`
- Custom JS: `resources/js/app.js`

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Run `php artisan test` before committing
- Use meaningful commit messages

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [AdminLTE](https://adminlte.io/) - Admin dashboard theme
- [FullCalendar](https://fullcalendar.io/) - Calendar integration
- [Line Messaging API](https://developers.line.biz/en/services/messaging-api/) - Notification service
- [Bootstrap](https://getbootstrap.com/) - CSS framework

## 📞 Support

For support and questions:

- **Email:** support@example.com
- **Documentation:** [docs/](docs/)
- **Issues:** [GitHub Issues](https://github.com/yourusername/hotel-booking/issues)

## 🗺️ Roadmap

### Completed ✅
- [x] User authentication and authorization
- [x] Room search and booking
- [x] Line notification integration
- [x] Admin dashboard and management
- [x] Calendar view for staff
- [x] Comprehensive testing suite
- [x] Security hardening (CSRF, XSS, Rate Limiting)
- [x] Performance optimization (Caching, Query optimization)
- [x] Production deployment setup

### Future Enhancements 🚀
- [ ] Email notifications (in addition to Line)
- [ ] Payment integration
- [ ] Recurring bookings
- [ ] Room equipment management
- [ ] Booking approval workflow
- [ ] Reports and analytics
- [ ] Mobile application (React Native)
- [ ] API for third-party integrations

---

**Built with ❤️ using Laravel**

Version: 1.0.0  
Last Updated: November 12, 2025
