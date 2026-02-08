<?php 
$page_title = "Home";
include 'includes/header.php'; 

// Fetch services for voice matching
$all_services = $pdo->query("SELECT id, service_name FROM services")->fetchAll(PDO::FETCH_KEY_PAIR);
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
        $stmt = $pdo->query("SELECT * FROM services LIMIT 3");
        while ($service = $stmt->fetch()) {
            echo "
            <div class='card shadow-hover'>
                <div style='font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;'><i class='fas fa-file-invoice'></i></div>
                <h3>{$service['service_name']}</h3>
                <p style='color: var(--secondary); margin: 1rem 0;'>{$service['description']}</p>
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
<div id="voiceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div class="card" style="width: 400px; text-align: center; padding: 3rem;">
        <div id="voiceWaves" style="display: flex; gap: 5px; justify-content: center; margin-bottom: 2rem;">
            <div class="wave" style="width: 5px; height: 20px; background: var(--primary); border-radius: 5px;"></div>
            <div class="wave" style="width: 5px; height: 40px; background: var(--primary); border-radius: 5px;"></div>
            <div class="wave" style="width: 5px; height: 20px; background: var(--primary); border-radius: 5px;"></div>
        </div>
        <h3 id="voiceStatus" style="margin-bottom: 1rem;"><?php echo __('listening'); ?></h3>
        <p id="voiceTranscript" style="color: var(--secondary); font-style: italic; min-height: 3em;"></p>
        <button id="closeVoice" class="btn" style="margin-top: 2rem; background: #f1f5f9;"><?php echo __('logout'); ?></button>
    </div>
</div>

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

    voiceBtn.onclick = () => {
        voiceModal.style.display = 'flex';
        startConversation();
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
}
</script>

<?php include 'includes/footer.php'; ?>
