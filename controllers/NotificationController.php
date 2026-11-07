<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Models\NotificationModel;

/**
 * Class NotificationController
 * Dedicated Controller untuk Halaman Notifikasi Pengguna (Terpisah dari Dashboard & Audit Log)
 */
class NotificationController extends Controller
{
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Halaman Utama Notifikasi
     */
    public function index(): void
    {
        $currentUser = $this->requireAuth();
        $userId = (int)$currentUser['id'];

        $category = trim($this->input('category', ''));
        $status   = trim($this->input('status', '')); // 'unread', 'read', or '' (all)
        $query    = trim($this->input('q', ''));

        $notifications = $this->notificationModel->getByUser(
            $userId,
            $category,
            $status !== '' ? $status : null,
            $query,
            100
        );

        $unreadCount    = $this->notificationModel->getUnreadCount($userId);
        $categoryCounts = $this->notificationModel->getCategoryCounts($userId);

        if ($this->isAjax() && $this->input('format') === 'json') {
            $this->json([
                'success'       => true,
                'data'          => $notifications,
                'unread_count'  => $unreadCount
            ]);
            return;
        }

        $this->render('notifications/index', [
            'title'          => 'Notifikasi - CLAIMORA',
            'user'           => $currentUser,
            'notifications'  => $notifications,
            'unreadCount'    => $unreadCount,
            'categoryCounts' => $categoryCounts,
            'currentCategory'=> $category,
            'currentStatus'  => $status,
            'query'          => $query,
            'activeNav'      => 'notifications'
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca
     */
    public function markRead(): void
    {
        $currentUser = $this->requireAuth();
        $id = (int)$this->input('id', 0);

        if ($id > 0) {
            $this->notificationModel->markAsRead($id, (int)$currentUser['id']);
        }

        if ($this->isAjax()) {
            $unreadCount = $this->notificationModel->getUnreadCount((int)$currentUser['id']);
            $this->json(['success' => true, 'unread_count' => $unreadCount]);
            return;
        }

        Session::setFlash('success', 'Notifikasi telah ditandai sudah dibaca.');
        $this->redirect('index.php?page=notifications');
    }

    /**
     * Tandai semua notifikasi milik pengguna sebagai telah dibaca
     */
    public function markAllRead(): void
    {
        $currentUser = $this->requireAuth();
        $this->notificationModel->markAllAsRead((int)$currentUser['id']);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'unread_count' => 0]);
            return;
        }

        Session::setFlash('success', 'Semua notifikasi berhasil ditandai telah dibaca.');
        $this->redirect('index.php?page=notifications');
    }

    /**
     * Hapus satu notifikasi milik pengguna
     */
    public function delete(): void
    {
        $currentUser = $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$this->input('id', 0);
        if ($id > 0) {
            $this->notificationModel->deleteForUser($id, (int)$currentUser['id']);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Notifikasi berhasil dihapus.']);
            return;
        }

        Session::setFlash('success', 'Notifikasi berhasil dihapus.');
        $this->redirect('index.php?page=notifications');
    }
}

