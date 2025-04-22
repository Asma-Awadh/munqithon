<?php
include("conn.php");
include("header.php");

//TES -8 ADD COMMENTS
// Helper function to mask email addresses
function maskEmail($email) {
    if (empty($email)) return 'غير متوفر';
    
    $parts = explode('@', $email);
    if (count($parts) != 2) return $email; // Not a valid email format
    
    $name = $parts[0];
    $domain = $parts[1];
    
    // Show first and last character of the name part, mask the rest
    $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
    
    return $maskedName . '@' . $domain;
}

// Helper function to mask phone numbers
function maskPhone($phone) {
    if (empty($phone)) return 'غير متوفر';
    
    // Keep the first two and last two digits visible, mask the rest
    $length = strlen($phone);
    if ($length <= 4) return $phone; // Too short to mask effectively
    
    $visibleStart = 2;
    $visibleEnd = 2;
    
    $maskedPhone = substr($phone, 0, $visibleStart) . 
                   str_repeat('*', $length - $visibleStart - $visibleEnd) . 
                   substr($phone, -$visibleEnd);
    
    return $maskedPhone;
}

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 4) {
    header("location:login.php");
    exit();
}

$success_message = "";
$error_message = "";

// حذف مستخدم
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    
    // لا يمكن حذف المسؤول نفسه
    if ($user_id == $_SESSION['id']) {
        $error_message = "لا يمكنك حذف حسابك الخاص";
    } else {
        // حذف المستخدم
        $delete_query = "DELETE FROM people WHERE id = ?";
        $stmt = mysqli_prepare($db, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "تم حذف المستخدم بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء حذف المستخدم، يرجى المحاولة مرة أخرى";
        }
    }
}

// تغيير حالة المستخدم (تفعيل/تعطيل)
if (isset($_GET['toggle_id']) && is_numeric($_GET['toggle_id'])) {
    $user_id = $_GET['toggle_id'];
    
    // لا يمكن تعطيل المسؤول نفسه
    if ($user_id == $_SESSION['id']) {
        $error_message = "لا يمكنك تعطيل حسابك الخاص";
    } else {
        // جلب حالة المستخدم الحالية
        $status_query = "SELECT status FROM people WHERE id = ?";
        $stmt = mysqli_prepare($db, $status_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        
        // تغيير الحالة
        $new_status = ($user_data['status'] == 1) ? 0 : 1;
        $update_query = "UPDATE people SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $new_status, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $status_text = ($new_status == 1) ? "تفعيل" : "تعطيل";
            $success_message = "تم $status_text المستخدم بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء تغيير حالة المستخدم، يرجى المحاولة مرة أخرى";
        }
    }
}

// البحث عن المستخدمين
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// فلترة حسب نوع المستخدم
$role_filter = "";
if (isset($_GET['role']) && is_numeric($_GET['role'])) {
    $role_filter = $_GET['role'];
}

// استعلام جلب المستخدمين مع البحث والفلترة
$users_query = "SELECT p.*, r.name as role_name, c.name as city_name 
                FROM people p 
                LEFT JOIN roles r ON p.role_id = r.id 
                LEFT JOIN cities c ON p.city_id = c.id 
                WHERE 1=1";

if (!empty($search)) {
    $search_param = "%$search%";
    $users_query .= " AND (p.name LIKE ? OR p.email LIKE ? OR p.phone LIKE ?)";
}

if (!empty($role_filter)) {
    $users_query .= " AND p.role_id = ?";
}

$users_query .= " ORDER BY p.id DESC";

$stmt = mysqli_prepare($db, $users_query);

// ربط المعلمات حسب الفلاتر المستخدمة
if (!empty($search) && !empty($role_filter)) {
    mysqli_stmt_bind_param($stmt, "sssi", $search_param, $search_param, $search_param, $role_filter);
} elseif (!empty($search)) {
    mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
} elseif (!empty($role_filter)) {
    mysqli_stmt_bind_param($stmt, "i", $role_filter);
}

