# How to Run the SQL to Fix Document Status

## Quick Steps

### Method 1: Using phpMyAdmin (Recommended) ⭐

1. **Open phpMyAdmin**
   - Go to: `http://localhost/phpmyadmin`

2. **Select Database**
   - Click on `webdev` database in the left sidebar

3. **Open SQL Tab**
   - Click the "SQL" tab at the top

4. **Copy and Paste SQL**
   - Open the file: `add_document_status_columns_simple.sql`
   - Copy ALL the SQL code
   - Paste it into the SQL query box in phpMyAdmin

5. **Execute**
   - Click the "Go" button at the bottom right
   - Wait for success message

6. **Verify**
   - You should see: "12 rows affected" or similar
   - This means 12 columns were added (3 columns × 4 documents)

### Method 2: Using the PHP Migration Tool

1. Open your browser
2. Go to: `http://localhost/webdev/run_migration.php`
3. Click "Run Migration Now"
4. Done!

## What Gets Added

For each of the 4 documents, these 3 columns are added:

### Certificate of Registration
- `cert_registration_status` - Can be: pending, approved, or flagged
- `cert_registration_comment` - Admin's comment/feedback
- `cert_registration_checked` - Whether admin reviewed it (0 or 1)

### Mayor's Permit
- `mayors_permit_status`
- `mayors_permit_comment`
- `mayors_permit_checked`

### Business Name Certificate
- `business_name_cert_status`
- `business_name_cert_comment`
- `business_name_cert_checked`

### Fire Safety Certificate
- `fire_safety_cert_status`
- `fire_safety_cert_comment`
- `fire_safety_cert_checked`

**Total: 12 new columns**

## After Running SQL

1. ✅ Columns are now in the database
2. 🔄 Admin needs to review documents again
3. 👁️ Gym owner will see correct statuses (Approved/Flagged/Pending)
4. 💬 Comments will appear on flagged documents

## Troubleshooting

### Error: "Duplicate column name"
- **This is OK!** It means the column already exists
- Just skip that statement and continue

### Error: "Table 'legal_documents' doesn't exist"
- Make sure you selected the `webdev` database
- Check if the table name is correct

### Error: "Access denied"
- Make sure you're logged in as `root` or a user with ALTER privileges

## Verify It Worked

After running the SQL, check if it worked:

1. In phpMyAdmin, click on `legal_documents` table
2. Click "Structure" tab
3. You should see the new columns listed

OR

Visit: `http://localhost/webdev/check_status.php`

## Next Steps

1. ✅ Run the SQL
2. 🔄 Admin goes to review page
3. 👆 Admin clicks Approve/Flag on each document
4. 🎉 Gym owner sees the correct statuses!
