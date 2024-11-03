<?php
session_start();

if (!isset($_SESSION['page_loaded'])) {
    $_SESSION['page_loaded'] = true;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

require_once 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

function clearSessionData() {
    $_SESSION['excel_data'] = [];
    $_SESSION['data_cleared'] = true;
    unset($_SESSION['page_loaded']);
}

function initSession() {
    if (!isset($_SESSION['data_cleared'])) {
        clearSessionData();
    }
}

initSession();

if (isset($_GET['clear'])) {
    clearSessionData();
    header('Location: request_card.php');
    exit;
}

function getGroupInfo($conn, $userId) {
    static $cache = [];
    
    if (!isset($cache[$userId])) {
        $stmt = $conn->prepare("SELECT idgroup, namegroup FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cache[$userId] = $result->fetch_assoc() ?: ['idgroup' => '0', 'namegroup' => 'نامشخص'];
    }
    
    return $cache[$userId];
}

if(isset($_POST['download_excel']) && !empty($_SESSION['excel_data'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    
    $headers = [
        'ردیف', 'نام', 'نام خانوادگی', 'شماره شناسنامه', 'کد ملی', 'نام پدر', 
        'تاریخ تولد', 'محل تولد', 'مبلغ شارژ', 'fname', 'lname',
        'جنسیت', 'آدرس منزل', 'تلفن منزل', 'کد پستی منزل', 'آدرس محل کار',
        'تلفن محل کار', 'محل ارسال کارت', 'محل ارسال رمز', 'محل ارسال صورتحساب',
        'کد گروه', 'نام گروه', 'شماره پرسنلی', 'نوع', 'شماره حساب',
        'کد شعبه', 'کنترلر', 'کد محل خدمت', 'موبایل'
    ];
    
    $sheet->fromArray($headers, NULL, 'A1');
    
    $groupInfo = getGroupInfo($conn, $_SESSION['user_id']);
    
    if (!empty($_SESSION['excel_data'])) {
        $row = 2;
        foreach ($_SESSION['excel_data'] as $index => $rowData) {
            $outputData = [
                $index + 1,
                $rowData[0], $rowData[1], $rowData[2], $rowData[3], $rowData[4],
                $rowData[5], $rowData[6], $rowData[7], $rowData[0], $rowData[1],
                ($rowData[8] == 'مرد' ? '1' : '2'),
                '0', '0', '0', $rowData[9], '0', '2', '2', '2',
                $groupInfo['idgroup'], $groupInfo['namegroup'],
                '0', '0', '0', '0', '0', '0', $rowData[10]
            ];
            
            $sheet->fromArray($outputData, NULL, 'A' . $row);
            $row++;
        }

        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $range = 'A1:' . $highestColumn . $highestRow;
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('@');

        foreach(range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        ob_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="card_requests.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

$data = [];
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $inputFileName = $_FILES['excel_file']['tmp_name'];
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    
    foreach ($worksheet->getRowIterator(2) as $rowIndex => $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue();
        }
        
        if (strlen($rowData[3]) !== 10) {
            $validationErrors[$rowIndex][] = "کد ملی در ردیف " . ($rowIndex-1) . " باید 10 رقم باشد";
        }
        if (strlen($rowData[10]) !== 11) {
            $validationErrors[$rowIndex][] = "شماره موبایل در ردیف " . ($rowIndex-1) . " باید 11 رقم باشد";
        }
        $birthDate = str_replace('/', '', $rowData[5]);
        if (strlen($birthDate) !== 8 && strlen($rowData[5]) !== 10) {
            $validationErrors[$rowIndex][] = "فرمت تاریخ تولد در ردیف " . ($rowIndex-1) . " نامعتبر است";
        }
        
        $data[] = $rowData;
    }
    $_SESSION['excel_data'] = $data;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>درخواست صدور کارت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #3b82f6;
            --success-color: #059669;
            --danger-color: #dc2626;
            --background-color: #f1f5f9;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Vazir', Tahoma, sans-serif;
            background: var(--background-color);
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 98%;
            padding: 2rem;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .stepper-wrapper {
            margin: 3rem auto;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
        }

        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            transition: all 0.4s ease;
        }

        .stepper-item::before,
        .stepper-item::after {
            position: absolute;
            content: "";
            border-bottom: 3px solid var(--border-color);
            width: 100%;
            top: 25px;
            z-index: 1;
        }

        .stepper-item::before { left: -50%; }
        .stepper-item::after { left: 50%; }
        .stepper-item:first-child::before,
        .stepper-item:last-child::after {
            content: none;
        }

        .step-counter {
            position: relative;
            z-index: 5;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--border-color);
            font-weight: bold;
            transition: all 0.4s ease;
        }

        .stepper-item.active .step-counter {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
            transform: scale(1.1);
        }

        .stepper-item.completed .step-counter {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .step-name {
            margin-top: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .stepper-item.active .step-name {
            color: var(--primary-color);
            font-weight: 600;
            transform: scale(1.05);
        }

        .stepper-item.completed .step-name {
            color: var(--success-color);
        }

        .upload-zone {
            padding: 2rem;
            margin: 1.5rem 0;
            background: #f8fafc;
            border: 2px dashed var(--border-color);
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
        }

        .btn-modern {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }

        .table-container {
            padding: 1.5rem;
            margin-top: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        #dataTable thead th {
            font-size: 0.875rem;
            padding: 1rem;
            background: #f8fafc;
            font-weight: 600;
        }

        .status-cell {
            font-size: 0.8125rem;
            padding: 0.5rem;
            border-radius: 6px;
        }

        .has-error {
            background-color: #fff5f5;
            transition: background-color 0.3s ease;
        }

        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .loading-content {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div id="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">در حال پردازش...</span>
            </div>
            <h5 class="mt-3">در حال پردازش فایل، لطفا صبر کنید...</h5>
        </div>
    </div>

    <div class="container main-container">
    <h3 class="text-center mb-4">درخواست صدور کارت</h3  >

        <div class="stepper-wrapper">
            <div class="stepper-item active">
                <div class="step-counter">1</div>
                <div class="step-name">آپلود فایل</div>
            </div>
            <div class="stepper-item">
                <div class="step-counter">2</div>
                <div class="step-name">بررسی اطلاعات</div>
            </div>
            <div class="stepper-item">
                <div class="step-counter">3</div>
                <div class="step-name">اعتبارسنجی</div>
            </div>
            <div class="stepper-item">
                <div class="step-counter">4</div>
                <div class="step-name">دانلود نهایی</div>
            </div>
        </div>
          <div class="step-content">
              <!-- مرحله 1: بارگذاری فایل -->
              <div class="step-pane" id="step1">
                  <div class="upload-zone">
                      <form method="post" enctype="multipart/form-data" id="uploadForm">
                          <div class="mb-3">
                              <label for="excel_file" class="form-label">فایل اکسل خود را انتخاب کنید</label>
                              <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                          </div>
                          <button type="submit" class="btn btn-primary btn-modern me-2">
                              <i class="mdi mdi-upload me-1"></i> بارگذاری فایل
                          </button>
                      </form>
                  </div>
              </div>

              <!-- مرحله 2: بررسی اطلاعات -->
              <div class="step-pane" id="step2">
                  <div class="table-container">
                      <!-- جدول داده‌ها -->
                      <?php if (!empty($data)): ?>
                          <table id="dataTable" class="table table-striped table-hover">
                              <thead>
                                  <tr>
                                      <th>ردیف</th>
                                      <th>نام</th>
                                      <th>نام خانوادگی</th>
                                      <th>شماره شناسنامه</th>
                                      <th>کد ملی</th>
                                      <th>نام پدر</th>
                                      <th>تاریخ تولد</th>
                                      <th>محل تولد</th>
                                      <th>مبلغ شارژ</th>
                                      <th>جنسیت</th>
                                      <th>آدرس محل کار</th>
                                      <th>موبایل</th>
                                      <th>وضعیت</th>
                                      <th>عملیات</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php foreach ($data as $index => $row): ?>
                                      <tr>
                                          <td><?php echo $index + 1; ?></td>
                                          <?php foreach ($row as $cell): ?>
                                              <td><?php echo htmlspecialchars($cell); ?></td>
                                          <?php endforeach; ?>
                                          <td class="status-cell"></td>
                                          <td>
                                              <button class="btn btn-sm btn-danger delete-btn" data-index="<?php echo $index; ?>">
                                                  <i class="mdi mdi-delete"></i>
                                              </button>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                      <?php endif; ?>
                  </div>
              </div>

              <!-- مرحله 3: اعتبارسنجی -->
              <div class="step-pane" id="step3">
                  <div class="validation-summary">
                      <!-- نمایش نتایج اعتبارسنجی -->
                  </div>
              </div>

              <!-- مرحله 4: دانلود -->
              <div class="step-pane" id="step4">
                  <div class="download-section text-center">
                      <form method="post" name="download_excel">
                          <button type="submit" name="download_excel" class="btn btn-success btn-modern" id="downloadButton">
                              <i class="mdi mdi-download me-1"></i> دانلود فایل اکسل
                          </button>
                      </form>
                  </div>
              </div>
          </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#dataTable').DataTable({
                language: {
                    paginate: {
                        next: 'بعدی',
                        previous: 'قبلی'
                    },
                    emptyTable: 'داده‌ای برای نمایش وجود ندارد',
                    zeroRecords: 'رکوردی یافت نشد'
                },
                ordering: false,
                searching: false,
                info: false,
                lengthChange: false,
                pageLength: 10,
                "drawCallback": function(settings) {
                    // Validate all visible rows after page change
                    $('#dataTable tbody tr:visible').each(function() {
                        updateStatus($(this));
                    });
                }
            });

            function updateStepperState(currentStep) {
                $('.stepper-item').removeClass('active completed');
                $('.stepper-item').each(function(index) {
                    if (index < currentStep) {
                        $(this).addClass('completed');
                    } else if (index === currentStep) {
                        $(this).addClass('active');
                    }
                });
            }

            function validateCell(value, type) {
                let errors = [];
                switch(type) {
                    case 'national_id':
                        if (value.length !== 10) {
                            errors.push('کد ملی باید 10 رقم باشد');
                        }
                        break;
                    case 'mobile':
                        if (value.length !== 11) {
                            errors.push('شماره موبایل باید 11 رقم باشد');
                        }
                        break;
                    case 'birth_date':
                        const cleanDate = value.replace('/', '');
                        if (cleanDate.length !== 8 && value.length !== 10) {
                            errors.push('فرمت تاریخ تولد نامعتبر است');
                        }
                        break;
                }
                return errors;
            }

            function updateStatus(row) {
                const statusCell = row.find('.status-cell');
                const nationalId = row.find('td:eq(4)').text();
                const birthDate = row.find('td:eq(6)').text();
                const mobile = row.find('td:eq(11)').text();

                let errors = [
                    ...validateCell(nationalId, 'national_id'),
                    ...validateCell(birthDate, 'birth_date'),
                    ...validateCell(mobile, 'mobile')
                ];

                if (errors.length > 0) {
                    const errorTooltip = errors.join('، ');
                    statusCell.html(`
                        <span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="${errorTooltip}">
                            <i class="mdi mdi-alert"></i> ${errors.length} خطا
                        </span>
                    `);
                    row.addClass('has-error');
                    $('[data-bs-toggle="tooltip"]').tooltip();
                } else {
                    statusCell.html(`<span class="text-success"><i class="mdi mdi-check"></i> معتبر</span>`);
                    row.removeClass('has-error');
                }

                updateDownloadButton();
                checkValidationStep();
            }

            function checkValidationStep() {
                const hasErrors = $('#dataTable tbody tr.has-error').length > 0;
                if (!hasErrors && $('#dataTable tbody tr').length > 0) {
                    $('#dataTable').trigger('validation-complete');
                }
            }

            function updateDownloadButton() {
                const hasErrors = $('#dataTable tbody tr.has-error').length > 0;
                $('#downloadButton').prop('disabled', hasErrors);
            }

            function showStep(stepNumber) {
                $('.step-pane').removeClass('active');
                $(`#step${stepNumber}`).addClass('active');
                updateStepperState(stepNumber - 1);
            }

            // اجرای اولیه
            showStep(1);

            // مدیریت رویدادها
          // Add this inside the existing $(document).ready function
$('#uploadForm').on('submit', function() {
    const fileSize = $('#excel_file')[0].files[0].size;
    const maxSize = 1024 * 1024; // 1MB threshold

    if (fileSize > maxSize) {
        $('#loading-overlay').css('display', 'flex');
    }
    
    showStep(2);
});

            $('#dataTable').on('validation-complete', function() {
                showStep(3);
            });

            $('#downloadButton').on('click', function() {
                showStep(4);
            });

            $('#clearData').on('click', function() {
                if (confirm('آیا مطمئن هستید که می‌خواهید تمام داده‌ها را پاک کنید؟')) {
                    window.location.href = 'request_card.php?clear=1';
                }
            });

            $('#dataTable tbody').on('click', 'td:not(:first-child):not(:last-child):not(.status-cell)', function() {
                const cell = $(this);
                if (!cell.hasClass('editing')) {
                    const currentValue = cell.text();
                    const input = $('<input type="text">').val(currentValue);
                    cell.html(input).addClass('editing');
                    input.focus();

                    input.on('blur', function() {
                        const newValue = $(this).val();
                        cell.html(newValue).removeClass('editing');
                        updateStatus(cell.closest('tr'));
                    });

                    input.on('keypress', function(e) {
                        if (e.which === 13) {
                            $(this).blur();
                        }
                    });
                }
            });

            if ($('#dataTable tbody tr').length > 0) {
                showStep(2);
                $('#dataTable tbody tr').each(function() {
                    updateStatus($(this));
                });
            }
        });
    </script>
</body>
</html>

