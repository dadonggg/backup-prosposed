# 💳 Payment System & Service Management Implementation Plan

## 🎯 Overview

Complete payment and service management system with PayMongo integration.

## 📋 Features to Implement

### 1. Gym Services Management (Gym Owner)
- ✅ Already exists: `gym_services` table
- ✅ Already exists: Gym owner can add/edit services
- ✅ Already exists: Service name and price fields
- 🔧 **Need to update:** Show services in membership application

### 2. Membership Application Updates
- 🔧 **Change:** Remove hardcoded plans
- 🔧 **Add:** Load services from gym owner's `gym_services` table
- 🔧 **Add:** Show service name and price dynamically
- 🔧 **Add:** Calculate total based on selected service

### 3. Trainer Assignment (Admin Officer)
- 🔧 **Add:** When member selects "With Personal Trainer"
- 🔧 **Add:** Admin officer sees list of available trainers
- 🔧 **Add:** Admin officer assigns trainer to member
- 🔧 **Add:** Trainer becomes unavailable after assignment

### 4. Payment Mode Selection
- 🔧 **Add:** Payment mode dropdown: "Cash" or "Online Payment"
- 🔧 **Add:** If "Cash" - proceed normally
- 🔧 **Add:** If "Online Payment" - show PayMongo checkout

### 5. PayMongo Integration
- 🔧 **Add:** PayMongo API credentials configuration
- 🔧 **Add:** Create payment link/checkout session
- 🔧 **Add:** Handle payment webhook/callback
- 🔧 **Add:** Verify payment status
- 🔧 **Add:** Update membership status after payment

### 6. Revenue Tracking
- 🔧 **Add:** Record payment in `financial_records` table
- 🔧 **Add:** Link payment to gym owner
- 🔧 **Add:** Show in gym owner dashboard revenue
- 🔧 **Add:** Payment history for members

## 🗄️ Database Changes Needed

### 1. Add payment tracking columns to membership_applications:

```sql
ALTER TABLE membership_applications
ADD COLUMN service_id INT NULL AFTER plan_id,
ADD COLUMN payment_mode ENUM('cash','online') DEFAULT 'cash' AFTER payment_amount,
ADD COLUMN payment_status ENUM('pending','paid','failed') DEFAULT 'pending' AFTER payment_mode,
ADD COLUMN payment_reference VARCHAR(255) NULL AFTER payment_status,
ADD COLUMN paymongo_payment_id VARCHAR(255) NULL AFTER payment_reference,
ADD COLUMN paid_at DATETIME NULL AFTER paymongo_payment_id,
ADD CONSTRAINT fk_membership_service FOREIGN KEY (service_id) REFERENCES gym_services(id) ON DELETE SET NULL;
```

### 2. Add PayMongo configuration table:

```sql
CREATE TABLE IF NOT EXISTS paymongo_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    public_key VARCHAR(255) NOT NULL,
    secret_key VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_paymongo_gym_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_gym_owner (gym_owner_id)
) ENGINE=InnoDB;
```

### 3. Add payment transactions table:

```sql
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_application_id INT NOT NULL,
    gym_owner_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_mode ENUM('cash','online') NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    paymongo_payment_id VARCHAR(255) NULL,
    paymongo_source_id VARCHAR(255) NULL,
    payment_reference VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_membership FOREIGN KEY (membership_application_id) REFERENCES membership_applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_gym_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_payment_status (payment_status),
    KEY idx_paymongo_id (paymongo_payment_id)
) ENGINE=InnoDB;
```

## 🔄 Implementation Flow

### Flow 1: Gym Owner Sets Up Services

```
1. Gym Owner logs in
   ↓
2. Goes to "Gym Services" (already exists)
   ↓
3. Adds services:
   - Regular Monthly - ₱700
   - Student Monthly - ₱600
   - With Personal Trainer - ₱1,500/session
   ↓
4. Services saved to gym_services table
```

### Flow 2: Customer Applies for Membership

```
1. Customer selects gym
   ↓
2. Fills membership form
   ↓
3. Selects service from dropdown (loaded from gym_services)
   ↓
4. If "With Personal Trainer" selected:
   - Shows trainer selection (will be assigned by admin)
   ↓
5. Selects payment mode:
   - Cash: Proceeds to verification
   - Online: Redirects to PayMongo
   ↓
6. If Online Payment:
   - PayMongo checkout opens
   - Customer pays
   - Webhook updates status
   ↓
7. Application submitted
```

### Flow 3: Admin Officer Reviews & Assigns Trainer

