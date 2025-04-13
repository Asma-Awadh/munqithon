<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// إلغاء الموعد
if (isset($_POST['cancel_appointment']) && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
   
    // التحقق من أن الموعد ينتمي للمستخدم الحالي
    $check_query = "SELECT * FROM appointments WHERE id = ? AND client_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($check_result) > 0) {
        $appointment_data = mysqli_fetch_assoc($check_result);
       
        // تحديث حالة الموعد إلى ملغي
        $update_query = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $appointment_id);
       
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم إلغاء الموعد بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء إلغاء الموعد، يرجى المحاولة مرة أخرى";
        }
    } else {
        $error_message = "لا يمكن العثور على الموعد المطلوب";
    }
}

// إضافة تقييم للموعد
if (isset($_POST['add_review'])) {
    $appointment_id = $_POST['appointment_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
   
    // التحقق من أن الموعد ينتمي للمستخدم الحالي وتم إكماله
    $check_query = "SELECT * FROM appointments WHERE id = ? AND client_id = ? AND status = 'completed'";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($check_result) > 0) {
        // إضافة أو تحديث التقييم
        $review_query = "INSERT INTO reviews (appointment_id, user_id, rating, review, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE rating = ?, review = ?, updated_at = NOW()";
        $stmt = mysqli_prepare($db, $review_query);
        mysqli_stmt_bind_param($stmt, "iiissi", $appointment_id, $user_id, $rating, $review_text, $rating, $review_text);
       
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم إضافة التقييم بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء إضافة التقييم، يرجى المحاولة مرة أخرى";
        }
    } else {
        $error_message = "لا يمكنك إضافة تقييم لهذا الموعد";
    }
}

// تحقق من نوع الجدول المرتبط بالحجز
$check_schedule_type_query = "SELECT cs.*, 
                            CASE 
                                WHEN cs.doctor_id IS NOT NULL THEN 'doctor'
                                WHEN cs.clinic_id IS NOT NULL THEN 'clinic'
                                ELSE NULL
                            END as schedule_type
                            FROM clinic_schedules cs
                            JOIN bookings b ON b.schedule_id = cs.id
                            WHERE b.id = ?";

// ثم استخدم هذه المعلومات لتحديد نوع الحجز

// استعلام حجوزات الأطباء
$doctor_appointments_query = "SELECT b.*, p.name as doctor_name, p.logo as doctor_logo, 
                            c.name as city_name, 
                            cs.date, cs.start_time, cs.end_time
                            FROM bookings b
                            JOIN clinic_schedules cs ON b.schedule_id = cs.id
                            JOIN people p ON cs.clinic_id = p.id
                            JOIN cities c ON p.city_id = c.id
                            WHERE b.client_id = ? AND b.type = 'doctor'
                            ORDER BY cs.date DESC, cs.start_time DESC";

