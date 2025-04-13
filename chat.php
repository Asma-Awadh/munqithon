<?php
include("conn.php");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];
$role_id = $_SESSION['role'];

// Create messages table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (sender_id),
    INDEX (receiver_id)
)";
mysqli_query($db, $sql_create_table);

// Handle sending messages
if (isset($_POST['send_message']) && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    
    if (!empty($message)) {
        $stmt = mysqli_prepare($db, "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $receiver_id, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to avoid form resubmission
            header("Location: chat.php?user=" . $receiver_id);
            exit();
        }
    }
}

// Get list of users to chat with based on role
if ($role_id == 2) { // Doctor
    // Doctors see clients who have appointments with them or have sent them messages
    $sql = "SELECT DISTINCT p.id, p.name, p.logo, p.role_id, r.name as role_name
            FROM people p
            JOIN roles r ON p.role_id = r.id
            LEFT JOIN appointments a ON (a.client_id = p.id AND a.doctor_id = ?)
            LEFT JOIN messages m ON (m.sender_id = p.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = p.id)
            WHERE (p.role_id = 1 OR p.role_id = 3) 
            AND (a.id IS NOT NULL OR m.id IS NOT NULL)
            ORDER BY p.name";
    
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);
} elseif ($role_id == 1 || $role_id == 3) { // Cliente o Refugio
    // Consulta modificada para mostrar todos los doctores, no solo aquellos con citas
    $sql = "SELECT DISTINCT p.id, p.name, p.logo, p.role_id, r.name as role_name
            FROM people p
            JOIN roles r ON p.role_id = r.id
            LEFT JOIN appointments a ON (a.doctor_id = p.id AND a.client_id = ?)
            LEFT JOIN messages m ON (m.sender_id = p.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = p.id)
            WHERE p.role_id = 2 AND p.status = 1
            ORDER BY 
                CASE WHEN a.id IS NOT NULL OR m.id IS NOT NULL THEN 0 ELSE 1 END,
                p.name";
    
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);
} else { // Admin
    // Admins see all users
    $sql = "SELECT p.id, p.name, p.logo, p.role_id, r.name as role_name
            FROM people p
            JOIN roles r ON p.role_id = r.id
            WHERE p.id != ?
            ORDER BY p.name";
    
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
$users_result = mysqli_stmt_get_result($stmt);

// Get selected user for chat
$selected_user = null;
$messages = null;

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $chat_user_id = $_GET['user'];
    
    // Get user details
    $stmt = mysqli_prepare($db, "SELECT p.*, r.name as role_name FROM people p JOIN roles r ON p.role_id = r.id WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $chat_user_id);
    mysqli_stmt_execute($stmt);
    $selected_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($selected_user) {
        // Get messages between users
        $stmt = mysqli_prepare($db, "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
        mysqli_stmt_bind_param($stmt, "iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
        mysqli_stmt_execute($stmt);
        $messages_result = mysqli_stmt_get_result($stmt);
        
        // Mark messages as read
        $stmt = mysqli_prepare($db, "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        mysqli_stmt_bind_param($stmt, "ii", $chat_user_id, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

// Count unread messages for each user
function getUnreadCount($db, $sender_id, $receiver_id) {
    $stmt = mysqli_prepare($db, "SELECT COUNT(*) as count FROM messages WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    mysqli_stmt_bind_param($stmt, "ii", $sender_id, $receiver_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $result['count'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>نظام الدردشة</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('images/login_bg.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            padding: 20px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background: #4CAF50;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .chat-container {
            display: flex;
            height: 70vh;
        }
        
        .users-list {
            width: 30%;
            border-left: 1px solid #ddd;
            overflow-y: auto;
        }
        
        .chat-area {
            width: 70%;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background: rgba(240, 240, 240, 0.5);
        }
        
        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
        }
        
        .user-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .user-item:hover, .user-item.active {
            background: rgba(76, 175, 80, 0.1);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-left: 15px;
            object-fit: cover;
        }
        
        .user-info {
            flex-grow: 1;
        }
        
        .user-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: #666;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            max-width: 70%;
        }
        
        .message.sent {
            align-self: flex-end;
        }
        
        .message.received {
            align-self: flex-start;
        }
        
        .message-content {
            padding: 10px 15px;
            border-radius: 20px;
            margin-bottom: 5px;
            position: relative;
        }
        
        .message.sent .message-content {
            background: #4CAF50;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message.received .message-content {
            background: #f1f1f1;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #888;
            align-self: flex-end;
        }
        
        .message.sent .message-time {
            text-align: left;
        }
        
        .message.received .message-time {
            text-align: right;
        }
        
        .badge-unread {
            background-color: #ff5722;
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 0.7rem;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            
            .users-list, .chat-area {
                width: 100%;
            }
            
            .users-list {
                border-left: none;
                border-bottom: 1px solid #ddd;
                max-height: 300px;
            }
            
            .chat-messages {
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-white mb-4">نظام الدردشة</h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-comments"></i> المحادثات
                    </div>
                    <div class="chat-container">
                        <div class="users-list">
                            <?php if (mysqli_num_rows($users_result) > 0): ?>
                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                    <?php $unread_count = getUnreadCount($db, $user['id'], $user_id); ?>
                                    <div class="user-item <?php echo (isset($_GET['user']) && $_GET['user'] == $user['id']) ? 'active' : ''; ?>" onclick="location.href='chat.php?user=<?php echo $user['id']; ?>'">
                                        <img src="images/<?php echo $user['logo'] ? $user['logo'] : 'default.png'; ?>" class="user-avatar" alt="صورة المستخدم">
                                        <div class="user-info">
                                            <div class="user-name"><?php echo $user['name']; ?></div>
                                            <div class="user-role"><?php echo $user['role_name']; ?></div>
                                        </div>
                                        <?php if ($unread_count > 0): ?>
                                            <span class="badge-unread"><?php echo $unread_count; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>لا يوجد مستخدمين للدردشة معهم</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-area">
                            <?php if ($selected_user): ?>
                                <div class="chat-messages" id="chatMessages">
                                    <?php if (mysqli_num_rows($messages_result) > 0): ?>
                                        <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                            <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                                                <div class="message-content">
                                                    <?php echo htmlspecialchars($message['message']); ?>
                                                </div>
                                                <div class="message-time">
                                                    <?php echo date('h:i A | j M Y', strtotime($message['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fas fa-comment-slash"></i>
                                            <p>لا توجد رسائل سابقة. ابدأ المحادثة الآن!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="chat-input">
                                    <form method="post" action="">
                                        <input type="hidden" name="receiver_id" value="<?php echo $selected_user['id']; ?>">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="message" placeholder="اكتب رسالتك هنا..." required>
                                            <div class="input-group-append">
                                                <button class="btn btn-success" type="submit" name="send_message">
                                                    <i class="fas fa-paper-plane"></i> إرسال
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <p>اختر مستخدم من القائمة لبدء المحادثة</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script>
        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
        
        // Scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });
        
        // Refresh chat every 10 seconds
        <?php if ($selected_user): ?>
        setInterval(function() {
            // Use fetch to get new messages without reloading the page
            fetch('get_messages.php?user=<?php echo $selected_user['id']; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('chatMessages').innerHTML = data.html;
                        scrollToBottom();
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 10000);
        <?php endif; ?>
    </script>
</body>
</html>
