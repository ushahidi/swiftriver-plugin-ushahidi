-- -----------------------------------------------------
-- Table `deployments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `deployments`(
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`deployment_url` VARCHAR(200) NOT NULL COMMENT 'URL of the Ushahidi deployment',
	`deployment_date_add` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	UNIQUE KEY `un_deployment_url` (`deployment_url`),
	KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB CHARSET=utf8;

-- -----------------------------------------------------
-- Table `deployment_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `deployment_users`(
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`user_id` BIGINT(20) NOT NULL,
	`deployment_id` BIGINT(20) NOT NULL,
	`deployment_name` VARCHAR(80) NOT NULL COMMENT 'Name of the deployment',
	`token_key` VARCHAR(255) NOT NULL COMMENT 'Token key for the Ushahidi deployment',
	`token_secret` VARCHAR(255) NOT NULL COMMENT 'Token secret for the Ushahidi deployment',
	PRIMARY KEY (`id`),
	UNIQUE KEY `un_deployment_user`(`user_id`, `deployment_id`)
) ENGINE=InnoDB CHARSET=utf8;

-- -----------------------------------------------------
-- Table `deployment_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `deployment_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deployment_id` bigint(20) NOT NULL,
  `deployment_category_id` int(11) NOT NULL COMMENT 'ID of the category  on the deployment',
  `deployment_parent_category_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent ID of the category',
  `deployment_category_name` varchar(150) NOT NULL COMMENT 'Name of the category',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_deployment_category` (`deployment_id`,`deployment_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `deployment_push_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `deployment_push_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deployment_id` bigint(20) NOT NULL,
  `bucket_id` bigint(20) NOT NULL,
  `droplet_id` bigint(20) NOT NULL,
  `droplet_date_push` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when the droplet was pushed to the deployment',
  `droplet_push_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether the droplet has been successfully pushed to the deployment',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_droplet_bucket` (`bucket_id`,`droplet_id`),
  KEY `deployment_id_idx` (`deployment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `deployment_push_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `deployment_push_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deployment_id` bigint(20) NOT NULL,
  `bucket_id` bigint(20) NOT NULL,
  `deployment_category_id` bigint(20) NOT NULL COMMENT 'Category to push to',
  `push_drop_count` int(11) DEFAULT 20 COMMENT 'Batch size for pushing drops to the deployment. Default is 20',
  `pending_drop_count` int(11) DEFUALT 0 'Number of drops that are yet to be pushed to the deployment',
  `push_active` tinyint(1) DEFAULT 1 COMMENT 'Whether the push is active or inactive. Default is 1 (active)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_bucket_deployment_category` (`bucket_id`,`deployment_category_id`),
  KEY `deployment_id_idx` (`deployment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;