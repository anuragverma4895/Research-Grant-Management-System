<?php
session_start();
include "auth_check.php";
requireReviewer();
include "db_connection.php";
require_once "functions.php";

$user = getCurrentUser();

// Get assigned/available applications for review
$applications = $conn->query("
    SELECT ga.*, r.first_name, r.last_name, r.institution, r.email as researcher_email, fa.agency_name
    FROM Grant_Applications ga
    JOIN Researchers r ON ga.researcher_id = r.researcher_id
    JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id
    WHERE ga.application_status IN ('Submitted', 'Under Review')
    ORDER BY ga.submission_date DESC
");

// Stats
$total_apps = $conn->query("SELECT COUNT(*) as c FROM Grant_Applications WHERE application_status IN ('Submitted', 'Under Review')")->fetch_assoc()['c'];
$reviewed = $conn->query("SELECT COUNT(*) as c FROM Grant_Applications WHERE application_status = 'Approved'")->fetch_assoc()['c'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $app_id = intval($_POST['application_id']);
    $score = intval($_POST['review_score']);
    $comments = sanitize($_POST['review_comments']);
    $recommendation = sanitize($_POST['recommendation']);
    
    if ($score >= 1 && $score <= 10) {
        // Update status to Under Review
        $conn->query("UPDATE Grant_Applications SET application_status = 'Under Review' WHERE application_id = $app_id AND application_status = 'Submitted'");
        
        $success = true;
        setFlash('success', "Review submitted for Application #$app_id");
        header("Location: reviewer_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Reviewer Dashboard'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('reviewer', 'dashboard'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Reviewer Dashboard</h2>
                <p class="text-gray-500 text-sm">Review and evaluate research proposals</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                    <p class="text-green-400 text-sm font-medium"><?php echo clean($flash['message']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $total_apps; ?></p>
                    <p class="text-gray-500 text-xs mt-1">Pending Reviews</p>
                </div>
                <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $reviewed; ?></p>
                    <p class="text-gray-500 text-xs mt-1">Approved Grants</p>
                </div>
                <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-5">
                    <p class="text-2xl font-black text-white"><?php echo $applications->num_rows; ?></p>
                    <p class="text-gray-500 text-xs mt-1">Available to Review</p>
                </div>
            </div>

            <!-- Applications to Review -->
            <div class="space-y-4">
                <h3 class="text-white font-bold text-lg">Applications for Review</h3>
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($app = $applications->fetch_assoc()): ?>
                    <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h4 class="text-white font-bold text-lg"><?php echo clean($app['grant_title']); ?></h4>
                                <p class="text-gray-500 text-sm">By <?php echo clean($app['first_name'] . ' ' . $app['last_name']); ?> • <?php echo clean($app['institution']); ?></p>
                            </div>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border <?php echo getStatusClass($app['application_status']); ?>">
                                <?php echo $app['application_status']; ?>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Amount</p>
                                <p class="text-white font-semibold text-sm"><?php echo formatCurrency($app['grant_amount_requested']); ?></p>
                            </div>
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Agency</p>
                                <p class="text-white font-semibold text-sm"><?php echo clean(substr($app['agency_name'], 0, 25)); ?></p>
                            </div>
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Submitted</p>
                                <p class="text-white font-semibold text-sm"><?php echo formatDate($app['submission_date']); ?></p>
                            </div>
                            <div class="bg-white/[0.02] rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Contact</p>
                                <p class="text-white font-semibold text-sm text-xs"><?php echo clean($app['researcher_email']); ?></p>
                            </div>
                        </div>

                        <div class="bg-white/[0.02] rounded-lg p-3 mb-4">
                            <p class="text-gray-400 text-sm leading-relaxed"><?php echo nl2br(clean(substr($app['grant_description'], 0, 300))); ?>...</p>
                        </div>

                        <?php if (!empty($app['documents_uploaded'])): ?>
                        <a href="<?php echo clean($app['documents_uploaded']); ?>" target="_blank" class="inline-flex items-center gap-2 text-primary-400 text-sm hover:underline mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Proposal PDF
                        </a>
                        <?php endif; ?>

                        <!-- Quick Review Form -->
                        <form method="POST" class="mt-4 pt-4 border-t border-white/5">
                            <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="text-gray-400 text-xs mb-1 block">Score (1-10)</label>
                                    <input type="number" name="review_score" min="1" max="10" required class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:border-primary-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="text-gray-400 text-xs mb-1 block">Recommendation</label>
                                    <select name="recommendation" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:border-primary-500 focus:outline-none">
                                        <option value="Approve" class="bg-dark-800">Approve</option>
                                        <option value="Neutral" class="bg-dark-800">Neutral</option>
                                        <option value="Reject" class="bg-dark-800">Reject</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-gray-400 text-xs mb-1 block">Comments</label>
                                    <input type="text" name="review_comments" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:border-primary-500 focus:outline-none" placeholder="Brief comments">
                                </div>
                            </div>
                            <button type="submit" name="submit_review" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors">Submit Review</button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-12 text-center">
                        <p class="text-gray-500">No applications pending review.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('sidebar-overlay').classList.toggle('hidden');}</script>
    <script src="script.js"></script>
</body>
</html>
