# API Documentation

Hotel Meeting Room Booking System - Internal API Reference

**Version:** 1.0.0  
**Base URL:** `https://booking.yourhotel.com`  
**Protocol:** HTTPS only in production

## Overview

This document describes the internal web API used by the Hotel Meeting Room Booking System. The API is primarily used for AJAX requests from the frontend and does not currently support external integrations.

## Authentication

All API requests require user authentication via Laravel session cookies. The API does not support token-based authentication at this time.

**Authentication Method:** Session-based (Cookies)  
**CSRF Protection:** Required for all POST/PUT/DELETE requests

### Including CSRF Token

**Via Axios (Configured Automatically):**
```javascript
// Already configured in resources/js/bootstrap.js
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
```

**Via Fetch API:**
```javascript
fetch('/api/endpoint', {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    }
})
```

## Rate Limiting

API endpoints are protected by rate limiting. See [RATE_LIMITING.md](RATE_LIMITING.md) for details.

**Common Limits:**
- Calendar Events: 120 requests/minute
- Booking Search: 30 requests/minute
- Booking Creation: 10/minute, 50/hour

**Rate Limit Headers:**
```
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 27
Retry-After: 60 (when rate limited)
```

## Error Responses

### Standard Error Format

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": [
            "The field name is required."
        ]
    }
}
```

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request successful, no content to return |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Not authorized to access resource |
| 404 | Not Found | Resource not found |
| 419 | Page Expired | CSRF token missing or expired |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

## Endpoints

### Calendar API

#### Get Calendar Events

Retrieves booking events for the calendar view.

**Endpoint:** `GET /staff/calendar/events` or `GET /admin/calendar/events`

**Authentication:** Required (Staff or Admin)

**Rate Limit:** 120 requests/minute

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| start | string | Yes | Start date (ISO 8601 format) |
| end | string | Yes | End date (ISO 8601 format) |
| room_id | integer | No | Filter by specific room |

**Example Request:**

```javascript
const params = new URLSearchParams({
    start: '2025-01-01T00:00:00',
    end: '2025-01-31T23:59:59',
    room_id: 3
});

