<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("conn.php");

if (isset($_POST['login_btn'])) {
    $password = $_POST['password'];
    $email = $_POST['email'];
    
    // استخدام Prepared Statements لمنع SQL Injection
    $sql = "SELECT * FROM people WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) != 0) {
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $role_id = $row['role_id'];
        
        $_SESSION['id'] = $id;
        $_SESSION['role'] = $role_id;
        
        header("location:home.php");
        exit();
   // check if input correct
    } else {
        $error_message = "بيانات الدخول غير صحيحة، يرجى المحاولة مرة أخرى";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>تسجيل الدخول | منقذون</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            margin: 2rem auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        
        .login-image {
            flex: 1;
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            color: white;
            text-align: center;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3QgaWQ9InBhdHRlcm4tYmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiBmaWxsPSJyZ2JhKDI1NSwgMjU1LCAyNTUsIDAuMDUpIj48L3JlY3Q+PHBhdGggZmlsbD0icmdiYSgyNTUsIDI1NSwgMjU1LCAwLjEpIiBkPSJNMCAwaDEwdjEwSDB6Ij48L3BhdGg+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCBmaWxsPSJ1cmwoI3BhdHRlcm4pIiBoZWlnaHQ9IjEwMCUiIHdpZHRoPSIxMDAlIj48L3JlY3Q+PC9zdmc+');
            opacity: 0.3;
        }
        
        .login-form {
            flex: 1;
            padding: 50px 40px;
            background-color: #fff;
        }
        
        .logo-container {
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        
        .logo-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .logo-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .image-caption {
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
            max-width: 80%;
        }
        
        .login-title {
            font-size: 28px;
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .login-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            border-radius: 3px;
        }
        
        .input-group {
            margin-bottom: 25px;
        }
        
        .input-label {
            color: #555;
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            font-size: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background-color: #f9f9f9;
            color: #333;
            transition: all 0.3s ease;
            padding-right: 40px;
        }
        
        .form-input:focus {
            border-color: #4CAF50;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }
        
        .input-icon-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus + .input-icon {
            color: #4CAF50;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
            cursor: pointer;
            z-index: 2;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #4CAF50;
        }
        
        .login-btn {
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            padding: 14px 30px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
            transform: translateY(-2px);
        }
        
        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
        }
        
        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .signup-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .signup-link a:hover {
            color: #2E7D32;
            text-decoration: underline;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-right: 4px solid #d32f2f;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-left: 10px;
            font-size: 16px;
        }
        
        .back-to-home {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .back-to-home i {
            margin-left: 5px;
        }
        
        .back-to-home:hover {
            color: rgba(255, 255, 255, 0.8);
            transform: translateX(-3px);
        }
        
        .animated-element {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease;
        }
        
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }
        .delay-4 { transition-delay: 0.4s; }
        .delay-5 { transition-delay: 0.5s; }
        
        .show {
            opacity: 1;
            transform: translateY(0);
        }
        
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
                margin: 1.5rem auto;
            }
            
            .login-image {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 40px 30px;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                height: 100vh;
            }
            
            .login-image {
                padding: 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 24px;
            }
            
            .form-input {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <a href="index.php" class="back-to-home animated-element">
                <i class="fa fa-arrow-right"></i> العودة للرئيسية
            </a>
            <div class="logo-container animated-element">
                <i class="fa fa-paw logo-icon"></i>
                <h1 class="logo-text">منقذون</h1>
            </div>
            <p class="image-caption animated-element delay-1">
                منصة متكاملة لرعاية الحيوانات الأليفة والإبلاغ عن حالات الحيوانات المصابة
            </p>
            <div class="features animated-element delay-2">
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i> استشارات بيطرية
                </div>
                <div class="feature-item">
                <i class="fa fa-check-circle"></i> حجز مواعيد العيادات
                </div>
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i> الإبلاغ عن حالات الطوارئ
                </div>
            </div>
        </div>
        
        <div class="login-form">
            <h2 class="login-title animated-element">تسجيل الدخول</h2>
            
            <?php if(isset($error_message)): ?>
            <div class="error-message animated-element delay-1">
                <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <form action="login.php" method="post" class="animated-element delay-2">
                <div class="input-group">
                    <label class="input-label">البريد الإلكتروني</label>
                    <div class="input-icon-wrapper">
                        <input class="form-input" type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                </div>
                
                <div class="input-group animated-element delay-3">
                    <label class="input-label">كلمة المرور</label>
                    <div class="input-icon-wrapper">
                        <input class="form-input" type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                        <i class="fa fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>
                
                <button name="login_btn" type="submit" class="login-btn animated-element delay-4">
                    <i class="fa fa-sign-in"></i> تسجيل الدخول
                </button>
                
                <div class="signup-link animated-element delay-5">
                    <p>ليس لديك حساب؟ <a href="signup.php">إنشاء حساب جديد</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/animsition/js/animsition.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/daterangepicker/moment.min.js"></script>
    <script src="vendor/daterangepicker/daterangepicker.js"></script>
    <script src="vendor/countdowntime/countdowntime.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        // إظهار وإخفاء كلمة المرور
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // تأثيرات التحريك عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const animatedElements = document.querySelectorAll('.animated-element');
                animatedElements.forEach(function(element) {
                    element.classList.add('show');
                });
            }, 100);
        });
        
        // تأثير التركيز على حقول الإدخال
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>
