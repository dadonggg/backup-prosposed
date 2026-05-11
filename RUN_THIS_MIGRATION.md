# IMPORTANT: Database Migration Required!

## Problem
The document status columns (cert_registration_status, mayors_permit_status, etc.) don't exist in your database yet. That's why the admin's flag/approve actions aren't being saved.

## Solution
Run the migration file to add the required columns.

### Option 1: Using phpMyAdmin (Recommended)
1. Open phpMyAdmin in your browser (usually http://localhost/phpmyadmin)
2. Select the `webdev` database from the left sidebar
3. Click on the "Import" tab at the top
4. Click "Choose File" and select: `sql/migration_v2_updates.sql`
5. Click "Go" at the bottom
6. Wait for success message

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p webdev < sql/migration_v2_updates.sql
```
(Press Enter when prompted for password if you don't have one)

### Option 3: Copy-Paste SQL
1. Open phpMyAdmin
2. Select `webdev` database
3. Click "SQL" tab
4. Open the file `sql/migration_v2_updates.sql` in a text editor
5. Copy ALL the SQL code
6. Paste it into the SQL query box
7. Click "Go"

## After Running Migration
1. Refresh your browser
2. Admin can now flag/approve documents
3. Gym owner will see the status changes immediately
4. Comments will be saved and displayed

## Verify It Worked
After running the migration, you can verify by:
1. Going to phpMyAdmin
2. Selecting `webdev` database
3. Clicking on `legal_documents` table
4. Clicking "Structure" tab
5. You should see columns like:
   - cert_registration_status
   - cert_registration_comment
   - cert_registration_checked
   - (and similar for other documents)

## Troubleshooting
If you get an error like "Column already exists", that's OK - it means some columns were already added. The migration is safe to run multiple times.

If you get other errors, please share the error message.