fetch(`/staff/calendar/events?${params}`, {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

**Success Response (200 OK):**

```json
[
    {
        "id": 123,
        "title": "Team Meeting (John Doe)",
        "start": "2025-01-15T10:00:00",
        "end": "2025-01-15T12:00:00",
        "backgroundColor": "#28a745",
        "borderColor": "#28a745",
        "textColor": "#ffffff",
        "extendedProps": {
            "booking_ref": "BK20250115001",
            "room_name": "Conference Room A",
            "attendee_count": 10,
            "status": "confirmed",
            "guest_name": "John Doe",
            "notes": "Quarterly planning meeting"
        }
    }
]
```

**Event Colors by Status:**
- Confirmed: Green (#28a745)
- Cancelled: Red (#dc3545)
- Completed: Gray (#6c757d)

#### Get Available Time Slots

Retrieves available time slots for a specific room and date.

**Endpoint:** `GET /staff/calendar/available-slots`

**Authentication:** Required (Staff or Admin)

**Rate Limit:** 120 requests/minute

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| room_id | integer | Yes | Room ID |
| date | string | Yes | Date (Y-m-d format) |

**Example Request:**

```javascript
fetch('/staff/calendar/available-slots?room_id=1&date=2025-01-15')
    .then(response => response.json())
    .then(data => console.log(data));
```

**Success Response (200 OK):**

```json
{
    "room": {
        "id": 1,
        "name": "Conference Room A",
        "max_capacity": 20
    },
    "date": "2025-01-15",
    "time_slots": [
        {
            "time": "08:00",
            "available": true
        },
        {
            "time": "09:00",
            "available": false,
            "booking": {
                "id": 123,
                "booking_ref": "BK20250115001",
                "guest_name": "John Doe"
            }
        }
    ]
}
```

### Booking API

#### Search Available Rooms

Search for available meeting rooms.

**Endpoint:** `POST /guest/booking/search`

**Authentication:** Required (Authenticated user)

**Rate Limit:** 30 requests/minute

**Request Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| attendee_count | integer | Yes | min:1 |
| start_datetime | string | Yes | date, after:now |
| end_datetime | string | Yes | date, after:start_datetime |

**Example Request:**

```javascript
axios.post('/guest/booking/search', {
    attendee_count: 10,
    start_datetime: '2025-01-15 10:00:00',
    end_datetime: '2025-01-15 12:00:00'
})
.then(response => {
    console.log(response.data);
});
```

**Success Response (200 OK):**

```json
{
    "available_rooms": [
        {
            "id": 1,
            "name": "Conference Room A",
            "description": "Large meeting room with projector",
            "max_capacity": 20,
            "image_path": "/storage/rooms/conference-a.jpg"
        },
        {
            "id": 2,
            "name": "Meeting Room B",
            "description": "Medium-sized room",
            "max_capacity": 12,
            "image_path": null
        }
    ],
    "search_params": {
        "attendee_count": 10,
        "start_datetime": "2025-01-15 10:00:00",
        "end_datetime": "2025-01-15 12:00:00"
    }
}
```

**Validation Error (422):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "start_datetime": [
            "The start datetime must be a date after now."
        ]
    }
}
```

#### Create Booking

Create a new booking.

**Endpoint:** `POST /guest/booking`

**Authentication:** Required (Authenticated user)

**Rate Limit:** 10 requests/minute, 50 requests/hour

**Request Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| room_id | integer | Yes | exists:meeting_rooms,id |
| start_datetime | string | Yes | date, after:now+24h, before:now+90d |
| end_datetime | string | Yes | date, after:start_datetime |
| attendee_count | integer | Yes | min:1 |
| decoration_theme | string | No | max:255 |
| notes | string | No | max:1000 |

**Example Request:**

```javascript
axios.post('/guest/booking', {
    room_id: 1,
    start_datetime: '2025-01-20 10:00:00',
    end_datetime: '2025-01-20 12:00:00',
    attendee_count: 10,
    decoration_theme: 'corporate',
    notes: 'Please prepare coffee and tea'
})
.then(response => {
    console.log('Booking created:', response.data);
})
.catch(error => {
    console.error('Booking failed:', error.response.data);
});
```

**Success Response (201 Created):**

```json
{
    "message": "Booking created successfully",
    "booking": {
        "id": 456,
        "booking_ref": "BK20250120001",
        "room_id": 1,
        "user_id": 10,
        "start_datetime": "2025-01-20 10:00:00",
        "end_datetime": "2025-01-20 12:00:00",
        "attendee_count": 10,
        "decoration_theme": "corporate",
        "notes": "Please prepare coffee and tea",
        "status": "confirmed",
        "created_at": "2025-01-15T08:30:00.000000Z"
    }
}
```

**Validation Error (422):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "start_datetime": [
            "Booking must be made at least 24 hours in advance."
        ],
        "notes": [
            "The notes may not be greater than 1000 characters."
        ]
    }
}
```

**Business Logic Error (400):**

```json
{
    "message": "The selected room is not available for the requested time slot."
}
```

#### Cancel Booking

Cancel an existing booking.

**Endpoint:** `DELETE /guest/booking/{id}/cancel`

**Authentication:** Required (Booking owner only)

**Request Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Booking ID (in URL) |

**Example Request:**

```javascript
axios.delete('/guest/booking/123/cancel')
    .then(response => {
        console.log('Booking cancelled:', response.data);
    });
```

**Success Response (200 OK):**

```json
{
    "message": "Booking cancelled successfully",
    "booking": {
        "id": 123,
        "booking_ref": "BK20250115001",
        "status": "cancelled"
    }
}
```

**Error Responses:**

**403 Forbidden (Not booking owner):**
```json
{
    "message": "You are not authorized to cancel this booking."
}
```

**404 Not Found:**
```json
{
    "message": "Booking not found."
}
```

### Admin API

#### Update Booking Status

Update the status of a booking (Admin only).

**Endpoint:** `PATCH /admin/bookings/{id}/status`

**Authentication:** Required (Admin only)

**Request Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| status | string | Yes | in:confirmed,cancelled,completed |

**Example Request:**

```javascript
axios.patch('/admin/bookings/123/status', {
    status: 'completed'
})
.then(response => console.log(response.data));
```

**Success Response (200 OK):**

```json
{
    "message": "Booking status updated from confirmed to completed",
    "booking": {
        "id": 123,
        "status": "completed"
    }
}
```

#### Toggle Room Status

Enable/disable a meeting room.

**Endpoint:** `POST /admin/rooms/{room}/toggle`

**Authentication:** Required (Admin only)

**Example Request:**

```javascript
axios.post('/admin/rooms/3/toggle')
    .then(response => console.log(response.data));
```

**Success Response (200 OK):**

```json
{
    "message": "Room status updated successfully",
    "room": {
        "id": 3,
        "name": "Conference Room C",
        "is_active": false
    }
}
```

#### Retry Failed Notification

Retry sending a failed Line notification.

**Endpoint:** `POST /admin/notifications/{notification}/retry`

**Authentication:** Required (Admin only)

**Rate Limit:** 5 requests/minute

**Example Request:**

```javascript
axios.post('/admin/notifications/789/retry')
    .then(response => console.log(response.data));
```

**Success Response (200 OK):**

```json
{
    "message": "Notification sent successfully",
    "notification": {
        "id": 789,
        "status": "sent"
    }
}
```

**Error Response (400):**

```json
{
    "message": "Notification can only be retried if status is failed"
}
```

## Frontend Integration

### Axios Configuration

All Axios requests automatically include CSRF token:

```javascript
// Configured in resources/js/bootstrap.js
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
```

### Error Handling

```javascript
axios.post('/guest/booking', data)
    .then(response => {
        // Success
        alert('Booking created successfully!');
    })
    .catch(error => {
        if (error.response) {
            // Server responded with error status
            if (error.response.status === 422) {
                // Validation errors
                const errors = error.response.data.errors;
                Object.keys(errors).forEach(field => {
                    console.error(`${field}: ${errors[field].join(', ')}`);
                });
            } else if (error.response.status === 429) {
                // Rate limit exceeded
                alert('Too many requests. Please try again later.');
            } else {
                // Other error
                alert(error.response.data.message);
            }
        } else {
            // Network error
            alert('Network error. Please check your connection.');
        }
    });
```

### FullCalendar Integration

```javascript
const calendar = new FullCalendar.Calendar(calendarEl, {
    events: function(info, successCallback, failureCallback) {
        const params = new URLSearchParams({
            start: info.startStr,
            end: info.endStr
        });

        fetch(`/staff/calendar/events?${params}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => successCallback(data))
        .catch(error => failureCallback(error));
    }
});
```

## Data Models

### Booking Object

```typescript
interface Booking {
    id: number;
    user_id: number;
    room_id: number;
    booking_ref: string;           // e.g., "BK20250115001"
    start_datetime: string;         // ISO 8601 format
    end_datetime: string;           // ISO 8601 format
    attendee_count: number;
    decoration_theme: string | null;
    notes: string | null;
    status: 'confirmed' | 'cancelled' | 'completed';
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}
```

### Room Object

```typescript
interface MeetingRoom {
    id: number;
    name: string;
    description: string | null;
    max_capacity: number;
    is_active: boolean;
    image_path: string | null;
    created_at: string;
    updated_at: string;
}
```

### Calendar Event Object

```typescript
interface CalendarEvent {
    id: number;
    title: string;
    start: string;                  // ISO 8601 format
    end: string;                    // ISO 8601 format
    backgroundColor: string;        // Hex color
    borderColor: string;            // Hex color
    textColor: string;              // Hex color
    extendedProps: {
        booking_ref: string;
        room_name: string;
        attendee_count: number;
        status: string;
        guest_name: string;
        notes: string | null;
    };
}
```

## Security Considerations

### CSRF Protection

All state-changing requests (POST, PUT, PATCH, DELETE) require a valid CSRF token. Include the token in one of these ways:

1. **X-CSRF-TOKEN header** (recommended for AJAX)
2. **_token field** in form data

### XSS Prevention

All API responses use JSON format. When displaying user-generated content:

```javascript
// ✅ Safe - use textContent
element.textContent = booking.notes;

// ❌ Unsafe - don't use innerHTML with user data
element.innerHTML = booking.notes; // XSS risk!
```

### Authorization

API endpoints check user permissions:

- Guest routes: Authenticated users only
- Staff routes: Staff or Admin role required
- Admin routes: Admin role required
- Booking ownership: Users can only modify their own bookings

## Testing the API

### Using cURL

```bash
# Get calendar events (requires authentication)
curl -X GET "https://booking.yourhotel.com/staff/calendar/events?start=2025-01-01&end=2025-01-31" \
  -H "Cookie: laravel_session=your_session_cookie" \
  -H "X-Requested-With: XMLHttpRequest"
```

### Using Postman

1. Authenticate via web browser first
2. Copy session cookie from browser DevTools
3. Add cookie to Postman requests
4. Include CSRF token in X-CSRF-TOKEN header

## Versioning

Current Version: **1.0.0**

The API does not currently support versioning. Breaking changes will be documented in release notes.

## Support

For API questions or issues:

- See [README.md](../README.md) for general documentation
- Review [docs/](../) for detailed guides
- Create GitHub issue for bugs or feature requests

---

**Last Updated:** November 12, 2025  
**Maintained By:** Development Team
