# ERP Multi-Company - Developer Quickstart Guide

## 🎯 Quick Start - Using Implemented Modules

### Installation & Setup

```bash
# 1. Install dependencies
composer install

# 2. Configure database
cp env .env
# Edit .env and set database credentials

# 3. Run migrations
php spark migrate

# 4. Run seeder (creates initial admin user)
php spark db:seed InitialDataSeeder

# 5. Create upload directories
mkdir -p public/uploads/{companies,users,products}
chmod -R 755 public/uploads

# 6. Start development server
php spark serve
```

### Default Login
- URL: `http://localhost:8080`
- Username: `admin` (or check seeder)
- Password: `admin123` (or check seeder)

---

## 📋 What's Already Working

### ✅ Master Data Management
Access from sidebar menu:

1. **Companies** (`/master/company`)
   - Create, edit, delete companies
   - Upload company logos
   - Toggle active/inactive status

2. **Users** (`/master/user`)
   - Create users with photos
   - Reset passwords
   - Toggle active/inactive status
   - Password strength indicator

3. **Roles & Permissions** (`/master/role`)
   - Create roles
   - Assign permissions (17 modules × 7 actions)
   - Clone existing roles
   - System roles protected

4. **Customers** (`/master/customer`)
   - Retail/Wholesale types
   - Credit limit tracking
   - Payment terms
   - View statements

5. **Suppliers** (`/master/supplier`)
   - Bank account info
   - Payment terms
   - View statements

6. **Products** (`/master/product`)
   - Product categories
   - SKU management
   - Price & cost tracking
   - Stock alert levels
   - Product images

### ✅ Finance Module
7. **Invoices** (`/finance/invoice`)
   - Create invoices with multiple line items
   - Auto-generated invoice numbers
   - Tax and discount calculation
   - PDF generation
   - Payment tracking
   - Status workflow

---

## 🛠️ Common Development Tasks

### Adding a New Module (Copy Pattern)

```bash
# 1. Copy existing controller
cp app/Controllers/Master/CustomerController.php app/Controllers/[Module]/[Entity]Controller.php

# 2. Search & replace throughout file
CustomerController → [Entity]Controller
customers → [entities]
customer → [entity]
Customer → [Entity]

# 3. Copy views
cp -r app/Views/master/customer app/Views/[module]/[entity]

# 4. Update routes in app/Config/Routes.php
$routes->get('[entity]', '[Module]\[Entity]Controller::index');
# ... add other routes

# 5. Test
# - Open in browser
# - Test CRUD operations
# - Verify DataTable works
# - Check permissions
```

