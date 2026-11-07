<?php
namespace Models;

use Core\Model;
use PDO;

/**
 * Class NotificationModel
 * Mengelola data notifikasi pengguna secara terpisah dari audit_logs.
 */
class NotificationModel extends Model
{
    protected string $table = 'notifications';
    protected array $fillable = [
        'user_id', 'title', 'message', 'category', 'link', 'is_read', 'created_at'
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Ambil notifikasi milik pengguna tertentu dengan filter kategori dan status baca
     */
    public function getByUser(int $userId, string $category = '', ?string $readStatus = null, string $search = '', int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($category) && $category !== 'Semua Kategori') {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        if ($readStatus === 'unread') {
            $sql .= " AND is_read = 0";
        } elseif ($readStatus === 'read') {
            $sql .= " AND is_read = 1";
        }

        if (!empty($search)) {
            $sql .= " AND (title LIKE :s1 OR message LIKE :s2)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
        }

        $sql .= " ORDER BY created_at DESC, id DESC";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung total notifikasi belum dibaca untuk user tertentu
     */
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Hitung statistik notifikasi berdasarkan kategori
     */
    public function getCategoryCounts(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT category, COUNT(*) as total, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
            FROM {$this->table}
            WHERE user_id = :user_id
            GROUP BY category
        ");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['category']] = $r;
        }
        return $result;
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca (verifikasi kepemilikan user)
     */
    public function markAsRead(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Tandai seluruh notifikasi milik user sebagai sudah dibaca
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Hapus satu notifikasi milik user
     */
    public function deleteForUser(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Helper untuk membuat notifikasi baru ke pengguna
     * Mendukung pemanggilan via array asosiatif maupun argumen posisional
     */
    public function createNotification(int|array $userId, string $title = '', string $message = '', string $category = 'Informasi Sistem', ?string $link = null): int
    {
        if (is_array($userId)) {
            $data = [
                'user_id'    => (int)($userId['user_id'] ?? 0),
                'title'      => (string)($userId['title'] ?? ''),
                'message'    => (string)($userId['message'] ?? ''),
                'category'   => (string)($userId['category'] ?? 'Informasi Sistem'),
                'link'       => $userId['link'] ?? null,
                'is_read'    => (int)($userId['is_read'] ?? 0),
                'created_at' => $userId['created_at'] ?? date('Y-m-d H:i:s')
            ];
        } else {
            $data = [
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'category'   => $category,
                'link'       => $link,
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return (int)$this->create($data);
    }
}

