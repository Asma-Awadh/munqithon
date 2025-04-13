<?php
include("conn.php");
include("header.php");

// Check if user is logged in and is a doctor
if (!isset($_SESSION['id']) || $_SESSION['role'] != 2) {
    header("location:login.php");
    exit();
}

$doctor_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// تحسين التحقق من الأمان
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    // التحقق من أن الحجز ينتمي للطبيب الحالي
    $check_query = "SELECT b.id FROM bookings b
                   JOIN clinic_schedules cs ON b.schedule_id = cs.id
                   WHERE b.id = ? AND cs.clinic_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $doctor_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // تحديث حالة الحجز
        $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $status, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // إذا تم رفض الحجز، قم بتحديث جدول doctor_schedule
            if ($status == 'rejected') {
                // الحصول على معرف الجدول الزمني للطبيب
                $schedule_query = "SELECT ds.id FROM doctor_schedule ds
                                  JOIN clinic_schedules cs ON ds.appointment_date = cs.date
                                  AND ds.start_time = cs.start_time
                                  JOIN bookings b ON b.schedule_id = cs.id
                                  WHERE b.id = ? AND ds.doctor_id = ?";
                $stmt = mysqli_prepare($db, $schedule_query);
                mysqli_stmt_bind_param($stmt, "ii", $booking_id, $doctor_id);
                mysqli_stmt_execute($stmt);
                $schedule_result = mysqli_stmt_get_result($stmt);
                
                if ($schedule_row = mysqli_fetch_assoc($schedule_result)) {
                    $doctor_schedule_id = $schedule_row['id'];
                    
                    // تحديث حالة الجدول الزمني للطبيب
                    $update_schedule_query = "UPDATE doctor_schedule SET is_booked = 0 WHERE id = ?";
                    $stmt = mysqli_prepare($db, $update_schedule_query);
                    mysqli_stmt_bind_param($stmt, "i", $doctor_schedule_id);
                    mysqli_stmt_execute($stmt);
                }
            }
            
            $success_message = "تم تحديث حالة الحجز بنجاح";
            
            // هنا يمكن إضافة كود لإرسال إشعار للمريض
        } else {
            $error_message = "حدث خطأ أثناء تحديث حالة الحجز: " . mysqli_error($db);
        }
    } else {
        $error_message = "لا يمكن العثور على الحجز أو أنه لا ينتمي لك";
    }
}

// Get all appointment requests for this doctor
$query = "SELECT b.*, p.name as client_name, p.phone as client_phone, 
          cs.date, cs.start_time, cs.end_time
          FROM bookings b
          JOIN people p ON b.client_id = p.id
          JOIN clinic_schedules cs ON b.schedule_id = cs.id
          WHERE cs.clinic_id = ? AND b.status IN ('pending', 'approved', 'rejected')
          ORDER BY cs.date ASC, cs.start_time ASC";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>طلبات الحجز</title>
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
        }
        
        .card-header {
            background: #4CAF50;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .btn-success:hover {
            background-color: #388E3C;
            border-color: #388E3C;
        }
        
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .badge-pending {
            background-color: #FFC107;
            color: #212529;
        }
        
        .badge-approved {
            background-color: #28A745;
        }
        
        .badge-rejected {
            background-color: #DC3545;
        }
        
        .appointment-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-white mb-4">طلبات الحجز</h1>
                
                <?php if(isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-check"></i> طلبات الحجز
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المريض</th>
                                        <th>رقم الهاتف</th>
                                        <th>التاريخ</th>
                                        <th>الوقت</th>
                                        <th>الملاحظات</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    while($row = mysqli_fetch_assoc($result)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $row['client_name']; ?></td>
                                        <td><?php echo $row['client_phone']; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($row['date'])); ?></td>
                                        <td>
                                            <?php echo date('h:i A', strtotime($row['start_time'])); ?> - 
                                            <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                                        </td>
                                        <td><?php echo $row['notes']; ?></td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                                <span class="badge badge-pending">قيد الانتظار</span>
                                            <?php elseif($row['status'] == 'approved'): ?>
                                                <span class="badge badge-approved">تمت الموافقة</span>
                                            <?php elseif($row['status'] == 'rejected'): ?>
                                                <span class="badge badge-rejected">تم الرفض</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد من الموافقة على هذا الحجز؟')">
                                                    <i class="fas fa-check"></i> موافقة
                                                </button>
                                            </form>
                                            
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من رفض هذا الحجز؟')">
                                                    <i class="fas fa-times"></i> رفض
                                                </button>
                                            </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>تم اتخاذ الإجراء</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            لا توجد طلبات حجز حالياً.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
