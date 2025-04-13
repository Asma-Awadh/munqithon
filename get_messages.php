<?php
include("conn.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_GET['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['id'];
$chat_user_id = $_GET['user'];

// Get messages between users
$stmt = mysqli_prepare($db, "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
mysqli_stmt_execute($stmt);
$messages_result = mysqli_stmt_get_result($stmt);

// Mark messages as read
$stmt = mysqli_prepare($db, "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
mysqli_stmt_bind_param($stmt, "ii", $chat_user_id, $user_id);
mysqli_stmt_execute($stmt);

// Generate HTML for messages
$html = '';
if (mysqli_num_rows($messages_result) > 0) {
    while ($message = mysqli_fetch_assoc($messages_result)) {
        $class = ($message['sender_id'] == $user_id) ? 'sent' : 'received';
        $html .= '<div class="message ' . $class . '">';
        $html .= '<div class="message-content">' . htmlspecialchars($message['message']) . '</div>';
        $html .= '<div class="message-time">' . date('h:i A | j M Y', strtotime($message['created_at'])) . '</div>';
        $html .= '</div>';
    }
} else {
    $html .= '<div class="empty-state">';
    $html .= '<i class="fas fa-comment-slash"></i>';
    $html .= '<p>لا توجد رسائل سابقة. ابدأ المحادثة الآن!</p>';
    $html .= '</div>';
}

echo json_encode(['success' => true, 'html' => $html]);
