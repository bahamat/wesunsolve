#!/bin/bash

/usr/bin/wget --no-check-certificate -O /srv/sunsolve/www/static/visits_year.png "https://stats.espix.net/index.php?module=API&method=ImageGraph.get&idSite=1&apiModule=VisitsSummary&apiAction=get&graphType=evolution&period=day&date=previous365&width=700&height=200&token_auth=44e62da70cd02d4eeaa352d227b182a4"

/usr/bin/wget --no-check-certificate -O /srv/sunsolve/www/static/visits_month.png "https://stats.espix.net/index.php?module=API&method=ImageGraph.get&idSite=1&apiModule=VisitsSummary&apiAction=get&graphType=evolution&period=day&date=previous31&width=700&height=200&token_auth=44e62da70cd02d4eeaa352d227b182a4"

#wget --no-check-certificate -O /srv/sunsolve/www/static/ ""
#wget --no-check-certificate -O /srv/sunsolve/www/static/ ""
