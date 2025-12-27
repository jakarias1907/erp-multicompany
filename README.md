# ERP Multi-Company System

A comprehensive Enterprise Resource Planning (ERP) system with multi-company and multi-user support built with CodeIgniter 4, AdminLTE 3, MySQL, and modern security features.

## 🎯 Features

### Core Features
- **Multi-Company Management**: Support for multiple companies with complete data isolation
- **Multi-User & Role-Based Access Control (RBAC)**: Granular permissions and role management
- **High-Level Security**: CSRF protection, XSS prevention, SQL injection prevention, rate limiting, 2FA support
- **Audit Logging**: Complete activity tracking and login history
- **Modern UI**: AdminLTE 3 with Bootstrap 4, responsive design

### Modules
- **Master Data**: Companies, Users, Roles, Products, Customers, Suppliers
- **Finance & Accounting**: Chart of Accounts, Journal Entries, Invoices, Bills
- **Inventory Management**: Stock tracking, Warehouses, Stock movements, Transfers
- **Sales & CRM**: Quotations, Sales Orders, Delivery Orders, Invoicing
- **Purchasing**: Purchase Requests, Purchase Orders, Goods Receipt
- **HR & Payroll**: Employee management, Attendance, Leave, Payroll
- **Reports & Analytics**: Comprehensive reporting with export to Excel/PDF

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Apache/Nginx with mod_rewrite enabled

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/jakarias1907/erp-multicompany.git
cd erp-multicompany
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp env .env
```

Edit `.env` file and configure your database:
```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = erp_multicompany
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Create Database
```bash
mysql -u root -p
CREATE DATABASE erp_multicompany;
EXIT;
```

### 5. Run Migrations
```bash
php spark migrate
```

### 6. Seed Initial Data
```bash
php spark db:seed InitialDataSeeder
```

### 7. Start Development Server
```bash
php spark serve
```

Visit http://localhost:8080 in your browser.

## 🔐 Default Login Credentials

```
Email: admin@erp.com
Password: Admin@123456
```

**⚠️ IMPORTANT**: Change the default password immediately after first login!

## 📁 Project Structure

```
erp-multicompany/
├── app/
│   ├── Config/          # Application configuration
│   ├── Controllers/     # Application controllers
│   │   ├── Auth/       # Authentication controllers
│   │   └── Dashboard/  # Dashboard controllers
│   ├── Database/
│   │   ├── Migrations/ # Database migrations
│   │   └── Seeds/      # Database seeders
│   ├── Filters/        # Request filters (Auth, RBAC, etc.)
│   ├── Libraries/      # Custom libraries (Auth, PDF, etc.)
│   ├── Models/         # Database models
│   └── Views/          # View templates
│       ├── auth/       # Authentication views
│       ├── dashboard/  # Dashboard views
│       └── layouts/    # Layout templates
├── public/             # Public assets (CSS, JS, images)
├── writable/           # Writable files (logs, cache, uploads)
└── .env               # Environment configuration
```

## 🔒 Security Features

### Authentication & Authorization
- ✅ Secure password hashing (bcrypt)
- ✅ Session management
- ✅ Rate limiting (5 failed attempts = 15 min lockout)
- ✅ Two-Factor Authentication (2FA) support
- ✅ Force password change on first login
- ✅ Remember me functionality

### Protection Mechanisms
- ✅ CSRF Protection enabled by default
- ✅ XSS Prevention through input validation and output escaping
- ✅ SQL Injection Prevention (Query Builder & prepared statements)
- ✅ Brute Force Protection
- ✅ Secure headers (X-Frame-Options, X-Content-Type-Options, etc.)

### Audit & Logging
- ✅ User activity logging
- ✅ Login history with IP tracking
- ✅ Database change tracking (created_by, updated_by)
- ✅ Failed login attempts tracking

## 📊 Database Schema

All tables include:
- `company_id` for multi-company data isolation
- `created_by`, `updated_by`, `deleted_by` for audit trail
- `created_at`, `updated_at`, `deleted_at` (soft delete)

### Core Tables
- `companies` - Company master data
- `users` - User accounts
- `roles` - User roles
- `permissions` - System permissions
- `role_permissions` - Role-permission mapping
- `company_users` - User-company assignments

### Operational Tables
- Products, Customers, Suppliers
- Chart of Accounts, Journal Entries
- Invoices, Bills
- Warehouses, Stock Cards, Stock Movements
- Sales Orders, Purchase Orders
- Employees, Attendance, Payroll

## 🎨 Technology Stack

- **Backend**: CodeIgniter 4.6.4
- **Frontend**: AdminLTE 3, Bootstrap 4
- **Database**: MySQL 8.0+
- **JavaScript Libraries**: 
  - jQuery
  - SweetAlert2 (alerts & notifications)
  - DataTables (planned)
  - Chart.js (planned)
  - Select2 (planned)

## 📝 Development Roadmap

### Phase 1: Core Foundation ✅
- [x] Project setup with CodeIgniter 4
- [x] Database migrations for all modules
- [x] Authentication system with security features
- [x] Multi-company architecture
- [x] Basic UI with AdminLTE 3

### Phase 2: Master Data (In Progress)
- [ ] Company management CRUD
- [ ] User management CRUD
- [ ] Role & permission management
- [ ] Product management
- [ ] Customer & supplier management

### Phase 3: Operations
- [ ] Sales module
- [ ] Purchase module
- [ ] Inventory management
- [ ] Finance & accounting

### Phase 4: Advanced Features
- [ ] HR & Payroll
- [ ] Reports & analytics
- [ ] Email notifications
- [ ] Export functionality (Excel, PDF)

## 🤝 Contributing

This project is currently in development. Contributions, issues, and feature requests are welcome!

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**Jakarias**
- GitHub: [@jakarias1907](https://github.com/jakarias1907)

## 🙏 Acknowledgments

- CodeIgniter 4 Framework
- AdminLTE 3 Template
- Bootstrap Framework
- Font Awesome Icons

---

**Note**: This is a production-ready ERP system foundation. Implementation is being done in phases. Security measures have been implemented and tested. Always review and test thoroughly before deploying to production.
