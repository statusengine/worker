-- THIS IS NOT A CrateDB.sql FILE
-- THIS FILE WAS CREATED FOR MySQL

CREATE TABLE IF NOT EXISTS `statusengine_logentries` (
  `entry_time`    DATETIME     DEFAULT '1970-01-01 00:00:00',
  `logentry_type` INT(11)      DEFAULT '0',
  `logentry_data` VARCHAR(255) DEFAULT NULL,
  `node_name`     VARCHAR(255) DEFAULT NULL,
  KEY `logentries` (`entry_time`, `logentry_data`, `node_name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `statusengine_host_statehistory` (
  `hostname`              VARCHAR(255),
  `state_time`            DATETIME            DEFAULT '1970-01-01 00:00:00',
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `last_state`            TINYINT(1) UNSIGNED DEFAULT 0,
  `last_hard_state`       TINYINT(1) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_statehistory` (
  `hostname`              VARCHAR(255),
  `service_description`   VARCHAR(255),
  `state_time`            DATETIME            DEFAULT '1970-01-01 00:00:00',
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `last_state`            TINYINT(1) UNSIGNED DEFAULT 0,
  `last_hard_state`       TINYINT(1) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;


CREATE TABLE `statusengine_hostchecks` (
  `hostname`              VARCHAR(255),
  `state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            DATETIME            DEFAULT '1970-01-01 00:00:00',
  `end_time`              DATETIME            DEFAULT '1970-01-01 00:00:00',
  `output`                VARCHAR(1024),
  `timeout`               TINYINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_servicechecks` (
  `hostname`              VARCHAR(255),
  `service_description`   VARCHAR(255),
  `state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            DATETIME            DEFAULT '1970-01-01 00:00:00',
  `end_time`              DATETIME            DEFAULT '1970-01-01 00:00:00',
  `output`                VARCHAR(1024),
  `timeout`               TINYINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` TINYINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    TINYINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_hoststatus` (
  `hostname`                      VARCHAR(255),
  `status_update_time`            TIMESTAMP,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt`         TINYINT(1) UNSIGNED DEFAULT 0,
  `max_check_attempts`            TINYINT(1) UNSIGNED DEFAULT 0,
  `last_check`                    DATETIME            DEFAULT '1970-01-01 00:00:00',
  `next_check`                    DATETIME            DEFAULT '1970-01-01 00:00:00',
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             DATETIME            DEFAULT '1970-01-01 00:00:00',
  `last_hard_state_change`        DATETIME            DEFAULT '1970-01-01 00:00:00',
  `last_hard_state`               TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             DATETIME            DEFAULT '1970-01-01 00:00:00',
  `next_notification`             DATETIME            DEFAULT '1970-01-01 00:00:00',
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
  `scheduled_downtime_depth`      TINYINT(1) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_host`              TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  PRIMARY KEY (`hostname`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_servicestatus` (
  `hostname`                      VARCHAR(255),
  `service_description`           VARCHAR(255),
  `status_update_time`            TIMESTAMP,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt`         TINYINT(1) UNSIGNED DEFAULT 0,
  `max_check_attempts`            TINYINT(1) UNSIGNED DEFAULT 0,
  `last_check`                    DATETIME            DEFAULT '1970-01-01 00:00:00',
  `next_check`                    DATETIME            DEFAULT '1970-01-01 00:00:00',
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             DATETIME            DEFAULT '1970-01-01 00:00:00',
  `last_hard_state_change`        DATETIME            DEFAULT '1970-01-01 00:00:00',
  `last_hard_state`               TINYINT(1) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             DATETIME            DEFAULT '1970-01-01 00:00:00',
  `next_notification`             DATETIME            DEFAULT '1970-01-01 00:00:00',
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
  `scheduled_downtime_depth`      TINYINT(1) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_service`           TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_nodes` (
  `node_name`       VARCHAR(255),
  `node_version`    VARCHAR(255),
  `node_start_time` DATETIME DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`node_name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_tasks` (
  `uuid`       VARCHAR(255),
  `node_name`  VARCHAR(255),
  `entry_time` DATETIME DEFAULT '1970-01-01 00:00:00',
  `type`       VARCHAR(255),
  `payload`    VARCHAR(8192)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_notifications` (
  `hostname`     VARCHAR(255),
  `contact_name` VARCHAR(1024),
  `command_name` VARCHAR(1024),
  `command_args` VARCHAR(1024),
  `state`        TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`   DATETIME            DEFAULT '1970-01-01 00:00:00',
  `end_time`     DATETIME            DEFAULT '1970-01-01 00:00:00',
  `reason_type`  TINYINT(1) UNSIGNED DEFAULT 0,
  `output`       VARCHAR(1024),
  `ack_author`   VARCHAR(255),
  `ack_data`     VARCHAR(1024)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_notifications` (
  `hostname`            VARCHAR(255),
  `service_description` VARCHAR(255),
  `contact_name`        VARCHAR(1024),
  `command_name`        VARCHAR(1024),
  `command_args`        VARCHAR(1024),
  `state`               TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`          DATETIME            DEFAULT '1970-01-01 00:00:00',
  `end_time`            DATETIME            DEFAULT '1970-01-01 00:00:00',
  `reason_type`         TINYINT(1) UNSIGNED DEFAULT 0,
  `output`              VARCHAR(1024),
  `ack_author`          VARCHAR(255),
  `ack_data`            VARCHAR(1024)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;