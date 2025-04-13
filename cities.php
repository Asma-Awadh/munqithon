<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 4) {
    header("location:login.php");
    exit();
}

$success_message = "";
$error_message = "";

// إضافة مدينة جديدة
if (isset($_POST['add_city'])) {
    $city_name = trim($_POST['city_name']);
    
    if (empty($city_name)) {
        $error_message = "يرجى إدخال اسم المدينة";
    } else {
        // التحقق من عدم وجود مدينة بنفس الاسم
        $check_query = "SELECT * FROM cities WHERE name = ?";
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $city_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = "المدينة موجودة بالفعل";
        } else {
            // إضافة المدينة الجديدة
            $insert_query = "INSERT INTO cities (name) VALUES (?)";
            $stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($stmt, "s", $city_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "تمت إضافة المدينة بنجاح";
                // تفريغ حقل الإدخال بعد الإضافة
                $city_name = "";
            } else {
                $error_message = "حدث خطأ أثناء إضافة المدينة، يرجى المحاولة مرة أخرى";
            }
        }
    }
}

// تعديل مدينة
if (isset($_POST['edit_city'])) {
    $city_id = $_POST['city_id'];
    $city_name = trim($_POST['city_name']);
    
    if (empty($city_name)) {
        $error_message = "يرجى إدخال اسم المدينة";
    } else {
        // التحقق من عدم وجود مدينة أخرى بنفس الاسم
        $check_query = "SELECT * FROM cities WHERE name = ? AND id != ?";
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "si", $city_name, $city_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = "المدينة موجودة بالفعل";
        } else {
            // تحديث المدينة
            $update_query = "UPDATE cities SET name = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $city_name, $city_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "تم تحديث المدينة بنجاح";
            } else {
                $error_message = "حدث خطأ أثناء تحديث المدينة، يرجى المحاولة مرة أخرى";
            }
        }
    }
}

// حذف مدينة
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $city_id = $_GET['delete_id'];
    
    // التحقق من عدم وجود مستخدمين مرتبطين بالمدينة
    $check_query = "SELECT COUNT(*) as user_count FROM people WHERE city_id = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $city_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
    
    if ($user_data['user_count'] > 0) {
        $error_message = "لا يمكن حذف هذه المدينة لوجود مستخدمين مرتبطين بها";
    } else {
        // حذف المدينة
        $delete_query = "DELETE FROM cities WHERE id = ?";
        $stmt = mysqli_prepare($db, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $city_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم حذف المدينة بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء حذف المدينة، يرجى المحاولة مرة أخرى";
        }
    }
}

// جلب المدن
$cities_query = "SELECT c.*, COUNT(p.id) as user_count 
                FROM cities c 
                LEFT JOIN people p ON c.id = p.city_id 
                GROUP BY c.id 
                ORDER BY c.name ASC";
