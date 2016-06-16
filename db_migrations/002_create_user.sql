DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `username` VARCHAR(45) NOT NULL COMMENT '',
  `encrypted_password` VARCHAR(45) NOT NULL COMMENT '',
  `token` VARCHAR(45) COMMENT '',
  `token_expire` DATETIME COMMENT '',
  PRIMARY KEY (`id`) COMMENT '',
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) COMMENT '');
