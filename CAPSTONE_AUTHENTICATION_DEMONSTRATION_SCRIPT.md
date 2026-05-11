# Capstone Project Authentication Demonstration Script
## Nutrify Gym Management System

### Project Overview
**Project Name:** Nutrify Gym Management System  
**Technology Stack:** PHP 8.1+, MySQL, Bootstrap 5, MVC Architecture  
**Core Features:** User Authentication, Role-based Access, Gym Management, Equipment Inventory, Financial Tracking  

---

## STEP 1: REGISTRATION WORKFLOW

### 1.1 Navigate to Homepage
**Action:** Launch the application and navigate to the landing page
- **URL:** `http://localhost/webdev/` or your configured domain
- **Expected View:** Modern landing page with Nutrify branding
- **Key Elements to Point Out:**
  - Clean, professional design with animated background
  - Navigation bar with "Login" and "Sign Up" buttons
  - Hero section with "Get Started Free" and "Sign In" buttons
  - Feature cards showing system capabilities

**Script:** *"This is the Nutrify Gym Management System landing page. As you can see, it's a modern, responsive interface designed for fitness enthusiasts, gym owners, and staff. The system provides comprehensive gym management capabilities including membership management, financial tracking, equipment inventory, and legal document processing."*

### 1.2 Access Sign-Up
**Action:** Locate and select the Sign Up or Registration option
- **Method 1:** Click "Sign Up" button in navigation bar
- **Method 2:** Click "Get Started Free" button in hero section
- **Method 3:** Direct URL: `http://localhost/webdev/index.php?r=auth/register`
- **Expected Result:** Redirects to registration form

**Script:** *"I'll click on the 'Sign Up' button to access the registration form. Notice how the system provides multiple entry points for user registration, following modern UX best practices."*

### 1.3 Input Credentials
**Action:** Fill out the registration form using test credentials
- **Test Data to Use:**
  ```
  First Name: John
  Last Name: Doe  
  Middle Initial: M
  Birth Date: 1995-06-15
  Height: 175 cm
  Weight: 70 kg
  Email: john.doe.test@example.com
  Password: SecurePass123!
  Confirm Password: SecurePass123!
  ```

**Key Points to Highlight:**
- Form validation (required fields, email format, password strength)
- User-friendly interface with clear labels and help text
- Responsive design that works on different screen sizes
- Age computation from birth date
- Physical measurements for fitness tracking

**Script:** *"I'm filling out the comprehensive registration form. Notice that the system collects essential user information including personal details and physical measurements, which are important for a fitness management system. The password field requires a minimum of 8 characters for security."*

---

## STEP 2: SECURITY & DATABASE IMPLEMENTATION

### 2.1 Password Hashing Demonstration
**Action:** Show that the system applies hashing algorithm before database storage

**Code Location:** `app/controllers/AuthController.php` (Line 89)
```php
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
```

**Key Security Features to Highlight:**
- Uses PHP's `password_hash()` function with `PASSWORD_DEFAULT` algorithm
- Currently implements bcrypt algorithm (industry standard)
- Automatic salt generation for each password
- No plain text passwords stored anywhere in the system

**Script:** *"Before I submit the form, let me show you the security implementation. In the AuthController, you can see that the system uses PHP's built-in password_hash function with PASSWORD_DEFAULT, which currently implements the bcrypt algorithm. This ensures that passwords are never stored in plain text and each password gets a unique salt."*

### 2.2 Database Verification
**Action:** Open database management tool and locate the newly created user record

**Database Details:**
- **Database Name:** `webdev`
- **Table:** `users`
- **Key Columns to Show:**
  - `id` (Primary Key)
  - `firstname`, `lastname`, `middle_initial`
  - `email` (Unique identifier)
  - `password` (Hashed string)
  - `is_verified` (Email verification status)
  - `created_at` (Timestamp)

**Steps:**
1. Open phpMyAdmin or MySQL Workbench
2. Navigate to `webdev` database
3. Open `users` table
4. Locate the newly created record
5. **CRITICAL:** Show that password field contains encrypted hash, not plain text

**Expected Password Hash Format:**
```
$2y$10$randomSaltAndHashedPasswordString...
```

**Script:** *"Now let me open the database to verify that the password is properly hashed. As you can see in the users table, the password field contains a bcrypt hash starting with '$2y$10$' which indicates the algorithm and cost factor. The original password 'SecurePass123!' is completely unrecoverable from this hash - this is one-way encryption that meets industry security standards."*

---

## STEP 3: AUTHENTICATION & VALIDATION

### 3.1 Login Attempt
**Action:** Return to the application and navigate to the Login page
- **Method 1:** Click "Login" in navigation
- **Method 2:** Direct URL: `http://localhost/webdev/index.php?r=auth/login`
- **Expected View:** Clean login form with email and password fields

