<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";
require_once "functions.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = "ga.researcher_id = ?";
$params = [$researcher_id];
$types = 'i';

if ($search) {
    $where .= " AND (ga.grant_title LIKE ? OR fa.agency_name LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $types .= 'ss';
}
if ($filter_status && in_array($filter_status, ALLOWED_STATUSES)) {
    $where .= " AND ga.application_status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$stmt = $conn->prepare("SELECT ga.*, fa.agency_name, fa.funding_area FROM Grant_Applications ga JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id WHERE $where ORDER BY ga.submission_date DESC");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$applications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('My Applications'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('user', 'applications'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">My Applications</h2>
                    <p class="text-gray-500 text-sm">Track your grant applications</p>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Search & Filter -->
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="<?php echo clean($search); ?>" placeholder="Search by title or agency..."
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
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($app = $applications->fetch_assoc()): ?>
                    <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex-1">
                                <h3 class="text-white font-bold text-lg"><?php echo clean($app['grant_title']); ?></h3>
                                <p class="text-gray-500 text-sm mt-1"><?php echo clean($app['agency_name']); ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-primary-500/20 text-primary-400 px-3 py-1 rounded-full text-xs font-bold">#<?php echo $app['application_id']; ?></span>
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border <?php echo getStatusClass($app['application_status']); ?>">
                                    <?php echo $app['application_status']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Amount</p>
                                <p class="text-white font-semibold text-sm"><?php echo formatCurrency($app['grant_amount_requested']); ?></p>
                            </div>
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Submitted</p>
                                <p class="text-white font-semibold text-sm"><?php echo formatDate($app['submission_date']); ?></p>
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
                        <div class="bg-white/[0.02] border-l-2 border-primary-500/30 rounded-r-lg p-3 mb-3">
                            <p class="text-gray-400 text-sm leading-relaxed"><?php echo nl2br(clean(substr($app['grant_description'], 0, 200))); ?>...</p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($app['documents_uploaded'])): ?>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <a href="<?php echo clean($app['documents_uploaded']); ?>" target="_blank" class="text-primary-400 text-sm hover:underline">View Uploaded Proposal</a>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($app['admin_comments'])): ?>
                        <div class="bg-amber-500/5 border border-amber-500/10 rounded-xl p-3">
                            <p class="text-amber-400 text-xs font-semibold mb-1">Admin Comments:</p>
                            <p class="text-gray-400 text-sm"><?php echo nl2br(clean($app['admin_comments'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-white/5">
                            <span class="text-gray-600 text-xs">Updated: <?php echo formatDateTime($app['updated_at']); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-12 text-center">
                        <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3 class="text-white font-bold text-xl mb-2">No Applications Yet</h3>
                        <p class="text-gray-500 text-sm mb-6">Start your research journey by submitting a grant application.</p>
                        <a href="apply_grant.php" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
                            Apply for Grant
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    </script>
    <script src="script.js"></script>
</body>
</html>