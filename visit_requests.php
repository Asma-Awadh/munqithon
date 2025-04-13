<?php
include("conn.php");
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['id']) || $_SESSION['role'] != 2) {
    header("location:login.php");
    exit();
}

$doctor_id = $_SESSION['id'];

// Create visit_requests table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS visit_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    client_id INT NOT NULL,
    animal_type VARCHAR(255) NOT NULL,
    animal_age VARCHAR(50),
    symptoms TEXT NOT NULL,
    address TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (doctor_id),
    INDEX (client_id)
)";
mysqli_query($db, $sql_create_table);

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['request_id']) && isset($_POST['status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    $stmt = mysqli_prepare($db, "UPDATE visit_requests SET status = ?, notes = ? WHERE id = ? AND doctor_id = ?");
    mysqli_stmt_bind_param($stmt, "ssii", $status, $notes, $request_id, $doctor_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "تم تحديث حالة الطلب بنجاح";
    } else {
        $error_message = "حدث خطأ أثناء تحديث حالة الطلب";
    }
}

// Get visit requests for the doctor
$sql = "SELECT vr.*, p.name as client_name, p.phone as client_phone, p.email as client_email, p.logo as client_logo
        FROM visit_requests vr
        JOIN people p ON vr.client_id = p.id
        WHERE vr.doctor_id = ?
        ORDER BY 
            CASE 
                WHEN vr.status = 'pending' THEN 1
                WHEN vr.status = 'approved' THEN 2
                WHEN vr.status = 'completed' THEN 3
                WHEN vr.status = 'rejected' THEN 4
            END,
            vr.preferred_date ASC, 
            vr.preferred_time ASC";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$requests_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>طلبات الزيارة الخارجية</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('images/login_bg.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            padding: 20px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: #4CAF50;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .btn-primary:hover {
            background-color: #388E3C;
            border-color: #388E3C;
        }
        
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .badge-pending {
            background-color: #FFC107;
            color: #333;
        }
        
        .badge-approved {
            background-color: #4CAF50;
            color: white;
        }
        
        .badge-rejected {
            background-color: #F44336;
            color: white;
        }
        
        .badge-completed {
            background-color: #2196F3;
            color: white;
        }
        
        .client-info {
            display: flex;
            align-items: center;
        }
        
        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
            object-fit: cover;
        }
        
        .modal-header {
            background: #4CAF50;
            color: white;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .request-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .request-details p {
            margin-bottom: 10px;
        }
        
        .request-details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-white mb-4">طلبات الزيارة الخارجية</h1>
                
                <?php if(isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clipboard-list"></i> قائمة طلبات الزيارة
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($requests_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>العميل</th>
                                        <th>نوع الحيوان</th>
                                        <th>التاريخ المفضل</th>
                                        <th>الوقت المفضل</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($request = mysqli_fetch_assoc($requests_result)): ?>
                                    <tr>
                                        <td>
                                            <div class="client-info">
                                                <img src="images/<?php echo $request['client_logo'] ? $request['client_logo'] : 'default.png'; ?>" class="client-avatar" alt="صورة العميل">
                                                <div>
                                                    <?php echo $request['client_name']; ?><br>
                                                    <small><?php echo $request['client_phone']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $request['animal_type']; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($request['preferred_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($request['preferred_time'])); ?></td>
                                        <td>
                                            <?php if($request['status'] == 'pending'): ?>
                                                <span class="badge badge-pending">قيد الانتظار</span>
                                            <?php elseif($request['status'] == 'approved'): ?>
                                                <span class="badge badge-approved">تمت الموافقة</span>
                                            <?php elseif($request['status'] == 'rejected'): ?>
                                                <span class="badge badge-rejected">مرفوض</span>
                                            <?php elseif($request['status'] == 'completed'): ?>
                                                <span class="badge badge-completed">مكتمل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#requestModal<?php echo $request['id']; ?>">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal for Request Details -->
                                    <div class="modal fade" id="requestModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel<?php echo $request['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="requestModalLabel<?php echo $request['id']; ?>">
                                                        تفاصيل طلب الزيارة
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="request-details">
                                                        <h4>معلومات العميل</h4>
                                                        <p><strong>الاسم:</strong> <?php echo $request['client_name']; ?></p>
                                                        <p><strong>رقم الهاتف:</strong> <?php echo $request['client_phone']; ?></p>
                                                        <p><strong>البريد الإلكتروني:</strong> <?php echo $request['client_email']; ?></p>
                                                        <p><strong>العنوان:</strong> <?php echo $request['address']; ?></p>
                                                    </div>
                                                    
                                                    <div class="request-details">
                                                        <h4>معلومات الحيوان</h4>
                                                        <p><strong>النوع:</strong> <?php echo $request['animal_type']; ?></p>
                                                        <p><strong>العمر:</strong> <?php echo $request['animal_age']; ?></p>
                                                        <p><strong>الأعراض:</strong> <?php echo $request['symptoms']; ?></p>
                                                    </div>
                                                    
                                                    <div class="request-details">
                                                        <h4>معلومات الزيارة</h4>
                                                        <p><strong>التاريخ المفضل:</strong> <?php echo date('Y-m-d', strtotime($request['preferred_date'])); ?></p>
                                                        <p><strong>الوقت المفضل:</strong> <?php echo date('h:i A', strtotime($request['preferred_time'])); ?></p>
                                                        <p><strong>تاريخ الطلب:</strong> <?php echo date('Y-m-d h:i A', strtotime($request['created_at'])); ?></p>
                                                        <p><strong>الحالة الحالية:</strong> 
                                                            <?php if($request['status'] == 'pending'): ?>
                                                                <span class="badge badge-pending">قيد الانتظار</span>
                                                            <?php elseif($request['status'] == 'approved'): ?>
                                                                <span class="badge badge-approved">تمت الموافقة</span>
                                                            <?php elseif($request['status'] == 'rejected'): ?>
                                                                <span class="badge badge-rejected">مرفوض</span>
                                                            <?php elseif($request['status'] == 'completed'): ?>
                                                                <span class="badge badge-completed">مكتمل</span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php if(!empty($request['notes'])): ?>
                                                        <p><strong>ملاحظات:</strong> <?php echo $request['notes']; ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <form method="post" action="">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                        
                                                        <div class="form-group">
                                                            <label for="status">تحديث الحالة</label>
                                                            <select class="form-control" id="status" name="status" required>
                                                                <option value="pending" <?php echo ($request['status'] == 'pending') ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                                <option value="approved" <?php echo ($request['status'] == 'approved') ? 'selected' : ''; ?>>تمت الموافقة</option>
                                                                <option value="rejected" <?php echo ($request['status'] == 'rejected') ? 'selected' : ''; ?>>مرفوض</option>
                                                                <option value="completed" <?php echo ($request['status'] == 'completed') ? 'selected' : ''; ?>>مكتمل</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label for="notes">ملاحظات</label>
                                                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $request['notes']; ?></textarea>
                                                        </div>
                                                        
                                                        <button type="submit" name="update_status" class="btn btn-primary">تحديث الحالة</button>
                                                        
                                                        <a href="chat.php?user=<?php echo $request['client_id']; ?>" class="btn btn-info">
                                                            <i class="fas fa-comments"></i> التواصل مع العميل
                                                        </a>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> لا توجد طلبات زيارة خارجية حالياً.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> معلومات عن الزيارات الخارجية
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-lightbulb"></i> كيفية التعامل مع طلبات الزيارة الخارجية:</h5>
                            <ul>
                                <li>راجع تفاصيل الطلب بعناية قبل الموافقة أو الرفض.</li>
                                <li>تواصل مع العميل للحصول على مزيد من المعلومات إذا لزم الأمر.</li>
                                <li>في حالة الموافقة، تأكد من تحديد موعد دقيق مع العميل.</li>
                                <li>بعد إتمام الزيارة، قم بتحديث حالة الطلب إلى "مكتمل".</li>
                                <li>أضف ملاحظات مفصلة عن الزيارة والتشخيص والعلاج المقدم.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
