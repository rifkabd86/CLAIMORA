<?php
namespace Models;

use Core\Model;

/**
 * Class UserModel
 * Handles User persistence, authentication, and password hashing verification
 */
class UserModel extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'username', 'email', 'password', 'role', 'role_label', 'avatar', 'created_at', 'updated_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    public function findByEmailOrUsername(string $identifier): ?array
    {
        $cleanIdentifier = trim($identifier);
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE LOWER(email) = LOWER(:email) OR LOWER(username) = LOWER(:username)
            LIMIT 1
        ");
        $stmt->execute([':email' => $cleanIdentifier, ':username' => $cleanIdentifier]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function isEmailTaken(string $email, int $excludeId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM {$this->table} WHERE LOWER(email) = LOWER(:email) AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return ((int)($stmt->fetch()['cnt'] ?? 0)) > 0;
    }

    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    public function getAllUsers(): array
    {
        return $this->findAll('name ASC');
    }
}
