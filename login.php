<?php
session_start();
require_once "db_connection.php";

// Already logged in
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin'
        ? "admin_dashboard.php"
        : "grant_application.php"));
    exit();
}

$error_message = "";
$user_not_found = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$username || !$password) {
        $error_message = "Please enter both username and password.";
    } else {

        // SIMPLE & COMPATIBLE QUERY
        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role, researcher_id
             FROM Users
             WHERE username = ? AND role = 'user'
             LIMIT 1"
        );

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['researcher_id'] = $user['researcher_id'];

                header("Location: grant_application.php");
                exit();
            } else {
                $error_message = "❌ Incorrect password. Please try again.";
            }
        } else {
            $user_not_found = true;
            $error_message = "⚠️ User not found! Please create an account first.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Research Grant Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .login-body {
            padding: 40px 35px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
            font-weight: 500;
        }

        .alert-error {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }

        .user-not-found-box {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }

        .user-not-found-box a {
            display: inline-block;
            padding: 12px 35px;
            background: white;
            color: #ff6b6b;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 700;
            font-size: 16px;
        }

        .form-group { margin-bottom: 25px; }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
        }

        .or-divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }

        .or-divider::before { left: 0; }
        .or-divider::after { right: 0; }

        .or-divider span {
            background: white;
            padding: 0 15px;
            color: #999;
            font-weight: 600;
        }

        .create-account-btn {
            width: 100%;
            padding: 16px;
            background: white;
            color: #667eea;
            border: 3px solid #667eea;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h1>🎓 Researcher Login</h1>
        <p>Access Your Research Portal</p>
    </div>

    <div class="login-body">

        <?php if ($user_not_found): ?>
            <div class="user-not-found-box">
                <h3>⚠️ Account Not Found!</h3>
                <p>This username does not exist.</p>
                <a href="signup.php">🚀 Create New Account</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message) && !$user_not_found): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login" class="login-btn">🔐 Login</button>
        </form>

        <div class="or-divider"><span>OR</span></div>

        <a href="signup.php" class="create-account-btn">✨ Create New Account</a>

        <div class="back-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
