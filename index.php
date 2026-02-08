<?php 
$page_title = "Home";
include 'includes/header.php'; 

// Fetch and Localize services for voice matching
$all_services = [];
$get_services = $pdo->query("SELECT id, service_name FROM services");
while($row = $get_services->fetch()) {
    $slug = strtolower(str_replace([' ', '  '], '_', trim($row['service_name'])));
    // Hardcoded mapping for base services if they don't follow the slug pattern
    if ($slug == 'pan_card_registration') $slug = 'pan_card';
    if ($slug == 'birth_certificate_registration') $slug = 'birth_certificate';
    
    $all_services[$row['id']] = __($slug);
}
?>

<header class="hero">
    <div class="container">
        <h1><?php echo __('hero_title'); ?></h1>
        <p><?php echo __('hero_subtitle'); ?></p>
        <div style="display: flex; gap: 1rem; justify-content: center; align-items: center; flex-wrap: wrap;">
            <a href="pages/services.php" class="btn btn-primary"><?php echo __('browse_services'); ?></a>
            <?php if (!$logged_in): ?>
                <a href="pages/register.php" class="btn" style="background: white; color: var(--primary);"><?php echo __('get_started'); ?></a>
            <?php endif; ?>
            
            <!-- Voice to Apply Button -->
            <button id="voiceApplyBtn" class="btn" style="background: #ef4444; color: white; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);">
                <i class="fas fa-microphone"></i> <?php echo __('voice_apply'); ?>
            </button>
        </div>
    </div>
</header>

<section class="container">
    <h2 style="text-align: center; margin-bottom: 3rem;"><?php echo __('popular_services'); ?></h2>
    <div class="services-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM services LIMIT 6");
        while ($service = $stmt->fetch()) {
            $slug = strtolower(str_replace([' ', '  '], '_', trim($service['service_name'])));
            if ($slug == 'pan_card_registration') $slug = 'pan_card';
            
            $name = __($slug);
            $desc = __($slug . '_desc');
            
            echo "
            <div class='card shadow-hover'>
                <div style='font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;'><i class='fas fa-file-invoice'></i></div>
                <h3>{$name}</h3>
                <p style='color: var(--secondary); margin: 1rem 0;'>{$desc}</p>
                <div style='display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;'>
                    <span style='font-weight: 700; color: var(--primary);'>".CURRENCY."{$service['service_charge']}</span>
                    <a href='pages/book_service.php?id={$service['id']}' class='btn btn-primary' style='padding: 0.5rem 1rem;'>".__('book_now')."</a>
                </div>
            </div>";
        }
        ?>
    </div>
</section>

<section class="container card" style="background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; margin-top: 5rem; text-align: center; border: none;">
    <h2><?php echo __('how_it_works'); ?></h2>
    <div class="services-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: 3rem; border: none;">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-search"></i></div>
            <h4><?php echo __('step_1'); ?></h4>
            <p style="opacity: 0.8; font-size: 0.9rem;"><?php echo __('step_1_desc'); ?></p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-calendar-alt"></i></div>
            <h4><?php echo __('step_2'); ?></h4>
            <p style="opacity: 0.8; font-size: 0.9rem;"><?php echo __('step_2_desc'); ?></p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-user-tie"></i></div>
            <h4><?php echo __('step_3'); ?></h4>
            <p style="opacity: 0.8; font-size: 0.9rem;"><?php echo __('step_3_desc'); ?></p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-check-circle"></i></div>
            <h4><?php echo __('step_4'); ?></h4>
            <p style="opacity: 0.8; font-size: 0.9rem;"><?php echo __('step_4_desc'); ?></p>
        </div>
    </div>
</section>

<!-- Conversational Voice Modal -->
<div id="voiceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div class="card" style="width: 400px; text-align: center; padding: 3rem;">
        <div id="voiceWaves" style="display: flex; gap: 5px; justify-content: center; margin-bottom: 2rem;">
            <div class="wave" style="width: 5px; height: 20px; background: var(--primary); border-radius: 5px;"></div>
            <div class="wave" style="width: 5px; height: 40px; background: var(--primary); border-radius: 5px;"></div>
            <div class="wave" style="width: 5px; height: 20px; background: var(--primary); border-radius: 5px;"></div>
        </div>
        <h3 id="voiceStatus" style="margin-bottom: 1rem;"><?php echo __('listening'); ?></h3>
        <p id="voiceTranscript" style="color: var(--secondary); font-style: italic; min-height: 3em;"></p>
        <button id="closeVoice" class="btn" style="margin-top: 2rem; background: #f1f5f9;"><?php echo __('cancel'); ?></button>
    </div>
