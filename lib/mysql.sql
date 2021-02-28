-- THIS IS NOT A CrateDB.sql FILE
-- THIS FILE WAS CREATED FOR MySQL
--
-- The usage of this file is DEPRECATED and support for plain SQL dumps will be dropped soon!
-- Please use the following command to create and update your database
-- /opt/statusengine/worker/bin/Console.php database --update
--
-- Use the --dry-run option to see all SQL statements that will be executed without touching your live database
-- /opt/statusengine/worker/bin/Console.php database --update --dry-run
--

CREATE TABLE IF NOT EXISTS `statusengine_logentries` (
  `entry_time`    BIGINT(13) NOT NULL,
  `logentry_type` INT(11)      DEFAULT '0',
  `logentry_data` VARCHAR(255) DEFAULT NULL,
  `node_name`     VARCHAR(255) DEFAULT NULL,
  KEY `logentries` (`entry_time`, `logentry_data`, `node_name`),
  KEY `logentry_data_time` (`logentry_data`, `entry_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `statusengine_host_statehistory` (
  `hostname`              VARCHAR(255),
  `state_time`            BIGINT(13) NOT NULL,
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    SMALLINT(3) UNSIGNED DEFAULT 0,
  `last_state`            SMALLINT(2) UNSIGNED DEFAULT 0,
  `last_hard_state`       SMALLINT(2) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192),
  KEY `hostname_time` (`hostname`, `state_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_statehistory` (
  `hostname`              VARCHAR(255),
  `service_description`   VARCHAR(255),
  `state_time`            BIGINT(13) NOT NULL,
  `state_change`          TINYINT(1)          DEFAULT 0,
  `state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `current_check_attempt` SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    SMALLINT(3) UNSIGNED DEFAULT 0,
  `last_state`            SMALLINT(2) UNSIGNED DEFAULT 0,
  `last_hard_state`       SMALLINT(2) UNSIGNED DEFAULT 0,
  `output`                VARCHAR(1024),
  `long_output`           VARCHAR(8192),
  KEY `host_servicename_time` (`hostname`, `service_description`, `state_time`),
  KEY `servicename_time` (`service_description`, `state_time`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;


CREATE TABLE `statusengine_hostchecks` (
  `hostname`              VARCHAR(255),
  `state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            BIGINT(13) NOT NULL,
  `end_time`              BIGINT(13) NOT NULL,
  `output`                VARCHAR(1024),
  `timeout`               SMALLINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    SMALLINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192),
  KEY `times` (`start_time`, `end_time`),
  KEY `hostname` (`hostname`, `start_time`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_servicechecks` (
  `hostname`              VARCHAR(255),
  `service_description`   VARCHAR(255),
  `state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`          TINYINT(1) UNSIGNED DEFAULT 0,
  `start_time`            BIGINT(13) NOT NULL,
  `end_time`              BIGINT(13) NOT NULL,
  `output`                VARCHAR(1024),
  `timeout`               SMALLINT(3) UNSIGNED DEFAULT 0,
  `early_timeout`         TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`               FLOAT               DEFAULT 0,
  `execution_time`        FLOAT               DEFAULT 0,
  `perfdata`              VARCHAR(1024),
  `command`               VARCHAR(1024),
  `current_check_attempt` SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`    SMALLINT(3) UNSIGNED DEFAULT 0,
  `long_output`           VARCHAR(8192),
  KEY `servicename` (`hostname`, `service_description`, `start_time`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_hoststatus` (
  `hostname`                      VARCHAR(255),
  `status_update_time`            BIGINT(13) NOT NULL,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `current_check_attempt`         SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`            SMALLINT(3) UNSIGNED DEFAULT 0,
  `last_check`                    BIGINT(13) NOT NULL,
  `next_check`                    BIGINT(13) NOT NULL,
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             BIGINT(13) NOT NULL,
  `last_hard_state_change`        BIGINT(13) NOT NULL,
  `last_hard_state`               SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             BIGINT(13) NOT NULL,
  `next_notification`             BIGINT(13) NOT NULL,
  `notifications_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `problem_has_been_acknowledged` TINYINT(1) UNSIGNED DEFAULT 0,
  `acknowledgement_type`          SMALLINT(2) UNSIGNED DEFAULT 0,
  `passive_checks_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `active_checks_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `event_handler_enabled`         TINYINT(1) UNSIGNED DEFAULT 0,
  `flap_detection_enabled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `is_flapping`                   TINYINT(1) UNSIGNED DEFAULT 0,
  `latency`                       FLOAT               DEFAULT 0,
  `execution_time`                FLOAT               DEFAULT 0,
  `scheduled_downtime_depth`      SMALLINT(2) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_host`              TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  `last_time_up`                  BIGINT(13) NOT NULL,
  `last_time_down`                BIGINT(13) NOT NULL,
  `last_time_unreachable`         BIGINT(13) NOT NULL,
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
  `hostname`                      VARCHAR(255),
  `service_description`           VARCHAR(255),
  `status_update_time`            BIGINT(13) NOT NULL,
  `output`                        VARCHAR(1024),
  `long_output`                   VARCHAR(1024),
  `perfdata`                      VARCHAR(1024),
  `current_state`                 SMALLINT(2) UNSIGNED DEFAULT 0,
  `current_check_attempt`         SMALLINT(3) UNSIGNED DEFAULT 0,
  `max_check_attempts`            SMALLINT(3) UNSIGNED DEFAULT 0,
  `last_check`                    BIGINT(13) NOT NULL,
  `next_check`                    BIGINT(13) NOT NULL,
  `is_passive_check`              TINYINT(1) UNSIGNED DEFAULT 0,
  `last_state_change`             BIGINT(13) NOT NULL,
  `last_hard_state_change`        BIGINT(13) NOT NULL,
  `last_hard_state`               SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_hardstate`                  TINYINT(1) UNSIGNED DEFAULT 0,
  `last_notification`             BIGINT(13) NOT NULL,
  `next_notification`             BIGINT(13) NOT NULL,
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
  `scheduled_downtime_depth`      SMALLINT(2) UNSIGNED DEFAULT 0,
  `process_performance_data`      TINYINT(1) UNSIGNED DEFAULT 0,
  `obsess_over_service`           TINYINT(1) UNSIGNED DEFAULT 0,
  `normal_check_interval`         INT(11) UNSIGNED    DEFAULT 0,
  `retry_check_interval`          INT(11) UNSIGNED    DEFAULT 0,
  `check_timeperiod`              VARCHAR(255),
  `node_name`                     VARCHAR(255),
  last_time_ok                    BIGINT(13) NOT NULL,
  last_time_warning               BIGINT(13) NOT NULL,
  last_time_critical              BIGINT(13) NOT NULL,
  last_time_unknown               BIGINT(13) NOT NULL,
  `current_notification_number`   INT(11) UNSIGNED    DEFAULT 0,
  `percent_state_change`          DOUBLE              DEFAULT 0,
  `event_handler`                 VARCHAR(255),
  `check_command`                 VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`),
  KEY `current_state_node` (`current_state`, `node_name`),
  KEY `issues` (`problem_has_been_acknowledged`, `scheduled_downtime_depth`, `current_state`),
  KEY `service_description` (`service_description`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_nodes` (
  `node_name`       VARCHAR(255),
  `node_version`    VARCHAR(255),
  `node_start_time` BIGINT(13) NOT NULL,
  PRIMARY KEY (`node_name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_tasks` (
  `uuid`       VARCHAR(255),
  `node_name`  VARCHAR(255),
  `entry_time` BIGINT(13) NOT NULL,
  `type`       VARCHAR(255),
  `payload`    VARCHAR(8192),
  KEY `node_name` (`node_name`),
  KEY `uuid` (`uuid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_notifications` (
  `hostname`     VARCHAR(255),
  `contact_name` VARCHAR(1024),
  `command_name` VARCHAR(1024),
  `command_args` VARCHAR(1024),
  `state`        SMALLINT(2) UNSIGNED DEFAULT 0,
  `start_time`   BIGINT(13) NOT NULL,
  `end_time`     BIGINT(13) NOT NULL,
  `reason_type`  SMALLINT(3) UNSIGNED DEFAULT 0,
  `output`       VARCHAR(1024),
  `ack_author`   VARCHAR(255),
  `ack_data`     VARCHAR(1024),
  KEY `hostname` (`hostname`),
  KEY `start_time` (`start_time`)

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
  `state`               SMALLINT(2) UNSIGNED DEFAULT 0,
  `start_time`          BIGINT(13) NOT NULL,
  `end_time`            BIGINT(13) NOT NULL,
  `reason_type`         SMALLINT(3) UNSIGNED DEFAULT 0,
  `output`              VARCHAR(1024),
  `ack_author`          VARCHAR(255),
  `ack_data`            VARCHAR(1024),
  KEY `servicename` (`hostname`, `service_description`),
  KEY `start_time` (`start_time`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_acknowledgements` (
  `hostname`             VARCHAR(255),
  `state`                SMALLINT(2) UNSIGNED DEFAULT 0,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `entry_time`           BIGINT(13) NOT NULL,
  `acknowledgement_type` SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_sticky`            TINYINT(1) UNSIGNED DEFAULT 0,
  `persistent_comment`   TINYINT(1) UNSIGNED DEFAULT 0,
  `notify_contacts`      TINYINT(1) UNSIGNED DEFAULT 0,
  KEY `hostname` (`hostname`),
  KEY `entry_time` (`entry_time`)

)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_acknowledgements` (
  `hostname`             VARCHAR(255),
  `service_description`  VARCHAR(255),
  `state`                SMALLINT(2) UNSIGNED DEFAULT 0,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `entry_time`           BIGINT(13) NOT NULL,
  `acknowledgement_type` SMALLINT(2) UNSIGNED DEFAULT 0,
  `is_sticky`            TINYINT(1) UNSIGNED DEFAULT 0,
  `persistent_comment`   TINYINT(1) UNSIGNED DEFAULT 0,
  `notify_contacts`      TINYINT(1) UNSIGNED DEFAULT 0,
  KEY `servicename` (`hostname`, `service_description`),
  KEY `entry_time` (`entry_time`),
  KEY `servicedesc_time` (`service_description`, `entry_time`)
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
  `hostname`             VARCHAR(255),
  `entry_time`           BIGINT(13) NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) NOT NULL,
  `scheduled_end_time`   BIGINT(13) NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) NOT NULL,
  `actual_end_time`      BIGINT(13) NOT NULL,
  `was_cancelled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `node_name`, `scheduled_start_time`, `internal_downtime_id`),
  KEY `list` (`hostname`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_host_scheduleddowntimes` (
  `hostname`             VARCHAR(255),
  `entry_time`           BIGINT(13) NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) NOT NULL,
  `scheduled_end_time`   BIGINT(13) NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) NOT NULL,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `node_name`, `scheduled_start_time`, `internal_downtime_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_downtimehistory` (
  `hostname`             VARCHAR(255),
  `service_description`  VARCHAR(255),
  `entry_time`           BIGINT(13) NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) NOT NULL,
  `scheduled_end_time`   BIGINT(13) NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) NOT NULL,
  `actual_end_time`      BIGINT(13) NOT NULL,
  `was_cancelled`        TINYINT(1) UNSIGNED DEFAULT 0,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`, `node_name`, `scheduled_start_time`, `internal_downtime_id`),
  KEY `report` (`service_description`, `scheduled_start_time`, `scheduled_end_time`, `was_cancelled`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_service_scheduleddowntimes` (
  `hostname`             VARCHAR(255),
  `service_description`  VARCHAR(255),
  `entry_time`           BIGINT(13) NOT NULL,
  `author_name`          VARCHAR(255),
  `comment_data`         VARCHAR(1024),
  `internal_downtime_id` INT(11) UNSIGNED,
  `triggered_by_id`      INT(11) UNSIGNED,
  `is_fixed`             TINYINT(1) UNSIGNED DEFAULT 0,
  `duration`             INT(11) UNSIGNED,
  `scheduled_start_time` BIGINT(13) NOT NULL,
  `scheduled_end_time`   BIGINT(13) NOT NULL,
  `was_started`          TINYINT(1) UNSIGNED DEFAULT 0,
  `actual_start_time`    BIGINT(13) NOT NULL,
  `node_name`            VARCHAR(255),
  PRIMARY KEY (`hostname`, `service_description`, `node_name`, `scheduled_start_time`, `internal_downtime_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_general_ci;

CREATE TABLE `statusengine_perfdata` (
  `hostname`            VARCHAR(255),
  `service_description` VARCHAR(255),
  `label`               VARCHAR(255),
  `timestamp`           BIGINT(20) NOT NULL,
  `timestamp_unix`      BIGINT(13) NOT NULL,
  `value`               DOUBLE,
  `unit`                VARCHAR(10),
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
