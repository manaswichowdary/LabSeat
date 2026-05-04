<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use POST with a JSON body.']);
    exit;
}

$rawBody = file_get_contents('php://input') ?: '{}';
$payload = json_decode($rawBody, true);

if (! is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body.']);
    exit;
}

$required = ['ZoneID', 'UserID', 'SeatAvailability', 'NoiseLevel', 'OutletAvailability'];
foreach ($required as $field) {
    if (! array_key_exists($field, $payload) || $payload[$field] === '' || $payload[$field] === null) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => "Missing required field: {$field}."]);
        exit;
    }
}

$zoneId = filter_var($payload['ZoneID'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$userId = filter_var($payload['UserID'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($zoneId === false || $userId === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ZoneID and UserID must be positive integers.']);
    exit;
}

$allowedSeat   = ['None', 'Low', 'Medium', 'High'];
$allowedNoise  = ['Quiet', 'Moderate', 'Loud'];
$allowedOutlet = ['None', 'Low', 'High'];

$seat   = (string) $payload['SeatAvailability'];
$noise  = (string) $payload['NoiseLevel'];
$outlet = (string) $payload['OutletAvailability'];

if (! in_array($seat, $allowedSeat, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'SeatAvailability must be one of: ' . implode(', ', $allowedSeat) . '.']);
    exit;
}
if (! in_array($noise, $allowedNoise, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'NoiseLevel must be one of: ' . implode(', ', $allowedNoise) . '.']);
    exit;
}
if (! in_array($outlet, $allowedOutlet, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'OutletAvailability must be one of: ' . implode(', ', $allowedOutlet) . '.']);
    exit;
}

$sql = <<<'SQL'
INSERT INTO AvailabilityReport
  (ZoneID, UserID, SeatAvailability, NoiseLevel, OutletAvailability, CreatedAt, ExpiresAt, IsFlagged)
VALUES
  (:zone_id, :user_id, :seat, :noise, :outlet, NOW(), NOW() + INTERVAL '30 minutes', FALSE)
RETURNING ReportID
SQL;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':zone_id' => $zoneId,
        ':user_id' => $userId,
        ':seat'    => $seat,
        ':noise'   => $noise,
        ':outlet'  => $outlet,
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newReportId = $row['reportid'] ?? null;

    echo json_encode([
        'ok'       => true,
        'message'  => 'Report added successfully (ReportID: ' . (string) $newReportId . ').',
        'ReportID' => $newReportId,
    ]);
} catch (PDOException $e) {
    
    $sqlState = $e->getCode();
    http_response_code(400);

    if ($sqlState === '23503') {
        echo json_encode([
            'ok'    => false,
            'error' => 'ZoneID or UserID does not exist in the database.',
        ]);
    } else {
        echo json_encode([
            'ok'    => false,
            'error' => 'Database error while inserting report: ' . $e->getMessage(),
        ]);
    }
}
