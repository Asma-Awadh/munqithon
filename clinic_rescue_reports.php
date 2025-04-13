<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول وأن المستخدم عيادة
if (!isset($_SESSION['id']) || $_SESSION['role'] != 3) {
    header("location:login.php");
    exit();
}

$clinic_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// جلب معلومات العيادة بما في ذلك المدينة
$clinic_query = "SELECT p.*, cities.name as city_name, cities.id as city_id 
                FROM people p 
                JOIN cities ON p.city_id = cities.id 
                WHERE p.id = ? AND p.role_id = 3";
$stmt = mysqli_prepare($db, $clinic_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$clinic_result = mysqli_stmt_get_result($stmt);
$clinic_data = mysqli_fetch_assoc($clinic_result);

if (!$clinic_data) {
    $error_message = "لم يتم العثور على معلومات العيادة";
}

// تحديث حالة البلاغ
if (isset($_POST['update_status'])) {
    $report_id = mysqli_real_escape_string($db, $_POST['report_id']);
    $new_status = mysqli_real_escape_string($db, $_POST['new_status']);
    $notes = mysqli_real_escape_string($db, $_POST['notes']);
    
    // التحقق من وجود عمود updated_at في الجدول
    $check_column = mysqli_query($db, "SHOW COLUMNS FROM rescue_reports LIKE 'updated_at'");
    
    if (mysqli_num_rows($check_column) > 0) {
        // إذا كان العمود موجود، استخدم الاستعلام الأصلي
        $update_query = "UPDATE rescue_reports SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
    } else {
        // إذا لم يكن العمود موجود، استخدم استعلام بدون updated_at
        $update_query = "UPDATE rescue_reports SET status = ?, notes = ? WHERE id = ?";
    }
    
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $notes, $report_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "تم تحديث حالة البلاغ بنجاح";
        // إعادة تحميل الصفحة لتحديث البيانات
        echo "<script>
            alert('تم تحديث حالة البلاغ بنجاح');
            window.location.href = 'clinic_rescue_reports.php';
        </script>";
        exit();
    } else {
        $error_message = "حدث خطأ أثناء تحديث حالة البلاغ: " . mysqli_error($db);
    }
}

