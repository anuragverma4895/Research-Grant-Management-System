<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";

$success_message = "";

// Add new agency
if (isset($_POST['add_agency'])) {
    $agency_name = trim($_POST['agency_name']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $address = trim($_POST['address']);
    $funding_area = trim($_POST['funding_area']);
    $agency_type = $_POST['agency_type'];
    $website = trim($_POST['website']);
    $total_budget = floatval($_POST['total_budget']);
    
    $stmt = $conn->prepare("INSERT INTO Funding_Agencies (agency_name, contact_email, contact_phone, address, funding_area, agency_type, website, total_budget) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssd", $agency_name, $contact_email, $contact_phone, $address, $funding_area, $agency_type, $website, $total_budget);
    
    if ($stmt->execute()) {
        $success_message = "Funding agency added successfully!";
    }
}

// Delete agency
if (isset($_GET['delete'])) {
    $agency_id = intval($_GET['delete']);
    $conn->query("DELETE FROM Funding_Agencies WHERE funding_agency_id = $agency_id");
    header("Location: manage_agencies.php?deleted=1");
    exit;
}

$agencies = $conn->query("SELECT * FROM Funding_Agencies ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agencies - RGMS</title>
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .form-group select,
        .form-group textarea {
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
            font-size: 13px;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .delete-btn {
            padding: 6px 12px;
            background: #e74c3c;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏛️ Manage Funding Agencies</h1>
        <div class="navbar-right">
            <a href="admin_dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="panel">
            <h2>➕ Add New Funding Agency</h2>

            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Agency Name</label>
                        <input type="text" name="agency_name" required>
                    </div>
                    <div class="form-group">
                        <label>Agency Type</label>
                        <select name="agency_type">
                            <option value="Government">Government</option>
                            <option value="Private">Private</option>
                            <option value="NGO">NGO</option>
                            <option value="International">International</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="tel" name="contact_phone">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Funding Area</label>
                        <input type="text" name="funding_area" placeholder="e.g., Science & Technology">
                    </div>
                    <div class="form-group">
                        <label>Website</label>
                        <input type="url" name="website" placeholder="https://">
                    </div>
                </div>

                <div class="form-group">
                    <label>Total Budget (₹)</label>
                    <input type="number" name="total_budget" step="0.01" min="0">
                </div>

                <button type="submit" name="add_agency" class="submit-btn">Add Agency</button>
            </form>
        </div>

        <div class="panel">
            <h2>📋 All Funding Agencies</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agency Name</th>
                        <th>Type</th>
                        <th>Funding Area</th>
                        <th>Budget</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($agency = $agencies->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $agency['funding_agency_id']; ?></td>
                        <td><?php echo htmlspecialchars($agency['agency_name']); ?></td>
                        <td><?php echo $agency['agency_type']; ?></td>
                        <td><?php echo htmlspecialchars($agency['funding_area']); ?></td>
                        <td>₹<?php echo number_format($agency['total_budget']); ?></td>
                        <td><?php echo htmlspecialchars($agency['contact_email']); ?></td>
                        <td>
                            <a href="?delete=<?php echo $agency['funding_agency_id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Delete this agency?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>