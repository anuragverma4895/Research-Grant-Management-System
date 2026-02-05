<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";

$success_message = "";
$error_message = "";

// Add new researcher
if (isset($_POST['add_researcher'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $institution = trim($_POST['institution']);
    $department = trim($_POST['department']);
    $research_area = trim($_POST['research_area']);
    $qualification = trim($_POST['qualification']);
    $experience_years = intval($_POST['experience_years']);
    
    $stmt = $conn->prepare("INSERT INTO Researchers (first_name, last_name, email, phone, institution, department, research_area, qualification, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $phone, $institution, $department, $research_area, $qualification, $experience_years);
    
    if ($stmt->execute()) {
        $success_message = "Researcher added successfully!";
    } else {
        $error_message = "Error adding researcher.";
    }
}

// Delete researcher
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $researcher_id = intval($_GET['delete']);
    $conn->query("DELETE FROM Researchers WHERE researcher_id = $researcher_id");
    header("Location: manage_researchers.php?deleted=1");
    exit;
}

// Fetch all researchers
$researchers = $conn->query("SELECT * FROM Researchers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Researchers - RGMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 { font-size: 24px; }
        .navbar-right { display: flex; gap: 15px; }
        .navbar a {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .panel h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .submit-btn {
            padding: 12px 30px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>👨‍🔬 Manage Researchers</h1>
        <div class="navbar-right">
            <a href="admin_dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Add Researcher Form -->
        <div class="panel">
            <h2>➕ Add New Researcher</h2>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Institution</label>
                        <input type="text" name="institution">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Research Area</label>
                        <input type="text" name="research_area">
                    </div>
                    <div class="form-group">
                        <label>Qualification</label>
                        <select name="qualification">
                            <option value="Ph.D.">Ph.D.</option>
                            <option value="M.Phil.">M.Phil.</option>
                            <option value="Masters">Masters</option>
                            <option value="Bachelors">Bachelors</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Years of Experience</label>
                    <input type="number" name="experience_years" min="0" value="0">
                </div>

                <button type="submit" name="add_researcher" class="submit-btn">Add Researcher</button>
            </form>
        </div>

        <!-- Researchers List -->
        <div class="panel">
            <h2>📋 All Researchers</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Institution</th>
                        <th>Research Area</th>
                        <th>Experience</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($researcher = $researchers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $researcher['researcher_id']; ?></td>
                        <td><?php echo htmlspecialchars($researcher['first_name'] . ' ' . $researcher['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($researcher['email']); ?></td>
                        <td><?php echo htmlspecialchars($researcher['institution']); ?></td>
                        <td><?php echo htmlspecialchars($researcher['research_area']); ?></td>
                        <td><?php echo $researcher['experience_years']; ?> years</td>
                        <td>
                            <a href="?delete=<?php echo $researcher['researcher_id']; ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this researcher?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>