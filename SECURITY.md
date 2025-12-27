# Security Documentation - ERP Multi-Company System

This document outlines the security features, best practices, and guidelines for the ERP Multi-Company system.

## 🔒 Security Features Overview

The ERP system implements multiple layers of security to protect against common web vulnerabilities and ensure data integrity.

## Authentication Security

### Password Security
- ✅ **Bcrypt Hashing**: All passwords are hashed using PHP's `password_hash()` with bcrypt algorithm
- ✅ **Minimum Password Length**: 6 characters (configurable)
- ✅ **Force Password Change**: Administrators can force users to change password on first login
- ✅ **Password History**: Prevents reuse of recent passwords (planned feature)

### Account Protection
- ✅ **Account Lockout**: Automatic lockout after 5 failed login attempts
- ✅ **Lockout Duration**: 15 minutes (configurable)
- ✅ **Account Status**: Three states - active, inactive, locked
- ✅ **Session Timeout**: Automatic logout after period of inactivity

### Two-Factor Authentication (2FA)
- ✅ **Database Support**: User table includes 2FA fields
- ⏳ **Implementation**: Google Authenticator/Email OTP (planned)

## Authorization & Access Control

### Role-Based Access Control (RBAC)
- ✅ **Granular Permissions**: Module-level and action-level permissions
- ✅ **Permission Matrix**: Create, Read, Update, Delete, Approve, Print, Export
- ✅ **System Roles**: Super Admin, Company Admin, Manager, Staff
- ✅ **Custom Roles**: Ability to create custom roles per company

### Multi-Company Data Isolation
- ✅ **Company-Based Segmentation**: Each company's data is isolated via `company_id`
- ✅ **Automatic Filtering**: All queries automatically filter by current company
- ✅ **Cross-Company Prevention**: Users cannot access data from other companies
- ✅ **Super Admin Exception**: Super Admins can access all companies

## Protection Mechanisms

### CSRF Protection
**Status**: ✅ Enabled

CodeIgniter 4's built-in CSRF protection is active:
- CSRF tokens generated for all forms
- Token validation on POST requests
- Automatic token regeneration
- Session-based token storage

### XSS Prevention
**Status**: ✅ Implemented

Multiple layers of XSS protection:
- **Output Escaping**: All user input displayed using `esc()` function
- **Input Validation**: Server-side validation for all inputs
- **Content Security Policy**: Headers configured (planned)

### SQL Injection Prevention
**Status**: ✅ Implemented

- **Query Builder**: All database queries use CodeIgniter's Query Builder
- **Prepared Statements**: Automatic parameter binding
- **No Raw Queries**: Direct SQL execution prohibited in application code

### Rate Limiting
**Status**: ✅ Implemented

Login rate limiting prevents brute force attacks:
- Maximum 5 attempts per username/email
- 15-minute lockout window
- IP address logging
- Attempt tracking in database

### Secure Headers
**Status**: ✅ Configured

Security headers implemented:
```php
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000 (production only)
```

## Audit & Logging

### Activity Logging
**Status**: ✅ Implemented

All critical actions are logged:
- User actions (Create, Update, Delete)
- Module accessed
- IP address
- User agent
- Timestamp
- Company context

### Login History
**Status**: ✅ Implemented

Complete login tracking:
- Successful and failed attempts
- IP addresses
- Timestamps
- Username/email used

### Database Audit Trail
**Status**: ✅ Implemented

All transactional tables include:
- `created_by` - User who created the record
- `updated_by` - User who last updated
- `deleted_by` - User who soft-deleted (if applicable)
- `created_at`, `updated_at`, `deleted_at` - Timestamps

## Data Security

### Soft Delete
**Status**: ✅ Implemented

Important data is never permanently deleted:
- Records marked with `deleted_at` timestamp
- Data remains in database for audit purposes
- Can be restored if needed
- Hard delete requires special permissions

### Data Encryption
**Status**: ⏳ Planned

For sensitive data:
- Credit card numbers
- Bank account details
- Social security numbers
- Other PII (Personally Identifiable Information)

### Database Backups
**Status**: ⏳ Recommended

Implement regular backups:
```bash
# Daily backup script
mysqldump -u root -p erp_multicompany > backup_$(date +%Y%m%d).sql
```

## File Upload Security

### Validation
**Status**: ✅ Implemented (Structure)

