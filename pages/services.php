<?php 
$page_title = "Services";
include '../includes/header.php'; 
?>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 1rem;"><?php echo __('available_services'); ?></h1>
    <p style="text-align: center; color: var(--secondary); margin-bottom: 3rem;"><?php echo __('services_subtitle'); ?></p>

    <div class="services-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM services ORDER BY service_name ASC");
        while ($service = $stmt->fetch()) {
            $slug = strtolower(str_replace([' ', '  '], '_', trim($service['service_name'])));
            if ($slug == 'pan_card_registration') $slug = 'pan_card';
            
            $name = __($slug);
            $desc = __($slug . '_desc');
            
            echo "
            <div class='card' style='display: flex; flex-direction: column; justify-content: space-between;'>
                <div>
                    <div style='font-size: 2rem; color: var(--primary); margin-bottom: 1rem;'><i class='fas fa-file-contract'></i></div>
                    <h3 style='margin-bottom: 0.5rem;'>{$name}</h3>
                    <p style='color: var(--secondary); font-size: 0.9rem; margin-bottom: 1.5rem;'>{$desc}</p>
                    
                    <div style='background: #f1f5f9; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;'>
                        <h4 style='font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.5rem;'>".__('service_checklist')."</h4>
                        <ul style='list-style: none; padding: 0; font-size: 0.85rem;'>";
                            $docs = explode(',', $service['required_documents']);
                            foreach($docs as $doc) {
                                // Try to localize document names if they exist in lang.php
                                $d_slug = strtolower(str_replace([' ', '  ', '(', ')'], '_', trim($doc)));
                                $d_name = __($d_slug);
                                if ($d_name == $d_slug) $d_name = $doc; // Fallback to DB text
                                
                                echo "<li style='margin-bottom: 0.2rem;'><i class='fas fa-check-circle' style='color: var(--success); font-size: 0.7rem;'></i> " . $d_name . "</li>";
                            }
            echo "      </ul>
                    </div>
                </div>
                
                <div style='border-top: 1px solid #e2e8f0; padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;'>
                    <div>
                        <div style='font-size: 1.25rem; font-weight: 700; color: var(--primary);'>".CURRENCY."{$service['service_charge']}</div>
                        <div style='font-size: 0.7rem; color: var(--secondary);'>~{$service['estimated_days']} ".__('days')."</div>
                    </div>
                    <a href='book_service.php?id={$service['id']}' class='btn btn-primary'>".__('book_now')."</a>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
