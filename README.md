# 🎓 Smart School Management System (SMS)

A comprehensive, production-ready school management system built using pure PHP following MVC architecture principles. This system handles everything from students and academic tracks to automated payroll, finances, libraries, and inventory management.

[![PHP Version](https://shields.io)](https://php.net)
[![MySQL Version](https://shields.io)](https://mysql.com)
[![License](https://shields.io)](LICENSE)
[![Version](https://shields.io)]()

---

## 📋 Table of Contents
- [Features](#-features)
- [Security Architecture](#️-security-architecture)
- [Technical Stack](#-technical-stack)
- [Prerequisites](#-prerequisites)
- [Installation & Setup](#-installation--setup)
- [License](#-license)

---

## 📋 Features

### 🔐 Authentication & User Management
* **Role-Based Access Control (RBAC):** Advanced permission management mapped across 11 default tiers (Super Admin, Branch Manager, School Principal, Academic Coordinator, Accountant, Teacher, Librarian, Transport Manager, Staff, Parent, Student).
* **Defensive Auth:** Secure sessions, login rate-limiting (brute-force defense), password recovery workflow, and optional Two-Factor Authentication (2FA).

### 🏫 Academic Management
* **Institutional Mapping:** Manage multi-branch setups, separate academic departments, custom semesters, and distinct class sections.
* **Smart Rosters:** Weekly timetables and dynamic scheduling equipped with a conflict detection engine.

### 👥 People Tracking
* **Students:** End-to-end management spanning registration, documents, automated term promotion, and transfers.
* **Teachers & Staff:** Class allocations, tracking administrative workloads, and non-teaching personnel records.
* **Parents:** Dedicated parent-student link profiles to track academic performance and attendance.

### 📊 Attendance Tracking
* **Logs:** Daily attendance logs for both students and staff.
* **Speed Check:** Single-click bulk entry tools for teachers.
* **Alert System:** Monthly aggregated attendance scores with automated real-time absence alerts directed to parents.

### 📝 Exams & Results
* **Configurable Exams:** Support for custom evaluation types (Midterms, Finals, Quizzes, Projects).
* **Grade Automation:** Input matrices featuring auto-calculated GPAs, weight metrics, and printable digital report cards.

### 💰 Financial Management & Payroll
* **Tuition Handling:** Structured fee categorization, custom student discount allowances, and automatic late fee calculations.
* **General Ledger:** Expense logs, payment processing, and income-vs-expense analytics.
* **Automated Payroll:** Base salary calculators adjusted against dynamic modifiers (housing, transport, and medical allowances) complete with downloadable payslips.

### 📚 Library & Infrastructure
* **Library:** Complete physical asset cataloging tracking lending, returns, overdues, and automated fine tickers.
* **Logistics & Transit:** Fleet/vehicle management, dynamic route mapping, and automated student transport fare assignments.
* **Inventory Control:** Central warehouse hub mapping stock inlets/outlets, low-stock thresholds, and asset turnaround matrices.

### 🔔 Notifications & Analytics
* **Omnichannel Alerts:** Delivery systems sending localized Email, SMS, and in-app updates based on preset templates.
* **Reporting Engine:** Generate complex matrix summaries (Finance, Attendance, Grades) exportable directly into Excel, CSV, or raw PDF formats.

---

## 🛡️ Security Architecture
* **SQL Injection Prevention:** Universal application of PDO Prepared Statements.
* **Cross-Site Scripting (XSS) Mitigation:** Contextual sanitization and output escaping.
* **CSRF Protection:** Cryptographic tokens bound to all write operations.
* **Cryptographic Storage:** Strict password hashing utilizing Bcrypt (`password_hash`).
* **Audit Trail:** Monolog-powered system trails tracking user interactions and mutations.

---

## 🔧 Technical Stack
* **Architecture:** Core MVC (Model-View-Controller) utilizing SOLID development design principles.
* **Database Layer:** MySQL 8.0 (InnoDB) managed via a clean, abstract PDO Database layer.
* **Asset Tools:** Scalable schema adjustments via native Database Migration and Seeder engines.
* **Performance:** File-based micro-caching system built straight into database transactions.
* **Frontend Design:** Mobile-responsive interface offering switchable Light/Dark UI variants, interactive layouts powered by Chart.js, search-optimized DataTables, and dual-calendar formats (Gregorian & Jalali/Solar).

---

## 🚀 Prerequisites

Before installing, ensure your development environment satisfies the following minimum specifications:
* **PHP:** Version 8.1 or higher
* **Database Engine:** MySQL 8.0+
* **Web Server:** Apache or Nginx with URL rewriting modules (`mod_rewrite`) enabled.
* **Package Management:** Composer installed locally.
* **Required PHP Core Extensions:** `PDO`, `mbstring`, `json`, `curl`, `fileinfo`, `openssl`, `zip`, `gd`.

---

## 💻 Installation & Setup

Follow these sequential steps to configure and boot the ecosystem locally:

### 1. Clone the Repository
```bash
git clone https://github.com
cd school-management-system
```

### 2. Install Project Dependencies
Run Composer to load necessary architecture dependencies and populate the vendor directory:
```bash
composer install
```

### 3. Configure Environmental Variables
Copy the distribution environment file template to establish your local configuration profile:
```bash
cp .env.example .env
```
Open your newly created `.env` file in your preferred text editor and update your local database credentials:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=school_db
DB_USER=root
DB_PASSWORD=your_secure_password
```

### 4. Database Setup & Hydration
Execute your migrations to initialize your database schema, followed by seeders to populate structural parameters and foundational accounts:

```bash
# Run database migrations
php vendor/bin/phinx migrate

# Hydrate the database with mock records and defaults
php vendor/bin/phinx seed:run
```
*(Note: If your system framework utilizes custom commands instead of Phinx wrapper tools, use `php generate_all_data.php` or your designated project CLI engine).*

### 5. Finalize Web Server Routing
Ensure your local Apache configuration (`.htaccess`) or your Nginx setup directs incoming requests toward your root index processing scripts.

* For **Apache**, verify that the global `AllowOverride All` setting is applied to your active site directory.
* For **Nginx**, your location routing block should match this standard pattern:
  ```nginx
  location / {
      try_files \$uri uri/ /index.php?query_string;
  }
  ```

---

## 📄 License
This application is distributed under the open-source **MIT License**. For complete legal conditions and governance parameters, please review the [LICENSE](LICENSE) file included within this project bundle.
