# Staff Application System - Quick Reference Card

## 🚀 Quick Start (3 Steps)

### Step 1: Run SQL Migration
```bash
mysql -u root -p webdev < sql/add_gym_to_staff_applications.sql
```
Or copy/paste from `sql/add_gym_to_staff_applications.sql` into phpMyAdmin

### Step 2: Verify Prerequisites
- ✅ Gym owner exists with verified legal documents
- ✅ Customer account exists

### Step 3: Test
1. Login as customer → "View Available Gyms" → Apply
2. Login as gym owner → "Staff Applications" → Review → Approve

---

## 📍 URLs

| Page | URL | Who Can Access |
|------|-----|----------------|
| Available Gyms | `index.php?r=staff/gyms` | Customer |
| Apply to Gym | `index.php?r=staff/apply&gym_id=X` | Customer |
| View Applications | `index.php?r=staff/applications` | Gym Owner |
| Review Application | `index.php?r=staff/review&id=X` | Gym Owner |

---

## 🗄️ Database

### New Column
```sql
staff_applications.gym_owner_id INT NULL
```

### Key Relationships
```
staff_applications.gym_owner_id → users.id (gym owner)
employees.hired_by → users.id (gym owner)
```

---

## 📁 Key Files

### Models
- `app/models/StaffApplication.php` - Added gym_owner_id support
- `app/models/Employee.php` - Added findByGymOwner()

### Controllers
- `app/controllers/StaffController.php` - Added gymsAction(), updated apply/applications

### Views
- `app/views/staff/gyms.php` - NEW: Shows available gyms
- `app/views/staff/apply.php` - UPDATED: Shows gym info
- `app/views/dashboard/customer.php` - UPDATED: Links to gyms page

---

## 🔍 Key Methods

### StaffApplication Model
```php
create($userId, $type, $medCert, $resume, $gymOwnerId)
findAvailableGyms() // Returns verified gyms
findByGymOwner($gymOwnerId) // Filter by gym
```

### Employee Model
```php
findByGymOwner($gymOwnerId) // Filter employees by who hired them
```

### StaffController
```php
gymsAction() // Show available gyms
applyAction() // Apply to specific gym (requires gym_id)
applicationsAction() // Show gym owner's applications only
```

---

## ✅ Testing Checklist

- [ ] SQL migration completed
- [ ] Customer sees available gyms
- [ ] Customer can apply to specific gym
- [ ] Application has gym_owner_id set
- [ ] Gym owner sees only their applications
- [ ] Approval creates employee with correct hired_by
- [ ] Dashboard shows correct status
- [ ] Notifications include gym_id in links

---

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| No gyms appear | Check `legal_documents.status = 'verified'` |
| Foreign key error | Run SQL migration |
| Gym owner sees all apps | Verify `findByGymOwner()` is used |
| Apply page error | Check gym_id parameter is valid |

---

## 📊 Data Flow

```
Customer → Select Gym → Apply (with gym_owner_id)
    ↓
Gym Owner → Review → Approve
    ↓
Create Employee (hired_by = gym_owner_id)
    ↓
Update User Role (trainer/maintenance)
```

---

## 🎯 Success Criteria

✅ **Customer Experience:**
- Can view all verified gyms
- Can see gym details (logo, name, address, owner)
- Can apply to specific gym
- Receives notifications with correct links

✅ **Gym Owner Experience:**
- Sees only applications to their gym
- Can review and approve/reject
- Employees show correct hired_by
- Dashboard shows accurate counts

✅ **Database Integrity:**
- gym_owner_id properly set
- Foreign keys working
- Employees linked to correct gym owner
- No orphaned records

---

## 📞 Need Help?

1. Check `TESTING_GUIDE.md` for detailed test scenarios
2. See `STAFF_APPLICATION_IMPLEMENTATION.md` for full documentation
3. Review `SYSTEM_FLOW_DIAGRAM.md` for visual flow
4. Check `RUN_THIS_SQL_MIGRATION.md` for database setup

---

**Version:** 1.0  
**Date:** May 3, 2026  
**Status:** ✅ Production Ready
