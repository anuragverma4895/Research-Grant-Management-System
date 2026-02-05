<?php
ini_set('session.save_path', '/tmp');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db_connection.php";

$success_message = "";
$error_message = "";
$account_created = false;
$created_username = "";

// Helper function
function clean($data) {
    return trim($data ?? '');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {

    // Required fields
    $first_name = clean($_POST['first_name']);
    $last_name  = clean($_POST['last_name']);
    $email      = clean($_POST['email']);
    $username   = clean($_POST['username']);
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Optional fields
    $phone        = clean($_POST['phone']);
    $institution  = clean($_POST['institution']);
    $department   = clean($_POST['department']);
    $research_area = clean($_POST['research_area']);

    // Validation
    if ($first_name === "" || $last_name === "" || $email === "" || $username === "" || $password === "") {
        $error_message = "❌ Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "❌ Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_message = "❌ Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_message = "❌ Password must be at least 6 characters.";
    } else {

        // Check if email already exists in Researchers
        $check_email = $conn->prepare("SELECT researcher_id FROM Researchers WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $res_email = $check_email->get_result();

        if ($res_email->num_rows > 0) {
            $error_message = "❌ This email is already registered. Please login.";
        } else {

            // Check if username already exists in Users
            $check_user = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
            $check_user->bind_param("s", $username);
            $check_user->execute();
            $res_user = $check_user->get_result();

            if ($res_user->num_rows > 0) {
                $error_message = "❌ Username already taken. Choose another.";
            } else {

                // Insert into Researchers table
                $stmt_researcher = $conn->prepare("
                    INSERT INTO Researchers (first_name, last_name, email, phone, institution, department, research_area)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_researcher->bind_param("sssssss", $first_name, $last_name, $email, $phone, $institution, $department, $research_area);

                if ($stmt_researcher->execute()) {

                    $researcher_id = $stmt_researcher->insert_id;

                    // Insert into Users table
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt_user = $conn->prepare("
                        INSERT INTO Users (username, password, email, role, researcher_id, is_active)
                        VALUES (?, ?, ?, 'user', ?, 1)
                    ");
                    $stmt_user->bind_param("sssi", $username, $hashed_password, $email, $researcher_id);

                    if ($stmt_user->execute()) {
                        $account_created = true;
                        $created_username = $username;
                        $success_message = "🎉 Account created successfully!";
                    } else {
                        $error_message = "❌ Error creating user account.";
                    }

                    $stmt_user->close();

                } else {
                    $error_message = "❌ Error creating researcher profile.";
                }

                $stmt_researcher->close();
            }

            $check_user->close();
        }

        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Research Grant System</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:Segoe UI, Tahoma, sans-serif;
            background:linear-gradient(135deg,#667eea,#764ba2);
            min-height:100vh;
            padding:30px;
        }
        .box{
            max-width:900px;
            margin:auto;
            background:white;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,0.25);
        }
        .header{
            padding:35px;
            text-align:center;
            color:white;
            background:linear-gradient(135deg,#667eea,#764ba2);
        }
        .header h1{font-size:34px;}
        .content{padding:35px;}
        .alert{
            padding:14px 16px;
            border-radius:10px;
            margin-bottom:20px;
            font-weight:600;
        }
        .error{background:#ffe1e1;color:#b30000;border:2px solid #ff5a5a;}
        .success{background:#d4edda;color:#155724;border:2px solid #28a745;}
        .row{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
        }
        @media(max-width:700px){.row{grid-template-columns:1fr;}}
        label{display:block;margin:12px 0 8px;font-weight:700;color:#333;}
        input{
            width:100%;
            padding:14px;
            border:2px solid #e5e5e5;
            border-radius:10px;
            font-size:15px;
        }
        input:focus{outline:none;border-color:#667eea;}
        button{
            width:100%;
            margin-top:18px;
            padding:15px;
            border:none;
            border-radius:10px;
            background:linear-gradient(135deg,#667eea,#764ba2);
            color:white;
            font-size:16px;
            font-weight:800;
            cursor:pointer;
        }
        button:hover{opacity:0.95;}
        .links{text-align:center;margin-top:18px;}
        .links a{color:#667eea;text-decoration:none;font-weight:700;}
    </style>
</head>
<body>

<div class="box">
    <div class="header">
        <h1>✨ Create Researcher Account</h1>
        <p>Signup to apply for grants</p>
    </div>

    <div class="content">

        <?php if ($error_message): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($account_created): ?>
            <div class="alert success">
                ✅ Account created successfully! <br>
                Your username: <b><?php echo htmlspecialchars($created_username); ?></b><br><br>
                <a href="login.php" style="color:#155724;font-weight:800;">Click here to login</a>
            </div>
        <?php endif; ?>

        <?php if (!$account_created): ?>
        <form method="POST">

            <div class="row">
                <div>
                    <label>First Name *</label>
                    <input type="text" name="first_name" required>
                </div>
                <div>
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Institution</label>
                    <input type="text" name="institution">
                </div>
                <div>
                    <label>Department</label>
                    <input type="text" name="department">
                </div>
            </div>

            <label>Research Area</label>
            <input type="text" name="research_area">

            <div class="row">
                <div>
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
            </div>

            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="signup">🚀 Create Account</button>

            <div class="links">
                Already have an account? <a href="login.php">Login</a>
            </div>

        </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
