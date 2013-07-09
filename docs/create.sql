SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `sigmat` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `sigmat`;

-- -----------------------------------------------------
-- Table `sigmat`.`agencies`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sigmat`.`agencies` ;

CREATE  TABLE IF NOT EXISTS `sigmat`.`agencies` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NOT NULL ,
  `acronym` VARCHAR(50) NOT NULL ,
  `contact` VARCHAR(200) NULL ,
  `phone` VARCHAR(15) NULL ,
  `email` VARCHAR(250) NULL ,
  `status` BOOLEAN NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sigmat`.`logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sigmat`.`logs` ;

CREATE  TABLE IF NOT EXISTS `sigmat`.`logs` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `description` TEXT NOT NULL ,
  `event` VARCHAR(45) NOT NULL ,
  `severity` INT NOT NULL ,
  `created` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sigmat`.`logs_context`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sigmat`.`logs_context` ;

CREATE  TABLE IF NOT EXISTS `sigmat`.`logs_context` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `log_id` INT NOT NULL ,
  `key` VARCHAR(100) NOT NULL ,
  `value` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_logs_context_logs` (`log_id` ASC) ,
  CONSTRAINT `fk_logs_context_logs`
    FOREIGN KEY (`log_id` )
    REFERENCES `sigmat`.`logs` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sigmat`.`administrative_units`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sigmat`.`administrative_units` ;

CREATE  TABLE IF NOT EXISTS `sigmat`.`administrative_units` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NOT NULL ,
  `status` BOOLEAN NOT NULL DEFAULT 1 ,
  `agency_id` INT NOT NULL ,
  `parent_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_units_administratives_agencies` (`agency_id` ASC) ,
  INDEX `fk_administrative_units_administrative_units` (`parent_id` ASC) ,
  CONSTRAINT `fk_units_administratives_agencies`
    FOREIGN KEY (`agency_id` )
    REFERENCES `sigmat`.`agencies` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_administrative_units_administrative_units`
    FOREIGN KEY (`parent_id` )
    REFERENCES `sigmat`.`administrative_units` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sigmat`.`stockrooms`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sigmat`.`stockrooms` ;

CREATE  TABLE IF NOT EXISTS `sigmat`.`stockrooms` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NOT NULL ,
  `status` BOOLEAN NOT NULL DEFAULT 1 ,
  `agency_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_stockroom_agencies` (`agency_id` ASC) ,
  CONSTRAINT `fk_stockroom_agencies`
    FOREIGN KEY (`agency_id` )
    REFERENCES `sigmat`.`agencies` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

USE `sigmat`;

-- -----------------------------------------------------
-- Data for table `sigmat`.`agencies`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO `agencies` (`id`, `name`, `acronym`, `contact`, `phone`, `email`, `status`) VALUES (null, 'Secretária de Estado de Gestão e Planejamento', 'SEGPLAN', '', '', '', 1);
INSERT INTO `agencies` (`id`, `name`, `acronym`, `contact`, `phone`, `email`, `status`) VALUES (null, 'Secretária de Estado da Saúde', 'SES', '', '', '', 1);

COMMIT;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
