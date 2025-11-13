# Research: Hotel Meeting Room Booking System

**Feature**: 001-meeting-room-booking  
**Date**: 2025-11-11  
**Phase**: 0 - Technical Research & Decision Documentation

## Purpose

This document consolidates technical research findings and architectural decisions for implementing the hotel meeting room booking system using Laravel, Blade templates, AdminLTE, Bootstrap, and MySQL.

## Technology Stack Decisions

### 1. Framework: Laravel 12.x

**Decision**: Use Laravel 12.x as the primary PHP framework

**Rationale**:
- **MVC Architecture**: Clean separation of concerns aligns with code quality principles
- **Eloquent ORM**: Simplifies database interactions with expressive syntax and relationship handling
- **Built-in Authentication**: Laravel Breeze/Fortify provides secure auth out-of-the-box
- **Migration System**: Version-controlled database schema changes
- **Testing Tools**: PHPUnit integration with Feature and Unit test support
- **Queue System**: Async processing for Line notifications prevents blocking
- **Middleware**: Role-based access control for guest vs. staff features
- **Validation**: Robust form validation with customizable error messages
- **Localization**: Multi-language support (English/Thai) built-in

**Alternatives Considered**:
- **Symfony**: More complex, steeper learning curve, Laravel more suitable for rapid development
- **CodeIgniter**: Lighter but lacks modern features like built-in queues and sophisticated ORM
- **Raw PHP**: Would require building all framework features from scratch

**Best Practices**:
- Use Service layer for business logic (not in Controllers)
- Repository pattern for complex queries (optional, use when needed)
- Form Request classes for validation
- Laravel Events/Listeners for decoupled notification system
- Resource Controllers for CRUD operations
- API Resources for structured JSON responses

### 2. Frontend: Blade + AdminLTE + Bootstrap

**Decision**: Server-side rendering with Blade templates, AdminLTE for admin UI, Bootstrap for styling

**Rationale**:
- **Blade Templates**: Native Laravel templating with component reusability
- **AdminLTE 3.x**: Professional admin dashboard with pre-built components (calendars, cards, tables)
- **Bootstrap 5**: Mobile-first responsive grid system, accessibility support
- **SEO-Friendly**: Server-side rendering better for search engines
- **Performance**: No heavy JavaScript framework overhead
- **Progressive Enhancement**: Can add Vue.js/Alpine.js components where needed

**Alternatives Considered**:
- **Vue.js SPA**: Would require separate API, more complex, overkill for this use case
- **React SPA**: Same drawbacks as Vue for this primarily CRUD application
- **Livewire**: Interesting but adds complexity, standard Blade sufficient

**Best Practices**:
- Use Blade components for reusable UI elements (room cards, booking forms)
- Implement layouts for Guest vs Admin areas
- Use Bootstrap utility classes for responsive design
- AdminLTE sidebar for admin navigation
- AJAX for real-time calendar updates (progressive enhancement)
- Vite for asset bundling and compilation

### 3. Database: MySQL 8.0+

**Decision**: MySQL 8.0+ for relational data storage

**Rationale**:
- **ACID Compliance**: Critical for booking transactions (prevent double-bookings)
- **Row-Level Locking**: Ensures concurrent booking safety
- **Performance**: Excellent for relational queries (rooms, bookings, users)
- **Indexing**: Supports complex queries for availability searches
- **JSON Support**: Can store flexible theme configurations if needed
- **Replication**: Scalability option for read-heavy workloads
- **Laravel Compatibility**: First-class Eloquent support

**Alternatives Considered**:
- **PostgreSQL**: Excellent but MySQL more common in shared hosting, easier deployment
- **SQLite**: Too limited for production concurrent access
- **MongoDB**: NoSQL not suitable for transactional booking system

**Best Practices**:
- Index foreign keys and frequently queried columns (date, room_id, status)
- Use database transactions for booking creation
- Implement soft deletes for audit trail
- Use migrations for all schema changes
- Connection pooling (default in Laravel)
- Query optimization with eager loading (N+1 prevention)

### 4. Line Messaging Integration

**Decision**: Official Line Messaging API SDK for PHP

**Rationale**:
- **Official SDK**: line/line-bot-sdk-php maintained by Line Corporation
- **Push Messages**: Send notifications to specific users/groups
- **Webhook Support**: Can receive messages from users (future feature)
- **Rich Messages**: Support for cards, buttons, images
- **Reliability**: Official support and documentation

