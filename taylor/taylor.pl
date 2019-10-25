# Mathematical Assistant on Web - web interface for mathematical          
# computations including step by step solutions
# Copyright 2007-2010 Robert Marik, Miroslava Tihlarikova
# Copyright 2013 Robert Marik
#
# This file is part of Mathematical Assistant on Web.
#
# Mathematical Assistant on Web is free software: you can
# redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software
# Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Mathematical Assistant on Web is distributed in the hope that it
# will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
# PURPOSE.  See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mathematical Assistant o Web.  If not, see 
# <http://www.gnu.org/licenses/>.


use Locale::gettext;

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();
 
open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");

if ($order>10) {$order=10;}
$shorter=$mawhome."/common/shorter.mac";

## podobne, ale argumentem je pole. pokud dojde k chybe, zapise se
## vystup do souboru output
sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"load(\\\"$shorter\\\")\$ tex($prikaz);\"`;

    if (maw::maximaerror($retezec))
    {
	$errmsg=sprintf(gettext("The function is either not well formatted mathematical expression or it is not continuous or one of the derivatives at %s does not exist. Check the Maxima output below."),$point);
	saveoutput ("</pre><hr><h2 class='red'>".gettext("Error")."</h2><b class='red'>$errmsg</b><br><pre>\n<pre>".$retezec."</pre>");
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    if ($retezec!~/\$\$\\left/)
     {
      saveoutput("Error. (Calculation terminated, overladed server?)\n"); 
      die();
     }
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}



$i=0;

for ($i=0;$i<=$order;$i=$i+1)
{
    if ($i==0)
    {
	$retezec="$function,ev($function,x=$point),ev($function,x=$point)";
    }
    else
    {
	$retezec=$retezec.",(expr:fullratsimp(trigsimp(diff($function,x,$i))),shorter(expr)),subst($point,x,diff($function,x,$i)),subst($point,x,diff($function,x,$i))/($i!)";
    }
}

@vystupmax=maximatexlist("[$retezec,block(pfeformat:true,taylor($function,x,$point,$order)),block(simp:false,$function)]");

#print $retezec;

# $vystupmax[0]=`echo "$function" | formconv `;
$vystupmax[0]=$vystupmax[3*$order+4];

$point =~ s/%pi/\\pi/g;
$point =~ s/%e/e/g;

print VYSTUP2 "<?php\n";

print VYSTUP "\\retezecA{$order}{$vystupmax[0]}{$point}";
print VYSTUP2 "\$retezecA1='$order'; \$retezecA2='$vystupmax[0]'; \$retezecA3='$point';\n";

print VYSTUP "\\retezecB";
print VYSTUP "\$f(x)=$vystupmax[0]\$\\qquad\$f($point)=$vystupmax[1]\$\n\n";
print VYSTUP2 "\$fx='f(x)=$vystupmax[0]'; \$fxat='f($point)=$vystupmax[1]';\n\n";

print VYSTUP "\\retezecC{$point} \n\n\n";
print VYSTUP2 "\$retezecC='$point'; \n\n\n";

print VYSTUP "\\indent\\null\\qquad\\begin{tabular}{|c|c|c|c|}\\hline\n \$ i\$&\$f^{(i)}(x)\$ & \$f^{(i)}(x_0)\$ & \\fbox{\$\\frac{f^{(i)}(x_0)}{i!}\$}\\\\\\hline\\hline\n";
print VYSTUP2 "\$htmltable='<table><tr><th>\$ i\$</th><th>\$f^{(i)}(x)\$</th><th>\$f^{(i)}(x_0)\$</th><th>\$\\frac{f^{(i)}(x_0)}{i!}\$</th></tr>\n";

for ($i=1;$i<=$order;$i=$i+1)
{
    print VYSTUP "$i&\\fbox{\$ $vystupmax[3*$i]\$}& \\fbox{\$ $vystupmax[3*$i+1]\$}&\\fbox{\$ $vystupmax[3*$i+2]\$}";
    print VYSTUP2 "<tr><th>$i</th><td>\$ $vystupmax[3*$i]\$</td><td>\$ $vystupmax[3*$i+1]\$</td><td>\$ $vystupmax[3*$i+2]\$</td></tr>\n";
    if ($i<$order) 
    {
	print VYSTUP  "\\\\\\hline\n";
    }
    
}

print VYSTUP2 "</table>';\n\n";
print VYSTUP  "\\\\\\hline\n\\end{tabular}\n\n\\medskip\\textbf{3.}  \\retezecD";

$vystupmax[3*$order+3] =~ s/\+\\cdots//;

print VYSTUP  "\n\n\n\n \\null\\qquad \$\\displaystyle T_{$order}(x)= $vystupmax[3*$order+3] \$";
print VYSTUP2  "\$resultHTML='T_{$order}(x)= $vystupmax[3*$order+3]';";

print VYSTUP  "\\let\\test\\relax";

print VYSTUP2 "?>\n";