**Script:** *"Now I'll test the authentication system by logging in with the credentials I just created. I'm navigating to the login page which shows a clean, professional interface."*

### 3.2 Credential Entry
**Action:** Enter the exact username and password used during registration
- **Email:** `john.doe.test@example.com`
- **Password:** `SecurePass123!`

**Authentication Process to Highlight:**
1. **Email Validation:** System checks if email exists in database
2. **Password Verification:** Uses `password_verify()` function to compare
3. **Account Status Check:** Verifies email verification status
4. **2FA Implementation:** System sends OTP code for additional security
5. **Session Management:** Creates secure session upon successful authentication

**Code Location:** `app/controllers/AuthController.php` (Lines 25-35)
```php
if (!password_verify($password, (string)$user['password'])) {
    $loginActivityModel->logLoginFailed($email, 'Invalid password');
    $error = 'Invalid email or password.';
} else {
    // Generate OTP for 2FA
    $otp = (string)random_int(100000, 999999);
    // ... OTP email sending logic
}
```

**Script:** *"I'm entering the same credentials used during registration. The system will first verify the email exists, then use password_verify() to securely compare the entered password against the stored hash. Notice that the system also implements two-factor authentication with OTP codes for enhanced security."*

### 3.3 Verification of Success
**Action:** Upon submission, demonstrate successful authentication

**Expected Flow:**
1. **Initial Validation:** Email and password verified
2. **OTP Generation:** 6-digit code generated and sent via email
3. **OTP Entry:** User enters received OTP code
4. **Session Creation:** Secure session established
5. **Dashboard Redirect:** User redirected to personalized dashboard

**Success Indicators:**
- **URL Change:** Redirects to `index.php?r=home/index`
- **Welcome Message:** Shows "Welcome" with user's full name
- **Profile Information:** Displays user's personal details
- **Navigation Menu:** Shows role-based navigation options
- **Session Security:** User ID stored in secure session

**Dashboard Features to Highlight:**
- Personalized welcome message with user's name
- Profile section showing registered information
- Role-based interface (customer dashboard in this case)
- Secure logout functionality
- Clean, professional design consistent with landing page

**Script:** *"Perfect! The authentication was successful. As you can see, I'm now logged into the system with a personalized dashboard showing my name 'John Doe' and the email I registered with. The system has created a secure session and is displaying role-appropriate content. This completes the full user lifecycle from registration to successful authentication."*

---

## ADDITIONAL SECURITY FEATURES TO HIGHLIGHT

### Login Activity Tracking
**Location:** `app/models/LoginActivity.php`
- Logs all login attempts (successful and failed)
- Tracks IP addresses and user agents
- Monitors OTP generation and validation
- Provides audit trail for security analysis

### Email Verification System
**Location:** `app/models/EmailVerification.php`
- Requires email verification before account activation
- Uses cryptographically secure tokens
- Prevents unauthorized account creation

### Role-Based Access Control
**Roles Available:**
- `customer` (default)
- `gym_owner`
- `admin`
- `administrative_officer`
- `trainer`
- `maintenance`

### Data Validation & Sanitization
- Input validation on all form fields
- SQL injection prevention with prepared statements
- XSS protection with proper output encoding
- CSRF protection with form tokens

---

## PROJECT STRUCTURE OVERVIEW

### Core Architecture
```
webdev/
├── app/
│   ├── controllers/     # MVC Controllers
│   ├── models/         # Database Models
│   ├── views/          # User Interface Templates
│   └── core/           # Framework Core Classes
├── public/             # Web-accessible files
├── sql/               # Database migrations
└── index.php          # Application entry point
```

### Key Files Demonstrated
1. **AuthController.php** - Handles registration and login logic
2. **User.php** - User model with password hashing
3. **register.php** - Registration form view
4. **login.php** - Login form view
5. **home.php** - Authenticated user dashboard

### Database Schema
- **users table** - Core user information with hashed passwords
- **email_verification** - Email verification tokens
- **otp_codes** - Two-factor authentication codes
- **login_activity** - Security audit trail

---

## CONCLUSION

This demonstration shows a complete, production-ready authentication system with:

✅ **Secure Registration** - Comprehensive user data collection with validation  
✅ **Password Hashing** - Industry-standard bcrypt encryption  
✅ **Database Security** - No plain text passwords stored  
✅ **Secure Authentication** - Password verification with 2FA  
✅ **Session Management** - Secure session handling  
✅ **Audit Trail** - Complete login activity tracking  
✅ **Professional UI** - Modern, responsive user interface  

The system exceeds basic requirements by implementing additional security features like two-factor authentication, email verification, and comprehensive activity logging, making it suitable for real-world deployment in a gym management environment.