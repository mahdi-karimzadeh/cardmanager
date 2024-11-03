<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setRightToLeft(true);

$headers = [
    'نام',
    'نام خانوادگی',
    'شماره شناسنامه',
    'کد ملی',
    'نام پدر',
    'تاریخ تولد',
    'محل تولد',
    'مبلغ شارژ',
    'جنسیت',
    'آدرس محل کار',
    'موبایل'
];

$sheet->fromArray($headers, NULL, 'A1');

$sampleData = [
    'علی',
    'محمدی',
    '1234567',
    '0123456789',
    'محمد',
    '1370/01/01',
    'تهران',
    '1000000',
    'مرد',
    'تهران - خیابان ولیعصر',
    '09123456789'
];

$sheet->fromArray($sampleData, NULL, 'A2');

// تنظیم فرمت text برای تمام ستون‌ها
$lastColumn = $sheet->getHighestColumn();
$lastRow = $sheet->getHighestRow();
$range = 'A1:' . $lastColumn . $lastRow;
$sheet->getStyle($range)->getNumberFormat()->setFormatCode('@');

// تنظیم عرض ستون‌ها
foreach(range('A', $lastColumn) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);

if (!file_exists('templates')) {
    mkdir('templates', 0777, true);
}

$writer->save('templates/card_request_template.xlsx');
