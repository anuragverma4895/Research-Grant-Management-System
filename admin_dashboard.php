<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";

// Fetch comprehensive statistics
$stats = [];

// Total applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications");
$stats['total_apps'] = $result->fetch_assoc()['total'];

// Approved applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status = 'Approved'");
$stats['approved'] = $result->fetch_assoc()['total'];

// Pending applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status IN ('Submitted', 'Under Review')");
$stats['pending'] = $result->fetch_assoc()['total'];

// Rejected applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status = 'Rejected'");
$stats['rejected'] = $result->fetch_assoc()['total'];

// Total researchers
$result = $conn->query("SELECT COUNT(*) as total FROM Researchers");
$stats['total_researchers'] = $result->fetch_assoc()['total'];

// Total funding agencies
$result = $conn->query("SELECT COUNT(*) as total FROM Funding_Agencies");
$stats['total_agencies'] = $result->fetch_assoc()['total'];

// Total grants awarded
$result = $conn->query("SELECT COUNT(*) as total FROM Grants");
$stats['grants_awarded'] = $result->fetch_assoc()['total'];

// Total funding allocated
$result = $conn->query("SELECT COALESCE(SUM(grant_amount_awarded), 0) as total FROM Grants");
$stats['total_funding'] = $result->fetch_assoc()['total'];

