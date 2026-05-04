<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sqlSelect = <<<'SQL'
SELECT
  ar.reportid AS "ReportID",
  b.name AS "BuildingName",
  f.floornumber AS "FloorNumber",
  z.name AS "ZoneName",
  u.name AS "UserName",
  ar.seatavailability AS "SeatAvailability",
  ar.noiselevel AS "NoiseLevel",
  ar.outletavailability AS "OutletAvailability",
  ar.createdat AS "CreatedAt",
  ar.expiresat AS "ExpiresAt",
  ar.isflagged AS "IsFlagged"
FROM availabilityreport ar
INNER JOIN zone z ON ar.zoneid = z.zoneid
INNER JOIN floor f ON z.floorid = f.floorid
INNER JOIN building b ON f.buildingid = b.buildingid
INNER JOIN "User" u ON ar.userid = u.userid
SQL;

try {
    $sqlActive = $sqlSelect . "\nWHERE ar.expiresat > NOW()\nORDER BY ar.createdat DESC\n";
    $stmt = $pdo->query($sqlActive);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($reports) === 0) {
        $sqlAll = $sqlSelect . "\nORDER BY ar.createdat DESC\n";
        $stmt = $pdo->query($sqlAll);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'reports' => $reports,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
