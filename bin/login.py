#!/usr/bin/python

import sys
import time
import requests
import cookielib
from BeautifulSoup import BeautifulSoup

OutFile='/srv/sunsolve/tmp/cookies.txt'
MOSLogin='user@mail.com'
MOSPassword='larry123'


def mprint(x):
    sys.stdout.write(x)
    return

class MyMozillaCookieJar(cookielib.MozillaCookieJar):
    def __init__(self, c):
            self.__dict__ = c.__dict__

    def save(self, filename):
      f = open(filename, "w")
      try:
#          f.write(self.header)
          now = time.time()
          for cookie in self:
              if cookie.secure: secure = "TRUE"
              else: secure = "FALSE"
              if cookie.domain.startswith("."): initial_dot = "TRUE"
              else: initial_dot = "FALSE"
              if cookie.expires is not None:
                  expires = str(cookie.expires)
              else:
                  expires = "0"
              if cookie.value is None:
                  # cookies.txt regards 'Set-Cookie: foo' as a cookie
                  # with no name, whereas cookielib regards it as a
                  # cookie with no value.
                  name = ""
                  value = cookie.name
              else:
                  name = cookie.name
                  value = cookie.value
              f.write(
                  "\t".join([cookie.domain, initial_dot, cookie.path,
                             secure, expires, name, value])+
                  "\n")
      finally:
          f.close()



headers = {'User-Agent': 'Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20100101 Firefox/7.0.1'}

mprint('[-] Initialization...')
s = requests.session(headers=headers)
print 'done'


mprint('[-] Gathering JSESSIONID..')
r = s.get('https://support.oracle.com/CSP/ui/flash.html')
if r.status_code != requests.codes.ok:
  print 'error'
  exit(1)
print 'done'

mprint('[-] Trying loginSuccess.jsp...')
r = s.get('https://support.oracle.com/CSP/secure/loginSuccess.jsp')

if r.status_code != requests.codes.ok:
  print 'error'
  exit(1)
print 'done'

c = r.content;
soup = BeautifulSoup(c);

svars = {}

for var in soup.findAll('input', type="hidden"):
  svars[var['name']]=var['value']

mprint('[-] Signing in...')

data = { 
	'v': svars['v'],
	'site2pstoretoken': svars['site2pstoretoken'],
	'p_submit_url': svars['p_submit_url'],
	'p_cancel_url': svars['p_cancel_url'],
	'p_error_code': svars['p_error_code'],
	'ssousername': svars['ssousername'],
	'subscribername': svars['subscribername'],
	'request_id': svars['request_id'],
	'OAM_REQ': svars['OAM_REQ'],
	'locale': svars['locale'],
	}

r = s.post('https://login.oracle.com/mysso/signon.jsp', data=data)

if r.status_code != requests.codes.ok:
  print 'error'
  exit(1)

print 'done'

c = r.content;
soup = BeautifulSoup(c);

svars = {}

for var in soup.findAll('input', type="hidden"):
  svars[var['name']]=var['value']

data = 	{
	'v': svars['v'],
	'OAM_REQ': svars['OAM_REQ'],
	'site2pstoretoken': svars['site2pstoretoken'],
	'locale': svars['locale'],
	'ssousername': MOSLogin,
	'password': MOSPassword,
	}

mprint('[-] Trying to submit credentials...')

r = s.post('https://login.oracle.com/oam/server/sso/auth_cred_submit', data=data)
if r.status_code != requests.codes.ok:
  print 'error'
  exit(1)

print 'done'

mprint('[-] Checking that credentials are valid...')
r = s.get('https://support.oracle.com/CSP/ui/flash.html')
if r.status_code != requests.codes.ok:
  print 'error'
  exit(1)

print 'done'
print '[-] Logged-in.'

mc = MyMozillaCookieJar(s.cookies)
mc.save(OutFile)
exit(0)
