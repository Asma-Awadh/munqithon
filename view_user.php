<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 4) {
    header("location:login.php");
    exit();
}

// Helper function to mask email addresses
function maskEmail($email) {
    if (empty($email)) return 'غير متوفر';
    
    $parts = explode('@', $email);
    if (count($parts) != 2) return $email; // Not a valid email format
    
    $name = $parts[0];
    $domain = $parts[1];
    
    // Show first and last character of the name part, mask the rest
    $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
    
    return $maskedName . '@' . $domain;
}

// Helper function to mask phone numbers
function maskPhone($phone) {
    if (empty($phone)) return 'غير متوفر';
    
    // Keep the first two and last two digits visible, mask the rest
    $length = strlen($phone);
    if ($length <= 4) return $phone; // Too short to mask effectively
    
    $visibleStart = 2;
    $visibleEnd = 2;
    
    $maskedPhone = substr($phone, 0, $visibleStart) . 
                   str_repeat('*', $length - $visibleStart - $visibleEnd) . 
                   substr($phone, -$visibleEnd);
    
    return $maskedPhone;
}

// التحقق من وجود معرف المستخدم
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:users.php");
    exit();
}

$user_id = $_GET['id'];

// استعلام لجلب بيانات المستخدم
$user_query = "SELECT p.*, r.name as role_name, c.name as city_name, b.name as bank_name
               FROM people p
               LEFT JOIN roles r ON p.role_id = r.id
               LEFT JOIN cities c ON p.city_id = c.id
               LEFT JOIN banks b ON p.bank_id = b.id
               WHERE p.id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// التحقق من وجود المستخدم
if (mysqli_num_rows($result) == 0) {
    header("location:users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>عرض بيانات المستخدم</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        
        .user-profile-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .profile-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .profile-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        
        .user-avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 5px solid #f0f0f0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .user-info-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .user-info-card h3 {
            color: #4CAF50;
            font-size: 1.2rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .info-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 120px;
            margin-left: 10px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #e8f5e9;
            color: #4CAF50;
        }
        
        .status-inactive {
            background-color: #ffebee;
            color: #f44336;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn-action {
            margin: 0 5px;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-back {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-edit {
            background-color: #FFC107;
            color: #212121;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        
        .btn-toggle {
            background-color: #9E9E9E;
            color: white;
        }
        
        .btn-toggle.active {
            background-color: #4CAF50;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #4CAF50;
            margin: 30px 0 20px;
            position: relative;
            padding-right: 15px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 25px;
            background-color: #4CAF50;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .info-item {
                flex-direction: column;
            }
            
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="user-profile-container">
                    <div class="profile-header">
                        <h2>بيانات المستخدم</h2>
                    </div>
                    
                    <div class="text-center mb-4">
                        <?php if (!empty($user['logo'])): ?>
                            <img src="images/<?= $user['logo'] ?>" alt="صورة المستخدم" class="user-avatar-large">
                        <?php else: ?>
                            <img src="images/default_avatar.png" alt="صورة افتراضية" class="user-avatar-large">
                        <?php endif; ?>
                        
                        <h3 class="mt-3"><?= $user['name'] ?></h3>
                        <p class="text-muted"><?= $user['role_name'] ?></p>
                        
                        <span class="status-badge <?= ($user['status'] == 1) ? 'status-active' : 'status-inactive' ?>">
                            <?= ($user['status'] == 1) ? 'مفعل' : 'معطل' ?>
                        </span>
                    </div>
                    
                    <h4 class="section-title">المعلومات الشخصية</h4>
                    <div class="user-info-card">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-user"></i> الاسم:</span>
                                    <span class="info-value"><?= $user['name'] ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-envelope"></i> البريد الإلكتروني:</span>
                                    <span class="info-value"><?= $user['email'] ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-phone"></i> رقم الهاتف:</span>
                                    <span class="info-value"><?= $user['phone'] ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-users"></i> نوع المستخدم:</span>
                                    <span class="info-value"><?= $user['role_name'] ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-building"></i> المدينة:</span>
                                    <span class="info-value"><?= $user['city_name'] ?? 'غير محدد' ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-map-marker-alt"></i> العنوان:</span>
                                    <span class="info-value"><?= $user['address'] ?? 'غير محدد' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="section-title">معلومات إضافية</h4>
                    <div class="user-info-card">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-info-circle"></i> نبذة تعريفية:</span>
                            <span class="info-value"><?= $user['bio'] ?? 'لا توجد نبذة تعريفية' ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['bank_id']) || !empty($user['account_number']) || !empty($user['iban'])): ?>
                    <h4 class="section-title">المعلومات البنكية</h4>
                    <div class="user-info-card">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-university"></i> البنك:</span>
                                    <span class="info-value"><?= $user['bank_name'] ?? 'غير محدد' ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-credit-card"></i> رقم الحساب:</span>
                                    <span class="info-value"><?= $user['account_number'] ?? 'غير محدد' ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-money-check"></i> رقم الآيبان:</span>
                                    <span class="info-value"><?= $user['iban'] ?? 'غير محدد' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="users.php" class="btn btn-action btn-back">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                        
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-action btn-edit">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        
                        <?php if ($user['id'] != $_SESSION['id']): ?>
                            <a href="users.php?toggle_id=<?= $user['id'] ?>" class="btn btn-action btn-toggle <?= ($user['status'] == 1) ? '' : 'active' ?>" onclick="return confirm('هل أنت متأكد من تغيير حالة هذا المستخدم؟')">
                                <?php if ($user['status'] == 1): ?>
                                    <i class="fas fa-ban"></i> تعطيل
                                <?php else: ?>
                                    <i class="fas fa-check"></i> تفعيل
                                <?php endif; ?>
                            </a>
                            
                            <a href="users.php?delete_id=<?= $user['id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.')">
                                <i class="fas fa-trash"></i> حذف
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
