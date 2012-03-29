#!/usr/bin/python
##
# Thomas Gouverneur - 2012
##
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

url = 'http://www.oracle.com/technetwork/topics/security/thirdparty-patch-map-1482893.html'
cvelist = []

debug('[-] Fetching page ' + url)
r = s.get(url)
if r.status_code != requests.codes.ok:
  debug('[!] Error fetching page')
  exit(-1)
  
c = r.content;
soup = BeautifulSoup(c)
    
for vuln in soup.findAll('tr', { 'class':'vtr' } ):
  subtd = vuln.findAll('td')
  component = subtd[1].text
  cveids = subtd[2]
  fixes = subtd[3]
  tmps = []
  for cveid in cveids.findAll('a'):
    cname = cveid.text

    sv = None
    for svv in cvelist:
      if svv.cve == cname:
        sv = svv

    for svv in tmps:
      if svv.cve == cname:
        if sv == None:
          sv = svv

    if sv == None:
      sv = SecVuln(cname, component)
      tmps.append(sv)
    
  if len(fixes.contents) == 1:
    for cve in tmps:
      cve.patches.append(fixes.text)
  else:
    for link in fixes.findAll('a'):
      for cve in tmps:
        cve.patches.append(link.string)

  for cve in tmps:
    cvelist.append(cve)

debug('[-] Found CVEs:')
for cve in cvelist:
  print cve


