<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "functions.php";

$sql = "
    SELECT ga.application_id, ga.grant_title, ga.grant_description, ga.grant_amount_requested,
           ga.application_status, ga.submission_date, ga.documents_uploaded,
           r.first_name, r.last_name, r.email, r.institution,
           fa.agency_name
    FROM Grant_Applications ga
    JOIN Researchers r ON ga.researcher_id = r.researcher_id
    JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id
    ORDER BY ga.submission_date DESC
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Valuation Panel'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('admin', 'valuation'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Grant Valuation Panel</h2>
                <p class="text-gray-500 text-sm">Review all submitted applications</p>
            </div>
        </header>

        <div class="p-6 space-y-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($app = mysqli_fetch_assoc($result)): ?>
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-white font-bold text-lg"><?php echo clean($app['grant_title']); ?></h3>
                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                                <span><?php echo clean($app['first_name'] . ' ' . $app['last_name']); ?></span>
                                <span><?php echo clean($app['institution']); ?></span>
                            </div>
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
                            <p class="text-white font-semibold text-sm"><?php echo clean($app['agency_name']); ?></p>
                        </div>
                        <div class="bg-white/[0.02] rounded-lg p-3">
                            <p class="text-gray-500 text-xs">Submitted</p>
                            <p class="text-white font-semibold text-sm"><?php echo formatDate($app['submission_date']); ?></p>
                        </div>
                        <div class="bg-white/[0.02] rounded-lg p-3">
                            <p class="text-gray-500 text-xs">Contact</p>
                            <p class="text-white font-semibold text-xs"><?php echo clean($app['email']); ?></p>
                        </div>
                    </div>

                    <div class="bg-white/[0.02] rounded-lg p-3 mb-3">
                        <p class="text-gray-400 text-sm leading-relaxed"><?php echo nl2br(clean($app['grant_description'])); ?></p>
                    </div>

                    <?php if (!empty($app['documents_uploaded'])): ?>
                    <a href="<?php echo clean($app['documents_uploaded']); ?>" target="_blank" class="inline-flex items-center gap-2 text-primary-400 text-sm hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Proposal PDF
                    </a>
                    <?php endif; ?>

                    <div class="mt-3 text-gray-600 text-xs">Application ID: #<?php echo $app['application_id']; ?></div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-dark-800/50 border border-white/5 rounded-2xl p-12 text-center">
                    <p class="text-gray-500">No applications to review</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('sidebar-overlay').classList.toggle('hidden');}</script>
    <script src="script.js"></script>
</body>
</html>