</div>

<!-- Floating Voice Action -->
<button id="floatingVoiceBtn" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: white; border: none; font-size: 1.5rem; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); cursor: pointer; z-index: 1000; transition: 0.3s; display: flex; align-items: center; justify-content: center;">
    <i class="fas fa-microphone"></i>
    <span style="position: absolute; right: 70px; background: var(--primary); padding: 8px 15px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; white-space: nowrap; pointer-events: none; opacity: 0; transition: 0.3s; transform: translateX(10px);"><?php echo __('voice_apply'); ?></span>
</button>

<style>
@keyframes pulse {
    0% { transform: scaleY(0.5); }
    50% { transform: scaleY(1.5); }
    100% { transform: scaleY(0.5); }
}
.wave { animation: pulse 1s infinite ease-in-out; }
.wave:nth-child(2) { animation-delay: 0.2s; }
.wave:nth-child(3) { animation-delay: 0.4s; }
</style>

<script>
const services = <?php echo json_encode($all_services); ?>;
const voiceBtn = document.getElementById('voiceApplyBtn');
const floatingBtn = document.getElementById('floatingVoiceBtn');
const floatingLabel = floatingBtn.querySelector('span');
const voiceModal = document.getElementById('voiceModal');
const vStatus = document.getElementById('voiceStatus');
const vTranscript = document.getElementById('voiceTranscript');
const closeVoice = document.getElementById('closeVoice');

let bookingData = { service: '', date: '', time: '' };
let currentStep = 'service';

if ('webkitSpeechRecognition' in window) {
    const recognition = new webkitSpeechRecognition();
    const synth = window.speechSynthesis;
    
    recognition.lang = '<?php echo $lang == 'ta' ? 'ta-IN' : 'en-IN'; ?>';
    recognition.continuous = false;
    recognition.interimResults = false;

    function speak(text, callback) {
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = recognition.lang;
        utter.onend = callback;
        synth.speak(utter);
    }

    const startVoiceFlow = () => {
        voiceModal.style.display = 'flex';
        startConversation();
    };

    voiceBtn.onclick = startVoiceFlow;
    floatingBtn.onclick = startVoiceFlow;

    floatingBtn.onmouseover = () => {
        floatingLabel.style.opacity = '1';
        floatingLabel.style.transform = 'translateX(0)';
    };
    floatingBtn.onmouseout = () => {
        floatingLabel.style.opacity = '0';
        floatingLabel.style.transform = 'translateX(10px)';
    };

    closeVoice.onclick = () => {
        voiceModal.style.display = 'none';
        recognition.stop();
        synth.cancel();
    };

    function startConversation() {
        currentStep = 'service';
        speak("<?php echo __('v_prompt_service'); ?>", () => recognition.start());
    }

    recognition.onresult = (event) => {
        const text = event.results[0][0].transcript.toLowerCase();
        vTranscript.innerText = `"${text}"`;
        
        if (currentStep === 'service') {
            let foundId = null;
            for (let id in services) {
                if (text.includes(services[id].toLowerCase())) {
                    foundId = id;
                    bookingData.service = foundId;
                    break;
                }
            }
            
            if (foundId) {
                currentStep = 'date';
                speak("<?php echo __('v_prompt_date'); ?>", () => recognition.start());
            } else {
                speak("<?php echo __('v_not_found'); ?>", () => recognition.start());
            }
        } 
        else if (currentStep === 'date') {
            bookingData.date = text;
            currentStep = 'time';
            speak("<?php echo __('v_prompt_time'); ?>", () => recognition.start());
        } 
        else if (currentStep === 'time') {
            bookingData.time = text;
            speak("<?php echo __('v_success'); ?>", () => {
                const url = new URL('pages/book_service.php', window.location.origin + '/project/Doorstep/');
                url.searchParams.set('id', bookingData.service);
                url.searchParams.set('v_date', bookingData.date);
                url.searchParams.set('v_time', bookingData.time);
                window.location.href = url.href;
            });
        }
    };

    recognition.onerror = () => {
        speak("<?php echo __('v_not_found'); ?>", () => recognition.start());
    };
} else {
    voiceBtn.style.display = 'none';
    floatingBtn.style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
