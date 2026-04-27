<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "functions.php";

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = [];
$params = [];
$types = '';

if ($search) {
    $where_clauses[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR ga.grant_title LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
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
          $where_sql ORDER BY ga.submission_date DESC";

if ($params) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $apps = $stmt->get_result();
} else {
    $apps = $conn->query($query);
}

if (isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['application_status'];
    $admin_comments = sanitize($_POST['admin_comments'] ?? '');
    
    if (in_array($new_status, ALLOWED_STATUSES)) {
        $stmt = $conn->prepare("UPDATE Grant_Applications SET application_status = ?, admin_comments = ? WHERE application_id = ?");
        $stmt->bind_param("ssi", $new_status, $admin_comments, $application_id);
        $stmt->execute();
        header("Location: manage_applications.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Manage Applications'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('admin', 'applications'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Manage Applications</h2>
                <p class="text-gray-500 text-sm">Review and update application statuses</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-green-400 text-sm font-medium">Status updated successfully!</p>
                </div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="<?php echo clean($search); ?>" placeholder="Search by name or title..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm">
                </div>
                <select name="status" onchange="this.form.submit()"
                    class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-gray-300 focus:border-primary-500 focus:outline-none text-sm">
                    <option value="">All Status</option>
                    <?php foreach (ALLOWED_STATUSES as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $filter_status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">Search</button>
            </form>

            <!-- Applications -->
            <div class="space-y-4">
                <?php while ($app = $apps->fetch_assoc()): ?>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-white font-bold text-lg"><?php echo clean($app['grant_title']); ?></h3>
                            <p class="text-gray-500 text-sm mt-1">
                                By <?php echo clean($app['first_name'] . ' ' . $app['last_name']); ?> • 
                                <?php echo clean($app['agency_name']); ?> • 
                                <?php echo formatDate($app['submission_date']); ?>
                            </p>
                        </div>
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border shrink-0 <?php echo getStatusClass($app['application_status']); ?>">
                            <?php echo $app['application_status']; ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        <div class="bg-white/[0.02] rounded-lg p-3">
                            <p class="text-gray-500 text-xs">Amount</p>
                            <p class="text-white font-semibold text-sm"><?php echo formatCurrency($app['grant_amount_requested']); ?></p>
                        </div>
                        <div class="bg-white/[0.02] rounded-lg p-3">
                            <p class="text-gray-500 text-xs">Duration</p>
                            <p class="text-white font-semibold text-sm"><?php echo $app['project_duration_months'] ?? 'N/A'; ?> months</p>
                        </div>
                        <div class="bg-white/[0.02] rounded-lg p-3">
                            <p class="text-gray-500 text-xs">Priority</p>
                            <p class="text-white font-semibold text-sm"><?php echo $app['priority_level'] ?? 'Medium'; ?></p>
                        </div>
                    </div>

                    <?php if (!empty($app['grant_description'])): ?>
                    <div class="bg-white/[0.02] rounded-lg p-3 mb-4">
                        <p class="text-gray-400 text-sm leading-relaxed"><?php echo nl2br(clean(substr($app['grant_description'], 0, 300))); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($app['documents_uploaded'])): ?>
                    <a href="<?php echo clean($app['documents_uploaded']); ?>" target="_blank" class="inline-flex items-center gap-2 text-primary-400 text-sm hover:underline mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Download Proposal
                    </a>
                    <?php endif; ?>

                    <!-- Update Status Form -->
                    <form method="POST" class="mt-4 pt-4 border-t border-white/5 flex flex-col sm:flex-row items-start sm:items-end gap-3">
                        <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                        <div class="flex-1 w-full sm:w-auto">
                            <label class="text-gray-400 text-xs mb-1 block">Status</label>
                            <select name="application_status" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:outline-none">
                                <?php foreach (ALLOWED_STATUSES as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $app['application_status'] === $s ? 'selected' : ''; ?> class="bg-dark-800"><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1 w-full sm:w-auto">
                            <label class="text-gray-400 text-xs mb-1 block">Admin Comments</label>
                            <input type="text" name="admin_comments" value="<?php echo clean($app['admin_comments'] ?? ''); ?>" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:outline-none" placeholder="Add comments...">
                        </div>
                        <button type="submit" name="update_status" class="px-4 py-2 bg-red-500/20 text-red-400 rounded-lg text-sm font-semibold hover:bg-red-500/30 transition-colors whitespace-nowrap">
                            Update Status
                        </button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('sidebar-overlay').classList.toggle('hidden');}</script>
    <script src="script.js"></script>
</body>
</html>
