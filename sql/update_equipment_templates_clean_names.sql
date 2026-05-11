-- Update Equipment Templates with Clean Names
-- This script updates the equipment templates to have clean names without weight specifications

-- First, clear existing global templates to avoid conflicts
DELETE FROM equipment_templates WHERE is_global = 1;

-- Insert clean equipment templates
INSERT INTO equipment_templates (equipment_name, brand, dimensions, weight_kg, category, is_global) VALUES
-- Cardio Equipment
('Treadmill', 'NordicTrack', '200x90x150 cm', 120.00, 'Cardio', 1),
('Treadmill', 'Life Fitness', '210x85x160 cm', 140.00, 'Cardio', 1),
('Elliptical Trainer', 'Precor', '180x70x170 cm', 85.00, 'Cardio', 1),
('Stationary Bike', 'Schwinn', '120x60x110 cm', 45.00, 'Cardio', 1),
('Rowing Machine', 'Concept2', '244x61x36 cm', 26.00, 'Cardio', 1),

-- Weight Equipment
('Olympic Barbell', 'Rogue', '220 cm', 20.00, 'Weights', 1),
('Olympic Barbell', 'Eleiko', '220 cm', 20.00, 'Weights', 1),
('Power Rack', 'Rogue', '137x137x218 cm', 180.00, 'Racks', 1),
('Squat Rack', 'Rep Fitness', '122x122x218 cm', 120.00, 'Racks', 1),
('Adjustable Bench', 'Rep Fitness', '140x60x45 cm', 35.00, 'Benches', 1),
('Flat Bench', 'Rogue', '122x35x43 cm', 25.00, 'Benches', 1),

-- Dumbbells (Clean names without weight)
('Adjustable Dumbbells', 'PowerBlocks', '30x15x15 cm', 24.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '25x12x12 cm', 5.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '30x15x15 cm', 10.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '35x18x18 cm', 15.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '40x20x20 cm', 20.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '45x22x22 cm', 25.00, 'Weights', 1),
('Hex Dumbbells', 'CAP Barbell', '50x25x25 cm', 30.00, 'Weights', 1),

-- Plates (Clean names without weight)
('Olympic Plate', 'Rogue', '45cm diameter', 1.25, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 2.50, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 5.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 10.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 15.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 20.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 25.00, 'Weights', 1),

-- Kettlebells (Clean names without weight)
('Kettlebell', 'Kettlebell Kings', '20x20x25 cm', 4.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '22x22x27 cm', 6.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '25x25x30 cm', 8.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '27x27x32 cm', 10.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '28x28x32 cm', 12.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '30x30x35 cm', 16.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '32x32x37 cm', 20.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '35x35x40 cm', 24.00, 'Weights', 1),

-- Medicine Balls (Clean names without weight)
('Medicine Ball', 'Dynamax', '25cm diameter', 2.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '30cm diameter', 3.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 5.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 6.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 8.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 10.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '40cm diameter', 12.00, 'Accessories', 1),

-- Machines
('Lat Pulldown Machine', 'Life Fitness', '150x120x200 cm', 200.00, 'Machines', 1),
('Cable Crossover', 'Cybex', '300x150x220 cm', 350.00, 'Machines', 1),
('Leg Press Machine', 'Hammer Strength', '200x150x140 cm', 280.00, 'Machines', 1),
('Chest Press Machine', 'Technogym', '160x120x150 cm', 180.00, 'Machines', 1),
('Shoulder Press Machine', 'Technogym', '140x120x180 cm', 160.00, 'Machines', 1),
('Leg Curl Machine', 'Life Fitness', '180x100x140 cm', 150.00, 'Machines', 1),
('Leg Extension Machine', 'Life Fitness', '160x80x120 cm', 140.00, 'Machines', 1),

-- Accessories
('Yoga Mat', 'Manduka', '183x61x0.6 cm', 2.50, 'Accessories', 1),
('Resistance Bands Set', 'Bodylastics', '120 cm', 1.00, 'Accessories', 1),
('Pull-up Bar', 'Iron Gym', '100 cm', 3.00, 'Accessories', 1),
('Exercise Ball', 'Gaiam', '65cm diameter', 1.50, 'Accessories', 1),
('Foam Roller', 'TriggerPoint', '33x14 cm', 0.70, 'Accessories', 1),
('Jump Rope', 'Crossrope', '3m length', 0.50, 'Accessories', 1),
('Weight Belt', 'Harbinger', '15cm width', 0.80, 'Accessories', 1),
('Lifting Straps', 'Versa Gripps', '60cm length', 0.30, 'Accessories', 1);

-- Update any existing user templates to clean names (preserve user data)
UPDATE equipment_templates SET equipment_name = 'Hex Dumbbells' WHERE equipment_name LIKE 'Hex Dumbbells %kg' AND is_global = 0;
UPDATE equipment_templates SET equipment_name = 'Olympic Plate' WHERE equipment_name LIKE 'Olympic Plate %kg' AND is_global = 0;
UPDATE equipment_templates SET equipment_name = 'Kettlebell' WHERE equipment_name LIKE 'Kettlebell %kg' AND is_global = 0;
UPDATE equipment_templates SET equipment_name = 'Medicine Ball' WHERE equipment_name LIKE 'Medicine Ball %kg' AND is_global = 0;

SELECT 'Equipment templates updated with clean names! Now you will see:' as status;
SELECT CONCAT('- ', equipment_name, ' (', COUNT(*), ' variants)') as clean_names 
FROM equipment_templates 
WHERE is_global = 1 
GROUP BY equipment_name 
ORDER BY equipment_name;