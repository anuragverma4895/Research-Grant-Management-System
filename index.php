<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Grant Management System</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Update path to CSS -->
    <style>
        /* Additional styling for the homepage */
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 50px 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .intro-container {
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f8f8;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .intro-container h2 {
            color: #4CAF50;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .intro-container p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .intro-container .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin: 10px;
        }

        .intro-container .btn:hover {
            background-color: #45a049;
        }

        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin: 50px 20px;
        }

        .feature {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .feature h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #4CAF50;
        }

        .feature p {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            position: relative;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome to the Research Grant Management System</h1>
    <p>Your one-stop platform for managing research grant applications, reviews, and awards.</p>
</header>

<div class="intro-container">
    <h2>What We Do</h2>
    <p>Our system streamlines the process of applying, reviewing, and awarding research grants. It connects researchers with funding agencies to help drive innovation and support academic research.</p>
    <a href="grant_application.php" class="btn">Apply for a Grant</a> <!-- Updated to match the relative path -->
    <a href="admin_dashboard.php" class="btn">Admin Dashboard</a> <!-- Updated to match the relative path -->
</div>

<div class="features-container">
    <div class="feature">
        <h3>Researcher Portal</h3>
        <p>Easily submit your research grant applications, track the status of your submissions, and receive valuable feedback from reviewers.</p>
    </div>
    <div class="feature">
        <h3>Admin Dashboard</h3>
        <p>Efficiently manage grant applications, evaluate submissions, and make funding decisions for researchers. View and update statuses with a simple interface.</p>
    </div>
    <div class="feature">
        <h3>Funding Agencies</h3>
        <p>Connect with the right researchers by reviewing grant applications and awarding funds to the most promising projects in your area of interest.</p>
    </div>
</div>

<footer>
    <p>&copy; 2025 Research Grant Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