// Recent applications
$recent_apps = $conn->query("SELECT ga.*, r.first_name, r.last_name, fa.agency_name 
                             FROM Grant_Applications ga 
                             JOIN Researchers r ON ga.researcher_id = r.researcher_id 
                             JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id 
                             ORDER BY ga.submission_date DESC LIMIT 5");

// Handle status update
if (isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['application_status'];
    $admin_comments = trim($_POST['admin_comments'] ?? '');
    
    $allowed_statuses = ['Submitted', 'Under Review', 'Approved', 'Rejected', 'On Hold'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE Grant_Applications SET application_status = ?, admin_comments = ? WHERE application_id = ?");
        $stmt->bind_param("ssi", $new_status, $admin_comments, $application_id);
        $stmt->execute();
        
        // Create notification
        $researcher_id_query = $conn->query("SELECT researcher_id FROM Grant_Applications WHERE application_id = $application_id");
        $researcher_data = $researcher_id_query->fetch_assoc();
        $user_id_query = $conn->query("SELECT user_id FROM Users WHERE researcher_id = {$researcher_data['researcher_id']}");
        if ($user_data = $user_id_query->fetch_assoc()) {
            $message = "Your application #$application_id status has been updated to: $new_status";
            $notif_stmt = $conn->prepare("INSERT INTO Notifications (user_id, message, notification_type) VALUES (?, ?, 'Application')");
            $notif_stmt->bind_param("is", $user_data['user_id'], $message);
            $notif_stmt->execute();
        }
        
        header("Location: admin_dashboard.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RGMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar h1 {
            font-size: 24px;
        }

        .navbar-right {
            display: flex;
            gap: 15px;
        }

        .navbar a {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: all 0.3s;
        }

        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .admin-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .admin-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 5px solid #e74c3c;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .menu-item {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 2px solid transparent;
            text-align: center;
        }

        .menu-item:hover {
            border-color: #e74c3c;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(231,76,60,0.2);
        }

        .menu-item .icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .menu-item h3 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .menu-item p {
            font-size: 13px;
            color: #666;
        }

        .recent-panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .recent-panel h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-under.review { background: #d1ecf1; color: #0c5460; }
        .status-on.hold { background: #d6d8db; color: #383d41; }

        .update-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .update-form select,
        .update-form button {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
        }

        .update-form button {
            background: #e74c3c;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .update-form button:hover {
            background: #c0392b;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>🔐 Admin Control Panel</h1>
        <div class="navbar-right">
            <span>Administrator</span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">✅ Application status updated successfully!</div>
        <?php endif; ?>

        <!-- Admin Header -->
        <div class="admin-header">
            <h2>🎯 System Overview</h2>
            <p style="color: #666;">Manage all aspects of the Research Grant Management System</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📊</div>
                <h3>Total Applications</h3>
                <div class="number"><?php echo $stats['total_apps']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">✅</div>
                <h3>Approved</h3>
                <div class="number"><?php echo $stats['approved']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">⏳</div>
                <h3>Pending</h3>
                <div class="number"><?php echo $stats['pending']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">❌</div>
                <h3>Rejected</h3>
                <div class="number"><?php echo $stats['rejected']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">👥</div>
                <h3>Researchers</h3>
                <div class="number"><?php echo $stats['total_researchers']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">🏢</div>
                <h3>Agencies</h3>
                <div class="number"><?php echo $stats['total_agencies']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">🎁</div>
                <h3>Grants Awarded</h3>
                <div class="number"><?php echo $stats['grants_awarded']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">💰</div>
                <h3>Total Funding</h3>
                <div class="number">₹<?php echo number_format($stats['total_funding']/1000000, 1); ?>M</div>
            </div>
        </div>

        <!-- Admin Menu -->
        <h3 style="margin-bottom: 20px; color: #333;">⚙️ Management Tools</h3>
        <div class="admin-menu">
            <a href="manage_applications.php" class="menu-item">
                <div class="icon">📄</div>
                <h3>Manage Applications</h3>
                <p>Review and process grant applications</p>
            </a>

            <a href="manage_researchers.php" class="menu-item">
                <div class="icon">👨‍🔬</div>
                <h3>Manage Researchers</h3>
                <p>Add, edit, or remove researchers</p>
            </a>

            <a href="manage_agencies.php" class="menu-item">
                <div class="icon">🏛️</div>
                <h3>Funding Agencies</h3>
                <p>Manage funding organizations</p>
            </a>

            <a href="manage_reviewers.php" class="menu-item">
                <div class="icon">👨‍⚖️</div>
                <h3>Manage Reviewers</h3>
                <p>Handle expert review panel</p>
            </a>

            <a href="grant_allocation.php" class="menu-item">
                <div class="icon">💵</div>
                <h3>Grant Allocation</h3>
                <p>Allocate approved grants</p>
            </a>

            <a href="payment_management.php" class="menu-item">
                <div class="icon">💳</div>
                <h3>Payment Tracking</h3>
                <p>Monitor payment disbursements</p>
            </a>

            <a href="view_reports.php" class="menu-item">
                <div class="icon">📈</div>
                <h3>Analytics & Reports</h3>
                <p>Generate detailed reports</p>
            </a>

            <a href="system_settings.php" class="menu-item">
                <div class="icon">⚙️</div>
                <h3>System Settings</h3>
                <p>Configure system parameters</p>
            </a>
        </div>

        <!-- Recent Applications Table -->
        <div class="recent-panel">
            <h3>🆕 Recent Applications</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Researcher</th>
                        <th>Grant Title</th>
                        <th>Amount</th>
                        <th>Agency</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_apps->num_rows > 0): ?>
                        <?php while ($app = $recent_apps->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $app['application_id']; ?></td>
                            <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($app['grant_title'], 0, 40)); ?>...</td>
                            <td>₹<?php echo number_format($app['grant_amount_requested']); ?></td>
                            <td><?php echo htmlspecialchars(substr($app['agency_name'], 0, 20)); ?></td>
                            <td><?php echo date('d M Y', strtotime($app['submission_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '.', $app['application_status'])); ?>">
                                    <?php echo $app['application_status']; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="update-form">
                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                    <select name="application_status">
                                        <option value="Submitted" <?php if($app['application_status']=='Submitted') echo 'selected'; ?>>Submitted</option>
                                        <option value="Under Review" <?php if($app['application_status']=='Under Review') echo 'selected'; ?>>Under Review</option>
                                        <option value="Approved" <?php if($app['application_status']=='Approved') echo 'selected'; ?>>Approved</option>
                                        <option value="Rejected" <?php if($app['application_status']=='Rejected') echo 'selected'; ?>>Rejected</option>
                                        <option value="On Hold" <?php if($app['application_status']=='On Hold') echo 'selected'; ?>>On Hold</option>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">No applications found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>