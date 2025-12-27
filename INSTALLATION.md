# Installation Guide - ERP Multi-Company System

This guide will walk you through the complete installation process for the ERP Multi-Company system.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP**: Version 8.1 or higher
  ```bash
  php -v
  ```
- **Composer**: Latest version
  ```bash
  composer --version
  ```
- **MySQL**: Version 8.0 or higher
  ```bash
  mysql --version
  ```
- **Web Server**: Apache or Nginx with mod_rewrite enabled

### Required PHP Extensions

Ensure the following PHP extensions are installed and enabled:

- intl
- mbstring
- json (enabled by default)
- mysqlnd
- libcurl

Check with:
```bash
php -m | grep -E 'intl|mbstring|mysqlnd|curl'
```

## Installation Steps

### Step 1: Clone the Repository

```bash
git clone https://github.com/jakarias1907/erp-multicompany.git
cd erp-multicompany
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

This will install all required dependencies including:
- CodeIgniter 4 Framework
- dompdf (for PDF generation)
- phpspreadsheet (for Excel export)

### Step 3: Configure Environment

1. Copy the environment file:
   ```bash
   cp env .env
   ```

2. Open `.env` in your text editor and update the following settings:

   **Basic Configuration:**
   ```env
   CI_ENVIRONMENT = development
   ```

   **Application URL:**
   ```env
   app.baseURL = 'http://localhost:8080/'
   # For production, use your actual domain:
   # app.baseURL = 'https://yourdomain.com/'
   ```

   **Database Configuration:**
   ```env
   database.default.hostname = localhost
   database.default.database = erp_multicompany
   database.default.username = root
   database.default.password = your_password_here
   database.default.DBDriver = MySQLi
   database.default.DBPrefix = 
   database.default.port = 3306
   ```

3. Set appropriate file permissions:
   ```bash
   chmod 664 .env
   chmod -R 775 writable/
   ```

### Step 4: Create Database

#### Option 1: Using MySQL Command Line

```bash
mysql -u root -p
```

Then execute:
```sql
CREATE DATABASE erp_multicompany CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
EXIT;
```

#### Option 2: Using phpMyAdmin

1. Open phpMyAdmin in your browser
2. Click on "New" in the left sidebar
3. Enter database name: `erp_multicompany`
4. Select Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 5: Run Database Migrations

Execute migrations to create all database tables:

```bash
php spark migrate
```

You should see output like:
```
Migration: 2025-12-27-153942_CreateCompaniesTable
Running: 2025-12-27-153942_CreateCompaniesTable
Migration: 2025-12-27-153942_CreateCompaniesTable
Migrated: 2025-12-27-153942_CreateCompaniesTable
...
```

To verify all migrations ran successfully:
```bash
php spark migrate:status
```

### Step 6: Seed Initial Data

Populate the database with initial data:

```bash
php spark db:seed InitialDataSeeder
```

This will create:
- ✅ Demo company
- ✅ Super admin user
- ✅ Default roles (Super Admin, Company Admin, Manager, Staff)
- ✅ 105 permissions across all modules
- ✅ Default warehouse
- ✅ Default units (Piece, Box, Kilogram, Meter)
- ✅ Chart of accounts template

You should see:
```
Initial data seeded successfully!
Login credentials:
Email: admin@erp.com
Password: Admin@123456
```

### Step 7: Start Development Server

For development purposes, use CodeIgniter's built-in server:

```bash
php spark serve
```

The application will be available at: **http://localhost:8080**

For production, configure your web server (see below).

### Step 8: Login

1. Open your browser and navigate to: http://localhost:8080
2. You'll be redirected to the login page
3. Use the default credentials:
   - **Email**: admin@erp.com
   - **Password**: Admin@123456

4. **IMPORTANT**: Change the default password immediately after first login!

## Production Deployment

### Apache Configuration

1. Create a virtual host configuration file:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/erp-multicompany/public
    
    <Directory /path/to/erp-multicompany/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/erp_error.log
    CustomLog ${APACHE_LOG_DIR}/erp_access.log combined
</VirtualHost>
```

2. Enable required modules:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx Configuration

Create a server block configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/erp-multicompany/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### Production Environment Settings

Update your `.env` file for production:

```env
CI_ENVIRONMENT = production

# Force HTTPS
app.forceGlobalSecureRequests = true

# Disable debug toolbar
CI_DEBUG = false

# Enable CSRF protection
security.csrfProtection = 'session'

# Database with proper credentials
database.default.password = strong_password_here
```

### Security Checklist for Production

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Set strong application key
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Disable directory listing
- [ ] Configure firewall rules
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Remove or restrict access to development tools

### File Permissions

Set proper permissions for production:

```bash
# Set ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data /path/to/erp-multicompany

# Set directory permissions
find /path/to/erp-multicompany -type d -exec chmod 755 {} \;

# Set file permissions
find /path/to/erp-multicompany -type f -exec chmod 644 {} \;

# Writable directories need special permissions
chmod -R 775 /path/to/erp-multicompany/writable
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed

**Error**: `Unable to connect to the database.`

**Solution**:
- Verify database credentials in `.env`
- Check if MySQL service is running: `sudo systemctl status mysql`
- Test connection: `mysql -u username -p -h localhost database_name`

#### 2. 404 Error on Routes

**Error**: All pages except homepage show 404

**Solution**:
- Ensure mod_rewrite is enabled (Apache)
- Check `.htaccess` file exists in `public/` directory
- Verify `AllowOverride All` in Apache configuration

#### 3. Permission Denied Errors

**Error**: `Permission denied` when accessing certain features

**Solution**:
```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

#### 4. Composer Install Fails

**Error**: Authentication errors or timeout

**Solution**:
```bash
# Clear composer cache
composer clear-cache

# Retry installation
composer install --no-interaction
```

#### 5. Migration Errors

**Error**: Migration fails with foreign key constraint

**Solution**:
```bash
# Rollback all migrations
php spark migrate:rollback

# Re-run migrations
php spark migrate
```

## Updating the Application

To update to the latest version:

```bash
# Backup database first!
mysqldump -u root -p erp_multicompany > backup_$(date +%Y%m%d).sql

# Pull latest changes
git pull origin main

# Update dependencies
composer update

# Run any new migrations
php spark migrate

# Clear cache
php spark cache:clear
```

## Getting Help

If you encounter issues not covered here:

1. Check the [README.md](README.md) for general documentation
2. Review CodeIgniter 4 documentation: https://codeigniter.com/user_guide/
3. Create an issue on GitHub: https://github.com/jakarias1907/erp-multicompany/issues

## Next Steps

After successful installation:

1. Change the default admin password
2. Create additional users and assign roles
3. Set up your company information
4. Configure system settings
5. Start using the ERP modules!

---

**Congratulations! Your ERP Multi-Company system is now installed and ready to use!** 🎉
