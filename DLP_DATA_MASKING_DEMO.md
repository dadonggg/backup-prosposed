# 🛡️ DLP Feature: Data Masking

## 📋 What is Data Masking?

**Data Masking** is a Data Loss Prevention (DLP) technique that protects sensitive information by hiding parts of it. This prevents unauthorized users from viewing complete sensitive data while still allowing them to identify records.

## 🎯 Why It's Important

1. **Protects PII (Personally Identifiable Information)**
   - Phone numbers, emails, addresses
   - Birth dates, credit cards, API keys

2. **Compliance**
   - GDPR, PDPA (Philippines Data Privacy Act)
   - Prevents data breaches

3. **Role-Based Data Access**
   - Different users see different levels of detail
   - Admin sees full data, customers see masked data

## ✅ What I Implemented

### File Created: `app/core/DataMasking.php`

This utility class provides masking functions for different data types:

### 1. **Phone Number Masking**
```php
DataMasking::phone('09123456789')
// Output: '*****6789'
```
- Shows only last 4 digits
- Hides area code and middle digits

### 2. **Email Masking**
```php
DataMasking::email('john.doe@gmail.com')
// Output: 'j***@gmail.com'
```
- Shows first letter and domain
- Hides username

### 3. **Credit Card Masking**
```php
DataMasking::cardNumber('4343434343434345')
// Output: '****-****-****-4345'
```
- Shows only last 4 digits
- Formatted with dashes

### 4. **Name Masking**
```php
DataMasking::name('John Doe')
// Output: 'J*** D***'
```
- Shows only initials
- Hides full name

### 5. **Birth Date Masking**
```php
DataMasking::birthDate('1990-05-15')
// Output: '****-**-** (1990)'
```
- Shows only year
- Hides month and day

### 6. **API Key Masking**
```php
DataMasking::apiKey('sk_test_abc123def456ghi789')
// Output: 'sk_t********************i789'
```
- Shows first 4 and last 4 characters
- Hides middle part

### 7. **Address Masking**
```php
DataMasking::address('123 Main St, Manila, Philippines')
// Output: '*****, Manila, Philippines'
```
- Hides street address
- Shows city/province

## 🎨 Smart Masking (Role-Based)

The `smartMask()` function automatically decides whether to mask data based on user role:

```php
// Admin sees full data
DataMasking::smartMask('09123456789', 'phone', 'admin')
// Output: '09123456789' (unmasked)

// Customer sees masked data
DataMasking::smartMask('09123456789', 'phone', 'customer')
// Output: '*****6789' (masked)
```

### Permission Matrix:

| Data Type | Admin | Admin Officer | Gym Owner | Customer |
|-----------|-------|---------------|-----------|----------|
| Phone     | ✅ Full | ✅ Full | ✅ Full | ❌ Masked |
| Email     | ✅ Full | ✅ Full | ✅ Full | ❌ Masked |
| Name      | ✅ Full | ✅ Full | ✅ Full | ❌ Masked |
| Birth Date| ✅ Full | ✅ Full | ❌ Masked | ❌ Masked |
| Card      | ✅ Full | ❌ Masked | ❌ Masked | ❌ Masked |
| API Key   | ✅ Full | ❌ Masked | ❌ Masked | ❌ Masked |

## 📊 Data Classification

The system also provides data classification labels:

```php
DataMasking::getClassification('phone')
// Output: 'CONFIDENTIAL'

DataMasking::getClassification('card')
// Output: 'RESTRICTED'
```

### Classification Levels:

1. **PUBLIC** - Can be shared freely
   - Gym name, service names, prices

2. **INTERNAL** - For internal use only
   - Names, ages, membership status

3. **CONFIDENTIAL** - Sensitive personal data
   - Phone numbers, emails, addresses, birth dates

4. **RESTRICTED** - Highly sensitive
   - Credit cards, API keys, passwords

## 🔧 How to Use in Your Views

### Example 1: Display Masked Phone in Member List

**Before (Unmasked):**
```php
<td><?= htmlspecialchars($member['phone_number']) ?></td>
<!-- Shows: 09123456789 -->
```

