SET sql_mode = '';

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
        ON DELETE CASCADE
);

CREATE TABLE ChargeCard (
    cardNum BIGINT(19) UNIQUE NOT NULL,
    memID INT(9) NOT NULL,
    cardCvv INT(3) NOT NULL,
    cardExpDate DATE NOT NULL,
    cardFname CHAR(64) NOT NULL,
    cardMinitial CHAR(1) NOT NULL,
    cardLname CHAR(64) NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (cardNum),
    FOREIGN KEY (memID)
        REFERENCES Member (memID)
        ON DELETE CASCADE
);

CREATE TABLE RoomType (
    roomTypeID INT(4) UNIQUE NOT NULL AUTO_INCREMENT,
    pricePerNight DECIMAL(6 , 2 ) NOT NULL,
    roomCatagory SET('normal', 'pet', 'gaming', 'family', 'chef') NOT NULL DEFAULT 'normal',
    numOfRooms INT(4) NOT NULL DEFAULT 1,
    roomNumBeds INT(1) NOT NULL DEFAULT 1,
    roomAllowsPets TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (roomTypeID)
);


CREATE TABLE InvoiceReservation (
    invoiceID INT(20) UNIQUE AUTO_INCREMENT NOT NULL,
    cardNum BIGINT(19) NOT NULL,
    memID INT(9) NOT NULL,
    roomTypeID INT(7) NOT NULL,
    invoiceStartDate DATE NOT NULL,
    invoiceEndDate DATE NOT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (invoiceID),
    FOREIGN KEY (cardNum)
        REFERENCES ChargeCard (cardNum)
        ON DELETE CASCADE,
    FOREIGN KEY (memID)
        REFERENCES Member (memID)
        ON DELETE CASCADE,
    FOREIGN KEY (roomTypeID)
        REFERENCES RoomType (roomTypeID)
        ON DELETE CASCADE
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
INSERT INTO RoomType(pricePerNight, roomCatagory, roomNumBeds, roomAllowsPets)
	VALUES 
    ('100.00','normal',1,0),
    ('125.00','normal',2,0),
    ('150.00','pet',1,1),
    ('175.00','pet',2,1),
    ('1020.00','gaming',1,0),
    ('1050.00','gaming',2,0),
    ('200.00','family',2,0),
    ('220.00','family',3,0),
    ('250.00','family',2,1),
    ('270.00','family',3,1),
    ('300.00','chef',1,0),
    ('325.00','chef',2,0);
    
INSERT INTO Member (memID, memEmail, memPasswd, memFname, memLname, memDob, memPhone, memRewardPoints, memActivationLink, isMember)
	VALUES 
	('1', 'Joe@gmail.com', 'c7b0f1b6ac6cfff2062f0ce4d7d9d96e4a6e49cb602732ef5c6b84ad725ae89d', 'Joe', 'Smith', '1983-09-23', '1234567890', '0', '807453141', '1');
    
INSERT INTO Address (addressID, memID, addressType, addressBuildNum, addressStreetName, addressCity, addressZip, addressProvence, addressCountry, addressAptNum)
	VALUES
    ('1', '1', 'mailing', '1234', 'Main St.', 'St. Paul', '12345', 'MN', 'United States', '14'),
    ('2', '1', 'billing', '1234', 'Main St.', 'St. Paul', '12345', 'MN', 'United States', '14');
    
INSERT INTO ChargeCard (cardNum, memID, cardCvv, cardExpDate, cardFname, cardMinitial, cardLname)
	VALUES
    ('5241766314993654', '1', '676', '2022-09-23', 'Joe', 'A', 'Smith'),
    ('5486712187282316', '1', '578', '2021-12-22', 'Fred', 'F', 'Flintstone');
    
INSERT INTO InvoiceReservation (cardNum, memID, invoiceStartDate, invoiceEndDate, roomTypeID)
	VALUES
    ('5241766314993654', '1', '2019-04-15', '2019-04-22', '1'),
    ('5241766314993654', '1', '2019-05-23', '2019-05-28', '1'),
    ('5486712187282316', '1', '2019-04-23', '2019-05-17', '5');

