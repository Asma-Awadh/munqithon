<?php
include("conn.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>منقذون - لحماية الحيوانات</title>
   
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
       
      /* Hero Section */
      .hero-section {
          position: relative;
          height: 100vh;
          width: 100%;
          overflow: hidden;
          display: flex;
          align-items: center;
          justify-content: center;
      }
       
      .hero-bg {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-image: url('images/background.jpg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
          z-index: -1;
          transform: scale(1.1);
          animation: zoomBg 20s infinite alternate ease-in-out;
      }
       
      .hero-overlay {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: linear-gradient(135deg, rgba(46, 125, 50, 0.7) 0%, rgba(139, 195, 74, 0.4) 100%);
          z-index: -1;
      }
       
      .hero-content {
          text-align: center;
          max-width: 800px;
          padding: 2rem;
          border-radius: 20px;
          background: rgba(255, 255, 255, 0.15);
          backdrop-filter: blur(10px);
          box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
          border: 1px solid rgba(255, 255, 255, 0.2);
          transform: translateY(0);
          animation: float 6s infinite ease-in-out;
          z-index: 1;
      }
       
      .hero-title {
          font-size: 3.5rem;
          font-weight: 800;
          color: var(--white-color);
          margin-bottom: 1.5rem;
          text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
          animation: fadeInDown 1.5s;
      }
       
      .hero-subtitle {
          font-size: 1.8rem;
          color: var(--white-color);
          margin-bottom: 2rem;
          line-height: 1.6;
          font-weight: 500;
          text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
          animation: fadeIn 2s;
      }
       
      .quran-verse {
          font-size: 1.5rem;
          color: var(--white-color);
          line-height: 1.8;
          font-weight: 500;
          padding: 1.5rem;
          border-radius: 15px;
          background: rgba(0, 0, 0, 0.2);
          margin-bottom: 2rem;
          position: relative;
          animation: fadeInUp 2s;
      }
       
      .quran-verse::before {
          content: """;
          font-size: 4rem;
          position: absolute;
          top: -20px;
          right: 10px;
          color: var(--accent-color);
          opacity: 0.5;
      }
       
      .quran-verse::after {
          content: """;
          font-size: 4rem;
          position: absolute;
          bottom: -50px;
          left: 10px;
          color: var(--accent-color);
          opacity: 0.5;
      }
       
      .quran-reference {
          font-size: 1rem;
          color: var(--accent-color);
          font-weight: 700;
          margin-top: 1rem;
          display: block;
      }
       
      /* Buttons */
      .btn-hero {
          display: inline-block;
          padding: 1rem 2rem;
          background: var(--primary-color);
          color: white;
          border-radius: 50px;
          font-size: 1.2rem;
          font-weight: 700;
          text-decoration: none;
          margin: 0.5rem;
          transition: all 0.3s ease;
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
          position: relative;
          overflow: hidden;
          z-index: 1;
          animation: fadeInUp 2.5s;
      }
       
      .btn-hero:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
          color: white;
      }
       
      .btn-hero::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, var(--primary-color), var(--dark-color));
          z-index: -1;
          transition: all 0.5s ease;
          transform: scaleX(0);
          transform-origin: right;
      }
       
      .btn-hero:hover::before {
          transform: scaleX(1);
          transform-origin: left;
      }
       
      .btn-secondary {
          background: transparent;
          border: 2px solid var(--white-color);
      }
       
      .btn-secondary::before {
          background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
      }
       
      /* Floating Animals */
      .floating-animals {
          position: absolute;
          width: 100%;
          height: 100%;
          top: 0;
          left: 0;
          pointer-events: none;
          z-index: 0;
      }
       
      .animal {
          position: absolute;
          width: 100px;
          height: 100px;
          background-size: contain;
          background-repeat: no-repeat;
          background-position: center;
          opacity: 0.7;
      }
       
      .animal-1 {
          top: 15%;
          left: 10%;
          background-image: url('images/cat-silhouette.png');
          animation: float 8s infinite ease-in-out, moveLeftRight 20s infinite alternate ease-in-out;
      }
       
      .animal-2 {
          top: 60%;
          right: 15%;
          background-image: url('images/dog-silhouette.png');
          animation: float 10s infinite ease-in-out, moveRightLeft 25s infinite alternate ease-in-out;
      }
       
      .animal-3 {
          bottom: 20%;
          left: 20%;
          background-image: url('images/bird-silhouette.png');
          animation: float 7s infinite ease-in-out, moveLeftRight 15s infinite alternate ease-in-out;
      }
       
      /* Animations */
      @keyframes float {
          0%, 100% {
              transform: translateY(0);
          }
          50% {
              transform: translateY(-20px);
          }
      }
       
      @keyframes moveLeftRight {
          0% {
              transform: translateX(0) rotate(0deg);
          }
          50% {
              transform: translateX(100px) rotate(5deg);
          }
          100% {
              transform: translateX(0) rotate(0deg);
          }
      }
       
      @keyframes moveRightLeft {
          0% {
              transform: translateX(0) rotate(0deg);
          }
          50% {
              transform: translateX(-100px) rotate(-5deg);
          }
          100% {
              transform: translateX(0) rotate(0deg);
          }
      }
       
      @keyframes zoomBg {
          0% {
              transform: scale(1);
          }
          100% {
              transform: scale(1.2);
          }
      }
       
      /* Responsive */
      @media (max-width: 768px) {
          .hero-title {
              font-size: 2.5rem;
          }
           
          .hero-subtitle, .quran-verse {
              font-size: 1.2rem;
          }
           
          .btn-hero {
              padding: 0.8rem 1.5rem;
              font-size: 1rem;
          }
           
          .animal {
              width: 70px;
              height: 70px;
          }
      }
       
      @media (max-width: 576px) {
          .hero-title {
              font-size: 2rem;
          }
           
          .hero-content {
              padding: 1.5rem;
          }
      }
  </style>
</head>

<body>
  <!-- Incluir el header -->
  <?php include('header.php'); ?>

  <!-- Hero Section -->
  <section class="hero-section">
      <div class="hero-bg"></div>
      <div class="hero-overlay"></div>
       
      <!-- Floating Animals -->
      <div class="floating-animals">
          <div class="animal animal-1"></div>
          <div class="animal animal-2"></div>
          <div class="animal animal-3"></div>
      </div>
       
      <div class="hero-content animate__animated animate__fadeIn">
          <h1 class="hero-title animate__animated animate__fadeInDown">منقذون لحماية الحيوانات</h1>
          <h2 class="hero-subtitle animate__animated animate__fadeIn">معاً لحماية حقوق الحيوانات وتوفير بيئة آمنة لهم</h2>
           
          <div class="quran-verse animate__animated animate__fadeInUp">
              والأنعام خلقها لكم فيها دفء ومنافع ومنها تأكلون ۝ ولكم فيها جمال حين تريحون وحين تسرحون
              <span class="quran-reference">(سورة النحل: 5-6)</span>
          </div>
           
          <div class="hero-buttons">
              <a href="services.php" class="btn-hero animate__animated animate__fadeInUp">خدماتنا</a>
              <a href="about.php" class="btn-hero btn-secondary animate__animated animate__fadeInUp">تعرف علينا</a>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.

  <script>
      new WOW().init();
  </script>
</body>
</html>