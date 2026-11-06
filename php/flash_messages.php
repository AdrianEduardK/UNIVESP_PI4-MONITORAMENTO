<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_msg'] = $message;
    $_SESSION['flash_type'] = $type;
}

function displayFlashMessages() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = htmlspecialchars((string)$_SESSION['flash_msg'], ENT_QUOTES, 'UTF-8');
        $type = $_SESSION['flash_type'] ?? 'success';
        
        // Cores vibrantes modernas
        $bgColor = $type === 'error' ? '#ef4444' : '#10b981';
        $icon = $type === 'error' ? '❌' : '✅';
        
        echo "
        <div id='toast-msg' style='
            position: fixed; 
            top: 30px; 
            right: 30px; 
            z-index: 99999; 
            background: $bgColor; 
            color: white; 
            padding: 16px 24px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            font-size: 15px;
            font-weight: 600; 
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(150%); 
            animation: slideInToast 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards, fadeOutToast 0.5s 4s forwards;'>
            <span style='font-size: 20px;'>$icon</span>
            <span>$msg</span>
        </div>
        <style>
            @keyframes slideInToast { 
                to { transform: translateX(0); } 
            }
            @keyframes fadeOutToast { 
                to { opacity: 0; visibility: hidden; transform: translateY(-20px); } 
            }
        </style>
        ";
        
        unset($_SESSION['flash_msg']);
        unset($_SESSION['flash_type']);
    }
}
?>
