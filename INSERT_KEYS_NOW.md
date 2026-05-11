# 🔑 INSERT YOUR PAYMONGO KEYS NOW!

## ⚡ FASTEST METHOD (30 seconds)

### Step 1: Open phpMyAdmin
- Go to your phpMyAdmin
- Select database: **webdev**
- Click **SQL** tab

### Step 2: Copy & Paste This SQL

```sql
-- Insert your PayMongo keys for the first gym owner
INSERT INTO paymongo_config (gym_owner_id, public_key, secret_key, is_active)
SELECT 
    id,
    'pk_test_YOUR_PUBLIC_KEY',
    'sk_test_YOUR_SECRET_KEY',
    1
FROM users 
WHERE role = 'gym_owner' 
LIMIT 1
ON DUPLICATE KEY UPDATE 
    public_key = 'pk_test_YOUR_PUBLIC_KEY',
    secret_key = 'sk_test_YOUR_SECRET_KEY',
    is_active = 1,
    updated_at = NOW();
```

### Step 3: Click "Go"

### Step 4: Verify

```sql
-- Check if keys were inserted
SELECT 
    u.fullname,
    pc.public_key,
    CONCAT('****', RIGHT(pc.secret_key, 4)) as secret_key_masked,
    CASE WHEN pc.is_active = 1 THEN '✓ ACTIVE' ELSE 'INACTIVE' END as status
FROM paymongo_config pc
JOIN users u ON u.id = pc.gym_owner_id;
```

**Expected Result:**
```
fullname        | public_key                        | secret_key_masked | status
----------------|-----------------------------------|-------------------|----------
Your Name       | pk_test_YOUR_PUBLIC_KEY | ****NzzH         | ✓ ACTIVE
```

---

## ✅ DONE!

Your PayMongo keys are now configured!

### What You Can Do Now:

1. **View Configuration:**
   - Login as gym owner
   - Go to: `index.php?r=gymowner/paymongo`
   - You'll see your keys (secret key is masked)

2. **Test in Application:**
   - Customers can now select "Online Payment (PayMongo)"
   - Payment processing will be implemented in Phase 3

3. **Manage Keys:**
   - Enable/Disable PayMongo anytime
   - Update keys via web interface
   - Delete configuration if needed

---

## 🎯 Your Keys

**Public Key:** `pk_test_YOUR_PUBLIC_KEY`  
**Secret Key:** `sk_test_YOUR_SECRET_KEY`  
**Mode:** Test (safe for development)  
**Status:** ✅ Ready to use

---

## 📱 Access PayMongo Configuration

**URL:** `index.php?r=gymowner/paymongo`

**Or from Dashboard:**
- Login as gym owner
- Look for "PayMongo Setup" card (blue card with credit card icon)
- Click "Configure PayMongo"

---

## 🔒 Security

✅ These are **test keys** - safe to use for development  
✅ No real money will be processed  
✅ Secret key is masked in the UI  
✅ Only gym owner can view/edit keys  

⚠️ **Switch to live keys only when ready for production!**

---

**Need more details?** Read `PAYMONGO_SETUP_GUIDE.md`
