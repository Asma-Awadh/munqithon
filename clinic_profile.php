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

// جلب بيانات العيادة الحالية
$query = "SELECT * FROM people WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $clinic_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$clinic_data = mysqli_fetch_assoc($result);

// تحديث بيانات العيادة
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    $city_id = $_POST['city_id'];
    
    // التحقق من تغيير كلمة المرور
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $password_query = ", password = ?";
    } else {
        $password = "";
        $password_query = "";
    }
    
    // التحقق من وجود صورة جديدة
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo_name = time() . '_' . $_FILES['logo']['name'];
        $upload_dir = "images/";
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        // التحقق من نوع الملف
        if (in_array($file_ext, $allowed_types)) {
            // التحقق من حجم الملف (5MB كحد أقصى)
            if ($_FILES['logo']['size'] < 5000000) {
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name)) {
                    // حذف الصورة القديمة إذا وجدت
                    if (!empty($clinic_data['logo']) && file_exists($upload_dir . $clinic_data['logo'])) {
                        unlink($upload_dir . $clinic_data['logo']);
                    }
                    $logo_query = ", logo = ?";
                } else {
                    $error_message = "فشل في رفع الصورة، يرجى المحاولة مرة أخرى";
                    $logo_query = "";
                    $logo_name = "";
                }
            } else {
                $error_message = "حجم الصورة كبير جدًا، يجب أن يكون أقل من 5 ميجابايت";
                $logo_query = "";
                $logo_name = "";
            }
        } else {
            $error_message = "نوع الملف غير مدعوم، يرجى استخدام JPG أو PNG أو GIF";
            $logo_query = "";
            $logo_name = "";
        }
    } else {
        $logo_query = "";
        $logo_name = "";
    }
    
    // تحديث البيانات في قاعدة البيانات
    if (empty($error_message)) {
        $update_query = "UPDATE people SET name = ?, email = ?, phone = ?, address = ?, bio = ?, city_id = ?";
        
        if (!empty($password_query)) {
            $update_query .= $password_query;
        }
        
        if (!empty($logo_query)) {
            $update_query .= $logo_query;
        }
        
        $update_query .= " WHERE id = ?";
        
        $stmt = mysqli_prepare($db, $update_query);
        
        if (!empty($password) && !empty($logo_name)) {
            mysqli_stmt_bind_param($stmt, "sssssiss", $name, $email, $phone, $address, $bio, $city_id, $password, $logo_name, $clinic_id);
        } elseif (!empty($password)) {
            mysqli_stmt_bind_param($stmt, "sssssis", $name, $email, $phone, $address, $bio, $city_id, $password, $clinic_id);
        } elseif (!empty($logo_name)) {
            mysqli_stmt_bind_param($stmt, "sssssiss", $name, $email, $phone, $address, $bio, $city_id, $logo_name, $clinic_id);
        } else {
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $address, $bio, $city_id, $clinic_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم تحديث البيانات بنجاح";
            
            // تحديث بيانات العيادة بعد التعديل
            $query = "SELECT * FROM people WHERE id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "i", $clinic_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $clinic_data = mysqli_fetch_assoc($result);
        } else {
            $error_message = "حدث خطأ أثناء تحديث البيانات، يرجى المحاولة مرة أخرى";
        }
    }
}

// استعلام لجلب المدن
$cities_query = "SELECT * FROM cities";
$cities_result = mysqli_query($db, $cities_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>تحديث حساب العيادة</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 5px solid #4CAF50;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        .btn-update {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        .btn-update:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="profile-container">
                    <div class="profile-header">
                        <h2>تحديث حساب العيادة</h2>
                        <?php if (!empty($clinic_data['logo'])): ?>
                            <img src="images/<?= $clinic_data['logo'] ?>" alt="صورة العيادة" class="profile-img">
                        <?php else: ?>
                            <img src="images/default_clinic.png" alt="صورة افتراضية" class="profile-img">
                        <?php endif; ?>
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
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">اسم العيادة</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= $clinic_data['name'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= $clinic_data['email'] ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">رقم الهاتف</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= $clinic_data['phone'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city_id">المدينة</label>
                                    <select class="form-control" id="city_id" name="city_id" required>
                                        <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                            <option value="<?= $city['id'] ?>" <?= ($city['id'] == $clinic_data['city_id']) ? 'selected' : '' ?>>
                                                <?= $city['name'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">العنوان</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= $clinic_data['address'] ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">نبذة عن العيادة</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?= $clinic_data['bio'] ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">كلمة المرور (اتركها فارغة إذا لم ترغب في تغييرها)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">شعار العيادة</label>
                                    <input type="file" class="form-control-file" id="logo" name="logo">
                                    <small class="form-text text-muted">اختر صورة بصيغة JPG أو PNG أو GIF بحجم أقل من 5 ميجابايت</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="update_profile" class="btn btn-update">
                                <i class="fas fa-save ml-2"></i> حفظ التغييرات
                            </button>
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
