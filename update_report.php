<?php
// تأكد من بدء الجلسة قبل أي شيء آخر
session_start();

// التحقق من تسجيل الدخول وأن المستخدم عيادة
if (!isset($_SESSION['id']) || $_SESSION['role'] != 3) {
    header("location:login.php");
    exit();
}

include("conn.php");

$clinic_id = $_SESSION['id'];
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// جلب معلومات البلاغ بشكل مباشر
$report_query = "SELECT r.*, c.name as city_name
                FROM rescue_reports r
                JOIN cities c ON r.city_id = c.id
                WHERE r.id = ?";
$stmt = mysqli_prepare($db, $report_query);
mysqli_stmt_bind_param($stmt, "i", $report_id);
mysqli_stmt_execute($stmt);
$report_result = mysqli_stmt_get_result($stmt);
$report = mysqli_fetch_assoc($report_result);

if (!$report) {
    header("location:clinic_rescue_reports.php");
    exit();
}

// جلب معلومات مقدم البلاغ بشكل منفصل إذا كان user_id موجوداً
if (!empty($report['user_id'])) {
    $user_query = "SELECT name FROM people WHERE id = ?";
    $stmt = mysqli_prepare($db, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $report['user_id']);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($user_result);
    
    if ($user && !empty($user['name'])) {
        $report['reporter_name'] = $user['name'];
    } else {
        $report['reporter_name'] = 'غير معروف';
    }
} else {
    $report['reporter_name'] = 'غير معروف';
}

// تحديث حالة البلاغ
if (isset($_POST['update_status'])) {
    $new_status = mysqli_real_escape_string($db, $_POST['new_status']);
    $notes = mysqli_real_escape_string($db, $_POST['notes']);
   
    $update_query = "UPDATE rescue_reports SET status = ?, notes = ? WHERE id = ?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $notes, $report_id);
   
    if (mysqli_stmt_execute($stmt)) {
        // استخدم JavaScript للتوجيه بدلاً من header()
        $redirect = true;
    } else {
        $error = "حدث خطأ أثناء تحديث البلاغ: " . mysqli_error($db);
    }
}

// الآن يمكننا تضمين header.php بعد معالجة النموذج
include("header.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>تحديث حالة البلاغ - <?= $report['id'] ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .update-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .update-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-action {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            background-color: #388E3C;
            color: white;
        }
    </style>
</head>
<body>
    <?php if (isset($redirect)): ?>
    <script>
        window.location.href = "clinic_rescue_reports.php?success=1";
    </script>
    <?php endif; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
                <?php endif; ?>
                
                <div class="update-container">
                    <div class="update-header">
                        <h2><i class="fas fa-edit"></i> تحديث حالة البلاغ #<?= $report['id'] ?></h2>
                        <p class="text-muted">يمكنك تغيير حالة البلاغ وإضافة ملاحظات</p>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5>معلومات البلاغ</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>نوع الحيوان:</strong> <?= $report['animal_type'] ?></p>
                                    <p><strong>المدينة:</strong> <?= $report['city_name'] ?></p>
                                    <p><strong>الموقع:</strong> <?= $report['location'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>رقم التواصل:</strong> <?= $report['contact_phone'] ?></p>
                                    <p><strong>تاريخ البلاغ:</strong> <?= date('Y/m/d h:i A', strtotime($report['created_at'])) ?></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>الوصف:</strong> <?= $report['description'] ?></p>
                            </div>
                            <?php if (!empty($report['image'])): ?>
                                <div class="mt-3 text-center">
                                    <img src="images/<?= $report['image'] ?>" alt="صورة البلاغ" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="new_status">الحالة الجديدة</label>
                            <select class="form-control" id="new_status" name="new_status" required>
                                <option value="pending" <?= $report['status'] == 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                                <option value="in_progress" <?= $report['status'] == 'in_progress' ? 'selected' : '' ?>>جاري العمل عليه</option>
                                <option value="completed" <?= $report['status'] == 'completed' ? 'selected' : '' ?>>تم الإنقاذ</option>
                                <option value="cancelled" <?= $report['status'] == 'cancelled' ? 'selected' : '' ?>>تم الإلغاء</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">ملاحظات (اختياري)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= $report['notes'] ?></textarea>
                            <small class="form-text text-muted">أضف ملاحظات حول الإجراءات المتخذة أو سبب تغيير الحالة</small>
                        </div>
                        
                        <div class="form-group text-center mt-4">
                            <button type="submit" name="update_status" class="btn btn-action px-5">
                                <i class="fas fa-save mr-2"></i> حفظ التغييرات
                            </button>
                            <a href="clinic_rescue_reports.php" class="btn btn-secondary px-5 mr-2">
                                <i class="fas fa-arrow-right mr-2"></i> العودة
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
