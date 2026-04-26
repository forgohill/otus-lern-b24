CREATE TABLE IF NOT EXISTS `otus_titanic_passenger_cabin` (
    `PASSENGER_ID` INT UNSIGNED NOT NULL COMMENT 'ID пассажира из otus_titanic_passengers',
    `CABIN_ID` INT UNSIGNED NOT NULL COMMENT 'ID каюты из otus_titanic_cabins',

    PRIMARY KEY (`PASSENGER_ID`, `CABIN_ID`),

    KEY `IX_CABIN_ID` (`CABIN_ID`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;