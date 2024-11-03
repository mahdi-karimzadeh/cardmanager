<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

function getServerTime() {
    $url = "http://worldtimeapi.org/api/timezone/Asia/Tehran";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    if ($response === false) {
        return new DateTime('now', new DateTimeZone('Asia/Tehran'));
    }
    
    $data = json_decode($response, true);
    curl_close($ch);
    
    return isset($data['datetime']) 
        ? new DateTime($data['datetime']) 
        : new DateTime('now', new DateTimeZone('Asia/Tehran'));
}

function getGreeting($dateTime) {
    $hour = $dateTime->format('G');
    
    if ($hour >= 5 && $hour < 12) {
        return 'صبح بخیر';
    } elseif ($hour >= 12 && $hour < 16) {
        return 'ظهر بخیر';
    } elseif ($hour >= 16 && $hour < 20) {
        return 'عصر بخیر';
    } else {
        return 'شب بخیر';
    }
}
$currentTime = getServerTime();
$greeting = getGreeting($currentTime);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد مدیریت کارت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            transition: background-color 0.3s ease;
        }
        
      
        
        #sidebar:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .nav-link {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            transform: translateX(5px);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-logout {
            background-color: #e74c3c;
            color: #ffffff;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        .user-info img {
            transition: transform 0.3s ease;
        }
        
        .user-info img:hover {
            transform: scale(1.1);
        }
        .nav-link.text-white-50:hover {
            background-color: #4a6785;
            color: #ffffff !important;
            transition: all 0.3s ease;
        }
        body, input, button, select, textarea {
            font-family: 'Vazir', sans-serif;
        }
        .btn-logout i {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">

            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar vh-100" style="background-color: #2c3e50;">
                <div class="position-sticky">
                    <div class="user-info text-center py-4">
                        <img src="images/pec-logo-new.png" alt="PEC Logo" class="img-fluid mb-3">
                        <p class="h5 text-white"><?php echo htmlspecialchars($greeting . ' ' . $_SESSION['nameco']); ?></p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">

                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#operationsSubmenu">
                                <i class="fas fa-cogs"></i> عملیات
                                <i class="fas fa-chevron-down float-end"></i>
                            </a>
                            <div class="collapse" id="operationsSubmenu">
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link text-white-50" href="request_card.php"><i class="fas fa-credit-card"></i> درخواست صدور کارت</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="test_db.php.php"><i class="fas fa-copy"></i> صدور رونوشت کارت</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="test_db.php"><i class="fas fa-toggle-on"></i> فعال سازی</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="recharge_card.php"><i class="fas fa-money-bill-wave"></i> شارژ کارت</a></li>
    </ul>
</div>
                        </li>
                        <li class="nav-item">

                            <a class="nav-link text-white" data-bs-toggle="collapse" href="#managementSubmenu">
                                <i class="fas fa-tasks"></i> مدیریت درخواست
                                <i class="fas fa-chevron-down float-end"></i>
                            </a>
                            <div class="collapse" id="managementSubmenu">
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link text-white-50" href="view_requests.php"><i class="fas fa-eye"></i> مشاهده درخواست‌ها</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="review_requests.php"><i class="fas fa-check-square"></i> بررسی درخواست‌ها</a></li>
    </ul>
</div>
                        </li>
                    </ul>
                </div>
    </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">داشبورد مدیریت کارت</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-logout" onclick="logout()">
    <i class="fas fa-sign-out-alt"></i> خروج
</button>
                            </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">تعداد کارت‌های فعال</h5>
                                <p class="card-text h2">1,234</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">مجموع شارژ (ریال)</h5>
                                <p class="card-text h2">5,678,900</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">درخواست‌های در انتظار</h5>
                                <p class="card-text h2">42</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">کارت‌های صادر شده امروز</h5>
                                <p class="card-text h2">15</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                آخرین درخواست‌ها
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">درخواست صدور کارت - شرکت الف</li>
                                    <li class="list-group-item">درخواست شارژ کارت - شرکت ب</li>
                                    <li class="list-group-item">درخواست فعال‌سازی - شرکت ج</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                نمودار فعالیت
                            </div>
                            <div class="card-body">
                                <!-- اینجا می‌توانید یک نمودار اضافه کنید -->
                                <p class="text-muted">نمودار فعالیت اینجا نمایش داده می‌شود.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
function logout() {
    if (confirm('آیا مطمئن هستید که می‌خواهید خارج شوید؟')) {
        window.location.href = 'logout.php';
    }
}
</script>
</body>
</html>


