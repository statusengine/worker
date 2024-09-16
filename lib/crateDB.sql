-- THIS IS NOT A MYSQL.sql FILE
-- THIS FILE WAS CREATED FOR CrateDB

-- Load this schema via the CrateDB Admin UI
-- You need to execute every "CRATE TABLE" as a own query

CREATE TABLE statusengine_logentries (
    logentry_time timestamp,
    entry_time timestamp,
    logentry_type int,
    logentry_data string,
    node_name string,
    day as date_trunc('day', logentry_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

CREATE TABLE statusengine_host_statehistory (
    hostname   string,
    state_time timestamp,
    state_change boolean,
    state short,
    is_hardstate boolean,
    current_check_attempt int,
    max_check_attempts int,
    last_state short,
    last_hard_state short,
    output string,
    long_output string,
    day as date_trunc('day', state_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

CREATE TABLE statusengine_service_statehistory (
    hostname   string,
    service_description string,
    state_time timestamp,
    state_change boolean,
    state short,
    is_hardstate boolean,
    current_check_attempt int,
    max_check_attempts int,
    last_state short,
    last_hard_state short,
    output string,
    long_output string,
    day as date_trunc('day', state_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

CREATE TABLE statusengine_hostchecks (
    hostname string,
    state short,
    is_hardstate boolean,
    start_time timestamp,
    end_time timestamp,
    output string,
    timeout int,
    early_timeout boolean,
    latency float,
    execution_time float,
    perfdata string,
    command string,
    current_check_attempt int,
    max_check_attempts int,
    long_output string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

CREATE TABLE statusengine_servicechecks (
    hostname string,
    service_description string,
    state short,
    is_hardstate boolean,
    start_time timestamp,
    end_time timestamp,
    output string,
    timeout int,
    early_timeout boolean,
    latency float,
    execution_time float,
    perfdata string,
    command string,
    current_check_attempt int,
    max_check_attempts int,
    long_output string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_perfdata (
    hostname string,
    service_description string,
    label string,
    timestamp  timestamp,
    timestamp_unix timestamp,
    day as date_trunc('day', timestamp),
    value double,
    unit string
) clustered into 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_hoststatus (
    hostname string,
    status_update_time timestamp,
    output string,
    long_output string,
    perfdata string,
    current_state int,
    current_check_attempt int,
    max_check_attempts int,
    last_check timestamp,
    next_check timestamp,
    is_passive_check boolean,
    last_state_change timestamp,
    last_hard_state_change timestamp,
    last_hard_state int,
    is_hardstate boolean,
    last_notification timestamp,
    next_notification timestamp,
    notifications_enabled boolean,
    problem_has_been_acknowledged boolean,
    acknowledgement_type int,
    passive_checks_enabled boolean,
    active_checks_enabled boolean,
    event_handler_enabled boolean,
    flap_detection_enabled boolean,
    is_flapping boolean,
    latency float,
    execution_time float,
    scheduled_downtime_depth int,
    process_performance_data boolean,
    obsess_over_host boolean,
    normal_check_interval int,
    retry_check_interval int,
    check_timeperiod string,
    node_name string,
    last_time_up timestamp,
    last_time_down timestamp,
    last_time_unreachable timestamp,
    current_notification_number int,
    percent_state_change double,
    event_handler string,
    check_command string,
    PRIMARY KEY ("hostname")
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

create table statusengine_servicestatus (
    hostname string,
    service_description string,
    status_update_time timestamp,
    output string,
    long_output string,
    perfdata string,
    current_state int,
    current_check_attempt int,
    max_check_attempts int,
    last_check timestamp,
    next_check timestamp,
    is_passive_check boolean,
    last_state_change timestamp,
    last_hard_state_change timestamp,
    last_hard_state int,
    is_hardstate boolean,
    last_notification timestamp,
    next_notification timestamp,
    notifications_enabled boolean,
    problem_has_been_acknowledged boolean,
    acknowledgement_type int,
    passive_checks_enabled boolean,
    active_checks_enabled boolean,
    event_handler_enabled boolean,
    flap_detection_enabled boolean,
    is_flapping boolean,
    latency float,
    execution_time float,
    scheduled_downtime_depth int,
    process_performance_data boolean,
    obsess_over_service boolean,
    normal_check_interval int,
    retry_check_interval int,
    check_timeperiod string,
    node_name string,
    last_time_ok timestamp,
    last_time_warning timestamp,
    last_time_critical timestamp,
    last_time_unknown timestamp,
    current_notification_number int,
    percent_state_change double,
    event_handler string,
    check_command string,
    PRIMARY KEY ("hostname", "service_description")
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

create table statusengine_nodes (
    node_name string,
    node_version string,
    node_start_time timestamp,
    PRIMARY KEY ("node_name")
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_tasks (
    uuid string,
    node_name string,
    entry_time timestamp,
    type string,
    payload string
) CLUSTERED INTO 4 shards with (number_of_replicas = '1');

create table statusengine_host_notifications (
    hostname string,
    contact_name string,
    command_name string,
    command_args string,
    state int,
    start_time timestamp,
    end_time timestamp,
    reason_type int,
    output string,
    ack_author string,
    ack_data string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_host_notifications_log (
    hostname string,
    start_time timestamp,
    end_time timestamp,
    state int,
    reason_type int,
    is_escalated boolean,
    contacts_notified_count int,
    output string,
    ack_author string,
    ack_data string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_service_notifications (
    hostname string,
    service_description string,
    contact_name string,
    command_name string,
    command_args string,
    state int,
    start_time timestamp,
    end_time timestamp,
    reason_type int,
    output string,
    ack_author string,
    ack_data string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_service_notifications_log (
    hostname string,
    service_description string,
    start_time timestamp,
    end_time timestamp,
    state int,
    reason_type int,
    is_escalated boolean,
    contacts_notified_count int,
    output string,
    ack_author string,
    ack_data string,
    day as date_trunc('day', start_time * 1000)
) CLUSTERED INTO 4 shards partitioned by (day) with (number_of_replicas = '0');

create table statusengine_host_acknowledgements (
    hostname string,
    state int,
    author_name string,
    comment_data string,
    entry_time timestamp,
    acknowledgement_type int,
    is_sticky boolean,
    persistent_comment boolean,
    notify_contacts boolean
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

create table statusengine_service_acknowledgements (
    hostname string,
    service_description string,
    state int,
    author_name string,
    comment_data string,
    entry_time timestamp,
    acknowledgement_type int,
    is_sticky boolean,
    persistent_comment boolean,
    notify_contacts boolean
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

create table statusengine_users (
    username string,
    password string
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_host_downtimehistory (
    hostname string,
    entry_time timestamp,
    author_name string,
    comment_data string,
    internal_downtime_id int,
    triggered_by_id int,
    is_fixed boolean,
    duration int,
    scheduled_start_time timestamp,
    scheduled_end_time timestamp,
    was_started boolean,
    actual_start_time timestamp,
    actual_end_time timestamp,
    was_cancelled boolean,
    node_name string,
    PRIMARY KEY ("hostname", "node_name", "scheduled_start_time", "internal_downtime_id")
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_host_scheduleddowntimes (
    hostname string,
    entry_time timestamp,
    author_name string,
    comment_data string,
    internal_downtime_id int,
    triggered_by_id int,
    is_fixed boolean,
    duration int,
    scheduled_start_time timestamp,
    scheduled_end_time timestamp,
    was_started boolean,
    actual_start_time timestamp,
    node_name string,
    PRIMARY KEY ("hostname", "node_name", "scheduled_start_time", "internal_downtime_id")
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_service_downtimehistory (
    hostname string,
    service_description string,
    entry_time timestamp,
    author_name string,
    comment_data string,
    internal_downtime_id int,
    triggered_by_id int,
    is_fixed boolean,
    duration int,
    scheduled_start_time timestamp,
    scheduled_end_time timestamp,
    was_started boolean,
    actual_start_time timestamp,
    actual_end_time timestamp,
    was_cancelled boolean,
    node_name string,
    PRIMARY KEY ("hostname", "service_description", "node_name", "scheduled_start_time", "internal_downtime_id")
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_service_scheduleddowntimes (
    hostname string,
    service_description string,
    entry_time timestamp,
    author_name string,
    comment_data string,
    internal_downtime_id int,
    triggered_by_id int,
    is_fixed boolean,
    duration int,
    scheduled_start_time timestamp,
    scheduled_end_time timestamp,
    was_started boolean,
    actual_start_time timestamp,
    node_name string,
    PRIMARY KEY ("hostname", "service_description", "node_name", "scheduled_start_time", "internal_downtime_id")
) CLUSTERED INTO 4 shards with (number_of_replicas = '1-all');

create table statusengine_dbversion (
    id int,
    dbversion string,
    PRIMARY KEY ("id")
) CLUSTERED INTO 1 shards with (number_of_replicas = '1-all');


INSERT INTO statusengine_dbversion (id, dbversion)VALUES(1, '3.8.0');