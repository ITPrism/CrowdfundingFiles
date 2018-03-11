CREATE TABLE IF NOT EXISTS `#__cffiles_files` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `filename` varchar(32) NOT NULL,
  `filedata` varchar(1024) DEFAULT '{}',
  `section` enum('details','conditions') NOT NULL DEFAULT 'details',
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;