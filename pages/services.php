<?php 
$page_title = "Services";
include '../includes/header.php'; 
?>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 1rem;">Available doorstep services</h1>
    <p style="text-align: center; color: var(--secondary); margin-bottom: 3rem;">Select a service to see required documents and charges.</p>

    <div class="services-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM services ORDER BY service_name ASC");
        while ($service = $stmt->fetch()) {
            echo "
            <div class='card' style='display: flex; flex-direction: column; justify-content: space-between;'>
                <div>
                    <div style='font-size: 2rem; color: var(--primary); margin-bottom: 1rem;'><i class='fas fa-file-contract'></i></div>
                    <h3 style='margin-bottom: 0.5rem;'>{$service['service_name']}</h3>
                    <p style='color: var(--secondary); font-size: 0.9rem; margin-bottom: 1.5rem;'>{$service['description']}</p>
                    
                    <div style='background: #f1f5f9; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;'>
                        <h4 style='font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.5rem;'>Required Documents</h4>
                        <ul style='list-style: none; padding: 0; font-size: 0.85rem;'>";
                            $docs = explode(',', $service['required_documents']);
                            foreach($docs as $doc) {
                                echo "<li style='margin-bottom: 0.2rem;'><i class='fas fa-check-circle' style='color: var(--success); font-size: 0.7rem;'></i> " . trim($doc) . "</li>";
                            }
            echo "      </ul>
                    </div>
                </div>
                
                <div style='border-top: 1px solid #e2e8f0; padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;'>
                    <div>
                        <div style='font-size: 1.25rem; font-weight: 700; color: var(--primary);'>".CURRENCY."{$service['service_charge']}</div>
                        <div style='font-size: 0.7rem; color: var(--secondary);'>~{$service['estimated_days']} Days</div>
                    </div>
                    <a href='book_service.php?id={$service['id']}' class='btn btn-primary'>Book Visit</a>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
