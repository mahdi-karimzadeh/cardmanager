<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // ایجاد فایل CSV
    $csvFile = fopen('php://temp', 'w');
    
    // نوشتن هدر CSV
    fputcsv($csvFile, ['شماره کارت', 'مبلغ']);
    
    // نوشتن داده‌ها
    foreach ($data as $row) {
        fputcsv($csvFile, [$row['cardNumber'], $row['amount']]);
    }
    
    // آماده‌سازی فایل برای دانلود
    rewind($csvFile);
    $csvContent = stream_get_contents($csvFile);
    fclose($csvFile);
    
    // ایجاد فایل ZIP
    $zip = new ZipArchive();
    $zipName = 'card_data_' . date('Y-m-d_H-i-s') . '.zip';
    
    if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
        $zip->addFromString('cards.csv', $csvContent);
        $zip->close();
        
        // ارسال فایل ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipName));
        readfile($zipName);
        unlink($zipName); // حذف فایل موقت
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'خطا در پردازش درخواست']);
?>
