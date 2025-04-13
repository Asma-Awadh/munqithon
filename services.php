<?php
include("conn.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>خدماتنا - منقذون</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #8BC34A;
            --accent-color: #FFC107;
            --dark-color: #2E7D32;
            --light-color: #F1F8E9;
            --text-color: #333;
            --white-color: #fff;
        }
        
        * {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--text-color);
            overflow-x: hidden;
        }
        
        .services-hero {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .services-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.85) 0%, rgba(139, 195, 74, 0.7) 100%);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        
        .services-section {
            padding: 80px 0;
            background-color: var(--white-color);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }
        
        .service-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(76, 175, 80, 0.2);
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.9) 0%, rgba(139, 195, 74, 0.8) 100%);
            opacity: 0;
            z-index: -1;
            transition: all 0.3s ease;
        }
        
        .service-card:hover::before {
            opacity: 1;
        }
        
        .service-card:hover .service-title,
        .service-card:hover .service-description {
            color: white;
        }
        
        .service-icon {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 4px solid var(--accent-color);
        }
        
        .service-content {
            padding: 25px;
            text-align: center;
        }
        
        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark-color);
            transition: all 0.3s ease;
        }
        
        .service-description {
            font-size: 1rem;
            color: var(--text-color);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .service-btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
        }
        
        .service-btn:hover {
            background-color: transparent;
            color: var(--primary-color);
        }
        
        .service-card:hover .service-btn {
            background-color: white;
            color: var(--primary-color);
            border-color: white;
        }
        
        .service-card:hover .service-btn:hover {
            background-color: transparent;
            color: white;
        }
        
        .cta-section {
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.9) 0%, rgba(139, 195, 74, 0.8) 100%), url('images/cta-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            text-align: center;
            color: white;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .cta-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-btn {
            display: inline-block;
            padding: 15px 40px;
            background-color: var(--accent-color);
            color: var(--text-color);
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid var(--accent-color);
        }
        
        .cta-btn:hover {
            background-color: transparent;
            color: white;
            border-color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .cta-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>

    <!-- Hero Section -->
    <section class="services-hero">
        <div class="hero-content">
            <h1 class="hero-title animate__animated animate__fadeInDown">خدماتنا</h1>
            <p class="hero-subtitle animate__animated animate__fadeInUp">نقدم مجموعة متكاملة من الخدمات لرعاية وحماية الحيوانات</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <h2 class="section-title animate__animated animate__fadeIn">ما نقدمه لكم</h2>
            
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <img src="images/clinics.jpg" alt="العيادات البيطرية" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">العيادات البيطرية</h3>
                            <p class="service-description">نوفر شبكة واسعة من العيادات البيطرية المتخصصة في مختلف المدن لتقديم الرعاية الصحية الشاملة للحيوانات.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <img src="images/doctors.jpg" alt="الأطباء المستقلون" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">الأطباء المستقلون</h3>
                            <p class="service-description">فريق من الأطباء البيطريين المستقلين المتاحين للزيارات المنزلية والاستشارات الطارئة على مدار الساعة.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                        <img src="images/vetbot.jpg" alt="المساعد الذكي" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">المساعد الذكي</h3>
                            <p class="service-description">روبوت ذكي يقدم استشارات أولية ومعلومات عن الحالات الشائعة ويساعدك في تحديد الخطوات التالية للرعاية.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                        <img src="images/report.jpg" alt="بلاغات الطوارئ" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">بلاغات الطوارئ</h3>
                            <p class="service-description">خدمة الإبلاغ عن حالات الحيوانات المصابة أو المهملة، مع فريق استجابة سريع للتدخل في الحالات الطارئة.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                        <img src="images/adoption.jpg" alt="التبني" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">خدمات التبني</h3>
                            <p class="service-description">نساعد في إيجاد منازل دائمة للحيوانات المحتاجة من خلال برنامج التبني المسؤول والمتابعة المستمرة.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
                        <img src="images/training.jpg" alt="التدريب" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">تدريب الحيوانات</h3>
                            <p class="service-description">برامج تدريبية متخصصة للحيوانات الأليفة على يد مدربين محترفين لتحسين سلوكها وتعزيز التواصل معها.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
                        <img src="images/shelter.jpg" alt="الملاجئ" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">ملاجئ الحيوانات</h3>
                            <p class="service-description">توفير ملاجئ آمنة ومجهزة للحيوانات المشردة والمصابة، مع تقديم الرعاية الصحية والغذائية المناسبة.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card animate__animated animate__fadeInUp" style="animation-delay: 0.8s;">
                        <img src="images/awareness.jpg" alt="التوعية" class="service-icon">
                        <div class="service-content">
                            <h3 class="service-title">برامج التوعية</h3>
                            <p class="service-description">حملات توعوية وتثقيفية لنشر ثقافة الرفق بالحيوان والتعامل السليم معها في المدارس والمجتمعات.</p>
                            <a href="#" class="service-btn">المزيد</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title animate__animated animate__fadeIn">كن جزءاً من مبادرتنا</h2>
            <p class="cta-description animate__animated animate__fadeIn">انضم إلينا في مهمتنا لحماية الحيوانات وتوفير بيئة آمنة لها. يمكنك المساهمة بالتطوع أو التبرع أو المشاركة في حملاتنا التوعوية.</p>
            <a href="#" class="cta-btn animate__animated animate__fadeInUp">تواصل معنا الآن</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">منقذون</h5>
                    <p>مبادرة لحماية الحيوانات وتوفير بيئة آمنة لهم، نسعى لنشر الوعي بأهمية الرفق بالحيوان.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">روابط سريعة</h5>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white">الرئيسية</a></li>
                        <li><a href="about.php" class="text-white">من نحن</a></li>
                        <li><a href="services.php" class="text-white">خدماتنا</a></li>
                        <li><a href="#" class="text-white">اتصل بنا</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="mb-3">تواصل معنا</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white mx-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3 bg-light">
            <p>© 2023 منقذون - لحماية الحيوانات. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Scroll animation
        $(window).scroll(function() {
            var scroll = $(window).scrollTop();
            
            // Add shadow to navbar on scroll
            if (scroll >= 50) {
                $('.navbar').addClass('navbar-scrolled');
            } else {
                $('.navbar').removeClass('navbar-scrolled');
            }
            
            // Animate elements when they come into view
            $('.animate__animated').each(function() {
                var position = $(this).offset().top;
                var windowHeight = $(window).height();
                
                if (position < scroll + windowHeight - 100) {
                    var animationClass = $(this).data('animation') || 'animate__fadeIn';
                    $(this).addClass(animationClass);
                }
            });
        });
        
        // Service card hover effect
        $('.service-card').hover(function() {
            $(this).find('.service-icon').css('transform', 'scale(1.05)');
        }, function() {
            $(this).find('.service-icon').css('transform', 'scale(1)');
        });
    </script>
</body>
</html>
