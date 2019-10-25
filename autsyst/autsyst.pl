# Mathematical Assistant on Web - web interface for mathematical          
# computations including step by step solutions
# Copyright 2007-2010 Robert Marik, Miroslava Tihlarikova
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

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 


use URI::Escape;

open(VSTUP,"vstup");
open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");
print VYSTUP2 "<?php\n";

sub modifiedprint
{
    print VYSTUP "\\def\\".$_[0]."{".$_[1]."}\n";
    print VYSTUP2 "\$"."$_[0]='$_[1]';\n";
}


@predfiltrem=<VSTUP>;

$f=@predfiltrem[0];
$g=@predfiltrem[1];
$xs=@predfiltrem[2];
$ys=@predfiltrem[3];
$lang=@predfiltrem[4];

chomp($f);
chomp($g);
chomp($xs);
chomp($ys);
chomp($lang);

sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"tex($prikaz);\"`;
    if (maw::maximaerror($retezec))
    {
	saveoutput("</pre><span style='font-weight:bold'><span class='red'>".gettext("An error occurred when processing your input.<br>Check your formulas (perhaps using Preview button) and report the problem if you think that your input is correct and should be processed without any error.")."</span></span><pre>\n<pre>".$retezec."</pre>");
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}


########################################################




@vystupmax=maximatexlist("[$f,
$g,
radcan(ev($f,x=$xs,y=$ys)),
radcan(ev($g,x=$xs,y=$ys)),
A:diff($f,x),
B:diff($f,y),
C:diff($g,x),
D:diff($g,y),
AA:ev(A,x=$xs,y=$ys),
BB:ev(B,x=$xs,y=$ys),
CC:ev(C,x=$xs,y=$ys),
DD:ev(D,x=$xs,y=$ys),
M:matrix([A,B],[C,D]),
MM:matrix([AA,BB],[CC,DD]),
det:determinant(MM),
p:expand(charpoly(MM,x)),
$xs,
$ys,
(temp:solve(p,x), l1:rectform(rhs(temp[1]))),
if length(temp)>1 then l2:rectform(rhs(temp[2])) else l2:rectform(rhs(temp[1])),
float(rectform(l1)),
float(rectform(l2)),
stopa:AA+DD,
if is(det<0) then 1 
  elseif is(det>0) and is(stopa>0) and is(stopa^2-4*det>=0) then 2 
  elseif is(det>0) and is(stopa>0) and is(stopa^2-4*det<0) then 3
  elseif is(det>0) and is(stopa<0) and is(stopa^2-4*det>=0) then 4 
  elseif is(det>0) and is(stopa<0) and is(stopa^2-4*det<0) then 5
  elseif is(det>0) and is(stopa=0) and is(stopa^2-4*det<0) then 6
  else 7
]");

$ftex=$vystupmax[0];
$gtex=$vystupmax[1];
$testA=$vystupmax[2];
$testB=$vystupmax[3];
$dfx=$vystupmax[4];
$dfy=$vystupmax[5];
$dgx=$vystupmax[6];
$dgy=$vystupmax[7];
$dfxs=$vystupmax[8];
$dfys=$vystupmax[9];
$dgxs=$vystupmax[10];
$dgys=$vystupmax[11];
$jakobihomatice=$vystupmax[12];
$jakobihomatices=$vystupmax[13];
$determinant=$vystupmax[14];
$charpoly=$vystupmax[15];
$charpoly =~ s/x/\\lambda/gs;
$stbod=$vystupmax[16].",".$vystupmax[17];
$vlastnicisla="\\\\ \\null \\qquad \$ \\lambda_1=".$vystupmax[18]."\$ \\\\ \\null \\qquad \$ \\lambda_2= ".$vystupmax[19]."\$";
$vlastnicislanum="\\\\ \\null \\qquad \$ \\lambda_1\\approx ".$vystupmax[20]."\$ \\\\ \\null \\qquad \$ \\lambda_2\\approx ".$vystupmax[21]."\$";

$testA =~ s/ //gs;
$testB =~ s/ //gs;

if (not (($testA eq "0") and ($testB eq "0"))) 
{
    $link="/maw/autsyst/autsyst.php?funkcef=".uri_escape($f)."&funkceg=".uri_escape($g)."&akce=1&lang=".$lang;

    $hlaskaSA="<h2 class='red'>".sprintf(gettext("Error: There is no stationary point at %s."),"[$xs,$ys]")."</h2>\n<hr>";
    $hlaskaSB=sprintf(gettext("Click %shere%s to find stationary points by computer."),"<a href=\"$link\">","</a>")."<hr>";
    saveoutput($hlaskaSA.$hlaskaSB); 
    die;
}

$determinant =~ s/ //gs;
if ($determinant eq "0")
{
    $hlaskaS="<h2 class='red'>".sprintf(gettext("Error: The Jacobian is zero, unable to clasify stationary point %s."),"[$xs,$ys]")."</h2><hr>";
    saveoutput($hlaskaS);
    die;
} 


#$ftex=`echo "$f" | formconv `;
#$gtex=`echo "$g" | formconv `;

&modifiedprint ("stbod", "$stbod");
&modifiedprint ("ftex", "$ftex");
&modifiedprint ("gtex", "$gtex");
&modifiedprint ("dfx", "$dfx");
&modifiedprint ("dfy", "$dfy");
&modifiedprint ("dgx", "$dgx");
&modifiedprint ("dgy", "$dgy");
&modifiedprint ("dfxs", "$dfxs");
&modifiedprint ("dfys", "$dfys");
&modifiedprint ("dgxs", "$dgxs");
&modifiedprint ("dgys", "$dgys");
&modifiedprint ("trace", "$vystupmax[22]");
&modifiedprint ("jakobihomatice", "$jakobihomatice");
&modifiedprint ("jakobihomatices", "$jakobihomatices");
&modifiedprint ("determinant", "$determinant");
&modifiedprint ("charpoly", "$charpoly");
&modifiedprint ("vlastnicisla", "$vlastnicisla");
&modifiedprint ("vlastnicislanum", "$vlastnicislanum");

if ($vystupmax[23]==1)      {$decision=gettext("saddle point");}
elsif ($vystupmax[23]==2)   {$decision=gettext("unstable node");}
elsif ($vystupmax[23]==3)   {$decision=gettext("unstable focus");}
elsif ($vystupmax[23]==4)   {$decision=gettext("stable node");}
elsif ($vystupmax[23]==5)   {$decision=gettext("stable focus");}
elsif ($vystupmax[23]==6)   {$decision=gettext("point of rotation or focus");}
elsif ($vystupmax[23]==7)   {$decision="\\textit{(".gettext("unable to find the answer").")}";}

&modifiedprint ("decision", "$decision");





