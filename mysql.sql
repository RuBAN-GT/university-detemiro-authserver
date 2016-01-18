CREATE TABLE IF NOT EXISTS `modules` (
  `service_id` bigint(20) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `method` varchar(60) NOT NULL DEFAULT 'require',
  PRIMARY KEY (`service_id`,`name`),
  CONSTRAINT `md_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `redirects` (
  `service_id` bigint(20) unsigned NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`service_id`,`address`),
  CONSTRAINT `rs_service_id` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `secret` text,
  `info` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `param` varchar(255) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`service_id`, `param`),
  CONSTRAINT `cg_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB CHARSET=utf8