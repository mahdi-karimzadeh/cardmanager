    <?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nameco'] = $row['nameco'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "نام کاربری یا رمز عبور اشتباه است.";
        }
    } else {
        $error = "نام کاربری یا رمز عبور اشتباه است.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم مدیریت کارت</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <img src="images/pec-logo-new.png" alt="PEC Logo" class="logo">
        <h2>ورود به سیستم مدیریت کارت</h2>
        
        <form method="post" action="">
            <input type="text" name="username" placeholder="نام کاربری" required autocomplete="off">
            <input type="password" name="password" placeholder="رمز عبور" required autocomplete="off">
            <button type="submit">ورود</button>
        </form>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
    
</body>
</html>
