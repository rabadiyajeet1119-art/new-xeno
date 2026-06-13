# Transport & Logistics Management System

A comprehensive web-based Transport & Logistics Management System built for college projects. This application supports three user roles: **Customer**, **Driver**, and **Admin**, with features for booking management, shipment tracking, and user administration.

---

## Features

### Public Features
- **Home Page**: Hero section, service previews, statistics, and call-to-action
- **About Page**: Company information, mission, vision, and core values
- **Services Page**: Detailed information about transportation, warehousing, and delivery services
- **Contact Page**: Contact form with database storage and FAQ section
- **Track Page**: Public shipment tracking by Booking ID

### Customer Features
- Registration and login with role selection
- Create new transport bookings
- View booking history and details
- Track shipments in real-time
- View booking status timeline

### Driver Features
- View assigned delivery jobs
- Update delivery status (Pending → In Transit → Delivered)
- Add notes to bookings
- Manage delivery workflow

### Admin Features
- Dashboard with statistics and overview
- Manage all users (view, search, delete)
- Manage all bookings (view, update status, assign drivers, delete)
- View contact form messages
- Export data capabilities

---

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Poppins)

---

## Installation Guide

### Prerequisites
- XAMPP, WAMP, or LAMP server installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

#### 1. Download and Extract
1. Download the project ZIP file
2. Extract the `transport-logistics` folder
3. Copy the folder to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\`
   - **WAMP**: `C:\wamp\www\`
   - **LAMP**: `/var/www/html/`

#### 2. Create Database
1. Open your web browser
2. Navigate to `http://localhost/phpmyadmin`
3. Click on "New" to create a database
4. Enter database name: `transport_logistics`
5. Click "Create"

#### 3. Import Database Schema
1. Select the `transport_logistics` database
2. Click on "Import" tab
3. Click "Choose File" and select `database.sql` from the project folder
4. Click "Go" to import

#### 4. Configure Database Connection
1. Open `includes/config.php` in a text editor
2. Update the database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'transport_logistics');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password (empty for XAMPP default)
```

#### 5. Start the Application
1. Start Apache and MySQL from XAMPP Control Panel
2. Open your web browser
3. Navigate to: `http://localhost/transport-logistics/`

---

## Default Login Credentials

### Admin Account
- **Email**: `admin@example.com`
- **Password**: `Admin@123`

### Customer Account
- **Email**: `customer@example.com`
- **Password**: `Customer@123`

### Driver Account
- **Email**: `driver@example.com`
- **Password**: `Driver@123`

---

## Project Structure

```
transport-logistics/
├── admin/                  # Admin area
│   ├── login.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   ├── users.php          # User management
│   ├── bookings.php       # Booking management
│   └── contacts.php       # Contact messages
├── css/
│   └── style.css          # Custom styles
├── driver/                 # Driver area
│   ├── dashboard.php      # Driver dashboard
│   ├── assigned.php       # Assigned jobs list
│   └── update_status.php  # Update delivery status
├── includes/               # Shared components
│   ├── header.php         # Page header
│   ├── footer.php         # Page footer
│   ├── config.php         # Database configuration
│   ├── functions.php      # Helper functions
│   └── auth_check.php     # Authentication checks
├── js/
│   └── main.js            # JavaScript functions
├── about.php              # About page
├── contact.php            # Contact form
├── create_booking.php     # Create new booking
├── dashboard.php          # Customer dashboard
├── database.sql           # Database schema
├── booking_view.php       # Booking details
├── bookings.php           # Customer bookings list
├── index.php              # Home page
├── login.php              # User login
├── logout.php             # Logout script
├── register.php           # User registration
├── services.php           # Services page
├── track.php              # Shipment tracking
└── README.md              # This file
```

---

## Database Schema

### Tables

#### 1. users
- `id` - Primary key
- `name` - User full name
- `email` - User email (unique)
- `password` - Hashed password
- `phone` - Phone number
- `role` - User role (customer/driver/admin)
- `created_at` - Registration date

#### 2. bookings
- `id` - Primary key
- `user_id` - Customer ID (foreign key)
- `driver_id` - Assigned driver ID (foreign key)
- `pickup_location` - Pickup address
- `delivery_location` - Delivery address
- `goods_type` - Type of goods
- `weight` - Weight in kg
- `delivery_date` - Expected delivery date
- `status` - Booking status (Pending/In Transit/Delivered/Cancelled)
- `notes` - Additional notes
- `created_at` - Booking creation date
- `updated_at` - Last update date

#### 3. contacts
- `id` - Primary key
- `name` - Contact name
- `email` - Contact email
- `subject` - Message subject
- `message` - Message content
- `created_at` - Message date

---

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements for all database queries (SQL injection prevention)
- Input sanitization and validation
- Session-based authentication
- Role-based access control
- CSRF token support (ready for implementation)
- XSS protection through output escaping

---

## Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

---

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Solution**: Check your database credentials in `includes/config.php`

#### 2. 404 Not Found Error
**Solution**: Ensure the project folder is in the correct web server directory

#### 3. CSS/JS Not Loading
**Solution**: Check that all file paths are correct and files exist

#### 4. Login Issues
**Solution**: Verify that the database was imported correctly and users exist

---

---

## Driver Marketplace Feature (New)

### 1. Database Migration
To enable this feature, run the following SQL commands:
- `migrations/2026_add_assignment_and_notifications.sql`

This adds:
- `accepted_by`, `accepted_at`, `driver_note` columns to `bookings`
- `notifications` table
- Index on `accepted_by`
- 'Accepted' status to the `status` enum

### 2. New Endpoints
- `driver/pending_bookings.php`: Returns JSON list of open bookings.
- `driver/accept_booking.php`: Handles atomic assignment of booking to driver.
- `customer/check_updates.php`: Customer polling endpoint for assignment updates.

### 3. Real-time Behavior
- **Driver Dashboard**: Polls every 15 seconds for new open bookings.
- **Customer Dashboard**: Polls every 10 seconds for assignment updates.

### 4. Concurrency Handling
Race conditions are handled via atomic SQL UPDATE with `rowCount()` check:
```sql
UPDATE bookings 
SET accepted_by = ?, ... 
WHERE id = ? AND (accepted_by IS NULL OR accepted_by = 0)
```
Only one driver can successfully update the row.

### 5. Verification Test
To verify concurrency:
1. Create a pending booking.
2. Open two driver sessions (or use a script).
3. Attempt to accept the same booking simultaneously.
4. One should succeed (200 OK), the other fail (409 Conflict).

---

## For Developers


### Adding New Features

1. **New Page**: Create PHP file in appropriate folder
2. **Database Changes**: Update `database.sql` and run migrations
3. **Styles**: Add custom CSS to `css/style.css`
4. **Scripts**: Add JavaScript to `js/main.js`

### Code Standards

- Follow PSR-12 coding standards
- Use prepared statements for all database queries
- Sanitize all user inputs
- Use meaningful variable and function names
- Comment complex code sections

---

## License

This project is created for educational purposes as a college project.

---

## Credits

- Bootstrap 5 - Frontend framework
- Font Awesome - Icons
- Google Fonts - Typography
- PHP - Server-side scripting
- MySQL - Database

---

## Support

For any issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Contact your project supervisor

---

## Future Enhancements

- Email notifications for booking updates
- SMS alerts for drivers and customers
- Real-time GPS tracking integration
- Payment gateway integration
- Multi-language support
- API for mobile applications
- Advanced reporting and analytics
- Barcode/QR code generation for bookings

---

**Thank you for using Transport & Logistics Management System!**
