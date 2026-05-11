<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DateTimeImmutable;
use PDO;

final class OtpCode extends Model
{
    public function create(int $userId, string $otpCode, DateTimeImmutable $expiresAt): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO otp_codes (user_id, otp_code, expires_at, created_at)
             VALUES (:user_id, :otp_code, :expires_at, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':otp_code' => $otpCode,
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function latestValidForUser(int $userId, string $otpCode): ?array
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $this->db()->prepare(
            'SELECT * FROM otp_codes
             WHERE user_id = :user_id
               AND otp_code = :otp_code
               AND expires_at >= :now
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':otp_code' => $otpCode,
            ':now' => $now,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteAllForUser(int $userId): void
    {
        $stmt = $this->db()->prepare('DELETE FROM otp_codes WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }
}
