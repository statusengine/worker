Statusengine Worker is able to read the configuration from a config file or environment variable.
If both is present the values from the configuration file gets used as preferred values.

| Priority | Source               | Comment                                                        |
|----------|----------------------|----------------------------------------------------------------|
| 0        | default value        | Hardcoded value                                                |
| 1        | environment variable | If present it will overwrite the default              |
| 2        | configuration file   | If present it will overwrite the environment variable |


To keep it simple it is recommended to define everything in the configuration file **or** the environment variables.
Even if its possible, I don't recommend to use a mix of both.

## List of available environment variables
| Environment variable                         | Type   | Required | Example / Comments                                                                  |
|----------------------------------------------|--------|----------|-------------------------------------------------------------------------------------|
| SE_NODE_NAME                                 | string | yes      |                                                                                     |
| SE_USE_CRATE                                 | bool   | yes      | You must set `SE_USE_CRATE` or `SE_USE_MYSQL`                                       |
| SE_USE_MYSQL                                 | bool   | yes      |                                                                                     |
| SE_USE_GEARMAN                               | bool   | yes      | You must set `SE_USE_GEARMAN` or `SE_USE_RABBITMQ`                                  |
| SE_USE_RABBITMQ                              | bool   | yes      | You must set `SE_USE_GEARMAN` or `SE_USE_RABBITMQ`                                  |
| SE_USE_REDIS                                 | bool   | no       |                                                                                     |
| SE_PROCESS_PERFDATA                          | bool   | no       | `export SE_PROCESS_PERFDATA=1` or `export SE_PROCESS_PERFDATA=true`                 |
| SE_PERFDATA_BACKEND                          | array  | no       | `export SE_PERFDATA_BACKEND="crate,graphite,mysql,elasticsearch"`                   |
| SE_MYSQL_HOST                                | string | depends  | Required if `SE_USE_MYSQL` is enabled                                               |
| SE_MYSQL_PORT                                | int    | depends  | Required if `SE_USE_MYSQL` is enabled                                               |
| SE_MYSQL_USER                                | string | depends  | Required if `SE_USE_MYSQL` is enabled                                               |
| SE_MYSQL_PASSWORD                            | string | depends  | Required if `SE_USE_MYSQL` is enabled                                               |
| SE_MYSQL_DATABASE                            | string | depends  | Required if `SE_USE_MYSQL` is enabled                                               |
| SE_MYSQL_ENCODING                            | string | depends  | Required if `SE_USE_MYSQL` is enabled, Example: utf8 or utf8mb4                     |
| SE_CRATE_NODES                               | array  | depends  | `export SE_CRATE_NODES="127.0.0.1:4200,192.168.1.1:4200,192.168.10.1:4200"`         |
| SE_GEARMAN_ADDRESS                           | string | depends  | Required if `SE_USE_GEARMAN` is enabled                                             |
| SE_GEARMAN_PORT                              | string | no       |                                                                                     |
| SE_GEARMAN_TIMEOUT                           | string | no       | Gearman connection timeout in milliseconds                                          |
| SE_REDIS_ADDRESS                             | string | no       |                                                                                     |
| SE_REDIS_PORT                                | int    | no       |                                                                                     |
| SE_REDIS_DB                                  | int    | no       |                                                                                     |
| SE_NUMBER_SERVICESTATUS_WORKER               | int    | no       |                                                                                     |
| SE_NUMBER_HOSTSTATUS_WORKER                  | int    | no       |                                                                                     |
| SE_NUMBER_LOGENTRY_WORKER                    | int    | no       |                                                                                     |
| SE_NUMBER_STATECHANGE_WORKER                 | int    | no       |                                                                                     |
| SE_NUMBER_HOSTCHECK_WORKER                   | int    | no       |                                                                                     |
| SE_NUMBER_SERVICECHECK_WORKER                | int    | no       |                                                                                     |
| SE_NUMBER_PERFDATA_WORKER                    | int    | no       |                                                                                     |
| SE_NUMBER_MISC_WORKER                        | int    | no       |                                                                                     |
| SE_NUMBER_OF_BULK_RECORDS                    | int    | no       | Batch size of rows getting insert in one statement                                  |
| SE_MAX_BULK_DELAY                            | int    | no       | Timeout in seconds Statusengine will wait that SE_NUMBER_OF_BULK_RECORDS is reached |
| SE_STORE_LIVE_DATA_IN_ARCHIVE_BACKEND        | bool   | no       |                                                                                     |
| SE_CHECK_FOR_COMMANDS                        | bool   | no       |                                                                                     |
| SE_COMMAND_CHECK_INTERVAL                    | int    | no       |                                                                                     |
| SE_QUERY_HANDLER                             | string | depends  | If `SE_SUBMIT_METHOD=qh`                                                            |
| SE_EXTERNAL_COMMAND_FILE                     | string | depends  | IF `SE_SUBMIT_METHOD=cmd`                                                           |
| SE_SUBMIT_METHOD                             | string | no       | Default value is `cmd`                                                              |
| SE_GRAPHITE_ADDRESS                          | string | depends  | If `graphite` is in `SE_PERFDATA_BACKEND`                                           |
| SE_GRAPHITE_PORT                             | int    | depends  | If `graphite` is in `SE_PERFDATA_BACKEND`                                           |
| SE_GRAPHITE_ILLEGAL_CHARACTERS               | string | no       | `export SE_GRAPHITE_ILLEGAL_CHARACTERS="/[^a-zA-Z^0-9\-\.]/"`                       |
| SE_GRAPHITE_PREFIX                           | string | no       |                                                                                     |
| SE_ELASTICSEARCH_PATTERN                     | string | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_NAME               | string | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_NUMBER_OF_SHARDS   | int    | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_NUMBER_OF_REPLICAS | int    | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_REFRESH_INTERVAL   | string | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_CODEC              | string | no       |                                                                                     |
| SE_ELASTICSEARCH_TEMPLATE_ENABLE_SOURCE      | bool   | no       |                                                                                     |
| SE_ELASTICSEARCH_ADDRESS                     | string | depends  | If `elasticsearch` is in `SE_PERFDATA_BACKEND`                                      |
| SE_ELASTICSEARCH_PORT                        | int    | depends  | If `elasticsearch` is in `SE_PERFDATA_BACKEND`                                      |
| SE_SYSLOG_ENABLED                            | bool   | no       |                                                                                     |
| SE_SYSLOG_TAG                                | string | no       |                                                                                     |
| SE_AGE_HOSTCHECKS                            | int    | no       |                                                                                     |
| SE_AGE_HOST_ACKNOWLEDGEMENTS                 | int    | no       |                                                                                     |
| SE_AGE_HOST_NOTIFICATIONS                    | int    | no       |                                                                                     |
| SE_AGE_HOST_STATEHISTORY                     | int    | no       |                                                                                     |
| SE_AGE_SERVICECHECKS                         | int    | no       |                                                                                     |
| SE_AGE_SERVICE_ACKNOWLEDGEMENTS              | int    | no       |                                                                                     |
| SE_AGE_SERVICE_NOTIFICATIONS                 | int    | no       |                                                                                     |
| SE_AGE_SERVICE_STATEHISTORY                  | int    | no       |                                                                                     |
| SE_AGE_LOGENTRIES                            | int    | no       |                                                                                     |
| SE_AGE_TASKS                                 | int    | no       |                                                                                     |
| SE_AGE_PERFDATA                              | int    | no       |                                                                                     |
| SE_AGE_HOST_DOWNTIMES                        | int    | no       |                                                                                     |
| SE_AGE_SERVICE_DOWNTIMES                     | int    | no       |                                                                                     |
| SE_DISABLE_HTTP_PROXY                        | bool   | no       |                                                                                     |
| SE_RABBITMQ_HOST                             | string | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_PORT                             | int    | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_USER                             | string | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_PASSWORD                         | string | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_VHOST                            | string | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_EXCHANGE                         | string | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_DURABLE_EXCHANGE                 | bool   | depends  | If `SE_USE_RABBITMQ=1`                                                              |
| SE_RABBITMQ_DURABLE_QUEUES                   | bool   | depends  | If `SE_USE_RABBITMQ=1`                                                              |

