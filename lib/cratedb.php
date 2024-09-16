<?php

use Doctrine\DBAL\Schema\Schema;

/****************************************
 * Unfinished due to: https://github.com/crate/crate-dbal/issues/92
 ***************************************/

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$schema = new Schema();

/****************************************
 * Define: statusengine_users
 ***************************************/
$table = $schema->createTable("statusengine_users");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("username", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("password", "string", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_service_scheduleddowntimes
 ***************************************/
$table = $schema->createTable("statusengine_service_scheduleddowntimes");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("internal_downtime_id", "integer", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("scheduled_start_time", "timestamp", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("triggered_by_id", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_fixed", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("duration", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_started", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname", 
    "service_description", 
    "node_name", 
    "scheduled_start_time", 
    "internal_downtime_id"
]);



/****************************************
 * Define: statusengine_service_statehistory
 ***************************************/
$table = $schema->createTable("statusengine_service_statehistory");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state_change", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_hostchecks
 ***************************************/
$table = $schema->createTable("statusengine_hostchecks");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("timeout", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("early_timeout", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("latency", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("execution_time", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("perfdata", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_dbversion
 ***************************************/
$table = $schema->createTable("statusengine_dbversion");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 1);
$table->addColumn("id", "integer", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("dbversion", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "id"
]);



/****************************************
 * Define: statusengine_host_acknowledgements
 ***************************************/
$table = $schema->createTable("statusengine_host_acknowledgements");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("acknowledgement_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_sticky", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("persistent_comment", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("notify_contacts", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_service_notifications
 ***************************************/
$table = $schema->createTable("statusengine_service_notifications");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("contact_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command_args", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("reason_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("ack_author", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("ack_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));


/****************************************
 * Define: statusengine_service_notifications_log
 ***************************************/
$table = $schema->createTable("statusengine_service_notifications_log");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("service_description", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("state", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("reason_type", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("is_escalated", "boolean", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("contacts_notified_count", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("output", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("ack_author", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("ack_data", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));


/****************************************
 * Define: statusengine_hoststatus
 ***************************************/
$table = $schema->createTable("statusengine_hoststatus");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("status_update_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("perfdata", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_check", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("next_check", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_passive_check", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_state_change", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state_change", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_notification", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("next_notification", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("notifications_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("problem_has_been_acknowledged", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("acknowledgement_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("passive_checks_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("active_checks_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("event_handler_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("flap_detection_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_flapping", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("latency", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("execution_time", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_downtime_depth", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("process_performance_data", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("obsess_over_host", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("normal_check_interval", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("retry_check_interval", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("check_timeperiod", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_up", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_down", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_unreachable", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_notification_number", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("percent_state_change", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("event_handler", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("check_command", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname"
]);



/****************************************
 * Define: statusengine_host_statehistory
 ***************************************/
$table = $schema->createTable("statusengine_host_statehistory");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state_change", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_servicechecks
 ***************************************/
$table = $schema->createTable("statusengine_servicechecks");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "smallint", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("timeout", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("early_timeout", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("latency", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("execution_time", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("perfdata", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_host_notifications
 ***************************************/
$table = $schema->createTable("statusengine_host_notifications");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("contact_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("command_args", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("reason_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("ack_author", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("ack_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));

/****************************************
 * Define: statusengine_host_notifications_log
 ***************************************/
$table = $schema->createTable("statusengine_host_notifications_log");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("start_time", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("end_time", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("state", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("reason_type", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("is_escalated", "boolean", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("contacts_notified_count", "integer", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("output", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("ack_author", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("ack_data", "string", array (
    'notnull' => false,
    'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
    'notnull' => false,
    'default' => NULL,
));

/****************************************
 * Define: statusengine_service_acknowledgements
 ***************************************/
$table = $schema->createTable("statusengine_service_acknowledgements");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("acknowledgement_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_sticky", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("persistent_comment", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("notify_contacts", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_logentries
 ***************************************/
$table = $schema->createTable("statusengine_logentries");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("logentry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("logentry_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("logentry_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));



/****************************************
 * Define: statusengine_servicestatus
 ***************************************/
$table = $schema->createTable("statusengine_servicestatus");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("status_update_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("long_output", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("perfdata", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_check_attempt", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("max_check_attempts", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_check", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("next_check", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_passive_check", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_state_change", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state_change", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_hard_state", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_hardstate", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_notification", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("next_notification", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("notifications_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("problem_has_been_acknowledged", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("acknowledgement_type", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("passive_checks_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("active_checks_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("event_handler_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("flap_detection_enabled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_flapping", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("latency", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("execution_time", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_downtime_depth", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("process_performance_data", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("obsess_over_service", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("normal_check_interval", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("retry_check_interval", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("check_timeperiod", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_ok", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_warning", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_critical", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("last_time_unknown", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("current_notification_number", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("percent_state_change", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("event_handler", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("check_command", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname", 
    "service_description"
]);



/****************************************
 * Define: statusengine_service_downtimehistory
 ***************************************/
$table = $schema->createTable("statusengine_service_downtimehistory");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("internal_downtime_id", "integer", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("scheduled_start_time", "timestamp", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("triggered_by_id", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_fixed", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("duration", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_started", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_cancelled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname", 
    "service_description", 
    "node_name", 
    "scheduled_start_time", 
    "internal_downtime_id"
]);



/****************************************
 * Define: statusengine_host_scheduleddowntimes
 ***************************************/
$table = $schema->createTable("statusengine_host_scheduleddowntimes");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("internal_downtime_id", "integer", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("scheduled_start_time", "timestamp", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("triggered_by_id", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_fixed", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("duration", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_started", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname", 
    "node_name", 
    "scheduled_start_time", 
    "internal_downtime_id"
]);



/****************************************
 * Define: statusengine_host_downtimehistory
 ***************************************/
$table = $schema->createTable("statusengine_host_downtimehistory");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("hostname", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("internal_downtime_id", "integer", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("scheduled_start_time", "timestamp", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("node_name", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("entry_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("author_name", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("comment_data", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("triggered_by_id", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("is_fixed", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("duration", "integer", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("scheduled_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_started", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("actual_end_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("was_cancelled", "boolean", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "hostname", 
    "node_name", 
    "scheduled_start_time", 
    "internal_downtime_id"
]);



/****************************************
 * Define: statusengine_nodes
 ***************************************/
$table = $schema->createTable("statusengine_nodes");
$table->addOption("table_options", ["number_of_replicas" => "1-all"]);
$table->addOption("sharding_num_shards" , 4);
$table->addColumn("node_name", "string", array (
  'notnull' => true,
  'default' => NULL,
));
$table->addColumn("node_version", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("node_start_time", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->setPrimaryKey([
    "node_name"
]);



/****************************************
 * Define: statusengine_perfdata
 ***************************************/
$table = $schema->createTable("statusengine_perfdata");
$table->addOption("table_options", ["number_of_replicas" => "0"]);
$table->addOption("sharding_num_shards" , 4);
$table->addOption("partition_columns" , "array (
  0 => 'day',
)");
$table->addColumn("hostname", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("service_description", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("label", "string", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("timestamp", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("timestamp_unix", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("day", "timestamp", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("value", "float", array (
  'notnull' => false,
  'default' => NULL,
));
$table->addColumn("unit", "string", array (
  'notnull' => false,
  'default' => NULL,
));



return $schema;
