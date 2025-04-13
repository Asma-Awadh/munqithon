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

// قبول الحجز
if (isset($_GET['accept_id']) && is_numeric($_GET['accept_id'])) {
    $booking_id = $_GET['accept_id'];
   
    // التحقق من ملكية الحجز
    $check_query = "SELECT cb.* FROM clinic_bookings cb
                    JOIN clinic_schedules cs ON cb.schedule_id = cs.id
                    WHERE cb.id = ? AND cs.clinic_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $clinic_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($result) > 0) {
        // تحديث حالة الحجز
        $update_query = "UPDATE clinic_bookings SET status = 'approved', updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
       
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم قبول الحجز بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء قبول الحجز، يرجى المحاولة مرة أخرى";
        }
    } else {
        $error_message = "الحجز غير موجود أو ليس لديك صلاحية لقبوله";
    }
}

// رفض الحجز
if (isset($_GET['reject_id']) && is_numeric($_GET['reject_id'])) {
    $booking_id = $_GET['reject_id'];
   
    // التحقق من ملكية الحجز
    $check_query = "SELECT cb.* FROM clinic_bookings cb
                    JOIN clinic_schedules cs ON cb.schedule_id = cs.id
                    WHERE cb.id = ? AND cs.clinic_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $clinic_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($result) > 0) {
        // تحديث حالة الحجز
        $update_query = "UPDATE clinic_bookings SET status = 'rejected', updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
       
        if (mysqli_stmt_execute($stmt)) {
            // زيادة عدد المقاعد المتاحة في الجدول الزمني
            $booking_data = mysqli_fetch_assoc($result);
            $schedule_id = $booking_data['schedule_id'];
            
            $update_schedule_query = "UPDATE clinic_schedules SET max_bookings = max_bookings + 1 WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_schedule_query);
            mysqli_stmt_bind_param($stmt, "i", $schedule_id);
            mysqli_stmt_execute($stmt);
            
            $success_message = "تم رفض الحجز بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء رفض الحجز، يرجى المحاولة مرة أخرى";
        }
    } else {
        $error_message = "الحجز غير موجود أو ليس لديك صلاحية لرفضه";
    }
}

// جلب الحجوزات
$bookings_query = "SELECT cb.*, cs.date, cs.start_time, cs.end_time, p.name as client_name, p.phone as client_phone
                  FROM clinic_bookings cb
                  JOIN clinic_schedules cs ON cb.schedule_id = cs.id
                  JOIN people p ON cb.client_id = p.id
                  WHERE cs.clinic_id = ?
                  ORDER BY cs.date ASC, cs.start_time ASC";
$stmt = mysqli_prepare($db, $bookings_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إدارة طلبات الحجوزات</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .bookings-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .bookings-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .bookings-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .bookings-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .btn-accept {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-accept:hover {
            background: #2E7D32;
            transform: translateY(-2px);
            color: white;
        }
        .btn-reject {
            background: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-reject:hover {
            background: #d32f2f;
            transform: translateY(-2px);
            color: white;
        }
        .bookings-table {
            margin-top: 30px;
        }
        .bookings-table th {
            background-color: #4CAF50;
            color: white;
            font-weight: 600;
        }
        .alert {
            border-radius: 10px;
        }
        .no-bookings {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
        .status-pending {
            background-color: #FFC107;
            color: #212121;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-approved {
            background-color: #4CAF50;
            color: white;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-rejected {
            background-color: #f44336;
            color: white;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .booking-details {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="bookings-container">
                    <div class="bookings-header">
                        <h2>إدارة طلبات الحجوزات</h2>
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
                   
                    <div class="bookings-table">
                        <h4 class="mb-4 text-center">طلبات الحجوزات</h4>
                       
                        <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>اسم العميل</th>
                                            <th>رقم الهاتف</th>
                                            <th>التاريخ</th>
                                            <th>الوقت</th>
                                            <th>الحالة</th>
                                            <th>ملاحظات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                            <tr>
                                                <td><?= $booking['client_name'] ?></td>
                                                <td><?= $booking['client_phone'] ?></td>
                                                <td><?= date('Y-m-d', strtotime($booking['date'])) ?></td>
                                                <td>
                                                    <?= date('h:i A', strtotime($booking['start_time'])) ?> -
                                                    <?= date('h:i A', strtotime($booking['end_time'])) ?>
                                                </td>
                                                <td>
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <span class="status-pending">قيد الانتظار</span>
                                                    <?php elseif ($booking['status'] == 'approved'): ?>
                                                        <span class="status-approved">مقبول</span>
                                                    <?php elseif ($booking['status'] == 'rejected'): ?>
                                                        <span class="status-rejected">مرفوض</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($booking['notes'])): ?>
                                                        <div class="booking-details">
                                                            <?= $booking['notes'] ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">لا توجد ملاحظات</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <a href="clinic_bookings.php?accept_id=<?= $booking['id'] ?>" class="btn btn-accept btn-sm" onclick="return confirm('هل أنت متأكد من قبول هذا الحجز؟')">
                                                            <i class="fas fa-check"></i> قبول
                                                        </a>
                                                        <a href="clinic_bookings.php?reject_id=<?= $booking['id'] ?>" class="btn btn-reject btn-sm mt-1" onclick="return confirm('هل أنت متأكد من رفض هذا الحجز؟')">
                                                            <i class="fas fa-times"></i> رفض
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled>
                                                            <i class="fas fa-lock"></i> تم اتخاذ القرار
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-bookings">
                                <p class="text-muted">لا توجد طلبات حجوزات حالياً</p>
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
