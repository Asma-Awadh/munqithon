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

// إضافة اشتراك جديد
if (isset($_POST['add_subscription'])) {
    $user_id = $_POST['user_id'];
    $subscription_type = $_POST['subscription_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];
    $status = $_POST['status'];
    
    // التحقق من صحة البيانات
    if (empty($user_id) || empty($subscription_type) || empty($start_date) || empty($end_date) || empty($amount)) {
        $error_message = "يرجى ملء جميع الحقول المطلوبة";
    } else {
        // إضافة الاشتراك الجديد
        $insert_query = "INSERT INTO subscriptions (user_id, subscription_type, start_date, end_date, amount, payment_method, notes, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($stmt, "isssdssi", $user_id, $subscription_type, $start_date, $end_date, $amount, $payment_method, $notes, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            // تحديث حالة المستخدم إلى مفعل
            $update_user = "UPDATE people SET status = 1 WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_user);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            
            $success_message = "تمت إضافة الاشتراك بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء إضافة الاشتراك، يرجى المحاولة مرة أخرى";
        }
    }
}

// تعديل اشتراك
if (isset($_POST['edit_subscription'])) {
    $subscription_id = $_POST['subscription_id'];
    $subscription_type = $_POST['subscription_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];
    $status = $_POST['status'];
    
    // التحقق من صحة البيانات
    if (empty($subscription_type) || empty($start_date) || empty($end_date) || empty($amount)) {
        $error_message = "يرجى ملء جميع الحقول المطلوبة";
    } else {
        // تحديث الاشتراك
        $update_query = "UPDATE subscriptions SET subscription_type = ?, start_date = ?, end_date = ?, 
                         amount = ?, payment_method = ?, notes = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "sssdssis", $subscription_type, $start_date, $end_date, $amount, $payment_method, $notes, $status, $subscription_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // إذا كان الاشتراك غير مفعل، قم بتعطيل المستخدم
            if ($status == 0) {
                $get_user_id = "SELECT user_id FROM subscriptions WHERE id = ?";
                $stmt = mysqli_prepare($db, $get_user_id);
                mysqli_stmt_bind_param($stmt, "i", $subscription_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user_data = mysqli_fetch_assoc($result);
                
                // التحقق من عدم وجود اشتراكات أخرى مفعلة للمستخدم
                $check_active = "SELECT COUNT(*) as active_count FROM subscriptions 
                                WHERE user_id = ? AND status = 1 AND id != ?";
                $stmt = mysqli_prepare($db, $check_active);
                mysqli_stmt_bind_param($stmt, "ii", $user_data['user_id'], $subscription_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $active_data = mysqli_fetch_assoc($result);
                
                if ($active_data['active_count'] == 0) {
                    // تعطيل المستخدم إذا لم يكن لديه اشتراكات مفعلة أخرى
                    $update_user = "UPDATE people SET status = 0 WHERE id = ?";
                    $stmt = mysqli_prepare($db, $update_user);
                    mysqli_stmt_bind_param($stmt, "i", $user_data['user_id']);
                    mysqli_stmt_execute($stmt);
                }
            } else {
                // تفعيل المستخدم إذا كان الاشتراك مفعل
                $get_user_id = "SELECT user_id FROM subscriptions WHERE id = ?";
                $stmt = mysqli_prepare($db, $get_user_id);
                mysqli_stmt_bind_param($stmt, "i", $subscription_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user_data = mysqli_fetch_assoc($result);
                
                $update_user = "UPDATE people SET status = 1 WHERE id = ?";
                $stmt = mysqli_prepare($db, $update_user);
                mysqli_stmt_bind_param($stmt, "i", $user_data['user_id']);
                mysqli_stmt_execute($stmt);
            }
            
            $success_message = "تم تحديث الاشتراك بنجاح";
        } else {
            $error_message = "حدث خطأ أثناء تحديث الاشتراك، يرجى المحاولة مرة أخرى";
        }
    }
}

// حذف اشتراك
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $subscription_id = $_GET['delete_id'];
    
    // حذف الاشتراك
    $delete_query = "DELETE FROM subscriptions WHERE id = ?";
    $stmt = mysqli_prepare($db, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $subscription_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "تم حذف الاشتراك بنجاح";
    } else {
        $error_message = "حدث خطأ أثناء حذف الاشتراك، يرجى المحاولة مرة أخرى";
    }
}

// البحث عن الاشتراكات
$search = "";
$role_filter = "";
$status_filter = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['role']) && is_numeric($_GET['role'])) {
    $role_filter = $_GET['role'];
}

