#!/usr/bin/python
import requests
from BeautifulSoup import BeautifulSoup, NavigableString

__debug = False

def debug(message):
  if __debug == True:
    print message

class SecVuln:
  def __init__(self, cve, component=''):
    self.cve = cve.strip()
    self.component = component.strip()
    self.patches = []

  def __repr__(self):
    out = self.cve + ';' + self.component + ';'
    i = 0
    for p in self.patches:
      if i != 0:
        out = out + ','
      out = out + p
      i = i + 1

    return out



__headers = {'User-Agent': 'Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20100101 Firefox/7.0.1'}

s = requests.session(headers=__headers)

i = 0
url = 'https://blogs.oracle.com/sunsecurity/?page='
cvelist = []

while True:

  debug('[-] Fetching page ' + str(i) + ' ...')
  r = s.get(url + str(i))
  if r.status_code != requests.codes.ok:
    debug('[!] Error fetching page')
    exit(-1)
  
  c = r.content;
  soup = BeautifulSoup(c)
  found = 0
    
  for vuln in soup.findAll('table', { 'class':'vuln' } ):
    
    found = found + 1
    tmps = []
    tmpp = []

    products = vuln.find('table', { 'class':'product' } )
    if products == None:
      continue # malformed entry

    for cve in vuln.findAll('td', { 'class':'cve' } ):
      component = vuln.find('td', { 'class':'component' } )
      c = None
      cname = cve.a.string.strip()

      for cc in cvelist:
        if cc.cve == cname:
	  c = cc

      for cc in tmps:
        if cc.cve == cname:
	  if c == None:
	    c = cc
      
      if c == None:
        c = SecVuln(cname, component.string.strip())
	tmps.append(c)

    for patch in products.findAll('a'):
      for cve in tmps:
        cve.patches.append(patch.string.strip())
  
    for cve in tmps:
      cvelist.append(cve)

  debug('\t> ' + str(found) + ' CVE record found (' + str(len(cvelist)) + ')')

  if found > 0: # we still have vuln on next page probably
    i = i + 1
    continue

  break


debug('[-] Found CVEs:')
for cve in cvelist:
  print cve


