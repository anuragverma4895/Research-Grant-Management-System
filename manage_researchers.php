<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "functions.php";

$success_message = "";
$error_message = "";

if (isset($_POST['add_researcher'])) {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $institution = sanitize($_POST['institution']);
    $department = sanitize($_POST['department']);
    $research_area = sanitize($_POST['research_area']);
    $qualification = sanitize($_POST['qualification']);
    $experience_years = intval($_POST['experience_years']);
    
    $stmt = $conn->prepare("INSERT INTO Researchers (first_name, last_name, email, phone, institution, department, research_area, qualification, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $phone, $institution, $department, $research_area, $qualification, $experience_years);
    
    if ($stmt->execute()) {
        $success_message = "Researcher added successfully!";
    } else {
        $error_message = "Error adding researcher.";
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $researcher_id = intval($_GET['delete']);
    $conn->query("DELETE FROM Researchers WHERE researcher_id = $researcher_id");
    header("Location: manage_researchers.php?deleted=1");
    exit;
}

$researchers = $conn->query("SELECT * FROM Researchers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Manage Researchers'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('admin', 'researchers'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Manage Researchers</h2>
                <p class="text-gray-500 text-sm">Add and manage researcher profiles</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php if (!empty($success_message) || isset($_GET['deleted'])): ?>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-green-400 text-sm font-medium"><?php echo $success_message ?: 'Researcher deleted successfully!'; ?></p>
                </div>
            <?php endif; ?>

            <!-- Add Form -->
            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                <h3 class="text-white font-bold mb-4">Add New Researcher</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">First Name *</label>
                            <input type="text" name="first_name" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="First name">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Last Name *</label>
                            <input type="text" name="last_name" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Last name">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Email">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Phone</label>
                            <input type="tel" name="phone" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Phone">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Institution</label>
                            <input type="text" name="institution" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Institution">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Department</label>
                            <input type="text" name="department" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Department">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Research Area</label>
                            <input type="text" name="research_area" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="Research area">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Qualification</label>
                            <select name="qualification" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm">
                                <option value="Ph.D." class="bg-dark-800">Ph.D.</option>
                                <option value="M.Phil." class="bg-dark-800">M.Phil.</option>
                                <option value="Masters" class="bg-dark-800">Masters</option>
                                <option value="Bachelors" class="bg-dark-800">Bachelors</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Experience (Years)</label>
                            <input type="number" name="experience_years" min="0" value="0" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm">
                        </div>
                    </div>
                    <button type="submit" name="add_researcher" class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">Add Researcher</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/5">
                    <h3 class="text-white font-bold">All Researchers</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">ID</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Name</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Email</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Institution</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Research Area</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Exp</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $researchers->fetch_assoc()): ?>
                            <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo $r['researcher_id']; ?></td>
                                <td class="px-6 py-4 text-sm text-white font-medium"><?php echo clean($r['first_name'] . ' ' . $r['last_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean($r['email']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean($r['institution']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean($r['research_area']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo $r['experience_years']; ?>y</td>
                                <td class="px-6 py-4">
                                    <a href="?delete=<?php echo $r['researcher_id']; ?>" onclick="return confirm('Delete this researcher?')" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold hover:bg-red-500/30 transition-colors">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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