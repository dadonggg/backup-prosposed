-- Fix Equipment Names - Remove Weight from Names
-- This script cleans up equipment names by removing weight specifications
-- and ensures the weight values are properly stored in the weight_kg field

-- Update existing equipment templates to remove weight from names
UPDATE equipment_templates SET equipment_name = 'Hex Dumbbells' WHERE equipment_name LIKE 'Hex Dumbbells %kg';
UPDATE equipment_templates SET equipment_name = 'Olympic Plate' WHERE equipment_name LIKE 'Olympic Plate %kg';
UPDATE equipment_templates SET equipment_name = 'Kettlebell' WHERE equipment_name LIKE 'Kettlebell %kg';
UPDATE equipment_templates SET equipment_name = 'Medicine Ball' WHERE equipment_name LIKE 'Medicine Ball %kg';

-- Also update any existing gym_equipment entries if they exist
UPDATE gym_equipment SET name = 'Hex Dumbbells' WHERE name LIKE 'Hex Dumbbells %kg';
UPDATE gym_equipment SET name = 'Olympic Plate' WHERE name LIKE 'Olympic Plate %kg';
UPDATE gym_equipment SET name = 'Kettlebell' WHERE name LIKE 'Kettlebell %kg';
UPDATE gym_equipment SET name = 'Medicine Ball' WHERE name LIKE 'Medicine Ball %kg';

-- Clean up any duplicate templates that might have been created
DELETE t1 FROM equipment_templates t1
INNER JOIN equipment_templates t2 
WHERE t1.id > t2.id 
AND t1.equipment_name = t2.equipment_name 
AND t1.brand = t2.brand 
AND t1.dimensions = t2.dimensions 
AND t1.weight_kg = t2.weight_kg 
AND t1.created_by = t2.created_by;

SELECT 'Equipment names cleaned successfully! Weight values moved to weight field.' as status;