<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/lang.php';

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
    <link rel="stylesheet" href="/project/Doorstep/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $is_agent ? 'agent-portal' : ''; ?>">
    <div class="bg-gradient"></div>
    <nav>
        <a href="/project/Doorstep/index.php" class="logo">
            <i class="fas fa-hand-holding-heart"></i> Doorstep
        </a>
        <ul class="nav-links">
            <li class="lang-selector" style="position: relative; margin-right: 1rem;">
                <div style="cursor: pointer; font-size: 0.8rem; font-weight: 700; color: var(--secondary); background: #f1f5f9; padding: 5px 12px; border-radius: 50px;">
                    <i class="fas fa-globe"></i> <?php echo strtoupper($lang); ?>
                </div>
                <div class="lang-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; box-shadow: 0 10px 15px rgba(0,0,0,0.1); border-radius: 12px; padding: 0.5rem; z-index: 1000; min-width: 120px; margin-top: 10px;">
                    <a href="?lang=en" style="display: block; padding: 8px 12px; color: var(--dark); font-size: 0.85rem; border-radius: 8px;">English</a>
                    <a href="?lang=hi" style="display: block; padding: 8px 12px; color: var(--dark); font-size: 0.85rem; border-radius: 8px;">हिंदी (Hindi)</a>
                    <a href="?lang=ta" style="display: block; padding: 8px 12px; color: var(--dark); font-size: 0.85rem; border-radius: 8px;">தமிழ் (Tamil)</a>
                </div>
            </li>
            <script>
                document.querySelector('.lang-selector').onclick = (e) => {
                    const dropdown = document.querySelector('.lang-dropdown');
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                    e.stopPropagation();
                };
                window.onclick = () => document.querySelector('.lang-dropdown').style.display = 'none';
            </script>

            <li><a href="/project/Doorstep/index.php"><?php echo __('home'); ?></a></li>
            
            <?php if (!$logged_in): ?>
                <li><a href="/project/Doorstep/pages/services.php"><?php echo __('services'); ?></a></li>
                <li><a href="/project/Doorstep/pages/login.php"><?php echo __('login'); ?></a></li>
                <li><a href="/project/Doorstep/pages/register.php" class="nav-btn"><?php echo __('register'); ?></a></li>
            <?php else: ?>
                <?php if ($is_user): ?>
                    <li><a href="/project/Doorstep/pages/services.php"><?php echo __('services'); ?></a></li>
                    <li><a href="/project/Doorstep/user/dashboard.php"><?php echo __('my_bookings'); ?></a></li>
                    <li><a href="/project/Doorstep/user/profile.php"><?php echo __('my_profile'); ?></a></li>
                <?php elseif ($is_agent): ?>
                    <li><a href="/project/Doorstep/agent/dashboard.php">My Assignments</a></li>
                <?php elseif ($is_admin): ?>
                    <li><a href="/project/Doorstep/admin/dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>

                <!-- Notification Bell -->
                <li style="position: relative;">
                    <div class="notification-bell" id="notifBell">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notifs) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifs); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-dropdown" id="notifDropdown" style="right: 0; left: auto; padding-bottom: 0;">
                        <div style="padding: 0.8rem; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 0.9rem; color: var(--dark); display: flex; justify-content: space-between; align-items: center;">
                            <?php echo __('notifications'); ?>
                            <?php if (count($notifs) > 0): ?>
                                <a href="/project/Doorstep/actions/clear_notifications.php" style="font-size: 0.75rem; color: var(--primary); font-weight: 600;"><?php echo __('clear_all'); ?></a>
                            <?php endif; ?>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php if (count($notifs) === 0): ?>
                                <div class="notification-item"><?php echo __('no_notifs'); ?></div>
                            <?php else: ?>
                                <?php foreach ($notifs as $n): ?>
                                    <div class="notification-item">
                                        <div style="line-height: 1.4; color: #4b5563;"><?php echo translate_msg($n['message']); ?></div>
                                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($n['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>

                <li><a href="/project/Doorstep/actions/logout.php" class="nav-btn" style="background: var(--danger);">Logout</a></li>
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
