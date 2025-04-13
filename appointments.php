<?php
// تفعيل عرض الأخطاء للتشخيص (قم بإزالة هذه الأسطر بعد حل المشكلة)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// تضمين ملف الاتصال
include("conn.php");

// التحقق من تسجيل الدخول
if (!isset($_SESSION['id']) || $_SESSION['role'] != 2) {
    header("location:login.php");
    exit();
}

// معالجة إضافة موعد
if (isset($_POST['add_appointment'])) {
    try {
        $doctor_id = $_SESSION['id'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $status = 1; // استخدم 1 بدلاً من 'available' لأن العمود من نوع tinyint
        $is_booked = 0; // 0 means not booked
       
        // التحقق من صحة البيانات
        if (empty($date) || empty($start_time) || empty($end_time)) {
            throw new Exception("جميع الحقول مطلوبة");
        }
        
        // التحقق من اتصال قاعدة البيانات
        if (!$db) {
            throw new Exception("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
        }
        
        // إعداد الاستعلام
        $query = "INSERT INTO doctor_schedule (doctor_id, appointment_date, start_time, end_time, status, is_booked, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($db, $query);
        
        if (!$stmt) {
            throw new Exception("خطأ في إعداد الاستعلام: " . mysqli_error($db));
        }
        
        // ربط المعلمات
        if (!mysqli_stmt_bind_param($stmt, "isssii", $doctor_id, $date, $start_time, $end_time, $status, $is_booked)) {
            throw new Exception("خطأ في ربط المعلمات: " . mysqli_stmt_error($stmt));
        }
        
        // تنفيذ الاستعلام
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("خطأ في تنفيذ الاستعلام: " . mysqli_stmt_error($stmt));
        }
        
        $success_message = "تم إضافة الموعد بنجاح";
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        $error_message = "حدث خطأ: " . $e->getMessage();
    }
}

// جلب مواعيد الطبيب
try {
    $doctor_id = $_SESSION['id'];
    $sql = "SELECT * FROM doctor_schedule WHERE doctor_id = ? ORDER BY appointment_date, start_time";
    $stmt = mysqli_prepare($db, $sql);
    
    if (!$stmt) {
        throw new Exception("خطأ في إعداد الاستعلام: " . mysqli_error($db));
    }
    
    if (!mysqli_stmt_bind_param($stmt, "i", $doctor_id)) {
        throw new Exception("خطأ في ربط المعلمات: " . mysqli_stmt_error($stmt));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("خطأ في تنفيذ الاستعلام: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
} catch (Exception $e) {
    $error_message = "حدث خطأ في جلب المواعيد: " . $e->getMessage();
    $result = false;
}

// تضمين ملف الرأس
include("header.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إدارة المواعيد</title>
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
        
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .btn-primary:hover {
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-white mb-4">إدارة المواعيد</h1>
                
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
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle"></i> إضافة موعد جديد
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="form-group mb-3">
                                <label for="date">التاريخ</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="start_time">وقت البدء</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="end_time">وقت الانتهاء</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            
                            <button type="submit" name="add_appointment" class="btn btn-primary w-100">إضافة الموعد</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i> المواعيد المتاحة
                    </div>
                    <div class="card-body">
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>وقت البدء</th>
                                        <th>وقت الانتهاء</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($row['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($row['end_time'])); ?></td>
                                        <td>
                                            <?php if($row['is_booked'] == 0): ?>
                                                <span class="badge bg-success">متاح</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">محجوز</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الموعد؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            لا توجد مواعيد متاحة حالياً. يرجى إضافة مواعيد جديدة.
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
