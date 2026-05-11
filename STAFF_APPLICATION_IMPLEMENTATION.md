# Staff Application System - Implementation Guide

## Overview
This implementation allows customers to view available gyms and apply as staff (Maintenance Officer or Fitness Trainer) to specific gyms. Gym owners can then review and approve/reject applications for their gym.

## Database Changes

### 1. Add gym_owner_id to staff_applications table
Run the SQL file: `sql/add_gym_to_staff_applications.sql`

```sql
ALTER TABLE staff_applications 
ADD COLUMN gym_owner_id INT NULL AFTER user_id,
ADD KEY idx_staff_app_gym_owner (gym_owner_id),
ADD CONSTRAINT fk_staff_app_gym_owner 
    FOREIGN KEY (gym_owner_id) REFERENCES users(id) 
    ON DELETE CASCADE;
```

## Features Implemented

### 1. **Customer View - Available Gyms** (`/staff/gyms`)
- Displays all verified gyms with:
  - Gym logo
  - Gym name
  - Gym address
  - Owner name
  - Current staff count (maintenance & trainers)
- Customers can click "Apply to This Gym" to start application

### 2. **Customer View - Staff Application** (`/staff/apply?gym_id=X`)
- Shows selected gym information at the top
- Application form with:
  - Position selection (Maintenance Officer or Fitness Trainer)
  - Medical certificate upload
  - Resume/CV upload
- Supports document resubmission for flagged documents
- Back button to return to gym list

### 3. **Gym Owner View - Staff Applications** (`/staff/applications`)
- Shows only applications submitted to their gym
- Shows only employees hired by them
- Can review, approve, or reject applications
- Per-document review system with status tracking

### 4. **Customer Dashboard Updates**
- "Apply as Staff" card now links to gym selection page
- Shows staff application status
- Notifications for resubmission/rejection link to correct gym

## File Changes

### Models
- **app/models/StaffApplication.php**
  - Added `gym_owner_id` parameter to `create()` method
  - Added `findAvailableGyms()` - fetches verified gyms
  - Added `findByGymOwner()` - filters applications by gym owner

- **app/models/Employee.php**
  - Added `findByGymOwner()` - filters employees by who hired them

### Controllers
- **app/controllers/StaffController.php**
  - Added `gymsAction()` - displays available gyms
  - Updated `applyAction()` - requires gym_id parameter, shows gym info
  - Updated `applicationsAction()` - filters by gym owner

### Views
- **app/views/staff/gyms.php** (NEW)
  - Grid layout of available gyms
  - Shows gym logo, name, address, owner, staff count
  - Apply button for each gym

- **app/views/staff/apply.php** (UPDATED)
  - Added gym information card at top
  - Added back button to gym list
  - Updated success messages to include gym name

- **app/views/dashboard/customer.php** (UPDATED)
  - Updated "Apply as Staff" link to point to gym selection
  - Updated notification links to include gym_id

## How It Works

### Application Flow:
1. Customer logs in and goes to dashboard
2. Clicks "View Available Gyms" in "Apply as Staff" card
3. Sees list of all verified gyms
4. Clicks "Apply to This Gym" on desired gym
5. Fills out application form with documents
6. Application is submitted with gym_owner_id
7. Gym owner sees application in their "Staff Applications" page
8. Gym owner reviews and approves/rejects
9. If approved, customer becomes staff member

### Database Relationships:
```
staff_applications
├── user_id → users.id (applicant)
├── gym_owner_id → users.id (gym owner)
└── reviewer_id → users.id (who reviewed)

employees
├── user_id → users.id (employee)
└── hired_by → users.id (gym owner who hired)
```

## Testing Checklist

- [ ] Run SQL migration: `sql/add_gym_to_staff_applications.sql`
- [ ] Verify gym owner has verified legal documents
- [ ] Customer can see available gyms at `/staff/gyms`
- [ ] Customer can apply to specific gym
- [ ] Gym owner sees only their applications
- [ ] Gym owner can approve/reject applications
- [ ] Approved staff becomes employee with correct hired_by
- [ ] Dashboard shows correct status and links

## Notes

- Only gyms with `legal_documents.status = 'verified'` appear in the list
- Gym information comes from the `legal_documents` table (gym_name, gym_logo, gym_address)
- Staff counts (maintenance_count, trainer_count) are stored in `legal_documents` table
- Each gym owner only sees applications submitted to their gym
- Employees are linked to the gym owner who hired them via `hired_by` field