## Default values
All variables have a predefined default value.
Search in the file [src/Config.php](/src/Config.php) for a variable name to get the default value.

## Documentation for each variable
More information about each variable can be found in
[etc/config.yml.example](/etc/config.yml.example).
Search for a variable without the `SE_` prefix.

## Data types
| Data Type | How to pass                                   | Example                                                           |
|-----------|-----------------------------------------------|-------------------------------------------------------------------|
| string    | `VAR="value"`                                 | `export SE_NODE_NAME="Statusengine"`                              |
| int       | `VAR=value`                                   | `export SE_MYSQL_PORT=3306`                                       |
| bool      | `VAR=1` or out of `[1, true, on, 0, false, off]` | `export SE_USE_MYSQL=1`                                           |
| array     | `VAR=value1,value2`                           | `export SE_PERFDATA_BACKEND="crate,graphite,mysql,elasticsearch"` |


## Examples

This examples work without any config.yml file.

### bash
````
#!/bin/bash
export SE_NODE_NAME="Statusengine"
export SE_USE_GEARMAN=1

export SE_USE_MYSQL=1
export SE_STORE_LIVE_DATA_IN_ARCHIVE_BACKEND=1
export SE_MYSQL_USER="statusengine"
export SE_MYSQL_PASSWORD="password"
export SE_MYSQL_DATABASE="statusengine_data"

