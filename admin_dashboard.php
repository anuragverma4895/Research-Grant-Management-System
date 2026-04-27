<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "functions.php";

// Fetch comprehensive statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications");
$stats['total_apps'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status = 'Approved'");
$stats['approved'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status IN ('Submitted', 'Under Review')");
$stats['pending'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE application_status = 'Rejected'");
$stats['rejected'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Researchers");
$stats['total_researchers'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Funding_Agencies");
$stats['total_agencies'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grants");
$stats['grants_awarded'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(grant_amount_awarded), 0) as total FROM Grants");
$stats['total_funding'] = $result->fetch_assoc()['total'];

// Chart data - status distribution
$status_data = [];
$status_result = $conn->query("SELECT application_status, COUNT(*) as count FROM Grant_Applications GROUP BY application_status");
while ($row = $status_result->fetch_assoc()) {
    $status_data[$row['application_status']] = (int)$row['count'];
}

// Monthly applications data
$monthly_data = [];
$monthly_result = $conn->query("SELECT DATE_FORMAT(submission_date, '%Y-%m') as month, COUNT(*) as count FROM Grant_Applications GROUP BY month ORDER BY month DESC LIMIT 6");
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = (int)$row['count'];
}
$monthly_data = array_reverse($monthly_data);

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = [];
$params = [];
$types = '';

if ($search) {
    $where_clauses[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR ga.grant_title LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}
if ($filter_status && in_array($filter_status, ALLOWED_STATUSES)) {
    $where_clauses[] = "ga.application_status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$query = "SELECT ga.*, r.first_name, r.last_name, fa.agency_name 
          FROM Grant_Applications ga 
          JOIN Researchers r ON ga.researcher_id = r.researcher_id 
          JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id 
          $where_sql
          ORDER BY ga.submission_date DESC LIMIT 20";

if ($params) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $recent_apps = $stmt->get_result();
} else {
    $recent_apps = $conn->query($query);
}

// Handle status update
if (isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['application_status'];
    $admin_comments = trim($_POST['admin_comments'] ?? '');
    
    if (in_array($new_status, ALLOWED_STATUSES)) {
        $stmt = $conn->prepare("UPDATE Grant_Applications SET application_status = ?, admin_comments = ? WHERE application_id = ?");
        $stmt->bind_param("ssi", $new_status, $admin_comments, $application_id);
        $stmt->execute();
        
        $researcher_id_query = $conn->query("SELECT researcher_id FROM Grant_Applications WHERE application_id = $application_id");
        $researcher_data = $researcher_id_query->fetch_assoc();
        $user_id_query = $conn->query("SELECT user_id FROM Users WHERE researcher_id = {$researcher_data['researcher_id']}");
        if ($user_data = $user_id_query->fetch_assoc()) {
            createNotification($conn, $user_data['user_id'], "Your application #$application_id status has been updated to: $new_status", 'Application');
        }
        
        header("Location: admin_dashboard.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Admin Dashboard'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    
    <?php echo renderSidebar('admin', 'dashboard'); ?>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Dashboard</h2>
                    <p class="text-gray-500 text-sm">System overview & analytics</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-gray-400 text-sm hidden sm:block"><?php echo date('d M Y, h:i A'); ?></span>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-green-400 text-sm font-medium">Application status updated successfully!</p>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-primary-500/30 transition-all group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['total_apps']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Total Applications</p>
                </div>

                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-green-500/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['approved']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Approved</p>
                </div>

                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-amber-500/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['pending']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Pending</p>
                </div>

                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-red-500/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['rejected']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Rejected</p>
                </div>
            </div>

            <!-- Second Row Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $stats['total_researchers']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Researchers</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $stats['total_agencies']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Agencies</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $stats['grants_awarded']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Grants Awarded</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white">₹<?php echo number_format($stats['total_funding']/1000000, 1); ?>M</p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Total Funding</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                    <h3 class="text-white font-bold mb-4">Application Status Distribution</h3>
                    <div class="h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                    <h3 class="text-white font-bold mb-4">Monthly Applications</h3>
                    <div class="h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Search & Filter + Applications Table -->
            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <h3 class="text-white font-bold text-lg">Recent Applications</h3>
                        <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto" id="search-filter-form">
                            <div class="relative flex-1 sm:w-64">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="search" value="<?php echo clean($search); ?>" placeholder="Search by name or title..." id="search-input"
                                    class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm">
                            </div>
                            <select name="status" onchange="this.form.submit()" id="status-filter"
                                class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-gray-300 focus:border-primary-500 focus:outline-none text-sm">
                                <option value="">All Status</option>
                                <?php foreach (ALLOWED_STATUSES as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $filter_status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">Search</button>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">ID</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Researcher</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Title</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Amount</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Agency</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Date</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_apps->num_rows > 0): ?>
                                <?php while ($app = $recent_apps->fetch_assoc()): ?>
                                <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-400">#<?php echo $app['application_id']; ?></td>
                                    <td class="px-6 py-4 text-sm text-white font-medium"><?php echo clean($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-300 max-w-[200px] truncate"><?php echo clean($app['grant_title']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-300"><?php echo formatCurrency($app['grant_amount_requested']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean(substr($app['agency_name'], 0, 20)); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-400"><?php echo formatDate($app['submission_date']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border <?php echo getStatusClass($app['application_status']); ?>">
                                            <?php echo $app['application_status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                            <select name="application_status" class="px-2 py-1.5 bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 focus:outline-none">
                                                <?php foreach (ALLOWED_STATUSES as $s): ?>
                                                    <option value="<?php echo $s; ?>" <?php echo $app['application_status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold hover:bg-red-500/30 transition-colors">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        No applications found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="manage_researchers.php" class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-primary-500/30 transition-all group text-center">
                    <div class="w-12 h-12 rounded-xl bg-primary-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Researchers</p>
                    <p class="text-gray-500 text-xs mt-1">Manage profiles</p>
                </a>
                <a href="manage_agencies.php" class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-emerald-500/30 transition-all group text-center">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Agencies</p>
                    <p class="text-gray-500 text-xs mt-1">Manage agencies</p>
                </a>
                <a href="grant_valuation_panel.php" class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-amber-500/30 transition-all group text-center">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Valuation</p>
                    <p class="text-gray-500 text-xs mt-1">Review panel</p>
                </a>
                <a href="api/applications.php" target="_blank" class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-accent-500/30 transition-all group text-center">
                    <div class="w-12 h-12 rounded-xl bg-accent-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">REST API</p>
                    <p class="text-gray-500 text-xs mt-1">View endpoints</p>
                </a>
            </div>
        </div>
    </main>

    <script>
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Chart.js - Status Distribution
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($status_data)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($status_data)); ?>,
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#6b7280'],
                borderWidth: 0,
                borderRadius: 5,
                spacing: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#9ca3af', padding: 15, usePointStyle: true, pointStyleWidth: 10, font: { size: 11 } }
                }
            },
            cutout: '65%'
        }
    });

    // Chart.js - Monthly Applications
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_data)); ?>,
            datasets: [{
                label: 'Applications',
                data: <?php echo json_encode(array_values($monthly_data)); ?>,
                backgroundColor: 'rgba(99, 102, 241, 0.5)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b7280', font: { size: 11 } }, beginAtZero: true }
            }
        }
    });
    </script>
    <script src="script.js"></script>
</body>
</html>