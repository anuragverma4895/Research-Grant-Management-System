<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";
require_once "functions.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];
$success_message = "";
$error_message = "";

$agencies = $conn->query("SELECT * FROM Funding_Agencies ORDER BY agency_name");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    $grant_title = sanitize($_POST['grant_title']);
    $grant_description = sanitize($_POST['grant_description']);
    $grant_amount = floatval($_POST['grant_amount_requested']);
    $funding_agency_id = intval($_POST['funding_agency_id']);
    $project_duration = intval($_POST['project_duration_months']);
    $priority_level = $_POST['priority_level'];
    
    if (empty($grant_title) || empty($grant_description) || $grant_amount <= 0) {
        $error_message = "Please fill all required fields correctly.";
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
            $stmt = $conn->prepare("INSERT INTO Grant_Applications (researcher_id, grant_title, grant_description, grant_amount_requested, funding_agency_id, project_duration_months, priority_level, documents_uploaded, submission_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->bind_param("issdisss", $researcher_id, $grant_title, $grant_description, $grant_amount, $funding_agency_id, $project_duration, $priority_level, $uploaded_file);
            
            if ($stmt->execute()) {
                $application_id = $stmt->insert_id;
                createNotification($conn, $user['user_id'], "Your grant application #$application_id has been submitted successfully!", 'Application');
                $success_message = "Grant application submitted successfully! Application ID: #$application_id";
            } else {
                $error_message = "Error submitting application. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Apply for Grant'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    
    <?php echo renderSidebar('user', 'apply'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Apply for Grant</h2>
                    <p class="text-gray-500 text-sm">Submit a new research grant application</p>
                </div>
                <a href="user_dashboard.php" class="text-gray-400 hover:text-white text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Dashboard
                </a>
            </div>
        </header>

        <div class="p-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-8">
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-green-400 text-sm font-medium"><?php echo clean($success_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <p class="text-red-400 text-sm font-medium"><?php echo clean($error_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-6">
                            <label class="block text-gray-300 text-sm font-medium mb-2">Grant Title <span class="text-red-400">*</span></label>
                            <input type="text" name="grant_title" required id="grant-title"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Enter a descriptive title for your research grant">
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-300 text-sm font-medium mb-2">Grant Description <span class="text-red-400">*</span></label>
                            <textarea name="grant_description" required rows="5" id="grant-description"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm resize-y"
                                placeholder="Describe your research project, objectives, methodology, and expected outcomes"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2">Amount Requested (₹) <span class="text-red-400">*</span></label>
                                <input type="number" name="grant_amount_requested" required min="1" step="0.01" id="grant-amount"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                    placeholder="Enter amount">
                            </div>
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2">Duration (Months) <span class="text-red-400">*</span></label>
                                <input type="number" name="project_duration_months" required min="1" max="120" id="grant-duration"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                    placeholder="Project duration">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2">Funding Agency <span class="text-red-400">*</span></label>
                                <select name="funding_agency_id" required id="grant-agency"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm">
                                    <option value="" class="bg-dark-800">-- Select Agency --</option>
                                    <?php while ($agency = $agencies->fetch_assoc()): ?>
                                        <option value="<?php echo $agency['funding_agency_id']; ?>" class="bg-dark-800">
                                            <?php echo clean($agency['agency_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-300 text-sm font-medium mb-2">Priority Level</label>
                                <select name="priority_level" id="grant-priority"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm">
                                    <option value="Medium" class="bg-dark-800">Medium</option>
                                    <option value="High" class="bg-dark-800">High</option>
                                    <option value="Low" class="bg-dark-800">Low</option>
                                </select>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-8">
                            <label class="block text-gray-300 text-sm font-medium mb-2">Upload Proposal (PDF)</label>
                            <div class="border-2 border-dashed border-white/10 rounded-xl p-6 text-center hover:border-primary-500/30 transition-all cursor-pointer" id="drop-zone" onclick="document.getElementById('proposal_pdf').click()">
                                <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-400 text-sm mb-1">Click to upload or drag and drop</p>
                                <p class="text-gray-600 text-xs">PDF files only, max 5MB</p>
                                <p class="text-primary-400 text-sm mt-2 hidden" id="file-name"></p>
                                <input type="file" name="proposal_pdf" id="proposal_pdf" accept=".pdf" class="hidden" onchange="showFileName(this)">
                            </div>
                        </div>

                        <button type="submit" name="submit_application" id="submit-btn"
                            class="w-full py-3.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary-500/25 transition-all duration-300 text-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Submit Application
                        </button>
                    </form>
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
    function showFileName(input) {
        const nameEl = document.getElementById('file-name');
        if (input.files.length > 0) {
            nameEl.textContent = '📄 ' + input.files[0].name;
            nameEl.classList.remove('hidden');
        }
    }
    </script>
    <script src="script.js"></script>
</body>
</html>