if (isset($_GET['status']) && ($_GET['status'] == '0' || $_GET['status'] == '1')) {
    $status_filter = $_GET['status'];
}

// استعلام جلب الاشتراكات مع البحث والفلترة
$subscriptions_query = "SELECT s.*, p.name as user_name, p.email, p.phone, r.name as role_name 
                       FROM subscriptions s 
                       JOIN people p ON s.user_id = p.id 
                       JOIN roles r ON p.role_id = r.id 
                       WHERE 1=1";

if (!empty($search)) {
    $search_param = "%$search%";
    $subscriptions_query .= " AND (p.name LIKE ? OR p.email LIKE ? OR p.phone LIKE ?)";
}

if (!empty($role_filter)) {
    $subscriptions_query .= " AND p.role_id = ?";
}

if ($status_filter !== "") {
    $subscriptions_query .= " AND s.status = ?";
}

$subscriptions_query .= " ORDER BY s.start_date DESC";

$stmt = mysqli_prepare($db, $subscriptions_query);

// ربط المعلمات حسب الفلاتر المستخدمة
$param_types = "";
$param_values = array();

if (!empty($search)) {
    $param_types .= "sss";
    $param_values[] = $search_param;
    $param_values[] = $search_param;
    $param_values[] = $search_param;
}

if (!empty($role_filter)) {
    $param_types .= "i";
    $param_values[] = $role_filter;
}

if ($status_filter !== "") {
    $param_types .= "i";
    $param_values[] = $status_filter;
}

if (!empty($param_types)) {
    $params = array($stmt, $param_types);
    foreach ($param_values as $value) {
        $params[] = $value;
    }
    call_user_func_array('mysqli_stmt_bind_param', $params);
}

mysqli_stmt_execute($stmt);
$subscriptions_result = mysqli_stmt_get_result($stmt);

// جلب الأطباء والعيادات للاختيار
$users_query = "SELECT p.id, p.name, p.email, p.phone, r.name as role_name 
               FROM people p 
               JOIN roles r ON p.role_id = r.id 
               WHERE p.role_id IN (2, 3) 
               ORDER BY p.name ASC";
$users_result = mysqli_query($db, $users_query);

