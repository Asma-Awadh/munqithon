<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 3) {
    header("location:login.php");
    exit();
}

$clinic_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// إضافة موعد جديد
if (isset($_POST['add_schedule'])) {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $max_bookings = $_POST['max_bookings'];
    $zoom_link = $_POST['zoom_link']; // إضافة متغير رابط زوم
    
    // التحقق من صحة المدخلات
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error_message = "لا يمكن إضافة مواعيد لتاريخ سابق";
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error_message = "وقت البداية يجب أن يكون قبل وقت النهاية";
    } elseif ($max_bookings <= 0) {
        $error_message = "يجب أن يكون الحد الأقصى للحجوزات أكبر من صفر";
    } else {
        // التحقق من عدم وجود تعارض في المواعيد
        $check_query = "SELECT * FROM clinic_schedules 
                        WHERE clinic_id = ? AND date = ? 
                        AND ((start_time <= ? AND end_time > ?) 
                        OR (start_time < ? AND end_time >= ?) 
                        OR (start_time >= ? AND end_time <= ?))";
        
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "isssssss", $clinic_id, $date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = "يوجد تعارض مع مواعيد أخرى في نفس الفترة";
        } else {
            // إضافة الموعد الجديد مع رابط زوم
            $insert_query = "INSERT INTO clinic_schedules (clinic_id, date, start_time, end_time, max_bookings, zoom_link) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($stmt, "isssis", $clinic_id, $date, $start_time, $end_time, $max_bookings, $zoom_link);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "تم إضافة الموعد بنجاح";
            } else {
                $error_message = "حدث خطأ أثناء إضافة الموعد، يرجى المحاولة مرة أخرى";
            }
        }
    }
}

// حذف موعد
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $schedule_id = $_GET['delete_id'];
    
    // التحقق من ملكية الموعد
    $check_query = "SELECT * FROM clinic_schedules WHERE id = ? AND clinic_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $schedule_id, $clinic_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // التحقق من عدم وجود حجوزات مرتبطة
        $check_bookings = "SELECT COUNT(*) as booking_count FROM bookings WHERE schedule_id = ?";
        $stmt = mysqli_prepare($db, $check_bookings);
        mysqli_stmt_bind_param($stmt, "i", $schedule_id);
        mysqli_stmt_execute($stmt);
        $booking_result = mysqli_stmt_get_result($stmt);
        $booking_data = mysqli_fetch_assoc($booking_result);
        
        if ($booking_data['booking_count'] > 0) {
            $error_message = "لا يمكن حذف هذا الموعد لوجود حجوزات مرتبطة به";
        } else {
            // حذف الموعد
            $delete_query = "DELETE FROM clinic_schedules WHERE id = ?";
            $stmt = mysqli_prepare($db, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $schedule_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "تم حذف الموعد بنجاح";
            } else {
                $error_message = "حدث خطأ أثناء حذف الموعد، يرجى المحاولة مرة أخرى";
            }
        }
    } else {
        $error_message = "الموعد غير موجود أو ليس لديك صلاحية لحذفه";
    }
}

// جلب مواعيد العيادة
$schedules_query = "SELECT * FROM clinic_schedules WHERE clinic_id = ? ORDER BY date ASC, start_time ASC";
$stmt = mysqli_prepare($db, $schedules_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$schedules_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إدارة مواعيد العيادة</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .schedule-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .schedule-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .schedule-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .schedule-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        .btn-add {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-delete {
            background: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        .schedule-table {
            margin-top: 30px;
        }
        .schedule-table th {
            background-color: #4CAF50;
            color: white;
            font-weight: 600;
        }
        .alert {
            border-radius: 10px;
        }
        .no-schedules {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="schedule-container">
                    <div class="schedule-header">
                        <h2>إدارة مواعيد العيادة</h2>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">التاريخ</label>
                                    <input type="date" class="form-control" id="date" name="date" min="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_bookings">الحد الأقصى للحجوزات</label>
                                    <input type="number" class="form-control" id="max_bookings" name="max_bookings" min="1" value="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">وقت البداية</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">وقت النهاية</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- إضافة حقل رابط زوم -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="zoom_link">رابط اجتماع زوم (اختياري)</label>
                                    <input type="url" class="form-control" id="zoom_link" name="zoom_link" placeholder="https://zoom.us/j/...">
                                    <small class="form-text text-muted">أدخل رابط اجتماع زوم للمواعيد عن بعد</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="add_schedule" class="btn btn-add">
                                <i class="fas fa-plus ml-2"></i> إضافة موعد جديد
                            </button>
                        </div>
                    </form>
                    
                    <div class="schedule-table">
                        <h4 class="mb-4 text-center">المواعيد المتاحة</h4>
                        
                        <?php if (mysqli_num_rows($schedules_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>التاريخ</th>
                                            <th>وقت البداية</th>
                                            <th>وقت النهاية</th>
                                            <th>الحد الأقصى للحجوزات</th>
                                            <th>الحجوزات الحالية</th>
                                            <th>رابط زوم</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($schedule = mysqli_fetch_assoc($schedules_result)):
                                            // جلب عدد الحجوزات الحالية
                                            $booking_query = "SELECT COUNT(*) as booking_count FROM bookings WHERE schedule_id = ?";
                                            $stmt = mysqli_prepare($db, $booking_query);
                                            mysqli_stmt_bind_param($stmt, "i", $schedule['id']);
                                            mysqli_stmt_execute($stmt);
                                            $booking_result = mysqli_stmt_get_result($stmt);
                                            $booking_data = mysqli_fetch_assoc($booking_result);
                                            $current_bookings = $booking_data['booking_count'];
                                        ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($schedule['date'])) ?></td>
                                                <td><?= date('h:i A', strtotime($schedule['start_time'])) ?></td>
                                                <td><?= date('h:i A', strtotime($schedule['end_time'])) ?></td>
                                                <td><?= $schedule['max_bookings'] ?></td>
                                                <td><?= $current_bookings ?></td>
                                                <td>
                                                    <?php if (!empty($schedule['zoom_link'])): ?>
                                                        <a href="<?= $schedule['zoom_link'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-video"></i> فتح رابط زوم                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">لا يوجد رابط</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($current_bookings == 0): ?>
                                                        <a href="clinic_schedule.php?delete_id=<?= $schedule['id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا الموعد؟')">
                                                            <i class="fas fa-trash"></i> حذف
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled>
                                                            <i class="fas fa-lock"></i> لا يمكن الحذف
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-schedules">
                                <p class="text-muted">لا توجد مواعيد متاحة حالياً</p>
                            </div>
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

