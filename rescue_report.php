<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول
if (!isset($_SESSION['id'])) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// جلب قائمة المدن
$cities_query = "SELECT * FROM cities ORDER BY name ASC";
$cities_result = mysqli_query($db, $cities_query);

// إرسال بلاغ إنقاذ جديد
if (isset($_POST['submit_report'])) {
    $animal_type = mysqli_real_escape_string($db, $_POST['animal_type']);
    $city_id = mysqli_real_escape_string($db, $_POST['city_id']);
    $location = mysqli_real_escape_string($db, $_POST['location']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $contact_phone = mysqli_real_escape_string($db, $_POST['contact_phone']);
    
    // التحقق من رفع الصورة
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $image_name = 'rescue_' . time() . '_' . $_FILES['image']['name'];
            $upload_path = 'images/' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // تم رفع الصورة بنجاح
            } else {
                $error_message = "حدث خطأ أثناء رفع الصورة، يرجى المحاولة مرة أخرى";
            }
        } else {
            $error_message = "نوع الملف غير مسموح به، يرجى اختيار صورة بصيغة JPG أو PNG";
        }
    }
    
    if (empty($error_message)) {
        // إدخال بلاغ الإنقاذ في قاعدة البيانات
        $insert_query = "INSERT INTO rescue_reports (user_id, animal_type, city_id, location, description, contact_phone, image, status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($stmt, "isiisss", $user_id, $animal_type, $city_id, $location, $description, $contact_phone, $image_name);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم إرسال بلاغ الإنقاذ بنجاح، سيتم التواصل معك قريباً";
        } else {
            $error_message = "حدث خطأ أثناء إرسال البلاغ، يرجى المحاولة مرة أخرى";
        }
    }
}

// جلب بلاغات الإنقاذ السابقة للمستخدم
$reports_query = "SELECT r.*, c.name as city_name,
                 CASE 
                     WHEN r.status = 'pending' THEN 'قيد المراجعة'
                     WHEN r.status = 'in_progress' THEN 'جاري العمل عليه'
                     WHEN r.status = 'completed' THEN 'تم الإنقاذ'
                     WHEN r.status = 'cancelled' THEN 'تم الإلغاء'
                     ELSE r.status
                 END as status_text
                 FROM rescue_reports r
                 JOIN cities c ON r.city_id = c.id
                 WHERE r.user_id = ?
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($db, $reports_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$reports_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>بلاغ إنقاذ</title>
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
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #388E3C;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .custom-file-label {
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
        }
        .custom-file-label::after {
            height: auto;
            padding: 12px 15px;
            background-color: #4CAF50;
            color: white;
            content: "اختر صورة";
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
        .nav-tabs {
            border-bottom: 2px solid #4CAF50;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #555;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 0;
            margin-right: 5px;
        }
        .nav-tabs .nav-link.active {
            color: #4CAF50;
            background-color: transparent;
            border-bottom: 3px solid #4CAF50;
        }
        .no-reports {
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
                <div class="rescue-container">
                    <div class="rescue-header">
                        <h2><i class="fas fa-ambulance"></i> بلاغ إنقاذ</h2>
                        <p class="text-muted">ساعدنا في إنقاذ الحيوانات المحتاجة للمساعدة</p>
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
                    
                    <ul class="nav nav-tabs" id="rescueTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="new-report-tab" data-toggle="tab" href="#new-report" role="tab">
                                <i class="fas fa-plus-circle"></i> بلاغ جديد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="my-reports-tab" data-toggle="tab" href="#my-reports" role="tab">
                                <i class="fas fa-history"></i> بلاغاتي السابقة
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="rescueTabsContent">
                        <!-- نموذج بلاغ جديد -->
                        <div class="tab-pane fade show active" id="new-report" role="tabpanel">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="animal_type">نوع الحيوان</label>
                                            <input type="text" class="form-control" id="animal_type" name="animal_type" placeholder="مثال: قطة، كلب، طائر..." required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city_id">المدينة</label>
                                            <select class="form-control" id="city_id" name="city_id" required>
                                                <option value="">اختر المدينة</option>
                                                <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                                    <option value="<?= $city['id'] ?>"><?= $city['name'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">الموقع بالتفصيل</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="وصف دقيق للموقع" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">وصف الحالة</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="اشرح حالة الحيوان والمساعدة المطلوبة" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_phone">رقم الهاتف للتواصل</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" placeholder="رقم هاتف للتواصل معك" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">صورة (اختياري)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                        <label class="custom-file-label" for="image">اختر صورة للحيوان</label>
                                    </div>
                                    <small class="form-text text-muted">الصورة تساعد فريق الإنقاذ في تحديد الحالة بشكل أفضل</small>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" name="submit_report" class="btn btn-submit">
                                        <i class="fas fa-paper-plane"></i> إرسال البلاغ
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- بلاغاتي السابقة -->
                        <div class="tab-pane fade" id="my-reports" role="tabpanel">
                            <?php if (mysqli_num_rows($reports_result) > 0): ?>
                                <div class="row">
                                    <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
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
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-reports">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                                    <h4>لا توجد بلاغات سابقة</h4>
                                    <p>لم تقم بإرسال أي بلاغات إنقاذ حتى الآن.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // تفعيل التبويبات
        $(document).ready(function() {
            $('#rescueTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
            
            // عرض اسم الملف المختار
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName || "اختر صورة للحيوان");
            });
        });
    </script>
</body>
</html>
