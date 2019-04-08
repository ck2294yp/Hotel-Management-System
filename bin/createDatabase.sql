DROP DATABASE IF EXISTS tci;
CREATE DATABASE tci; 

USE tci;


CREATE TABLE FailedLogins (
    failLoginID INT(10) UNIQUE NOT NULL AUTO_INCREMENT,
    failLoginIP VARCHAR(64) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (failLoginID)
);
    
CREATE TABLE Member (
    memID INT(9) UNIQUE NOT NULL AUTO_INCREMENT,
    memEmail VARCHAR(255) UNIQUE NOT NULL,
    memPasswd VARCHAR(255) NOT NULL,
    memFname CHAR(64) NOT NULL,
    memLname CHAR(64) NOT NULL,
    memDob DATE NOT NULL,
    memPhone INT(11),
    memRewardPoints INT(9) NOT NULL DEFAULT 0,
    memActivationLink VARCHAR(64),
    isMember TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (memID)
);

CREATE TABLE Address (
    addressID INT(9) UNIQUE AUTO_INCREMENT NOT NULL,
    memID INT(9) NOT NULL,
    addressType SET('mailing', 'billing') NOT NULL,
    addressBuildNum INT(8) NOT NULL,
    addressStreetName CHAR(64) NOT NULL,
    addressCity CHAR(64) NOT NULL,
    addressZip INT(7) NOT NULL,
    addressProvence CHAR(32) NOT NULL,
    addressCountry CHAR(64) NOT NULL,
    addressAptNum VARCHAR(7) DEFAULT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (addressID),
    FOREIGN KEY (memID)
        REFERENCES Member (memID)
);

CREATE TABLE ChargeCard (
    cardNum INT(19) UNIQUE NOT NULL,
    memID INT(9) NOT NULL,
    cardExpDate DATE NOT NULL,
    cardFname CHAR(64) NOT NULL,
    cardMinitial CHAR(1) NOT NULL,
    cardLname CHAR(64) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (cardNum),
    FOREIGN KEY (memID)
        REFERENCES Member (memID)
);

CREATE TABLE Rate (
    rateID INT(4) UNIQUE AUTO_INCREMENT NOT NULL,
    centsPerNight INT(8) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rateID)
);

CREATE TABLE RoomType (
    roomTypeID INT(4) UNIQUE NOT NULL AUTO_INCREMENT,
    roomCatagory SET('normal', 'pet', 'gaming', 'family', 'chef') NOT NULL DEFAULT 'normal',
    roomNumBeds INT(1) NOT NULL DEFAULT 0,
    roomAllowsPets TINYINT(1) NOT NULL DEFAULT 0,
    rateID INT(4) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (roomTypeID),
    FOREIGN KEY (rateID)
        REFERENCES Rate (rateID)
);
    
CREATE TABLE Room (
    roomNum INT(7) UNIQUE NOT NULL,
    roomTypeID INT(7) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (roomNum),
    FOREIGN KEY (roomTypeID)
        REFERENCES RoomType (roomTypeID)
        ON UPDATE CASCADE
);

CREATE TABLE InvoiceReservation (
    invoiceID INT(20) UNIQUE AUTO_INCREMENT NOT NULL,
    cardNum INT(19) NOT NULL,
    memID INT(9) NOT NULL,
    invoiceStartDate DATE NOT NULL,
    invoiceEndDate DATE NOT NULL,
    roomNum INT(7) NOT NULL,
    paidInFull TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (invoiceID),
    FOREIGN KEY (cardNum)
        REFERENCES ChargeCard (cardNum),
    FOREIGN KEY (memID)
        REFERENCES Member (memID),
    FOREIGN KEY (roomNum)
        REFERENCES Room (roomNum)
);

-- Creates the user that the website will use to access the database 
-- 	(make shure to change the IP address of this user from "%" to whatever the internel
--  IP address is for the web server).
-- DROP USER 'TciWebsite'@'%';
-- FLUSH PRIVILEGES;
-- CREATE USER 'TciWebsite'@'%' IDENTIFIED by '7gj35deM7rNR#y9*D57&';
-- FLUSH PRIVILEGES;
-- GRANT SELECT,UPDATE,DROP,INSERT,LOCK TABLES,ALTER ON tci.* TO 'TciWebsite'@'%';
-- FLUSH PRIVILEGES;


-- Creates some example rooms, roomRates, and RoomTypes to be imported into the database.
INSERT INTO Rate (centsPerNight)
	VALUES 
    ('10000'),
    ('12500'),
    ('15000'),
    ('17500'),
    ('102000'),
    ('105000'),
    ('20000'),
    ('22000'),
    ('25000'),
    ('27000'),
    ('30000'),
    ('32500');

INSERT INTO RoomType (roomCatagory, roomNumBeds, roomAllowsPets, rateID)
	VALUES
    ('normal',1,0,1),
    ('normal',2,0,2),
    ('pet',1,1,2),
    ('pet',2,1,2),
    ('gaming',1,0,5),
    ('gaming',2,0,6),
    ('family',2,0,7),
    ('family',3,0,8),
    ('family',2,1,9),
    ('family',3,1,10),
    ('chef',1,0,11),
    ('chef',2,0,12);

INSERT INTO Room (RoomNum, RoomTypeID)
VALUES
	(124, 1),
    (225,3),
    (250,4),
    (550,5),
    (551,5),
    (552,5),
    (553,5),
    (554,6),
    (555,6),
    (312,7),
    (313,7),
    (317,7),
    (352,8),
    (412,9),
    (434,9),
    (343,9),
	(453,10),
	(454,10),
	(455,10),
	(460,11),
	(462,11),
	(467,12),
	(475,12),
	(476,12);