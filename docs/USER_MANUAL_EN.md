# 📚 Meeting Room Booking System User Manual

## 📖 Table of Contents

1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [User Guide](#user-guide)
4. [Staff Guide](#staff-guide)
5. [Administrator Guide](#administrator-guide)
6. [Troubleshooting](#troubleshooting)
7. [Support Contact](#support-contact)

---

## System Overview

### Smart Meeting Room Booking System

An online meeting room booking system that makes managing and reserving meeting rooms easy, fast, and efficient.

**Key Features:**
- 🔍 Search available rooms by time and number of participants
- 📅 Real-time booking calendar
- 🔔 LINE notifications
- 🎨 Room decoration themes selection
- 📊 Reports and usage statistics
- 📺 Room status display screens

---

## Getting Started

### 1. Registration

1. Visit the website `https://kankoon.ddstudioex.com`
2. Click **"Register"**
3. Fill in the information:
   - Full name
   - Email
   - Password (minimum 8 characters)
4. Click **"Register"**
5. Check your email to verify your account

### 2. Login

1. Click **"Login"**
2. Enter your email and password
3. Click **"Login"**

### 3. LINE Connection (Recommended)

1. Go to **"Profile"** menu
2. In the **"LINE Connection"** section, click **"Connect with LINE"**
3. Login with your LINE account
4. Allow the app to access your information
5. Once connected, you will receive notifications via LINE

**Benefits:**
- ✅ Receive notifications when booking is confirmed
- ✅ Reminder 24 hours before meeting time
- ✅ Notification when booking is cancelled

---

## User Guide

### Booking a Meeting Room

#### Step 1: Search for Available Rooms

1. Click **"Search Meeting Rooms"** from the homepage
2. Fill in the information:
   - **Number of Participants**: Specify the number of attendees
   - **Start Date and Time**: Select desired date and time
   - **End Date and Time**: Select end time (duration 1-8 hours)
3. Click **"Search Available Rooms"**

**Tips:**
- 💡 Use Quick Select buttons for popular participant numbers (5, 10, 20, 50 people)
- ⏱️ Use quick duration selection buttons (1, 2, 3, 4 hours)

#### Step 2: Select a Meeting Room

1. The system will display available rooms based on your criteria
2. View room details:
   - Room name
   - Maximum capacity
   - Location (building/floor)
   - Description
3. Click **"Book This Room"**

#### Step 3: Fill in Booking Details

1. Review booking information
2. Select **Decoration Theme** (optional):
   - 💼 Business Theme - Formal
   - 💙 Blue - White Theme
   - 💗 Pink - White Theme
   - 💜 Purple - Gold Theme
   - 💚 Green - White Theme
3. Enter **Notes** (if any)
4. Accept **Terms and Conditions**
5. Click **"Confirm Booking"**

#### Step 4: Receive Confirmation

1. The system will display a Booking Reference number
2. You will receive a confirmation email
3. If LINE is connected, you will receive a LINE message

---

### Managing Bookings

#### View Booking Details

1. Go to **"Dashboard"** or **"Booking History"**
2. Click **"View Details"** for the desired booking
3. View information:
   - Reference number
   - Meeting room
   - Date and time
   - Number of participants
   - Decoration theme
   - Status

#### Cancel a Booking

**Conditions:**
- ⏰ Free cancellation up to 24 hours before meeting time
- ❌ Cannot cancel past bookings

**Steps:**
1. Go to booking details
2. Click **"Cancel Booking"**
3. Confirm cancellation
4. The system will send email and LINE notifications

#### Print Booking

1. Go to booking details
2. Click **"Print"**
3. Select printer
4. Click **"Print"**

---

### Viewing Calendar

1. Click **"Calendar"** from the menu
2. View all bookings in calendar format
3. Click on an event to view details
4. Event colors:
   - 🟢 **Green**: Confirmed booking
   - 🔴 **Red**: Cancelled booking
   - 🔵 **Blue**: Completed booking

---

### Profile Settings

#### Edit Personal Information

1. Go to **"Profile"**
2. Edit:
   - Name
   - Email
3. Click **"Save"**

#### Change Password

1. Go to **"Profile"**
2. In the **"Change Password"** section
3. Fill in:
   - Current password
   - New password
   - Confirm password
4. Click **"Save"**

#### Manage LINE Connection

**Connect:**
1. Click **"Connect with LINE"**
2. Login to LINE
3. Allow access to information

**Disconnect:**
1. Click **"Disconnect"**
2. Confirm disconnection

---

## Staff Guide

### Staff Responsibilities

- 👀 View and manage all bookings
- ✅ Confirm or cancel bookings
- 📅 View booking calendar
- 📊 View usage reports

### Managing Bookings

#### View All Bookings

1. Login as staff
2. Go to **"Bookings"** > **"All Bookings"**
3. View booking list with filters:
   - All
   - Confirmed
   - Cancelled
   - Completed

#### Confirm Booking

1. Open booking details
2. Review information
3. Click **"Confirm Booking"** (if status is Pending)
4. The system will send notification to the user

#### Cancel Booking

1. Open booking details
2. Click **"Cancel Booking"**
3. Confirm cancellation
4. The system will send notification to the user

#### Mark as Completed

1. Open booking details
2. Click **"Mark as Completed"**
3. Confirm

### Viewing Calendar

1. Go to **"Calendar"**
2. View all bookings in calendar format
3. Can filter by meeting room
4. Click on an event to view details or edit

### Viewing Room Status

#### All Rooms Status Display

1. Go to **"Room Status"** or `/room-status`
2. View real-time status of all rooms:
   - 🟢 **Green**: Available
   - 🔵 **Blue**: Booked (not yet in use)
   - 🟡 **Yellow**: Booked within 30 minutes
   - 🔴 **Red**: Occupied (in use)
3. Auto-updates every 30 seconds

#### Individual Room Status Display

1. Go to `/room-status/{id}` (e.g., `/room-status/1`)
2. Suitable for display outside meeting rooms
3. Shows:
   - Room name and capacity
   - Current status (available/occupied/booked)
   - Current user (if any)
   - Next booking
   - Today's booking schedule

---

## Administrator Guide

### Administrator Responsibilities

- 🏢 Manage meeting rooms
- 👥 Manage users and permissions
- 🎨 Manage decoration themes
- 📊 View reports and statistics
- ⚙️ System configuration

### Managing Meeting Rooms

#### Add New Meeting Room

1. Go to **"Meeting Rooms"** > **"Add New Room"**
2. Fill in information:
   - Room name
   - Maximum capacity
   - Location (building/floor)
   - Description
   - Status (Active/Inactive)
3. Upload room image (if any)
4. Click **"Save"**

#### Edit Meeting Room

1. Go to **"Meeting Rooms"**
2. Click **"Edit"** for the desired room
3. Edit information
4. Click **"Save"**

#### Deactivate Meeting Room

1. Go to room edit page
2. Change status to **"Inactive"**
3. Click **"Save"**
4. Room will not appear in search, but data remains

#### Delete Meeting Room

**⚠️ Warning:** Deleting a room will delete all related booking data

1. Go to room edit page
2. Click **"Delete Room"**
3. Confirm deletion

### Managing Users

#### View User List

1. Go to **"Users"**
2. View all users
3. Filter by role:
   - Admin (Administrator)
   - Staff (Staff)
   - Guest (Regular User)

#### Change User Role

1. Open user details
2. Select new **Role**
3. Click **"Save"**

**Roles and Permissions:**
- **Admin**: Access to all functions
- **Staff**: Manage bookings, view reports
- **Guest**: Book rooms, view own history

#### Suspend User

1. Open user details
2. Click **"Suspend Account"**
3. Confirm
4. User will not be able to login

### Managing Decoration Themes

#### Add New Theme

1. Go to **"Decoration Themes"** > **"Add New Theme"**
2. Fill in information:
   - Theme name
   - Description
   - Primary color
   - Status
3. Click **"Save"**

#### Edit/Delete Theme

1. Go to **"Decoration Themes"**
2. Click **"Edit"** or **"Delete"**
3. Make changes/confirm deletion

### Viewing Reports and Statistics

#### Room Usage Report

1. Go to **"Reports"** > **"Room Usage"**
2. Select time period
3. View statistics:
   - Most frequently used rooms
   - Utilization rate
   - Number of bookings per room

#### Booking Report

1. Go to **"Reports"** > **"Bookings"**
2. View statistics:
   - Total number of bookings
   - Bookings by status
   - Cancellation rate

#### Export Data

1. Select desired report
2. Click **"Export"**
3. Choose format (Excel, PDF)
4. Download file

### System Configuration

#### General Settings

1. Go to **"Settings"** > **"General"**
2. Configure:
   - System name
   - Logo
   - Timezone
   - Default language

#### Booking Settings

1. Go to **"Settings"** > **"Bookings"**
2. Configure:
   - Minimum booking duration (hours)
   - Maximum booking duration (hours)
   - Maximum advance booking (days)
   - Free cancellation period (hours)

#### LINE Notification Settings

1. Go to **"Settings"** > **"LINE Notification"**
2. Fill in:
   - LINE Channel ID
   - LINE Channel Secret
   - LINE Channel Access Token
3. Click **"Test Message Sending"**
4. Click **"Save"**

**How to get Credentials:**
1. Go to https://developers.line.biz/console/
2. Create LINE Login Channel
3. Create Messaging API Channel
4. Link both channels together
5. Copy credentials

---

## Troubleshooting

### Issue: Forgot Password

**Solution:**
1. Click **"Forgot Password?"** on the login page
2. Enter your registered email
3. Click **"Send Reset Link"**
4. Check your email
5. Click the link in the email
6. Set a new password

### Issue: Not Receiving Confirmation Email

**Solution:**
1. Check Spam/Junk folder
2. Verify email address is correct
3. Click **"Resend Confirmation Email"**
4. Wait 5-10 minutes
5. If still not received, contact system administrator

### Issue: Not Receiving LINE Notifications

**Solution:**
1. Check if LINE is connected (go to Profile)
2. Check if Official Account is blocked
3. Try disconnecting and reconnecting LINE
4. Contact system administrator

### Issue: No Available Rooms Found

**Solution:**
1. Try changing the time
2. Reduce number of participants
3. Select a room with similar capacity
4. Check if room is active

### Issue: Cannot Cancel Booking

**Possible Reasons:**
1. Exceeded free cancellation period (24 hours before meeting)
2. Booking is already completed
3. Booking is already cancelled

**Solution:**
- Contact staff to request cancellation

### Issue: Website Error (404, 500)

**Solution:**
1. Refresh the page (F5)
2. Clear browser cache (Ctrl+Shift+Delete)
3. Try a different browser
4. Check internet connection
5. Wait a moment and try again
6. Report to system administrator

---

## Support Contact

- 📧 **Email:** support@kankoon.ddstudioex.com
- 📞 **Phone:** [Phone Number]
- 💬 **LINE Official:** @kankoon
- 🌐 **Website:** https://kankoon.ddstudioex.com

**Business Hours:**
- Monday - Friday: 08:00 - 17:00
- Saturday - Sunday: Closed

---

## System Requirements

**Recommended Browsers:**
- Google Chrome (latest)
- Mozilla Firefox (latest)
- Microsoft Edge (latest)
- Safari (latest)

**Minimum Requirements:**
- Screen: 1024x768 pixels or higher
- Internet: Broadband connection
- JavaScript: Enabled

---

**Version:** 1.0.0  
**Last Updated:** November 13, 2025  
**Developed by:** DD Studio EX

---

© 2025 Smart Meeting Room Booking System. All rights reserved.
