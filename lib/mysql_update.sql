
ALTER TABLE `statusengine_logentries` ADD `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `statusengine_host_statehistory` ADD `state_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `state_time`;
ALTER TABLE `statusengine`.`statusengine_host_statehistory` ADD PRIMARY KEY (`hostname`, `state_time`, `state_time_usec`);

-- Add Hostname for Statusengine !!!
ALTER TABLE `statusengine_service_statehistory` ADD `state_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `state_time`;
ALTER TABLE `statusengine`.`statusengine_service_statehistory` ADD PRIMARY KEY (`service_description`, `state_time`, `state_time_usec`);

ALTER TABLE `statusengine_hostchecks` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`; 
ALTER TABLE `statusengine`.`statusengine_hostchecks` ADD PRIMARY KEY (`hostname`, `start_time`, `start_time_usec`); 

-- Add Hostname for Statusengine !!!
ALTER TABLE `statusengine_servicechecks` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`; 
ALTER TABLE `statusengine`.`statusengine_servicechecks` ADD PRIMARY KEY (`service_description`, `start_time`, `start_time_usec`); 

ALTER TABLE `statusengine_host_notifications` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`; 
ALTER TABLE `statusengine`.`statusengine_host_notifications` ADD PRIMARY KEY (`hostname`, `start_time`, `start_time_usec`);

-- Add Hostname for Statusengine !!!
ALTER TABLE `statusengine_service_notifications` ADD `start_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `start_time`; 
ALTER TABLE `statusengine`.`statusengine_service_notifications` ADD PRIMARY KEY (`service_description`, `start_time`, `start_time_usec`);

ALTER TABLE `statusengine_host_acknowledgements` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`; 
ALTER TABLE `statusengine`.`statusengine_host_acknowledgements` ADD PRIMARY KEY (`hostname`, `entry_time`, `entry_time_usec`);

-- Add Hostname for Statusengine !!!
ALTER TABLE `statusengine_service_acknowledgements` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`; 
ALTER TABLE `statusengine`.`statusengine_service_acknowledgements` ADD PRIMARY KEY (`service_description`, `entry_time`, `entry_time_usec`);

ALTER TABLE `statusengine_host_downtimehistory` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`; 
ALTER TABLE `statusengine`.`statusengine_host_downtimehistory` ADD INDEX `reports` (`hostname`, `entry_time`, `entry_time_usec`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`); 

-- Add Hostname for Statusengine !!!
ALTER TABLE `statusengine_service_downtimehistory` ADD `entry_time_usec` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entry_time`; 
ALTER TABLE `statusengine`.`statusengine_service_downtimehistory` ADD INDEX `reports` (`service_description`, `entry_time`, `entry_time_usec`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`); 


