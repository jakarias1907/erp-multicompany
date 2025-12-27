# Quick Reference Guide - ERP Multi-Company System

Quick reference for common tasks and important information.

## 🚀 Quick Start

```bash
# Clone and setup
git clone https://github.com/jakarias1907/erp-multicompany.git
cd erp-multicompany
composer install
cp env .env

# Edit .env with your database credentials

# Setup database
mysql -u root -p -e "CREATE DATABASE erp_multicompany"
php spark migrate
php spark db:seed InitialDataSeeder

# Start server
php spark serve
```

**Default Login**: admin@erp.com / Admin@123456

## 📂 Project Structure

```
erp-multicompany/
├── app/
│   ├── Config/          # App configuration
│   ├── Controllers/     # HTTP controllers
│   │   └── Auth/       # Authentication
│   ├── Database/
│   │   ├── Migrations/ # 36 migration files
│   │   └── Seeds/      # Database seeders
│   ├── Filters/        # AuthFilter
│   ├── Libraries/      # AuthLibrary
│   ├── Models/         # 5 core models
│   └── Views/          # Blade templates
│       ├── auth/       # Login page
│       └── dashboard/  # Main dashboard
├── public/             # Web root
├── writable/           # Logs, cache, uploads
├── CONTRIBUTING.md     # How to contribute
├── INSTALLATION.md     # Setup guide
├── README.md          # Project overview
└── SECURITY.md        # Security docs
```

## 🗄️ Database Tables (36 Total)

### Core (10 tables)
- companies
- users
- roles
- permissions
- role_permissions
- company_users
- branches
- departments
- user_activity_logs
- login_attempts

### Master Data (6 tables)
- product_categories
- units
- products
- customers
- suppliers
- warehouses

### Finance (6 tables)
- chart_of_accounts
- journal_entries
- journal_entry_lines
- invoices
- invoice_items
- bills

### Inventory (4 tables)
- stock_cards
- stock_movements
- stock_transfers
- stock_transfer_items

### Sales (3 tables)
- quotations
- sales_orders
- delivery_orders

### Purchase (3 tables)
- purchase_requests
- purchase_orders
- goods_receipts

### HR (4 tables)
- employees
- attendance
- leaves
- payrolls

## 🔐 Default Credentials

**Super Admin**:
- Email: admin@erp.com
- Password: Admin@123456
- Company: Demo Company

**⚠️ Change password after first login!**

## 🛠️ Common Commands

### Database

```bash
# Run migrations
php spark migrate

# Check migration status
php spark migrate:status

# Rollback last migration
php spark migrate:rollback

# Refresh database (rollback all + migrate)
php spark migrate:refresh

# Seed database
php spark db:seed InitialDataSeeder
```

### Development

```bash
# Start dev server
php spark serve

# Start on specific port
php spark serve --port=8000

# Clear cache
php spark cache:clear

# View routes
php spark routes
```

### Code Generation

```bash
# Create controller
php spark make:controller ControllerName

# Create model
php spark make:model ModelName

# Create migration
php spark make:migration CreateTableName

# Create filter
php spark make:filter FilterName

# Create seeder
php spark make:seeder SeederName
```

## 🔒 Security Features

### Implemented
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention
- ✅ Rate limiting (5 attempts, 15 min lockout)
- ✅ Session security
- ✅ Audit logging
- ✅ Soft delete
- ✅ Multi-company data isolation

### Account Lockout
- Failed attempts: 5
- Lockout duration: 15 minutes
- Tracked by: username/email + IP

## 📊 Default Seeded Data

**Company**:
- 1 demo company

**Users**:
- 1 super admin

**Roles**:
- Super Admin (system role)
- Company Admin
- Manager
- Staff

**Permissions**:
- 105 total (15 modules × 7 actions)
- Modules: dashboard, companies, users, roles, products, customers, suppliers, invoices, bills, warehouses, inventory, sales, purchase, reports, settings
- Actions: create, read, update, delete, approve, print, export

**Chart of Accounts**:
- 13 default accounts (Assets, Liabilities, Equity, Revenue, Expenses)

**Other**:
- 1 warehouse (Main Warehouse)
- 4 units (Piece, Box, Kilogram, Meter)

## 🌐 Routes

### Public Routes
```
GET  /              → Login page
GET  /login         → Login page
POST /login/authenticate → Login action
GET  /logout        → Logout action
```

### Protected Routes (requires auth)
```
GET /dashboard → Main dashboard
```

## 📝 Key Files

### Configuration
- `.env` - Environment settings
- `app/Config/Database.php` - Database config
- `app/Config/Routes.php` - Route definitions
- `app/Config/Filters.php` - Filter config

### Authentication
- `app/Libraries/AuthLibrary.php` - Auth logic
- `app/Controllers/Auth/LoginController.php` - Login controller
- `app/Filters/AuthFilter.php` - Auth middleware
- `app/Views/auth/login.php` - Login view

### Models
- `app/Models/UserModel.php`
- `app/Models/RoleModel.php`
- `app/Models/PermissionModel.php`
- `app/Models/CompanyUserModel.php`
- `app/Models/LoginAttemptModel.php`

## 🎨 Frontend

### Libraries (CDN)
- AdminLTE 3.2
- Bootstrap 4.6.2
- jQuery 3.6.0
- Font Awesome 6.4.0
- SweetAlert2 11

### Ready for Integration
- DataTables (for data grids)
- Select2 (for dropdowns)
- Chart.js (for charts)

## 🐛 Troubleshooting

### Database Connection Failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u root -p -h localhost

# Verify .env credentials
cat .env | grep database
```

### Permission Denied
```bash
# Fix writable permissions
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

### 404 on Routes
```bash
# Enable mod_rewrite (Apache)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check .htaccess in public/
ls -la public/.htaccess
```

### Composer Install Fails
```bash
# Clear cache
composer clear-cache

# Try again
composer install --no-interaction
```

## 📚 Documentation

- **README.md** - Overview and features
- **INSTALLATION.md** - Step-by-step setup
- **SECURITY.md** - Security features and guidelines
- **CONTRIBUTING.md** - How to contribute

## 🔗 Useful Links

- [CodeIgniter 4 Docs](https://codeigniter.com/user_guide/)
- [AdminLTE 3 Docs](https://adminlte.io/docs/3.2/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## 💡 Tips

1. **Always backup before updates**:
   ```bash
   mysqldump -u root -p erp_multicompany > backup.sql
   ```

2. **Keep dependencies updated**:
   ```bash
   composer update
   ```

3. **Review logs regularly**:
   ```bash
   tail -f writable/logs/log-*.php
   ```

4. **Use environment variables** for sensitive data

5. **Test in development** before deploying to production

## 🚀 Next Steps

1. Change default admin password
2. Configure company settings
3. Create additional users and roles
4. Start implementing master data modules
5. Build operational workflows

---

For detailed information, see the full documentation files.
