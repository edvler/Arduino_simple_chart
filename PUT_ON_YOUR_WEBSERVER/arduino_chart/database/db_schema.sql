-- Project: Arduino simple Chart, Author: Matthias Maderer
-- This file is called from script db_config.php

-- You will find the schema under the MySQL folder of the GitHub Repository

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE  TABLE IF NOT EXISTS `Category` (
  `Category_ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Arduino_device_ID` INT(10) UNSIGNED NOT NULL ,
  `Category_NAME` TINYTEXT NOT NULL ,
  PRIMARY KEY (`Category_ID`) ,
  INDEX `fk_Category_Arduino_device1` (`Arduino_device_ID` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `DataStore` (
  `DataStore_ID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Sensor_ID` INT(10) UNSIGNED NOT NULL ,
  `DataStore_VALUE` FLOAT(12,5) NULL DEFAULT NULL ,
  `DataStore_TIME` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`DataStore_ID`) ,
  INDEX `DataStore_FKIndex1` (`Sensor_ID` ASC) ,
  INDEX `DataStore_TIME` (`DataStore_TIME` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 2515
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `Sensor` (
  `Sensor_ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Category_ID` INT(10) UNSIGNED NOT NULL ,
  `Sensor_NAME` TINYTEXT NULL DEFAULT NULL ,
  `Sensor_Color` TINYTEXT NULL DEFAULT NULL ,
  `Sensor_Order` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`Sensor_ID`) ,
  INDEX `Sensor_FKIndex1` (`Category_ID` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `DataStore_hour` (
  `DataStore_ID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Sensor_ID` INT(10) UNSIGNED NOT NULL ,
  `DataStore_VALUE` FLOAT(12,5) NULL DEFAULT NULL ,
  `DataStore_TIME` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`DataStore_ID`) ,
  INDEX `DataStore_FKIndex1` (`Sensor_ID` ASC) ,
  INDEX `DataStore_TIME` (`DataStore_TIME` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 2515
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `DataStore_day` (
  `DataStore_ID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Sensor_ID` INT(10) UNSIGNED NOT NULL ,
  `DataStore_VALUE` FLOAT(12,5) NULL DEFAULT NULL ,
  `DataStore_TIME` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`DataStore_ID`) ,
  INDEX `DataStore_FKIndex1` (`Sensor_ID` ASC) ,
  INDEX `DataStore_TIME` (`DataStore_TIME` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 2515
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `DataStore_week` (
  `DataStore_ID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `Sensor_ID` INT(10) UNSIGNED NOT NULL ,
  `DataStore_VALUE` FLOAT(12,5) NULL DEFAULT NULL ,
  `DataStore_TIME` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`DataStore_ID`) ,
  INDEX `DataStore_FKIndex1` (`Sensor_ID` ASC) ,
  INDEX `DataStore_TIME` (`DataStore_TIME` ASC) )
ENGINE = MyISAM
AUTO_INCREMENT = 2515
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

CREATE  TABLE IF NOT EXISTS `Arduino_device` (
  `Arduino_device_ID` INT(10) UNSIGNED NOT NULL ,
  `Arduino_device_Name` TINYTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`Arduino_device_ID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


-- -----------------------------------------------------
-- Placeholder table for view `DATA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DATA` (`Arduino_device_ID` INT, `Category_ID` INT, `Category_NAME` INT, `Sensor_ID` INT, `Sensor_NAME` INT, `Sensor_COLOR` INT, `DataStore_ID` INT, `DataStore_VALUE` INT, `DataStore_TIME` INT);

-- -----------------------------------------------------
-- Placeholder table for view `DATA_day`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DATA_day` (`Arduino_device_ID` INT, `Category_ID` INT, `Category_NAME` INT, `Sensor_ID` INT, `Sensor_NAME` INT, `Sensor_COLOR` INT, `DataStore_ID` INT, `DataStore_VALUE` INT, `DataStore_TIME` INT);

-- -----------------------------------------------------
-- Placeholder table for view `DATA_hour`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DATA_hour` (`Arduino_device_ID` INT, `Category_ID` INT, `Category_NAME` INT, `Sensor_ID` INT, `Sensor_NAME` INT, `Sensor_COLOR` INT, `DataStore_ID` INT, `DataStore_VALUE` INT, `DataStore_TIME` INT);

-- -----------------------------------------------------
-- Placeholder table for view `DATA_week`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `DATA_week` (`Arduino_device_ID` INT, `Category_ID` INT, `Category_NAME` INT, `Sensor_ID` INT, `Sensor_NAME` INT, `Sensor_COLOR` INT, `DataStore_ID` INT, `DataStore_VALUE` INT, `DataStore_TIME` INT);




-- -----------------------------------------------------
-- View `DATA`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `DATA`;

CREATE  OR REPLACE VIEW `DATA` AS
select 
Arduino_device.Arduino_device_ID,
Category.Category_ID,
Category_NAME,
Sensor.Sensor_ID,
Sensor.Sensor_NAME,
Sensor.Sensor_COLOR,
DataStore.DataStore_ID,
DataStore.DataStore_VALUE,
DataStore.DataStore_TIME
from
    Arduino_device
        inner join
    Category ON (Arduino_device.Arduino_device_ID = Category.Arduino_device_ID)
        inner join
    Sensor ON (Sensor.Category_ID = Category.Category_ID)
        inner join
    DataStore ON (DataStore.Sensor_ID = Sensor.Sensor_ID) order by DataStore_TIME desc;




-- -----------------------------------------------------
-- View `DATA_day`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `DATA_day`;

CREATE  OR REPLACE VIEW `DATA_day` AS
select 
Arduino_device.Arduino_device_ID,
Category.Category_ID,
Category_NAME,
Sensor.Sensor_ID,
Sensor.Sensor_NAME,
Sensor.Sensor_COLOR,
DataStore_ID,
DataStore_VALUE,
DataStore_TIME
from
    Arduino_device
        inner join
    Category ON (Arduino_device.Arduino_device_ID = Category.Arduino_device_ID)
        inner join
    Sensor ON (Sensor.Category_ID = Category.Category_ID)
        inner join
    DataStore_day ON (DataStore_day.Sensor_ID = Sensor.Sensor_ID) order by DataStore_TIME desc;




-- -----------------------------------------------------
-- View `DATA_hour`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `DATA_hour`;

CREATE  OR REPLACE VIEW `DATA_hour` AS
select 
Arduino_device.Arduino_device_ID,
Category.Category_ID,
Category_NAME,
Sensor.Sensor_ID,
Sensor.Sensor_NAME,
Sensor.Sensor_COLOR,
DataStore_ID,
DataStore_VALUE,
DataStore_TIME
from
    Arduino_device
        inner join
    Category ON (Arduino_device.Arduino_device_ID = Category.Arduino_device_ID)
        inner join
    Sensor ON (Sensor.Category_ID = Category.Category_ID)
        inner join
    DataStore_hour ON (DataStore_hour.Sensor_ID = Sensor.Sensor_ID) order by DataStore_TIME desc;




-- -----------------------------------------------------
-- View `DATA_week`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `DATA_week`;

CREATE  OR REPLACE VIEW `DATA_week` AS
select 
Arduino_device.Arduino_device_ID,
Category.Category_ID,
Category_NAME,
Sensor.Sensor_ID,
Sensor.Sensor_NAME,
Sensor.Sensor_COLOR,
DataStore_ID,
DataStore_VALUE,
DataStore_TIME
from
    Arduino_device
        inner join
    Category ON (Arduino_device.Arduino_device_ID = Category.Arduino_device_ID)
        inner join
    Sensor ON (Sensor.Category_ID = Category.Category_ID)
        inner join
    DataStore_week ON (DataStore_week.Sensor_ID = Sensor.Sensor_ID) order by DataStore_TIME desc;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

