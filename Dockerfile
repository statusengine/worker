FROM ubuntu
RUN mkdir -p /opt/statusengine/worker
RUN echo "America/New_York" > /etc/timezone 
RUN apt-get update && apt-get install tzdata && dpkg-reconfigure -f noninteractive tzdata
RUN   apt-get install -y git php-cli php-zip php-redis redis-server php-mysql php-json php-gearman php-bcmath php-mbstring unzip wget
RUN  wget https://getcomposer.org/installer && chmod +x installer && php ./installer --install-dir=bin --filename=composer
WORKDIR /opt/statusengine/worker/
COPY . /opt/statusengine/worker/
RUN chmod +x bin/* && composer install
RUN cp etc/config.yml.example etc/config.yml
CMD [ "/opt/statusengine/worker/bin/StatusengineWorker.php" ]
