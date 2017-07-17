#!/usr/bin/perl 
my $req = new CGI;  
my $basedira = $req->param("uploaddir");
$basedira =~ s!^.*(\.\.)!!;
$basedir = "/tmp/mnt".$basedira;
my $donepage = $req->param("rej_url");
$allowall = "yes"; 
@theext =(".raw",".iso",".tgz",".lib"); 
use CGI::Carp qw(fatalsToBrowser); 
#use strict; 
use CGI qw(:all); 
$cgi = new CGI; 
print $cgi->header(),
$cgi->start_html(),
$onnum = 1; 
$filenotgood = ""; 
$message="fail!";
while ($onnum != 11) { 
	my $req = new CGI;  
	my $file = $req->param("FILE$onnum");  
	if ($file ne "") { 
		my $fileName = $file;  
		$fileName =~ s!^.*(\\|\/)!!;  
		$newmain = $fileName; 
		$extname = lc(substr($newmain,length($newmain) - 4,4)); 
		if ($allowall eq "yes") { 
			for(my $i = 0; $i < @theext; $i++){ 
				if ($extname eq $theext[$i]){ 
					$filenotgood = "yes"; 
					last; 
				} 
			} 
		}
		
		if ($filenotgood eq "yes") {  
			open (OUTFILE, ">$basedir/$fileName");  
			#print "$basedir/$fileName"; 
			while (my $bytesread = read($file, my $buffer, 1024)) {  
				print OUTFILE $buffer;  
				$message="success!";
			}  
			close (OUTFILE);  
		} 
	} 
	$onnum++; 
} 

print "<script language='JavaScript'>  alert('$message'); self.location='$donepage'; </script>" ;
$cgi->end_html();
