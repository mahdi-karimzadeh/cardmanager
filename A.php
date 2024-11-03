<?php
// Include necessary libraries and configurations
require_once 'config.php';
require_once 'vendor/autoload.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the uploaded files and form data
    $excelFile = $_FILES['excel_file'];
    $requestLetterFile = $_FILES['request_letter'];
    $paymentReceiptFile = $_FILES['payment_receipt'];
    $totalAmount = $_POST['total_amount'];

    // Validate and process the Excel file
    $cardData = processExcelFile($excelFile);

    // Validate card numbers and amounts
    $errors = validateCardData($cardData);

    if (empty($errors)) {
        // Generate txt file with card numbers and amounts
        $txtFilePath = generateTxtFile($cardData);

        // Create zip file with all uploaded files and generated txt file
        $zipFilePath = createZipFile($excelFile, $requestLetterFile, $paymentReceiptFile, $txtFilePath);

        // Provide download link for the zip file
        $downloadLink = $zipFilePath;
    }
}

// Helper functions
function processExcelFile($file) {
    // Process Excel file and return card data
    // You may use a library like PhpSpreadsheet for this
}

function validateCardData($cardData) {
    $errors = [];
    foreach ($cardData as $index => $card) {
        if (!isValidCardNumber($card['number'])) {
            $errors[$index][] = 'Invalid card number';
        }
        if (isDuplicateCardNumber($card['number'], $cardData)) {
            $errors[$index][] = 'Duplicate card number';
        }
        if (!isValidCardAlgorithm($card['number'])) {
            $errors[$index][] = 'Invalid card algorithm';
        }
        if (!is_int($card['amount'])) {
            $errors[$index][] = 'Amount must be an integer';
        }
    }
    return $errors;
}

function isValidCardNumber($number) {
    return strlen($number) === 16 && ctype_digit($number);
}

function isDuplicateCardNumber($number, $cardData) {
    $count = 0;
    foreach ($cardData as $card) {
        if ($card['number'] === $number) {
            $count++;
        }
    }
    return $count > 1;
}

function isValidCardAlgorithm($number) {
    // Implement card algorithm validation (e.g., Luhn algorithm)
}

function generateTxtFile($cardData) {
    // Generate txt file with card numbers and amounts
}

function createZipFile($excelFile, $requestLetterFile, $paymentReceiptFile, $txtFilePath) {
    // Create zip file with all uploaded files and generated txt file
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کارت - درخواست شارژ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #495057;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .table {
            background-color: #ffffff;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">درخواست شارژ کارت</h2>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">فایل اکسل</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="mb-3">
                        <label for="request_letter" class="form-label">نامه درخواست</label>
                        <input type="file" class="form-control" id="request_letter" name="request_letter" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_receipt" class="form-label">فیش واریزی</label>
                        <input type="file" class="form-control" id="payment_receipt" name="payment_receipt" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">مبلغ کل واریزی (ریال)</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" required>
                    </div>
                    <button type="submit" class="btn btn-primary">ثبت درخواست</button>
                    <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
                </form>

                <?php if (isset($cardData) && !empty($cardData)): ?>
                    <h3 class="mt-5">اطلاعات کارت‌ها</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>شماره کارت</th>
                                    <th>مبلغ (ریال)</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cardData) && is_array($cardData)): ?>
                                    <?php foreach ($cardData as $index => $card): ?>
                                        <tr>
                                            <td><?php echo $card['number']; ?></td>
                                            <td><?php echo number_format($card['amount']); ?></td>
                                            <td>
                                                <?php if (isset($errors[$index])): ?>
                                                    <?php foreach ($errors[$index] as $error): ?>
                                                        <div class="alert alert-danger p-1 mb-1"><?php echo $error; ?></div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-success p-1 mb-1">معتبر</div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="alert alert-warning p-1 mb-1">داده‌ای برای نمایش وجود ندارد.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (isset($downloadLink)): ?>
                    <div class="alert alert-success mt-3">
                        درخواست شما با موفقیت ثبت شد. لطفاً فایل زیپ شده را دانلود کنید.
                        <a href="<?php echo $downloadLink; ?>" class="btn btn-success mt-2">دانلود فایل</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
