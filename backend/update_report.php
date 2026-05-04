<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (! in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use PUT, PATCH, or POST with a JSON body.']);
    exit;
}

$rawBody = file_get_contents('php://input') ?: '{}';
$payload = json_decode($rawBody, true);

if (! is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body.']);
    exit;
}

if (! array_key_exists('ReportID', $payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing required field: ReportID.']);
    exit;
}

$reportId = filter_var($payload['ReportID'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($reportId === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ReportID must be a positive integer.']);
    exit;
}

$required = ['SeatAvailability', 'NoiseLevel', 'OutletAvailability', 'IsFlagged'];
foreach ($required as $field) {
    if (! array_key_exists($field, $payload) || $payload[$field] === '' || $payload[$field] === null) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => "Missing required field: {$field}."]);
        exit;
    }
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

$rawFlag = $payload['IsFlagged'];
if (is_bool($rawFlag)) {
    $isFlagged = $rawFlag;
} elseif (is_string($rawFlag)) {
    $low = strtolower($rawFlag);
    if (in_array($low, ['true', '1', 't', 'yes'], true)) {
        $isFlagged = true;
    } elseif (in_array($low, ['false', '0', 'f', 'no'], true)) {
        $isFlagged = false;
    } else {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'IsFlagged must be true or false.']);
        exit;
    }
} elseif (is_int($rawFlag)) {
    $isFlagged = $rawFlag === 1;
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'IsFlagged must be true or false.']);
    exit;
}

$sql = <<<'SQL'
UPDATE AvailabilityReport
SET
  SeatAvailability   = :seat,
  NoiseLevel         = :noise,
  OutletAvailability = :outlet,
  IsFlagged          = :is_flagged
WHERE ReportID = :report_id
SQL;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':seat',       $seat,                    PDO::PARAM_STR);
    $stmt->bindValue(':noise',      $noise,                   PDO::PARAM_STR);
    $stmt->bindValue(':outlet',     $outlet,                  PDO::PARAM_STR);
    $stmt->bindValue(':is_flagged', $isFlagged,               PDO::PARAM_BOOL);
    $stmt->bindValue(':report_id',  $reportId,                PDO::PARAM_INT);
    $stmt->execute();

    $rowsAffected = $stmt->rowCount();

    if ($rowsAffected === 0) {
        http_response_code(404);
        echo json_encode([
            'ok'    => false,
            'error' => "No report found with ReportID {$reportId}.",
        ]);
        exit;
    }

    echo json_encode([
        'ok'           => true,
        'message'      => "Report {$reportId} updated successfully.",
        'ReportID'     => $reportId,
        'rowsAffected' => $rowsAffected,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Database error while updating report: ' . $e->getMessage(),
    ]);
}
