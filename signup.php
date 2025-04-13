<?php
include("conn.php");

// تحسين الأمان باستخدام Prepared Statements
if (isset($_POST['register_btn'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    $city_id = $_POST['cities'];
    $role_id = $_POST['roles'];
    
    // التحقق من وجود الصورة
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        // إنشاء اسم فريد للصورة لتجنب تكرار الأسماء
        $logo_name = time() . '_' . $_FILES['logo']['name'];
        $upload_dir = "images/";
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        // التحقق من نوع الملف
        if(in_array($file_ext, $allowed_types)) {
            // التحقق من حجم الملف (5MB كحد أقصى)
            if($_FILES['logo']['size'] < 5000000) {
                if(move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name)) {
                    // استخدام Prepared Statements لمنع SQL Injection
                    $stmt = mysqli_prepare($db, "INSERT INTO people(name, password, email, phone, address, bio, city_id, role_id, logo, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    // إضافة متغير للحالة
                    $status = ($role_id == 1) ? 1 : 0; // إذا كان المستخدم عميل (client) يتم تعيين الحالة إلى 1
                    mysqli_stmt_bind_param($stmt, "ssssssiisd", $name, $password, $email, $phone, $address, $bio, $city_id, $role_id, $logo_name, $status);
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $id = mysqli_insert_id($db);
                        $_SESSION['id'] = $id;
                        $_SESSION['role'] = $role_id;
                        $_SESSION['type'] = $role_id;
                        
                        header("location:home.php");
                        exit();
                    } else {
                        $error_message = "فشل إنشاء الحساب، يرجى التحقق من المدخلات";
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
    } else {
        $error_message = "يرجى اختيار صورة للملف الشخصي";
    }
}

// استعلام لجلب الأدوار
$sql_role = "SELECT * FROM roles WHERE id != 4";
$query_roles = mysqli_query($db, $sql_role);

// استعلام لجلب المدن
$sql_cities = "SELECT * FROM cities";
$query_cities = mysqli_query($db, $sql_cities);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إنشاء حساب جديد</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            overflow-x: hidden;
            background-color: #f5f7fa;
        }
        
        .container-login100 {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            min-height: 100vh;
            padding: 50px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .wrap-login100 {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            padding: 40px 55px;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            position: relative;
            z-index: 1;
            max-width: 700px;
            width: 90%;
            margin: 0 auto;
        }
        
        .login100-form-title {
            font-size: 28px;
            color: #333;
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
        }
        
        .login100-form-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        
        .input-label {
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        
        .wrap-input100 {
            position: relative;
            margin-bottom: 25px;
        }
        
        .input100 {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            color: #333;
            width: 100%;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .input100:focus {
            background: #fff;
            box-shadow: 0 0 0 2px rgba(78, 84, 200, 0.2);
            border-color: #4e54c8;
            outline: none;
        }
        
        .focus-input100 {
            position: absolute;
            display: block;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            border-radius: 10px;
        }
        
        .input100:focus + .focus-input100::before {
            opacity: 1;
        }
        
        .focus-input100::before {
            content: "";
            display: block;
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            opacity: 0;
            transition: all 0.4s;
        }
        
        .login100-form-btn {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            padding: 12px 30px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .login100-form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #4e54c8;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            padding-bottom: 2px;
        }
        
        .login-link a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, #4e54c8, #8f94fb);
            transition: width 0.3s ease;
        }
        
        .login-link a:hover::after {
            width: 100%;
        }
        
        .error-message {
            background-color: #fff5f5;
            color: #e53e3e;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e53e3e;
            font-size: 14px;
            text-align: center;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
            cursor: pointer;
            z-index: 2;
        }
        
        /* تأثيرات متحركة */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animated-element {
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        .delay-5 { animation-delay: 1.0s; }
        .delay-6 { animation-delay: 1.2s; }
        
        /* تجاوب مع الشاشات الصغيرة */
        @media (max-width: 576px) {
            .wrap-login100 {
                padding: 30px 25px;
            }
            
            .login100-form-title {
                font-size: 24px;
            }
            
            .input100 {
                padding: 12px 15px;
            }
        }
        
        /* تصميم حقل رفع الملف */
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-upload-button {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            color: #333;
            width: 100%;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .file-upload-button i {
            margin-left: 10px;
            font-size: 20px;
            color: #4e54c8;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: #718096;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* تصميم القوائم المنسدلة */
        select.input100 {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23a0aec0" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: left 10px center;
            padding-left: 40px;
        }
        
        /* تصميم الشبكة للنموذج */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-grid-full {
            grid-column: 1 / -1;
        }
        
        /* تعديل لون النص في الحقول */
        .input100 {
            color: #333;
        }
        
        /* تعديل لون النص في placeholder */
        ::placeholder {
            color: #a0aec0 !important;
            opacity: 0.7;
        }
        
        /* تعديل لون النص في القوائم المنسدلة */
        select.input100 option {
            color: #333;
            background-color: #fff;
            padding: 10px;
        }
        
        /* تحسين مظهر القوائم المنسدلة */
        select.input100 {
            color: #333;
            font-weight: 500;
            padding-right: 40px;
            text-align: right;
            direction: rtl;
            font-size: 16px;
        }
        
        /* تحسين مظهر خيارات القوائم المنسدلة */
        select.input100 option {
            padding: 10px;
            font-size: 16px;
            font-weight: 500;
        }
        
        /* تحسين مظهر الخيار المحدد افتراضياً */
        select.input100 option[disabled] {
            color: #a0aec0;
            font-style: italic;
        }
        
        /* مؤشرات الخطوات */
        .steps-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step-indicator {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .step-indicator.active {
            background-color: #4e54c8;
            color: white;
            box-shadow: 0 0 0 5px rgba(78, 84, 200, 0.2);
        }
        
        .step-indicator::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            background-color: #e2e8f0;
            right: -100%;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .step-indicator:last-child::after {
            display: none;
        }
        
        .step-indicator.active::after {
            background-color: #4e54c8;
        }
        
        /* تصميم الخطوات */
        .step {
            display: none;
        }
        
        .step.active {
            display: block;
            animation: fadeIn 0.5s ease forwards;
        }
        
        .step-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .step-button {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px 20px;
            color: #4a5568;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .step-button:hover {
            background: #edf2f7;
        }
        
        .step-button.next {
            background: linear-gradient(90deg, #4e54c8, #8f94fb);
            color: white;
            border: none;
        }
        
        .step-button.next:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <form enctype="multipart/form-data" action="signup.php" method="post" class="login100-form validate-form">
                    <h1 class="login100-form-title animated-element">
                        إنشاء حساب جديد
                    </h1>
                    
                    <!-- مؤشرات الخطوات -->
                    <div class="steps-container animated-element delay-1">
                        <div class="step-indicator active" id="step1-indicator">1</div>
                        <div class="step-indicator" id="step2-indicator">2</div>
                        <div class="step-indicator" id="step3-indicator">3</div>
                    </div>
                    
                    <?php if(isset($error_message)): ?>
                    <div class="error-message animated-element delay-1">
                        <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- الخطوة الأولى: المعلومات الأساسية -->
                    <div class="step active" id="step1">
                        <div class="form-grid">
                            <!-- اسم المستخدم -->
                            <div class="animated-element delay-1">
                                <label class="input-label">اسم المستخدم</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل اسم المستخدم">
                                    <input class="input100" type="text" name="name" placeholder="أدخل اسم المستخدم" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-user input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- البريد الإلكتروني -->
                            <div class="animated-element delay-1">
                                <label class="input-label">البريد الإلكتروني</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل البريد الإلكتروني">
                                    <input class="input100" type="email" name="email" placeholder="أدخل البريد الإلكتروني" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-envelope input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- كلمة المرور -->
                            <div class="animated-element delay-2">
                                <label class="input-label">كلمة المرور</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل كلمة المرور">
                                    <input minlength="8" class="input100" type="password" id="password" name="password" placeholder="أدخل كلمة المرور (8 أحرف على الأقل)" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-eye password-toggle" id="togglePassword"></i>
                                </div>
                            </div>
                            
                            <!-- نوع المستخدم -->
                            <div class="animated-element delay-2">
                                <label class="input-label">نوع المستخدم</label>
                                <div class="wrap-input100 validate-input">
                                    <select class="input100" name="roles" required>
                                        <option value="" disabled selected>اختر نوع المستخدم</option>
                                        <?php foreach ($query_roles as $row_role) { ?>
                                            <option value="<?= $row_role['id']; ?>" style="padding: 10px; font-weight: 500;"> <?= $row_role['name']; ?> </option>
                                        <?php } ?>
                                    </select>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-users input-icon"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-buttons">
                            <div></div> <!-- فارغ للمحاذاة -->
                            <button type="button" id="to-step2" class="step-button next">التالي <i class="fa fa-arrow-left"></i></button>
                        </div>
                    </div>
                    
                    <!-- الخطوة الثانية: معلومات الاتصال -->
                    <div class="step" id="step2">
                        <div class="form-grid">
                            <!-- رقم الهاتف -->
                            <div class="animated-element delay-1">
                                <label class="input-label">رقم الهاتف</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل رقم الهاتف">
                                    <input minlength="10" class="input100" type="text" name="phone" placeholder="أدخل رقم الهاتف" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-phone input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- المدينة -->
                            <div class="animated-element delay-1">
                                <label class="input-label">المدينة</label>
                                <div class="wrap-input100 validate-input">
                                    <select class="input100" name="cities" required>
                                        <option value="" disabled selected>اختر المدينة</option>
                                        <?php foreach ($query_cities as $row_city) { ?>
                                            <option value="<?= $row_city['id']; ?>" style="padding: 10px; font-weight: 500;"> <?= $row_city['name']; ?> </option>
                                        <?php } ?>
                                    </select>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-building input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- العنوان -->
                            <div class="animated-element delay-2">
                                <label class="input-label">العنوان</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل العنوان">
                                    <input class="input100" type="text" name="address" placeholder="أدخل العنوان" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-map-marker input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- نبذة تعريفية -->
                            <div class="animated-element delay-2">
                                <label class="input-label">نبذة تعريفية</label>
                                <div class="wrap-input100 validate-input" data-validate="أدخل نبذة تعريفية">
                                    <input class="input100" type="text" name="bio" placeholder="أدخل نبذة تعريفية" required>
                                    <span class="focus-input100"></span>
                                    <i class="fa fa-info-circle input-icon"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-buttons">
                            <button type="button" id="back-to-step1" class="step-button"><i class="fa fa-arrow-right"></i> السابق</button>
                            <button type="button" id="to-step3" class="step-button next">التالي <i class="fa fa-arrow-left"></i></button>
                        </div>
                    </div>
                    
                    <!-- الخطوة الثالثة: الصورة الشخصية -->
                    <div class="step" id="step3">
                        <div class="form-grid">
                            <!-- الصورة الشخصية -->
                            <div class="animated-element delay-1 form-grid-full">
                                <label class="input-label">الصورة الشخصية</label>
                                <div class="wrap-input100">
                                    <div class="file-upload">
                                        <input type="file" name="logo" id="logo" class="file-upload-input" accept="image/*" required>
                                        <div class="file-upload-button">
                                            <i class="fa fa-cloud-upload"></i> اختر صورة
                                        </div>
                                    </div>
                                    <div class="file-name" id="file-name">لم يتم اختيار ملف</div>
                                </div>
                            </div>
                            
                            <!-- معاينة الصورة -->
                            <div class="animated-element delay-2 form-grid-full" style="text-align: center; margin-top: 20px;">
                                <div id="image-preview-container" style="display: none;">
                                    <label class="input-label">معاينة الصورة</label>
                                    <img id="image-preview" src="#" alt="معاينة الصورة" style="max-width: 200px; max-height: 200px; border-radius: 10px; margin-top: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-buttons">
                            <button type="button" id="back-to-step2" class="step-button"><i class="fa fa-arrow-right"></i> السابق</button>
                            <button name="register_btn" type="submit" class="step-button next">
                                <i class="fa fa-user-plus"></i> إنشاء الحساب
                            </button>
                        </div>
                    </div>
                    
                    <div class="login-link animated-element delay-6">
                        <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="dropDownSelect1"></div>
    <!--===============================================================================================-->
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/animsition/js/animsition.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/select2/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/daterangepicker/moment.min.js"></script>
    <script src="vendor/daterangepicker/daterangepicker.js"></script>
    <!--===============================================================================================-->
    <script src="vendor/countdowntime/countdowntime.js"></script>
    <!--===============================================================================================-->
    <script src="js/main.js"></script>
    <!--===============================================================================================-->
    <script>
        // إظهار وإخفاء كلمة المرور
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // عرض اسم الملف المختار
        document.getElementById('logo').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'لم يتم اختيار ملف';
            document.getElementById('file-name').textContent = fileName;
            
            // إظهار معاينة الصورة
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('image-preview-container').style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // التنقل بين الخطوات
        document.getElementById('to-step2').addEventListener('click', function() {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('step1-indicator').classList.remove('active');
            document.getElementById('step2-indicator').classList.add('active');
        });
        
        document.getElementById('back-to-step1').addEventListener('click', function() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            document.getElementById('step2-indicator').classList.remove('active');
            document.getElementById('step1-indicator').classList.add('active');
        });
        
        document.getElementById('to-step3').addEventListener('click', function() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step3').classList.add('active');
            document.getElementById('step2-indicator').classList.remove('active');
            document.getElementById('step3-indicator').classList.add('active');
        });
        
        document.getElementById('back-to-step2').addEventListener('click', function() {
            document.getElementById('step3').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('step3-indicator').classList.remove('active');
            document.getElementById('step2-indicator').classList.add('active');
        });
        
        // تأثيرات إضافية عند التحميل
        $(document).ready(function() {
            // تأثير التلاشي للعناصر
            $('.animated-element').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        'opacity': '1',
                        'transform': 'translateY(0)',
                        'transition': 'all 0.8s ease'
                    });
                }, 200 * index);
            });
            
            // تأثير الحقول عند التركيز
            $('.input100').focus(function() {
                $(this).parent().addClass('focused');
            }).blur(function() {
                if ($(this).val() === '') {
                    $(this).parent().removeClass('focused');
                }
            });
            
            // تحقق من صحة المدخلات
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // التحقق من كلمة المرور
                const password = $('#password').val();
                if (password.length < 8) {
                    alert('يجب أن تكون كلمة المرور 8 أحرف على الأقل');
                    isValid = false;
                }
                
                // التحقق من رقم الهاتف
                const phone = $('input[name="phone"]').val();
                if (phone.length < 10) {
                    alert('يجب أن يكون رقم الهاتف 10 أرقام على الأقل');
                    isValid = false;
                }
                
                // التحقق من الصورة
                const fileInput = $('#logo')[0];
                if (fileInput.files.length === 0) {
                    alert('يرجى اختيار صورة شخصية');
                    isValid = false;
                } else {
                    const fileSize = fileInput.files[0].size / 1024 / 1024; // بالميجابايت
                    if (fileSize > 5) {
                        alert('حجم الصورة كبير جدًا، يجب أن يكون أقل من 5 ميجابايت');
                        isValid = false;
                    }
                    
                    const fileType = fileInput.files[0].type;
                    if (!fileType.match('image.*')) {
                        alert('يرجى اختيار ملف صورة صالح');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // التحقق من اكتمال الحقول قبل الانتقال للخطوة التالية
            $('#to-step2').click(function(e) {
                const name = $('input[name="name"]').val();
                const email = $('input[name="email"]').val();
                const password = $('input[name="password"]').val();
                const role = $('select[name="roles"]').val();
                
                if (!name || !email || !password || !role) {
                    alert('يرجى ملء جميع الحقول المطلوبة قبل المتابعة');
                    e.preventDefault();
                    return false;
                }
                
                if (password.length < 8) {
                    alert('يجب أن تكون كلمة المرور 8 أحرف على الأقل');
                    e.preventDefault();
                    return false;
                }
            });
            
            $('#to-step3').click(function(e) {
                const phone = $('input[name="phone"]').val();
                const city = $('select[name="cities"]').val();
                const address = $('input[name="address"]').val();
                const bio = $('input[name="bio"]').val();
                
                if (!phone || !city || !address || !bio) {
                    alert('يرجى ملء جميع الحقول المطلوبة قبل المتابعة');
                    e.preventDefault();
                    return false;
                }
                
                if (phone.length < 10) {
                    alert('يجب أن يكون رقم الهاتف 10 أرقام على الأقل');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>

