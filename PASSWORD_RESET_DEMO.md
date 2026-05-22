# Password Reset Demo Guide

## 1) Add the columns
Run:
```sql
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `reset_token_hash` VARCHAR(64) DEFAULT NULL AFTER `role`,
    ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token_hash`;
```

## 2) Forgot Password flow
- Open `http://localhost/library-app/public/forgot-password`
- Enter an email address
- If the user exists, a simulated reset link is appended to `mail_dump.txt` in the project root

## 3) Reset Password flow
- Open the link from `mail_dump.txt`
- Enter a new password and confirm it
- Submit the form to `update-password`

## 4) What happens
- The reset token is hashed with SHA-256
- The token is stored in `users.reset_token_hash`
- The token expires in 30 minutes
- After a successful reset, the token is cleared so it cannot be reused

## 5) Files involved
- `app/Controllers/AuthController.php`
- `app/Models/User.php`
- `app/Views/auth/forgot.php`
- `app/Views/auth/reset_view.php`
- `public/index.php`
- `db/migrations/2026_05_22_add_password_reset_columns.sql`

