-- THIS IS NOT A MYSQL.sql FILE
-- THIS FILE WAS CREATED FOR CrateDB

-- Load this schema via the CrateDB Admin UI
-- You need to execute every "CRATE TABLE" as a own query

CREATE TABLE statusengine_logentries (
    logentry_time timestamp,
    entry_time timestamp,
    logentry_type int,
    logentry_data string,
    node_name string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
    long_output string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
    long_output string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
    long_output string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
    ack_data string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');

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
    ack_data string
) CLUSTERED INTO 4 shards with (number_of_replicas = '0');