$cities_result = mysqli_query($db, $cities_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>إدارة المدن - منقذون</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #8BC34A;
            --accent-color: #FFC107;
            --dark-color: #2E7D32;
            --light-color: #F1F8E9;
            --text-color: #333;
            --white-color: #fff;
            --danger-color: #f44336;
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
        
        .cities-section {
            padding-top: 120px;
            padding-bottom: 80px;
            position: relative;
        }
        
        .cities-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.85) 0%, rgba(139, 195, 74, 0.7) 100%);
            z-index: -1;
        }
        
        .cities-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .cities-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .cities-title::after {
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
        
        .add-city-btn {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .add-city-btn:hover {
            background-color: var(--dark-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
            color: white;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 1rem;
        }
        
        .cities-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .cities-table th {
            background-color: var(--dark-color);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
            border: none;
            position: relative;
        }
        
        .cities-table th:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--accent-color);
        }
        
        .cities-table td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .cities-table tr {
            transition: all 0.3s ease;
        }
        
        .cities-table tr:hover {
            background-color: rgba(76, 175, 80, 0.1);
            transform: scale(1.01);
        }
        
        .cities-table tr:hover td {
            color: var(--dark-color);
        }
        
        .btn-action {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
            border: none;
        }
        
        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
        }
        
        .btn-edit {
            background-color: var(--accent-color);
            color: var(--text-color);
        }
        
        .btn-edit:hover {
            background-color: #FFB300;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }
        
        .city-badge {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.2);
            transition: all 0.3s ease;
        }
        
        .city-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .empty-text {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px 20px 0 0;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 700;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border-top: none;
            padding: 1rem 2rem 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .cities-title {
                font-size: 1.8rem;
            }
            
            .cities-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>

    <section class="cities-section">
        <div class="container">
            <div class="cities-card animate__animated animate__fadeIn">
                <h1 class="cities-title">إدارة المدن</h1>
                
                <div class="text-center mb-4">
                    <button type="button" class="add-city-btn" data-bs-toggle="modal" data-bs-target="#addCityModal">
                        <i class="fas fa-plus-circle"></i> إضافة مدينة جديدة
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table id="citiesTable" class="cities-table table">
                        <thead>
                            <tr>
                                <th>اسم المدينة</th>
                                <th>عدد المستخدمين</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if(mysqli_num_rows($cities_result) > 0) {
                                foreach ($cities_result as $row) {
                                    // Get user count for this city
                                    $city_id = $row['id'];
                                    $user_count_sql = "SELECT COUNT(*) as count FROM people WHERE city_id = $city_id";
                                    $user_count_result = mysqli_query($db, $user_count_sql);
                                    $user_count = mysqli_fetch_assoc($user_count_result)['count'];
                            ?>
                                <tr>
                                    <td>
                                        <span class="city-badge"><?= $row['name']; ?></span>
                                    </td>
                                    <td><?= $user_count; ?> مستخدم</td>
                                    <td><?= isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : 'غير محدد'; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-action btn-edit edit-city-btn" 
                                                data-id="<?= $row['id']; ?>" 
                                                data-name="<?= $row['name']; ?>">
                                            <i class="fas fa-edit"></i> تعديل
                                        </button>
                                        <a href="city-delete.php?id=<?= $row['id']; ?>" class="btn btn-action btn-delete" 
                                           onclick="return confirm('هل أنت متأكد من حذف هذه المدينة؟ سيؤثر ذلك على المستخدمين المرتبطين بها.')">
                                            <i class="fas fa-trash-alt"></i> حذف
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="fas fa-city empty-icon"></i>
                                            <p class="empty-text">لا توجد مدن مضافة حالياً</p>
                                            <button type="button" class="add-city-btn" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                                <i class="fas fa-plus-circle"></i> إضافة مدينة جديدة
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Add City Modal -->
    <div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCityModalLabel">إضافة مدينة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_city.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cityName" class="form-label">اسم المدينة</label>
                            <input type="text" class="form-control" id="cityName" name="city_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary" name="add_city_btn">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit City Modal -->
    <div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCityModalLabel">تعديل المدينة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_city.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editCityId" name="city_id">
                        <div class="mb-3">
                            <label for="editCityName" class="form-label">اسم المدينة</label>
                            <input type="text" class="form-control" id="editCityName" name="city_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary" name="edit_city_btn">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#citiesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
                },
                "responsive": true,
                "ordering": true,
                "paging": true,
                "searching": true,
                "info": true,
                "lengthChange": true,
                "pageLength": 10,
                "dom": '<"top"lf>rt<"bottom"ip>',
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "الكل"]],
                "columnDefs": [
                    { "orderable": false, "targets": [3] }
                ]
            });
            
            // Edit city button click
            $('.edit-city-btn').on('click', function() {
                var cityId = $(this).data('id');
                var cityName = $(this).data('name');
                
                $('#editCityId').val(cityId);
                $('#editCityName').val(cityName);
                
                $('#editCityModal').modal('show');
            });
            
            // Confirm delete
            $('.btn-delete').on('click', function(e) {
                if(!confirm('هل أنت متأكد من حذف هذه المدينة؟ سيؤثر ذلك على المستخدمين المرتبطين بها.')) {
                    e.preventDefault();
                }
            });
            
            // Add animation to city badges
            $('.city-badge').each(function(index) {
                $(this).addClass('animate__animated animate__fadeIn');
                $(this).css('animation-delay', (0.1 * index) + 's');
            });
            
            // Tooltip for user count
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>
