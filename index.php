<?php 
$page_title = "Home";
include 'includes/header.php'; 
?>

<header class="hero">
    <div class="container">
        <h1>Government Services, <br>Delivered at Your Doorstep.</h1>
        <p>Book a professional visit to help you with PAN, Aadhaar, Ration Card, and more without visiting any government office.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="pages/services.php" class="btn btn-primary">Browse Services</a>
            <a href="pages/register.php" class="btn" style="background: white; color: var(--primary);">Get Started</a>
        </div>
    </div>
</header>

<section class="container">
    <h2 style="text-align: center; margin-bottom: 3rem;">Popular Services</h2>
    <div class="services-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM services LIMIT 3");
        while ($service = $stmt->fetch()) {
            echo "
            <div class='card'>
                <div style='font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;'><i class='fas fa-file-invoice'></i></div>
                <h3>{$service['service_name']}</h3>
                <p style='color: var(--secondary); margin: 1rem 0;'>{$service['description']}</p>
                <div style='display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;'>
                    <span style='font-weight: 700; color: var(--primary);'>".CURRENCY."{$service['service_charge']}</span>
                    <a href='pages/book_service.php?id={$service['id']}' class='btn btn-primary' style='padding: 0.5rem 1rem;'>Book Now</a>
                </div>
            </div>";
        }
        ?>
    </div>
</section>

<section class="container card" style="background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; margin-top: 5rem; text-align: center;">
    <h2>How it Works?</h2>
    <div class="services-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: 3rem; border: none;">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-search"></i></div>
            <h4>1. Pick Service</h4>
            <p style="opacity: 0.8; font-size: 0.9rem;">Choose from available government services.</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-calendar-alt"></i></div>
            <h4>2. Select Slot</h4>
            <p style="opacity: 0.8; font-size: 0.9rem;">Pick a date and time for our agent to visit.</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-user-tie"></i></div>
            <h4>3. Agent Visit</h4>
            <p style="opacity: 0.8; font-size: 0.9rem;">Our agent visits your home for document collection.</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-check-circle"></i></div>
            <h4>4. Done</h4>
            <p style="opacity: 0.8; font-size: 0.9rem;">Receive your certificate/card at your home.</p>
        </div>
    </div>
</section>

<?php if (!$logged_in): ?>
<section class="container" style="margin-top: 5rem; text-align: center;">
    <div class="card" style="padding: 3rem; background: #fff;">
        <h2>Want to join our network?</h2>
        <p style="color: var(--secondary); margin: 1rem 0 2rem;">Become a certified doorstep agent and help citizens while earning.</p>
        <a href="pages/doorstepcare.php" class="btn" style="border: 1px solid var(--primary); color: var(--primary);">Apply at DoorstepCare</a>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