export SE_MAX_BULK_DELAY=2
export SE_NUMBER_OF_BULK_RECORDS=100

export SE_PROCESS_PERFDATA=1
export SE_PERFDATA_BACKEND="mysql"

export SE_CHECK_FOR_COMMANDS=1
export SE_EXTERNAL_COMMAND_FILE="/opt/naemon/var/naemon.cmd"

/opt/statusengine/worker/bin/StatusengineWorker.php 

````

#### systemd
````
[Unit]
Description=Statusengine Worker
After=syslog.target network.target gearman-job-server.service crate.service

[Service]
Environment="SE_NODE_NAME=Statusengine"
Environment="SE_USE_GEARMAN=1"

Environment="SE_USE_MYSQL=1"
Environment="SE_STORE_LIVE_DATA_IN_ARCHIVE_BACKEND=1"
Environment="SE_MYSQL_USER=statusengine"
Environment="SE_MYSQL_PASSWORD=password"
Environment="SE_MYSQL_DATABASE=statusengine_data"

Environment="SE_MAX_BULK_DELAY=2"
Environment="SE_NUMBER_OF_BULK_RECORDS=100"

Environment="SE_PROCESS_PERFDATA=1"
Environment="SE_PERFDATA_BACKEND=mysql"

Environment="SE_CHECK_FOR_COMMANDS=1
Environment="SE_EXTERNAL_COMMAND_FILE=/opt/naemon/var/naemon.cmd"

User=root
Type=simple
Restart=on-failure
ExecStart=/opt/statusengine/worker/bin/StatusengineWorker.php

[Install]
WantedBy=multi-user.target
````


#### Docker 
````
docker run \
  -d \
  --name=statusengine-worker \
  -e "SE_NODE_NAME=Statusengine" \
  -e "SE_USE_CRATE=1" \
  -e "SE_USE_GEARMAN=1" \
  -e "SE_GEARMAN_ADDRESS=192.168.10.6"
  -e "SE_CRATE_NODES=192.168.10.5:4200" \
  -e "SE_STORE_LIVE_DATA_IN_ARCHIVE_BACKEND=1" \
  -e "SE_MAX_BULK_DELAY=2" \
  -e "SE_NUMBER_OF_BULK_RECORDS=100" \
  -e "SE_PROCESS_PERFDATA=1" \
  -e "SE_PERFDATA_BACKEND=mysql" \
  -e "SE_CHECK_FOR_COMMANDS=1 \
  -e "SE_EXTERNAL_COMMAND_FILE=/opt/naemon/var/naemon.cmd" \
  ...
````
