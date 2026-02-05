<?php
session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error_message = "";

// ADMIN PASSWORD (Hidden)
$ADMIN_PASSWORD = "Anurag@2006";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['admin_login'])) {
    $password = $_POST['password'];

    if (empty($password)) {
        $error_message = "Please enter the admin password.";
    } else {
        if ($password === $ADMIN_PASSWORD) {
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = "Admin";
            $_SESSION['role'] = "admin";

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid admin password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - RGMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .admin-login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }

        .admin-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .admin-header .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .admin-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .admin-body {
            padding: 40px 35px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            background: #fee;
            color: #c33;
            border-left: 4px solid #e55;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .admin-login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-header">
            <div class="icon">🔐</div>
            <h1>Admin Access</h1>
            <p>Authorized Personnel Only</p>
        </div>

        <div class="admin-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="password">Admin Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter admin password">
                </div>

                <button type="submit" name="admin_login" class="admin-login-btn">
                    🔓 Access Admin Panel
                </button>
            </form>

            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
