<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];

// التحقق من وجود جدول clinic_bookings
$check_table_query = "SHOW TABLES LIKE 'clinic_bookings'";
$check_table_result = mysqli_query($db, $check_table_query);
if (mysqli_num_rows($check_table_result) == 0) {
    // إنشاء جدول clinic_bookings إذا لم يكن موجودًا
    $create_table_query = "CREATE TABLE `clinic_bookings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `client_id` int(11) NOT NULL,
        `clinic_id` int(11) NOT NULL,
        `schedule_id` int(11) NOT NULL,
        `notes` text DEFAULT NULL,
        `status` enum('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `client_id` (`client_id`),
        KEY `clinic_id` (`clinic_id`),
        KEY `schedule_id` (`schedule_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (!mysqli_query($db, $create_table_query)) {
        $error_message = "حدث خطأ أثناء إنشاء جدول الحجوزات. يرجى الاتصال بمسؤول النظام.";
    }
}

// التحقق من وجود معرف العيادة
if (!isset($_GET['clinic_id']) || !is_numeric($_GET['clinic_id'])) {
    header("location:clinics_list.php");
    exit();
}

$clinic_id = $_GET['clinic_id'];

// جلب معلومات العيادة
$clinic_query = "SELECT p.*, c.name as city_name
                FROM people p
                JOIN cities c ON p.city_id = c.id
                WHERE p.id = ? AND p.role_id = 3 AND p.status = 1";
$stmt = mysqli_prepare($db, $clinic_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$clinic_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($clinic_result) == 0) {
    header("location:clinics_list.php");
    exit();
}

$clinic = mysqli_fetch_assoc($clinic_result);

// جلب مواعيد العيادة المتاحة
$schedules_query = "SELECT * FROM clinic_schedules
                   WHERE clinic_id = ? AND date >= CURDATE()
                   ORDER BY date ASC, start_time ASC";
$stmt = mysqli_prepare($db, $schedules_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$schedules_result = mysqli_stmt_get_result($stmt);

// معالجة نموذج الحجز
$success_message = "";
$error_message = "";

if (isset($_POST['book_appointment'])) {
    $schedule_id = $_POST['schedule_id'];
    $notes = $_POST['notes'];
    
    // التحقق من أن الموعد لا يزال متاحًا
    $check_query = "SELECT * FROM clinic_schedules
                   WHERE id = ? AND clinic_id = ? AND max_bookings > 0";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $schedule_id, $clinic_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $schedule = mysqli_fetch_assoc($check_result);
        
        // إنشاء الحجز
        $insert_query = "INSERT INTO clinic_bookings (client_id, clinic_id, schedule_id, notes, status, created_at)
                        VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiis", $user_id, $clinic_id, $schedule_id, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            // تحديث عدد الحجوزات المتاحة
            $update_query = "UPDATE clinic_schedules SET max_bookings = max_bookings - 1 WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $schedule_id);
            mysqli_stmt_execute($stmt);
            
            $success_message = "تم حجز الموعد بنجاح! سيتم مراجعة طلبك من قبل العيادة.";
        } else {
            $error_message = "حدث خطأ أثناء حجز الموعد. يرجى المحاولة مرة أخرى.";
        }
    } else {
        $error_message = "عذراً، هذا الموعد لم يعد متاحاً. يرجى اختيار موعد آخر.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>حجز موعد - <?= $clinic['name'] ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .booking-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .booking-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .booking-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .booking-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .clinic-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .clinic-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 20px;
            border: 3px solid #4CAF50;
        }
        .clinic-details h3 {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .schedule-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .schedule-card:hover, .schedule-card.selected {
            background-color: #f0f8f0;
            border-color: #4CAF50;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .schedule-card.selected {
            border-width: 2px;
        }
        .schedule-date {
            font-weight: 700;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .schedule-time {
            color: #666;
            font-size: 0.9rem;
        }
        .zoom-badge {
            display: inline-block;
            background-color: #2D8CFF;
            color: white;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .btn-book {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
            width: 100%;
            margin-top: 20px;
        }
        .btn-book:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 10px;
        }
        .no-schedules {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="booking-container">
                    <div class="booking-header">
                        <h2>حجز موعد في عيادة</h2>
                        <p class="text-muted">اختر الموعد المناسب لك</p>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="clinic-info">
                        <img src="images/<?= !empty($clinic['logo']) ? $clinic['logo'] : 'default-clinic.jpg' ?>" alt="<?= $clinic['name'] ?>" class="clinic-logo">
                        <div class="clinic-details">
                            <h3><?= $clinic['name'] ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> <?= $clinic['city_name'] ?> <?= !empty($clinic['address']) ? ' - ' . $clinic['address'] : '' ?></p>
                            <?php if (!empty($clinic['phone'])): ?>
                                <p><i class="fas fa-phone"></i> <?= $clinic['phone'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="post" id="bookingForm">
                        <h4 class="mb-4">المواعيد المتاحة:</h4>
                        
                        <?php if (mysqli_num_rows($schedules_result) > 0): ?>
                            <div class="row">
                                <?php while ($schedule = mysqli_fetch_assoc($schedules_result)): ?>
                                    <?php if ($schedule['max_bookings'] > 0): ?>
                                        <div class="col-md-4">
                                            <div class="schedule-card" onclick="selectSchedule(this, <?= $schedule['id'] ?>, '<?= !empty($schedule['zoom_link']) ? $schedule['zoom_link'] : '' ?>')">
                                                <div class="schedule-date">
                                                    <?= date('Y-m-d', strtotime($schedule['date'])) ?>
                                                </div>
                                                <div class="schedule-time">
                                                    <i class="far fa-clock"></i>
                                                    <?= date('h:i A', strtotime($schedule['start_time'])) ?> -
                                                    <?= date('h:i A', strtotime($schedule['end_time'])) ?>
                                                </div>
                                                <div class="mt-2 text-muted">
                                                    <small>المقاعد المتاحة: <?= $schedule['max_bookings'] ?></small>
                                                </div>
                                                <?php if (!empty($schedule['zoom_link'])): ?>
                                                    <div class="zoom-badge">
                                                        <i class="fas fa-video"></i> موعد عبر زوم
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>
                            
                            <input type="hidden" name="schedule_id" id="schedule_id" required>
                            <input type="hidden" name="zoom_link" id="zoom_link">
                            
                            <div id="zoom-info" class="alert alert-info mt-3" style="display: none;">
                                <i class="fas fa-info-circle"></i> هذا الموعد متاح عبر تطبيق زوم. سيتم إرسال رابط الاجتماع إليك بعد تأكيد الحجز.
                            </div>
                            
                            <div class="form-group mt-4">
                                <label for="notes">ملاحظات (اختياري):</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="أضف أي ملاحظات أو استفسارات تريد إرسالها للعيادة"></textarea>
                            </div>
                            
                            <button type="submit" name="book_appointment" class="btn btn-book">
                                <i class="fas fa-calendar-check"></i> تأكيد الحجز
                            </button>
                        <?php else: ?>
                            <div class="no-schedules">
                                <i class="far fa-calendar-times fa-3x mb-3 text-muted"></i>
                                <h4>لا توجد مواعيد متاحة</h4>
                                <p>لم يتم العثور على مواعيد متاحة لهذه العيادة حالياً.</p>
                                <a href="clinics_list.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-arrow-right"></i> العودة إلى قائمة العيادات
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function selectSchedule(element, scheduleId, zoomLink) {
            // إزالة الفئة المحددة من جميع البطاقات
            document.querySelectorAll('.schedule-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // إضافة الفئة المحددة إلى البطاقة المختارة
            element.classList.add('selected');
            
            // تعيين معرف الجدول الزمني المحدد
            document.getElementById('schedule_id').value = scheduleId;
            
            // إظهار معلومات زوم إذا كان هناك رابط
            const zoomInfo = document.getElementById('zoom-info');
            if (zoomLink && zoomLink.trim() !== '') {
                zoomInfo.style.display = 'block';
                document.getElementById('zoom_link').value = zoomLink;
            } else {
                zoomInfo.style.display = 'none';
                document.getElementById('zoom_link').value = '';
            }
        }
        
        // التحقق من النموذج قبل الإرسال
        document.getElementById('bookingForm').addEventListener('submit', function(event) {
            const scheduleId = document.getElementById('schedule_id').value;
            
            if (!scheduleId) {
                event.preventDefault();
                alert('يرجى اختيار موعد قبل تأكيد الحجز');
            }
        });
    </script>
</body>
</html>

