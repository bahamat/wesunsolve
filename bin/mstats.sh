#!/bin/bash

sudo awffull --use_geoip -p -c /srv/sunsolve/etc/awffull.conf -o /srv/sunsolve/www/stats /var/log/apache2/access.log
sudo /usr/sbin/logrotate -fs /srv/sunsolve/etc/logrotate.state /srv/sunsolve/etc/logrotate.conf
