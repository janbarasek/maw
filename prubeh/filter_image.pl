open(FILE,"graf.eps");

$prevrecord="";
$prevprevrecord="";
$prevjump="undefined";
$prevprevjump="undefined";
$multiple=0.05;

$i=0;
while ($record = <FILE>)
{ 
 $i=$i+1;
 if ($record =~ /^[-0-9]* ([-0-9]*) [VR]$/)
 {
   @prevskip= $record =~ /[-0-9]* ([-0-9]*) [VR]/gs;
   $jump=@prevskip[0];
 }
 else
 { $jump="undefined";}
#print "                aaaa $jump in $record";
if (((!($prevjump eq "undefined"))&& (!($prevprevjump eq "undefined"))&& (! ($jump eq "undefined"))&&(abs($prevprevjump)<($multiple*abs($prevjump)))&&(abs($jump)<($multiple*abs($prevjump)))&&(abs($prevjump)>10))||((!($prevjump eq "undefined"))&& (!($prevprevjump eq "undefined"))&&($jump eq "undefined")&&(abs($prevprevjump)<($multiple*abs($prevjump)))))
# if ((abs($prevprevjump)<($multiple*abs($prevjump)))&&(abs($jump)<($multiple*abs($prevjump)))) 
{ 
#   print "changing $prevrecord";
   $prevrecord =~ s/V/R/;
 }
# else {print "keeping $prevrecord";
#print "variables: $prevprevjump $prevjump $jump \n";}
 print $prevrecord;
 $prevprevrecord=$prevrecord;
 $prevprevjump=$prevjump;
 $prevrecord=$record;
 $prevjump=$jump;
}

print $prevrecord;
close(FILE);
