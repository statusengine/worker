-- THIS IS NOT A CrateDB.sql FILE
-- THIS FILE WAS CREATED FOR MySQL
--
-- EXPERIMENTAL
-- This file will use BINARY for Host and Service names

CREATE TABLE IF NOT EXISTS `statusengine_logentries` (
  `entry_time`    BIGINT(13)   UNSIGNED NOT NULL,
  `logentry_type` INT(11)      DEFAULT '0',
  `logentry_data` VARCHAR(255) DEFAULT NULL,
  `node_name`     VARCHAR(255) DEFAULT NULL,
  KEY `logentries` (`entry_time`, `logentry_data`, `node_name`),
  KEY `logentry_data_time` (`logentry_data`, `entry_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `statusengine_host_statehistory` (
  `hostname`              BINARY(16) NOT NULL,
  `state_time`            BIGINT(13) UNSIGNED NOT NULL,
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `last_state`            TINYINT(2) UNSIGNED DEFAULT 0,
  `last_hard_state`       TINYINT(2) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192),
  PRIMARY KEY (`hostname`, `state_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_statehistory` (
  `hostname`              BINARY(16) NOT NULL,
  `service_description`   BINARY(16) NOT NULL,
  `state_time`            BIGINT(13) UNSIGNED NOT NULL,
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `last_state`            TINYINT(2) UNSIGNED DEFAULT 0,
  `last_hard_state`       TINYINT(2) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192),
  PRIMARY KEY (`service_description`, `state_time`),
  KEY `hostname` (`hostname`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;


CREATE TABLE `statusengine_hostchecks` (
  `hostname`              BINARY(16) NOT NULL,
  `state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            BIGINT(13) UNSIGNED NOT NULL,
  `end_time`              BIGINT(13) UNSIGNED NOT NULL,
  `output`                VARCHAR(1024),
  `timeout`               TINYINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192),
  PRIMARY KEY (`hostname`, `start_time`),
  KEY `times` (`start_time`, `end_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_servicechecks` (
  `hostname`              BINARY(16) NOT NULL,
  `service_description`   BINARY(16) NOT NULL,
  `state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            BIGINT(13) UNSIGNED NOT NULL,
  `end_time`              BIGINT(13) UNSIGNED NOT NULL,
  `output`                VARCHAR(1024),
  `timeout`               TINYINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192),
  PRIMARY KEY (`service_description`, `start_time`),
  KEY `hostname` (`hostname`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_hoststatus` (
  `hostname`                      BINARY(16) NOT NULL,
  `status_update_time`            BIGINT(13) UNSIGNED NOT NULL,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `current_check_attempt`         TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`            TINYINT(3) UNSIGNED DEFAULT 0,
  `last_check`                    BIGINT(13) UNSIGNED NOT NULL,
  `next_check`                    BIGINT(13) UNSIGNED NOT NULL,
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             BIGINT(13) UNSIGNED NOT NULL,
  `last_hard_state_change`        BIGINT(13) UNSIGNED NOT NULL,
  `last_hard_state`               TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             BIGINT(13) UNSIGNED NOT NULL,
  `next_notification`             BIGINT(13) UNSIGNED NOT NULL,
  `notifications_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `problem_has_been_acknowledged` TINYINT(1) UNSIGNED DEFAULT 0,
  `acknowledgement_type`          TINYINT(2) UNSIGNED DEFAULT 0,
  `passive_checks_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `active_checks_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `event_handler_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `flap_detection_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `is_flapping`                   TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`                       FLOAT               DEFAULT 0,
  `execution_time`                FLOAT               DEFAULT 0,
  `scheduled_downtime_depth`      TINYINT(2) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_host`              TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  `last_time_up`                  BIGINT(13) UNSIGNED NOT NULL,
  `last_time_down`                BIGINT(13) UNSIGNED NOT NULL,
  `last_time_unreachable`         BIGINT(13) UNSIGNED NOT NULL,
  `current_notification_number`   INT(11) UNSIGNED    DEFAULT 0,
  `percent_state_change`          DOUBLE              DEFAULT 0,
  `event_handler`                 VARCHAR(255),
  `check_command`                 VARCHAR(255),
  PRIMARY KEY (`hostname`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_servicestatus` (
  `hostname`                      BINARY(16) NOT NULL,
  `service_description`           BINARY(16) NOT NULL,
  `status_update_time`            BIGINT(13) UNSIGNED NOT NULL,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 TINYINT(2) UNSIGNED DEFAULT 0,
  `current_check_attempt`         TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`            TINYINT(3) UNSIGNED DEFAULT 0,
  `last_check`                    BIGINT(13) UNSIGNED NOT NULL,
  `next_check`                    BIGINT(13) UNSIGNED NOT NULL,
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             BIGINT(13) UNSIGNED NOT NULL,
  `last_hard_state_change`        BIGINT(13) UNSIGNED NOT NULL,
  `last_hard_state`               TINYINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             BIGINT(13) UNSIGNED NOT NULL,
  `next_notification`             BIGINT(13) UNSIGNED NOT NULL,
  `notifications_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `problem_has_been_acknowledged` TINYINT(1) UNSIGNED DEFAULT 0,
  `acknowledgement_type`          TINYINT(1) UNSIGNED DEFAULT 0,
  `passive_checks_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `active_checks_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `event_handler_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `flap_detection_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `is_flapping`                   TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`                       FLOAT               DEFAULT 0,
  `execution_time`                FLOAT               DEFAULT 0,
  `scheduled_downtime_depth`      TINYINT(2) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_service`           TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  last_time_ok                    BIGINT(13) UNSIGNED NOT NULL,
  last_time_warning               BIGINT(13) UNSIGNED NOT NULL,
  last_time_critical              BIGINT(13) UNSIGNED NOT NULL,
  last_time_unknown               BIGINT(13) UNSIGNED NOT NULL,
  `current_notification_number`   INT(11) UNSIGNED    DEFAULT 0,
  `percent_state_change`          DOUBLE              DEFAULT 0,
  `event_handler`                 VARCHAR(255),
  `check_command`                 VARCHAR(255),
  PRIMARY KEY (`service_description`),
  KEY `current_state_node` (`current_state`, `node_name`),
  KEY `issues` (`problem_has_been_acknowledged`, `scheduled_downtime_depth`, `current_state`),
  KEY `hostname` (`hostname`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_nodes` (
  `node_name`       VARCHAR(255),
  `node_version`    VARCHAR(255),
  `node_start_time` BIGINT(13) UNSIGNED NOT NULL,
  PRIMARY KEY (`node_name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_tasks` (
  `uuid`       VARCHAR(255),
  `node_name`  VARCHAR(255),
  `entry_time` BIGINT(13) UNSIGNED NOT NULL,
  `type`       VARCHAR(255),
  `payload`    VARCHAR(8192),
  KEY `node_name` (`node_name`),
  KEY `uuid` (`uuid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_notifications` (
  `hostname`     BINARY(16) NOT NULL,
  `contact_name` VARCHAR(1024),
  `command_name` VARCHAR(1024),
  `command_args` VARCHAR(1024),
  `state`        TINYINT(2) UNSIGNED DEFAULT 0,
  `start_time`   BIGINT(13) UNSIGNED NOT NULL,
  `end_time`     BIGINT(13) UNSIGNED NOT NULL,
  `reason_type`  TINYINT(3) UNSIGNED DEFAULT 0,
  `output`       VARCHAR(1024),
  `ack_author`   VARCHAR(255),
  `ack_data`     VARCHAR(1024),
  PRIMARY KEY (`hostname`, `start_time`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_notifications` (
  `hostname`            BINARY(16) NOT NULL,
  `service_description` BINARY(16) NOT NULL,
  `contact_name`        VARCHAR(1024),
  `command_name`        VARCHAR(1024),
  `command_args`        VARCHAR(1024),
  `state`               TINYINT(2) UNSIGNED DEFAULT 0,
  `start_time`          BIGINT(13) UNSIGNED NOT NULL,
  `end_time`            BIGINT(13) UNSIGNED NOT NULL,
  `reason_type`         TINYINT(3) UNSIGNED DEFAULT 0,
  `output`              VARCHAR(1024),
  `ack_author`          VARCHAR(255),
  `ack_data`            VARCHAR(1024),
  PRIMARY KEY (`service_description`, `start_time`),
  KEY `hostname` (`hostname`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_acknowledgements` (
  `hostname`             BINARY(16) NOT NULL,
  `state`                TINYINT(2) UNSIGNED DEFAULT 0,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `acknowledgement_type` TINYINT(2) UNSIGNED DEFAULT 0,
  `is_sticky`            TINYINT(1) UNSIGNED DEFAULT 0,
  `persistent_comment`   TINYINT(1) UNSIGNED DEFAULT 0,
  `notify_contacts`      TINYINT(1) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`hostname`, `entry_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_acknowledgements` (
  `hostname`             BINARY(16) NOT NULL,
  `service_description`  BINARY(16) NOT NULL,
  `state`                TINYINT(2) UNSIGNED DEFAULT 0,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `acknowledgement_type` TINYINT(2) UNSIGNED DEFAULT 0,
  `is_sticky`            TINYINT(1) UNSIGNED DEFAULT 0,
  `persistent_comment`   TINYINT(1) UNSIGNED DEFAULT 0,
  `notify_contacts`      TINYINT(1) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`service_description`, `entry_time`),
  KEY `hostname` (`hostname`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_users` (
  `username` VARCHAR(255),
  `password` VARCHAR(255),
  KEY `username` (`username`, `password`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_downtimehistory` (
  `hostname`             BINARY(16) NOT NULL,
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) UNSIGNED NOT NULL,
  `scheduled_end_time`   BIGINT(13) UNSIGNED NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) UNSIGNED NOT NULL,
  `actual_end_time`      BIGINT(13) UNSIGNED NOT NULL,
  `was_cancelled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `node_name`, `scheduled_start_time`, `internal_downtime_id`),
  KEY `list` (`scheduled_start_time`, `scheduled_end_time`, `was_cancelled`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_scheduleddowntimes` (
  `hostname`             BINARY(16) NOT NULL,
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) UNSIGNED NOT NULL,
  `scheduled_end_time`   BIGINT(13) UNSIGNED NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) UNSIGNED NOT NULL,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `node_name`, `scheduled_start_time`, `internal_downtime_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_downtimehistory` (
  `hostname`             BINARY(16) NOT NULL,
  `service_description`  BINARY(16) NOT NULL,
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) UNSIGNED NOT NULL,
  `scheduled_end_time`   BIGINT(13) UNSIGNED NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) UNSIGNED NOT NULL,
  `actual_end_time`      BIGINT(13) UNSIGNED NOT NULL,
  `was_cancelled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`, `node_name`, `scheduled_start_time`, `internal_downtime_id`),
  KEY `report` (`scheduled_start_time`, `scheduled_end_time`, `was_cancelled`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_scheduleddowntimes` (
  `hostname`             BINARY(16) NOT NULL,
  `service_description`  BINARY(16) NOT NULL,
  `entry_time`           BIGINT(13) UNSIGNED NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) UNSIGNED NOT NULL,
  `scheduled_end_time`   BIGINT(13) UNSIGNED NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) UNSIGNED NOT NULL,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`, `node_name`, `scheduled_start_time`, `internal_downtime_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_perfdata` (
  `hostname`            BINARY(16) NOT NULL,
  `service_description` BINARY(16) NOT NULL,
  `label`               VARCHAR(255),
  `timestamp`           BIGINT(20) NOT NULL,
  `timestamp_unix`      BIGINT(13) UNSIGNED NOT NULL,
  `value`               DOUBLE,
  `unit`                VARCHAR(10),
  PRIMARY KEY (`service_description`, `timestamp_unix`),
  KEY `metric` (`hostname`, `service_description`, `label`, `timestamp_unix`),
  KEY `timestamp_unix` (`timestamp_unix`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `statusengine_dbversion` (
  `id`        INT(13) NOT NULL,
  `dbversion` VARCHAR(255) DEFAULT '3.0.0',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO `statusengine_dbversion` (`id`, `dbversion`) VALUES (1, '3.2.0');