mysqli_stmt_execute($stmt);
$users_result = mysqli_stmt_get_result($stmt);

// جلب الأدوار للفلترة
$roles_query = "SELECT * FROM roles";
$roles_result = mysqli_query($db, $roles_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إدارة المستخدمين</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .users-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .users-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .users-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .users-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .btn-action {
            margin: 2px;
            border-radius: 50px;
            padding: 5px 10px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
        .btn-view {
            background-color: #2196F3;
            color: white;
        }
        .btn-view:hover {
            background-color: #0b7dda;
            color: white;
        }
        .btn-edit {
            background-color: #FFC107;
            color: #212121;
        }
        .btn-edit:hover {
            background-color: #e0a800;
            color: #212121;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background-color: #d32f2f;
            color: white;
        }
        .btn-toggle-active {
            background-color: #4CAF50;
            color: white;
        }
        .btn-toggle-active:hover {
            background-color: #388E3C;
            color: white;
        }
        .btn-toggle-inactive {
            background-color: #9E9E9E;
            color: white;
        }
        .btn-toggle-inactive:hover {
            background-color: #757575;
            color: white;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-active {
            color: #4CAF50;
            font-weight: 600;
        }
        .status-inactive {
            color: #f44336;
            font-weight: 600;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="users-container">
                    <div class="users-header">
                        <h2>إدارة المستخدمين</h2>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="search-box">
                        <form method="get" class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="بحث بالاسم، البريد الإلكتروني، أو رقم الهاتف" value="<?= htmlspecialchars($search) ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> بحث
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="role" class="form-control" onchange="this.form.submit()">
                                    <option value="">جميع الأدوار</option>
                                    <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                        <option value="<?= $role['id'] ?>" <?= ($role_filter == $role['id']) ? 'selected' : '' ?>>
                                            <?= $role['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="users.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-sync-alt"></i> إعادة تعيين
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>الصورة</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الهاتف</th>
                                    <th>المدينة</th>
                                    <th>نوع المستخدم</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($users_result) > 0): ?>
                                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($user['logo'])): ?>
                                                    <img src="images/<?= $user['logo'] ?>" alt="صورة المستخدم" class="user-avatar">
                                                <?php else: ?>
                                                    <img src="images/default_avatar.png" alt="صورة افتراضية" class="user-avatar">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $user['name'] ?></td>
                                            <td><?= maskEmail($user['email']) ?></td>
                                            <td><?= maskPhone($user['phone']) ?></td>
                                            <td><?= $user['city_name'] ?? 'غير محدد' ?></td>
                                            <td><?= $user['role_name'] ?></td>
                                            <td>
                                                <?php if ($user['status'] == 1): ?>
                                                    <span class="status-active">مفعل</span>
                                                <?php else: ?>
                                                    <span class="status-inactive">معطل</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view_user.php?id=<?= $user['id'] ?>" class="btn btn-action btn-view">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-action btn-edit">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['id']): ?>
                                                    <a href="users.php?toggle_id=<?= $user['id'] ?>" class="btn btn-action <?= ($user['status'] == 1) ? 'btn-toggle-inactive' : 'btn-toggle-active' ?>" onclick="return confirm('هل أنت متأكد من تغيير حالة هذا المستخدم؟')">
                                                        <?php if ($user['status'] == 1): ?>
                                                            <i class="fas fa-ban"></i> تعطيل
                                                        <?php else: ?>
                                                            <i class="fas fa-check"></i> تفعيل
                                                        <?php endif; ?>
                                                    </a>
                                                    <a href="users.php?delete_id=<?= $user['id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.')">
                                                        <i class="fas fa-trash"></i> حذف
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">لا يوجد مستخدمين مطابقين لمعايير البحث</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="add_user.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> إضافة مستخدم جديد
                        </a>
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
