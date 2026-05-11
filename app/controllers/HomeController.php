<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\LegalDocument;
use App\Models\GymMember;
use App\Models\StaffApplication;
use App\Models\MembershipApplication;
use App\Models\Employee;
use App\Models\FinancialRecord;
use App\Models\GymInventory;
use App\Models\AttendanceLog;

final class HomeController extends Controller
{
    /** Public landing page — shown when not logged in */
    public function landingAction(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('home/index');
        }
        $this->view('home/landing');
    }

    public function indexAction(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('home/landing');
        }

        $userModel = new User();
        $user = $userModel->findById((int)$_SESSION['user_id']);

        if (!$user) {
            unset($_SESSION['user_id']);
            $this->redirect('auth/login');
        }

        $role = $user['role'] ?? 'customer';

        switch ($role) {
            case 'admin':
                $this->adminDashboard($user);
                break;
            case 'administrative_officer':
                $this->admOfficerDashboard($user);
                break;
            case 'gym_owner':
                $this->gymOwnerDashboard($user);
                break;
            case 'trainer':
            case 'maintenance':
                $this->staffDashboard($user, $role);
                break;
            default:
                $this->customerDashboard($user);
                break;
        }
    }

    private function customerDashboard(array $user): void
    {
        $legalDoc = (new LegalDocument())->findByUserId((int)$user['id']);
        $staffApp = (new StaffApplication())->findByUserId((int)$user['id']);
        $memberApp = (new MembershipApplication())->findByUserId((int)$user['id']);
        $gymMember = (new GymMember())->findByUserId((int)$user['id']);

        $this->view('dashboard/customer', [
            'user' => $user,
            'legalDoc' => $legalDoc,
            'staffApp' => $staffApp,
            'memberApp' => $memberApp,
            'gymMember' => $gymMember,
        ]);
    }

    private function gymOwnerDashboard(array $user): void
    {
        $finModel = new FinancialRecord();
        $invModel = new GymInventory();
        $memberModel = new GymMember();

        $budget = $finModel->getBudget((int)$user['id']);
        $totalExpenses = $finModel->getTotalExpenses((int)$user['id']);
        $totalEquipment = $invModel->getTotalSpent((int)$user['id']);
        $staffApps = (new StaffApplication())->findAllPending();

        // Revenue & Members data for dashboard
        $totalRevenue = $finModel->getTotalRevenue((int)$user['id']);
        $revenueBreakdown = $finModel->getRevenueBreakdown((int)$user['id']);
        $monthlyProfit = $finModel->getMonthlyProfit((int)$user['id']);
        $members = $memberModel->findAll();
        $activeMembers = $memberModel->findAllActive();
        $monthlyMemberRevenue = $memberModel->getMonthlyRevenue();
        $revenueByMonth = $memberModel->getRevenueByMonth(6);
        $memberApps = (new MembershipApplication())->findAll();
        $pendingMemberApps = array_filter($memberApps, fn($a) => in_array($a['status'], ['pending', 'resubmit'], true));

        $this->view('dashboard/gymowner', [
            'user' => $user,
            'budget' => $budget,
            'totalExpenses' => $totalExpenses,
            'totalEquipment' => $totalEquipment,
            'remaining' => $budget - $totalExpenses - $totalEquipment,
            'staffApps' => $staffApps,
            'totalRevenue' => $totalRevenue,
            'revenueBreakdown' => $revenueBreakdown,
            'monthlyProfit' => $monthlyProfit,
            'members' => $members,
            'activeMembers' => $activeMembers,
            'monthlyMemberRevenue' => $monthlyMemberRevenue,
            'revenueByMonth' => $revenueByMonth,
            'pendingMemberApps' => $pendingMemberApps,
        ]);
    }

    private function adminDashboard(array $user): void
    {
        $legalDocs = (new LegalDocument())->findAllPending();

        $this->view('dashboard/admin', [
            'user' => $user,
            'legalDocs' => $legalDocs,
        ]);
    }

    private function admOfficerDashboard(array $user): void
    {
        $memberApps = (new MembershipApplication())->findAll();
        $gymMembers = (new GymMember())->findAll();
        $employees = (new Employee())->findAll();
        $pendingApps = array_filter($memberApps, fn($a) => in_array($a['status'], ['pending', 'resubmit'], true));

        $this->view('dashboard/admofficer', [
            'user' => $user,
            'memberApps' => $memberApps,
            'gymMembers' => $gymMembers,
            'employees' => $employees,
            'pendingCount' => count($pendingApps),
        ]);
    }

    private function staffDashboard(array $user, string $role): void
    {
        $this->view('dashboard/staff', [
            'user' => $user,
            'role' => $role,
        ]);
    }

    public function logoutAction(): void
    {
        // Log logout activity before destroying session
        if (isset($_SESSION['user_id'])) {
            $userModel = new User();
            $user = $userModel->findById((int)$_SESSION['user_id']);
            
            if ($user) {
                $loginActivityModel = new \App\Models\LoginActivity();
                $loginActivityModel->logLogout((int)$user['id'], (string)$user['email']);
            }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('home/landing');
    }
}
