<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row = $_POST['row'];
    $col = $_POST['col'] - 1; // کم کردن یک برای تطبیق با ایندکس آرایه
    $value = $_POST['value'];

    if (isset($_SESSION['excel_data'][$row])) {
        $_SESSION['excel_data'][$row][$col] = $value;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ردیف مورد نظر یافت نشد']);
    }
}