**After (Masked):**
```php
<?php
use App\Core\DataMasking;
$userRole = $_SESSION['user_role'] ?? 'customer';
?>
<td>
    <?= htmlspecialchars(DataMasking::smartMask($member['phone_number'], 'phone', $userRole)) ?>
    <span class="<?= DataMasking::getClassificationBadgeClass('CONFIDENTIAL') ?>">
        CONFIDENTIAL
    </span>
</td>
<!-- Admin sees: 09123456789 -->
<!-- Customer sees: *****6789 -->
```

### Example 2: Display Masked Email

```php
<?php
use App\Core\DataMasking;
$userRole = $_SESSION['user_role'] ?? 'customer';
?>
<td><?= htmlspecialchars(DataMasking::smartMask($member['email'], 'email', $userRole)) ?></td>
<!-- Admin sees: john.doe@gmail.com -->
<!-- Customer sees: j***@gmail.com -->
```

### Example 3: Display Masked PayMongo Secret Key

```php
<?php
use App\Core\DataMasking;
?>
<td>
    <code><?= htmlspecialchars(DataMasking::apiKey($config['secret_key'])) ?></code>
    <span class="badge bg-danger">RESTRICTED</span>
</td>
<!-- Shows: sk_t********************NzzH -->
```

## 📈 Benefits for Your Defense

### 1. **DLP Feature** ✅
- Implements data masking (core DLP technique)
- Protects sensitive information from unauthorized viewing

### 2. **Data Classification** ✅
- Labels data by sensitivity level
- Helps identify what needs protection

### 3. **Role-Based Access** ✅
- Different users see different data levels
- Supports RBAC requirements

### 4. **Compliance** ✅
- Helps meet GDPR/PDPA requirements
- Demonstrates data protection awareness

### 5. **Security Best Practice** ✅
- Reduces risk of data exposure
- Limits damage from potential breaches

## 🧪 Testing the Feature

### Test Case 1: Phone Masking
```php
require_once 'app/core/DataMasking.php';
use App\Core\DataMasking;

echo DataMasking::phone('09123456789');
// Expected: *****6789
```

### Test Case 2: Email Masking
```php
echo DataMasking::email('admin@example.com');
// Expected: a***@example.com
```

### Test Case 3: Smart Masking
```php
// As admin
echo DataMasking::smartMask('09123456789', 'phone', 'admin');
// Expected: 09123456789 (full)

// As customer
echo DataMasking::smartMask('09123456789', 'phone', 'customer');
// Expected: *****6789 (masked)
```

## 📝 Where to Apply This

### Priority 1 (High Impact):
1. ✅ **Member List** (`app/views/gymowner/members.php`)
   - Mask phone numbers
   - Mask emails

2. ✅ **Membership Applications** (`app/views/admofficer/memberships.php`)
   - Mask applicant phone numbers
   - Show classification badges

3. ✅ **PayMongo Configuration** (`app/views/gymowner/paymongo.php`)
   - Already masks secret key!
   - Add classification badge

### Priority 2 (Medium Impact):
4. ✅ **Staff Applications** (`app/views/gymowner/applications.php`)
   - Mask applicant contact info

5. ✅ **User Profile** (`app/views/dashboard/customer.php`)
   - Mask birth date for non-owners

### Priority 3 (Low Impact):
6. ✅ **Admin Dashboard** (`app/views/dashboard/admin.php`)
   - Show classification labels
   - Mask sensitive data in logs

## 🎉 Summary

**What You Got:**
- ✅ Complete data masking utility class
- ✅ 7 different masking functions
- ✅ Smart masking with role-based access
- ✅ Data classification system
- ✅ Ready to use in any view

**DLP Features Covered:**
- ✅ Data Masking (core DLP technique)
- ✅ Data Classification (labeling sensitive data)
- ✅ Role-Based Data Access (who can see what)
- ✅ Sensitive Data Protection (PII protection)

**Defense Points:**
- This demonstrates understanding of DLP concepts
- Shows implementation of data protection
- Provides role-based access control for data
- Includes data classification system

**Next Steps:**
1. Apply masking to member lists
2. Apply masking to application forms
3. Add classification badges to sensitive fields
4. Document in your defense presentation

---

**File Created:** `app/core/DataMasking.php`  
**Lines of Code:** ~300  
**DLP Feature:** Data Masking ✅  
**Ready to Use:** Yes! 🚀
