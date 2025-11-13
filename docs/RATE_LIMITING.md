# Rate Limiting Configuration

## Overview

The Meeting Room Booking System implements comprehensive rate limiting to protect against abuse, brute force attacks, and excessive API usage.

**Date:** 2025-11-12  
**Status:** ✅ ACTIVE

## Rate Limiting Strategy

### Implementation
- **Storage:** Redis (production) / File (development)
- **Identification:** User ID (authenticated) or IP address (guest)
- **Response:** HTTP 429 Too Many Requests
- **Headers:** `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `Retry-After`

### Configuration Location
- **Service Provider:** `app/Providers/RateLimitServiceProvider.php`
- **Routes:** `routes/web.php`, `routes/auth.php`
- **Bootstrap:** `bootstrap/app.php`

## Rate Limit Rules

### 1. Authentication Routes

#### Login Attempts (Brute Force Protection)
- **Limiter:** `login`
- **Rate:** 5 requests per minute
- **Scope:** Per IP address
- **Routes:**
  - `POST /login`

**Purpose:** Prevent brute force password attacks

**Example Response (429):**
```json
{
  "message": "Too many login attempts. Please try again in 60 seconds."
}
```

#### Registration
- **Rate:** 5 requests per minute
- **Scope:** Per IP address
- **Routes:**
  - `POST /register`

**Purpose:** Prevent automated account creation

#### Password Reset
- **Rate:** 3 requests per minute
- **Scope:** Per IP address
- **Routes:**
  - `POST /forgot-password`
  - `POST /reset-password` (5 per minute)

**Purpose:** Prevent password reset abuse

### 2. Booking Routes

#### Booking Search
- **Limiter:** `booking-search`
- **Rate:** 30 requests per minute
- **Scope:** Per user ID or IP address
- **Routes:**
  - `POST /guest/booking/search`

**Purpose:** Prevent spam searches that could overload database

**Custom Response:**
```json
{
  "message": "Too many search requests. Please try again later.",
  "retry_after": 60
}
```

#### Booking Creation
- **Limiter:** `booking-create`
- **Rate:** 
  - 10 requests per minute
  - 50 requests per hour
- **Scope:** Per user ID or IP address
- **Routes:**
  - `POST /guest/booking`

**Purpose:** Prevent rapid booking attempts and room reservation abuse

**Multiple Limits Applied:**
- Short-term burst protection (10/min)
- Long-term abuse protection (50/hour)

### 3. Calendar & AJAX Routes

#### Calendar Events
- **Limiter:** `calendar`
- **Rate:** 120 requests per minute
- **Scope:** Per user ID or IP address
- **Routes:**
  - `GET /staff/calendar/events`
  - `GET /staff/calendar/available-slots`
  - `GET /admin/calendar/events`
  - `GET /admin/calendar/available-slots`

**Purpose:** Allow frequent calendar refreshes while preventing abuse

**Reasoning:** Higher limit due to:
- FullCalendar frequent polling
- Real-time availability checks
- Multiple simultaneous calendar views

### 4. Admin Routes

#### General Admin Operations
- **Limiter:** `admin`
- **Rate:** 100 requests per minute
- **Scope:** Per user ID or IP address
- **Routes:**
  - All routes under `/admin/*` prefix

**Purpose:** Moderate limit for administrative operations

**Note:** Admin users typically have legitimate high-frequency usage

### 5. Notification Routes

#### Notification Retry
- **Limiter:** `notification-retry`
- **Rate:** 5 requests per minute
- **Scope:** Per user ID or IP address
- **Routes:**
  - `POST /admin/notifications/{id}/retry`
  - `POST /admin/notifications/test`

**Purpose:** Prevent spam notification retries to Line API

### 6. API Routes

#### General API
- **Limiter:** `api`
- **Rate:** 60 requests per minute
- **Scope:** Per user ID or IP address
- **Routes:**
  - All routes under `/api/*` prefix

**Purpose:** Standard API rate limiting

### 7. Global Fallback

#### Default Limit
- **Limiter:** `global`
- **Rate:** 60 requests per minute
- **Scope:** Per user ID or IP address

**Purpose:** Catch-all for routes without specific rate limits

## Rate Limit Headers

Every response includes rate limit information:

```http
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 27
Retry-After: 60
```

**Header Descriptions:**
- `X-RateLimit-Limit`: Maximum requests allowed in time window
- `X-RateLimit-Remaining`: Remaining requests in current window
- `Retry-After`: Seconds until rate limit resets (only in 429 responses)

## Error Handling

### Standard 429 Response

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json
Retry-After: 60
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0

{
  "message": "Too Many Requests"
}
```

### Custom JSON Responses

Some endpoints return custom messages (e.g., booking-search):

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json

{
  "message": "Too many search requests. Please try again later.",
  "retry_after": 60
}
```

### Frontend Handling

JavaScript should handle 429 responses gracefully:

```javascript
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 429) {
            const retryAfter = error.response.headers['retry-after'] || 60;
            
            alert(`Too many requests. Please wait ${retryAfter} seconds and try again.`);
            
            // Optional: Auto-retry after delay
            // setTimeout(() => retryRequest(), retryAfter * 1000);
        }
        return Promise.reject(error);
    }
);
```

## Configuration

### Environment Setup

**Production (.env):**
```env
# Use Redis for rate limiting in production
CACHE_STORE=redis
REDIS_CLIENT=phpredis

# Redis connection
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Development (.env):**
```env
# File cache acceptable for development
CACHE_STORE=file
```

### Customizing Rate Limits

Edit `app/Providers/RateLimitServiceProvider.php`:

```php
// Example: Increase booking search limit to 50/min
RateLimiter::for('booking-search', function (Request $request) {
    return Limit::perMinute(50) // Changed from 30
        ->by($request->user()?->id ?: $request->ip());
});
```

### Disable Rate Limiting (Development Only)

**Option 1: Remove middleware from routes**
```php
// Remove ->middleware('throttle:booking-search')
Route::post('/booking/search', [BookingController::class, 'searchResults'])
    ->name('booking.search.results');
```

**Option 2: Set extremely high limits**
```php
RateLimiter::for('booking-search', function (Request $request) {
    return Limit::perMinute(99999);
});
```

**⚠️ WARNING:** Never disable rate limiting in production!

## Testing Rate Limits

### Manual Testing

```bash
# Test login rate limit (should block after 5 attempts)
for i in {1..10}; do
  curl -X POST http://localhost/login \
    -d "email=test@example.com&password=wrong" \
    -H "Content-Type: application/x-www-form-urlencoded"
done
```

### Automated Testing

Create test to verify rate limiting:

```php
public function test_booking_search_rate_limit()
{
    $user = User::factory()->create();
    
    // Make 30 requests (within limit)
    for ($i = 0; $i < 30; $i++) {
        $response = $this->actingAs($user)
            ->post('/guest/booking/search', [
                'start_datetime' => now()->addHours(1),
                'end_datetime' => now()->addHours(2),
                'attendee_count' => 5,
            ]);
        
        $response->assertStatus(200);
    }
    
    // 31st request should be rate limited
    $response = $this->actingAs($user)
        ->post('/guest/booking/search', [
            'start_datetime' => now()->addHours(1),
            'end_datetime' => now()->addHours(2),
            'attendee_count' => 5,
        ]);
    
    $response->assertStatus(429);
    $response->assertJson([
        'message' => 'Too many search requests. Please try again later.'
    ]);
}
```

### Check Rate Limit Status

```php
use Illuminate\Support\Facades\RateLimiter;

// Check if user has exceeded limit
$key = 'booking-search:' . auth()->id();
$remaining = RateLimiter::remaining($key, 30);

if ($remaining === 0) {
    // Rate limit exceeded
    $seconds = RateLimiter::availableIn($key);
    echo "Rate limit exceeded. Try again in {$seconds} seconds.";
}
```

## Monitoring

### Log Rate Limit Events

Add to `app/Providers/RateLimitServiceProvider.php`:

```php
use Illuminate\Support\Facades\Log;

protected function configureRateLimiting(): void
{
    // ... existing rate limiters ...
    
    // Log rate limit hits
    Event::listen(function (\Illuminate\Routing\Events\ResponsePrepared $event) {
        if ($event->response->getStatusCode() === 429) {
            Log::warning('Rate limit exceeded', [
                'ip' => request()->ip(),
                'user_id' => auth()->id(),
                'route' => request()->path(),
                'method' => request()->method(),
            ]);
        }
    });
}
```

### Redis Monitoring

```bash
# Connect to Redis
redis-cli

# Check rate limit keys
KEYS *:timer
KEYS *:attempts

# Check specific user's rate limit
GET "booking-search:{user_id}:timer"
GET "booking-search:{user_id}:attempts"

# Clear specific rate limit (testing only)
DEL "booking-search:{user_id}:timer"
DEL "booking-search:{user_id}:attempts"
```

## Security Best Practices

### ✅ Implemented

1. **IP-based limiting for unauthenticated users**
   - Prevents anonymous abuse
   - Based on `$request->ip()`

2. **User ID-based limiting for authenticated users**
   - More accurate tracking
   - Survives IP changes (mobile users)

3. **Multiple time windows**
   - Short-term burst protection (per minute)
   - Long-term abuse protection (per hour)

4. **Brute force protection**
   - Login: 5 attempts per minute
   - Password reset: 3 attempts per minute

5. **Custom error messages**
   - User-friendly feedback
   - Includes retry-after information

### 🔒 Additional Recommendations

1. **Monitor for distributed attacks**
   - Multiple IPs attacking same user
   - Implement CAPTCHA for repeated failures

2. **Implement progressive delays**
   - Exponential backoff for repeated violations
   - Temporary bans for severe abuse

3. **Whitelist trusted IPs**
   - Admin office IPs
   - Monitoring services
   - Integration partners

4. **Rate limit by endpoint sensitivity**
   - Stricter limits on write operations
   - Looser limits on read-only operations

5. **Geographic restrictions (optional)**
   - Block requests from unexpected countries
   - Requires GeoIP database

## Troubleshooting

### Issue: Legitimate users getting blocked

**Cause:** Rate limits too strict for normal usage

**Solution:**
1. Review logs to identify usage patterns
2. Increase specific rate limits if justified
3. Implement user-specific overrides for VIP users

### Issue: Rate limits not working

**Cause:** Redis not configured or not running

**Solution:**
```bash
# Check Redis status
redis-cli ping
# Should return: PONG

# Check Laravel cache driver
php artisan config:clear
php artisan cache:clear

# Verify .env settings
CACHE_STORE=redis
```

### Issue: Rate limits reset too quickly

**Cause:** Cache keys expiring prematurely

**Solution:**
- Check Redis memory limits
- Ensure Redis `maxmemory-policy` is not `volatile-lru`
- Increase Redis memory allocation

### Issue: Different users share rate limit

**Cause:** Incorrect rate limit key

**Solution:**
```php
// ❌ Wrong - all users share same limit
return Limit::perMinute(10)->by('booking');

// ✅ Correct - per user limit
return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
```

## Production Checklist

- [x] Redis configured as cache driver
- [x] Rate limiters registered in RateLimitServiceProvider
- [x] Critical routes protected (login, booking, admin)
- [x] Custom error messages for user-facing endpoints
- [x] Multiple time windows for booking creation
- [x] IP-based limiting for guest users
- [x] User ID-based limiting for authenticated users
- [ ] Monitoring/alerting for rate limit violations
- [ ] CAPTCHA fallback for repeated blocks (optional)
- [ ] Whitelist for trusted IPs (optional)

## References

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/11.x/routing#rate-limiting)
- [Redis Configuration](https://redis.io/docs/management/config/)
- [OWASP Rate Limiting Guide](https://cheatsheetseries.owasp.org/cheatsheets/Denial_of_Service_Cheat_Sheet.html)

## Summary

The application implements **8 different rate limiting strategies** across:
- ✅ Authentication (login, registration, password reset)
- ✅ Booking operations (search, creation)
- ✅ Calendar/AJAX endpoints
- ✅ Admin operations
- ✅ Notification management
- ✅ API routes

**Total Protected Endpoints:** 20+ routes with rate limiting

**Next Steps:**
- Monitor rate limit violations in production
- Adjust limits based on actual usage patterns
- Implement alerting for suspicious activity