```
1. Admin Officer logs in
   ↓
2. Goes to "Membership Applications"
   ↓
3. Sees application with "With Personal Trainer"
   ↓
4. Clicks "Review"
   ↓
5. Sees list of available trainers (from employees table)
   ↓
6. Assigns trainer to member
   ↓
7. Verifies payment:
   - If Cash: Marks as paid manually
   - If Online: Already paid via PayMongo
   ↓
8. Approves membership
   ↓
9. Revenue recorded in financial_records
```

### Flow 4: Gym Owner Sees Revenue

```
1. Gym Owner logs in
   ↓
2. Dashboard shows:
   - Total Revenue (includes membership payments)
   - Recent Transactions
   - Revenue by Service
   ↓
3. Can view detailed revenue reports
```

## 📝 Files to Create/Modify

### New Files:
1. `app/models/PayMongoConfig.php` - PayMongo configuration model
2. `app/models/PaymentTransaction.php` - Payment tracking model
3. `app/controllers/PaymentController.php` - Handle payments
4. `app/views/payment/checkout.php` - Payment page
5. `app/views/payment/success.php` - Payment success page
6. `app/views/payment/failed.php` - Payment failed page
7. `public/webhook/paymongo.php` - PayMongo webhook handler

### Modified Files:
1. `app/views/membership/apply.php` - Add service selection, payment mode
2. `app/controllers/MembershipController.php` - Handle service selection
3. `app/views/admofficer/review.php` - Add trainer assignment
4. `app/controllers/AdmofficerController.php` - Handle trainer assignment
5. `app/views/dashboard/gymowner.php` - Show revenue from payments

## 🔧 Implementation Priority

### Phase 1: Service Selection (High Priority)
1. ✅ Gym services already exist
2. 🔧 Update membership form to load services dynamically
3. 🔧 Remove hardcoded plans
4. 🔧 Calculate amount based on selected service

### Phase 2: Trainer Assignment (High Priority)
1. 🔧 Add trainer selection to admin review page
2. 🔧 Load available trainers from employees table
3. 🔧 Assign trainer to member
4. 🔧 Mark trainer as unavailable

### Phase 3: Payment Mode (Medium Priority)
1. 🔧 Add payment mode selection (Cash/Online)
2. 🔧 If Cash - proceed normally
3. 🔧 If Online - prepare for PayMongo

### Phase 4: PayMongo Integration (Medium Priority)
1. 🔧 Add PayMongo configuration
2. 🔧 Create payment link
3. 🔧 Handle payment callback
4. 🔧 Update payment status

### Phase 5: Revenue Tracking (Low Priority)
1. 🔧 Record payments in financial_records
2. 🔧 Show in gym owner dashboard
3. 🔧 Payment history reports

## 💰 PayMongo API Integration

### Setup:
1. Get API keys from PayMongo dashboard
2. Store in `paymongo_config` table
3. Use secret key for API calls

### Create Payment:
```php
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paymongo.com/v1/links",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Basic " . base64_encode($secretKey . ":"),
        "content-type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "data" => [
            "attributes" => [
                "amount" => $amount * 100, // in centavos
                "description" => "Gym Membership - " . $serviceName,
                "remarks" => "membership_" . $applicationId
            ]
        ]
    ])
]);
$response = curl_exec($curl);
```

### Webhook Handler:
```php
// Verify webhook signature
// Update payment status
// Approve membership if paid
// Record revenue
```

## 🧪 Testing Checklist

- [ ] Gym owner can add services
- [ ] Services appear in membership form
- [ ] Customer can select service
- [ ] Amount calculates correctly
- [ ] Payment mode selection works
- [ ] Cash payment proceeds normally
- [ ] Online payment redirects to PayMongo
- [ ] PayMongo payment completes
- [ ] Webhook updates status
- [ ] Admin can assign trainer
- [ ] Trainer becomes unavailable
- [ ] Revenue appears in gym owner dashboard
- [ ] Payment history is accurate

## 📊 Estimated Time

- Phase 1 (Service Selection): 2-3 hours
- Phase 2 (Trainer Assignment): 2-3 hours
- Phase 3 (Payment Mode): 1-2 hours
- Phase 4 (PayMongo Integration): 4-6 hours
- Phase 5 (Revenue Tracking): 2-3 hours

**Total: 11-17 hours**

## 🚀 Let's Start!

Would you like me to:
1. Start with Phase 1 (Service Selection)?
2. Create all database migrations first?
3. Focus on a specific part?

Let me know and I'll begin implementation!
