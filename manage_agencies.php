<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "functions.php";

$success_message = "";

if (isset($_POST['add_agency'])) {
    $agency_name = sanitize($_POST['agency_name']);
    $contact_email = sanitize($_POST['contact_email']);
    $contact_phone = sanitize($_POST['contact_phone']);
    $address = sanitize($_POST['address']);
    $funding_area = sanitize($_POST['funding_area']);
    $agency_type = $_POST['agency_type'];
    $website = sanitize($_POST['website']);
    $total_budget = floatval($_POST['total_budget']);
    
    $stmt = $conn->prepare("INSERT INTO Funding_Agencies (agency_name, contact_email, contact_phone, address, funding_area, agency_type, website, total_budget) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssd", $agency_name, $contact_email, $contact_phone, $address, $funding_area, $agency_type, $website, $total_budget);
    
    if ($stmt->execute()) {
        $success_message = "Funding agency added successfully!";
    }
}

if (isset($_GET['delete'])) {
    $agency_id = intval($_GET['delete']);
    $conn->query("DELETE FROM Funding_Agencies WHERE funding_agency_id = $agency_id");
    header("Location: manage_agencies.php?deleted=1");
    exit;
}

$agencies = $conn->query("SELECT * FROM Funding_Agencies ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Manage Agencies'); ?>
</head>
<body class="font-inter bg-dark-900 text-white min-h-screen">
    <?php echo renderSidebar('admin', 'agencies'); ?>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-dark-800/50 backdrop-blur-xl border-b border-white/5 px-6 py-4 sticky top-0 z-30">
            <div>
                <h2 class="text-xl font-bold text-white">Manage Funding Agencies</h2>
                <p class="text-gray-500 text-sm">Add and manage funding organizations</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php if (!empty($success_message) || isset($_GET['deleted'])): ?>
                <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-green-400 text-sm font-medium"><?php echo $success_message ?: 'Agency deleted!'; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6">
                <h3 class="text-white font-bold mb-4">Add New Agency</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Agency Name *</label>
                            <input type="text" name="agency_name" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Agency Type</label>
                            <select name="agency_type" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm">
                                <option value="Government" class="bg-dark-800">Government</option>
                                <option value="Private" class="bg-dark-800">Private</option>
                                <option value="NGO" class="bg-dark-800">NGO</option>
                                <option value="International" class="bg-dark-800">International</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                            <input type="email" name="contact_email" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Phone</label>
                            <input type="tel" name="contact_phone" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Funding Area</label>
                            <input type="text" name="funding_area" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="e.g., Science">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Website</label>
                            <input type="url" name="website" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm" placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Budget (₹)</label>
                            <input type="number" name="total_budget" step="0.01" min="0" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-primary-500 focus:outline-none text-sm">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Address</label>
                        <textarea name="address" rows="2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none text-sm resize-y"></textarea>
                    </div>
                    <button type="submit" name="add_agency" class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">Add Agency</button>
                </form>
            </div>

            <div class="bg-dark-800/50 backdrop-blur-xl border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/5"><h3 class="text-white font-bold">All Agencies</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">ID</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Name</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Type</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Area</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Budget</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Contact</th>
                                <th class="text-left px-6 py-4 text-gray-400 text-xs font-semibold uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($a = $agencies->fetch_assoc()): ?>
                            <tr class="border-b border-white/5 hover:bg-white/[0.02]">
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo $a['funding_agency_id']; ?></td>
                                <td class="px-6 py-4 text-sm text-white font-medium"><?php echo clean($a['agency_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo $a['agency_type']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean($a['funding_area']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo formatCurrency($a['total_budget']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo clean($a['contact_email']); ?></td>
                                <td class="px-6 py-4">
                                    <a href="?delete=<?php echo $a['funding_agency_id']; ?>" onclick="return confirm('Delete?')" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold hover:bg-red-500/30">Delete</a>
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