// جلب الأدوار للفلترة
$roles_query = "SELECT * FROM roles WHERE id IN (2, 3)";
$roles_result = mysqli_query($db, $roles_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>إدارة الاشتراكات</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .subscriptions-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .subscriptions-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .subscriptions-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .subscriptions-header::after {
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
        .btn-add {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
        .subscription-details {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="subscriptions-container">
                    <div class="subscriptions-header">
                        <h2>إدارة الاشتراكات</h2>
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
                    
                    <!-- نموذج إضافة اشتراك جديد -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">إضافة اشتراك جديد</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="user_id">المستخدم</label>
                                            <select class="form-control" id="user_id" name="user_id" required>
                                                <option value="">اختر المستخدم</option>
                                                <?php mysqli_data_seek($users_result, 0); ?>
                                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                                    <option value="<?= $user['id'] ?>">
                                                        <?= $user['name'] ?> (<?= $user['role_name'] ?>) - <?= $user['phone'] ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="subscription_type">نوع الاشتراك</label>
                                            <select class="form-control" id="subscription_type" name="subscription_type" required>
                                                <option value="monthly">شهري</option>
                                                <option value="quarterly">ربع سنوي</option>
                                                <option value="semi-annual">نصف سنوي</option>
                                                <option value="annual">سنوي</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="amount">المبلغ</label>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">تاريخ البداية</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="end_date">تاريخ الانتهاء</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="payment_method">طريقة الدفع</label>
                                            <select class="form-control" id="payment_method" name="payment_method">
                                                <option value="cash">نقدي</option>
                                                <option value="bank_transfer">تحويل بنكي</option>
                                                <option value="credit_card">بطاقة ائتمان</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="notes">ملاحظات</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="status">الحالة</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="1">مفعل</option>
                                                <option value="0">غير مفعل</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="add_subscription" class="btn btn-add">
                                        <i class="fas fa-plus ml-2"></i> إضافة اشتراك
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- البحث والفلترة -->
                    <div class="search-box">
                        <form method="get" class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="بحث بالاسم، البريد الإلكتروني، أو رقم الهاتف" value="<?= htmlspecialchars($search) ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> بحث
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-control" onchange="this.form.submit()">
                                    <option value="">جميع الأنواع</option>
                                    <?php mysqli_data_seek($roles_result, 0); ?>
                                    <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                        <option value="<?= $role['id'] ?>" <?= ($role_filter == $role['id']) ? 'selected' : '' ?>>
                                            <?= $role['name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" <?= ($status_filter === '1') ? 'selected' : '' ?>>مفعل</option>
                                    <option value="0" <?= ($status_filter === '0') ? 'selected' : '' ?>>غير مفعل</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="subscriptions.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-sync-alt"></i> إعادة تعيين
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- جدول الاشتراكات -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>المستخدم</th>
                                    <th>نوع المستخدم</th>
                                    <th>نوع الاشتراك</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($subscriptions_result) > 0): ?>
                                    <?php while ($subscription = mysqli_fetch_assoc($subscriptions_result)): ?>
                                        <tr>
                                            <td><?= $subscription['user_name'] ?><br><small><?= $subscription['phone'] ?></small></td>
                                            <td><?= $subscription['role_name'] ?></td>
                                            <td>
                                                <?php
                                                switch($subscription['subscription_type']) {
                                                    case 'monthly':
                                                        echo 'شهري';
                                                        break;
                                                    case 'quarterly':
                                                        echo 'ربع سنوي';
                                                        break;
                                                    case 'semi-annual':
                                                        echo 'نصف سنوي';
                                                        break;
                                                    case 'annual':
                                                        echo 'سنوي';
                                                        break;
                                                    default:
                                                        echo $subscription['subscription_type'];
                                                }
                                                ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($subscription['start_date'])) ?></td>
                                            <td><?= date('Y-m-d', strtotime($subscription['end_date'])) ?></td>
                                            <td><?= number_format($subscription['amount'], 2) ?> ريال</td>
                                            <td>
                                                <?php if ($subscription['status'] == 1): ?>
                                                    <span class="status-active">مفعل</span>
                                                <?php else: ?>
                                                    <span class="status-inactive">غير مفعل</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-action btn-edit" data-toggle="modal" data-target="#editSubscriptionModal<?= $subscription['id'] ?>">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </button>
                                                <a href="subscriptions.php?delete_id=<?= $subscription['id'] ?>" class="btn btn-action btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا الاشتراك؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </a>
                                                
                                                <!-- Modal for editing subscription -->
                                                <div class="modal fade" id="editSubscriptionModal<?= $subscription['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editSubscriptionModalLabel<?= $subscription['id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editSubscriptionModalLabel<?= $subscription['id'] ?>">تعديل الاشتراك</h5>
                                                                <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form method="post">
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>المستخدم</label>
                                                                                <input type="text" class="form-control" value="<?= $subscription['user_name'] ?> (<?= $subscription['role_name'] ?>)" readonly>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="edit_subscription_type<?= $subscription['id'] ?>">نوع الاشتراك</label>
                                                                                <select class="form-control" id="edit_subscription_type<?= $subscription['id'] ?>" name="subscription_type" required>
                                                                                    <option value="monthly" <?= ($subscription['subscription_type'] == 'monthly') ? 'selected' : '' ?>>شهري</option>
                                                                                    <option value="quarterly" <?= ($subscription['subscription_type'] == 'quarterly') ? 'selected' : '' ?>>ربع سنوي</option>
                                                                                    <option value="semi-annual" <?= ($subscription['subscription_type'] == 'semi-annual') ? 'selected' : '' ?>>نصف سنوي</option>
                                                                                    <option value="annual" <?= ($subscription['subscription_type'] == 'annual') ? 'selected' : '' ?>>سنوي</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="edit_amount<?= $subscription['id'] ?>">المبلغ</label>
                                                                                <input type="number" class="form-control" id="edit_amount<?= $subscription['id'] ?>" name="amount" step="0.01" value="<?= $subscription['amount'] ?>" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="edit_start_date<?= $subscription['id'] ?>">تاريخ البداية</label>
                                                                                <input type="date" class="form-control" id="edit_start_date<?= $subscription['id'] ?>" name="start_date" value="<?= date('Y-m-d', strtotime($subscription['start_date'])) ?>" required>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="edit_end_date<?= $subscription['id'] ?>">
                                                                                <label for="edit_end_date<?= $subscription['id'] ?>">تاريخ الانتهاء</label>
                                                                                <input type="date" class="form-control" id="edit_end_date<?= $subscription['id'] ?>" name="end_date" value="<?= date('Y-m-d', strtotime($subscription['end_date'])) ?>" required>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="edit_payment_method<?= $subscription['id'] ?>">طريقة الدفع</label>
                                                                                <select class="form-control" id="edit_payment_method<?= $subscription['id'] ?>" name="payment_method">
                                                                                    <option value="cash" <?= ($subscription['payment_method'] == 'cash') ? 'selected' : '' ?>>نقدي</option>
                                                                                    <option value="bank_transfer" <?= ($subscription['payment_method'] == 'bank_transfer') ? 'selected' : '' ?>>تحويل بنكي</option>
                                                                                    <option value="credit_card" <?= ($subscription['payment_method'] == 'credit_card') ? 'selected' : '' ?>>بطاقة ائتمان</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="edit_notes<?= $subscription['id'] ?>">ملاحظات</label>
                                                                                <textarea class="form-control" id="edit_notes<?= $subscription['id'] ?>" name="notes" rows="3"><?= $subscription['notes'] ?></textarea>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label for="edit_status<?= $subscription['id'] ?>">الحالة</label>
                                                                                <select class="form-control" id="edit_status<?= $subscription['id'] ?>" name="status" required>
                                                                                    <option value="1" <?= ($subscription['status'] == 1) ? 'selected' : '' ?>>مفعل</option>
                                                                                    <option value="0" <?= ($subscription['status'] == 0) ? 'selected' : '' ?>>غير مفعل</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="subscription_id" value="<?= $subscription['id'] ?>">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                                                                    <button type="submit" name="edit_subscription" class="btn btn-primary">حفظ التغييرات</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">لا توجد اشتراكات مطابقة لمعايير البحث</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // تحديث تاريخ الانتهاء تلقائيًا عند تغيير نوع الاشتراك أو تاريخ البداية
        document.addEventListener('DOMContentLoaded', function() {
            const subscriptionTypeSelect = document.getElementById('subscription_type');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            function updateEndDate() {
                if (startDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    let endDate = new Date(startDate);
                    
                    switch(subscriptionTypeSelect.value) {
                        case 'monthly':
                            endDate.setMonth(endDate.getMonth() + 1);
                            break;
                        case 'quarterly':
                            endDate.setMonth(endDate.getMonth() + 3);
                            break;
                        case 'semi-annual':
                            endDate.setMonth(endDate.getMonth() + 6);
                            break;
                        case 'annual':
                            endDate.setFullYear(endDate.getFullYear() + 1);
                            break;
                    }
                    
                    // تنسيق التاريخ بصيغة YYYY-MM-DD
                    const year = endDate.getFullYear();
                    const month = String(endDate.getMonth() + 1).padStart(2, '0');
                    const day = String(endDate.getDate()).padStart(2, '0');
                    endDateInput.value = `${year}-${month}-${day}`;
                }
            }
            
            if (subscriptionTypeSelect && startDateInput && endDateInput) {
                subscriptionTypeSelect.addEventListener('change', updateEndDate);
                startDateInput.addEventListener('change', updateEndDate);
            }
        });
    </script>
</body>
</html>
