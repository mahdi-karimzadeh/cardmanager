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
        // Store Excel file
        $_SESSION['excel_file_data'] = base64_encode(file_get_contents($_FILES['excel_file']['tmp_name']));
    
        // Store organization letter
        $_SESSION['org_letter_data'] = base64_encode(file_get_contents($_FILES['org_letter']['tmp_name']));
        $_SESSION['org_letter_ext'] = pathinfo($_FILES['org_letter']['name'], PATHINFO_EXTENSION);
    
        // Store payment receipt if exists
        if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
            $_SESSION['payment_receipt_data'] = base64_encode(file_get_contents($_FILES['payment_receipt']['tmp_name']));
            $_SESSION['payment_receipt_ext'] = pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION);
        }
    
        // Process Excel data as before
        $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows);
        $_SESSION['excel_data'] = $rows;
    
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
?>

<<!DOCTYPE html>
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
            --primary: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f1f5f9;
        }

        body {
        font-family: 'Vazir', sans-serif;
        background: var(--light);
        color: var(--dark);
    }
    .page-header {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    padding: 3rem;
    border-radius: 25px;
    margin: -2rem -2rem 2rem -2rem;
    color: white;
    box-shadow: 0 15px 30px rgba(79, 70, 229, 0.15);
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
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

        .upload-box .form-label {
            font-weight: 500;
            margin-bottom: 1rem;
            color: #374151;
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

        .btn-check:checked + .btn-outline-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-group {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
        }

        .btn-group .btn {
            padding: 0.75rem 2rem;
        }

        .card-body {
            background: #f8fafc;
            border-radius: 16px;
            padding: 2rem;
        }
        .info-card {
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.info-card:hover {
    transform: translateY(-5px);
}

.info-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.info-card .counter {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
}

.table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.table thead {
    background: linear-gradient(45deg, #3b82f6, #60a5fa);
    color: white;
}
.upload-box {
    border: 2px dashed #e2e8f0;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-box:hover {
    border-color: var(--primary);
    background: #f8fafc;
    transform: scale(1.02);
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: #f1f5f9;
    transform: scale(1.01);
}
.btn-check:checked + .btn-outline-primary {
    background-color: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-outline-primary {
    border: 1px solid var(--primary);
    color: var(--primary);
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background-color: var(--primary);
    color: white;
}

.btn-group {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}
.page-header {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    color: white;
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.1);
}
.stats-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}
 /* Add to your existing style section */
.btn-group-vertical {
    gap: 0.5rem;
}

.btn-group-vertical .btn {
    border-radius: 8px !important;
    text-align: right;
    padding: 1rem;
    transition: all 0.3s ease;
}

.btn-group-vertical .btn:hover {
    transform: translateX(-5px);
}

.btn-check:checked + .btn-outline-primary {
    background: linear-gradient(45deg, var(--primary), #60a5fa);
    border-color: transparent;
}

#surplus_fields .form-group {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

    </style>
    </head>
    <body>
        
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4 text-center">مدیریت شارژ کارت</h2>
                        
                        <form action="" method="post" enctype="multipart/form-data" class="mb-4">
                            <!-- Charge Type Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-group text-center">
                                        <label class="mb-2">نوع شارژ:</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="charge_type" id="cash" value="cash" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary w-50" for="cash">
                                                <i class="bi bi-cash me-2"></i>نقدی
                                            </label>

                                            <input type="radio" class="btn-check" name="charge_type" id="credit" value="credit" autocomplete="off">
                                            <label class="btn btn-outline-primary w-50" for="credit">
                                                <i class="bi bi-credit-card me-2"></i>بستانکاری
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Excel File Upload -->
                                <div class="col-md-12">
                                    <div class="upload-box">
                                        <label class="form-label">فایل اکسل</label>
                                        <input type="file" class="form-control" name="excel_file" accept=".xlsx, .xls" required>
                                    </div>
                                </div>

                                <!-- Organization Letter Upload -->
                                <div class="col-md-6">
                                    <div class="upload-box">
                                        <label class="form-label">نامه سازمان</label>
                                        <input type="file" class="form-control" name="org_letter" accept="image/*,.pdf" required>
                                    </div>
                                </div>

                                <!-- Payment Receipt Upload -->
                                <div class="col-md-6" id="payment_receipt_container">
                                    <div class="upload-box">
                                        <label class="form-label">فیش واریزی</label>
                                        <input type="file" class="form-control" name="payment_receipt" accept="image/*,.pdf">
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="bi bi-upload me-2"></i>آپلود و پردازش
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                        <?php if (isset($_SESSION['excel_data'])): ?>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-card bg-primary text-white">
                                    <i class="bi bi-credit-card"></i>
                                    <h3 class="counter" id="totalCards">0</h3>
                                    <p>تعداد کل کارت‌ها</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-card bg-success text-white">
                                    <i class="bi bi-check-circle"></i>
                                    <h3 class="counter" id="validCards">0</h3>
                                    <p>کارت‌های معتبر</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-card bg-danger text-white">
                                    <i class="bi bi-x-circle"></i>
                                    <h3 class="counter" id="invalidCards">0</h3>
                                    <p>کارت‌های نامعتبر</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-card bg-info text-white">
                                    <i class="bi bi-cash-stack"></i>
                                    <h3 class="counter" id="totalAmount">0</h3>
                                    <p>مجموع مبلغ (ریال)</p>
                                </div>
                            </div>
                        </div>
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
<!-- Surplus Management Section -->
<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <div class="form-group text-center">
            <label class="mb-2">نحوه مدیریت مازاد شارژ:</label>
            <div class="btn-group-vertical w-100" role="group">
                <input type="radio" class="btn-check" name="surplus_type" id="gift_card" value="gift_card" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="gift_card">
                    <i class="bi bi-gift me-2"></i>دریافت کارت هدیه
                </label>

                <input type="radio" class="btn-check" name="surplus_type" id="distribute" value="distribute" autocomplete="off">
                <label class="btn btn-outline-primary" for="distribute">
                    <i class="bi bi-diagram-3 me-2"></i>تسهیم مازاد شارژ بر روی کارت‌ها
                </label>

                <input type="radio" class="btn-check" name="surplus_type" id="transfer" value="transfer" autocomplete="off">
                <label class="btn btn-outline-primary" for="transfer">
                    <i class="bi bi-bank me-2"></i>واریز به حساب سازمان
                </label>
            </div>
        </div>
    </div>
</div>

<!-- Additional Fields Container -->
<div class="row mb-4" id="surplus_fields">
    <!-- Gift Card Fields -->
    <div class="col-md-6 mx-auto" id="gift_card_fields">
        <div class="form-group">
            <label class="form-label">شماره کارت هدیه</label>
            <input type="text" class="form-control" name="gift_card_number" maxlength="16" placeholder="شماره کارت 16 رقمی">
        </div>
    </div>

    <!-- Bank Transfer Fields -->
    <div class="col-md-6 mx-auto d-none" id="transfer_fields">
        <div class="form-group">
            <label class="form-label">شماره حساب سازمان</label>
            <input type="text" class="form-control" name="organization_account" placeholder="شماره حساب سازمان">
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add this to your existing script section
document.addEventListener('DOMContentLoaded', function() {
    const surplusTypeInputs = document.querySelectorAll('input[name="surplus_type"]');
    const giftCardFields = document.getElementById('gift_card_fields');
    const transferFields = document.getElementById('transfer_fields');

    surplusTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Hide all fields first
            giftCardFields.classList.add('d-none');
            transferFields.classList.add('d-none');

            // Show relevant fields based on selection
            switch(this.value) {
                case 'gift_card':
                    giftCardFields.classList.remove('d-none');
                    break;
                case 'transfer':
                    transferFields.classList.remove('d-none');
                    break;
                case 'distribute':
                    // No additional fields needed for distribution
                    break;
            }
        });
    });

    // Validate gift card number
    const giftCardInput = document.querySelector('input[name="gift_card_number"]');
    if (giftCardInput) {
        giftCardInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substr(0, 16);
        });
    }
});

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
      // تابع جدید برای فرمت کردن اعداد با جداکننده
      function formatAmount(input) {
          // حذف همه کاراکترهای غیر عددی
          let value = input.value.replace(/\D/g, '');
          // اضافه کردن جداکننده
          input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }

      // اضافه کردن event listener برای input های مبلغ
      document.addEventListener('DOMContentLoaded', function() {
          const amountInputs = document.querySelectorAll('input[name="amount[]"]');
          amountInputs.forEach(input => {
              input.addEventListener('input', function() {
                  formatAmount(this);
              });
              // فرمت کردن مقادیر اولیه
              formatAmount(input);
          });
      });
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

    // تابع جدید برای فرمت کردن اعداد با جداکننده
    function formatAmount(input) {
        // حذف همه کاراکترهای غیر عددی
        let value = input.value.replace(/\D/g, '');
        // اضافه کردن جداکننده
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // اضافه کردن event listener برای input های مبلغ
    document.addEventListener('DOMContentLoaded', function() {
        const amountInputs = document.querySelectorAll('input[name="amount[]"]');
        amountInputs.forEach(input => {
            input.addEventListener('input', function() {
                formatAmount(this);
            });
            // فرمت کردن مقادیر اولیه
            formatAmount(input);
        });
    });
      // Add this function to check if all cards are valid
      function areAllCardsValid() {
          const statusCells = document.querySelectorAll('#cardTable .status');
          return Array.from(statusCells).every(cell => cell.textContent === "معتبر");
      }

      // Modify the downloadTableData function
      async function downloadTableData() {
          if (!areAllCardsValid()) {
              alert('لطفا ابتدا همه کارت‌ها را اصلاح کنید');
              return;
          }
    
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
    
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('#cardTable')) {
            validateAllRows();
            document.querySelector('#cardTable').addEventListener('input', function(e) {
                if (e.target.matches('input')) {
                    validateRow(e.target.closest('tr'));
                }
            });
        }

        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', downloadTableData);
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
    const chargeTypeInputs = document.querySelectorAll('input[name="charge_type"]');
    const paymentReceiptInput = document.querySelector('input[name="payment_receipt"]');
    
    chargeTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'cash') {
                paymentReceiptInput.required = true;
                document.getElementById('payment_receipt_container').style.display = 'block';
            } else {
                paymentReceiptInput.required = false;
                document.getElementById('payment_receipt_container').style.display = 'none';
            }
        });
    });
});

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
            
            if (statusCell.textContent === "معتبر") {
                validCards++;
            }
            
            // محاسبه مجموع مبلغ با حذف کاما از اعداد
            const amount = parseInt(amountInput.value.replace(/,/g, '')) || 0;
            totalAmount += amount;
        });

        document.getElementById('totalCards').textContent = totalCards;
        document.getElementById('validCards').textContent = validCards;
        document.getElementById('invalidCards').textContent = totalCards - validCards;
        document.getElementById('totalAmount').textContent = new Intl.NumberFormat('fa-IR').format(totalAmount);
    }

    // اضافه کردن فراخوانی تابع به رویدادهای مختلف
    document.addEventListener('DOMContentLoaded', function() {
        updateStatistics();
        
        // بروزرسانی آمار در زمان تغییر جدول
        const cardTable = document.getElementById('cardTable');
        if (cardTable) {
            cardTable.addEventListener('input', updateStatistics);
            cardTable.addEventListener('click', function(e) {
                if (e.target.closest('.btn-danger')) {
                    setTimeout(updateStatistics, 100);
                }
            });
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
