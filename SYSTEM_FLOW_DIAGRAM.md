# Staff Application System - Flow Diagram

## 🎯 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    STAFF APPLICATION SYSTEM                      │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   CUSTOMER   │         │  GYM OWNER   │         │    ADMIN     │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │                        │                        │
       ▼                        ▼                        ▼
```

## 📋 Customer Flow

```
START: Customer Dashboard
    │
    ├─► Click "View Available Gyms"
    │
    ▼
┌─────────────────────────────────────┐
│  Available Gyms Page                │
│  (index.php?r=staff/gyms)           │
│                                     │
│  Shows:                             │
│  • Gym Logo                         │
│  • Gym Name                         │
│  • Gym Address                      │
│  • Owner Name                       │
│  • Staff Counts                     │
│  • "Apply to This Gym" button      │
└─────────────┬───────────────────────┘
              │
              ├─► Click "Apply to This Gym"
              │
              ▼
┌─────────────────────────────────────┐
│  Application Form                   │
│  (index.php?r=staff/apply&gym_id=X) │
│                                     │
│  Shows:                             │
│  • Gym Info Card                    │
│  • Position Dropdown                │
│  • Medical Certificate Upload       │
│  • Resume Upload                    │
│  • Submit Button                    │
└─────────────┬───────────────────────┘
              │
              ├─► Submit Application
              │
              ▼
┌─────────────────────────────────────┐
│  Database: staff_applications       │
│                                     │
│  INSERT:                            │
│  • user_id = customer ID            │
│  • gym_owner_id = selected gym      │
│  • application_type = position      │
│  • medical_certificate = file path  │
│  • resume = file path               │
│  • status = 'pending'               │
└─────────────┬───────────────────────┘
              │
              ▼
    Application Under Review
```

## 🏢 Gym Owner Flow

```
START: Gym Owner Dashboard
    │
    ├─► Click "Staff Applications"
    │
    ▼
┌─────────────────────────────────────┐
│  Staff Applications Page            │
│  (index.php?r=staff/applications)   │
│                                     │
│  Query:                             │
│  SELECT * FROM staff_applications   │
│  WHERE gym_owner_id = [owner_id]    │
│                                     │
│  Shows:                             │
│  • Applicant Name                   │
│  • Email                            │
│  • Position                         │
│  • Status                           │
│  • Date                             │
│  • Review Button                    │
└─────────────┬───────────────────────┘
              │
              ├─► Click "Review"
              │
              ▼
┌─────────────────────────────────────┐
│  Review Application Page            │
│  (index.php?r=staff/review&id=X)    │
│                                     │
│  Shows:                             │
│  • Applicant Details                │
│  • Medical Certificate (view/flag)  │
│  • Resume (view/flag)               │
│  • Approve/Reject/Flag buttons      │
└─────────────┬───────────────────────┘
              │
              ├─► Click "Approve"
              │
              ▼
┌─────────────────────────────────────┐
│  Database Updates:                  │
│                                     │
│  1. staff_applications:             │
│     • status = 'approved'           │
│     • reviewer_id = gym_owner_id    │
│                                     │
│  2. employees:                      │
│     • INSERT new employee           │
│     • hired_by = gym_owner_id       │
│                                     │
│  3. users:                          │
│     • role = 'trainer'/'maintenance'│
└─────────────┬───────────────────────┘
              │
              ▼
    Employee Hired Successfully
```

## 🔄 Data Relationships

```
┌─────────────────────────────────────────────────────────────┐
│                     DATABASE RELATIONSHIPS                   │
└─────────────────────────────────────────────────────────────┘

users (gym_owner)
    │
    ├─► legal_documents
    │   ├─ gym_name
    │   ├─ gym_logo
    │   ├─ gym_address
    │   ├─ maintenance_count
    │   ├─ trainer_count
    │   └─ status = 'verified' ◄─── Required for gym to appear
    │
    └─► staff_applications
        ├─ user_id (applicant)
        ├─ gym_owner_id ◄─────────── Links to specific gym
        ├─ application_type
        ├─ medical_certificate
        ├─ resume
        └─ status
            │
            └─► (if approved) employees
                ├─ user_id (employee)
                ├─ position
                └─ hired_by ◄─────── Links to gym owner
