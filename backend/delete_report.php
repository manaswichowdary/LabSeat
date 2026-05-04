<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (! in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use DELETE (or POST) with JSON body or ?id= query string.']);
    exit;
}

$rawBody = file_get_contents('php://input') ?: '';
$payload = json_decode($rawBody, true);

$rawId = null;

if (is_array($payload) && array_key_exists('ReportID', $payload)) {
    $rawId = $payload['ReportID'];
} elseif (isset($_GET['id'])) {
    $rawId = $_GET['id'];
} elseif (isset($_GET['ReportID'])) {
    $rawId = $_GET['ReportID'];
}

if ($rawId === null || $rawId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing required field: ReportID.']);
    exit;
}

$reportId = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($reportId === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ReportID must be a positive integer.']);
    exit;
}

$sql = 'DELETE FROM AvailabilityReport WHERE ReportID = :report_id';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':report_id', $reportId, PDO::PARAM_INT);
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
        'message'      => "Report {$reportId} deleted successfully.",
        'ReportID'     => $reportId,
        'rowsAffected' => $rowsAffected,
    ]);
} catch (PDOException $e) {
    $sqlState = $e->getCode();

    if ($sqlState === '23503') {
        
        http_response_code(409);
        echo json_encode([
            'ok'    => false,
            'error' => "Cannot delete ReportID {$reportId} because a ModeratorAction row still references it. Remove the related moderator action first.",
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'ok'    => false,
            'error' => 'Database error while deleting report: ' . $e->getMessage(),
        ]);
    }
}
