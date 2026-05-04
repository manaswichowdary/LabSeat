-- 1. Count total reports per building
SELECT 
    b.Name AS BuildingName,
    COUNT(ar.ReportID) AS TotalReports
FROM Building b
JOIN Floor f ON b.BuildingID = f.BuildingID
JOIN Zone z ON f.FloorID = z.FloorID
LEFT JOIN AvailabilityReport ar ON z.ZoneID = ar.ZoneID
GROUP BY b.Name
ORDER BY TotalReports DESC;


-- 2. Count total reports per zone
SELECT 
    z.Name AS ZoneName,
    COUNT(ar.ReportID) AS ReportCount
FROM Zone z
LEFT JOIN AvailabilityReport ar ON z.ZoneID = ar.ZoneID
GROUP BY z.Name
ORDER BY ReportCount DESC;


-- 3. Count reports by noise level
SELECT 
    NoiseLevel,
    COUNT(*) AS TotalReports
FROM AvailabilityReport
GROUP BY NoiseLevel
ORDER BY TotalReports DESC;


-- 4. Count flagged vs non-flagged reports
SELECT 
    IsFlagged,
    COUNT(*) AS ReportCount
FROM AvailabilityReport
GROUP BY IsFlagged;


-- 5. Count zones per building
SELECT 
    b.Name AS BuildingName,
    COUNT(z.ZoneID) AS TotalZones
FROM Building b
JOIN Floor f ON b.BuildingID = f.BuildingID
JOIN Zone z ON f.FloorID = z.FloorID
GROUP BY b.Name
ORDER BY TotalZones DESC;


-- 6. Average trust score by user role
SELECT 
    Role,
    ROUND(AVG(TrustScore), 2) AS AverageTrustScore,
    COUNT(UserID) AS TotalUsers
FROM "User"
GROUP BY Role;