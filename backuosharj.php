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
    $upload_dir = sys_get_temp_dir() . '/' . uniqid();
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Handle file uploads
    $org_letter = $_FILES['org_letter'];
    $payment_receipt = $_FILES['payment_receipt'];
    
    // Save uploaded files
    move_uploaded_file($org_letter['tmp_name'], $upload_dir . '/org_letter.' . pathinfo($org_letter['name'], PATHINFO_EXTENSION));
    move_uploaded_file($payment_receipt['tmp_name'], $upload_dir . '/payment_receipt.' . pathinfo($payment_receipt['name'], PATHINFO_EXTENSION));
    
    $_SESSION['upload_dir'] = $upload_dir;

    $depositAmount = isset($_POST['deposit_amount']) ? intval($_POST['deposit_amount']) : 0;
    $inputFileName = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    array_shift($rows);
    
    $_SESSION['excel_data'] = $rows;
    $_SESSION['deposit_amount'] = $depositAmount;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت شارژ کارت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* استایل‌های اصلی */
        :root {
            --primary: #2563eb;    
            --secondary: #4f46e5;  
            --success: #059669;    
            --danger: #dc2626;     
            --warning: #d97706;    
            --info: #0891b2;       
            --light: #f1f5f9;      
            --dark: #0f172a;       
        }

        body {
            font-family: 'Vazir', 'Tahoma', sans-serif;
            background: var(--light);
            min-height: 100vh;
            font-size: 14px;
            color: var(--dark);
        }

        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        /* Upload Section */
        .upload-section {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            margin-bottom: 2rem;
        }

        .upload-box {
            background: var(--light);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .upload-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .upload-box .input-group {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 0.5rem;
        }

        /* Table Design */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1.5rem;
        }

        .data-table th {
            background: var(--light);
            padding: 1rem;
            font-weight: 600;
            text-align: right;
            color: var(--dark);
        }

        .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            filter: brightness(110%);
            transform: translateY(-1px);
        }

        .custom-file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
            margin-bottom: 1rem;
        }

        .custom-file-upload:hover {
            border-color: var(--primary);
            background: #f1f5f9;
        }

        .custom-file-upload i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .file-name {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        .download-btn {
            background: linear-gradient(45deg, var(--primary), #60a5fa);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.1);
        }
        /* Form Controls */
        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Status Indicators */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-valid {
            background: #dcfce7;
            color: #166534;
        }

        .status-invalid {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .upload-section {
                grid-template-columns: 1fr;
            }
        
            .data-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* اضافه کردن page-header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 2.5rem;
            border-radius: 24px;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15);
        }

        /* اضافه کردن stats-card */
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.8rem;
            transition: all 0.3s ease;
            border-right: 4px solid var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
    </head>
    <body>
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="page-header text-center mb-5">
                    <h1 class="display-5 mb-3">سامانه مدیریت شارژ کارت</h1>
                    <p class="lead text-white-50">مدیریت و پردازش شارژ کارت‌های سازمانی</p>
                </div>

                <!-- اضافه کردن بخش آمار -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">تعداد کل کارت‌ها</h6>
                                    <h3 class="counter" id="totalCards">0</h3>
                                </div>
                                <i class="bi bi-credit-card-2-front fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <!-- سایر کارت‌های آمار -->
                </div>
                        <form action="" method="post" enctype="multipart/form-data" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="input-group mb-3">
                                        <input type="file" class="form-control" name="excel_file" accept=".xlsx, .xls" required>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                                             آپلود فایل اکسل
                                            </button>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="org_letter" accept="image/*,.pdf" required>
                                        <label class="input-group-text">تصویر نامه سازمان</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="payment_receipt" accept="image/*,.pdf" required>
                                        <label class="input-group-text">تصویر فیش واریزی</label>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <?php if (isset($_SESSION['excel_data'])): ?>
                        <div class="table-responsive">
                            <table id="cardTable" class="table table-striped table-hover">
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
                                        <td><input type="text" class="form-control form-control-sm" name="card_number[]" value="<?php echo $row[0]; ?>" disabled></td>
                                        <td><input type="text" class="form-control form-control-sm" name="amount[]" value="<?php echo number_format($row[1]); ?>" data-original-value="<?php echo $row[1]; ?>" disabled></td>
                                        <td class="status"></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-btn" onclick="toggleEdit(this)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="text-center mt-3">
                                <button id="downloadBtn" class="btn btn-success">
                                    <i class="bi bi-download me-2"></i>دانلود اطلاعات
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateCard(cardNumber) {
        let sum = 0;
        let isEven = false;
        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber.charAt(i), 10);
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            sum += digit;
            isEven = !isEven;
        }
        return (sum % 10 === 0);
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function validateRow(row) {
        const cardInput = row.querySelector('input[name="card_number[]"]');
        const amountInput = row.querySelector('input[name="amount[]"]');
        const statusCell = row.querySelector('.status');
        let errors = [];

        if (cardInput.value.length !== 16) {
            errors.push("شماره کارت باید 16 رقمی باشد");
        }
        if (!validateCard(cardInput.value)) {
            errors.push("شماره کارت نامعتبر است");
        }

        const allCards = document.querySelectorAll('input[name="card_number[]"]');
        const duplicates = Array.from(allCards).filter(input => input !== cardInput && input.value === cardInput.value);
        if (duplicates.length > 0) {
            errors.push("شماره کارت تکراری است");
        }

        statusCell.textContent = errors.length > 0 ? errors.join("، ") : "معتبر";
        statusCell.style.color = errors.length > 0 ? "red" : "green";
    }

    function validateAllRows() {
        const rows = document.querySelectorAll('#cardTable tbody tr');
        rows.forEach(validateRow);
    }

    function deleteRow(button) {
        const row = button.closest('tr');
        row.remove();
        updateRowNumbers();
        validateAllRows();
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#cardTable tbody tr');
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });
    }

    function toggleEdit(button) {
        const row = button.closest('tr');
        const inputs = row.querySelectorAll('input');
        const isEditing = button.classList.contains('editing');

        inputs.forEach(input => {
            input.disabled = isEditing;
        });

        if (isEditing) {
            button.innerHTML = '<i class="bi bi-pencil"></i>';
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
            validateRow(row);
        } else {
            button.innerHTML = '<i class="bi bi-check"></i>';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
        }

        button.classList.toggle('editing');
    }

    async function downloadTableData() {
        const table = document.getElementById('cardTable');
        let data = '';
        
        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            const cardNumber = row.cells[1].querySelector('input').value;
            const amount = row.cells[2].querySelector('input').value.replace(/,/g, '');
            data += `${cardNumber},${amount}\n`;
        }

        const formData = new FormData();
        formData.append('action', 'create_zip');
        formData.append('data', data);

        try {
            const response = await fetch('create_zip.php', {
                method: 'POST',
                body: formData
            });
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'card_data.zip';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Download failed:', error);
        }
    }

    // اضافه کردن تابع updateStatistics
    function updateStatistics() {
        const table = document.getElementById('cardTable');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        const totalCards = rows.length;
        let validCards = 0;
        let totalAmount = 0;

        rows.forEach(row => {
            const statusCell = row.querySelector('.status');
            const amountInput = row.querySelector('input[name="amount[]"]');
            
            if (statusCell.textContent.includes('معتبر')) {
                validCards++;
            }
            
            const amount = parseInt(amountInput.value.replace(/,/g, '')) || 0;
            totalAmount += amount;
        });

        document.getElementById('totalCards').textContent = totalCards.toLocaleString('fa-IR');
        document.getElementById('validCards').textContent = validCards.toLocaleString('fa-IR');
        document.getElementById('invalidCards').textContent = (totalCards - validCards).toLocaleString('fa-IR');
        document.getElementById('totalAmount').textContent = totalAmount.toLocaleString('fa-IR');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('#cardTable')) {
            validateAllRows();
            updateStatistics();
            document.querySelector('#cardTable').addEventListener('input', function(e) {
                if (e.target.matches('input')) {
                    validateRow(e.target.closest('tr'));
                    updateStatistics();
                }
            });
        }

        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', downloadTableData);
        }
    });
    </script>
    <?php
    if (isset($_SESSION['excel_data'])) {
        unset($_SESSION['excel_data']);
        unset($_SESSION['deposit_amount']);
    }
    ?>
</body>
</html>
