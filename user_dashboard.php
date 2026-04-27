<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";
require_once "functions.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];

$stmt = $conn->prepare("SELECT * FROM Researchers WHERE researcher_id = ?");
$stmt->bind_param("i", $researcher_id);
$stmt->execute();
$researcher = $stmt->get_result()->fetch_assoc();

$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id");
$stats['total_applications'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id AND application_status = 'Approved'");
$stats['approved'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id AND application_status IN ('Submitted', 'Under Review')");
$stats['pending'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COALESCE(SUM(g.grant_amount_awarded), 0) as total FROM Grants g JOIN Grant_Applications ga ON g.application_id = ga.application_id WHERE ga.researcher_id = $researcher_id");
$stats['total_funding'] = $result->fetch_assoc()['total'];

$recent_apps = $conn->query("SELECT * FROM Grant_Applications WHERE researcher_id = $researcher_id ORDER BY submission_date DESC LIMIT 5");
$notifications = $conn->query("SELECT * FROM Notifications WHERE user_id = {$user['user_id']} ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Researcher Dashboard'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    
    <?php echo renderSidebar('user', 'dashboard'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Welcome back, <?php echo clean($researcher['first_name']); ?>!</h2>
                    <p class="text-gray-500 text-sm"><?php echo clean($researcher['institution'] ?? 'Researcher'); ?> • <?php echo clean($researcher['department'] ?? ''); ?></p>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-primary-500/30 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-primary-500/10 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['total_applications']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Total Applications</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-green-500/30 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['approved']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Approved Grants</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-amber-500/30 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo $stats['pending']; ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Pending Review</p>
                </div>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 hover:border-accent-500/30 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-accent-500/10 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-white"><?php echo formatCurrency($stats['total_funding']); ?></p>
                    <p class="text-gray-500 text-xs font-medium mt-1">Total Funding</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="apply_grant.php" class="bg-gradient-to-br from-primary-600/20 to-accent-600/20 border border-primary-500/20 rounded-2xl p-5 text-center hover:border-primary-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Apply for Grant</p>
                </a>
                <a href="my_applications.php" class="bg-dark-800/50 border border-white/5 rounded-2xl p-5 text-center hover:border-white/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">My Applications</p>
                </a>
                <a href="grant_application.php" class="bg-dark-800/50 border border-white/5 rounded-2xl p-5 text-center hover:border-white/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Quick Apply</p>
                </a>
                <a href="logout.php" class="bg-dark-800/50 border border-white/5 rounded-2xl p-5 text-center hover:border-red-500/20 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    <p class="text-white font-semibold text-sm">Logout</p>
                </a>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Applications -->
                <div class="lg:col-span-2 bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                    <h3 class="text-white font-bold mb-4">Recent Applications</h3>
                    <div class="space-y-3">
                        <?php if ($recent_apps->num_rows > 0): ?>
                            <?php while ($app = $recent_apps->fetch_assoc()): ?>
                                <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4 hover:border-white/10 transition-all">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-white font-semibold text-sm truncate"><?php echo clean($app['grant_title']); ?></h4>
                                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                <span><?php echo formatCurrency($app['grant_amount_requested']); ?></span>
                                                <span><?php echo formatDate($app['submission_date']); ?></span>
                                            </div>
                                        </div>
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border shrink-0 <?php echo getStatusClass($app['application_status']); ?>">
                                            <?php echo $app['application_status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500 text-sm">No applications yet. Start by applying for a grant!</p>
                                <a href="apply_grant.php" class="inline-flex items-center gap-2 mt-3 text-primary-400 text-sm font-semibold hover:text-primary-300">
                                    Apply Now <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                    <h3 class="text-white font-bold mb-4">Notifications</h3>
                    <div class="space-y-3">
                        <?php if ($notifications->num_rows > 0): ?>
                            <?php while ($notif = $notifications->fetch_assoc()): ?>
                                <div class="p-3 rounded-xl text-sm <?php echo $notif['is_read'] ? 'bg-white/[0.02]' : 'bg-primary-500/5 border border-primary-500/10'; ?>">
                                    <p class="text-gray-300 text-xs leading-relaxed"><?php echo clean($notif['message']); ?></p>
                                    <p class="text-gray-600 text-[10px] mt-2"><?php echo formatDateTime($notif['created_at']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm text-center py-4">No new notifications</p>
                        <?php endif; ?>
                    </div>
                </div>
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