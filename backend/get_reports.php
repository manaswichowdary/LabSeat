<?php
header('Content-Type: application/json');

$reports = [
    [
        'ReportID'           => 1,
        'BuildingName'       => 'Hayden Library',
        'FloorNumber'        => 1,
        'ZoneName'           => 'Hayden First Floor North',
        'UserName'           => 'Krutika Anaganti',
        'SeatAvailability'   => 'High',
        'NoiseLevel'         => 'Moderate',
        'OutletAvailability' => 'High',
        'CreatedAt'          => '2026-05-03 10:00:00',
        'ExpiresAt'          => '2026-05-03 10:30:00',
        'IsFlagged'          => false,
    ],
    [
        'ReportID'           => 2,
        'BuildingName'       => 'Noble Library',
        'FloorNumber'        => 2,
        'ZoneName'           => 'Noble Silent Zone',
        'UserName'           => 'Diya Shrivastava',
        'SeatAvailability'   => 'Low',
        'NoiseLevel'         => 'Quiet',
        'OutletAvailability' => 'High',
        'CreatedAt'          => '2026-05-03 09:45:00',
        'ExpiresAt'          => '2026-05-03 10:15:00',
        'IsFlagged'          => false,
    ],
    [
        'ReportID'           => 3,
        'BuildingName'       => 'Memorial Union',
        'FloorNumber'        => 1,
        'ZoneName'           => 'MU Lounge Seating',
        'UserName'           => 'Harini Sekar',
        'SeatAvailability'   => 'Low',
        'NoiseLevel'         => 'Loud',
        'OutletAvailability' => 'None',
        'CreatedAt'          => '2026-05-03 09:30:00',
        'ExpiresAt'          => '2026-05-03 10:00:00',
        'IsFlagged'          => true,
    ],
    [
        'ReportID'           => 4,
        'BuildingName'       => 'Engineering Center',
        'FloorNumber'        => 1,
        'ZoneName'           => 'Engineering Study Hall',
        'UserName'           => 'Aung Ko Ko',
        'SeatAvailability'   => 'Medium',
        'NoiseLevel'         => 'Moderate',
        'OutletAvailability' => 'High',
        'CreatedAt'          => '2026-05-03 09:15:00',
        'ExpiresAt'          => '2026-05-03 09:45:00',
        'IsFlagged'          => false,
    ],
];

echo json_encode($reports);