### Creating a Model

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class EntityModel extends Model
{
    protected $table = 'entities';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['company_id', 'name', 'description', ...];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[3]'
    ];
    
    protected $beforeInsert = ['setCompanyId'];
    protected $beforeUpdate = ['setCompanyId'];
    
    protected function setCompanyId(array $data) {
        if (!isset($data['data']['company_id'])) {
            $data['data']['company_id'] = getCurrentCompanyId();
        }
        return $data;
    }
}
```

### Adding Custom Methods to Controllers

```php
// Example: Export to Excel
public function export()
{
    if (!hasPermission('[entities]', 'export')) {
        return redirect()->to('/dashboard')->with('error', 'Access denied');
    }
    
    $companyId = getCurrentCompanyId();
    $data = $this->[entity]Model->where('company_id', $companyId)->findAll();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Name');
    // ... more headers
    
    // Data
    $row = 2;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['id']);
        $sheet->setCellValue('B' . $row, $item['name']);
        $row++;
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $fileName = '[entity]_' . date('YmdHis') . '.xlsx';
    $writer->save($fileName);
    
    return $this->response->download($fileName, null)->setFileName($fileName);
}
```

### Generating PDF

```php
public function print($id)
{
    $entity = $this->[entity]Model->find($id);
    
    $dompdf = new \Dompdf\Dompdf();
    $html = view('[module]/[entity]/pdf_template', ['entity' => $entity]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    return $dompdf->stream('[entity]_' . $id . '.pdf');
}
```

---

## 🧪 Testing

### Manual Testing Checklist
```
Module: [Module Name]

CRUD Operations:
[ ] Create - Form displays, saves data, validates input
[ ] Read - List displays, DataTable works, search works
[ ] Update - Edit form loads, updates data
[ ] Delete - Confirmation shows, deletes (soft delete)

Security:
[ ] Permission check works (test with role without permission)
[ ] Company isolation works (can't see other company data)
[ ] CSRF protection active
[ ] XSS protection works (try <script>alert('xss')</script>)
[ ] SQL injection protected (try ' OR '1'='1)

Features:
[ ] File upload works (if applicable)
[ ] Foreign key relationships work
[ ] Status changes work (if applicable)
[ ] Custom features work

Performance:
[ ] DataTable pagination works with 100+ records
[ ] Search is fast
[ ] No N+1 query problems
```

### Debug Mode
Enable in `.env`:
```
CI_ENVIRONMENT = development
```

View errors at: `app/Config/Boot/development.php`

---

## 🎨 UI Customization

### Adding Menu Items
Edit `app/Views/layouts/sidebar.php`:

```php
<li class="nav-item">
    <a href="<?= base_url('[module]/[entity]') ?>" class="nav-link">
        <i class="far fa-circle nav-icon"></i>
        <p>[Entity Name]</p>
    </a>
</li>
```

### Changing Theme Colors
Edit `public/assets/css/custom.css` (create if not exists):

```css
.btn-primary {
    background-color: #your-color !important;
}
```

### Custom JavaScript
Add to view:
```php
<?= $this->section('scripts') ?>
<script>
// Your custom JS
</script>
<?= $this->endSection() ?>
```

---

## 📊 Database Access

### Using Query Builder
```php
$db = \Config\Database::connect();

// Select
$results = $db->table('table_name')
    ->where('company_id', getCurrentCompanyId())
    ->get()
    ->getResultArray();

// Insert
$db->table('table_name')->insert([
    'field' => 'value'
]);

// Update
$db->table('table_name')
    ->where('id', $id)
    ->update(['field' => 'new_value']);

// Delete
$db->table('table_name')->where('id', $id)->delete();
```

### Transactions
```php
$db->transStart();
// Multiple operations
$db->table('table1')->insert($data1);
$db->table('table2')->insert($data2);
$db->transComplete();

if ($db->transStatus() === false) {
    // Transaction failed
}
```

---

## 🔍 Debugging Tips

### View Database Queries
```php
$db = \Config\Database::connect();
echo $db->getLastQuery();
```

### Log Custom Messages
```php
log_message('error', 'Custom error message');
log_message('debug', 'Debug information');
// View in writable/logs/log-YYYY-MM-DD.php
```

### Dump and Die
```php
dd($variable); // Dumps variable and stops execution
d($variable);  // Dumps variable and continues
```

---

## 🚀 Deployment Checklist

```
[ ] Set CI_ENVIRONMENT = production in .env
[ ] Set secure app.baseURL in .env
[ ] Set strong encryption.key in .env
[ ] Disable debug toolbar (set to false in Config/Filters.php)
[ ] Set file upload limits
[ ] Configure email settings for notifications
[ ] Set up database backups
[ ] Configure proper file permissions (755 for directories, 644 for files)
[ ] Test all modules in production environment
[ ] Set up SSL certificate
[ ] Configure security headers
[ ] Set up monitoring/logging
```

---

## 📚 Key Files Reference

### Configuration
- `app/Config/Routes.php` - All routes
- `app/Config/Database.php` - DB config
- `app/Config/Email.php` - Email settings
- `.env` - Environment variables

### Core
- `app/Controllers/BaseController.php` - Base controller
- `app/Helpers/auth_helper.php` - Auth functions
- `app/Filters/AuthFilter.php` - Authentication filter

### Views
- `app/Views/layouts/main.php` - Main layout
- `app/Views/layouts/sidebar.php` - Sidebar menu
- `app/Views/layouts/navbar.php` - Top navbar

### Assets
- `public/assets/` - CSS, JS, images
- `public/uploads/` - User uploads

---

## 💡 Helpful Commands

```bash
# Create migration
php spark make:migration [name]

# Run migrations
php spark migrate

# Rollback migration
php spark migrate:rollback

# Create seeder
php spark make:seeder [name]

# Run seeder
php spark db:seed [name]

# Create model
php spark make:model [name]

# Create controller
php spark make:controller [name]

# Clear cache
php spark cache:clear

# List routes
php spark routes
```

---

## 🔗 Documentation Links

- **Full Implementation Guide**: `DETAILED_IMPLEMENTATION_GUIDE.md`
- **Implementation Status**: `README_IMPLEMENTATION.md`
- **Original Spec**: `COMPLETION_GUIDE.md`
- **CodeIgniter 4 Docs**: https://codeigniter.com/user_guide/
- **AdminLTE 3 Docs**: https://adminlte.io/docs/3.0/

---

## 🆘 Common Issues & Solutions

### "Class not found"
```bash
composer dump-autoload
```

### "Database connection failed"
Check `.env` database settings

### "CSRF verification failed"
Clear browser cookies and refresh

### "Permission denied" on uploads
```bash
chmod -R 755 public/uploads
```

### DataTable not loading
Check browser console for JavaScript errors

### Session issues
```bash
# Clear sessions
rm -rf writable/session/*
```

---

## ✅ Next Steps

1. **Test existing modules** - Verify all CRUD operations work
2. **Read DETAILED_IMPLEMENTATION_GUIDE.md** - Understand patterns
3. **Implement next priority module** - Start with Warehouse or Journal Entry
4. **Follow the pattern** - Copy existing code and modify
5. **Test as you go** - Don't implement everything then test
6. **Commit frequently** - After each working module

**Estimated time to complete**: 30-40 hours following the guide.

Good luck! The foundation is solid and the patterns are clear. 🚀
