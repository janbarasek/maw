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

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

use List::Util qw[min max];


open(HLAVA,"data");
open(VYSTUP,">data.tex");
open(VYSTUPPHP,">data.php");
print VYSTUPPHP "<?php\n";
open (DATAFILE, '>datafile');

@pole=<HLAVA>;


$xmin=-0.2;
$ymin=-0.2;
$xmax=0.2;
$xmin=-0.2;

chomp($pole[0]);
$pole[0]=~ s/ //g;

sub nejednicka
{
    my ($testnumber)=@_;
    $vystup="";
    if (not($testnumber eq "1")) {$vystup=$testnumber;}
    return $vystup;
}

sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"tex($prikaz);\"`;
    if (maw::maximaerror($retezec))
    {
	saveoutput ("<h2 class='red'>".gettext("Error in math. Check your input.")."</h2>\n");
	if ($retezec=~/Duplicated abscissas are not allowed/)
{	saveoutput ("<b class='red'>".gettext("All x-coordiantes have to be mutually different numbers.")."</b><br>\n");
}
	saveoutput("<pre>".$retezec."</pre>");
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}


#print $pole[0],"\n";
$bodymaximalagr=$pole[0];
$bodymaximalagr=~ s/;/],[/g;

@body = split (";",$pole[0]);
$vsechno=$pole[0]; $vsechno =~ s/;/,/g;
@xy = split(",",$vsechno);

#open (DATAFILE, '>datafile');
#open (TEXTABLE, '>table.tex');

$n=@body-1;
@citatel=();
@jmenovatel=();

$i=0;
foreach $jedenbod (@body)
{
    $jedenbod=~s/ //g;
    @sour = split (",",$jedenbod);    
    if (($sour[0] eq "") || ($sour[1] eq ""))
    {
	system ("echo \"Error: error in the computation. Bad input? Check the notation of your data.<BR>\n\" >> output");
	
	$tempmsg=sprintf(gettext("Invalid point number %s."),($i+1));
	system ("echo \"<b>$tempmsg</b>\" >> output");
	die;
    }
    $xmin=min($xmin,$sour[0]);
    $xmax=max($xmax,$sour[0]);
    $ymin=min($ymin,$sour[1]);
    $ymax=max($ymax,$sour[1]);
    $i=$i+1;
    print DATAFILE $sour[0]."  ".$sour[1]."\n";
}


$ymax=1.4*$ymax;
$xmax=1.4*$xmax;
$ymin=1.4*$ymin;
$xmin=1.4*$xmin;

$i=0;

for ($i=0;$i <= $n;$i++)
{
#    print "a",$i;
    if($xy[2*$i+1] eq "0") 
    {
	$citatel[$i]="0";
	$jmenovatel[$i]="1";
    }
    else
    {
	print "";
	for ($j=0;$j<=$n;$j++)
	{
	    if (not($i eq $j))
	    {
		if ($xy[2*$j]>= 0)
		{
		    $citatel[$i]=$citatel[$i]."(x-".($xy[2*$j]).")";
		    $jmenovatel[$i]=$jmenovatel[$i]."(".($xy[2*$i])."-".($xy[2*$j]).")";
		}
		else 
		{
		    $citatel[$i]=$citatel[$i]."(x+".abs($xy[2*$j]).")";
		    $jmenovatel[$i]=$jmenovatel[$i]."(".($xy[2*$i])."+".abs($xy[2*$j]).")";
		}		
	    }
	}
	$citatel[$i]=~ s/\)\(/)*(/g;
	$jmenovatel[$i]=~ s/\)\(/)*(/g;
    }
}

## pocet bodu a stupen polynomu
print VYSTUP "\\def\\pocetbodu{".($n+1)."}\n";
print VYSTUP "\\def\\stupen{".($n)."}\n";
print VYSTUPPHP "\$pocetbodu='".($n+1)."';\n";
print VYSTUPPHP "\$stupen='".($n)."';\n";

## tabulka se zadanim
$radekA=" \$ i \$ ";
$radekB="\\\\\\hline\\hline \$ x_i \$ ";
$radekC="\\\\\\hline \$ y_i \$ ";
$radekAP="<th> \$ i \$ </th>";
$radekBP="<th> \$ x_i \$ </th>";
$radekCP="<th> \$ y_i \$ </th>";
$hlavicka="|c|";
for ($i=0; $i<=$n;$i++)
{
    $hlavicka=$hlavicka."c|";
    $radekA=$radekA." & \$ $i \$ ";
    $radekB=$radekB." & \$ ".$xy[2*$i]." \$ ";
    $radekC=$radekC." & \$ ".$xy[2*$i+1]." \$ ";
    $radekAP=$radekAP." <th> $i  </th>";
    $radekBP=$radekBP." <td> \$ ".$xy[2*$i]." \$ </td>";
    $radekCP=$radekCP." <td> \$ ".$xy[2*$i+1]." \$ </td>";

}


print VYSTUP "\\def\\zadani{\\begin{tabular}{\n".$hlavicka."}\\hline\n".$radekA."\n".$radekB."\n".$radekC."\n\\\\\\hline\\end{tabular}}\n";
print VYSTUPPHP "\$zadani='<table><tr>".$radekAP."</tr>\n<tr>".$radekBP."</tr>\n<tr>".$radekCP."</tr></table>';\n";



## formalni tvar Lagrangeova polynomu
$Lpol="";
for ($i=0; $i<=$n;$i++)
{
    if (not($xy[2*$i+1] eq "0"))
	{
	    if ($xy[2*$i+1]<0) 
	    {
		$Lpol=$Lpol."-".nejednicka(abs($xy[2*$i+1]))."l_".$i."(x)";
	    }
	    else
	    {
		if (not($Lpol eq "")) {$Lpol=$Lpol."+";}
		$Lpol=$Lpol.nejednicka($xy[2*$i+1])."l_".$i."(x)";
	    }
	}
}

if ($Lpol eq "")
{
    system("echo \"<b class='red'>".gettext("At least one y-coordinate has to be nonzero.")."</b>\">>output");
    die;
}
print VYSTUP "\\def\\L{L(x)=$Lpol}\n";
print VYSTUPPHP "\$L='L(x)=$Lpol';\n";


$retezec="block(load(interpol),simp:false,pfeformat:true,ev(expand(lagrange([[$bodymaximalagr]])),simp))";

for ($i=0;$i<=$n;$i++)
{
#    if ($i>0) 
    {$retezec=$retezec.","}
    $retezec=$retezec."$citatel[$i],$jmenovatel[$i],ev(expand($citatel[$i]),simp), ev($jmenovatel[$i],simp),ev(expand($citatel[$i]/($jmenovatel[$i])),simp)"
}

#print $retezec;

@vystup=maximatexlist("[$retezec]");

$retezec="";
$retezecP="";
for ($i=0;$i<=$n;$i++)
{
    if (not($xy[2*$i+1] eq "0"))
    {
	$retezec=$retezec."\\par\$l_{$i}(x)=";
	$retezec=$retezec."\\frac{$vystup[5*$i+1]}{$vystup[5*$i+2]}=";
	$retezec=$retezec."\\frac{$vystup[5*$i+3]}{$vystup[5*$i+4]}=";
	$retezec=$retezec."{$vystup[5*$i+5]}";
	$retezec=$retezec."\$";
	$retezecP=$retezecP."<p>\$l_{$i}(x)=";
	$retezecP=$retezecP."\\frac{$vystup[5*$i+1]}{$vystup[5*$i+2]}=";
	$retezecP=$retezecP."\\frac{$vystup[5*$i+3]}{$vystup[5*$i+4]}=";
	$retezecP=$retezecP."{$vystup[5*$i+5]}";
	$retezecP=$retezecP."\$</p>\n";
    }
}
print VYSTUP "\\long\\def\\pompol{$retezec}\n";
print VYSTUPPHP "\$pompol='$retezecP';\n";


$retezec="";
for ($i=0;$i<=$n;$i++)
{
    if (not($xy[2*$i+1] eq "0"))
    {
	if ($xy[2*$i+1]<0)
	{
	    $retezec=$retezec."-".nejednicka(abs($xy[2*$i+1]));
	}
	else
	{
	    if (not($retezec eq "")){$retezec=$retezec."+";}
	    $retezec=$retezec.nejednicka($xy[2*$i+1]);
	}
	$retezec=$retezec."{\\left($vystup[5*$i+5]\\right)}";
    }
}


print VYSTUP "\\def\\vysledek{L(x)=$retezec=\\boxed{$vystup[0]}}\n";
print VYSTUPPHP "\$vysledek='\\begin{aligned}L(x)&=$retezec \\\\\\\\ L(x)&=\\boxed{\\displaystyle $vystup[0]}\\end{aligned}';\n";

print VYSTUPPHP "?>\n";

if ($requestedoutput eq "html") {exit;}


if ( ($requestedoutput eq "pdf") || ($requestedoutput eq "svg") )
{

$retezec="block(load(interpol),simp:false,ev(lagrange([[$bodymaximalagr]]),simp))";

$funkce=`$maxima --batch-string="block(display2d:false,load(interpol),lagrange([[$bodymaximalagr]]));"`;

$funkce =~ s/\n//gs;
$funkce =~ s/.*\(%o1\)//g;

$funkcegnuplot=`$mawtimeout echo "$funkce" | $formconv_bin -r -O gnuplot`;
chomp($funkcegnuplot);	

open (MYFILE, '>figure');
print MYFILE "\n";
print MYFILE "set xtics axis nomirror \n";
print MYFILE "set ytics axis nomirror \n";
print MYFILE "set samples 1000 \n";
print MYFILE "set noborder \n";
print MYFILE "set term postscript eps color \n";
print MYFILE 'set output "graf.eps"'."\n";
print MYFILE "set zeroaxis linetype -1 \n";
print MYFILE "unset key\n";
print MYFILE "set xrange [$xmin:$xmax]\n";
print MYFILE "set yrange [$ymin:$ymax]\n";
print MYFILE "plot $funkcegnuplot linetype -1 linewidth 5,'datafile' with points pointtype 7 pointsize 2\n ";
close (MYFILE);

open (MYFILE, '>figuresvg');
print MYFILE "\n";
print MYFILE "set xtics axis nomirror \n";
print MYFILE "set ytics axis nomirror \n";
print MYFILE "set samples 1000 \n";
print MYFILE "set noborder \n";
print MYFILE "set term svg font 'Verdana,9' rounded solid\n";
print MYFILE 'set output "graf.svg"'."\n";
print MYFILE "set zeroaxis linetype -1 \n";
print MYFILE "unset key\n";
print MYFILE "set xrange [$xmin:$xmax]\n";
print MYFILE "set yrange [$ymin:$ymax]\n";
print MYFILE "plot $funkcegnuplot linetype -1 linewidth 5,'datafile' with points pointtype 7 pointsize 2\n ";
close (MYFILE);

if ($requestedoutput eq "pdf") {system("gnuplot figure 2>/dev/null");}
if ($requestedoutput eq "svg") {system("gnuplot figuresvg 2>/dev/null");}

}

exit;
