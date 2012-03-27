#!/usr/bin/python
import requests
from BeautifulSoup import BeautifulSoup, NavigableString
import sys

__debug = False

def has_overview(tag):
  if tag.text == 'Overview' and tag.name == 'h4':
    return True
  return False

def has_cvss(tag):
  if tag.name == 'span' and tag.text == 'CVSS v2 Base Score:':
    return True
  return False

def has_released(tag):
  if tag.name == 'span' and tag.text == 'Original release date:':
    return True
  return False

def has_revised(tag):
  if tag.name == 'span' and tag.text == 'Last revised:':
    return True
  return False

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


if len(sys.argv) != 2:
  print sys.argv[0] + ' <CVE>'
  exit(-1)

__headers = {'User-Agent': 'Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20100101 Firefox/7.0.1'}

s = requests.session(headers=__headers)

cve = sys.argv[1]
url = 'http://web.nvd.nist.gov/view/vuln/detail?vulnId=' + cve

debug('[-] Fetching page ' + url + ' ...')
r = s.get(url)
if r.status_code != requests.codes.ok:
  debug('[!] Error fetching page')
  exit(-1)
  
c = r.content;
soup = BeautifulSoup(c)


released = soup.find(has_released).parent
revised = soup.find(has_revised).parent

print released.text
print revised.text

cvsscore = soup.find(has_cvss).parent
print cvsscore.text

    
overview = soup.find(has_overview)
print overview.parent.p.text


