# Queue Worker Setup Guide

This guide covers setting up queue workers for the Meeting Room Booking System in production.

## Prerequisites

- PHP 8.2+ installed
- MySQL/MariaDB database configured
- Redis installed (optional, for redis queue driver)
- Supervisor or systemd for process management

## Queue Configuration

The application uses Laravel's queue system for:
- **Line notification sending** - Asynchronous notification delivery
- **Email notifications** - Background email processing
- **Long-running tasks** - Performance optimization

### Queue Driver Options

#### 1. Database Queue (Default - Recommended for Small-Medium Scale)

**Advantages:**
- No additional services required
- Easy to setup and debug
- Transactions support
- Good for moderate traffic

**Configuration (.env):**
```env
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
```

**Setup:**
```bash
# Migrations already exist in database/migrations
php artisan migrate

# Test queue
php artisan queue:work database --once
```

#### 2. Redis Queue (Recommended for High Traffic)

**Advantages:**
- Faster performance
- Better for high-volume applications
- Supports queue priorities
- Lower database load

**Configuration (.env):**
```env
QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

**Setup:**
```bash
# Install Redis
sudo apt-get install redis-server

# Install PHP Redis extension
sudo apt-get install php-redis

# Start Redis
sudo systemctl start redis
sudo systemctl enable redis

# Test queue
php artisan queue:work redis --once
```

## Process Manager Setup

### Option 1: Supervisor (Ubuntu/Debian)

**Install Supervisor:**
```bash
sudo apt-get update
sudo apt-get install supervisor
```

**Configure Worker:**
```bash
# Copy configuration file
sudo cp deployment/supervisor/laravel-worker.conf /etc/supervisor/conf.d/

# Edit paths to match your deployment
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start worker
sudo supervisorctl start laravel-worker:*
```

**Manage Workers:**
```bash
# Check status
sudo supervisorctl status

# Start workers
sudo supervisorctl start laravel-worker:*

# Stop workers
sudo supervisorctl stop laravel-worker:*

# Restart workers (after code deployment)
sudo supervisorctl restart laravel-worker:*

# View logs
tail -f /var/www/hotel-booking/storage/logs/worker.log
```

**Configuration Options:**

- `numprocs=2` - Number of worker processes (adjust based on load)
- `--sleep=3` - Seconds to wait between jobs
- `--tries=3` - Number of retry attempts for failed jobs
- `--max-time=3600` - Maximum seconds a worker should run (1 hour)

### Option 2: Systemd (Alternative)

**Install Service:**
```bash
# Copy service file
sudo cp deployment/systemd/laravel-queue.service /etc/systemd/system/

# Edit paths
sudo nano /etc/systemd/system/laravel-queue.service

# Reload systemd
sudo systemctl daemon-reload

# Enable service
sudo systemctl enable laravel-queue

# Start service
sudo systemctl start laravel-queue
```

**Manage Service:**
```bash
# Check status
sudo systemctl status laravel-queue

# View logs
sudo journalctl -u laravel-queue -f

# Restart service
sudo systemctl restart laravel-queue

# Stop service
sudo systemctl stop laravel-queue
```

## Windows Development Setup

For local Windows development, use artisan command:

```cmd
# Open terminal in project directory
cd c:\project\hotel-booking

# Run queue worker (keep terminal open)
php artisan queue:work database --sleep=3 --tries=3

# Or run in background with start command
start /B php artisan queue:work database
```

**For production Windows Server:**
- Use [NSSM (Non-Sucking Service Manager)](https://nssm.cc/)
- Or Windows Task Scheduler with "Run whether user is logged on or not"

## Monitoring Queue

### Check Queue Status
```bash
# List queued jobs
php artisan queue:monitor database

# Failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Database Monitoring
```sql
-- Check jobs table
SELECT COUNT(*) FROM jobs WHERE queue = 'default';

-- Check failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;
```

### Application Monitoring
```bash
# Check worker logs
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log
```

## Queue Performance Tuning

### Adjust Worker Count

**Low Traffic (< 100 jobs/hour):**
- 1-2 workers sufficient
- `numprocs=1` in supervisor config

**Medium Traffic (100-1000 jobs/hour):**
- 2-4 workers recommended
- `numprocs=3` in supervisor config

**High Traffic (> 1000 jobs/hour):**
- 5-10 workers or more
- Consider Redis queue driver
- `numprocs=8` in supervisor config

### Optimize Worker Settings

```bash
# Fast processing, many jobs
php artisan queue:work database --sleep=1 --tries=3 --timeout=60

# Slow jobs, few workers
php artisan queue:work database --sleep=5 --tries=5 --timeout=300

# Memory management (restart after processing 1000 jobs)
php artisan queue:work database --max-jobs=1000

# Time-based restart (every 1 hour)
php artisan queue:work database --max-time=3600
```

## Deployment Workflow

**After deploying new code:**

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers (IMPORTANT!)
sudo supervisorctl restart laravel-worker:*
# OR
sudo systemctl restart laravel-queue
```

**Important:** Always restart queue workers after deployment to load new code!

## Troubleshooting

### Workers Not Processing Jobs

1. **Check worker is running:**
   ```bash
   sudo supervisorctl status
   # OR
   sudo systemctl status laravel-queue
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/worker.log
   tail -f storage/logs/laravel.log
   ```

3. **Test manually:**
   ```bash
   php artisan queue:work database --once
   ```

4. **Check database connection:**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

### Failed Jobs

**Common causes:**
- Network errors (Line API timeout)
- Invalid data
- Missing dependencies
- Memory limits exceeded

**Resolution:**
```bash
# View failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {id}

# Retry all
php artisan queue:retry all

# Delete failed job
php artisan queue:forget {id}

# Clear all failed
php artisan queue:flush
```

### High Memory Usage

```bash
# Add memory limit flag
php artisan queue:work database --memory=512

# Or restart workers periodically
php artisan queue:work database --max-time=3600 --max-jobs=1000
```

### Queue Backing Up

1. **Increase workers:**
   - Edit supervisor config `numprocs`
   - Reload supervisor

2. **Check for slow jobs:**
   - Review job execution time in logs
   - Optimize slow operations

3. **Switch to Redis:**
   - Better performance for high volume
   - See Redis Queue setup above

## Security Considerations

1. **Run as www-data user** (not root)
2. **Set proper file permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/hotel-booking
   sudo chmod -R 755 /var/www/hotel-booking
   sudo chmod -R 775 /var/www/hotel-booking/storage
   sudo chmod -R 775 /var/www/hotel-booking/bootstrap/cache
   ```

3. **Protect sensitive data:**
   - Queue jobs may contain user data
   - Review `failed_jobs` table access
   - Implement log rotation

4. **Monitor failed jobs:**
   - Set up alerts for high failure rates
   - Regular review of failed job reasons

## Related Documentation

- [Laravel Queue Documentation](https://laravel.com/docs/11.x/queues)
- [Supervisor Documentation](http://supervisord.org/)
- [Systemd Service Documentation](https://www.freedesktop.org/software/systemd/man/systemd.service.html)
- [Redis Queue Configuration](https://laravel.com/docs/11.x/queues#driver-prerequisites)

## Next Steps

After setting up queue workers, configure:
- [Rate Limiting](./RATE_LIMITING.md) - API protection
- [Monitoring](./MONITORING.md) - Application health checks
- [Backup Strategy](./BACKUP.md) - Data protection
