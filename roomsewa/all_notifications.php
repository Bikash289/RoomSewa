<?php
require 'includes/header.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all notifications for the user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mark all as read (safer prepared statement)
$update = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$update->bind_param("i", $user_id);
$update->execute();

// Count unread notifications (should now be 0)
$count_stmt = $conn->prepare("SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$unread_count = $count_stmt->get_result()->fetch_assoc()['count'];
?>

<div class="notifications-page">
    <div class="container">
        <h1>Your Notifications</h1>

        <div class="notifications-list">
            <?php if (!empty($all_notifications)): ?>
                <?php foreach ($all_notifications as $notification): ?>
                    <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>">
                        <div class="notification-content">
                            <p><?= htmlspecialchars($notification['message']) ?></p>
                            <small><?= date('M d, Y h:i A', strtotime($notification['created_at'])) ?></small>
                        </div>

                        <?php if (!empty($notification['link'])): ?>
                            <a href="<?= htmlspecialchars($notification['link']) ?>" class="notification-link">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notifications-page {
    padding: 30px 0;
}

.notifications-list {
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    background: white;
    transition: background 0.2s ease-in-out;
}

.notification-item.unread {
    background: #f8f9fa;
}

.notification-item:hover {
    background: #eef2f5;
}

.notification-content {
    flex: 1;
}

.notification-content p {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.notification-content small {
    color: #777;
    font-size: 0.8rem;
}

.notification-link {
    color: #3498db;
    padding: 5px 10px;
    border-radius: 4px;
}

.no-notifications {
    text-align: center;
    padding: 40px;
    background: white;
}

.no-notifications i {
    font-size: 2rem;
    color: #ccc;
    margin-bottom: 10px;
}

.no-notifications p {
    color: #777;
}
</style>

<?php include 'includes/footer.php'; ?>