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

    /** Financial Dashboard: Investment, Operational Expenses, Revenue & Profit */
    public function budgetAction(): void
    {
        $user = $this->requireGymOwner();
        $finModel = new FinancialRecord();
        $error = ''; $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'set_investment') {
                $amount = round((float)($_POST['investment_amount'] ?? 0), 2);
                if ($amount <= 0) { $error = 'Enter a valid investment amount.'; }
                else {
                    $finModel->setInvestment((int)$user['id'], $amount);
                    $success = 'Investment set to ₱' . number_format($amount, 2);
                }
            } elseif ($action === 'add_investment_usage') {
                $name = trim((string)($_POST['inv_name'] ?? ''));
                $category = trim((string)($_POST['inv_category'] ?? ''));
                $amount = round((float)($_POST['inv_amount'] ?? 0), 2);
                $notes = trim((string)($_POST['inv_notes'] ?? ''));
                if ($name === '' || $amount <= 0) { $error = 'Name and valid amount are required.'; }
                else {
                    $remaining = $finModel->getInvestment((int)$user['id']) - $finModel->getTotalInvestmentUsage((int)$user['id']);
                    if ($amount > $remaining) {
                        $error = 'Not enough remaining investment. Remaining: ₱' . number_format($remaining, 2) . ', requested: ₱' . number_format($amount, 2) . '.';
                    } else {
                        $finModel->addInvestmentUsage((int)$user['id'], $name, $category, $amount, $notes);
                        $success = 'Investment usage recorded. ₱' . number_format($amount, 2) . ' deducted from investment.';
                    }
                }
            } elseif ($action === 'add_operational_expense') {
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

        $investment = $finModel->getInvestment((int)$user['id']);
        $totalInvUsage = $finModel->getTotalInvestmentUsage((int)$user['id']);
        $investmentUsages = $finModel->getInvestmentUsages((int)$user['id']);
        $operationalExpenses = $finModel->getOperationalExpenses((int)$user['id']);
        $totalOpex = $finModel->getTotalOperationalExpenses((int)$user['id']);
        $revenues = $finModel->getRevenues((int)$user['id']);
        $totalRevenue = $finModel->getTotalRevenue((int)$user['id']);
        $monthlyProfit = $finModel->getMonthlyProfit((int)$user['id']);

        $this->view('equipment/budget', [
            'user' => $user,
            'investment' => $investment,
            'investmentRemaining' => $investment - $totalInvUsage,
            'totalInvUsage' => $totalInvUsage,
            'investmentUsages' => $investmentUsages,
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
                $price = round((float)($_POST['eq_price'] ?? 0), 2);
                $description = trim((string)($_POST['eq_description'] ?? ''));
                $saveAsTemplate = !empty($_POST['save_as_template']);

                if ($name === '' || $quantity < 1) {
                    $error = 'Name and quantity (min 1) are required.';
                } elseif ($price < 0) {
                    $error = 'Enter a valid price.';
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
                        $totalCost = round($quantity * $price, 2);

                        // Check if investment can cover the equipment cost
                        $finModel = new FinancialRecord();
                        $remaining = $finModel->getInvestment((int)$user['id']) - $finModel->getTotalInvestmentUsage((int)$user['id']);
                        if ($totalCost > 0 && $totalCost > $remaining) {
                            $error = 'Not enough remaining investment (₱' . number_format($remaining, 2) . ') to cover equipment cost (₱' . number_format($totalCost, 2) . ').';
                        }
                    }

                    if ($error === '') {
                        $totalCost = round($quantity * $price, 2);
                        $equipModel->listEquipment(
                            (int)$user['id'], $name, $category, $brand,
                            $dimensions, $weightKg, $quantity, $price,
                            $description, $imagePath
                        );

                        // Auto-deduct from investment: record qty × price as investment usage
                        if ($totalCost > 0) {
                            $finModel = new FinancialRecord();
                            $finModel->addInvestmentUsage(
                                (int)$user['id'],
                                'Equipment: ' . $name,
                                'Equipment Purchase',
                                $totalCost,
                                'Qty: ' . $quantity . ' × ₱' . number_format($price, 2) . ' = ₱' . number_format($totalCost, 2)
                            );
                        }

                        // Save as template if requested and doesn't already exist
                        if ($saveAsTemplate && !empty($brand) && !empty($dimensions) && $weightKg !== null) {
                            if (!$templateModel->templateExists($name, $brand, $dimensions, $weightKg, (int)$user['id'])) {
                                $templateModel->createTemplate(
                                    $name, $brand, $dimensions, $weightKg, $category, (int)$user['id'], false
                                );
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully and saved as template for future use. Total cost ₱' . number_format($totalCost, 2) . ' deducted from investment.';
                            } else {
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully. Total cost ₱' . number_format($totalCost, 2) . ' deducted from investment. (Template already exists)';
                            }
                        } else {
                            // Check if this is a new equipment name that should be saved as a basic template
                            $uniqueNames = $templateModel->getUniqueEquipmentNames((int)$user['id']);
                            if (!in_array($name, $uniqueNames, true)) {
                                // This is a new equipment name - save it as a basic template
                                $templateModel->createTemplate(
                                    $name, $brand, $dimensions, $weightKg, $category, (int)$user['id'], false
                                );
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully and added to your equipment names for future use. Total cost ₱' . number_format($totalCost, 2) . ' deducted from investment.';
                            } else {
                                $success = 'Equipment "' . htmlspecialchars($name) . '" listed successfully. Total cost ₱' . number_format($totalCost, 2) . ' deducted from investment.';
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

                        // Refund the equipment cost back to investment
                        $totalCost = round((int)$equip['quantity'] * (float)$equip['price'], 2);
                        if ($totalCost > 0) {
                            $finModel = new FinancialRecord();
                            $finModel->addInvestmentUsage(
                                (int)$user['id'],
                                'Removed: ' . $equip['name'],
                                'Equipment Removal (Refund)',
                                -abs($totalCost),
                                'Refund: ' . (int)$equip['quantity'] . ' × ₱' . number_format((float)$equip['price'], 2) . ' = ₱' . number_format($totalCost, 2) . ' restored to investment.'
                            );
                        }

                        $success = 'Equipment removed from inventory. ₱' . number_format($totalCost, 2) . ' restored to investment.';
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
