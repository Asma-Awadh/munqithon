<?php
include("conn.php");
include("header.php");

// التحقق من تسجيل الدخول ودور المستخدم
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['id'];
$success_message = "";
$error_message = "";

// التحقق من وجود معرف الطبيب
if (!isset($_GET['doctor_id']) || !is_numeric($_GET['doctor_id'])) {
    header("location:doctors_list.php");
    exit();
}

$doctor_id = $_GET['doctor_id'];

// جلب بيانات الطبيب
$doctor_query = "SELECT p.*, c.name as city_name
                FROM people p
                JOIN cities c ON p.city_id = c.id
                WHERE p.id = ? AND p.role_id = 2 AND p.status = 1";
$stmt = mysqli_prepare($db, $doctor_query);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("location:doctors_list.php");
    exit();
}

$doctor_data = mysqli_fetch_assoc($result);

// جلب المواعيد المتاحة للطبيب من جدول doctor_schedule
function getAvailableSlots($db, $doctor_id) {
    $available_slots_query = "SELECT * FROM doctor_schedule
                             WHERE doctor_id = ? AND appointment_date >= CURDATE()
                             AND status = 'available' AND is_booked = 0
                             ORDER BY appointment_date ASC, start_time ASC";
    $stmt = mysqli_prepare($db, $available_slots_query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

$available_slots_result = getAvailableSlots($db, $doctor_id);

// إذا تم إرسال نموذج الحجز
if (isset($_POST['book_appointment'])) {
    $doctor_schedule_id = $_POST['schedule_id'];
    $reason = $_POST['reason'];
    $notes = $_POST['notes'];
   
    // التحقق من أن الموعد لا يزال متاحًا
    $check_query = "SELECT * FROM doctor_schedule
                   WHERE id = ? AND doctor_id = ? AND is_booked = 0 AND status = 'available'";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $doctor_schedule_id, $doctor_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
   
    if (mysqli_num_rows($check_result) > 0) {
        $schedule_data = mysqli_fetch_assoc($check_result);
        
        // إنشاء سجل في جدول clinic_schedules أولاً (لأن bookings يرتبط به)
        $insert_clinic_schedule = "INSERT INTO clinic_schedules (clinic_id, date, start_time, end_time, max_bookings, created_at) 
                                  VALUES (?, ?, ?, ?, 1, NOW())";
        $stmt = mysqli_prepare($db, $insert_clinic_schedule);
        mysqli_stmt_bind_param($stmt, "isss", $doctor_id, $schedule_data['appointment_date'], 
                              $schedule_data['start_time'], $schedule_data['end_time']);
        
        if (mysqli_stmt_execute($stmt)) {
            // الحصول على معرف السجل المضاف حديثًا
            $clinic_schedule_id = mysqli_insert_id($db);
            
            // إنشاء الحجز في جدول bookings
            $insert_query = "INSERT INTO bookings (client_id, schedule_id, notes, status, created_at)
                            VALUES (?, ?, ?, 'pending', NOW())";
            $stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $clinic_schedule_id, $notes);
            
            if (mysqli_stmt_execute($stmt)) {
                // تغيير الموعد الى حجز
                $update_query = "UPDATE doctor_schedule SET is_booked = 1 WHERE id = ?";
                $stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($stmt, "i", $doctor_schedule_id);
                mysqli_stmt_execute($stmt);
                
                $success_message = "تم حجز الموعد بنجاح! سيتم إعلامك عند تأكيد الموعد من قبل الطبيب.";
                
                // إعادة تحميل المواعيد المتاحة
                $available_slots_result = getAvailableSlots($db, $doctor_id);
            } else {
                $error_message = "حدث خطأ أثناء حجز الموعد، يرجى المحاولة مرة أخرى: " . mysqli_error($db);
            }
        } else {
            $error_message = "حدث خطأ أثناء إنشاء الموعد، يرجى المحاولة مرة أخرى: " . mysqli_error($db);
        }
    } else {
        $error_message = "عذراً، هذا الموعد لم يعد متاحاً. يرجى اختيار موعد آخر.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>حجز موعد</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .booking-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .booking-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .booking-header h2 {
            color: #4CAF50;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .booking-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            margin: 15px auto 0;
            border-radius: 3px;
        }
        .doctor-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .doctor-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 20px;
            border: 3px solid #4CAF50;
        }
        .doctor-details h4 {
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .doctor-details p {
            color: #666;
            margin-bottom: 5px;
        }
        .slot-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .slot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #4CAF50;
        }
        .slot-card.selected {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
        }
        .slot-date {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .slot-time {
            color: #4CAF50;
            font-weight: 600;
        }
        .btn-book {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-book:hover {
            background: #2E7D32;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        .no-slots {
            text-align: center;
            padding: 30px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="booking-container">
                    <div class="booking-header">
                        <h2>حجز موعد</h2>
                        <p class="text-muted">اختر موعداً مناسباً للكشف مع الطبيب</p>
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
                   
                    <div class="doctor-info">
                        <img src="images/<?= !empty($doctor_data['logo']) ? $doctor_data['logo'] : 'default-doctor.jpg' ?>" alt="<?= $doctor_data['name'] ?>" class="doctor-image">
                        <div class="doctor-details">
                            <h4><?= $doctor_data['name'] ?></h4>
                            <p><i class="fas fa-map-marker-alt"></i> <?= $doctor_data['city_name'] ?></p>
                            <?php if (!empty($doctor_data['phone'])): ?>
                                <p><i class="fas fa-phone"></i> <?= $doctor_data['phone'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                   
                    <form method="post" id="bookingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3">المواعيد المتاحة</h4>
                               
                                <?php if (mysqli_num_rows($available_slots_result) > 0): ?>
                                    <div class="available-slots">
                                        <?php while ($slot = mysqli_fetch_assoc($available_slots_result)): ?>
                                            <div class="slot-card" onclick="selectSlot(this, <?= $slot['id'] ?>)">
                                                <div class="slot-date">
                                                    <i class="far fa-calendar-alt"></i>
                                                    <?= date('Y/m/d', strtotime($slot['appointment_date'])) ?>
                                                    (<?= date('l', strtotime($slot['appointment_date'])) ?>)
                                                </div>
                                                <div class="slot-time">
                                                    <i class="far fa-clock"></i>
                                                    <?= date('h:i A', strtotime($slot['start_time'])) ?> -
                                                    <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <input type="hidden" name="schedule_id" id="selected_slot" required>
                                <?php else: ?>
                                    <div class="no-slots">
                                        <i class="far fa-calendar-times fa-3x mb-3 text-muted"></i>
                                        <h5>لا توجد مواعيد متاحة حالياً</h5>
                                        <p>يرجى التحقق لاحقاً أو التواصل مع الطبيب مباشرة.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                           
                            <div class="col-md-6">
                                <h4 class="mb-3">تفاصيل الحجز</h4>
                               
                                <div class="form-group">
                                    <label for="reason">سبب الزيارة</label>
                                    <select class="form-control" id="reason" name="reason" required>
                                        <option value="">اختر سبب الزيارة</option>
                                        <option value="كشف">كشف</option>
                                        <option value="متابعة">متابعة</option>
                                        <option value="استشارة">استشارة</option>
                                        <option value="طوارئ">طوارئ</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                </div>
                               
                                <div class="form-group">
                                    <label for="notes">ملاحظات إضافية</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="اكتب أي معلومات إضافية ترغب في إخبار الطبيب بها"></textarea>
                                </div>
                               
                                <?php if (mysqli_num_rows($available_slots_result) > 0): ?>
                                    <button type="submit" name="book_appointment" class="btn btn-book btn-block mt-4">
                                        <i class="fas fa-calendar-check"></i> تأكيد الحجز
                                    </button>
                                <?php else: ?>
                                    <a href="doctors_list.php" class="btn btn-secondary btn-block mt-4">
                                        <i class="fas fa-arrow-right"></i> العودة لقائمة الأطباء
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function selectSlot(element, slotId) {
            // إزالة الفئة المحددة من جميع البطاقات
            document.querySelectorAll('.slot-card').forEach(card => {
                card.classList.remove('selected');
            });
           
            // إضافة الفئة المحددة إلى البطاقة المختارة
            element.classList.add('selected');
           
            // تعيين قيمة الحقل المخفي
            document.getElementById('selected_slot').value = slotId;
        }
       
        // التحقق من النموذج قبل الإرسال
        document.getElementById('bookingForm').addEventListener('submit', function(event) {
            const selectedSlot = document.getElementById('selected_slot').value;
            const reason = document.getElementById('reason').value;
           
            if (!selectedSlot) {
                alert('يرجى اختيار موعد متاح');
                event.preventDefault();
            }
           
            if (!reason) {
                alert('يرجى اختيار سبب الزيارة');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
