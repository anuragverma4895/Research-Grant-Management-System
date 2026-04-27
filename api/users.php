<?php
/**
 * ============================================
 * REST API - Users Endpoint
 * GET /api/users.php - List users/researchers
 * ============================================
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config.php';
require_once '../db_connection.php';

function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$type = $_GET['type'] ?? 'researchers'; // researchers or agencies
$limit = min(intval($_GET['limit'] ?? 50), 100);

if ($type === 'researchers') {
    $result = $conn->query("SELECT researcher_id, first_name, last_name, email, institution, department, research_area, qualification, experience_years FROM Researchers ORDER BY created_at DESC LIMIT $limit");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => (int)$row['researcher_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'institution' => $row['institution'],
            'department' => $row['department'],
            'research_area' => $row['research_area'],
            'qualification' => $row['qualification'],
            'experience_years' => (int)$row['experience_years']
        ];
    }

    sendJSON([
        'success' => true,
        'type' => 'researchers',
        'data' => $data,
        'total' => count($data),
        'api_version' => APP_VERSION
    ]);

} elseif ($type === 'agencies') {
    $result = $conn->query("SELECT funding_agency_id, agency_name, contact_email, funding_area, agency_type, website, total_budget FROM Funding_Agencies ORDER BY created_at DESC LIMIT $limit");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => (int)$row['funding_agency_id'],
            'name' => $row['agency_name'],
            'email' => $row['contact_email'],
            'funding_area' => $row['funding_area'],
            'type' => $row['agency_type'],
            'website' => $row['website'],
            'budget' => (float)$row['total_budget']
        ];
    }

    sendJSON([
        'success' => true,
        'type' => 'agencies',
        'data' => $data,
        'total' => count($data),
        'api_version' => APP_VERSION
    ]);
} else {
    sendJSON(['success' => false, 'error' => 'Invalid type. Use: researchers or agencies'], 400);
}
?>
