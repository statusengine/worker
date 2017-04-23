# Alpha code
This is alpha code! May be things are broken, not implemented or got removed with the stable version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License 3 for more details.

# Requirements
- PHP's composer
- CrateDB or MySQL
- Redis
- php >= 5.5.9
- Ubuntu 14.04, 16.04 or 16.10

# Install (Ubuntu 14.04)
````
apt-get install redis-server php5-redis git

mkdir -p /opt/statusengine
cd /opt/statusengine
git clone https://github.com/statusengine/worker.git
cd worker/
chmod +x worker/bin/*
composer install
````

# Config
````
cp worker/etc/config.yml.example worker/etc/config.yml
````
Change `node_name` to a unique name in your monitoring cluster!

# Usage
````
/opt/statusengine/worker/bin/StatusengineWorker.php
````

# Proxy warnign
If you are behind a proxy, set `no_proxy=127.0.0.1,localhost` in your `/etc/environment`!

# Statusengine statistics
````
/opt/statusengine/worker/bin/bin/Console.php statistics --watch 5
````

# ToDos
* [ ] Add Downtimes
* [X] Add Acknowledgements
* [x] Add Notifications
* [x] Add Graphite
* [x] Implement Query Error handling
* [x] Add Syslog support
* [ ] ~~More tests~~
* [ ] init/systemd support
* [x] Cronjob to cleanup database
* [ ] Delete old performance data recors in CrateDB via cron





# License
GNU General Public License v3.0
````
Statusengine Worker
Copyright (C) 2016-2017  Daniel Ziegler

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
````
