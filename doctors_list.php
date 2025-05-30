<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];

// البحث والفلترة
$search = "";
$city_filter = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['city']) && is_numeric($_GET['city'])) {
    $city_filter = $_GET['city'];
}

// استعلام جلب الأطباء مع البحث والفلترة
$doctors_query = "SELECT p.*, c.name as city_name 
                 FROM people p 
                 JOIN cities c ON p.city_id = c.id 
                 WHERE p.role_id = 2 AND p.status = 1";

if (!empty($search)) {
    $search_param = "%$search%";
    $doctors_query .= " AND (p.name LIKE ? OR p.bio LIKE ?)";
}

if (!empty($city_filter)) {
    $doctors_query .= " AND p.city_id = ?";
}

$doctors_query .= " ORDER BY p.name ASC";

$stmt = mysqli_prepare($db, $doctors_query);

// Bind parameters for search and filter
if (!empty($search) && !empty($city_filter)) {
    mysqli_stmt_bind_param($stmt, "ssi", $search_param, $search_param, $city_filter);
} elseif (!empty($search)) {
    mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
} elseif (!empty($city_filter)) {
    mysqli_stmt_bind_param($stmt, "i", $city_filter);
}

mysqli_stmt_execute($stmt);
$doctors_result = mysqli_stmt_get_result($stmt);

// جلب المدن للفلترة
$cities_query = "SELECT * FROM cities ORDER BY name ASC";
$cities_result = mysqli_query($db, $cities_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>قائمة الأطباء</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .doctors-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .doctors-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .doctors-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .doctors-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .doctor-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            background-color: #fff;
        }
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .doctor-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .doctor-info {
            padding: 20px;
        }
        .doctor-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .doctor-location {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .doctor-bio {
            color: #555;
            margin-bottom: 20px;
            font-size: 0.95rem;
            height: 80px;
            overflow: hidden;
        }
        .btn-contact {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .btn-contact:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        .btn-appointment {
            background: #FFC107;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .btn-appointment:hover {
            background: #FFA000;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        .search-box {
            margin-bottom: 30px;
        }
        .no-results {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="doctors-container">
                    <div class="doctors-header">
                        <h2>قائمة الأطباء</h2>
                        <p class="text-muted">يمكنك التواصل مع الأطباء وحجز المواعيد</p>
                    </div>
                    
                    <!-- البحث والفلترة -->
                    <div class="search-box">
                        <form method="get" class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="ابحث عن طبيب..." value="<?= htmlspecialchars($search) ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> بحث
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="city" class="form-control" onchange="this.form.submit()">
                                    <option value="">جميع المدن</option>
                                    <?php mysqli_data_seek($cities_result, 0); ?>
                                    <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                        <option value="<?= $city['id'] ?>" <?= ($city_filter == $city['id']) ? 'selected' : '' ?>>
                                            <?= $city['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="doctors_list.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-sync-alt"></i> إعادة تعيين
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- عرض الأطباء -->
                    <div class="row">
                        <?php if (mysqli_num_rows($doctors_result) > 0): ?>
                            <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                <div class="col-md-4">
                                    <div class="doctor-card">
                                        <img src="images/<?= !empty($doctor['logo']) ? $doctor['logo'] : 'default-doctor.jpg' ?>" alt="<?= $doctor['name'] ?>" class="doctor-image">
                                        <div class="doctor-info">
                                            <h3 class="doctor-name"><?= $doctor['name'] ?></h3>
                                            <p class="doctor-location">
                                                <i class="fas fa-map-marker-alt"></i> <?= $doctor['city_name'] ?>
                                            </p>
                                            <div class="doctor-bio">
                                                <?= !empty($doctor['bio']) ? $doctor['bio'] : 'لا توجد معلومات متاحة عن هذا الطبيب.' ?>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <a href="doctor_profile.php?id=<?= $doctor['id'] ?>" class="btn btn-info">
                                                    <i class="fas fa-info-circle"></i> التفاصيل
                                                </a>
                                                <a href="chat.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-contact">
                                                    <i class="fas fa-comments"></i> مراسلة
                                                </a>
                                                <a href="book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-appointment">
                                                    <i class="fas fa-calendar-plus"></i> حجز
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="no-results">
                                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                                    <h4>لا توجد نتائج</h4>
                                    <p>لم يتم العثور على أطباء مطابقين لمعايير البحث الخاصة بك.</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