```

## 🎨 Page Structure

### 1. Available Gyms Page (`/staff/gyms`)

```
┌────────────────────────────────────────────────────────┐
│  🏢 Apply as Staff                                     │
│  Choose a gym to apply as Maintenance or Trainer       │
├────────────────────────────────────────────────────────┤
│                                                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  [LOGO]  │  │  [LOGO]  │  │  [LOGO]  │           │
│  │          │  │          │  │          │           │
│  │ Gym Name │  │ Gym Name │  │ Gym Name │           │
│  │ Address  │  │ Address  │  │ Address  │           │
│  │ Owner    │  │ Owner    │  │ Owner    │           │
│  │ 👷 2 🏋️ 3│  │ 👷 1 🏋️ 2│  │ 👷 3 🏋️ 4│           │
│  │          │  │          │  │          │           │
│  │ [Apply]  │  │ [Apply]  │  │ [Apply]  │           │
│  └──────────┘  └──────────┘  └──────────┘           │
│                                                        │
└────────────────────────────────────────────────────────┘
```

### 2. Application Form (`/staff/apply?gym_id=X`)

```
┌────────────────────────────────────────────────────────┐
│  ← Back to Gyms                                        │
│  👤 Apply as Staff                                     │
│  Submit your application to join [Gym Name]            │
├────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────┐ │
│  │ 🏢 [LOGO]  Gym Name                              │ │
│  │            📍 Address                             │ │
│  │            👤 Owner: John Doe                     │ │
│  └──────────────────────────────────────────────────┘ │
│                                                        │
│  Position: [Maintenance Officer ▼]                    │
│                                                        │
│  Medical Certificate: [Choose File]                   │
│                                                        │
│  Resume / CV: [Choose File]                           │
│                                                        │
│  [Submit Application]                                 │
│                                                        │
└────────────────────────────────────────────────────────┘
```

### 3. Gym Owner Applications (`/staff/applications`)

```
┌────────────────────────────────────────────────────────┐
│  👥 Staff Applications                                 │
│  Review staff applications and manage employees        │
├────────────────────────────────────────────────────────┤
│  Pending Applications                                  │
│  ┌────────────────────────────────────────────────┐   │
│  │ ID │ Name    │ Email      │ Position │ Review │   │
│  ├────┼─────────┼────────────┼──────────┼────────┤   │
│  │ 1  │ John D. │ john@...   │ Trainer  │ [👁️]   │   │
│  │ 2  │ Jane S. │ jane@...   │ Maint.   │ [👁️]   │   │
│  └────────────────────────────────────────────────┘   │
│                                                        │
│  Current Employees                                     │
│  ┌────────────────────────────────────────────────┐   │
│  │ ID │ Name    │ Position │ Available │ Hired   │   │
│  ├────┼─────────┼──────────┼───────────┼─────────┤   │
│  │ 1  │ Mike T. │ Trainer  │ ✅ Yes    │ 2026-04 │   │
│  │ 2  │ Sara L. │ Maint.   │ ✅ Yes    │ 2026-04 │   │
│  └────────────────────────────────────────────────┘   │
│                                                        │
└────────────────────────────────────────────────────────┘
```

## 🔐 Security & Validation

```
┌─────────────────────────────────────────────────────────┐
│  SECURITY CHECKS                                        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Customer applying:                                     │
│  ✓ Must be logged in                                   │
│  ✓ Must have role = 'customer'                         │
│  ✓ gym_id must be valid gym owner                      │
│  ✓ Gym must have verified legal documents              │
│  ✓ File types: PDF, JPG, PNG, DOC, DOCX only          │
│                                                         │
│  Gym owner reviewing:                                   │
│  ✓ Must be logged in                                   │
│  ✓ Must have role = 'gym_owner'                        │
│  ✓ Can only see applications to their gym              │
│  ✓ Can only review applications with their gym_id      │
│                                                         │
│  Database constraints:                                  │
│  ✓ gym_owner_id → users.id (CASCADE DELETE)            │
│  ✓ user_id → users.id (CASCADE DELETE)                 │
│  ✓ reviewer_id → users.id (SET NULL)                   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## 📊 Status Flow

```
Application Status Flow:
┌─────────┐
│ pending │ ◄─── Initial submission
└────┬────┘
     │
     ├─► ┌──────────┐
     │   │ approved │ ─► Create employee, change user role
     │   └──────────┘
     │
     ├─► ┌──────────┐
     │   │ rejected │ ─► Can reapply
     │   └──────────┘
     │
     └─► ┌──────────┐
         │ resubmit │ ─► Flag documents, customer resubmits
         └──────────┘
              │
              └─► back to pending (after resubmission)
```

## 🎯 Key Features Summary

```
✅ Gym Selection
   • Only verified gyms shown
   • Display gym info from legal_documents
   • Show current staff counts

✅ Application Tracking
   • Linked to specific gym
   • Gym owners see only their apps
   • Employees linked to hiring gym

✅ Document Management
   • Per-document status
   • Individual resubmission
   • Flagging with comments

✅ Dashboard Integration
   • Status cards
   • Notifications
   • Proper routing with gym_id
```

---

**This diagram shows the complete flow from customer viewing gyms to gym owner approving applications.**
