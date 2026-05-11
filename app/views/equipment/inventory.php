<?php
declare(strict_types=1);
$pageTitle = 'Equipment Inventory';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-box-seam me-2"></i>Equipment Inventory</h1>
    <p class="text-muted">List and manage your gym equipment. Add items with details and images.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- ─── Extract Distinct Values for Datalists ─── -->
<?php
$distinctNames = array_unique(array_column($inventory ?? [], 'name'));
$distinctBrands = array_filter(array_unique(array_column($inventory ?? [], 'brand')));
$distinctDims = array_filter(array_unique(array_column($inventory ?? [], 'dimensions')));
$distinctWeights = array_filter(array_unique(array_column($inventory ?? [], 'weight_kg')));
?>

<!-- ─── List New Equipment Form ─── -->
<div class="card mb-4">
    <div class="card-header px-3 py-2">
        <h2 class="h6 mb-0"><i class="bi bi-plus-circle me-1"></i>List New Equipment</h2>
        <small class="text-muted">Smart dropdowns will auto-populate based on equipment name selection</small>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="vstack gap-3">
            <input type="hidden" name="action" value="list_equipment">
            
            <!-- Equipment Templates Data for JavaScript -->
            <script>
                window.equipmentTemplates = <?= $equipmentTemplates ?? '{}' ?>;
            </script>
            
            <datalist id="eq_names_list">
                <?php foreach ($distinctNames as $n): ?><option value="<?= htmlspecialchars($n) ?>"><?php endforeach; ?>
            </datalist>
            <datalist id="eq_brands_list">
                <?php foreach ($distinctBrands as $b): ?><option value="<?= htmlspecialchars($b) ?>"><?php endforeach; ?>
            </datalist>
            <datalist id="eq_dims_list">
                <?php foreach ($distinctDims as $d): ?><option value="<?= htmlspecialchars($d) ?>"><?php endforeach; ?>
            </datalist>
            <datalist id="eq_weights_list">
                <?php foreach ($distinctWeights as $w): ?><option value="<?= htmlspecialchars($w) ?>"><?php endforeach; ?>
            </datalist>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="eq_name">Equipment Name <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <select class="form-select" id="eq_name_select" name="eq_name_select">
                            <option value="">-- Select Equipment --</option>
                            <?php 
                            // Use the unique equipment names passed from controller
                            foreach ($uniqueEquipmentNames as $name): 
                            ?>
                                <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                            <option value="__ADD_NEW__" style="background-color: #e3f2fd; font-weight: bold; color: #1976d2;">➕ Add New Equipment Name</option>
                        </select>
                        <div class="d-none" id="eq_name_input_group">
                            <div class="input-group">
                                <input class="form-control" id="eq_name" type="text" name="eq_name" placeholder="Enter new equipment name..." autocomplete="off" required>
                                <button class="btn btn-outline-secondary" type="button" id="back_to_dropdown" title="Back to dropdown">
                                    <i class="bi bi-arrow-left"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">Select from dropdown or add new equipment name</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="eq_category">Category</label>
                    <select class="form-select" id="eq_category" name="eq_category">
                        <option value="Weights">Weights</option>
                        <option value="Benches">Benches</option>
                        <option value="Racks">Racks</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Machines">Machines</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="eq_brand">Brand</label>
                    <div class="position-relative">
                        <input class="form-control" id="eq_brand" type="text" name="eq_brand" list="eq_brands_list" placeholder="e.g. IronWorks" autocomplete="off">
                        <select class="form-select d-none" id="eq_brand_select" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 10;">
                            <option value="">Select brand...</option>
                        </select>
                    </div>
                    <div class="form-text">Auto-populated from template</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="eq_dimensions">Dimensions</label>
                    <div class="position-relative">
                        <input class="form-control" id="eq_dimensions" type="text" name="eq_dimensions" list="eq_dims_list" placeholder="e.g. 120x60x45 cm" autocomplete="off">
                        <select class="form-select d-none" id="eq_dimensions_select" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 10;">
                            <option value="">Select dimensions...</option>
                        </select>
                    </div>
                    <div class="form-text">Auto-populated from template</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="eq_weight">Weight</label>
                    <div class="position-relative">
                        <div class="input-group">
                            <input class="form-control" id="eq_weight" type="number" name="eq_weight" step="0.01" min="0" list="eq_weights_list" placeholder="e.g. 20" autocomplete="off">
                            <span class="input-group-text">kg</span>
                        </div>
                        <select class="form-select d-none" id="eq_weight_select" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 10;">
                            <option value="">Select weight...</option>
                        </select>
                    </div>
                    <div class="form-text">Auto-populated from template</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="eq_quantity">Quantity <span class="text-danger">*</span></label>
                    <input class="form-control" id="eq_quantity" type="number" name="eq_quantity" min="1" value="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="eq_price">Price / Value (₱)</label>
                    <input class="form-control" id="eq_price" type="number" name="eq_price" step="0.01" min="0" value="0">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="eq_description">Description</label>
                    <textarea class="form-control" id="eq_description" name="eq_description" rows="2" placeholder="Brief description of the equipment..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="eq_image">Equipment Image</label>
                    <input class="form-control" id="eq_image" type="file" name="eq_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">JPG, PNG, GIF, WEBP — Optional</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="save_as_template" name="save_as_template" value="1">
                        <label class="form-check-label" for="save_as_template">
                            <i class="bi bi-bookmark-plus me-1"></i>Save as template for future use
                        </label>
                        <div class="form-text">Save this equipment configuration so you can quickly add it again later</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>List Equipment</button>
                    <button class="btn btn-outline-secondary ms-2" type="button" id="clear_form"><i class="bi bi-arrow-clockwise me-1"></i>Clear Form</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ─── Inventory Grid ─── -->
