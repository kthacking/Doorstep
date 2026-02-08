<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_SESSION['lang'] ?? 'en';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
}

$translations = [
    'en' => [
        'home' => 'Home',
        'services' => 'Services',
        'login' => 'Login',
        'register' => 'Register',
        'my_bookings' => 'My Bookings',
        'my_profile' => 'My Profile',
        'logout' => 'Logout',
        'book_now' => 'Book Now',
        'voice_booking' => 'Voice Booking',
        'listening' => 'Listening...',
        'voice_help' => 'Say things like "Book Aadhaar Update" or "Check my status"',
        'service_checklist' => 'Checklist',
        'reminder' => 'Smart Reminder',
        'keep_ready' => 'Please keep original documents ready.',
        'confirm_booking' => 'Confirm Booking',
        'visit_address' => 'Visit Address',
        'visit_date' => 'Preferred Visit Date',
        'time_slot' => 'Preferred Time Slot',
        'agent_gender' => 'Preferred Agent Gender',
    ],
    'hi' => [ // Hindi
        'home' => 'होम',
        'services' => 'सेवाएं',
        'login' => 'लॉगिन',
        'register' => 'रजिस्टर',
        'my_bookings' => 'मेरी बुकिंग',
        'my_profile' => 'मेरी प्रोफाइल',
        'logout' => 'लॉगआउट',
        'book_now' => 'अभी बुक करें',
        'voice_booking' => 'वॉयस बुकिंग',
        'listening' => 'सुन रहा हूँ...',
        'voice_help' => '"आधार अपडेट बुक करें" जैसा कुछ कहें',
        'service_checklist' => 'चेकलिस्ट',
        'reminder' => 'स्मार्ट रिमाइंडर',
        'keep_ready' => 'कृपया मूल दस्तावेज़ तैयार रखें।',
        'confirm_booking' => 'बुकिंग की पुष्टि करें',
        'visit_address' => 'पता',
        'visit_date' => 'यात्रा की तारीख',
        'time_slot' => 'समय स्लॉट',
        'agent_gender' => 'एजेंट का लिंग',
    ],
    'ta' => [ // Tamil
        'home' => 'முகப்பு',
        'services' => 'சேவைகள்',
        'login' => 'உள்நுழை',
        'register' => 'பதிவு செய்',
        'my_bookings' => 'எனது முன்பதிவுகள்',
        'my_profile' => 'எனது சுயவிவரம்',
        'logout' => 'வெளியேறு',
        'book_now' => 'இப்போதே பதிவு செய்',
        'voice_booking' => 'குரல் முன்பதிவு',
        'listening' => 'கேட்கிறது...',
        'voice_help' => '"ஆதார் புதுப்பிப்பை பதிவு செய்யவும்" என்று சொல்லுங்கள்',
        'service_checklist' => 'சரிபார்ப்பு பட்டியல்',
        'reminder' => 'ஸ்மார்ட் நினைவூட்டல்',
        'keep_ready' => 'அசல் ஆவணங்களை தயார் நிலையில் வைத்திருங்கள்.',
        'confirm_booking' => 'முன்பதிவை உறுதிப்படுத்து',
        'visit_address' => 'முகவரி',
        'visit_date' => 'சந்திப்பு தேதி',
        'time_slot' => 'நேரம்',
        'agent_gender' => 'ஏஜென்ட் பாலினம்',
    ]
];

function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
