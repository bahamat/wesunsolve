#!/bin/bash

sudo awffull --use_geoip -p -c /srv/sunsolve/etc/awffull.conf -o /srv/sunsolve/www/static/stats ${1}
