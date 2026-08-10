<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\FinancialRecord;
use App\Models\GymEquipment;
use App\Models\GymInventory;
use App\Models\Supplier;
use App\Models\EquipmentTemplate;

final class EquipmentController extends Controller
{
    private function requireGymOwner(): array
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('auth/login'); }
        $user = (new User())->findById((int)$_SESSION['user_id']);
        if (!$user || $user['role'] !== 'gym_owner') { $this->redirect('home/index'); }
        return $user;
    }

    /** Financial Dashboard: Operational Expenses, Revenue & Profit */
    public function budgetAction(): void
    {
        $user = $this->requireGymOwner();
        $finModel = new FinancialRecord();
        $error = ''; $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'add_operational_expense') {
                $name = trim((string)($_POST['expense_name'] ?? ''));
                $category = trim((string)($_POST['expense_category'] ?? ''));
                $amount = round((float)($_POST['expense_amount'] ?? 0), 2);
                $notes = trim((string)($_POST['expense_notes'] ?? ''));
                if ($name === '' || $amount <= 0) { $error = 'Name and valid amount are required.'; }
                else {
                    $finModel->addOperationalExpense((int)$user['id'], $name, $category, $amount, $notes);
                    $success = 'Operational expense added.';
                }
            } elseif ($action === 'add_revenue') {
                $name = trim((string)($_POST['revenue_name'] ?? ''));
                $amount = round((float)($_POST['revenue_amount'] ?? 0), 2);
                $notes = trim((string)($_POST['revenue_notes'] ?? ''));
                if ($name === '' || $amount <= 0) { $error = 'Name and valid amount are required.'; }
                else {
                    $finModel->addRevenue((int)$user['id'], $name, $amount, $notes);
                    $success = 'Revenue recorded.';
                }
            }
        }

        $operationalExpenses = $finModel->getOperationalExpenses((int)$user['id']);
        $totalOpex = $finModel->getTotalOperationalExpenses((int)$user['id']);
        $revenues = $finModel->getRevenues((int)$user['id']);
        $totalRevenue = $finModel->getTotalRevenue((int)$user['id']);
        $monthlyProfit = $finModel->getMonthlyProfit((int)$user['id']);

        $this->view('equipment/budget', [
            'user' => $user,
            'operationalExpenses' => $operationalExpenses,
            'totalOpex' => $totalOpex,
            'revenues' => $revenues,
            'totalRevenue' => $totalRevenue,
            'monthlyProfit' => $monthlyProfit,
            'error' => $error,
            'success' => $success,
        ]);
    }

    /** Equipment Inventory: List new equipment */
    public function inventoryAction(): void
    {
        $user = $this->requireGymOwner();
        $equipModel = new GymEquipment();
        $templateModel = new EquipmentTemplate();
        $error = ''; $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'list_equipment') {
                // Handle equipment name from dropdown or input
                $name = '';
                if (!empty($_POST['eq_name_select']) && $_POST['eq_name_select'] !== '__ADD_NEW__') {
                    $name = trim((string)$_POST['eq_name_select']);
                } else {
                    $name = trim((string)($_POST['eq_name'] ?? ''));
                }

                $category = trim((string)($_POST['eq_category'] ?? ''));
                $brand = trim((string)($_POST['eq_brand'] ?? ''));
                $dimensions = trim((string)($_POST['eq_dimensions'] ?? ''));
                $weightKg = !empty($_POST['eq_weight']) ? round((float)$_POST['eq_weight'], 2) : null;
                $quantity = (int)($_POST['eq_quantity'] ?? 0);
                $description = trim((string)($_POST['eq_description'] ?? ''));
                $saveAsTemplate = !empty($_POST['save_as_template']);

                if ($name === '' || $quantity < 1) {
                    $error = 'Name and quantity (min 1) are required.';
                } else {
                    // Handle image upload
                    $imagePath = null;
                    if (!empty($_FILES['eq_image']['tmp_name'])) {
                        $uploadDir = BASE_PATH . '/public/uploads/equipment/';
                        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                        $ext = strtolower(pathinfo($_FILES['eq_image']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
                            $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
                        } else {
                            $filename = 'equip_' . $user['id'] . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($_FILES['eq_image']['tmp_name'], $uploadDir . $filename)) {
                                $imagePath = 'uploads/equipment/' . $filename;
                            } else {
                                $error = 'Failed to upload image.';
                            }
                        }
                    }

                    if ($error === '') {
                        $equipModel->listEquipment(
                            (int)$user['id'], $name, $category, $brand,
                            $dimensions, $weightKg, $quantity, 0.0,
                            $description, $imagePath
                        );

                        // Save as template if requested and doesn't already exist
                        if ($saveAsTemplate && !empty($brand) && !empty($dimensions) && $weightKg !== null) {
                            if (!$templateModel->templateExists($name, $brand, $dimensions, $weightKg, (int)$user['id'])) {
                                $templateModel->createTemplate(
                                    $name, $brand, $dimensions, $weightKg, $category, (int)$user['id'], false
                                );
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully and saved as template for future use.';
                            } else {
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully. (Template already exists)';
                            }
                        } else {
                            // Check if this is a new equipment name that should be saved as a basic template
                            $uniqueNames = $templateModel->getUniqueEquipmentNames((int)$user['id']);
                            if (!in_array($name, $uniqueNames, true)) {
                                // This is a new equipment name - save it as a basic template
                                $templateModel->createTemplate(
                                    $name, $brand, $dimensions, $weightKg, $category, (int)$user['id'], false
                                );
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully and added to your equipment names for future use.';
                            } else {
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully.';
                            }
                        }
                    }
                }
            } elseif ($action === 'delete_equipment') {
                $eqId = (int)($_POST['eq_id'] ?? 0);
                if ($eqId > 0) {
                    $equip = $equipModel->findById($eqId);
                    if ($equip) {
                        $equipModel->deactivate($eqId);
                        $success = 'Equipment removed from inventory.';
                    } else {
                        $error = 'Equipment not found.';
                    }
                }
            }
        }

        $inventory = $equipModel->findByOwnerId((int)$user['id']);
        $equipmentTemplates = $templateModel->getTemplatesAsJson((int)$user['id']);
        $uniqueEquipmentNames = $templateModel->getUniqueEquipmentNames((int)$user['id']);
        
        $this->view('equipment/inventory', [
            'user' => $user, 
            'inventory' => $inventory,
            'equipmentTemplates' => $equipmentTemplates,
            'uniqueEquipmentNames' => $uniqueEquipmentNames,
            'error' => $error, 
            'success' => $success,
        ]);
    }

    /** Equipment shop - kept for backward compatibility but redirects to inventory */
    public function shopAction(): void
    {
        $this->redirect('equipment/inventory');
    }
}
