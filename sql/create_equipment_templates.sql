-- Equipment Templates System for Smart Dropdowns
-- This table stores equipment templates that auto-populate brand, dimensions, and weight
-- when gym owners select an equipment name

CREATE TABLE IF NOT EXISTS equipment_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(255) NOT NULL,
    brand VARCHAR(255) DEFAULT NULL,
    dimensions VARCHAR(255) DEFAULT NULL,
    weight_kg DECIMAL(8,2) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    is_global TINYINT(1) DEFAULT 0 COMMENT '1 = available to all gym owners, 0 = only to creator',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_equipment_name (equipment_name),
    INDEX idx_created_by (created_by),
    INDEX idx_global (is_global),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some common equipment templates for all gym owners
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

-- Plates (Clean names without weight)
('Olympic Plate', 'Rogue', '45cm diameter', 2.50, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 5.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 10.00, 'Weights', 1),
('Olympic Plate', 'Rogue', '45cm diameter', 20.00, 'Weights', 1),

-- Machines
('Lat Pulldown Machine', 'Life Fitness', '150x120x200 cm', 200.00, 'Machines', 1),
('Cable Crossover', 'Cybex', '300x150x220 cm', 350.00, 'Machines', 1),
('Leg Press Machine', 'Hammer Strength', '200x150x140 cm', 280.00, 'Machines', 1),
('Chest Press Machine', 'Technogym', '160x120x150 cm', 180.00, 'Machines', 1),

-- Accessories
('Yoga Mat', 'Manduka', '183x61x0.6 cm', 2.50, 'Accessories', 1),
('Resistance Bands Set', 'Bodylastics', '120 cm', 1.00, 'Accessories', 1),
('Kettlebell', 'Kettlebell Kings', '25x25x30 cm', 8.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '28x28x32 cm', 12.00, 'Weights', 1),
('Kettlebell', 'Kettlebell Kings', '30x30x35 cm', 16.00, 'Weights', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 5.00, 'Accessories', 1),
('Medicine Ball', 'Dynamax', '35cm diameter', 10.00, 'Accessories', 1);

-- Add some sample gym owner specific templates (these would be created by individual gym owners)
-- Note: You'll need to replace the created_by values with actual gym owner user IDs from your database
-- INSERT INTO equipment_templates (equipment_name, brand, dimensions, weight_kg, category, created_by, is_global) VALUES
-- ('Custom Barbell', 'Local Brand', '200 cm', 18.00, 'Weights', 3, 0),
-- ('Custom Bench', 'Local Manufacturer', '120x50x40 cm', 30.00, 'Benches', 3, 0);

SELECT 'Equipment templates table created successfully!' as status;