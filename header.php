<?php
if (isset($_POST['logout_btn'])) {
  include('logout.php');
}

// Verificar si la sesión está iniciada
if(isset($_SESSION['role'])) {
  if($_SESSION['role'] == 1) {
    // Client role
    $hida_client = '';
    $hida_admin = 'none';
    $hida_doctor = 'none';
    $hida_clinic = 'none';
  } elseif($_SESSION['role'] == 4) {
    // Admin role
    $hida_client = 'none';
    $hida_admin = '';
    $hida_doctor = 'none';
    $hida_clinic = 'none';
  } elseif($_SESSION['role'] == 2) {
    // Doctor role
    $hida_client = 'none';
    $hida_admin = 'none';
    $hida_doctor = '';
    $hida_clinic = 'none';
  } elseif($_SESSION['role'] == 3) {
    // Clinic role
    $hida_client = 'none';
    $hida_admin = 'none';
    $hida_doctor = 'none';
    $hida_clinic = '';
  } else {
    $hida_client = '';
    $hida_admin = 'none';
    $hida_doctor = 'none';
    $hida_clinic = 'none';
  }
} else {
  $hida_client = 'none';
  $hida_admin = 'none';
  $hida_doctor = 'none';
  $hida_clinic = 'none';
}
?>

<head>
  <style>
    .row {
      margin-left: 0px !important;
      margin-right: 0px !important;
    }
    
    .navbar {
      width: 100%;
      background-color: rgba(255, 255, 255, 0.9);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 10px 20px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      transition: all 0.3s ease;
    }
    
    .navbar-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }
    
    .navbar-logo {
      display: flex;
      align-items: center;
      min-width: 150px;
    }
    
    .navbar-logo a {
      font-size: 1.8rem;
      font-weight: 700;
      color: #4CAF50;
      text-decoration: none;
      display: flex;
      align-items: center;
    }
    
    .navbar-logo i {
      font-size: 2rem;
      margin-left: 0.5rem;
      color: #FFC107;
    }
    
    .navbar-menu {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
    }
    
    .navbar a.nav-link {
      float: right;
      padding: 10px 15px;
      color: #333;
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      margin: 0 5px;
      border-radius: 50px;
      transition: all 0.3s ease;
    }
    
    .navbar a.nav-link:hover, .navbar a.nav-link.active {
      background: rgba(76, 175, 80, 0.1);
      color: #4CAF50;
      transform: translateY(-3px);
    }
    
    .navbar a.nav-link i {
      margin-left: 5px;
    }
    
    .logout-btn {
      background: #4CAF50;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 50px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 16px;
      display: inline-block;
      text-decoration: none;
    }
    
    .logout-btn:hover {
      background: #2E7D32;
      transform: translateY(-3px);
    }
    
    /* AI Assistant Link Highlight */
    .navbar a.nav-link.ai-assistant {
      background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(255, 193, 7, 0.1));
      border: 1px solid rgba(76, 175, 80, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .navbar a.nav-link.ai-assistant::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      transition: 0.5s;
    }
    
    .navbar a.nav-link.ai-assistant:hover::before {
      left: 100%;
    }
    
    .navbar a.nav-link.ai-assistant i {
      color: #4CAF50;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% {
        transform: scale(1);
        opacity: 1;
      }
      50% {
        transform: scale(1.1);
        opacity: 0.8;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
    
    @media (max-width: 768px) {
      .navbar-container {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .navbar-menu {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
        margin-top: 15px;
        display: none;
      }
      
      .navbar-menu.active {
        display: flex;
      }
      
      .navbar a.nav-link {
        width: 100%;
        text-align: right;
        padding: 12px 15px;
        margin: 3px 0;
      }
      
      .navbar-toggler {
        display: block;
        position: absolute;
        top: 15px;
        left: 15px;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        color: #4CAF50;
        cursor: pointer;
      }
    }
  </style>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" type="text/css" rel="stylesheet">
</head>

<nav class="navbar">
  <div class="navbar-container">
    <div class="navbar-logo">
      <a href="home.php">
        <i class="fas fa-paw"></i> منقذون
      </a>
    </div>
    
    <button class="navbar-toggler d-md-none" type="button" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="navbar-menu" id="navbarMenu">
      <a href="home.php" class="nav-link">
        <i class="fas fa-home"></i> الرئيسية
      </a>
      <a href="about.php" class="nav-link">
        <i class="fas fa-info-circle"></i> من نحن
      </a>
      <!-- AI Assistant Link - Visible to all users -->
      <a href="ai_assistant.php" class="nav-link ai-assistant">
        <i class="fas fa-robot"></i> المساعد الذكي
      </a>
      <a href="services.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-hands-helping"></i> الخدمات
      </a>
      <!-- Admin specific links -->
      <a href="users.php" class="nav-link" style="display:<?= $hida_admin; ?>">
        <i class="fas fa-users"></i> المستخدمين
      </a>
      <a href="cities.php" class="nav-link" style="display:<?= $hida_admin; ?>">
        <i class="fas fa-city"></i> المدن
      </a>
      <a href="subscriptions.php" class="nav-link" style="display:<?= $hida_admin; ?>">
        <i class="fas fa-credit-card"></i> الاشتراكات
      </a>
      
      <!-- Doctor specific links -->
      <a href="profile.php" class="nav-link" style="display:<?= $hida_doctor; ?>">
        <i class="fas fa-user-md"></i> الملف الشخصي
      </a>
      <a href="appointments.php" class="nav-link" style="display:<?= $hida_doctor; ?>">
        <i class="fas fa-calendar-alt"></i> المواعيد
      </a>
      <a href="chat.php" class="nav-link" style="display:<?= $hida_doctor; ?>">
        <i class="fas fa-comments"></i> الدردشة
      </a>
      <a href="visit_requests.php" class="nav-link" style="display:<?= $hida_doctor; ?>">
        <i class="fas fa-clipboard-list"></i> طلبات الزيارة
      </a>
      <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 2): ?>
        <li class="nav-item">
            <a class="nav-link" href="doctor_appointment_requests.php">
                <i class="fas fa-calendar-check"></i> طلبات الحجز
                <?php
                // Count pending requests
                $count_query = "SELECT COUNT(*) as count FROM bookings b 
                               JOIN clinic_schedules cs ON b.schedule_id = cs.id 
                               WHERE cs.clinic_id = ? AND b.status = 'pending'";
                $stmt = mysqli_prepare($db, $count_query);
                mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
                mysqli_stmt_execute($stmt);
                $count_result = mysqli_stmt_get_result($stmt);
                $count_row = mysqli_fetch_assoc($count_result);
                
                if($count_row['count'] > 0):
                ?>
                <span class="badge badge-pill badge-danger"><?php echo $count_row['count']; ?></span>
                <?php endif; ?>
            </a>
        </li>
      <?php endif; ?>
      
      <!-- Clinic specific links -->
      <a href="clinic_profile.php" class="nav-link" style="display:<?= $hida_clinic; ?>">
        <i class="fas fa-hospital"></i> تحديث الحساب
      </a>
      <a href="clinic_schedule.php" class="nav-link" style="display:<?= $hida_clinic; ?>">
        <i class="fas fa-calendar-plus"></i> إضافة مواعيد
      </a>
      <a href="clinic_bookings.php" class="nav-link" style="display:<?= $hida_clinic; ?>">
        <i class="fas fa-calendar-check"></i> طلبات الحجوزات
      </a>
      <a href="clinic_rescue_reports.php" class="nav-link" style="display:<?= $hida_clinic; ?>">
        <i class="fas fa-ambulance"></i> بلاغات الإنقاذ
      </a>
      
      <!-- Client specific links -->
      <a href="client_profile.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-user"></i> تحديث الحساب
      </a>
      <a href="doctors_list.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-user-md"></i> الأطباء
      </a>
      <a href="clinics_list.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-hospital"></i> العيادات
      </a>
      <a href="client_appointments.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-calendar-alt"></i> مواعيدي
      </a>
      <a href="client_chat.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-comments"></i> المحادثات
      </a>
      <a href="rescue_report.php" class="nav-link" style="display:<?= $hida_client; ?>">
        <i class="fas fa-ambulance"></i> بلاغ إنقاذ
      </a>
      
      <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
      </a>
    </div>
  </div>
</nav>

<script>
  // Toggle menu for mobile
  function toggleMenu() {
    const menu = document.getElementById('navbarMenu');
    menu.classList.toggle('active');
  }
  
  // Add active class to current page
  document.addEventListener('DOMContentLoaded', function() {
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.nav-link');
    
    menuItems.forEach(item => {
      if (item.getAttribute('href') === currentLocation.split('/').pop()) {
        item.classList.add('active');
      } else if (currentLocation.split('/').pop() === '' && item.getAttribute('href') === 'home.php') {
        item.classList.add('active');
      }
    });
  });
</script>
