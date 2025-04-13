<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول
if (!isset($_SESSION['id'])) {
    header("location:login.php");
    exit();
}

// التحقق من وجود معرف الطبيب في الرابط
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:doctors_list.php");
    exit();
}

$doctor_id = $_GET['id'];

// استعلام لجلب بيانات الطبيب
$doctor_query = "SELECT p.*, c.name as city_name, b.name as bank_name
                FROM people p
                LEFT JOIN cities c ON p.city_id = c.id
                LEFT JOIN banks b ON p.bank_id = b.id
                WHERE p.id = ? AND p.role_id = 2 AND p.status = 1";
$stmt = mysqli_prepare($db, $doctor_query);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// التحقق من وجود الطبيب
if (mysqli_num_rows($result) == 0) {
    header("location:doctors_list.php");
    exit();
}

$doctor = mysqli_fetch_assoc($result);

// استعلام لجلب تقييمات الطبيب (إذا كان هناك جدول للتقييمات)
$has_reviews = false;
$avg_rating = 0;
$total_reviews = 0;
$reviews_result = null;

// التحقق من وجود جدول التقييمات
$check_table = mysqli_query($db, "SHOW TABLES LIKE 'reviews'");
if(mysqli_num_rows($check_table) > 0) {
    // التحقق من وجود العمود doctor_id في جدول التقييمات
    $check_column = mysqli_query($db, "SHOW COLUMNS FROM reviews LIKE 'doctor_id'");
    if(mysqli_num_rows($check_column) > 0) {
        $has_reviews = true;
        $reviews_query = "SELECT r.*, p.name as client_name, p.logo as client_logo
                         FROM reviews r
                         JOIN people p ON r.client_id = p.id
                         WHERE r.doctor_id = ?
                         ORDER BY r.created_at DESC";
        $stmt = mysqli_prepare($db, $reviews_query);
        mysqli_stmt_bind_param($stmt, "i", $doctor_id);
        mysqli_stmt_execute($stmt);
        $reviews_result = mysqli_stmt_get_result($stmt);
        
        // حساب متوسط التقييم
        $total_reviews = mysqli_num_rows($reviews_result);
        if ($total_reviews > 0) {
            $rating_sum = 0;
            while ($review = mysqli_fetch_assoc($reviews_result)) {
                $rating_sum += $review['rating'];
            }
            $avg_rating = round($rating_sum / $total_reviews, 1);
            mysqli_data_seek($reviews_result, 0);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>الملف الشخصي للطبيب - <?= $doctor['name'] ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        
        .profile-container {
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
        
        .doctor-img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin: 0 auto 20px;
            display: block;
        }
        
        .doctor-info {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .doctor-info h3 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .doctor-info p {
            margin-bottom: 10px;
        }
        
        .doctor-info strong {
            color: #333;
        }
        
        .rating {
            color: #FFC107;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .btn-contact, .btn-appointment {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-contact {
            background: #4CAF50;
            color: white;
        }
        
        .btn-contact:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .btn-appointment {
            background: #FFC107;
            color: #333;
        }
        
        .btn-appointment:hover {
            background: #FFA000;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 18px;
        }
        
        .bank-info {
            background-color: rgba(255, 193, 7, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-right: 4px solid #FFC107;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-container">
                    <div class="profile-header">
                        <h2>الملف الشخصي للطبيب</h2>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/<?= !empty($doctor['logo']) ? $doctor['logo'] : 'default-doctor.jpg' ?>" alt="<?= $doctor['name'] ?>" class="doctor-img">
                            <h3 class="mt-3"><?= $doctor['name'] ?></h3>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?= $doctor['city_name'] ?>
                            </p>
                            
                            <?php if($has_reviews): ?>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $avg_rating): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="text-muted">(<?= $total_reviews ?> تقييم)</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="action-buttons">
                                <a href="chat.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-contact">
                                    <i class="fas fa-comments"></i> مراسلة
                                </a>
                                <a href="book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-appointment">
                                    <i class="fas fa-calendar-plus"></i> حجز موعد
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="doctor-info">
                                <h3><i class="fas fa-user-md"></i> نبذة عن الطبيب</h3>
                                <p><?= !empty($doctor['bio']) ? $doctor['bio'] : 'لا توجد معلومات متاحة عن هذا الطبيب.' ?></p>
                            </div>
                            
                            <div class="doctor-info">
                                <h3><i class="fas fa-info-circle"></i> معلومات الطبيب</h3>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <strong>البريد الإلكتروني:</strong>
                                        <p><?= $doctor['email'] ?></p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                    <div>
                                        <strong>رقم الهاتف:</strong>
                                        <p><?= $doctor['phone'] ?></p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <div>
                                        <strong>العنوان:</strong>
                                        <p><?= $doctor['address'] ?></p>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-city"></i>
                                    </div>
                                    <div>
                                        <strong>المدينة:</strong>
                                        <p><?= $doctor['city_name'] ?></p>
                                    </div>
                                </div>
                                
                                <?php if(!empty($doctor['bank_name']) && !empty($doctor['account_number'])): ?>
                                <div class="bank-info">
                                    <h5><i class="fas fa-university"></i> معلومات البنك</h5>
                                    <p><strong>اسم البنك:</strong> <?= $doctor['bank_name'] ?></p>
                                    <p><strong>رقم الحساب:</strong> <?= $doctor['account_number'] ?></p>
                                    <?php if(!empty($doctor['iban'])): ?>
                                    <p><strong>رقم الآيبان:</strong> <?= $doctor['iban'] ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- قسم الخدمات المقدمة -->
                <div class="profile-container">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-hand-holding-medical"></i> الخدمات المقدمة
                    </h3>
                    
                    <div class="row">
                      
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <i class="fas fa-comments fa-3x text-info mb-3"></i>
                                    <h4>استشارة عبر الدردشة</h4>
                                    <p>يمكنك التواصل مع الطبيب عبر نظام الدردشة</p>
                                    <a href="chat.php?doctor_id=<?= $doctor_id ?>" class="btn btn-outline-info">
                                        بدء محادثة
                                    </a>
                                </div>
                            </div>
                        </div>
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

