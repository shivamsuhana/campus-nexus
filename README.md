# 🌐 CampusNexus — Unified Smart Campus Ecosystem

> The complete digital backbone for modern campus management. Connecting Students, Faculty, and Administrators under one powerful platform.

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

---

## 📋 Project Overview

**CampusNexus** is a comprehensive, multi-role campus management platform built as a Capstone Web Project for **Web Technologies (23CSE404)**. It features **8 interconnected modules**, **3 user roles**, **20+ pages**, and **13 database tables** — solving real, daily campus problems.

### The Problem
- Students have no centralized system to report campus issues
- Study resources are scattered across WhatsApp groups
- Events are missed because notices get buried
- No formal feedback system for mess food quality
- Lost items rarely get returned
- Attendance marking is inefficient and prone to proxying

### The Solution
CampusNexus provides a unified platform that digitizes every aspect of campus life.

---

## ✨ Features

### 8 Modules
| Module | Description |
|--------|-------------|
| 📊 **Smart Attendance** | Faculty generates session codes, students mark attendance, anti-proxy |
| 📚 **Resource Hub** | Upload/download notes, slides, past papers with star ratings |
| 🔧 **Grievance Tracker** | Report campus issues with photos, upvote, track resolution |
| 🛒 **Campus Marketplace** | Buy/sell used books, electronics, furniture |
| 🎪 **Events Hub** | Create, discover, register for campus events |
| 🔍 **Lost & Found** | Report & recover lost items with photo matching |
| 🍽️ **Mess Feedback** | Daily menu, meal ratings, food quality tracking |
| 📢 **Announcements** | Priority-coded notices with department filtering |

### 3 User Roles
- **🎓 Student** — Report issues, buy/sell items, register for events, rate meals, mark attendance
- **👨‍🏫 Faculty** — Upload resources, create events, manage attendance, post announcements
- **🛡️ Admin** — System-wide analytics, user management, grievance resolution, marketplace moderation

### Key Technical Features
- ✅ Role-based access control (RBAC)
- ✅ Session & cookie management across all pages
- ✅ File uploads with image preview (JavaScript FileReader API)
- ✅ AJAX operations (upvoting, rating, event registration)
- ✅ Client-side form validation with password strength meter
- ✅ Dark/Light mode toggle with localStorage + cookies
- ✅ Chart.js analytics dashboards
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Glassmorphism UI with micro-animations
- ✅ Secure password hashing (bcrypt)
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Pagination, search, and filtering

---

## 🛠️ Technologies Used

| Technology | Usage |
|-----------|-------|
| **HTML5** | Semantic page structure, forms, tables |
| **CSS3** | Box Model, Flexbox, Grid, Floats, Positioning, Animations, Media Queries, Custom Properties |
| **JavaScript** | DOM manipulation, Event handling, Fetch API, IntersectionObserver, FileReader, localStorage |
| **PHP 8** | Server-side logic, sessions, cookies, file uploads, form handling, PDO database operations |
| **MySQL** | Database with 13 tables, CRUD operations, JOINs, aggregate functions |
| **Chart.js** | Dashboard analytics (Doughnut, Bar charts) |
| **Font Awesome 6** | Icon library |
| **Google Fonts** | Inter, JetBrains Mono typography |

---

## 📁 Project Structure

```
project/
├── index.php                    # Landing page
├── login.php / register.php     # Authentication
├── dashboard.php                # Role-based dashboard
├── profile.php                  # User profile
├── about.php / contact.php      # Info pages
├── modules/                     # 8 Feature modules
│   ├── attendance.php
│   ├── resources.php
│   ├── grievances.php
│   ├── marketplace.php
│   ├── events.php
│   ├── lost_found.php
│   ├── mess.php
│   └── announcements.php
├── admin/                       # Admin panel
├── api/                         # AJAX endpoints
├── config/                      # Database config
├── includes/                    # Shared components
├── css/                         # Design system
├── js/                          # JavaScript modules
├── sql/                         # Database schema
└── uploads/                     # User uploads
```

---

## 🚀 Setup & Installation

### Prerequisites
- PHP 8.0+ with PDO extension
- MySQL 5.7+ / MariaDB
- Apache/Nginx web server (or XAMPP/WAMP/MAMP)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/campusnexus.git
   ```

2. **Create MySQL database**
   ```sql
   CREATE DATABASE campusnexus;
   ```

3. **Import the schema**
   ```bash
   mysql -u root campusnexus < sql/schema.sql
   ```

4. **Configure database connection**
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'campusnexus');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Set SITE_URL**
   Update `SITE_URL` in `config/database.php` to match your local setup.

6. **Create upload directories**
   ```bash
   mkdir -p uploads/{avatars,grievances,marketplace,events,lost_found,resources,announcements}
   ```

7. **Start the server**
   ```bash
   php -S localhost:8000
   ```

### Demo Accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@campusnexus.com | password |
| Faculty | priya@campusnexus.com | password |
| Student | arjun@campusnexus.com | password |

> Note: All sample accounts use the bcrypt hash for "password".

---

## 📸 Screenshots

> Screenshots will be added after deployment

---

## 🔗 Live Demo

- **Frontend Demo (GitHub Pages):** [Coming Soon]
- **Full Version (PHP Hosting):** [Coming Soon]

---

## 📊 Database Schema

The application uses **13+ relational tables**:
- `users` — User authentication & profiles
- `attendance_sessions` / `attendance_records` — Smart attendance
- `resources` / `resource_ratings` — Study material sharing
- `grievances` / `grievance_comments` / `grievance_upvotes` — Issue tracking
- `marketplace_listings` — Campus marketplace
- `events` / `event_registrations` — Event management
- `lost_found` — Lost & found items
- `mess_menu` / `mess_ratings` — Mess feedback
- `announcements` — Notice board
- `contact_messages` — Contact form submissions

---

## 🎨 Design Highlights

- **Dark-mode first** with light mode toggle
- **Glassmorphism** cards with backdrop blur
- **CSS Custom Properties** for complete design token system
- **8px spacing grid** for visual consistency
- **Module-specific accent colors** for instant recognition
- **Micro-animations** on all interactive elements
- **Responsive** at 3 breakpoints (mobile, tablet, desktop)

---

## 📝 Course Information

- **Course:** Web Technologies (23CSE404)
- **Project Type:** Capstone Web Project
- **Total Marks:** 50
- **Instructor:** Mir Junaid Rasool

---

## 📄 License

This project is created for educational purposes as part of the Web Technologies course curriculum.

---

Built with ❤️ by Krishna