<h2 class="h5 mb-3"><i class="bi bi-grid me-1"></i>Your Inventory (<?= count($inventory) ?> items)</h2>
<?php
$totalInventoryValue = 0;
foreach ($inventory as $item) { $totalInventoryValue += (int)$item['quantity'] * (float)$item['price']; }
if ($totalInventoryValue > 0): ?>
<div class="alert alert-info mb-3"><i class="bi bi-calculator me-1"></i> Total Inventory Value: <strong>₱<?= number_format($totalInventoryValue, 2) ?></strong> <span class="text-muted">(sum of qty × price for all items)</span></div>
<?php endif; ?>

<?php if (empty($inventory)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-box-seam" style="font-size:2.5rem;opacity:.4"></i>
            <p class="mt-2">No equipment listed yet. Use the form above to add your first item.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4" id="inventory-grid">
        <?php foreach ($inventory as $item): ?>
        <div class="col-md-6 col-lg-4 inventory-item" data-item-id="<?= $item['id'] ?>">
            <div class="card h-100">
                <?php if (!empty($item['image_path'])): ?>
                    <div style="height:180px;overflow:hidden;border-radius:12px 12px 0 0;background:#f0f4f0">
                        <img src="public/<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                             style="width:100%;height:100%;object-fit:cover">
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="height:180px;background:rgba(27,107,42,.05);border-radius:12px 12px 0 0">
                        <i class="bi bi-image" style="font-size:3rem;color:rgba(27,107,42,.2)"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h3>
                    <div class="d-flex gap-1 flex-wrap mb-2">
                        <span class="badge bg-info"><?= htmlspecialchars($item['category'] ?? 'General') ?></span>
                        <?php if (!empty($item['brand'])): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($item['brand']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($item['description'])): ?>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($item['description']) ?></p>
                    <?php endif; ?>
                    <div class="small mb-1">
                        <?php if (!empty($item['dimensions'])): ?>
                            <span class="text-muted"><i class="bi bi-rulers me-1"></i><?= htmlspecialchars($item['dimensions']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['weight_kg'])): ?>
                            <span class="text-muted ms-2"><i class="bi bi-speedometer me-1"></i><?= number_format((float)$item['weight_kg'], 2) ?> kg</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2">
                        <span class="fw-bold" style="color:#1B6B2A">Qty: <?= (int)$item['quantity'] ?></span>
                        <?php if ((float)$item['price'] > 0): ?>
                            <span class="ms-2 small text-muted">@ ₱<?= number_format((float)$item['price'], 2) ?> each</span>
                        <?php endif; ?>
                    </div>
                    <?php if ((float)$item['price'] > 0): ?>
                    <div class="mb-2">
                        <span class="badge bg-success"><i class="bi bi-calculator me-1"></i>Total: ₱<?= number_format((int)$item['quantity'] * (float)$item['price'], 2) ?></span>
                        <span class="small text-muted ms-1"><?= (int)$item['quantity'] ?> × ₱<?= number_format((float)$item['price'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted" style="font-size:.7rem">Listed: <?= htmlspecialchars($item['created_at']) ?></div>
                        <form method="post" style="display:inline" class="delete-equipment-form" data-item-id="<?= $item['id'] ?>">
                            <input type="hidden" name="action" value="delete_equipment">
                            <input type="hidden" name="eq_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this equipment?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Equipment Templates Smart Dropdown System
    const equipmentTemplates = window.equipmentTemplates || {};
    
    const nameSelect = document.getElementById('eq_name_select');
    const nameInput = document.getElementById('eq_name');
    const nameInputGroup = document.getElementById('eq_name_input_group');
    const backToDropdownBtn = document.getElementById('back_to_dropdown');
    const brandInput = document.getElementById('eq_brand');
    const brandSelect = document.getElementById('eq_brand_select');
    const dimensionsInput = document.getElementById('eq_dimensions');
    const dimensionsSelect = document.getElementById('eq_dimensions_select');
    const weightInput = document.getElementById('eq_weight');
    const weightSelect = document.getElementById('eq_weight_select');
    const categorySelect = document.getElementById('eq_category');
    const clearFormBtn = document.getElementById('clear_form');
    
    // Function to populate dropdown options
    function populateDropdown(selectElement, inputElement, options, valueKey) {
        selectElement.innerHTML = '<option value="">Select ' + valueKey + '...</option>';
        
        if (options && options.length > 0) {
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option[valueKey] || '';
                optionElement.textContent = option[valueKey] || 'N/A';
                optionElement.dataset.template = JSON.stringify(option);
                selectElement.appendChild(optionElement);
            });
            
            // Show dropdown, hide input
            selectElement.classList.remove('d-none');
            inputElement.classList.add('d-none');
        } else {
            // Hide dropdown, show input
            selectElement.classList.add('d-none');
            inputElement.classList.remove('d-none');
        }
    }
    
    // Function to apply template data
    function applyTemplate(template) {
        if (template.brand) brandInput.value = template.brand;
        if (template.dimensions) dimensionsInput.value = template.dimensions;
        if (template.weight_kg) weightInput.value = template.weight_kg;
        if (template.category) categorySelect.value = template.category;
    }
    
    // Function to clear all dependent fields
    function clearDependentFields() {
        brandInput.value = '';
        dimensionsInput.value = '';
        weightInput.value = '';
        
        // Hide all dropdowns, show inputs
        brandSelect.classList.add('d-none');
        brandInput.classList.remove('d-none');
        dimensionsSelect.classList.add('d-none');
        dimensionsInput.classList.remove('d-none');
        weightSelect.classList.add('d-none');
        weightInput.classList.remove('d-none');
    }
    
    // Equipment name dropdown change handler
    nameSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        if (selectedValue === '__ADD_NEW__') {
            // Show input field for new equipment name
            this.classList.add('d-none');
            nameInputGroup.classList.remove('d-none');
            nameInput.focus();
            nameInput.value = '';
            
            // Clear all dependent fields
            clearDependentFields();
        } else if (selectedValue === '') {
            // No selection - clear dependent fields
            clearDependentFields();
        } else {
            // Existing equipment selected - populate templates
            nameInput.value = selectedValue;
            populateTemplatesForEquipment(selectedValue);
        }
    });
    
    // Back to dropdown button handler
    backToDropdownBtn.addEventListener('click', function() {
        nameInputGroup.classList.add('d-none');
        nameSelect.classList.remove('d-none');
        nameSelect.value = '';
        nameInput.value = '';
        clearDependentFields();
    });
    
    // Equipment name input change handler (for new equipment names)
    nameInput.addEventListener('input', function() {
        const equipmentName = this.value.trim();
        
        if (equipmentName && equipmentTemplates[equipmentName]) {
            populateTemplatesForEquipment(equipmentName);
        } else {
            clearDependentFields();
        }
    });
    
    // Function to populate templates for selected equipment
    function populateTemplatesForEquipment(equipmentName) {
        if (equipmentTemplates[equipmentName]) {
            const templates = equipmentTemplates[equipmentName];
            
            // If only one template, auto-fill everything
            if (templates.length === 1) {
                applyTemplate(templates[0]);
                clearDependentFields(); // Still show inputs for editing
            } else {
                // Multiple templates - show dropdowns
                populateDropdown(brandSelect, brandInput, templates, 'brand');
                
                // Clear other fields until brand is selected
                dimensionsInput.value = '';
                weightInput.value = '';
                dimensionsSelect.classList.add('d-none');
                dimensionsInput.classList.remove('d-none');
                weightSelect.classList.add('d-none');
                weightInput.classList.remove('d-none');
            }
            
            // Set category from first template if available
            if (templates[0] && templates[0].category) {
                categorySelect.value = templates[0].category;
            }
        } else {
            clearDependentFields();
        }
    }
    
    // Brand selection handler
    brandSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.dataset.template) {
            const template = JSON.parse(selectedOption.dataset.template);
            brandInput.value = template.brand || '';
            
            // Get all templates for this equipment name with the selected brand
            const equipmentName = nameInput.value.trim();
            if (equipmentName && equipmentTemplates[equipmentName]) {
                const matchingTemplates = equipmentTemplates[equipmentName].filter(t => t.brand === template.brand);
                
                if (matchingTemplates.length === 1) {
                    // Only one template with this brand - auto-fill everything
                    applyTemplate(matchingTemplates[0]);
                    dimensionsSelect.classList.add('d-none');
                    dimensionsInput.classList.remove('d-none');
                    weightSelect.classList.add('d-none');
                    weightInput.classList.remove('d-none');
                } else {
                    // Multiple templates with same brand - show dimension dropdown
                    populateDropdown(dimensionsSelect, dimensionsInput, matchingTemplates, 'dimensions');
                    weightSelect.classList.add('d-none');
                    weightInput.classList.remove('d-none');
                }
            }
        }
        
        // Hide brand dropdown, show input with selected value
        this.classList.add('d-none');
        brandInput.classList.remove('d-none');
    });
    
    // Dimensions selection handler
    dimensionsSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.dataset.template) {
            const template = JSON.parse(selectedOption.dataset.template);
            dimensionsInput.value = template.dimensions || '';
            
            // Get all templates matching equipment name, brand, and dimensions
            const equipmentName = nameInput.value.trim();
            const selectedBrand = brandInput.value.trim();
            
            if (equipmentName && equipmentTemplates[equipmentName]) {
                const matchingTemplates = equipmentTemplates[equipmentName].filter(t => 
                    t.brand === selectedBrand && t.dimensions === template.dimensions
                );
                
                if (matchingTemplates.length === 1) {
                    // Exact match - auto-fill weight
                    applyTemplate(matchingTemplates[0]);
                    weightSelect.classList.add('d-none');
                    weightInput.classList.remove('d-none');
                } else {
                    // Multiple matches - show weight dropdown
                    populateDropdown(weightSelect, weightInput, matchingTemplates, 'weight_kg');
                }
            }
        }
        
        // Hide dimensions dropdown, show input with selected value
        this.classList.add('d-none');
        dimensionsInput.classList.remove('d-none');
    });
    
    // Weight selection handler
    weightSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.dataset.template) {
            const template = JSON.parse(selectedOption.dataset.template);
            weightInput.value = template.weight_kg || '';
        }
        
        // Hide weight dropdown, show input with selected value
        this.classList.add('d-none');
        weightInput.classList.remove('d-none');
    });
    
    // Clear form handler
    clearFormBtn.addEventListener('click', function() {
        // Reset all form fields
        document.querySelector('form').reset();
        clearDependentFields();
        categorySelect.value = 'Weights'; // Reset to default
        
        // Show dropdown, hide input
        nameSelect.classList.remove('d-none');
        nameInputGroup.classList.add('d-none');
        nameSelect.value = '';
    });
    
    // Handle delete equipment forms
    const deleteForms = document.querySelectorAll('.delete-equipment-form');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Remove this equipment?')) {
                return;
            }
            
            const itemId = this.dataset.itemId;
            const formData = new FormData(this);
            
            // Find the inventory item card
            const itemCard = document.querySelector(`.inventory-item[data-item-id="${itemId}"]`);
            
            // Submit the form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Remove the item from the DOM with animation
                if (itemCard) {
                    itemCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    itemCard.style.opacity = '0';
                    itemCard.style.transform = 'scale(0.9)';
                    
                    setTimeout(() => {
                        itemCard.remove();
                        
                        // Update the inventory count
                        const inventoryGrid = document.getElementById('inventory-grid');
                        const remainingItems = inventoryGrid.querySelectorAll('.inventory-item').length;
                        
                        // Update the header count
                        const headerCount = document.querySelector('h2.h5');
                        if (headerCount) {
                            headerCount.innerHTML = `<i class="bi bi-grid me-1"></i>Your Inventory (${remainingItems} items)`;
                        }
                        
                        // If no items left, reload to show empty state
                        if (remainingItems === 0) {
                            window.location.reload();
                        }
                        
                        // Parse the response to check for success/error messages
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const successAlert = doc.querySelector('.alert-success');
                        const errorAlert = doc.querySelector('.alert-danger');
                        
                        // Show success or error message
                        if (successAlert) {
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success alert-dismissible fade show';
                            alertDiv.innerHTML = successAlert.textContent + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                            document.querySelector('.mb-4').after(alertDiv);
                            
                            // Auto-dismiss after 3 seconds
                            setTimeout(() => alertDiv.remove(), 3000);
                        }
                        
                        if (errorAlert) {
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                            alertDiv.innerHTML = errorAlert.textContent + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                            document.querySelector('.mb-4').after(alertDiv);
                        }
                        
                        // Update total inventory value
                        const totalValueAlert = doc.querySelector('.alert-info');
                        const currentTotalAlert = document.querySelector('.alert-info');
                        if (totalValueAlert && currentTotalAlert) {
                            currentTotalAlert.innerHTML = totalValueAlert.innerHTML;
                        } else if (!totalValueAlert && currentTotalAlert) {
                            currentTotalAlert.remove();
                        }
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete equipment. Please try again.');
            });
        });
    });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
