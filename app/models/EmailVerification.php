<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class EmailVerification extends Model
{
    public function create(int $userId, string $token): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO email_verifications (user_id, token, created_at)
             VALUES (:user_id, :token, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token,
        ]);
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM email_verifications WHERE token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteByUserId(int $userId): void
    {
        $stmt = $this->db()->prepare('DELETE FROM email_verifications WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }
}
