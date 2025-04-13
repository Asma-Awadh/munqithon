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

// جلب بيانات المستخدم
$user_query = "SELECT * FROM people WHERE id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

// تحديث بيانات المستخدم
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    $city_id = $_POST['city_id'];
    
    // التحقق من تغيير كلمة المرور
    $password = $user_data['password'];
    if (!empty($_POST['new_password'])) {
        $password = $_POST['new_password'];
    }
    
    // التحقق من وجود صورة جديدة
    $logo_name = $user_data['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo_name = time() . '_' . $_FILES['logo']['name'];
        $upload_dir = "images/";
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            if ($_FILES['logo']['size'] < 5000000) {
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name)) {
                    // حذف الصورة القديمة إذا كانت موجودة
                    if (!empty($user_data['logo']) && file_exists($upload_dir . $user_data['logo'])) {
                        unlink($upload_dir . $user_data['logo']);
                    }
                } else {
                    $error_message = "فشل في رفع الصورة، يرجى المحاولة مرة أخرى";
                }
            } else {
                $error_message = "حجم الصورة كبير جدًا، يجب أن يكون أقل من 5 ميجابايت";
            }
        } else {
            $error_message = "نوع الملف غير مدعوم، يرجى استخدام JPG أو PNG أو GIF";
        }
    }
    
    if (empty($error_message)) {
        // تحديث بيانات المستخدم
        $update_query = "UPDATE people SET name = ?, password = ?, email = ?, phone = ?, address = ?, bio = ?, city_id = ?, logo = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $password, $email, $phone, $address, $bio, $city_id, $logo_name, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم تحديث البيانات بنجاح";
            
            // تحديث بيانات المستخدم المعروضة
            $user_data['name'] = $name;
            $user_data['phone'] = $phone;
            $user_data['email'] = $email;
            $user_data['address'] = $address;
            $user_data['bio'] = $bio;
            $user_data['city_id'] = $city_id;
            $user_data['logo'] = $logo_name;
        } else {
            $error_message = "حدث خطأ أثناء تحديث البيانات، يرجى المحاولة مرة أخرى";
        }
    }
}

// جلب المدن
$cities_query = "SELECT * FROM cities ORDER BY name ASC";
$cities_result = mysqli_query($db, $cities_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>الملف الشخصي</title>
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
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 5px solid #4CAF50;
        }
        .btn-update {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-container">
                    <div class="profile-header">
                        <h2>الملف الشخصي</h2>
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
                    
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/<?= !empty($user_data['logo']) ? $user_data['logo'] : 'default-avatar.png' ?>" alt="صورة الملف الشخصي" class="profile-image">
                        </div>
                        <div class="col-md-8">
                            <form method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">الاسم</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= $user_data['name'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= $user_data['email'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">رقم الهاتف</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= $user_data['phone'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="city_id">المدينة</label>
                                    <select class="form-control" id="city_id" name="city_id" required>
                                        <?php mysqli_data_seek($cities_result, 0); ?>
                                        <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                            <option value="<?= $city['id'] ?>" <?= ($city['id'] == $user_data['city_id']) ? 'selected' : '' ?>>
                                                <?= $city['name'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="address">العنوان</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?= $user_data['address'] ?>">
                                </div>
                                <div class="form-group">
                                    <label for="bio">نبذة شخصية</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?= $user_data['bio'] ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="logo">تغيير الصورة الشخصية</label>
                                    <input type="file" class="form-control-file" id="logo" name="logo">
                                    <small class="form-text text-muted">الصيغ المدعومة: JPG, JPEG, PNG, GIF. الحجم الأقصى: 5 ميجابايت.</small>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">كلمة المرور الجديدة (اتركها فارغة إذا لم ترغب في تغييرها)</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-update">
                                        <i class="fas fa-save ml-2"></i> حفظ التغييرات
                                    </button>
                                </div>
                            </form>
                        </div>
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
