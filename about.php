<?php
include("conn.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>من نحن - منقذون لحماية الحيوانات</title>
  
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
      background-image: url('images/background.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      min-height: 100vh;
    }
    
    /* About Section */
    .about-section {
      padding-top: 120px;
      padding-bottom: 80px;
      position: relative;
    }
    
    .about-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(46, 125, 50, 0.85) 0%, rgba(139, 195, 74, 0.7) 100%);
      z-index: -1;
    }
    
    .about-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 3rem;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.2);
      margin-bottom: 2rem;
      transform: translateY(0);
      transition: all 0.3s ease;
    }
    
    .about-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .about-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--white-color);
      margin-bottom: 1.5rem;
      text-align: center;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
      position: relative;
      padding-bottom: 1rem;
    }
    
    .about-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }
    
    .about-content {
      color: var(--white-color);
      font-size: 1.2rem;
      line-height: 1.8;
      text-align: center;
      margin-bottom: 2rem;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    
    .mission-vision {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      margin-top: 3rem;
    }
    
    .mission-card, .vision-card {
      flex: 1;
      min-width: 300px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }
    
    .mission-card:hover, .vision-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
    }
    
    .card-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--white-color);
      margin-bottom: 1rem;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .card-content {
      color: var(--white-color);
      font-size: 1.1rem;
      line-height: 1.7;
      text-align: center;
    }
    
    .team-section {
      margin-top: 4rem;
    }
    
    .team-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--white-color);
      margin-bottom: 2rem;
      text-align: center;
      text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .team-members {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
    }
    
    .team-member {
      width: 250px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
    }
    
    .team-member:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
    }
    
    .member-img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }
    
    .member-info {
      padding: 1.5rem;
      text-align: center;
    }
    
    .member-name {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--white-color);
      margin-bottom: 0.5rem;
    }
    
    .member-role {
      font-size: 1rem;
      color: var(--accent-color);
      margin-bottom: 1rem;
    }
    
    .member-social {
      display: flex;
      justify-content: center;
      gap: 1rem;
    }
    
    .member-social a {
      color: var(--white-color);
      font-size: 1.2rem;
      transition: all 0.3s ease;
    }
    
    .member-social a:hover {
      color: var(--accent-color);
      transform: translateY(-3px);
    }
    
    .stats-section {
      margin-top: 4rem;
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
    }
    
    .stat-card {
      flex: 1;
      min-width: 200px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
    }
    
    .stat-number {
      font-size: 3rem;
      font-weight: 800;
      color: var(--accent-color);
      margin-bottom: 0.5rem;
    }
    
    .stat-title {
      font-size: 1.2rem;
      color: var(--white-color);
    }
    
    .partners-section {
      margin-top: 4rem;
    }
    
    .partners-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--white-color);
      margin-bottom: 2rem;
      text-align: center;
      text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .partners-logos {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      align-items: center;
    }
    
    .partner-logo {
      width: 150px;
      height: 100px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      transition: all 0.3s ease;
    }
    
    .partner-logo:hover {
      transform: scale(1.1);
      background: rgba(255, 255, 255, 0.3);
    }
    
    .partner-logo img {
      max-width: 100%;
      max-height: 100%;
      filter: brightness(0) invert(1);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .about-title {
        font-size: 2rem;
      }
      
      .about-content {
        font-size: 1rem;
      }
      
      .mission-card, .vision-card {
        min-width: 100%;
      }
      
      .card-title {
        font-size: 1.5rem;
      }
      
      .card-content {
        font-size: 1rem;
      }
      
      .about-card {
        padding: 2rem;
      }
    }
    
    @media (max-width: 576px) {
      .about-title {
        font-size: 1.8rem;
      }
      
      .team-member {
        width: 100%;
        max-width: 300px;
      }
    }
  </style>
</head>