// جلب بلاغات الإنقاذ في نفس مدينة العيادة
$reports_query = "SELECT r.*, c.name as city_name, p.name as reporter_name,
                 CASE
                     WHEN r.status = 'pending' THEN 'قيد المراجعة'
                     WHEN r.status = 'in_progress' THEN 'جاري العمل عليه'
                     WHEN r.status = 'completed' THEN 'تم الإنقاذ'
                     WHEN r.status = 'cancelled' THEN 'تم الإلغاء'
                     ELSE r.status
                 END as status_text
                 FROM rescue_reports r
                 JOIN cities c ON r.city_id = c.id
                 JOIN people p ON r.user_id = p.id
                 WHERE r.city_id = ?
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($db, $reports_query);
mysqli_stmt_bind_param($stmt, "i", $clinic_data['city_id']);
mysqli_stmt_execute($stmt);
$reports_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>بلاغات الإنقاذ - <?= $clinic_data['name'] ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .rescue-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .rescue-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .rescue-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .rescue-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .report-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        .report-header {
            padding: 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-body {
            padding: 15px;
        }
        .report-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .report-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .status-pending {
            background-color: #FFC107;
            color: #333;
        }
        .status-in_progress {
            background-color: #2196F3;
            color: white;
        }
        .status-completed {
            background-color: #4CAF50;
            color: white;
        }
        .status-cancelled {
            background-color: #F44336;
            color: white;
        }
        .report-info p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .report-info i {
            width: 20px;
            margin-left: 10px;
            color: #4CAF50;
        }
        .btn-action {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-action:hover {
            background-color: #388E3C;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        .no-reports {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="rescue-container">
                    <div class="rescue-header">
                        <h2><i class="fas fa-ambulance"></i> بلاغات الإنقاذ في <?= $clinic_data['city_name'] ?></h2>
                        <p class="text-muted">يمكنك مساعدة الحيوانات المحتاجة في منطقتك</p>
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
                   
                    <div class="filter-section">
                        <form method="get" class="form-inline justify-content-center">
                            <div class="form-group mx-2">
                                <label for="status_filter" class="ml-2">تصفية حسب الحالة:</label>
                                <select class="form-control" id="status_filter" name="status">
                                    <option value="">جميع البلاغات</option>
                                    <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                                    <option value="in_progress" <?= isset($_GET['status']) && $_GET['status'] == 'in_progress' ? 'selected' : '' ?>>جاري العمل عليه</option>
                                    <option value="completed" <?= isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : '' ?>>تم الإنقاذ</option>
                                    <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>تم الإلغاء</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mx-2">تصفية</button>
                            <a href="clinic_rescue_reports.php" class="btn btn-secondary">إعادة ضبط</a>
                        </form>
                    </div>

                    <?php if (mysqli_num_rows($reports_result) > 0): ?>
                        <div class="row">
                            <?php while ($report = mysqli_fetch_assoc($reports_result)):
                                // تصفية حسب الحالة إذا تم تحديدها
                                if (isset($_GET['status']) && !empty($_GET['status']) && $report['status'] != $_GET['status']) {
                                    continue;
                                }
                            ?>
                                <div class="col-md-6">
                                    <div class="report-card">
                                        <div class="report-header">
                                            <h5>بلاغ إنقاذ #<?= $report['id'] ?></h5>
                                            <span class="report-status status-<?= $report['status'] ?>">
                                                <?= $report['status_text'] ?>
                                            </span>
                                        </div>
                                        <div class="report-body">
                                            <?php if (!empty($report['image'])): ?>
                                                <img src="images/<?= $report['image'] ?>" alt="صورة البلاغ" class="report-image">
                                            <?php endif; ?>
                                           
                                            <div class="report-info">
                                             
                                                <p>
                                                    <i class="fas fa-paw"></i>
                                                    <strong>نوع الحيوان:</strong> <?= $report['animal_type'] ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <strong>المدينة:</strong> <?= $report['city_name'] ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-location-arrow"></i>
                                                    <strong>الموقع:</strong> <?= $report['location'] ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>الوصف:</strong> <?= $report['description'] ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-phone"></i>
                                                    <strong>رقم التواصل:</strong> <?= $report['contact_phone'] ?>
                                                </p>
                                                <p>
                                                    <i class="far fa-calendar-alt"></i>
                                                    <strong>تاريخ البلاغ:</strong>
                                                    <?= date('Y/m/d h:i A', strtotime($report['created_at'])) ?>
                                                </p>
                                                <?php if (!empty($report['notes'])): ?>
                                                    <p>
                                                        <i class="fas fa-sticky-note"></i>
                                                        <strong>ملاحظات:</strong> <?= $report['notes'] ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                           
                                            <!-- استبدل زر فتح Modal بهذا الرابط -->
                                            <a href="update_report.php?id=<?= $report['id'] ?>" class="btn btn-action">
                                                <i class="fas fa-edit"></i> تحديث الحالة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-reports">
                            <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                            <h4>لا توجد بلاغات إنقاذ</h4>
                            <p>لا توجد بلاغات إنقاذ في مدينتك حالياً.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function() {
            // تفعيل tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // تحديث نص زر اختيار الملف عند اختيار صورة
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName || "اختر صورة");
            });
            
            // إصلاح مشكلة اهتزاز Modal
            $('.modal').on('show.bs.modal', function () {
                $(this).find('.modal-dialog').css({
                    'transform': 'none',
                    'transition': 'none'
                });
            });
            
            // منع اهتزاز الصفحة عند فتح Modal
            $(document).on('hidden.bs.modal', '.modal', function () {
                $('.modal:visible').length && $(document.body).addClass('modal-open');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".update-status").click(function() {
                var reportId = $(this).data("id");
                var currentStatus = $(this).data("status");
                var currentNotes = $(this).data("notes");
                
                Swal.fire({
                    title: 'تحديث حالة البلاغ #' + reportId,
                    html: `
                        <form id="updateForm">
                            <div class="form-group text-right">
                                <label for="new_status">الحالة الجديدة</label>
                                <select class="form-control" id="new_status" required>
                                    <option value="pending" ${currentStatus == 'pending' ? 'selected' : ''}>قيد المراجعة</option>
                                    <option value="in_progress" ${currentStatus == 'in_progress' ? 'selected' : ''}>جاري العمل عليه</option>
                                    <option value="completed" ${currentStatus == 'completed' ? 'selected' : ''}>تم الإنقاذ</option>
                                    <option value="cancelled" ${currentStatus == 'cancelled' ? 'selected' : ''}>تم الإلغاء</option>
                                </select>
                            </div>
                            <div class="form-group text-right">
                                <label for="notes">ملاحظات (اختياري)</label>
                                <textarea class="form-control" id="notes" rows="3">${currentNotes || ''}</textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'حفظ التغييرات',
                    cancelButtonText: 'إلغاء',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return {
                            report_id: reportId,
                            new_status: document.getElementById('new_status').value,
                            notes: document.getElementById('notes').value
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        // إرسال البيانات باستخدام AJAX
                        $.ajax({
                            url: 'update_report_status.php',
                            type: 'POST',
                            data: result.value,
                            success: function(response) {
                                Swal.fire(
                                    'تم التحديث!',
                                    'تم تحديث حالة البلاغ بنجاح.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire(
                                    'خطأ!',
                                    'حدث خطأ أثناء تحديث حالة البلاغ.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
