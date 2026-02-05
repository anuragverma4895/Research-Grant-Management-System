<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Grant Management System</title>
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

        .landing-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }

        .header-section h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .header-section p {
            font-size: 18px;
            opacity: 0.95;
        }

        .login-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 50px;
            background: #f8f9fa;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }

        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border-color: #667eea;
        }

        .login-card .icon {
            font-size: 70px;
            margin-bottom: 25px;
        }

        .login-card h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }

        .login-card p {
            font-size: 15px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .login-card a {
            display: inline-block;
            padding: 15px 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }

        .login-card a:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: scale(1.05);
        }

        .admin-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .admin-card h3,
        .admin-card p {
            color: white;
        }

        .admin-card a {
            background: rgba(255, 255, 255, 0.2);
        }

        .admin-card a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .footer-section {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 25px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .login-options {
                grid-template-columns: 1fr;
                padding: 30px 20px;
            }
            
            .header-section h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="header-section">
            <h1>🎓 Research Grant Management System</h1>
            <p>Empowering Research, Enabling Innovation</p>
        </div>

        <div class="login-options">
            <!-- User Login/Signup -->
            <div class="login-card">
                <div class="icon">👨‍🔬</div>
                <h3>Researcher Portal</h3>
                <p>Login to your existing account or create a new account to apply for research grants</p>
                <a href="login.php">Enter Portal</a>
            </div>

            <!-- Admin Login -->
            <div class="login-card">
                <div class="icon">🔐</div>
                <h3>Admin Portal</h3>
                <p>Administrative access to manage applications, researchers, and funding agencies</p>
                <a href="admin_login.php">Admin Login</a>
            </div>
        </div>

        <div class="footer-section">
            <p>&copy; 2025 Research Grant Management System | All Rights Reserved</p>
        </div>
    </div>
</body>
</html>