File upload controls:
- Whitelist of allowed extensions
- File type verification (MIME type check)
- Size limitation
- Filename sanitization
- Secure storage location (outside web root)

Allowed extensions:
- Images: jpg, jpeg, png, gif
- Documents: pdf, doc, docx, xls, xlsx
- Archives: zip (with restrictions)

## Session Security

### Session Management
**Status**: ✅ Implemented

- Secure session handling
- Session regeneration on login
- HTTPOnly cookie flag
- Secure flag (HTTPS only)
- Session timeout configuration

Configuration in `.env`:
```env
session.cookieSecure = true  # HTTPS only
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'
```

## Input Validation

### Server-Side Validation
**Status**: ✅ Implemented

All user inputs are validated:
- Required field validation
- Data type validation
- Length constraints
- Format validation (email, phone, etc.)
- Custom business rule validation

### Client-Side Validation
**Status**: ⏳ Planned

Additional UX improvement:
- HTML5 validation
- JavaScript validation
- Real-time feedback
- **Note**: Never rely solely on client-side validation

## API Security (Planned)

### JWT Authentication
**Status**: ⏳ Planned

For API endpoints:
- JSON Web Token (JWT) authentication
- Token expiration
- Refresh tokens
- API rate limiting

## Security Best Practices

### For Developers

1. **Never Trust User Input**
   - Always validate and sanitize
   - Use parameterized queries
   - Escape output

2. **Follow Principle of Least Privilege**
   - Users get minimum required permissions
   - Database users have limited privileges
   - File permissions set to minimum necessary

3. **Keep Dependencies Updated**
   ```bash
   composer update
   ```

4. **Use Environment Variables**
   - Never commit `.env` to version control
   - Keep secrets in environment variables
   - Different configs for dev/staging/production

5. **Enable Error Logging**
   - Log errors to files, not to users
   - Review logs regularly
   - Set up error monitoring

### For Administrators

1. **Change Default Credentials**
   - Change admin password immediately
   - Use strong, unique passwords
   - Enable 2FA when available

2. **Keep Software Updated**
   - Apply security patches promptly
   - Update PHP, MySQL, web server
   - Monitor security advisories

3. **Regular Backups**
   - Daily database backups
   - Weekly full system backups
   - Test backup restoration

4. **Monitor System Activity**
   - Review audit logs regularly
   - Watch for suspicious activity
   - Set up alerts for critical events

5. **Use HTTPS**
   - Obtain SSL certificate
   - Force HTTPS for all connections
   - Set HSTS header

## Security Checklist

### Pre-Production

- [ ] Changed default admin password
- [ ] Updated all database credentials
- [ ] Configured strong session secret
- [ ] Enabled CSRF protection
- [ ] Set up HTTPS/SSL certificate
- [ ] Configured secure headers
- [ ] Set proper file permissions (644/755)
- [ ] Disabled directory listing
- [ ] Removed .env from version control
- [ ] Set CI_ENVIRONMENT to 'production'
- [ ] Disabled debug mode
- [ ] Configured error logging
- [ ] Set up automated backups
- [ ] Configured firewall rules
- [ ] Implemented rate limiting
- [ ] Tested authentication flow
- [ ] Tested permission system
- [ ] Performed security audit

### Post-Production

- [ ] Monitor error logs daily
- [ ] Review audit logs weekly
- [ ] Update dependencies monthly
- [ ] Perform security audits quarterly
- [ ] Test backup restoration quarterly
- [ ] Review user permissions monthly
- [ ] Update SSL certificates before expiry

## Vulnerability Reporting

If you discover a security vulnerability, please email:
**security@yourdomain.com**

Please include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

**Do not** publicly disclose vulnerabilities until they have been addressed.

## Security Updates

Check for security updates regularly:
- Watch the GitHub repository
- Subscribe to security advisories
- Follow CodeIgniter security announcements

## Compliance

This system implements security controls to help meet:
- OWASP Top 10 protection
- PCI DSS requirements (for payment data)
- GDPR compliance (for EU data)
- SOC 2 Type II (for enterprise clients)

**Note**: Full compliance requires additional operational and organizational controls beyond the application.

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CodeIgniter 4 Security](https://codeigniter.com/user_guide/concepts/security.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

---

**Remember**: Security is an ongoing process, not a one-time implementation. Stay vigilant and keep the system updated!
