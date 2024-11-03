<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Set proper headers for ZIP download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="card_data.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
        // Create new ZIP archive
        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip->open($tempFile, ZipArchive::CREATE);
    
        // Add card data from POST
        if (isset($_POST['data'])) {
            $zip->addFromString('card_data.txt', $_POST['data']);
        }
    
        // Add files from session with proper names and extensions
        if (isset($_SESSION['excel_file_data'])) {
            $zip->addFromString('original_data.xlsx', base64_decode($_SESSION['excel_file_data']));
        }
    
        if (isset($_SESSION['org_letter_data'])) {
            $zip->addFromString('organization_letter.' . $_SESSION['org_letter_ext'], 
                base64_decode($_SESSION['org_letter_data']));
        }
    
        if (isset($_SESSION['payment_receipt_data'])) {
            $zip->addFromString('payment_receipt.' . $_SESSION['payment_receipt_ext'], 
                base64_decode($_SESSION['payment_receipt_data']));
        }
    
        $zip->close();
    
        // Output ZIP file
        readfile($tempFile);
        unlink($tempFile);
        exit();
}

// Return error if something went wrong
header('HTTP/1.1 500 Internal Server Error');
echo "Failed to create ZIP file";
