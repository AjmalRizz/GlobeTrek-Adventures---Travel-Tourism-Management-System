# ✈️ GlobeTrek Adventures

A full-stack **Travel & Tourism Management System** built with **PHP**, **MySQL (PDO)**, **Bootstrap 5**, and vanilla CSS.

GlobeTrek Adventures is an academic web application that allows customers to browse tour packages, make bookings, submit inquiries, and request custom travel plans — while staff and admins manage listings, bookings, payments, and audit logs through dedicated dashboards.

---

## 🌟 Features

| Role | Capabilities |
|---|---|
| **Customer** | Register / Login, Browse packages, Book tours, Pay (simulated), View receipts, Cancel bookings, Submit inquiries, Request custom plans |
| **Staff** | Manage tour packages, accommodations & transport listings, Update booking statuses, Respond to inquiries & custom plans |
| **Admin** | Full staff management (create/toggle status), View analytics dashboard, View audit logs, All staff capabilities |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8+ (PDO, prepared statements, CSRF protection, RBAC)
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5, Bootstrap Icons, Vanilla CSS, Google Fonts (Outfit)
- **Security:** CSRF tokens, password_hash (bcrypt), input sanitization, role-based access control, audit logging

---

## 🚀 Local Setup

### Prerequisites
- PHP 8.0+
- MySQL / MariaDB
- A local server (XAMPP, WAMP, Laragon, or PHP built-in server)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/AjmalRizz/GlobeTrek-Adventures---Travel-Tourism-Management-System.git
   cd GlobeTrek-Adventures---Travel-Tourism-Management-System
   ```

2. **Configure your database credentials**

   Credentials are read from environment variables. For local dev, the defaults in `config/db.php` use:
   - Host: `127.0.0.1`
   - User: `root`
   - Password: *(empty)*
   - Database: `globetrek_db`

   To override, set the following environment variables (or edit `config/db.php` locally — **do not commit changes to that file**):
   ```
   DB_HOST=127.0.0.1
   DB_USER=root
   DB_PASS=
   DB_NAME=globetrek_db
   ```

3. **Run the database installer**

   Visit in your browser (localhost only):
   ```
   http://localhost/globetrek-adventures/config/setup_db.php
   ```
   This creates all tables and seeds sample data including demo accounts.

4. **Launch the application**
   ```
   http://localhost/globetrek-adventures/
   ```

### Demo Accounts (seeded by setup_db.php)

| Role | Email | Password |
|---|---|---|
| Customer | traveler@gmail.com | traveler123 |
| Staff | staff@globetrek.com | staff123 |
| Admin | admin@globetrek.com | admin123 |

> ⚠️ **Change all default passwords immediately if deploying to any non-local environment.**

---

## 📁 Project Structure

```
globetrek-adventures/
├── actions/              # Form processing scripts (auth, booking, payment, etc.)
├── admin/                # Admin-only pages & actions
│   └── actions/
├── assets/               # CSS & images
│   ├── css/
│   └── images/
├── config/
│   ├── db.php            # Database connection (uses env vars)
│   └── setup_db.php      # One-time database installer & seeder
├── customer/             # Customer portal pages
├── includes/             # Shared header, footer, auth helper
├── staff/                # Staff portal pages
├── .env.example          # Environment variable template
├── .gitignore
├── index.php             # Homepage
├── login.php
├── register.php
├── packages.php
├── contact.php
└── ...
```

---

## 🔐 Security Notes

- All database queries use **PDO prepared statements** — no raw SQL interpolation.
- All forms are protected by **CSRF tokens** (`hash_equals` validation).
- Passwords are hashed with **bcrypt** (`password_hash` / `password_verify`).
- **Role-Based Access Control (RBAC)** enforced on every protected page and action.
- All user-facing output is escaped with `htmlspecialchars()`.
- `setup_db.php` is **restricted to localhost** — it returns HTTP 403 if accessed remotely.
- An **audit log** is maintained in the database for all significant user actions.

---

## ⚠️ Disclaimer

This project is built for **academic / portfolio demonstration purposes**. The payment system is **simulated** and does not process real transactions. Do not use this in a real production environment without a full security review and real payment gateway integration.

---

## 📄 License

This project is open-source and available for portfolio and educational use.
