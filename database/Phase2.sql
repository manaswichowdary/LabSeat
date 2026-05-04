CREATE TABLE "User" (
    UserID SERIAL PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'Student' CHECK (Role IN ('Student', 'Moderator')),
    TrustScore DECIMAL(3,2) DEFAULT 1.0
);

CREATE TABLE Building (
    BuildingID SERIAL PRIMARY KEY,
    Name VARCHAR(100) UNIQUE NOT NULL,
    CampusLocation VARCHAR(100)
);

CREATE TABLE Amenity (
    AmenityID SERIAL PRIMARY KEY,
    Name VARCHAR(100) UNIQUE NOT NULL,
    Description TEXT
);

CREATE TABLE Floor (
    FloorID SERIAL PRIMARY KEY,
    BuildingID INT NOT NULL REFERENCES Building(BuildingID),
    FloorNumber INT NOT NULL
);

CREATE TABLE Zone (
    ZoneID SERIAL PRIMARY KEY,
    FloorID INT NOT NULL REFERENCES Floor(FloorID),
    Name VARCHAR(100) NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE
);

CREATE TABLE ZoneAmenity (
    ZoneID INT REFERENCES Zone(ZoneID),
    AmenityID INT REFERENCES Amenity(AmenityID),
    PRIMARY KEY (ZoneID, AmenityID)
);

CREATE TABLE AvailabilityReport (
    ReportID SERIAL PRIMARY KEY,
    ZoneID INT NOT NULL REFERENCES Zone(ZoneID),
    UserID INT NOT NULL REFERENCES "User"(UserID),
    SeatAvailability VARCHAR(10) NOT NULL CHECK (SeatAvailability IN ('None','Low','Medium','High')),
    NoiseLevel VARCHAR(10) NOT NULL CHECK (NoiseLevel IN ('Quiet','Moderate','Loud')),
    OutletAvailability VARCHAR(10) NOT NULL CHECK (OutletAvailability IN ('None','Low','High')),
    CreatedAt TIMESTAMP NOT NULL,
    ExpiresAt TIMESTAMP NOT NULL,
    IsFlagged BOOLEAN DEFAULT FALSE
);

CREATE TABLE ModeratorAction (
    ActionID SERIAL PRIMARY KEY,
    ModeratorID INT NOT NULL REFERENCES "User"(UserID),
    ReportID INT REFERENCES AvailabilityReport(ReportID),
    TargetUserID INT REFERENCES "User"(UserID),
    ZoneID INT REFERENCES Zone(ZoneID),
    ActionType VARCHAR(20) NOT NULL CHECK (ActionType IN ('RemoveReport','VerifyReport','WarnUser','MarkZoneClosed')),
    ActionTimestamp TIMESTAMP NOT NULL,
    Notes TEXT
);

CREATE TABLE PredictionSnapshot (
    SnapshotID SERIAL PRIMARY KEY,
    ZoneID INT NOT NULL REFERENCES Zone(ZoneID),
    DayOfWeek VARCHAR(10) NOT NULL CHECK (DayOfWeek IN ('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')),
    TimeBlock VARCHAR(20) NOT NULL,
    AvgSeatAvailability VARCHAR(10) NOT NULL CHECK (AvgSeatAvailability IN ('None','Low','Medium','High')),
    AvgNoiseLevel VARCHAR(10) NOT NULL CHECK (AvgNoiseLevel IN ('Quiet','Moderate','Loud')),
    SampleCount INT DEFAULT 0,
    LastComputedAt TIMESTAMP NOT NULL,
    UNIQUE (ZoneID, DayOfWeek, TimeBlock)
);