**Best Practices**:
- Queue Line notifications using Laravel Queue system
- Graceful degradation if Line API unavailable (log failure, don't block booking)
- Store Line User IDs in user table (optional field)
- Use Laravel Events to trigger notifications
- Rate limiting to respect Line API limits
- Template messages for consistency

### 5. Authentication & Authorization

**Decision**: Laravel's built-in authentication with role-based access control

**Rationale**:
- **Session-Based Auth**: Standard for web applications, meets <200ms requirement
- **Password Hashing**: Bcrypt by default, secure
- **CSRF Protection**: Built-in middleware
- **Middleware**: Easy role-based access control
- **Password Reset**: Email-based recovery built-in
- **Remember Me**: Persistent login support

**Implementation Approach**:
- Single `users` table with `role` column (guest, staff, admin)
- Middleware to restrict routes by role
- Polymorphic relationship for Guest/Staff specific data if needed
- Laravel Gates/Policies for fine-grained permissions

**Best Practices**:
- Use Laravel Breeze for scaffolding
- Implement custom middleware for role checks
- Session driver: Redis for scalability (or database for simplicity)
- API tokens if building mobile app later (Laravel Sanctum)

### 6. Testing Strategy

**Decision**: PHPUnit for unit/integration tests, Laravel Dusk for browser tests

**Rationale**:
- **PHPUnit**: Built into Laravel, industry standard
- **Feature Tests**: Test entire user journeys (HTTP requests → database → response)
- **Unit Tests**: Test Models, Services in isolation
- **Laravel Dusk**: Selenium-based browser testing for critical flows
- **Factories/Seeders**: Generate test data easily

**Testing Layers**:
1. **Unit Tests**: Models (relationships, scopes), Services (business logic)
2. **Feature Tests**: Controllers, routes, middleware, validation
3. **Browser Tests**: Critical flows (booking process, calendar interaction)
4. **Contract Tests**: API endpoint structure validation

**Best Practices**:
- Use in-memory SQLite for faster tests
- Database transactions to rollback test data
- Mock external services (Line API)
- Test factories for each model
- Separate test database
- Run tests in CI/CD pipeline

### 7. Performance Optimization

**Decision**: Laravel caching, query optimization, eager loading

**Strategies**:
- **Query Caching**: Cache room availability lookups
- **Eager Loading**: Prevent N+1 queries with `with()` method
- **Database Indexing**: Index `bookings` table by date, room_id, status
- **Redis Caching**: Cache frequently accessed data (room lists, themes)
- **Asset Optimization**: Vite for minification, code splitting
- **CDN**: Serve static assets (Bootstrap, AdminLTE) from CDN
- **Lazy Loading**: Images and non-critical scripts
- **Database Connection Pooling**: Laravel's default configuration

**Monitoring**:
- Laravel Telescope for local debugging
- Laravel Horizon for queue monitoring
- Custom logging for slow queries (>100ms)
- Performance metrics in production logs

### 8. Concurrency & Double-Booking Prevention

**Decision**: Database transactions with row-level locking

**Implementation**:
```php
DB::transaction(function () use ($bookingData) {
    // Lock the room for the time slot
    $existingBooking = Booking::where('room_id', $bookingData['room_id'])
        ->where('start_time', '<', $bookingData['end_time'])
        ->where('end_time', '>', $bookingData['start_time'])
        ->lockForUpdate()
        ->first();
    
    if ($existingBooking) {
        throw new BookingConflictException();
    }
    
    // Create booking
    $booking = Booking::create($bookingData);
    
    return $booking;
});
```

**Rationale**:
- MySQL row-level locking prevents simultaneous bookings
- Transaction ensures atomicity
- ACID properties guarantee consistency
- Pessimistic locking appropriate for critical booking flow

### 9. Calendar & Status Dashboard

**Decision**: FullCalendar.js for calendar UI, AJAX for real-time updates

**Rationale**:
- **FullCalendar**: Industry-standard JavaScript calendar library
- **AJAX**: Real-time updates without page refresh
- **Server-Side Rendering**: Initial load from Blade, then AJAX for updates
- **Progressive Enhancement**: Works without JS, better with JS

**Implementation**:
- Blade renders initial calendar HTML
- FullCalendar.js enhances with drag-drop, filtering
- AJAX endpoints return JSON for calendar events
- WebSockets optional for real-time updates (can use Laravel Echo + Pusher)

### 10. Multi-Language Support

**Decision**: Laravel's localization system

**Implementation**:
- Language files: `resources/lang/en/` and `resources/lang/th/`
- Middleware to set locale based on user preference
- `__('messages.key')` in Blade templates
- Store user language preference in session/database

**Best Practices**:
- Use translation keys, not hardcoded text
- Separate files for validation, messages, UI labels
- Admin interface to manage translations (optional future enhancement)

## Architecture Decisions

### 1. Monolithic vs Microservices

**Decision**: Monolithic Laravel application

**Rationale**: For this scope (meeting room booking), microservices add unnecessary complexity. A well-structured monolith is:
- Easier to develop and deploy
- Simpler to test
- Lower operational overhead
- Can be refactored to microservices later if needed

### 2. Service Layer Pattern

**Decision**: Implement Service classes for business logic

**Benefits**:
- Controllers stay thin (HTTP concerns only)
- Business logic reusable and testable
- Clear separation of concerns
- Example: `BookingService` handles availability checks, booking creation, conflict resolution

### 3. Event-Driven Notifications

**Decision**: Laravel Events/Listeners for Line notifications

**Flow**:
```
Booking Created → BookingCreated Event → SendLineNotification Listener → Queue Job
```

**Benefits**:
- Decoupled: Booking logic doesn't know about notifications
- Async: Queued jobs prevent blocking
- Testable: Easy to mock events
- Extensible: Add more listeners without changing booking code

### 4. Repository Pattern (Optional)

**Decision**: Use Eloquent directly, add Repositories only for complex queries

**Rationale**:
- Eloquent is already an abstraction over database
- Repository pattern adds overhead for simple CRUD
- Use Repositories for:
  - Complex availability searches
  - Multi-table joins
  - Performance-critical queries
  - When testing requires mocking database

## Security Considerations

### 1. Input Validation

- Use Laravel Form Requests for all user input
- Validate dates, times, capacities, theme selections
- Sanitize HTML output in Blade (automatic escaping)

### 2. SQL Injection Prevention

- Use Eloquent ORM (parameterized queries)
- Never concatenate user input into queries
- Validate all input types

### 3. CSRF Protection

- Laravel CSRF middleware on all POST/PUT/DELETE routes
- `@csrf` directive in forms
- Verify CSRF tokens on all state-changing requests

### 4. XSS Prevention

- Blade templates auto-escape output
- Use `{!! !!}` only for trusted HTML
- Content Security Policy headers

### 5. Authentication Security

- Bcrypt password hashing
- Rate limiting on login attempts
- Password reset tokens with expiration
- Session timeout configuration

### 6. Role-Based Access Control

- Middleware to enforce role-based routes
- Guests cannot access admin routes
- Staff roles (receptionist, manager, admin) with different permissions
- Authorization policies for sensitive actions

### 7. Sensitive Data

- Environment variables for Line API keys, database credentials
- `.env` file not committed to git
- Encrypt sensitive fields in database if needed
- HTTPS in production

## Scalability Considerations

### Immediate Requirements (MVP)
- Single server deployment sufficient for 50-100 concurrent users
- MySQL on same server
- Redis for caching (optional initially)

### Future Scalability (1000+ concurrent users)
- **Application**: Load balancer + multiple Laravel instances
- **Database**: MySQL replication (master-slave)
- **Caching**: Redis cluster
- **Sessions**: Redis-backed sessions for multi-server
- **Queue**: Redis queue with multiple workers
- **Assets**: CDN for static files
- **Monitoring**: Application Performance Monitoring (APM)

## Development Workflow

### Local Development
1. Laravel Homestead or Valet for local environment
2. MySQL database locally
3. Line API sandbox for testing notifications
4. Hot module replacement with Vite

### Version Control
- Git branching strategy (feature branches)
- Main branch protected
- Pull request reviews required

### CI/CD Pipeline
1. Run PHPUnit tests
2. PHP CS Fixer for code style
3. Static analysis (PHPStan)
4. Deploy to staging on merge to main
5. Manual deploy to production

## Deployment Strategy

### Production Environment
- Linux server (Ubuntu 22.04 LTS)
- Nginx web server
- PHP 8.1+ with PHP-FPM
- MySQL 8.0+
- Redis for caching and queues
- Supervisor for queue workers
- SSL certificate (Let's Encrypt)

### Deployment Process
1. Pull latest code from repository
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build` for assets
4. Run database migrations
5. Clear and rebuild caches
6. Restart PHP-FPM and queue workers
7. Run smoke tests

## Risk Mitigation

### Risk 1: Double-Booking
**Mitigation**: Database transactions with row-level locking, comprehensive tests

### Risk 2: Line API Downtime
**Mitigation**: Queue notifications, graceful degradation, retry logic

### Risk 3: Performance Degradation
**Mitigation**: Query optimization, caching, monitoring, load testing

### Risk 4: Data Loss
**Mitigation**: Daily database backups, soft deletes, audit logs

### Risk 5: Security Breach
**Mitigation**: Regular security updates, input validation, HTTPS, security audits

## Open Questions Resolved

All technical decisions have been made based on the user's specification of Laravel, Blade, AdminLTE, Bootstrap, and MySQL. No further clarifications needed for Phase 0.

## Next Steps

Proceed to **Phase 1: Design & Contracts**
- Create data-model.md with entity schemas
- Generate API contracts for all endpoints
- Create quickstart.md for development setup
- Update agent context with technology stack
