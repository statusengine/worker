
ALTER TABLE `statusengine_logentries` ADD `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `statusengine_host_statehistory` ADD `state_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `state_time`;
UPDATE `statusengine_host_statehistory` SET `state_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_host_statehistory` ADD PRIMARY KEY (`hostname`, `state_time`, `state_time_usec`);

ALTER TABLE `statusengine_service_statehistory` ADD `state_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `state_time`;
UPDATE `statusengine_service_statehistory` SET `state_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_service_statehistory` ADD PRIMARY KEY (`hostname`, `service_description`, `state_time`, `state_time_usec`);

ALTER TABLE `statusengine_hostchecks` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`;
UPDATE `statusengine_hostchecks` SET `start_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_hostchecks` ADD PRIMARY KEY (`hostname`, `start_time`, `start_time_usec`); 

ALTER TABLE `statusengine_servicechecks` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`;
UPDATE `statusengine_servicechecks` SET `start_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_servicechecks` ADD PRIMARY KEY (`hostname`, `service_description`, `start_time`, `start_time_usec`);

ALTER TABLE `statusengine_host_notifications` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`;
UPDATE `statusengine_host_notifications` SET `start_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_host_notifications` ADD PRIMARY KEY (`hostname`, `start_time`, `start_time_usec`);

ALTER TABLE `statusengine_service_notifications` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`;
UPDATE `statusengine_service_notifications` SET `start_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_service_notifications` ADD PRIMARY KEY (`hostname`, `service_description`, `start_time`, `start_time_usec`);

ALTER TABLE `statusengine_host_acknowledgements` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`;
UPDATE `statusengine_host_acknowledgements` SET `entry_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_host_acknowledgements` ADD PRIMARY KEY (`hostname`, `entry_time`, `entry_time_usec`);

ALTER TABLE `statusengine_service_acknowledgements` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`;
UPDATE `statusengine_service_acknowledgements` SET `entry_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_service_acknowledgements` ADD PRIMARY KEY (`hostname`, `service_description`, `entry_time`, `entry_time_usec`);

ALTER TABLE `statusengine_host_downtimehistory` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`;
UPDATE `statusengine_host_downtimehistory` SET `entry_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_host_downtimehistory` ADD INDEX `reports` (`hostname`, `entry_time`, `entry_time_usec`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`); 

ALTER TABLE `statusengine_service_downtimehistory` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`;
UPDATE `statusengine_service_downtimehistory` SET `entry_time_usec`=FLOOR(RAND() * 100000000);
ALTER TABLE `statusengine`.`statusengine_service_downtimehistory` ADD INDEX `reports` (`hostname`, `service_description`, `entry_time`, `entry_time_usec`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`);

INSERT INTO statusengine_dbversion (id, dbversion)VALUES(1, '3.7.0') ON DUPLICATE KEY UPDATE dbversion='3.7.0'
