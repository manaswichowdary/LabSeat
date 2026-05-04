-- SAMPLE DATA FOR LABSEAT

INSERT INTO "User" (Name, Email, Password, Role, TrustScore) VALUES
('Krutika Anaganti', 'krutika@asu.edu', 'password123', 'Student', 1.00),
('Diya Shrivastava', 'diya@asu.edu', 'password123', 'Student', 0.95),
('Harini Sekar', 'harini@asu.edu', 'password123', 'Student', 0.90),
('Aung Ko Ko', 'aung@asu.edu', 'password123', 'Student', 0.85),
('Maya Moderator', 'maya@asu.edu', 'password123', 'Moderator', 1.00);

INSERT INTO Building (Name, CampusLocation) VALUES
('Hayden Library', 'Tempe Campus'),
('Noble Library', 'Tempe Campus'),
('Memorial Union', 'Tempe Campus'),
('Engineering Center', 'Tempe Campus');

INSERT INTO Amenity (Name, Description) VALUES
('Outlets', 'Charging outlets available'),
('Whiteboards', 'Whiteboards for group study'),
('Quiet Area', 'Low-noise study area'),
('Printers', 'Nearby printing access'),
('Zoom Friendly', 'Good space for online meetings');

INSERT INTO Floor (BuildingID, FloorNumber) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1);

INSERT INTO Zone (FloorID, Name, IsActive) VALUES
(1, 'Hayden First Floor North', TRUE),
(2, 'Hayden Quiet Study', TRUE),
(3, 'Noble Group Study Area', TRUE),
(4, 'Noble Silent Zone', TRUE),
(5, 'MU Lounge Seating', TRUE),
(6, 'Engineering Study Hall', TRUE);

INSERT INTO ZoneAmenity (ZoneID, AmenityID) VALUES
(1, 1), (1, 5),
(2, 1), (2, 3),
(3, 1), (3, 2),
(4, 3),
(5, 1), (5, 4),
(6, 1), (6, 2), (6, 5);

INSERT INTO AvailabilityReport
(ZoneID, UserID, SeatAvailability, NoiseLevel, OutletAvailability, CreatedAt, ExpiresAt, IsFlagged)
VALUES
(1, 1, 'High', 'Moderate', 'High', NOW() - INTERVAL '5 minutes', NOW() + INTERVAL '25 minutes', FALSE),
(1, 2, 'Medium', 'Moderate', 'High', NOW() - INTERVAL '15 minutes', NOW() + INTERVAL '15 minutes', FALSE),
(2, 3, 'Low', 'Quiet', 'High', NOW() - INTERVAL '8 minutes', NOW() + INTERVAL '22 minutes', FALSE),
(3, 4, 'Medium', 'Loud', 'Low', NOW() - INTERVAL '12 minutes', NOW() + INTERVAL '18 minutes', FALSE),
(4, 1, 'High', 'Quiet', 'Low', NOW() - INTERVAL '3 minutes', NOW() + INTERVAL '27 minutes', FALSE),
(5, 2, 'Low', 'Loud', 'None', NOW() - INTERVAL '20 minutes', NOW() + INTERVAL '10 minutes', TRUE),
(6, 3, 'Medium', 'Moderate', 'High', NOW() - INTERVAL '6 minutes', NOW() + INTERVAL '24 minutes', FALSE),
(6, 4, 'High', 'Quiet', 'High', NOW() - INTERVAL '35 minutes', NOW() - INTERVAL '5 minutes', FALSE);

INSERT INTO ModeratorAction
(ModeratorID, ReportID, TargetUserID, ZoneID, ActionType, ActionTimestamp, Notes)
VALUES
(5, 6, 2, 5, 'VerifyReport', NOW() - INTERVAL '10 minutes', 'Report was checked and marked valid.'),
(5, NULL, NULL, 4, 'MarkZoneClosed', NOW() - INTERVAL '1 day', 'Zone temporarily closed for cleaning.');

INSERT INTO PredictionSnapshot
(ZoneID, DayOfWeek, TimeBlock, AvgSeatAvailability, AvgNoiseLevel, SampleCount, LastComputedAt)
VALUES
(1, 'Monday', '10:00-11:00', 'Medium', 'Moderate', 12, NOW()),
(2, 'Monday', '10:00-11:00', 'Low', 'Quiet', 15, NOW()),
(3, 'Tuesday', '12:00-13:00', 'Medium', 'Loud', 9, NOW()),
(4, 'Wednesday', '14:00-15:00', 'High', 'Quiet', 18, NOW()),
(5, 'Thursday', '15:00-16:00', 'Low', 'Loud', 7, NOW()),
(6, 'Friday', '11:00-12:00', 'Medium', 'Moderate', 11, NOW());