<body>
  <!-- Include header -->
  <?php include('header.php'); ?>

  <!-- About Section -->
  <section class="about-section">
    <div class="container">
      <div class="about-card animate__animated animate__fadeIn">
        <h1 class="about-title animate__animated animate__fadeInDown">من نحن</h1>
        <p class="about-content animate__animated animate__fadeIn">
          نحن في منقذون نربط بينك وبين أفضل الأطباء البيطريين والعيادات في المملكة العربية السعودية، 
          نقدم استشارات ذكية لمساعدتك في رعاية حيوانك الأليف.
          خدماتنا موثوقة وسريعة ودائماً في متناول يدك!
        </p>
        
        <div class="mission-vision">
          <div class="mission-card animate__animated animate__fadeInUp">
            <h3 class="card-title"><i class="fas fa-bullseye"></i> مهمتنا</h3>
            <p class="card-content">
              نسعى لتوفير بيئة آمنة للحيوانات من خلال نشر الوعي بأهمية الرفق بالحيوان، وتقديم خدمات بيطرية متميزة، 
              وإنقاذ الحيوانات المعرضة للخطر، وتوفير المأوى المناسب لها.
            </p>
          </div>
          
          <div class="vision-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <h3 class="card-title"><i class="fas fa-eye"></i> رؤيتنا</h3>
            <p class="card-content">
              نتطلع إلى مجتمع يحترم حقوق الحيوانات ويقدر أهميتها في التوازن البيئي، 
              ونسعى لأن نكون المرجع الأول في المملكة العربية السعودية في مجال رعاية الحيوانات وحمايتها.
            </p>
          </div>
        </div>
        
        <div class="stats-section animate__animated animate__fadeIn" style="animation-delay: 0.4s;">
          <div class="stat-card">
            <div class="stat-number" data-count="500">0</div>
            <div class="stat-title">حيوان تم إنقاذه</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number" data-count="50">0</div>
            <div class="stat-title">طبيب بيطري</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number" data-count="20">0</div>
            <div class="stat-title">مدينة نخدمها</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number" data-count="1000">0</div>
            <div class="stat-title">عميل سعيد</div>
          </div>
        </div>
        
        <div class="team-section">
          <h2 class="team-title animate__animated animate__fadeIn" style="animation-delay: 0.6s;">فريقنا</h2>
          <div class="team-members">
            <div class="team-member animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
              <img src="images/team1.jpg" alt="عضو الفريق" class="member-img">
              <div class="member-info">
                <h3 class="member-name">د. محمد العتيبي</h3>
                <p class="member-role">المؤسس والمدير التنفيذي</p>
                <div class="member-social">
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin"></i></a>
                  <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </div>
            
            <div class="team-member animate__animated animate__fadeInUp" style="animation-delay: 0.8s;">
              <img src="images/team2.jpg" alt="عضو الفريق" class="member-img">
              <div class="member-info">
                <h3 class="member-name">د. سارة الغامدي</h3>
                <p class="member-role">رئيس الأطباء البيطريين</p>
                <div class="member-social">
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin"></i></a>
                  <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </div>
            
            <div class="team-member animate__animated animate__fadeInUp" style="animation-delay: 0.9s;">
              <img src="images/team3.jpg" alt="عضو الفريق" class="member-img">
              <div class="member-info">
                <h3 class="member-name">أ. خالد القحطاني</h3>
                <p class="member-role">مدير العمليات</p>
                <div class="member-social">
                  <a href="#"><i class="fab fa-twitter"></i></a>
                  <a href="#"><i class="fab fa-linkedin"></i></a>
                  <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="partners-section">
          <h2 class="partners-title animate__animated animate__fadeIn" style="animation-delay: 1s;">شركاؤنا</h2>
          <div class="partners-logos">
            <div class="partner-logo animate__animated animate__fadeIn" style="animation-delay: 1.1s;">
              <img src="images/partner1.png" alt="شريك">
            </div>
            <div class="partner-logo animate__animated animate__fadeIn" style="animation-delay: 1.2s;">
              <img src="images/partner2.png" alt="شريك">
            </div>
            <div class="partner-logo animate__animated animate__fadeIn" style="animation-delay: 1.3s;">
              <img src="images/partner3.png" alt="شريك">
            </div>
            <div class="partner-logo animate__animated animate__fadeIn" style="animation-delay: 1.4s;">
              <img src="images/partner4.png" alt="شريك">
            </div>
          </div>
        </div>
      </div>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
  
  <script>
    // Initialize WOW.js
    new WOW().init();
    
    // Counter animation
    $(document).ready(function() {
      $('.stat-number').each(function() {
        var $this = $(this);
        var countTo = $this.attr('data-count');
        
        $({ countNum: $this.text() }).animate({
          countNum: countTo
        },
        {
          duration: 2000,
          easing: 'swing',
          step: function() {
            $this.text(Math.floor(this.countNum));
          },
          complete: function() {
            $this.text(this.countNum);
          }
        });
      });
    });
  </script>
</body>
</html>

