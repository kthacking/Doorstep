<?php
require_once __DIR__ . '/../config/db.php';

$role = $_SESSION['role'] ?? null;
$logged_in = isset($_SESSION['user_id']) || isset($_SESSION['agent_id']);
$is_agent = ($role === 'agent');
$is_admin = ($role === 'admin');
$is_user = ($role === 'user');

$notifs = [];
if ($logged_in) {
    if ($is_admin) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = 'admin' AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $notifs = $stmt->fetchAll();
    } elseif ($is_agent) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = 'agent' AND user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$_SESSION['agent_id']]);
        $notifs = $stmt->fetchAll();
    } elseif ($is_user) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = 'user' AND user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$_SESSION['user_id']]);
        $notifs = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="/project/foodapp/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $is_agent ? 'agent-portal' : ''; ?>">
    <div class="bg-gradient"></div>
    <nav>
        <a href="/project/foodapp/index.php" class="logo">
            <i class="fas fa-hand-holding-heart"></i> Doorstep
        </a>
        <ul class="nav-links">
            <li><a href="/project/foodapp/index.php">Home</a></li>
            
            <?php if (!$logged_in): ?>
                <li><a href="/project/foodapp/pages/services.php">Services</a></li>
                <li><a href="/project/foodapp/pages/login.php">Login</a></li>
                <li><a href="/project/foodapp/pages/register.php" class="nav-btn">Register</a></li>
            <?php else: ?>
                <?php if ($is_user): ?>
                    <li><a href="/project/foodapp/pages/services.php">Browse Services</a></li>
                    <li><a href="/project/foodapp/user/dashboard.php">My Bookings</a></li>
                    <li><a href="/project/foodapp/user/profile.php">My Profile</a></li>
                <?php elseif ($is_agent): ?>
                    <li><a href="/project/foodapp/agent/dashboard.php">My Assignments</a></li>
                <?php elseif ($is_admin): ?>
                    <li><a href="/project/foodapp/admin/dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>

                <!-- Notification Bell -->
                <li style="position: relative;">
                    <div class="notification-bell" id="notifBell">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notifs) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifs); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-dropdown" id="notifDropdown" style="right: 0; left: auto;">
                        <div style="padding: 0.8rem; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 0.9rem; color: var(--dark);">Notifications</div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php if (count($notifs) === 0): ?>
                                <div class="notification-item">No new alerts</div>
                            <?php else: ?>
                                <?php foreach ($notifs as $n): ?>
                                    <div class="notification-item">
                                        <div style="line-height: 1.4; color: #4b5563;"><?php echo $n['message']; ?></div>
                                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($n['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>

                <li><a href="/project/foodapp/actions/logout.php" class="nav-btn" style="background: var(--danger);">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <script>
        const bell = document.getElementById('notifBell');
        const dropdown = document.getElementById('notifDropdown');
        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        }
    </script>
