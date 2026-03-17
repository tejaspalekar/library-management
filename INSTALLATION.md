# Quick Installation Guide

## Step-by-Step Setup for XAMPP MySQL

### 1. Start XAMPP Services
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
```

### 2. Import Database
```
1. Open browser → http://localhost/phpmyadmin
2. Click "New" → Create database named: library_management
3. Select the database
4. Click "Import" tab
5. Choose file: database/library_db.sql
6. Click "Go"
```

### 3. Copy Files
```
Copy entire project folder to:
Windows: C:\xampp\htdocs\library-management\
Mac: /Applications/XAMPP/htdocs/library-management/
Linux: /opt/lampp/htdocs/library-management/
```

### 4. Access Application
```
Open browser → http://localhost/library-management/
```

### 5. Login
```
Admin Login:
Username: admin
Password: admin123

Student Login:
Click "Register here" to create student account
```

## Verification Checklist

- [ ] XAMPP Apache service is running
- [ ] XAMPP MySQL service is running
- [ ] Database "library_management" exists in phpMyAdmin
- [ ] All tables created (users, books, book_issues, fine_settings)
- [ ] Files copied to htdocs folder
- [ ] Can access http://localhost/library-management/
- [ ] Login page appears
- [ ] Can login with admin credentials

## Common Issues

**Cannot connect to database**
- Solution: Check MySQL is running in XAMPP

**Page not found**
- Solution: Ensure files are in correct htdocs directory

**Login fails**
- Solution: Verify database was imported correctly

**No books showing**
- Solution: Check sample data was imported from SQL file

## Default Settings

- **Fine per day**: ₹5.00
- **Grace period**: 0 days
- **Default issue period**: 14 days
- **Database host**: localhost
- **Database user**: root
- **Database password**: (empty)

## Next Steps

1. Login as admin
2. Explore the dashboard
3. Try adding a book (Manage Books)
4. Register a student account
5. Issue a book to the student
6. Test return functionality

## Database Configuration

If you need to change database settings, edit `config/database.php`:

```php
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password (empty for XAMPP)
define('DB_NAME', 'library_management'); // Database name
```

## Need Help?

Refer to the main README.md file for detailed documentation.
