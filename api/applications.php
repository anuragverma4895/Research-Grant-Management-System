<?php
/**
 * ============================================
 * REST API - Applications Endpoint
 * GET /api/applications.php - List all applications
 * POST /api/applications.php - Create new application
 * ============================================
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config.php';
require_once '../db_connection.php';

// Simple API Key Auth
$headers = getallheaders();
$api_key = $headers['Authorization'] ?? $headers['authorization'] ?? ($_GET['api_key'] ?? '');

function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // GET: List applications with optional filters
    $status = $_GET['status'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 50), 100);
    $offset = max(intval($_GET['offset'] ?? 0), 0);

    $where = '';
    $params = [];
    $types = '';

    if ($status && in_array($status, ALLOWED_STATUSES)) {
        $where = 'WHERE ga.application_status = ?';
        $params[] = $status;
        $types .= 's';
    }

    $query = "SELECT ga.application_id, ga.grant_title, ga.grant_description, ga.grant_amount_requested, 
                     ga.application_status, ga.submission_date, ga.project_duration_months, ga.priority_level,
                     r.first_name, r.last_name, r.email, r.institution,
                     fa.agency_name
              FROM Grant_Applications ga
              JOIN Researchers r ON ga.researcher_id = r.researcher_id
              JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id
              $where
              ORDER BY ga.submission_date DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = [
            'id' => (int)$row['application_id'],
            'title' => $row['grant_title'],
            'description' => $row['grant_description'],
            'amount_requested' => (float)$row['grant_amount_requested'],
            'status' => $row['application_status'],
            'submission_date' => $row['submission_date'],
            'duration_months' => (int)$row['project_duration_months'],
            'priority' => $row['priority_level'],
            'researcher' => [
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'email' => $row['email'],
                'institution' => $row['institution']
            ],
            'agency' => $row['agency_name']
        ];
    }

    // Total count
    $count_query = "SELECT COUNT(*) as total FROM Grant_Applications ga $where";
    if ($status && in_array($status, ALLOWED_STATUSES)) {
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->bind_param('s', $status);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications")->fetch_assoc()['total'];
    }

    sendJSON([
        'success' => true,
        'data' => $applications,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'returned' => count($applications)
        ],
        'api_version' => APP_VERSION
    ]);

} elseif ($method === 'POST') {
    // POST: Create new application (requires API key)
    if ($api_key !== API_KEY) {
        sendJSON(['success' => false, 'error' => 'Unauthorized. Valid API key required.'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJSON(['success' => false, 'error' => 'Invalid JSON input.'], 400);
    }

    $required = ['researcher_id', 'grant_title', 'grant_description', 'grant_amount_requested', 'funding_agency_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            sendJSON(['success' => false, 'error' => "Missing required field: $field"], 400);
        }
    }

    $stmt = $conn->prepare("INSERT INTO Grant_Applications (researcher_id, grant_title, grant_description, grant_amount_requested, funding_agency_id, submission_date) VALUES (?, ?, ?, ?, ?, CURDATE())");
    $stmt->bind_param("issdi", 
        $input['researcher_id'],
        $input['grant_title'],
        $input['grant_description'],
        $input['grant_amount_requested'],
        $input['funding_agency_id']
    );

    if ($stmt->execute()) {
        sendJSON([
            'success' => true,
            'message' => 'Application created successfully.',
            'application_id' => $stmt->insert_id
        ], 201);
    } else {
        sendJSON(['success' => false, 'error' => 'Failed to create application.'], 500);
    }

} else {
    sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}
?>
