# 🔑 How to Insert PayMongo Keys in PHP

## ✅ EASIEST METHOD: Run the PHP Script

I created a file called **`insert_paymongo_keys.php`** that will automatically insert your keys.

### Step-by-Step:

1. **The file is already created** in your project root: `insert_paymongo_keys.php`

2. **Open your browser** and go to:
   ```
   http://localhost/your-project-folder/insert_paymongo_keys.php
   ```
   (Replace `your-project-folder` with your actual folder name)

3. **The page will automatically:**
   - ✅ Connect to your database
   - ✅ Find your gym owner account
   - ✅ Insert your PayMongo keys
   - ✅ Show you a success message

4. **After it succeeds, DELETE the file** for security:
   - Delete `insert_paymongo_keys.php` from your project

5. **Done!** Your keys are now in the database

---

## 📝 What's Inside the PHP File

The file contains your keys hardcoded:

```php
// Your PayMongo API Keys
define('PAYMONGO_PUBLIC_KEY', 'pk_test_YOUR_PUBLIC_KEY');
define('PAYMONGO_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');
```

And it automatically:
1. Connects to your database
2. Finds your gym owner user
3. Inserts the keys into `paymongo_config` table
4. Shows you a success message

---

## 🎯 Alternative: Hardcode in Config File

If you want to store keys in a config file instead of database, create this file:

### Create: `app/config/paymongo.php`

```php
<?php
return [
    'public_key' => 'pk_test_YOUR_PUBLIC_KEY',
    'secret_key' => 'sk_test_YOUR_SECRET_KEY',
    'is_active' => true,
    'mode' => 'test', // 'test' or 'live'
];
```

Then use it in your code:
```php
$paymongoConfig = require BASE_PATH . '/app/config/paymongo.php';
$publicKey = $paymongoConfig['public_key'];
$secretKey = $paymongoConfig['secret_key'];
```

---

## 🔍 Verify It Worked

### Method 1: Check in phpMyAdmin
```sql
SELECT * FROM paymongo_config;
```

### Method 2: Check in your website
1. Login as gym owner
2. Go to: `index.php?r=gymowner/paymongo`
3. You should see your keys displayed

---

## ⚠️ Important Notes

1. **Use the PHP script method** - it's the easiest and safest
2. **Delete `insert_paymongo_keys.php`** after running it
3. **Never commit API keys to Git** - add to `.gitignore`
4. **These are test keys** - safe for development

---

## 🚀 Quick Summary

**Fastest Way:**
1. Open browser
2. Go to: `http://localhost/your-project/insert_paymongo_keys.php`
3. See success message
4. Delete the file
5. Done! ✅

**Your keys are now in the database and ready to use!**
