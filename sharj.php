<?php
session_start();
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function validateCardNumber($cardNumber) {
    $sum = 0;
    $numDigits = strlen($cardNumber);
    $parity = $numDigits % 2;
    for ($i = $numDigits - 1; $i >= 0; $i--) {
        $digit = intval($cardNumber[$i]);
        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    return ($sum % 10 == 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $_SESSION['current_step'] = 1;
    $_SESSION['excel_file_data'] = base64_encode(file_get_contents($_FILES['excel_file']['tmp_name']));
    $_SESSION['org_letter_data'] = base64_encode(file_get_contents($_FILES['org_letter']['tmp_name']));
    $_SESSION['org_letter_ext'] = pathinfo($_FILES['org_letter']['name'], PATHINFO_EXTENSION);

    if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
        $_SESSION['payment_receipt_data'] = base64_encode(file_get_contents($_FILES['payment_receipt']['tmp_name']));
        $_SESSION['payment_receipt_ext'] = pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION);
    }

    $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    array_shift($rows);
    $_SESSION['excel_data'] = $rows;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه مدیریت شارژ کارت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
        }

        body {
            font-family: 'Vazir', sans-serif;
            background: var(--light);
            color: var(--dark);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.1);
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
            padding: 0 2rem;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50px;
            right: 50px;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 50px;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
            z-index: 1;
        }

        .upload-container {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .file-upload-box {
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .file-upload-box:hover {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.05);
            transform: translateY(-5px);
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border-right: 4px solid;
        }

        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }

        .data-table thead {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .action-btn {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
            padding: 0 2rem;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50px;
            right: 50px;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 50px;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
            z-index: 1;
        }

        .upload-container {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .file-upload-box {
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .file-upload-box:hover {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.05);
            transform: translateY(-5px);
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border-right: 4px solid;
        }

        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }

        .data-table thead {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .action-btn {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="page-header">
            <h1>سامانه مدیریت شارژ کارت</h1>
            <p>آپلود و مدیریت اطلاعات شارژ کارت‌ها</p>
        </div>

        <div class="progress-steps">
            <div class="progress-line"></div>
            <div class="step active" data-step="1">
                <div class="step-number">۱</div>
                <div class="step-title">آپلود فایل‌ها</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">۲</div>
                <div class="step-title">بررسی اطلاعات</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">۳</div>
                <div class="step-title">تایید نهایی</div>
            </div>
        </div>

        <div class="upload-container">
            <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="row mb-4">
                    <div class="col-md-6 mx-auto">
                        <div class="form-group text-center">
                            <label class="mb-2">نوع شارژ:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="charge_type" id="cash" value="cash" checked>
                                <label class="btn btn-outline-primary w-50" for="cash">
                                    <i class="bi bi-cash me-2"></i>نقدی
                                </label>
                                <input type="radio" class="btn-check" name="charge_type" id="credit" value="credit">
                                <label class="btn btn-outline-primary w-50" for="credit">
                                    <i class="bi bi-credit-card me-2"></i>اعتباری
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="file-upload-box" id="excelUpload">
                            <i class="bi bi-file-earmark-excel display-4 text-success mb-3"></i>
                            <h5>فایل اکسل را اینجا رها کنید</h5>
                            <p class="text-muted">یا کلیک کنید برای انتخاب فایل</p>
                            <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls" required hidden>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="file-upload-box" id="letterUpload">
                            <i class="bi bi-file-text display-4 text-primary mb-3"></i>
                            <h5>نامه سازمان</h5>
                            <p class="text-muted">فایل PDF یا تصویر</p>
                            <input type="file" class="form-control" name="org_letter" accept="image/*,.pdf" required hidden>
                        </div>
                    </div>

                    <div class="col-md-6" id="receiptUploadContainer">
                        <div class="file-upload-box" id="receiptUpload">
                            <i class="bi bi-receipt display-4 text-warning mb-3"></i>
                            <h5>فیش واریزی</h5>
                            <p class="text-muted">فایل PDF یا تصویر</p>
                            <input type="file" class="form-control" name="payment_receipt" accept="image/*,.pdf" hidden>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="action-btn btn btn-primary px-5">
                        <i class="bi bi-upload me-2"></i>آپلود و پردازش
                    </button>
                </div>
            </form>
        </div>

        <!-- بخش نمایش داده‌ها -->
        <?php if (isset($_SESSION['excel_data'])): ?>
            <!-- کد PHP مربوط به نمایش جدول و آمار -->
        <?php endif; ?>
    </div>
    <script>
    // تابع بروزرسانی مراحل پیشرفت
    function updateProgressSteps(currentStep) {
        const steps = document.querySelectorAll('.step');
        const progressLine = document.querySelector('.progress-line');
        const totalSteps = steps.length;
        
        steps.forEach((step, index) => {
            if (index < currentStep) {
                step.classList.add('completed', 'active');
            } else if (index === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });

        const progress = (currentStep / (totalSteps - 1)) * 100;
        progressLine.style.width = `${progress}%`;
    }

    // تنظیم رویدادهای Drag & Drop
    const fileUploadBoxes = document.querySelectorAll('.file-upload-box');
    fileUploadBoxes.forEach(box => {
        const input = box.querySelector('input[type="file"]');
        
        box.addEventListener('click', () => input.click());
        
        box.addEventListener('dragover', (e) => {
            e.preventDefault();
            box.classList.add('border-primary');
        });

        box.addEventListener('dragleave', () => {
            box.classList.remove('border-primary');
        });

        box.addEventListener('drop', (e) => {
            e.preventDefault();
            box.classList.remove('border-primary');
            input.files = e.dataTransfer.files;
            updateFileName(input);
        });

        input.addEventListener('change', () => updateFileName(input));
    });

    function updateFileName(input) {
        const box = input.closest('.file-upload-box');
        const fileName = input.files[0]?.name;
        if (fileName) {
            box.querySelector('p').textContent = fileName;
        }
    }

    // نمایش/مخفی کردن بخش فیش واریزی
    document.querySelectorAll('input[name="charge_type"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const receiptContainer = document.getElementById('receiptUploadContainer');
            const receiptInput = document.querySelector('input[name="payment_receipt"]');
            if (e.target.value === 'cash') {
                receiptContainer.style.display = 'block';
                receiptInput.required = true;
            } else {
                receiptContainer.style.display = 'none';
                receiptInput.required = false;
            }
        });
    });

    // اعتبارسنجی فرم قبل از ارسال
    document.getElementById('uploadForm').addEventListener('submit', (e) => {
        const excelFile = document.querySelector('input[name="excel_file"]').files[0];
        const letterFile = document.querySelector('input[name="org_letter"]').files[0];
        
        if (!excelFile || !letterFile) {
            e.preventDefault();
            alert('لطفا تمام فایل‌های ضروری را آپلود کنید');
        }
    });
</script>

<?php if (isset($_SESSION['excel_data'])): ?>
    <!-- بخش نمایش جدول و آمار در پیام بعدی -->
<?php endif; ?>
<?php if (isset($_SESSION['excel_data'])): ?>
    <div class="data-container">
        <!-- کارت‌های آماری -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card primary">
                    <i class="bi bi-credit-card-2-front display-4 text-primary mb-3"></i>
                    <h3 class="counter" id="totalCards">0</h3>
                    <p class="mb-0">تعداد کل کارت‌ها</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                    <h3 class="counter" id="validCards">0</h3>
                    <p class="mb-0">کارت‌های معتبر</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger">
                    <i class="bi bi-x-circle display-4 text-danger mb-3"></i>
                    <h3 class="counter" id="invalidCards">0</h3>
                    <p class="mb-0">کارت‌های نامعتبر</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card info">
                    <i class="bi bi-cash-stack display-4 text-info mb-3"></i>
                    <h3 class="counter" id="totalAmount">0</h3>
                    <p class="mb-0">مجموع مبلغ (ریال)</p>
                </div>
            </div>
        </div>

        <!-- جدول داده‌ها -->
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>شماره کارت</th>
                        <th>مبلغ (ریال)</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['excel_data'] as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <input type="text" class="form-control form-control-sm card-number" 
                                   value="<?php echo $row[0]; ?>" disabled>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm amount" 
                                   value="<?php echo number_format($row[1]); ?>" disabled>
                        </td>
                        <td class="status"></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- دکمه‌های عملیات -->
        <div class="text-center mt-4">
            <button class="action-btn btn btn-success me-2" id="validateAllBtn">
                <i class="bi bi-check-circle me-2"></i>اعتبارسنجی همه
            </button>
            <button class="action-btn btn btn-primary" id="downloadBtn">
                <i class="bi bi-download me-2"></i>دانلود اطلاعات
            </button>
        </div>
    </div>

    <script>
        // بروزرسانی آمار
        updateStatistics();
        // اعتبارسنجی اولیه همه ردیف‌ها
        validateAllRows();
    </script>
<?php endif; ?>

</body>
</html>



