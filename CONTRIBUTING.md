# Contributing to ERP Multi-Company System

Thank you for considering contributing to the ERP Multi-Company System! This document provides guidelines and instructions for contributing.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)

## Code of Conduct

By participating in this project, you agree to maintain a respectful and collaborative environment for everyone.

## Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/your-username/erp-multicompany.git
   cd erp-multicompany
   ```
3. **Add upstream remote**:
   ```bash
   git remote add upstream https://github.com/jakarias1907/erp-multicompany.git
   ```
4. **Keep your fork synced**:
   ```bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   ```

## Development Setup

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Git

### Installation

1. **Install dependencies**:
   ```bash
   composer install
   ```

2. **Set up environment**:
   ```bash
   cp env .env
   # Edit .env with your settings
   ```

3. **Create and migrate database**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE erp_multicompany"
   php spark migrate
   php spark db:seed InitialDataSeeder
   ```

4. **Start development server**:
   ```bash
   php spark serve
   ```

## How to Contribute

### Reporting Bugs

Before creating a bug report:
- Check existing issues to avoid duplicates
- Collect relevant information (PHP version, error messages, steps to reproduce)

Create a detailed bug report including:
- **Summary**: Brief description
- **Environment**: PHP version, database version, OS
- **Steps to Reproduce**: Numbered steps
- **Expected Behavior**: What should happen
- **Actual Behavior**: What actually happens
- **Screenshots**: If applicable

### Suggesting Features

Feature suggestions are welcome! Please:
- Search existing issues first
- Describe the problem you're trying to solve
- Explain your proposed solution
- Consider implementation complexity
- Think about backward compatibility

### Contributing Code

1. **Create a branch**:
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b fix/bug-description
   ```

2. **Make your changes**:
   - Follow coding standards
   - Write tests if applicable
   - Update documentation

3. **Test your changes**:
   ```bash
   php spark test
   ```

4. **Commit your changes**:
   ```bash
   git add .
   git commit -m "feat: add user management module"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request** on GitHub

## Coding Standards

### PHP Standards

We follow **PSR-12** coding standard:

```php
<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index(): string
    {
        $data = [
            'users' => $this->userModel->findAll(),
        ];
        
        return view('users/index', $data);
    }
}
```

**Key Points**:
- 4 spaces for indentation (no tabs)
- Opening braces on same line for methods
- One blank line between methods
- Type hints for parameters and return values
- DocBlocks for classes and methods

### CodeIgniter 4 Best Practices

1. **Use Models for Database Operations**:
   ```php
   // Good
   $userModel = new UserModel();
   $user = $userModel->find($id);
   
   // Avoid
   $db = \Config\Database::connect();
   $user = $db->query("SELECT * FROM users WHERE id = ?", [$id]);
   ```

2. **Use View Data Arrays**:
   ```php
   return view('page', [
       'title' => 'Page Title',
       'data' => $data,
   ]);
   ```

3. **Validate Input**:
   ```php
   $validation = \Config\Services::validation();
   $validation->setRules([
       'email' => 'required|valid_email',
       'password' => 'required|min_length[6]',
   ]);
   ```

4. **Use Filters for Authentication**:
   ```php
   $routes->group('admin', ['filter' => 'auth'], function($routes) {
       $routes->get('dashboard', 'Admin\DashboardController::index');
   });
   ```

### Database Standards

1. **Use Migrations**:
   ```bash
   php spark make:migration CreateProductsTable
   ```

2. **Follow Naming Conventions**:
   - Tables: plural, snake_case (e.g., `product_categories`)
   - Columns: snake_case (e.g., `first_name`)
   - Primary keys: `id`
   - Foreign keys: `table_id` (e.g., `company_id`)

3. **Include Audit Fields**:
   ```php
   'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
   'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
   'created_at' => ['type' => 'DATETIME', 'null' => true],
   'updated_at' => ['type' => 'DATETIME', 'null' => true],
   'deleted_at' => ['type' => 'DATETIME', 'null' => true],
   ```

### Frontend Standards

1. **Use AdminLTE Components**:
   - Follow AdminLTE 3 structure
   - Use existing card, box, and widget components
   - Maintain consistent styling

2. **JavaScript**:
   - Use SweetAlert2 for alerts
   - DataTables for data tables
   - Chart.js for charts

3. **Accessibility**:
   - Use semantic HTML
   - Include ARIA labels
   - Ensure keyboard navigation

## Testing Guidelines

### Writing Tests

```php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;

class UserModelTest extends CIUnitTestCase
{
    public function testUserCreation()
    {
        $userModel = new UserModel();
        
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ];
        
        $result = $userModel->insert($data);
        $this->assertIsNumeric($result);
    }
}
```

### Running Tests

```bash
# Run all tests
php spark test

# Run specific test
php spark test Tests\Unit\UserModelTest
```

## Commit Message Guidelines

We follow **Conventional Commits** specification:

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```bash
feat(auth): add two-factor authentication

Implement 2FA using Google Authenticator.
Users can now enable 2FA in their profile settings.

Closes #123
```

```bash
fix(invoice): correct tax calculation

Fixed decimal precision issue in tax calculation
that caused rounding errors.

Fixes #456
```

```bash
docs(readme): update installation instructions

Added troubleshooting section and improved
clarity of database setup steps.
```

## Pull Request Process

### Before Submitting

- [ ] Code follows project standards
- [ ] Tests pass locally
- [ ] Documentation updated
- [ ] Commits follow commit message guidelines
- [ ] No merge conflicts
- [ ] Self-review completed

### PR Title and Description

**Title**: Follow commit message format
```
feat(module): add feature description
```

**Description Template**:
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How has this been tested?

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] No new warnings
```

### Review Process

1. **Automated Checks**: CI will run tests and linting
2. **Code Review**: Maintainers will review your code
3. **Feedback**: Address any requested changes
4. **Approval**: Once approved, your PR will be merged

### After Merge

- Your contribution will be included in the next release
- You'll be added to the contributors list
- Thank you for contributing! 🎉

## Questions?

- Create an issue for general questions
- Email: support@example.com for private inquiries

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to the ERP Multi-Company System!**
