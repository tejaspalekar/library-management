# Library Management System

A comprehensive web-based Library Management System built with PHP and MySQL, designed to run on XAMPP.

## Features

### User Management
- **Student Registration & Login**: Students can register and create their own accounts
- **Admin Login**: Administrators have separate login with elevated privileges
- **Role-based Access Control**: Different features available for students and admins

### Book Management (Admin)
- Add, edit, and delete books
- Track book inventory (total copies and available copies)
- Organize books by category
- Search and filter books
- Track shelf locations

### Book Issuing System (Admin)
- Issue books to students
- Set custom issue periods (7, 14, 21, or 30 days)
- Automatic tracking of due dates
- Prevent issuing books that are not available

### Book Return System (Admin)
- Return issued books
- Automatic fine calculation for overdue books
- View all currently issued books
- Track return history

### Fine Calculation
- Automatic calculation based on overdue days
- Configurable fine per day (default: ₹5.00)
- Grace period support
- Real-time fine preview before returning books

### Student Features
- Browse available books
- Search books by title, author, or ISBN
- Filter books by category
- View currently issued books
- Track due dates and potential fines
- View return history

### Dashboard & Reports
- Statistics overview (total books, available books, issued books, overdue books)
- Visual cards with color-coded information
- Complete issue history for admins
- Personal book history for students

## Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, Bootstrap Icons
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ (via XAMPP)
- **Server**: Apache (via XAMPP)

## Prerequisites

- XAMPP (includes Apache and MySQL)
- Web browser (Chrome, Firefox, Safari, or Edge)
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Installation Instructions

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your computer
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Database

1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Click on "Import" tab
3. Click "Choose File" and select the `database/library_db.sql` file
4. Click "Go" to import the database

   **OR**

   Manually create:
   1. Click "New" to create a new database
   2. Name it `library_management`
   3. Click on the database name
   4. Go to "SQL" tab
   5. Copy and paste the contents of `database/library_db.sql`
   6. Click "Go"

### Step 3: Setup Application Files

1. Copy the entire project folder to XAMPP's `htdocs` directory
   - Default location: `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
   - Example: `C:\xampp\htdocs\library-management\`

2. Verify the folder structure:
   ```
   htdocs/library-management/
   ├── config/
   │   └── database.php
   ├── database/
   │   └── library_db.sql
   ├── includes/
   │   └── functions.php
   ├── index.php
   ├── login.php
   ├── register.php
   ├── books.php
   ├── manage_books.php
   ├── issue_book.php
   ├── return_book.php
   ├── my_books.php
   ├── all_issues.php
   ├── logout.php
   └── README.md
   ```

### Step 4: Configure Database Connection

1. Open `config/database.php`
2. Verify the database credentials (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Empty password for XAMPP
   define('DB_NAME', 'library_management');
   ```

### Step 5: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/library-management/`
3. You will be redirected to the login page

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

### Student Account
Students can register their own accounts using the "Register" link on the login page.

## Usage Guide

### For Administrators

#### 1. Managing Books
- Go to **Manage Books** from the dashboard
- Fill in the form to add new books
- Edit existing books by clicking the pencil icon
- Delete books by clicking the trash icon

#### 2. Issuing Books
- Go to **Issue Book** from the dashboard
- Select the book from the dropdown (only available books shown)
- Select the student
- Choose the issue period (7, 14, 21, or 30 days)
- Click "Issue Book"

#### 3. Returning Books
- Go to **Return Book** from the dashboard
- View all currently issued books with their status
- Check for overdue books (highlighted in yellow)
- See calculated fines for each book
- Click "Return" to process the return
- System automatically updates book availability and records fine

#### 4. Viewing Reports
- Go to **All Issues** to see complete history
- View statistics on the dashboard
- Track overdue books and fines

### For Students

#### 1. Browsing Books
- Go to **Browse Books** from the dashboard
- Use search to find books by title, author, or ISBN
- Filter by category
- Check availability status

#### 2. Viewing Issued Books
- Go to **My Books** from the dashboard
- See currently issued books with due dates
- Check for potential fines if overdue
- View return history

## Database Structure

### Tables

1. **users** - Stores user information (students and admins)
   - user_id, username, password, full_name, email, phone, user_type

2. **books** - Stores book information
   - book_id, isbn, title, author, publisher, category, total_copies, available_copies, shelf_location

3. **book_issues** - Tracks book issues and returns
   - issue_id, book_id, user_id, issue_date, due_date, return_date, fine_amount, status

4. **fine_settings** - Stores fine configuration
   - setting_id, fine_per_day, grace_period_days

## Fine Calculation Logic

- Fine is calculated based on the number of days overdue
- Default fine: ₹5.00 per day
- Grace period: 0 days (configurable)
- Formula: `Fine = (Days Overdue - Grace Period) × Fine Per Day`
- Fine is automatically calculated when viewing issued books or processing returns

## Security Features

- Password hashing using PHP's `password_hash()` function
- SQL injection prevention using prepared statements
- Input sanitization
- Session-based authentication
- Role-based access control
- CSRF protection through form validation

## Customization

### Changing Fine Settings

Edit directly in the database:
```sql
UPDATE fine_settings SET fine_per_day = 10.00, grace_period_days = 1;
```

### Adding More Admins

Register as a student, then update the database:
```sql
UPDATE users SET user_type = 'admin' WHERE username = 'your_username';
```

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP Control Panel
- Check database credentials in `config/database.php`
- Verify database exists in phpMyAdmin

### Page Not Found (404)
- Ensure files are in the correct directory (`htdocs/library-management/`)
- Check Apache is running in XAMPP Control Panel
- Verify URL: `http://localhost/library-management/`

### Login Not Working
- Clear browser cookies and cache
- Verify user exists in database
- Check password (default admin password: `admin123`)

### Books Not Showing
- Ensure database is imported correctly
- Check sample data exists in `books` table
- Verify database connection

## File Structure

```
library-management/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── library_db.sql        # Database schema and sample data
├── includes/
│   └── functions.php         # Common functions
├── index.php                 # Dashboard
├── login.php                 # Login page
├── register.php              # Student registration
├── logout.php                # Logout handler
├── books.php                 # Browse books
├── manage_books.php          # Admin: Add/Edit/Delete books
├── issue_book.php            # Admin: Issue books
├── return_book.php           # Admin: Return books
├── my_books.php              # Student: View issued books
├── all_issues.php            # Admin: All issues history
└── README.md                 # Documentation
```

## Features in Detail

### Responsive Design
- Mobile-friendly interface using Bootstrap 5
- Adaptive layouts for different screen sizes
- Touch-friendly buttons and forms

### Real-time Updates
- Automatic availability tracking
- Live fine calculation
- Status updates (issued/returned/overdue)

### Search & Filter
- Multi-field search (title, author, ISBN)
- Category-based filtering
- Real-time search results

## License

This project is created for educational purposes.

## Support

For issues or questions:
1. Check the Troubleshooting section
2. Verify XAMPP services are running
3. Check browser console for errors
4. Verify database connection

## Version

Version 1.0.0 - Initial Release

## Credits

Developed using:
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0
- PHP & MySQL
- XAMPP Stack
