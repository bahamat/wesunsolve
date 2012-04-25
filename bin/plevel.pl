#!/usr/bin/perl
use Fcntl;
use POSIX;

##
# Fill your wesunsolve account credentials
##
$username="";
$password="";

$wget = "/usr/bin/wget";

##
# Do not modify below
#

sub trim($)
{
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

if ($#ARGV < 0 || $#ARGV > 3) {
  print "plevel.pl <name> [server] [showrev-p.out] [pkginfo-l.out]\n";
  print "\tname:\tName of the patch level to be added\n";
  print "\tserver:\tServer hostname\n";
  print "\tshowrev-p.out:\tPath to showrev-p.out file, defaults to output of /usr/sbin/showrev -p\n";
  print "\tpkginfo-l.out:\tPath to pkginfo-l.out file, defaults to output of /usr/sbin/pkginfo -l\n";
  exit(-1);
}

print "[-] Initialization...\n";

$name=$ARGV[0];

print "[-] Patch level name: $name\n";

if ($#ARGV >= 1) {
  $host=$ARGV[1];
} else {
  $host=`hostname`;
}
print "[-] Using hostname: $host\n";

if ($#ARGV >= 2) {
  $showrev_path = $ARGV[2];
} else {
  $showrev_path = "|/usr/bin/showrev -p";
}
print "[-] showrev path: $showrev_path\n";

if ($#ARGV >= 3) {
  $pkginfo_path = $ARGV[3];
} else {
  $pkginfo_path = "|/usr/bin/pkginfo -l";
}
print "[-] pkginfo path: $pkginfo_path\n";

$MAX_SIZE=1000000;

# Build url
$url = "https://wesunsolve.net/api/";
$url = "$url/u/$username/p/$password/action/add_plevel/arg/$host";

do {
  $rawfile = tmpnam();
} until sysopen(OUT, $rawfile, O_RDWR|O_CREAT|O_EXCL, 0600);

print OUT "name=$name&size=$size&type=binary/octet-stream&showrev=";

open(IN, $showrev_path);
$size=read IN,$RAW,$MAX_SIZE;
print "[-] Preparing showrev-p.out ($size bytes)\n";
$RAW =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
print OUT $RAW;
close IN;

print OUT "&pkginfo=";

open(IN, $pkginfo_path);
$size=read IN,$RAW,$MAX_SIZE;
print "[-] Preparing pkginfo-l.out ($size bytes)\n";
$RAW =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
print OUT $RAW;
close IN;
close OUT;

print "[-] Sending... ";
$rc = `$wget -q --post-file=$rawfile -O - $url`;
print trim($rc);
print "\n[-] Cleanup...\n";
unlink($rawfile);
