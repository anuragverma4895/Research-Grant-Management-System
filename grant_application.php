<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";
require_once "functions.php";

if (!isset($_SESSION['researcher_id']) || empty($_SESSION['researcher_id'])) {
    header("Location: logout.php");
    exit();
}

$researcher_id = intval($_SESSION['researcher_id']);
$result_agencies = $conn->query("SELECT funding_agency_id, agency_name FROM Funding_Agencies");
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $grant_title = sanitize($_POST['grant_title'] ?? '');
    $grant_description = sanitize($_POST['grant_description'] ?? '');
    $grant_amount_requested = floatval($_POST['grant_amount_requested'] ?? 0);
    $funding_agency_id = intval($_POST['funding_agency_id'] ?? 0);

    if ($grant_title === "" || $grant_description === "" || $grant_amount_requested <= 0 || $funding_agency_id <= 0) {
        $error_message = "Please fill all fields correctly.";
    } else {
        // Handle file upload
        $uploaded_file = null;
        if (isset($_FILES['proposal_pdf']) && $_FILES['proposal_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = handleFileUpload($_FILES['proposal_pdf'], 'proposal');
            if ($upload_result['success']) {
                $uploaded_file = $upload_result['path'];
            } else {
                $error_message = $upload_result['error'];
            }
        }

        if (empty($error_message)) {
            $stmt = $conn->prepare("
                INSERT INTO Grant_Applications
                (researcher_id, grant_title, grant_description, grant_amount_requested, application_status, submission_date, funding_agency_id, documents_uploaded)
                VALUES (?, ?, ?, ?, 'Submitted', CURDATE(), ?, ?)
            ");
            $stmt->bind_param("issdis", $researcher_id, $grant_title, $grant_description, $grant_amount_requested, $funding_agency_id, $uploaded_file);

            if ($stmt->execute()) {
                $success_message = "Grant application submitted successfully!";
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}

$stmt_my_apps = $conn->prepare("SELECT application_id, grant_title, grant_amount_requested, submission_date, application_status FROM Grant_Applications WHERE researcher_id = ? ORDER BY submission_date DESC");
$stmt_my_apps->bind_param("i", $researcher_id);
$stmt_my_apps->execute();
$result_my_apps = $stmt_my_apps->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Quick Apply'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('user', 'quick_apply'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Quick Grant Application</h2>
                <p class="text-gray-500 text-sm">Submit and track your applications</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Application Form -->
            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                <h3 class="text-white font-bold mb-4">New Application</h3>

                <?php if ($success_message): ?>
                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-6 flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-green-400 text-sm font-medium"><?php echo clean($success_message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                        <p class="text-red-400 text-sm font-medium"><?php echo clean($error_message); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Grant Title *</label>
                        <input type="text" name="grant_title" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Enter grant title">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Description *</label>
                        <textarea name="grant_description" required rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm resize-y" placeholder="Describe your research project"></textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Amount (₹) *</label>
                            <input type="number" name="grant_amount_requested" required min="1" step="0.01" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm" placeholder="Amount">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Funding Agency *</label>
                            <select name="funding_agency_id" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm">
                                <option value="" class="bg-dark-800">-- Select --</option>
                                <?php if ($result_agencies && $result_agencies->num_rows > 0): ?>
                                    <?php while ($agency = $result_agencies->fetch_assoc()): ?>
                                        <option value="<?php echo $agency['funding_agency_id']; ?>" class="bg-dark-800"><?php echo clean($agency['agency_name']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Upload Proposal (PDF, optional)</label>
                        <input type="file" name="proposal_pdf" accept=".pdf" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-gray-400 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary-500/20 file:text-primary-400">
                    </div>

                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">Submit Application</button>
                </form>
            </div>

            <!-- Applications Table -->
            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/5">
                    <h3 class="text-white font-bold">My Applications</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">ID</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Title</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Amount</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Date</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_my_apps->num_rows > 0): ?>
                                <?php while ($app = $result_my_apps->fetch_assoc()): ?>
                                <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                                    <td class="px-6 py-4 text-sm text-gray-400">#<?php echo $app['application_id']; ?></td>
                                    <td class="px-6 py-4 text-sm text-white font-medium"><?php echo clean($app['grant_title']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-300"><?php echo formatCurrency($app['grant_amount_requested']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-400"><?php echo formatDate($app['submission_date']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border <?php echo getStatusClass($app['application_status']); ?>">
                                            <?php echo $app['application_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No applications yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('sidebar-overlay').classList.toggle('hidden');}</script>
    <script src="script.js"></script>
</body>
</html>
