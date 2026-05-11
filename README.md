# 🏋️ Nutrify — Gym Management System

> A comprehensive, multi-role web-based gym management system built with PHP and MySQL.

---

## 📌 Project Title

**Nutrify** — Integrated Gym Management System

---

## 📖 Overview

**Nutrify** is a web-based gym management system designed to streamline the operations of fitness centers and improve the experience of fitness enthusiasts. It provides a unified platform where gym owners can manage their facilities, administrative officers can handle memberships and payments, fitness trainers can monitor their assigned members, and fitness enthusiasts can apply for memberships and track their progress.

The system is built using a clean **MVC (Model-View-Controller)** architecture with **PHP**, **MySQL**, and **Bootstrap 5**, and integrates online payment processing via **PayMongo**, real-time in-app notifications, and role-based access control for multiple user types.

Key capabilities include:
- Multi-role authentication (Admin, Gym Owner, Administrative Officer, Fitness Trainer, Maintenance Officer, Fitness Enthusiast)
- Legal document submission and verification workflow
- Membership application, approval, and code generation
- Staff recruitment and management
- Equipment inventory and financial tracking
- Online payment integration (PayMongo)
- Login activity monitoring and audit trail

---

## ❗ Problem Statement

1. **Inconsistent progress tracking across different fitness programs.**
   Fitness enthusiasts lack a centralized way to track their progress across multiple programs and gym services, leading to poor continuity and motivation loss.

2. **Ineffective fitness programs across diverse populations.**
   Gyms often apply one-size-fits-all programs that do not account for the varied fitness levels, goals, and health conditions of a diverse membership base, resulting in suboptimal outcomes.

3. **Inconsistent feedback delivery from fitness coaches.**
   There is no standardized channel for fitness coaches to deliver timely, structured feedback to their members, causing delays in performance correction and reduced member satisfaction.

---

## 🎯 Objectives

| # | Objective | Success Metric |
|---|-----------|---------------|
| 1 | Develop a **consistent progress tracking** system integrated across all fitness core programs | ≥ 95% data alignment accuracy |
| 2 | Implement **effective core fitness programs** adaptable to diverse populations and fitness levels | ≥ 90% improvement rate among enrolled members |
| 3 | Establish a **consistent feedback delivery** mechanism from fitness coaches to members | ≥ 95% on-time feedback response rate |

---

## 👥 Target Users / Personas

### 1. 🏃 Fitness Enthusiast
- **Who:** Individuals who want to join a gym, track their fitness journey, and improve their health.
- **Needs:** Easy membership application, access to fitness programs, membership code verification, and real-time updates on application status.
- **Pain Points:** Lack of centralized progress tracking, no structured feedback from coaches.

### 2. 🏢 Gym Owner
- **Who:** Business owners or operators of fitness centers registered on the Nutrify platform.
- **Needs:** Legal document submission and verification, financial dashboard, equipment inventory management, staff recruitment, membership oversight, and assigning administrative officers.
- **Pain Points:** Manual and fragmented management of gym operations; difficulty tracking revenue and expenses.

### 3. 🗂️ Administrative Officer *(assigned by Gym Owner)*
- **Who:** Staff members assigned by the gym owner to handle day-to-day membership and administrative operations.
- **Needs:** Review and approve membership applications, confirm payments, generate membership codes, assign fitness trainers, and manage attendance logs.
- **Pain Points:** Inefficient manual approval workflows and no centralized payment confirmation system.

### 4. 💪 Fitness Trainer
- **Who:** Certified trainers employed by the gym to guide and coach members.
- **Needs:** View assigned members, deliver structured feedback, and track member attendance and performance.
- **Pain Points:** Inconsistent feedback channels and no structured assignment tracking.

### 5. 🔧 Maintenance Officer
- **Who:** Staff responsible for equipment upkeep and facility maintenance.
- **Needs:** View equipment inventory, log maintenance activities, and report equipment issues.
- **Pain Points:** No centralized equipment tracking or maintenance scheduling.

---

## 🏗️ System Architecture

```
webdev/
├── app/
│   ├── config/         # Database and app configuration
│   ├── controllers/    # MVC Controllers (Admin, GymOwner, AdmOfficer, etc.)
│   ├── core/           # Core framework (App, Controller, Model, Database)
│   ├── models/         # Data models (User, GymMember, Employee, etc.)
│   └── views/          # HTML/PHP views per role
├── public/
│   └── assets/         # CSS, JS, images
├── sql/                # Database migration scripts
├── .gitignore          # Protects secrets from version control
├── index.php           # Application entry point
└── README.md
```

---

## 🔐 Security

- Passwords hashed with `password_hash()` (bcrypt)
- All user inputs sanitized with `htmlspecialchars()` and PDO prepared statements
- Secret credentials (SMTP password, Google OAuth, PayMongo keys) are **excluded from version control** via `.gitignore`
- Login activity logging and audit trail for admin monitoring

---

## ⚙️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x (MVC, no framework) |
| Database | MySQL via XAMPP |
| Frontend | Bootstrap 5.3, Bootstrap Icons, Vanilla CSS |
| Authentication | Session-based + Google OAuth 2.0 |
| Email | PHPMailer (SMTP via Gmail) |
| Payments | PayMongo API |
| Version Control | Git + GitHub |

---

## 🚀 How to Run Locally

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Clone this repository into `htdocs/`:
   ```bash
   git clone https://github.com/dadonggg/Current-Nutrify.git
   cd Current-Nutrify
   ```
3. Import the database:
   - Open `http://localhost/phpmyadmin`
   - Create a database named `webdev`
   - Import `sql/database.sql`
4. Copy config template and fill in credentials:
   ```bash
   cp app/config/google.example.php app/config/google.php
   ```
5. Visit `http://localhost/Current-Nutrify` in your browser.

---

## 👨‍💻 Developer

- **Project:** Nutrify Gym Management System
- **Course:** Capstone Project
- **GitHub:** [@dadonggg](https://github.com/dadonggg)
