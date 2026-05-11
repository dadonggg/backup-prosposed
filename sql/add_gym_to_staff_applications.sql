-- Add gym_owner_id to staff_applications table
-- This allows customers to apply to specific gyms

ALTER TABLE staff_applications 
ADD COLUMN gym_owner_id INT NULL AFTER user_id,
ADD KEY idx_staff_app_gym_owner (gym_owner_id),
ADD CONSTRAINT fk_staff_app_gym_owner 
    FOREIGN KEY (gym_owner_id) REFERENCES users(id) 
    ON DELETE CASCADE;
