<?php
include("conn.php");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Fetch user data
$sql = "SELECT p.*, c.name as city_name, r.name as role_name, b.name as bank_name
        FROM people p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN roles r ON p.role_id = r.id
        LEFT JOIN banks b ON p.bank_id = b.id
        WHERE p.id = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Fetch cities for dropdown
$sql_cities = "SELECT * FROM cities";
$query_cities = mysqli_query($db, $sql_cities);

// استعلام لجلب البنوك
$sql_banks = "SELECT * FROM banks";
$query_banks = mysqli_query($db, $sql_banks);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    $city_id = $_POST['city_id'];
    
    // معلومات البنك (للأطباء فقط)
    $bank_id = isset($_POST['bank_id']) && !empty($_POST['bank_id']) ? $_POST['bank_id'] : null;
    
    // تصحيح إضافي للتأكد من قيمة معرف البنك
    if ($bank_id === '0' || $bank_id === 0) {
        $bank_id = null;
    }
    
    $account_number = isset($_POST['account_number']) && !empty($_POST['account_number']) ? $_POST['account_number'] : null;
    $iban = isset($_POST['iban']) && !empty($_POST['iban']) ? $_POST['iban'] : null;
    
    // Check if password is being updated
    $password_update = "";
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $password_update = ", password = ?";
    }
    
    // Check if logo is being updated
    $logo_update = "";
    $logo_name = $user['logo']; // Default to current logo
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            if ($_FILES['logo']['size'] < 5000000) { // 5MB max
                $logo_name = time() . '_' . $_FILES['logo']['name'];
                $upload_dir = "images/";
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name)) {
                    // Delete old logo if it exists and is not the default
                    if ($user['logo'] && $user['logo'] != 'default.png' && file_exists($upload_dir . $user['logo'])) {
                        unlink($upload_dir . $user['logo']);
                    }
                    
                    $logo_update = ", logo = ?";
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
    
    if (!isset($error_message)) {
        // بناء استعلام SQL
        $sql = "UPDATE people SET name = ?, phone = ?, email = ?, address = ?, bio = ?, city_id = ?";
        
        // إضافة كلمة المرور إذا تم تحديثها
        if (!empty($password_update)) {
            $sql .= $password_update;
        }
        
        // إضافة الصورة إذا تم تحديثها
        if (!empty($logo_update)) {
            $sql .= $logo_update;
        }
        
        // إضافة معلومات البنك للأطباء فقط
        if ($_SESSION['role'] == 2) {
            $sql .= ", bank_id = ?, account_number = ?, iban = ?";
        }
        
        // إضافة شرط WHERE
        $sql .= " WHERE id = ?";
        
        // إعداد المعلمات
        $params = array($name, $phone, $email, $address, $bio, $city_id);
        $types = "ssssssi"; // s للنصوص، i للأرقام الصحيحة
        
        // إضافة كلمة المرور إذا تم تحديثها
        if (!empty($password_update)) {
            $params[] = $password;
            $types .= "s";
        }
        
        // إضافة الصورة إذا تم تحديثها
        if (!empty($logo_update)) {
            $params[] = $logo_name;
            $types .= "s";
        }
        
        // إضافة معلومات البنك للأطباء فقط
        if ($_SESSION['role'] == 2) {
            $params[] = $bank_id;
            $params[] = $account_number;
            $params[] = $iban;
            $types .= "iss"; // i للأرقام الصحيحة، s للنصوص
        }
        
        // إضافة معرف المستخدم
        $params[] = $user_id;
        
        // تنفيذ الاستعلام
        $stmt = mysqli_prepare($db, $sql);
        
        // ربط المعلمات
        $bind_params = array();
        $bind_params[] = $types;
        
        for ($i = 0; $i < count($params); $i++) {
            $bind_params[] = &$params[$i];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $bind_params);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم تحديث الملف الشخصي بنجاح";
            
            // Refresh user data
            $sql = "SELECT p.*, c.name as city_name, r.name as role_name, b.name as bank_name
                    FROM people p
                    LEFT JOIN cities c ON p.city_id = c.id
                    LEFT JOIN roles r ON p.role_id = r.id
                    LEFT JOIN banks b ON p.bank_id = b.id
                    WHERE p.id = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error_message = "حدث خطأ أثناء تحديث الملف الشخصي: " . mysqli_error($db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>الملف الشخصي</title>
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
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin: 0 auto 20px;
            display: block;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .btn-primary:hover {
            background-color: #388E3C;
            border-color: #388E3C;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .profile-info {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .profile-info h3 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .profile-info p {
            margin-bottom: 10px;
        }
        
        .profile-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-white mb-4">الملف الشخصي</h1>
                
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user"></i> معلومات الملف الشخصي
                    </div>
                    <div class="card-body text-center">
                        <img src="images/<?php echo $user['logo'] ? $user['logo'] : 'default.png'; ?>" class="profile-img" alt="صورة الملف الشخصي">
                        <h3><?php echo $user['name']; ?></h3>
                        <p class="text-muted"><?php echo $user['role_name']; ?></p>
                        
                        <div class="profile-info text-right">
                            <h3>معلومات الاتصال</h3>
                            <p><strong>البريد الإلكتروني:</strong> <?php echo $user['email']; ?></p>
                            <p><strong>رقم الهاتف:</strong> <?php echo $user['phone']; ?></p>
                            <p><strong>العنوان:</strong> <?php echo $user['address']; ?></p>
                            <p><strong>المدينة:</strong> <?php echo $user['city_name']; ?></p>
                        </div>
                        
                        <div class="profile-info text-right">
                            <h3>نبذة شخصية</h3>
                            <p><?php echo $user['bio'] ? $user['bio'] : 'لا توجد نبذة شخصية'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> تعديل الملف الشخصي
                    </div>
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name">الاسم</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email">البريد الإلكتروني</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone">رقم الهاتف</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="city_id">المدينة</label>
                                        <select class="form-control" id="city_id" name="city_id" required>
                                            <?php while($city = mysqli_fetch_assoc($query_cities)): ?>
                                            <option value="<?php echo $city['id']; ?>" <?php echo ($city['id'] == $user['city_id']) ? 'selected' : ''; ?>>
                                                <?php echo $city['name']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="address">العنوان</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $user['address']; ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="bio">نبذة شخصية</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo $user['bio']; ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="password">كلمة المرور (اتركها فارغة إذا لم ترغب في تغييرها)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="form-group mb-3">
                                <label for="logo">صورة الملف الشخصي</label>
                                <input type="file" class="form-control" id="logo" name="logo">
                                <small class="form-text text-muted">اختر صورة بصيغة JPG أو PNG أو GIF بحجم أقل من 5 ميجابايت</small>
                            </div>
                            
                            <!-- إضافة حقول معلومات البنك للأطباء فقط -->
                            <?php if($_SESSION['role'] == 2): // إذا كان المستخدم طبيب ?>
                            <hr>
                            <h4 class="mt-4 mb-3"><i class="fas fa-university"></i> معلومات البنك</h4>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="bank_id">اسم البنك</label>
                                        <select class="form-control" id="bank_id" name="bank_id">
                                            <option value="">اختر البنك</option>
                                            <?php
                                            // إعادة تعيين مؤشر النتائج
                                            mysqli_data_seek($query_banks, 0);
                                            while($bank = mysqli_fetch_assoc($query_banks)):
                                            ?>
                                            <option value="<?php echo $bank['id']; ?>" <?php echo ($user['bank_id'] == $bank['id']) ? 'selected' : ''; ?>>
                                                <?php echo $bank['name']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="account_number">رقم الحساب</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo isset($user['account_number']) ? $user['account_number'] : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="iban">رقم الآيبان (IBAN)</label>
                                        <input type="text" class="form-control" id="iban" name="iban" value="<?php echo isset($user['iban']) ? $user['iban'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary w-100">تحديث الملف الشخصي</button>
                        </form>
                    </div>
                </div>
                
                <?php if($_SESSION['role'] == 2): // إذا كان المستخدم طبيب ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-stethoscope"></i> معلومات الطبيب
                    </div>
                    <div class="card-body">
                        <?php if($_SESSION['role'] == 2 && (!empty($user['bank_name']) || !empty($user['account_number']) || !empty($user['iban']))): ?>
                        <div class="profile-info text-right">
                            <h3>معلومات البنك</h3>
                            <p><strong>اسم البنك:</strong> <?php echo $user['bank_name'] ? $user['bank_name'] : 'غير محدد'; ?></p>
                            <p><strong>رقم الحساب:</strong> <?php echo $user['account_number'] ? $user['account_number'] : 'غير محدد'; ?></p>
                            <p><strong>رقم الآيبان:</strong> <?php echo $user['iban'] ? $user['iban'] : 'غير محدد'; ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <p><strong>ملاحظة:</strong> يمكنك إدارة مواعيدك وخدماتك من خلال صفحة المواعيد.</p>
                            <a href="appointments.php" class="btn btn-info mt-2">
                                <i class="fas fa-calendar-alt"></i> إدارة المواعيد
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <p><strong>ملاحظة:</strong> يمكنك الاطلاع على طلبات الزيارة الخارجية من خلال صفحة طلبات الزيارة.</p>
                            <a href="visit_requests.php" class="btn btn-info mt-2">
                                <i class="fas fa-clipboard-list"></i> طلبات الزيارة
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <p><strong>ملاحظة:</strong> يمكنك التواصل مع العملاء من خلال نظام الدردشة.</p>
                            <a href="chat.php" class="btn btn-info mt-2">
                                <i class="fas fa-comments"></i> الدردشة
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>