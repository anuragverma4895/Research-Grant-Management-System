<div align="center">
  
# 🎓 Research Grant Management System (RGMS)

**A Production-Ready Full-Stack Web Application for Academic and Research Institutions**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](#)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](#)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](#)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)](#)

[Live Demo](https://grant-management-system.infinityfreeapp.com) • [Report Bug](#) • [Request Feature](#)

</div>

---

## 📖 About The Project

The **Research Grant Management System (RGMS)** is a comprehensive, digital solution designed to streamline the entire lifecycle of research funding. Built with raw PHP and modernized with Tailwind CSS, this platform eliminates paperwork, providing dedicated portals for Researchers, Reviewers, and Administrators to collaborate efficiently.

### 🎯 Key Objectives
- **Digitize Applications**: Move from paper-based submissions to a secure digital portal.
- **Role-Based Workflows**: Separate access and tools for Admins, Researchers, and Reviewers.
- **Data-Driven Insights**: Provide administrators with visual analytics via Chart.js.

---

## ✨ Core Features

### 🛡️ 1. Multi-Role Architecture
- **Admin Portal**: Complete oversight. Manage agencies, researchers, applications, and system settings.
- **Reviewer Portal**: Dedicated interface for academic reviewers to evaluate proposals, assign scores (1-10), and provide recommendations.
- **Researcher Portal**: User-friendly dashboard to track past applications, view funding stats, and submit new grant proposals.

### 📝 2. Seamless Application & File Uploads
- Modern application form with drag-and-drop **PDF proposal uploading**.
- Validated form fields ensuring complete data collection (Title, Budget, Agency, Duration).
- Real-time application status tracking (Submitted ➔ Under Review ➔ Approved/Rejected).

### 📊 3. Interactive Analytics Dashboard
- Built-in **Chart.js** integration.
- Dynamic Doughnut charts showing application status distribution.
- Bar charts tracking application submission trends over months.

### 🔍 4. Advanced Search & Filtering
- Server-side AJAX-ready search functionality.
- Filter thousands of applications by Status, Name, Title, or Agency instantly.

### 🌐 5. RESTful API Endpoints
- Includes built-in API routes (`/api/applications.php` and `/api/users.php`).
- Supports `GET` requests with pagination and `POST` requests secured by API keys.

---

## 💻 Technology Stack

* **Frontend**: HTML5, Vanilla JavaScript, Tailwind CSS (via CDN)
* **Backend**: Core PHP 8.x
* **Database**: MySQL / MariaDB (Optimized with structured indexes)
* **Data Visualization**: Chart.js v4
* **Security**: `password_hash()` for auth, CSRF Tokens, Prepared Statements (SQLi protection), `.htaccess` directory restrictions.

---

## 🚀 Installation & Setup

### Prerequisites
* A local server environment (XAMPP, WAMP, or LAMP stack)
* PHP version 8.0 or higher
* MySQL database

### Step-by-Step Guide

1. **Clone the repository**
   ```bash
   git clone https://github.com/anuragverma4895/Research-Grant-Management-System.git
   cd Research-Grant-Management-System
   ```

2. **Database Configuration**
   - Open your MySQL manager (e.g., phpMyAdmin).
   - Create a new database named `grant_db`.
   - Import the provided `SQL Queries.txt` file into this database to create tables and sample data.

3. **Environment Setup**
   - Open `config.php` in the root directory.
   - Update the database credentials to match your local setup:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'grant_db');
     ```

4. **Directory Permissions**
   - Ensure the `uploads/` directory has write permissions so users can upload PDF proposals.

5. **Run the Application**
   - Start your Apache and MySQL servers.
   - Navigate to `http://localhost/Research-Grant-Management-System` in your web browser.

---

## 📂 Project Structure

```text
📁 Research-Grant-Management-System/
├── 📄 index.php                 # Beautiful Landing Page
├── 📄 config.php                # Global Constants & DB Config
├── 📄 db_connection.php         # Secure MySQLi Connection
├── 📄 functions.php             # Core Reusable PHP Methods
├── 📄 auth_check.php            # Session & Role Middleware
├── 📁 api/                      # RESTful JSON Endpoints
│   ├── applications.php
│   └── users.php
├── 📁 uploads/                  # Secure PDF Storage
│   └── .htaccess                # Anti-execution Security
├── 📄 admin_dashboard.php       # Admin Analytics Hub
├── 📄 user_dashboard.php        # Researcher Hub
├── 📄 reviewer_dashboard.php    # Proposal Evaluation Hub
└── 📄 style.css & script.js     # Custom UI Enhancements
```

---

## 👨‍💻 Developed By

**Anurag Verma**  
Full-Stack Developer  
* [GitHub Profile](https://github.com/anuragverma4895)

---

<div align="center">
  <i>If you found this project helpful, please consider giving it a ⭐ on GitHub!</i>
</div>
