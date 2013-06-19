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
  `status` BOOLEAN NULL ,
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



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