$stmt = mysqli_prepare($db, $doctor_appointments_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$doctor_appointments_result = mysqli_stmt_get_result($stmt);

// استعلام حجوزات العيادات
$clinic_appointments_query = "SELECT b.*, p.name as clinic_name, p.logo as clinic_logo,
                            c.name as city_name, cs.date, cs.start_time, cs.end_time
                            FROM bookings b
                            JOIN clinic_schedules cs ON b.schedule_id = cs.id
                            JOIN people p ON cs.clinic_id = p.id
                            JOIN cities c ON p.city_id = c.id
                            WHERE b.client_id = ? AND b.type = 'clinic'
                            ORDER BY cs.date DESC, cs.start_time DESC";

$stmt = mysqli_prepare($db, $clinic_appointments_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$clinic_appointments_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مواعيدي</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Tajawal', sans-serif;
            padding-top: 80px;
        }
        
        .page-title {
            color: #4CAF50;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            border-radius: 3px;
        }
        
        .appointment-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }
        
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .appointment-header {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            color: white;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
        }
        
        .appointment-body {
            padding: 20px;
        }
        
        .doctor-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .doctor-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 15px;
            border: 3px solid #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .doctor-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }
        
        .doctor-specialty {
            color: #666;
            font-size: 14px;
        }
        
        .appointment-details p {
            margin-bottom: 10px;
            color: #555;
        }
        
        .appointment-details i {
            color: #4CAF50;
            width: 20px;
            margin-left: 8px;
        }
        
        .appointment-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .status-completed {
            background-color: #CCE5FF;
            color: #004085;
        }
        
        .status-cancelled {
            background-color: #F8D7DA;
            color: #721C24;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-review {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-review:hover {
            background-color: #3d8b40;
            transform: translateY(-2px);
        }
        
        .rating {
            display: flex;
            margin-top: 15px;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .rating input {
            display: none;
        }
        
        .rating label {
            cursor: pointer;
            width: 30px;
            height: 30px;
            margin-right: 5px;
            position: relative;
            font-size: 30px;
            color: #ddd;
        }
        
        .rating label:before {
            content: '\2605';
            position: absolute;
            top: 0;
            right: 0;
        }
        
        .rating input:checked ~ label {
            color: #ffc107;
        }
        
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffc107;
        }
        
        .rating input:checked + label:hover,
        .rating input:checked ~ label:hover,
        .rating label:hover ~ input:checked ~ label,
        .rating input:checked ~ label:hover ~ label {
            color: #ffc107;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 0;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            color: #4CAF50;
            background-color: transparent;
            border-bottom: 2px solid #4CAF50;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #4CAF50;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .empty-state i {
            font-size: 60px;
            color: #e9ecef;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .doctor-info {
                flex-direction: column;
                text-align: center;
            }
            
            .doctor-avatar {
                margin-left: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="page-title">مواعيدي</h2>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <?= $success_message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="doctors-tab" data-toggle="tab" href="#doctors" role="tab" aria-controls="doctors" aria-selected="true">
                    <i class="fas fa-user-md"></i> مواعيد الأطباء
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="clinics-tab" data-toggle="tab" href="#clinics" role="tab" aria-controls="clinics" aria-selected="false">
                    <i class="fas fa-hospital"></i> مواعيد العيادات
                </a>
            </li>
        </ul>
        
        <div class="tab-content" id="appointmentTabsContent">
            <!-- مواعيد الأطباء -->
            <div class="tab-pane fade show active" id="doctors" role="tabpanel" aria-labelledby="doctors-tab">
    <?php 
    // استعلام لجلب مواعيد الأطباء
    $doctor_appointments_query = "SELECT b.*, p.name as doctor_name, p.logo as doctor_logo, 
                                c.name as city_name, 
                                cs.date, cs.start_time, cs.end_time
                                FROM bookings b
                                JOIN clinic_schedules cs ON b.schedule_id = cs.id
                                JOIN people p ON cs.clinic_id = p.id
                                JOIN cities c ON p.city_id = c.id
                                WHERE b.client_id = ? AND b.type = 'doctor'
                                ORDER BY cs.date DESC, cs.start_time DESC";

    $stmt = mysqli_prepare($db, $doctor_appointments_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $doctor_appointments_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($doctor_appointments_result) > 0): 
    ?>
        <div class="row">
            <?php while ($appointment = mysqli_fetch_assoc($doctor_appointments_result)): ?>
                <div class="col-md-6">
                    <div class="card appointment-card">
                        <div class="appointment-header">
                            <h5>موعد مع طبيب</h5>
                        </div>
                        <div class="appointment-body">
                            <div class="doctor-info">
                                <?php if ($appointment['doctor_logo']): ?>
                                    <img src="images/<?= $appointment['doctor_logo'] ?>" alt="صورة الطبيب" class="doctor-avatar">
                                <?php else: ?>
                                    <img src="images/default-doctor.png" alt="صورة الطبيب" class="doctor-avatar">
                                <?php endif; ?>
                                <div>
                                    <h5 class="doctor-name"><?= $appointment['doctor_name'] ?></h5>
                                    <p class="doctor-specialty"><?= $appointment['city_name'] ?></p>
                                </div>
                            </div>
                           
                            <div class="appointment-details">
                                <?php if (isset($appointment['date'])): ?>
                                <p>
                                    <i class="far fa-calendar-alt"></i>
                                    <strong>التاريخ:</strong>
                                    <?= date('Y/m/d', strtotime($appointment['date'])) ?>
                                    (<?= date('l', strtotime($appointment['date'])) ?>)
                                </p>
                                <?php endif; ?>
                               
                                <?php if (isset($appointment['start_time'])): ?>
                                <p>
                                    <i class="far fa-clock"></i>
                                    <strong>الوقت:</strong>
                                    <?= date('h:i A', strtotime($appointment['start_time'])) ?>
                                </p>
                                <?php endif; ?>
                               
                                <?php if (!empty($appointment['reason'])): ?>
                                    <p>
                                        <i class="fas fa-comment-medical"></i>
                                        <strong>سبب الزيارة:</strong>
                                        <?= $appointment['reason'] ?>
                                    </p>
                                <?php endif; ?>
                               
                                <?php if (!empty($appointment['notes'])): ?>
                                    <p>
                                        <i class="fas fa-sticky-note"></i>
                                        <strong>ملاحظات:</strong>
                                        <?= $appointment['notes'] ?>
                                    </p>
                                <?php endif; ?>
                               
                                <div>
                                    <span class="appointment-status status-<?= $appointment['status'] ?>">
                                        <?php
                                        switch ($appointment['status']) {
                                            case 'pending':
                                                echo 'قيد الانتظار';
                                                break;
                                            case 'approved': // تغيير من confirmed إلى approved
                                                echo 'مؤكد';
                                                break;
                                            case 'accepted': // إضافة حالة accepted
                                                echo 'مؤكد';
                                                break;
                                            case 'completed':
                                                echo 'مكتمل';
                                                break;
                                            case 'cancelled':
                                                echo 'ملغي';
                                                break;
                                            case 'rejected':
                                                echo 'مرفوض';
                                                break;
                                            default:
                                                echo $appointment['status'];
                                        }
                                        ?>
                                    </span>
                                </div>
                               
                                <?php if ($appointment['status'] == 'pending' || $appointment['status'] == 'confirmed'): ?>
                                    <div class="mt-3">
                                        <form method="post" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الموعد؟');">
                                            <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                            <button type="submit" name="cancel_appointment" class="btn btn-cancel">
                                                <i class="fas fa-times-circle"></i> إلغاء الموعد
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                               
                                <?php if ($appointment['status'] == 'completed'): ?>
                                    <div class="mt-3">
                                        <?php if (isset($appointment['rating'])): ?>
                                            <div class="card bg-light p-3 mt-3">
                                                <h6><i class="fas fa-star text-warning"></i> تقييمك</h6>
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= ($i <= $appointment['rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                    <span class="mr-2">(<?= $appointment['rating'] ?>/5)</span>
                                                </div>
                                                <?php if (!empty($appointment['review'])): ?>
                                                    <p class="mb-0"><?= $appointment['review'] ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-review" data-toggle="modal" data-target="#reviewModal<?= $appointment['id'] ?>">
                                                <i class="fas fa-star"></i> إضافة تقييم
                                            </button>
                                           
                                            <!-- Modal for Review -->
                                            <div class="modal fade" id="reviewModal<?= $appointment['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="reviewModalLabel">تقييم الموعد مع <?= $appointment['doctor_name'] ?></h5>
                                                            <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                               
                                                                <div class="form-group">
                                                                    <label>التقييم:</label>
                                                                    <div class="rating">
                                                                        <input type="radio" name="rating" value="5" id="rating-5-<?= $appointment['id'] ?>">
                                                                        <label for="rating-5-<?= $appointment['id'] ?>"></label>
                                                                        <input type="radio" name="rating" value="4" id="rating-4-<?= $appointment['id'] ?>">
                                                                        <label for="rating-4-<?= $appointment['id'] ?>"></label>
                                                                        <input type="radio" name="rating" value="3" id="rating-3-<?= $appointment['id'] ?>">
                                                                        <label for="rating-3-<?= $appointment['id'] ?>"></label>
                                                                        <input type="radio" name="rating" value="2" id="rating-2-<?= $appointment['id'] ?>">
                                                                        <label for="rating-2-<?= $appointment['id'] ?>"></label>
                                                                        <input type="radio" name="rating" value="1" id="rating-1-<?= $appointment['id'] ?>">
                                                                        <label for="rating-1-<?= $appointment['id'] ?>"></label>
                                                                    </div>
                                                                </div>
                                                               
                                                                <div class="form-group">
                                                                    <label for="review-text-<?= $appointment['id'] ?>">تعليق (اختياري):</label>
                                                                    <textarea class="form-control" id="review-text-<?= $appointment['id'] ?>" name="review_text" rows="3" placeholder="اكتب تعليقك هنا..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                                                                <button type="submit" name="add_review" class="btn btn-success">إرسال التقييم</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h4>لا توجد مواعيد مع أطباء</h4>
            <p>لم تقم بحجز أي مواعيد مع أطباء حتى الآن</p>
            <a href="doctors_list.php" class="btn btn-success">
                <i class="fas fa-user-md"></i> تصفح الأطباء
            </a>
        </div>
    <?php endif; ?>
</div>

            
            <!-- مواعيد العيادات -->
           <!-- مواعيد العيادات -->
<div class="tab-pane fade" id="clinics" role="tabpanel" aria-labelledby="clinics-tab">
    <?php
    // استعلام حجوزات العيادات باستخدام جدول clinic_bookings
    $clinic_appointments_query = "SELECT cb.*, p.name as clinic_name, p.logo as clinic_logo,
                                c.name as city_name, cs.date, cs.start_time, cs.end_time
                                FROM clinic_bookings cb
                                JOIN clinic_schedules cs ON cb.schedule_id = cs.id
                                JOIN people p ON cs.clinic_id = p.id
                                JOIN cities c ON p.city_id = c.id
                                WHERE cb.client_id = ?
                                ORDER BY cs.date DESC, cs.start_time DESC";

    $stmt = mysqli_prepare($db, $clinic_appointments_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $clinic_appointments_result = mysqli_stmt_get_result($stmt);
    
    if ($clinic_appointments_result && mysqli_num_rows($clinic_appointments_result) > 0): 
    ?>
        <div class="row">
            <?php while ($appointment = mysqli_fetch_assoc($clinic_appointments_result)): ?>
                <div class="col-md-6">
                    <div class="card appointment-card">
                        <div class="appointment-header">
                            <h5>موعد في عيادة</h5>
                        </div>
                        <div class="appointment-body">
                            <div class="doctor-info">
                                <?php if ($appointment['clinic_logo']): ?>
                                    <img src="images/<?= $appointment['clinic_logo'] ?>" alt="شعار العيادة" class="doctor-avatar">
                                <?php else: ?>
                                    <img src="images/default-clinic.png" alt="شعار العيادة" class="doctor-avatar">
                                <?php endif; ?>
                                <div>
                                    <h5 class="doctor-name"><?= $appointment['clinic_name'] ?></h5>
                                    <p class="doctor-specialty"><?= $appointment['city_name'] ?></p>
                                </div>
                            </div>
                           
                            <div class="appointment-details">
                                <?php if (isset($appointment['date'])): ?>
                                <p>
                                    <i class="far fa-calendar-alt"></i>
                                    <strong>التاريخ:</strong>
                                    <?= date('Y/m/d', strtotime($appointment['date'])) ?>
                                    (<?= date('l', strtotime($appointment['date'])) ?>)
                                </p>
                                <?php endif; ?>
                               
                                <?php if (isset($appointment['start_time'])): ?>
                                <p>
                                    <i class="far fa-clock"></i>
                                    <strong>الوقت:</strong>
                                    <?= date('h:i A', strtotime($appointment['start_time'])) ?>
                                </p>
                                <?php endif; ?>
                               
                                <?php if (!empty($appointment['notes'])): ?>
                                    <p>
                                        <i class="fas fa-sticky-note"></i>
                                        <strong>ملاحظات:</strong>
                                        <?= $appointment['notes'] ?>
                                    </p>
                                <?php endif; ?>
                               
                                <div>
                                    <span class="appointment-status status-<?= $appointment['status'] ?>">
                                        <?php
                                        switch ($appointment['status']) {
                                            case 'pending':
                                                echo 'قيد الانتظار';
                                                break;
                                            case 'approved':
                                                echo 'مؤكد';
                                                break;
                                            case 'completed':
                                                echo 'مكتمل';
                                                break;
                                            case 'cancelled':
                                                echo 'ملغي';
                                                break;
                                            case 'rejected':
                                                echo 'مرفوض';
                                                break;
                                            default:
                                                echo $appointment['status'];
                                        }
                                        ?>
                                    </span>
                                </div>
                               
                                <?php if ($appointment['status'] == 'pending' || $appointment['status'] == 'approved'): ?>
                                    <div class="mt-3">
                                        <form method="post" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الموعد؟');">
                                            <input type="hidden" name="clinic_appointment_id" value="<?= $appointment['id'] ?>">
                                            <button type="submit" name="cancel_clinic_appointment" class="btn btn-cancel">
                                                <i class="fas fa-times-circle"></i> إلغاء الموعد
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-hospital-alt"></i>
            <h4>لا توجد مواعيد مع عيادات</h4>
            <p>لم تقم بحجز أي مواعيد مع عيادات حتى الآن</p>
            <a href="clinics_list.php" class="btn btn-success">
                <i class="fas fa-hospital"></i> تصفح العيادات
            </a>
        </div>
    <?php endif; ?>
</div>

        </div>
    </div>

    <!-- إضافة معالج لإلغاء مواعيد العيادات -->
  <!-- إضافة معالج لإلغاء مواعيد العيادات -->
<?php
// إلغاء موعد العيادة
if (isset($_POST['cancel_clinic_appointment']) && isset($_POST['clinic_appointment_id'])) {
    $appointment_id = $_POST['clinic_appointment_id'];
   
    // التحقق من أن الموعد ينتمي للمستخدم الحالي
    $check_query = "SELECT * FROM clinic_bookings WHERE id = ? AND client_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($check_result) > 0) {
        // تحديث حالة الموعد إلى ملغي
        $update_query = "UPDATE clinic_bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $appointment_id);
       
        if (mysqli_stmt_execute($stmt)) {
            // استرجاع معلومات الحجز للحصول على schedule_id
            $booking_data = mysqli_fetch_assoc($check_result);
            $schedule_id = $booking_data['schedule_id'];
            
            // زيادة عدد المقاعد المتاحة في الجدول الزمني
            $update_schedule_query = "UPDATE clinic_schedules SET max_bookings = max_bookings + 1 WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_schedule_query);
            mysqli_stmt_bind_param($stmt, "i", $schedule_id);
            mysqli_stmt_execute($stmt);
            
            echo "<script>
                alert('تم إلغاء الموعد بنجاح');
                window.location.href = 'client_appointments.php';
            </script>";
        } else {
            echo "<script>
                alert('حدث خطأ أثناء إلغاء الموعد، يرجى المحاولة مرة أخرى');
            </script>";
        }
    } else {
        echo "<script>
            alert('لا يمكن العثور على الموعد المطلوب');
        </script>";
    }
}
?>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
