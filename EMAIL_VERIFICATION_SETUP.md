
# Email Verification Setup 

## Overview
This guide explains how to set up and use the email verification feature for the Mentora application.

## Features
- ✅ Automatic email verification after registration
- ✅ 24-hour token expiration
- ✅ Secure token generation (32 bytes random)
- ✅ Database tracking of verification status
- ✅ User-friendly Bengali interface
- ✅ Email templates with HTML formatting

## Setup Steps

### 1. Database Configuration
The email verification feature requires three database tables:

**Run the SQL schema file:**
```bash
# Import the SQL file to your database
mysql -u root -p mentora_db < config/email-verification-schema.sql
```

Or manually run the queries in `config/email-verification-schema.sql` using phpMyAdmin.

### Tables Created/Modified:
1. **users** - Added columns:
   - `is_verified` (TINYINT) - 0 or 1
   - `is_active` (TINYINT) - 0 or 1
   - `login_attempts` (INT)
   - `last_login_at` (DATETIME)
   - `last_login_ip` (VARCHAR)

2. **email_verifications** - New table:
   - `id` - Primary key
   - `user_id` - Foreign key to users
   - `token` - Unique verification token
   - `expires_at` - Token expiration time
   - `verified_at` - Verification completion time
   - `created_at` - Creation timestamp

3. **user_logs** - New table for activity logging

### 2. Email Configuration
The application uses PHP's built-in `mail()` function. To enable email sending:

**On Linux/Mac:**
```bash
# Install postfix or sendmail
sudo apt-get install postfix
# or
sudo apt-get install sendmail
```

**On Windows (XAMPP):**
1. Edit `php.ini`:
   ```
   [mail function]
   SMTP = smtp.gmail.com
   smtp_port = 587
   sendmail_from = your-email@gmail.com
   ```

2. Or use a library like PHPMailer (optional enhancement)

### 3. Application Files

**Key files involved:**
- `/auth/register.php` - Registration form
- `/auth/register-process.php` - Handles registration and email sending
- `/auth/verify-email.php` - Email verification page
- `/auth/verify-email-sent.php` - Confirmation page after registration
- `/auth/login-process.php` - Checks if email is verified before login
- `/includes/functions.php` - Helper functions for email verification
- `/config/email-verification-schema.sql` - Database schema

## How It Works

### Registration Flow:
1. User fills registration form and submits
2. Form validation (client-side + server-side)
3. User data saved to database with `is_verified = 0`
4. Verification token generated and saved
5. Email sent with verification link
6. User redirected to `verify-email-sent.php`
7. User checks email and clicks verification link
8. `verify-email.php` processes the token
9. User account marked as verified (`is_verified = 1`)
10. User can now log in

### Login Flow:
1. User enters email/phone and password
2. System checks if user exists
3. System checks if `is_verified = 1`
4. If NOT verified: Show error "ইমেইল ভেরিফাই করা হয়নি"
5. If verified: Allow login

## Email Template
The verification email includes:
- Mentora branding
- User's full name
- Verification button
- Direct link (for cases where button doesn't work)
- 24-hour expiration notice
- Support message

**Email styling:**
- Responsive HTML
- Bengali + English text
- Teal and Indigo brand colors

## Functions Available

### In `/includes/functions.php`:

```php
// Generate verification token and save to database
$token = generateEmailToken($user_id);

// Send verification email
$sent = sendVerificationEmail($email, $token, $full_name);

// Verify token and mark user as verified
$result = verifyEmailToken($token);
// Returns: ['success' => bool, 'message' => string, 'user_id' => int]
```

## Testing

### Test Registration:
1. Go to `/auth/register.php`
2. Fill form with test data
3. Submit form
4. You should see "ইমেইল যাচাই পাঠানো হয়েছে" message
5. Go to `/auth/verify-email-sent.php?email=test@example.com`

### Test Email Verification:
1. Check your email inbox (or spam folder)
2. Look for "মেন্টোরা - ইমেইল যাচাই করুন"
3. Click the verification link
4. Should show success message
5. Try to log in - should work

### Test Unverified Login:
1. Manually set a user's `is_verified = 0` in database
2. Try to log in with that user
3. Should show "ইমেইল ভেরিফাই করা হয়নি"

## Security Features

1. **Token Security:**
   - 64-character random tokens (32 bytes)
   - Stored in database, not sent to user
   - One-time use only

2. **Expiration:**
   - Tokens expire after 24 hours
   - Expired tokens cannot be used

3. **Database:**
   - Foreign key constraints
   - Indexed for fast lookups
   - UTF-8 encoding for international characters

4. **Error Handling:**
   - No sensitive info leaked in error messages
   - Activity logging for suspicious behavior

## Troubleshooting

### Email not sending:
1. Check server's `php.ini` mail settings
2. Verify SMTP server configuration
3. Check spam folder
4. Look at server error logs

### Token invalid errors:
1. Check that `email_verifications` table exists
2. Verify database connection
3. Check token hasn't expired (24 hours)

### Login blocked for verified users:
1. Check `is_verified = 1` in users table
2. Verify login-process.php is updated
3. Check MySQL column exists

## Future Enhancements

1. **Resend Email:**
   - Add button to resend verification email
   - Rate limiting to prevent abuse

2. **Email Service Provider:**
   - Integration with SendGrid
   - Integration with Mailgun
   - Better deliverability

3. **SMS Verification (Optional):**
   - Two-factor authentication
   - SMS as backup verification

4. **Email Confirmation:**
   - Read receipts
   - Bounce handling

5. **Admin Dashboard:**
   - View unverified users
   - Manually verify users
   - Resend verification to users

## Support
For issues or questions about email verification, refer to:
- `/config/email-verification-schema.sql` - Database setup
- `/includes/functions.php` - Function documentation
- Email logs in `/auth/register-process.php`
