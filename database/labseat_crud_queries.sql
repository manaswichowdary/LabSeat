-- LABSEAT CRUD QUERIES
-- These queries demonstrate Create, Read, Update, and Delete operations.

-- CREATE: Insert a new availability report
INSERT INTO AvailabilityReport
(ZoneID, UserID, SeatAvailability, NoiseLevel, OutletAvailability, CreatedAt, ExpiresAt, IsFlagged)
VALUES
(1, 1, 'Medium', 'Quiet', 'High', NOW(), NOW() + INTERVAL '30 minutes', FALSE)
RETURNING *;


-- READ: View live availability reports with building, floor, zone, and user info
SELECT 
    ar.ReportID,
    b.Name AS BuildingName,
    f.FloorNumber,
    z.Name AS ZoneName,
    u.Name AS UserName,
    ar.SeatAvailability,
    ar.NoiseLevel,
    ar.OutletAvailability,
    ar.CreatedAt,
    ar.ExpiresAt,
    ar.IsFlagged
FROM AvailabilityReport ar
JOIN Zone z ON ar.ZoneID = z.ZoneID
JOIN Floor f ON z.FloorID = f.FloorID
JOIN Building b ON f.BuildingID = b.BuildingID
JOIN "User" u ON ar.UserID = u.UserID
ORDER BY ar.CreatedAt DESC;


-- UPDATE: Update the newest availability report
UPDATE AvailabilityReport
SET 
    SeatAvailability = 'High',
    NoiseLevel = 'Moderate',
    OutletAvailability = 'High',
    IsFlagged = FALSE
WHERE ReportID = (
    SELECT MAX(ReportID) FROM AvailabilityReport
)
RETURNING *;


-- DELETE: Delete the newest availability report
DELETE FROM AvailabilityReport
WHERE ReportID = (
    SELECT MAX(ReportID) FROM AvailabilityReport
)
RETURNING *;