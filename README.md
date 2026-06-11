# NDT Manager

NDT Manager is a lightweight PHP web application for managing student events, registrations, and announcements. This README describes how to set up the project locally (using XAMPP), outlines features, explains the main implementation details, and lists developer notes for customizing or extending the system.

---

## Table of contents
- Overview
- Features
- Requirements
- Installation & local setup
- Database schema (summary)
- Configuration
- Authentication and sessions
- Admin capabilities
- Sending email announcements
- File structure
- Styling and assets
- Troubleshooting
- Security notes
- Developer notes & next steps

---

## Overview
NDT Manager provides event listing and registration for students, an admin dashboard to create/update/delete events, view registrations, and send announcements to registered users. The app blends simple PHP + PDO for data access and a central CSS file for styling.

## Features
- Public home page with a cover banner and upcoming events preview.
- Events listing and registration.
- User registration and login (secure password hashing).
- Admin dashboard for event management and participant overviews.
- Announcement sender (basic mail() implementation included; SMTP recommended for production).
- Session-based admin controls and logout flow.

## Requirements
- XAMPP (Apache + PHP + MySQL/MariaDB). The repository has been tested using XAMPP's bundled PHP.
- PHP 7.4+ (PHP 8.x recommended).
- MySQL database.

## Installation & local setup
1. Copy the `project` folder to your web root. For XAMPP on macOS that is usually `/Applications/XAMPP/xamppfiles/htdocs/project`.
2. Start Apache and MySQL via XAMPP Control Panel.
3. Create a database and import SQL schema (or run SQL to create tables as described below).
4. Edit `config/db.php` and set your DB host, database name, user, and password.
5. Visit `http://localhost/project/` in your browser.

Tip: If PHP is not on your PATH (macOS), generate password hashes with the XAMPP PHP binary:
```
/Applications/XAMPP/xamppfiles/bin/php -r 'echo password_hash("YourPasswordHere", PASSWORD_DEFAULT) . PHP_EOL;'
```

## Database schema (summary)
Minimum tables referenced by the code:

- `users` (user account table)
  - `user_id` INT PRIMARY KEY
  - `name` VARCHAR
  - `student_id` VARCHAR
  - `email` VARCHAR
  - `password_hash` VARCHAR
  - `is_admin` TINYINT (0/1)

- `events` (event data)
  - `event_id` INT PRIMARY KEY
  - `title` VARCHAR
  - `date` DATE or DATETIME
  - `venue` VARCHAR
  - `description` TEXT
  - `organizer_id` INT (FK -> users.user_id)

- `registrations` (user registrations for events)
  - `reg_id` INT PRIMARY KEY
  - `event_id` INT (FK -> events.event_id)
  - `user_id` INT (FK -> users.user_id)
  - `contact_number` VARCHAR
  - `timestamp` DATETIME

Example: promote an existing user to admin (replace email and password_hash):
```sql
UPDATE users SET is_admin = 1 WHERE email = 'admin@example.com';
-- Or insert a new admin (with a password generated via password_hash())
INSERT INTO users (name, student_id, email, password_hash, is_admin)
VALUES ('Site Admin', 'A0001', 'admin@example.com', '$2y$...HASH...', 1);
```

## Configuration
- `config/db.php`: PDO connection used across the app — update DB credentials there.
- (Optional) Add mail settings if you plan to use SMTP/PHPMailer in production.

## Authentication and sessions
- `register.php` implements both user registration and the login handler (POST `register` and POST `login`).
- On successful login, the app sets `$_SESSION['user_id']` and `$_SESSION['is_admin']`.
- `includes/header.php` starts the session and shows/hides nav links based on those flags.

## Admin capabilities
- Admin dashboard: `/project/admin/dashboard.php` (access controlled by `is_admin` session check).
- Admin features:
  - Create, edit, delete events.
  - View participant lists for events.
  - Send announcement emails to registered users (basic implementation).

## Sending email announcements
- The current announcement function in `admin/dashboard.php` uses PHP's `mail()` to send simple HTML emails to all users. On local XAMPP this often fails because an SMTP server is not configured.
- Recommended: Integrate PHPMailer and configure SMTP credentials for reliable delivery.
  1. Install PHPMailer with Composer: `composer require phpmailer/phpmailer`.
  2. Replace the `mail()` loop with PHPMailer SMTP send (support batching and error logging).

## File structure (important files)
- `index.php` — Home page with upcoming highlights.
- `register.php` — Combined Login/Register handler and forms.
- `admin/dashboard.php` — Admin UI for events and announcements.
- `includes/header.php`, `includes/footer.php` — Common layout parts and session initialization.
- `logout.php` — Safe session destroy and redirect.
- `config/db.php` — Database connection (PDO).
- `assets/css/style.css` — Centralized styles; event card color variants added here.
- `assets/images/` — Banner, gallery, and event images.

## Styling and assets
- Project uses a mix of utility classes (Tailwind-like) in templates and centralized CSS in `assets/css/style.css`. Event card colors and small UI helpers were consolidated into the stylesheet for easier maintenance.

## Troubleshooting
- 404 when clicking links from pages in subfolders (e.g. admin): ensure header links are absolute or dynamically constructed. This project uses `/project/...` absolute paths in the header to avoid that issue.
- Email not sending: configure SMTP or use a local mail catcher (MailHog) or PHPMailer with a real SMTP provider.
- Session persists after logout: verify `logout.php` exists at `/project/logout.php` and that browsers clear cookies. Clear browser cookies if needed.

## Security notes
- Passwords are hashed with `password_hash()`.
- Protect admin endpoints with `is_admin` session checks (already implemented in dashboard).
- Add CSRF tokens for sensitive POST actions (delete, announcement send) for production readiness.
- Always escape output with `htmlspecialchars()` to mitigate XSS (templates already use it in many places).

## Developer notes & next steps
- Integrate PHPMailer (SMTP) for announcements.
- Add CSRF protection for admin actions.
- Create an admin seeding script for easier setup of initial admin users.
- Consider batching or background job processing for sending many emails (avoid long-running PHP requests).

---

If you'd like, I can also:
- Add a `config/mail.php` scaffold and implement PHPMailer with example SMTP settings.
- Add a small seeder script to create an initial admin user safely.

Feel free to ask for any addition or clarification — I can update this README with environment-specific instructions (Docker, production Apache/Nginx, etc.).

