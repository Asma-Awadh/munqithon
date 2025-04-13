<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to home.php if user is already logged in
if(isset($_SESSION['id'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>منقذون - رعاية الحيوانات الأليفة</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="منقذون - منصة متكاملة لرعاية الحيوانات الأليفة والإبلاغ عن حالات الحيوانات المصابة والضالة">
    <meta name="keywords" content="حيوانات أليفة, رعاية الحيوانات, إنقاذ الحيوانات, طب بيطري, عيادات بيطرية">
    
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent-color: #FFC107;
            --text-dark: #333;
            --text-light: #fff;
            --text-muted: #6c757d;
            --bg-light: #f8f9fa;
            --bg-dark: #333;
            --transition: all 0.3s ease;
            --shadow-sm: 0 5px 15px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 15px 30px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.2);
            --border-radius: 15px;
        }
        
        body {
            overflow-x: hidden;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
        
        /* Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: var(--transition);
        }
        
        .navbar.scrolled {
            padding: 10px 0;
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            font-size: 2rem;
            margin-left: 0.5rem;
            color: var(--accent-color);
        }
        
        .navbar-nav .nav-link {
            color: var(--text-dark);
            font-weight: 600;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .navbar-nav .nav-link:hover, 
        .navbar-nav .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(76, 175, 80, 0.1);
            transform: translateY(-3px);
        }
        
        .navbar-nav .nav-link i {
            margin-left: 5px;
        }
        
        .navbar-toggler {
            border: none;
            padding: 10px;
        }
        
        .navbar-toggler:focus {
            outline: none;
            box-shadow: none;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(76, 175, 80, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        .auth-buttons .btn {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            margin-right: 10px;
        }
        
        .auth-buttons .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .auth-buttons .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .auth-buttons .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .auth-buttons .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('images/login_bg.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 80px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(0,0,0,0) 0%, rgba(0,0,0,0.5) 100%);
            z-index: 0;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .hero-title {
            color: var(--text-light);
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 1s ease-out;
        }
        
        .hero-subtitle {
            color: var(--text-light);
            font-size: 1.8rem;
            margin-bottom: 2.5rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease-out 0.3s;
            animation-fill-mode: both;
        }
        
        .hero-buttons {
            animation: fadeIn 1s ease-out 0.6s;
            animation-fill-mode: both;
        }
        
        .btn-hero {
            padding: 12px 30px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            margin: 0 10px 15px;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-outline-light:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .hero-scroll {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--text-light);
            font-size: 2rem;
            animation: bounce 2s infinite;
            cursor: pointer;
            z-index: 1;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            40% {
                transform: translateY(-20px) translateX(-50%);
            }
            60% {
                transform: translateY(-10px) translateX(-50%);
            }
        }
        
        /* Stats Section */
        .stats-section {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 3rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .stat-text {
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.9;
        }
        
        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background-color: var(--bg-light);
        }
        
        .section-title {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-dark);
            text-align: center;
            position: relative;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
            border-bottom: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 0;
            background: linear-gradient(to bottom, rgba(76, 175, 80, 0.1), transparent);
            transition: var(--transition);
            z-index: 0;
        }
        
        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-md);
            border-bottom: 4px solid var(--primary-color);
        }
        
        .feature-card:hover::before {
            height: 100%;
        }
        
        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            transition: var(--transition);
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
            color: var(--primary-dark);
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
            position: relative;
            z-index: 1;
        }
        
        .feature-text {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }
        
        /* About Section */
        .about-section {
            background-color: #f1f8e9;
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .about-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background-color: rgba(76, 175, 80, 0.1);
            z-index: 0;
        }
        
        .about-section::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background-color: rgba(76, 175, 80, 0.1);
            z-index: 0;
        }
        
        .about-content {
            position: relative;
            z-index: 1;
        }
        
        .about-text {
            font-size: 1.2rem;
            line-height: 1.9;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }
        
        .about-text strong {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .about-img {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            transform: rotate(2deg);
            border: 10px solid white;
        }
        
        .about-img:hover {
            transform: rotate(0deg) scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .about-features {
            margin-top: 2rem;
        }
        
        .about-feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .about-feature-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-left: 1rem;
            background-color: rgba(76, 175, 80, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .about-feature-text {
            font-size: 1.1rem;
        }
        
        /* How It Works Section */
        .how-it-works {
            padding: 6rem 0;
            background-color: white;
        }
        
        .step-card {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 2;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .step-card::after {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            width: 100%;
            border-top: 2px dashed rgba(76, 175, 80, 0.3);
            z-index: 0;
        }
        
        .step-col:first-child .step-card::after {
            width: 50%;
            left: 50%;
        }
        
        .step-col:last-child .step-card::after {
            width: 50%;
            right: 50%;
        }
        
        .step-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
        
        .step-text {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.7;
        }
        
        /* Testimonials Section */
        .testimonials-section {
            padding: 6rem 0;
            background-color: #f9f9f9;
            position: relative;
            overflow: hidden;
        }
        
        .testimonials-section::before {
            content: '\f10d';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 50px;
            left: 50px;
            font-size: 8rem;
            color: rgba(76, 175, 80, 0.05);
            z-index: 0;
        }
        
        .testimonials-section::after {
            content: '\f10e';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            bottom: 50px;
            right: 50px;
            font-size: 8rem;
            color: rgba(76, 175, 80, 0.05);
            z-index: 0;
        }
        
        .swiper {
            width: 100%;
            padding: 3rem 1rem;
        }
        
        .testimonial-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-sm);
            position: relative;
            z-index: 1;
            margin: 1rem 0.5rem;
            transition: var(--transition);
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-md);
        }
        
        .testimonial-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            position: relative;
            padding-top: 1.5rem;
        }
        
        .testimonial-content::before {
            content: '\f10d';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 0;
            right: 0;
            font-size: 1.5rem;
            color: var(--primary-light);
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-left: 1rem;
            border: 3px solid var(--primary-light);
        }
        
        .testimonial-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .testimonial-info h4 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--text-dark);
        }
        
        .testimonial-info p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .swiper-pagination-bullet {
            background-color: var(--primary-color);
        }
        
        /* CTA Section */
        .cta-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .cta-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .cta-text {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }
        
        .btn-cta {
            background-color: white;
            color: var(--primary-color);
            padding: 12px 30px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 50px;
            transition: var(--transition);
            border: 2px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-cta:hover {
            background-color: transparent;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* Footer */
        .footer {
            background-color: #222;
            color: white;
            padding: 5rem 0 0;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        .footer-logo {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .footer-logo i {
            font-size: 2.2rem;
            margin-left: 0.5rem;
            color: var(--accent-color);
        }
        
        .footer-about {
            color: #ddd;
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .footer-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            padding-bottom: 0.8rem;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            font-size: 1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .footer-links a i {
            margin-left: 0.8rem;
            color: var(--primary-color);
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }
        
        .social-links {
            display: flex;
            margin-top: 1.5rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin-left: 1rem;
            color: white;
            font-size: 1.2rem;
            transition: var(--transition);
        }
        
        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-5px);
            color: white;
        }
        
        .copyright {
            background-color: #1a1a1a;
            padding: 1.5rem 0;
            text-align: center;
            color: #aaa;
            margin-top: 4rem;
            font-size: 0.9rem;
        }
        
        .copyright a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive Styles */
          /* Responsive Styles */
          @media (max-width: 992px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .step-card::after {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
            
            .btn-hero {
                padding: 10px 25px;
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .feature-card {
                padding: 2rem;
            }
            
            .about-img {
                margin-top: 2rem;
            }
            
            .cta-title {
                font-size: 2rem;
            }
            
            .cta-text {
                font-size: 1.1rem;
            }
            
            .footer-col {
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
            
            .stat-text {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .section-subtitle {
                font-size: 1rem;
            }
            
            .feature-icon {
                font-size: 3rem;
            }
            
            .feature-title {
                font-size: 1.3rem;
            }
            
            .about-text {
                font-size: 1.1rem;
            }
            
            .cta-title {
                font-size: 1.8rem;
            }
            
            .btn-cta {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-paw"></i> منقذون
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">خدماتنا</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">كيف يعمل</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">آراء العملاء</a>
                    </li>
                </ul>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline-primary">تسجيل الدخول</a>
                    <a href="signup.php" class="btn btn-primary">إنشاء حساب</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-content">
            <h1 class="hero-title">منقذون <i class="fas fa-paw"></i></h1>
            <p class="hero-subtitle">منصة متكاملة لرعاية الحيوانات الأليفة والإبلاغ عن حالات الحيوانات المصابة</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-hero">تسجيل الدخول</a>
                <a href="signup.php" class="btn btn-outline-light btn-hero">إنشاء حساب جديد</a>
            </div>
        </div>
        <a href="#stats" class="hero-scroll">
            <i class="fas fa-chevron-down"></i>
        </a>
    </section>
    
    <!-- Stats Section -->
    <section class="stats-section" id="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                        <span class="stat-number" id="stat1">5000+</span>
                        <span class="stat-text">حيوان تم إنقاذه</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                        <span class="stat-number" id="stat2">200+</span>
                        <span class="stat-text">طبيب بيطري</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                        <span class="stat-number" id="stat3">150+</span>
                        <span class="stat-text">عيادة بيطرية</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                        <span class="stat-number" id="stat4">10000+</span>
                        <span class="stat-text">مستخدم نشط</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">خدماتنا</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                نقدم مجموعة متنوعة من الخدمات المتكاملة لضمان رعاية أفضل للحيوانات الأليفة وإنقاذ الحيوانات المصابة
            </p>
            <div class="row">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <h3 class="feature-title">الإبلاغ عن حالات الطوارئ</h3>
                        <p class="feature-text">يمكنك الإبلاغ عن الحيوانات المصابة أو الضالة ليصل إليها فريق الإنقاذ في أسرع وقت ممكن. نعمل على مدار الساعة لتقديم المساعدة الفورية.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <h3 class="feature-title">استشارات بيطرية</h3>
                        <p class="feature-text">تواصل مع أطباء بيطريين مؤهلين للحصول على استشارات طبية لحيواناتك الأليفة. استفد من خبرة أكثر من 200 طبيب بيطري متخصص.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clinic-medical"></i>
                        </div>
                        <h3 class="feature-title">حجز مواعيد العيادات</h3>
                        <p class="feature-text">احجز موعداً في أقرب عيادة بيطرية بضغطة زر واحصل على الرعاية اللازمة لحيوانك الأليف. أكثر من 150 عيادة متصلة بشبكتنا.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <h3 class="feature-title">التبني</h3>
                        <p class="feature-text">تصفح قوائم الحيوانات المتاحة للتبني وامنح حيواناً محتاجاً بيتاً دافئاً. نضمن أن جميع الحيوانات المعروضة للتبني تم فحصها وتطعيمها.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book-medical"></i>
                        </div>
                        <h3 class="feature-title">نصائح وإرشادات</h3>
                        <p class="feature-text">احصل على نصائح وإرشادات من خبراء في مجال رعاية الحيوانات الأليفة. مقالات وفيديوهات تعليمية لمساعدتك في العناية بحيوانك الأليف.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="700">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="feature-title">مجتمع داعم</h3>
                        <p class="feature-text">انضم إلى مجتمع من محبي الحيوانات لتبادل الخبرات والمساعدة. شارك قصتك مع الآخرين واستفد من تجارب أكثر من 10,000 مستخدم نشط.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">من نحن</h2>
            <div class="row align-items-center">
                <div class="col-lg-6 about-content" data-aos="fade-right">
                    <p class="about-text">
                        <strong>منقذون</strong> هي منصة متكاملة تهدف إلى تقديم الرعاية للحيوانات الأليفة والمساهمة في إنقاذ الحيوانات المصابة والضالة.
                    </p>
                    <p class="about-text">
                        تأسست منصة منقذون عام 2023 على يد مجموعة من المتخصصين والمهتمين برعاية الحيوانات، إيماناً منهم بأهمية توفير بيئة آمنة وصحية للحيوانات الأليفة والضالة على حد سواء.
                    </p>
                    <p class="about-text">
                        نسعى لبناء مجتمع واعٍ بأهمية الرفق بالحيوان من خلال توفير خدمات متنوعة تشمل الإبلاغ عن حالات الطوارئ، والاستشارات البيطرية، وحجز المواعيد في العيادات البيطرية، بالإضافة إلى خدمات التبني.
                    </p>
                    
                    <div class="about-features">
                        <div class="about-feature-item">
                            <div class="about-feature-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="about-feature-text">
                                فريق متخصص من الأطباء البيطريين والمتطوعين
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="about-feature-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="about-feature-text">
                                شبكة واسعة من العيادات البيطرية المعتمدة
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="about-feature-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="about-feature-text">
                                خدمة متاحة على مدار الساعة للحالات الطارئة
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="about-feature-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="about-feature-text">
                                برامج توعوية مستمرة للعناية بالحيوانات الأليفة
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <img src="images/about-img.jpg" alt="صورة عن منقذون" class="img-fluid rounded shadow about-img" onerror="this.src='images/login_bg.jpeg'">
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">كيف تعمل المنصة</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                خطوات بسيطة للحصول على خدماتنا والمساهمة في إنقاذ الحيوانات
            </p>
            <div class="row">
                <div class="col-lg-3 col-md-6 step-col" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="step-title">إنشاء حساب</h3>
                        <p class="step-text">قم بإنشاء حساب جديد في منصة منقذون بخطوات بسيطة وسريعة</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 step-col" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <h3 class="step-title">تحديد الخدمة</h3>
                        <p class="step-text">اختر الخدمة التي تحتاجها من قائمة الخدمات المتاحة</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 step-col" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="step-title">تقديم الطلب</h3>
                        <p class="step-text">قم بتعبئة النموذج الخاص بالخدمة وتقديم طلبك</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 step-col" data-aos="fade-up" data-aos-delay="500">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="step-title">الحصول على الخدمة</h3>
                        <p class="step-text">استمتع بالخدمة المقدمة من فريقنا المتخصص</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">آراء العملاء</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                تعرف على تجارب عملائنا مع منصة منقذون
            </p>
            
            <div class="swiper testimonials-swiper" data-aos="fade-up" data-aos-delay="200">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                ساعدتني منصة منقذون في إنقاذ قطة مصابة وجدتها في الشارع. تواصلت مع الفريق وحضروا بسرعة لإنقاذها. أنا ممتن جداً لهذه الخدمة الرائعة!
                            </div>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <img src="images/testimonial1.jpg" alt="صورة العميل" onerror="this.src='https://via.placeholder.com/60'">
                                </div>
                                <div class="testimonial-info">
                                    <h4>أحمد محمد</h4>
                                    <p>الرياض</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                استخدمت خدمة الاستشارات البيطرية عندما مرض كلبي في وقت متأخر من الليل. حصلت على نصائح قيمة من الطبيب البيطري ساعدتني في التعامل مع الحالة حتى الصباح.
                            </div>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <img src="images/testimonial2.jpg" alt="صورة العميل" onerror="this.src='https://via.placeholder.com/60'">
                                </div>
                                <div class="testimonial-info">
                                    <h4>سارة عبدالله</h4>
                                    <p>جدة</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                تبنيت قطة جميلة من خلال منصة منقذون. كانت العملية سهلة وسريعة، والفريق كان متعاوناً جداً في تقديم كافة المعلومات اللازمة للعناية بها.
                            </div>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <img src="images/testimonial3.jpg" alt="صورة العميل" onerror="this.src='https://via.placeholder.com/60'">
                                </div>
                                <div class="testimonial-info">
                                    <h4>محمد خالد</h4>
                                    <p>الدمام</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                حجزت موعداً لكلبي في عيادة بيطرية قريبة من خلال المنصة. كانت التجربة ممتازة والخدمة سريعة. أنصح الجميع باستخدام منقذون!
                            </div>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <img src="images/testimonial4.jpg" alt="صورة العميل" onerror="this.src='https://via.placeholder.com/60'">
                                </div>
                                <div class="testimonial-info">
                                    <h4>نورة سعد</h4>
                                    <p>المدينة المنورة</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title" data-aos="fade-up">انضم إلينا اليوم</h2>
            <p class="cta-text" data-aos="fade-up" data-aos-delay="100">
                كن جزءاً من مبادرة إنسانية تسعى لجعل العالم مكاناً أفضل للحيوانات الأليفة. سجل الآن واستفد من خدماتنا المتنوعة.
            </p>
            <div data-aos="fade-up" data-aos-delay="200">
                <a href="signup.php" class="btn btn-cta">إنشاء حساب جديد</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-paw"></i> منقذون
                    </div>
                    <p class="footer-about">
                        منصة متكاملة لرعاية الحيوانات الأليفة والإبلاغ عن حالات الحيوانات المصابة والضالة. نسعى لبناء مجتمع واعٍ بأهمية الرفق بالحيوان.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 footer-col">
                    <h3 class="footer-title">روابط سريعة</h3>
                    <ul class="footer-links">
                        <li><a href="login.php"><i class="fas fa-angle-left"></i> تسجيل الدخول</a></li>
                        <li><a href="signup.php"><i class="fas fa-angle-left"></i> إنشاء حساب جديد</a></li>
                        <li><a href="#about"><i class="fas fa-angle-left"></i> من نحن</a></li>
                        <li><a href="#features"><i class="fas fa-angle-left"></i> خدماتنا</a></li>
                        <li><a href="#how-it-works"><i class="fas fa-angle-left"></i> كيف يعمل</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 footer-col">
                    <h3 class="footer-title">خدماتنا</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-angle-left"></i> الإبلاغ عن حالات الطوارئ</a></li>
                        <li><a href="#"><i class="fas fa-angle-left"></i> استشارات بيطرية</a></li>
                        <li><a href="#"><i class="fas fa-angle-left"></i> حجز مواعيد العيادات</a></li>
                        <li><a href="#"><i class="fas fa-angle-left"></i> التبني</a></li>
                        <li><a href="#"><i class="fas fa-angle-left"></i> نصائح وإرشادات</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 footer-col">
                    <h3 class="footer-title">تواصل معنا</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> المملكة العربية السعودية، الرياض</a></li>
                        <li><a href="#"><i class="fas fa-phone-alt"></i> +966 12 345 6789</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> info@munqithon.com</a></li>
                        <li><a href="#"><i class="fas fa-headset"></i> الدعم الفني: support@munqithon.com</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> متاح على مدار الساعة للطوارئ</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <p class="mb-0">جميع الحقوق محفوظة &copy; 2023 <a href="index.php">منقذون</a> | تم التطوير بواسطة فريق منقذون</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <!-- Scripts -->
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/animsition/js/animsition.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
        
        // Initialize Swiper
        var swiper = new Swiper(".testimonials-swiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                992: {
                    slidesPerView: 3,
                },
            },
            autoplay: {
                delay: 5000,
            },
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
        });
        
        // Counter animation
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const duration = 2000; // 2 seconds
            const startValue = 0;
            const increment = targetValue / (duration / 16);
            let currentValue = startValue;
            
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    clearInterval(timer);
                    currentValue = targetValue;
                }
                element.textContent = Math.floor(currentValue) + '+';
            }, 16);
        }
        
        // Intersection Observer for counters
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter('stat1', 5000);
                    animateCounter('stat2', 200);
                    animateCounter('stat3', 150);
                    animateCounter('stat4', 10000);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        // Observe the stats section
        const statsSection = document.getElementById('stats');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>

