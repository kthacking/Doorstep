    <footer style="margin-top: 5rem; padding: 3rem 0; border-top: 1px solid var(--glass-border); text-align: center; color: var(--secondary);">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        <p style="font-size: 0.8rem; margin-top: 0.5rem;">Built for Doorstep Government Service Assistance</p>
        <?php if (!isset($_SESSION['role'])): ?>
            <div style="margin-top: 1.5rem;">
                <a href="/project/foodapp/pages/doorstepcare.php" style="color: var(--primary); font-size: 0.85rem; text-decoration: none;">Join as an Agent (DoorstepCare)</a>
            </div>
        <?php endif; ?>
    </footer>
</body>
</html>
