# Staff Application System - Database Migration

## IMPORTANT: Run This SQL First!

Before testing the staff application system, you need to add the `gym_owner_id` column to the existing `staff_applications` table.

## Option 1: Using phpMyAdmin (Recommended)

1. Open phpMyAdmin in your browser (usually http://localhost/phpmyadmin)
2. Select the `webdev` database from the left sidebar
3. Click on the "SQL" tab at the top
4. Copy and paste the SQL below:

```sql
-- Add gym_owner_id column to staff_applications table
ALTER TABLE staff_applications 
ADD COLUMN gym_owner_id INT NULL AFTER user_id;

-- Add index for better query performance
ALTER TABLE staff_applications
ADD KEY idx_staff_app_gym_owner (gym_owner_id);

-- Add foreign key constraint
ALTER TABLE staff_applications
ADD CONSTRAINT fk_staff_app_gym_owner 
    FOREIGN KEY (gym_owner_id) REFERENCES users(id) 
    ON DELETE CASCADE;
```

5. Click "Go" to execute
6. You should see a success message

## Option 2: Using MySQL Command Line

1. Open your terminal/command prompt
2. Connect to MySQL:
   ```bash
   mysql -u root -p
   ```
3. Enter your MySQL password
4. Select the database:
   ```sql
   USE webdev;
   ```
5. Run the migration:
   ```sql
   ALTER TABLE staff_applications 
   ADD COLUMN gym_owner_id INT NULL AFTER user_id,
   ADD KEY idx_staff_app_gym_owner (gym_owner_id),
   ADD CONSTRAINT fk_staff_app_gym_owner 
       FOREIGN KEY (gym_owner_id) REFERENCES users(id) 
       ON DELETE CASCADE;
   ```

## Option 3: Using the SQL File

You can also run the prepared SQL file:

```bash
mysql -u root -p webdev < sql/add_gym_to_staff_applications.sql
```

## Verify the Migration

After running the migration, verify it worked:

```sql
DESCRIBE staff_applications;
```

You should see `gym_owner_id` in the column list.

## What This Does

- Adds a `gym_owner_id` column to link staff applications to specific gyms
- Creates an index for faster queries
- Adds a foreign key constraint to maintain data integrity
- Allows CASCADE deletion (if a gym owner is deleted, their applications are too)

## Next Steps

After running the migration:

1. Make sure you have at least one gym owner with verified legal documents
2. Log in as a customer
3. Go to "Apply as Staff" from the dashboard
4. You should see the list of available gyms
5. Apply to a gym
6. Log in as the gym owner to review the application

## Troubleshooting

**Error: Column 'gym_owner_id' already exists**
- The migration has already been run. You're good to go!

**Error: Cannot add foreign key constraint**
- Make sure the `users` table exists
- Check that there are no invalid values in existing `staff_applications` records

**Error: Table 'staff_applications' doesn't exist**
- Run the full database schema first from `webdev.sql` or `sql/database.sql`
