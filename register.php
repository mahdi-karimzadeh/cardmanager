<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nameco = trim($_POST['nameco']);
    
    // هش کردن رمز عبور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, password, nameco) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashed_password, $nameco);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "ثبت نام با موفقیت انجام شد";
        header("Location: index.php");
        exit();
    } else {
        $error = "خطا در ثبت نام";
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ثبت نام کاربر جدید</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>ثبت نام کاربر جدید</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>نام کاربری</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>رمز عبور</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>نام شرکت</label>
                                <input type="text" name="nameco" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">ثبت نام</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php">بازگشت به صفحه ورود</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
