<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Data Masking Utility - DLP Feature
 * 
 * Masks sensitive data to prevent unauthorized viewing of complete information.
 * This is a Data Loss Prevention (DLP) feature that protects PII (Personally Identifiable Information).
 * 
 * Usage:
 * - DataMasking::phone('09123456789') → '*****6789'
 * - DataMasking::email('john@example.com') → 'j***@example.com'
 * - DataMasking::cardNumber('4343434343434345') → '****-****-****-4345'
 */
final class DataMasking
{
    /**
     * Mask phone number - shows only last 4 digits
     * 
     * @param string $phone Phone number to mask
     * @param string $maskChar Character to use for masking (default: *)
     * @return string Masked phone number
     * 
     * Examples:
     * - '09123456789' → '*****6789'
     * - '09171234567' → '*****4567'
     */
    public static function phone(string $phone, string $maskChar = '*'): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-digits
        
        if (strlen($phone) <= 4) {
            return str_repeat($maskChar, strlen($phone));
        }
        
        $visibleDigits = 4;
        $maskedLength = strlen($phone) - $visibleDigits;
        $lastDigits = substr($phone, -$visibleDigits);
        
        return str_repeat($maskChar, $maskedLength) . $lastDigits;
    }

    /**
     * Mask email address - shows first letter and domain
     * 
     * @param string $email Email to mask
     * @param string $maskChar Character to use for masking (default: *)
     * @return string Masked email
     * 
     * Examples:
     * - 'john.doe@gmail.com' → 'j***@gmail.com'
     * - 'admin@example.com' → 'a***@example.com'
     */
    public static function email(string $email, string $maskChar = '*'): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return str_repeat($maskChar, strlen($email));
        }
        
        [$local, $domain] = explode('@', $email, 2);
        
        if (strlen($local) <= 1) {
            return $maskChar . '@' . $domain;
        }
        
        $firstChar = substr($local, 0, 1);
        $maskedLocal = $firstChar . str_repeat($maskChar, min(3, strlen($local) - 1));
        
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mask credit card number - shows only last 4 digits
     * 
     * @param string $cardNumber Card number to mask
     * @param string $maskChar Character to use for masking (default: *)
     * @return string Masked card number with dashes
     * 
     * Examples:
     * - '4343434343434345' → '****-****-****-4345'
     * - '5555555555554444' → '****-****-****-4444'
     */
    public static function cardNumber(string $cardNumber, string $maskChar = '*'): string
    {
        $cardNumber = preg_replace('/[^0-9]/', '', $cardNumber);
        
        if (strlen($cardNumber) < 4) {
            return str_repeat($maskChar, strlen($cardNumber));
        }
        
        $lastFour = substr($cardNumber, -4);
        $groups = (int)ceil((strlen($cardNumber) - 4) / 4);
        
        $masked = str_repeat($maskChar . $maskChar . $maskChar . $maskChar . '-', $groups);
        return $masked . $lastFour;
    }

    /**
     * Mask name - shows first and last initial only
     * 
     * @param string $name Full name to mask
     * @param string $maskChar Character to use for masking (default: *)
     * @return string Masked name
     * 
     * Examples:
     * - 'John Doe' → 'J*** D***'
     * - 'Maria Santos' → 'M*** S***'
     */
    public static function name(string $name, string $maskChar = '*'): string
    {
        $parts = explode(' ', trim($name));
        $masked = [];
        
        foreach ($parts as $part) {
            if (strlen($part) > 0) {
                $masked[] = substr($part, 0, 1) . str_repeat($maskChar, min(3, strlen($part) - 1));
            }
        }
        
        return implode(' ', $masked);
    }

    /**
     * Mask address - shows only city/province
     * 
     * @param string $address Full address to mask
     * @return string Masked address
     * 
     * Examples:
     * - '123 Main St, Manila, Philippines' → '*****, Manila, Philippines'
     */
    public static function address(string $address): string
    {
        $parts = explode(',', $address);
        
        if (count($parts) <= 1) {
            return '*****';
        }
        
        // Mask first part (street address), keep city/province
        $parts[0] = '*****';
        return implode(',', $parts);
    }

    /**
     * Mask birth date - shows only year
     * 
     * @param string $birthDate Birth date (Y-m-d format)
     * @return string Masked birth date
     * 
     * Examples:
     * - '1990-05-15' → '****-**-** (1990)'
     * - '1985-12-25' → '****-**-** (1985)'
     */
    public static function birthDate(string $birthDate): string
    {
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $birthDate, $matches)) {
            return '****-**-** (' . $matches[1] . ')';
        }
        return '****-**-**';
    }

    /**
     * Mask API key/secret - shows only first 4 and last 4 characters
     * 
     * @param string $key API key or secret to mask
     * @param string $maskChar Character to use for masking (default: *)
     * @return string Masked key
     * 
     * Examples:
     * - 'sk_test_abc123def456ghi789' → 'sk_t********************i789'
     * - 'pk_live_xyz789abc123' → 'pk_l***********c123'
     */
    public static function apiKey(string $key, string $maskChar = '*'): string
    {
        if (strlen($key) <= 8) {
            return str_repeat($maskChar, strlen($key));
        }
        
        $firstFour = substr($key, 0, 4);
        $lastFour = substr($key, -4);
        $maskedLength = strlen($key) - 8;
        
        return $firstFour . str_repeat($maskChar, $maskedLength) . $lastFour;
    }

    /**
     * Check if user has permission to view unmasked data
     * 
     * @param string $dataType Type of data (phone, email, etc.)
     * @param string|null $userRole Current user's role
     * @return bool True if user can view unmasked data
     */
    public static function canViewUnmasked(string $dataType, ?string $userRole): bool
    {
        // Admin can view all unmasked data
        if ($userRole === 'admin') {
            return true;
        }
        
        // Administrative officer can view phone and email
        if ($userRole === 'administrative_officer' && in_array($dataType, ['phone', 'email'], true)) {
            return true;
        }
        
        // Gym owner can view their own members' data
        if ($userRole === 'gym_owner' && in_array($dataType, ['phone', 'email', 'name'], true)) {
            return true;
        }
        
        // Default: cannot view unmasked
        return false;
    }

    /**
     * Smart mask - automatically masks data based on user role
     * 
     * @param string $data Data to mask
     * @param string $dataType Type of data (phone, email, card, name, etc.)
     * @param string|null $userRole Current user's role
     * @return string Masked or unmasked data based on permissions
     */
    public static function smartMask(string $data, string $dataType, ?string $userRole): string
    {
        // If user has permission, return unmasked data
        if (self::canViewUnmasked($dataType, $userRole)) {
            return $data;
        }
        
        // Otherwise, mask based on data type
        return match($dataType) {
            'phone' => self::phone($data),
            'email' => self::email($data),
            'card' => self::cardNumber($data),
            'name' => self::name($data),
            'address' => self::address($data),
            'birthdate' => self::birthDate($data),
            'apikey' => self::apiKey($data),
            default => str_repeat('*', min(strlen($data), 10)),
        };
    }

    /**
     * Get data classification label
     * 
     * @param string $dataType Type of data
     * @return string Classification label (PUBLIC, INTERNAL, CONFIDENTIAL, RESTRICTED)
     */
    public static function getClassification(string $dataType): string
    {
        return match($dataType) {
            'phone', 'email', 'birthdate', 'address' => 'CONFIDENTIAL',
            'card', 'apikey', 'password' => 'RESTRICTED',
            'name', 'age' => 'INTERNAL',
            default => 'PUBLIC',
        };
    }

    /**
     * Get CSS class for data classification badge
     * 
     * @param string $classification Classification level
     * @return string CSS class
     */
    public static function getClassificationBadgeClass(string $classification): string
    {
        return match($classification) {
            'RESTRICTED' => 'badge bg-danger',
            'CONFIDENTIAL' => 'badge bg-warning text-dark',
            'INTERNAL' => 'badge bg-info',
            'PUBLIC' => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }
}
