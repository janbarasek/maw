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

use List::Util qw[min max];

$xmin=-0.2;
$ymin=-0.2;
$xmax=0.2;
$xmin=-0.2;

$sumax=0;
$sumaxx=0;
$sumay=0;
$sumaxy=0;

@body = split (";",$inputdata);

open (DATAFILE, '>datafile');
open (TEXTABLE, '>table.tex');
open (PHPTABLE, '>table.php');

$n=0;

foreach $jedenbod (@body)
{
    $jedenbod=~s/ //g;
    @sour = split (",",$jedenbod);    
    if (($sour[0] eq "") || ($sour[1] eq ""))
    {
	saveoutput ("<b>".sprintf(gettext("Missing coordinate for point number %s."),($n+1))."</b><br><br>Error<BR>");
	exit;
    }
    print DATAFILE $sour[0]."  ".$sour[1]."\n";
    $xmin=min($xmin,$sour[0]);
    $xmax=max($xmax,$sour[0]);
    $ymin=min($ymin,$sour[1]);
    $ymax=max($ymax,$sour[1]);
    $n=$n+1;
    print TEXTABLE "$n. & \$ $sour[0]\$ &\$ $sour[1] 
\$&\$".($sour[0]*$sour[0])."\$ & \$".($sour[0]*$sour[1])."\$\\\\\n";
    print PHPTABLE "<tr><td>$n. </td><td> \$ $sour[0]\$ </td><td>\$ $sour[1] 
\$</td><td>\$".($sour[0]*$sour[0])."\$ </td><td> \$".($sour[0]*$sour[1])."\$</td></tr>\n";
    $sumax=$sumax+$sour[0];
    $sumay=$sumay+$sour[1];
    $sumaxx=$sumaxx+($sour[0]*$sour[0]);
    $sumaxy=$sumaxy+($sour[0]*$sour[1]);
}

    print TEXTABLE "\\hline\\hline \$\\Sigma\$&\\bf $sumax & \\bf 
$sumay 
& 
\\bf 
$sumaxx & \\bf $sumaxy \\\\\n";
    print PHPTABLE "<tr><th>\$\\Sigma\$</th><th> $sumax </th><th> $sumay </th><th> $sumaxx </th><th> $sumaxy </th></tr>\n";

close (DATAFILE);
close (TEXTABLE);

$det=($n*$sumaxx)-($sumax*$sumax);
$a=(($sumaxy*$n)-($sumax*$sumay))/$det;
$b=(($sumaxx*$sumay)-($sumax*$sumaxy))/$det;;
open (TEXEQ, '>eq.tex');
print TEXEQ "\\begin{equation*}\\boxed{\\begin{aligned} $sumaxx a+$sumax b &= $sumaxy\\\\\n";
print TEXEQ "$sumax a+$n b &= $sumay\\end{aligned}}\\end{equation*}\n";
print TEXEQ "\\bigskip\n";
print TEXEQ "\\begin{align*} a&=$a \\\\ b&=$b\\end{align*}";
close (TEXEQ);

open (PHPEQ, '>eq.php');
print PHPEQ "<div class=logickyBlok>";
print PHPEQ "\\begin{align} $sumaxx a+$sumax b &= $sumaxy\\\\\n";
print PHPEQ "$sumax a+$n b &= $sumay\\end{align}\n";
print PHPEQ "</div><div class=logickyBlok>\n";
print PHPEQ "\\begin{align} a&=$a \\\\ b&=$b\\end{align}";
print PHPEQ "</div>";
close (PHPEQ);

open (MYFILE, '>figure');
print MYFILE "\n";
print MYFILE "set xtics axis nomirror \n";
print MYFILE "set ytics axis nomirror \n";
print MYFILE "set samples 1000 \n";
print MYFILE "set noborder \n";
print MYFILE "set terminal epslatex color\n";
print MYFILE 'set output "graf.eps"'."\n";
print MYFILE "set zeroaxis linetype -1 \n";
print MYFILE "unset key\n";
print MYFILE "set xrange [$xmin:$xmax]\n";
print MYFILE "set yrange [$ymin:$ymax]\n";
print MYFILE "f(x)=a*x+b\n";
print MYFILE "fit f(x) 'datafile' via a,b\n";
print MYFILE "plot 'datafile' with points pointtype 3 pointsize 2,a*x+b linetype -1 linewidth 5\n ";
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
print MYFILE "f(x)=a*x+b\n";
print MYFILE "fit f(x) 'datafile' via a,b\n";
print MYFILE "plot 'datafile' with points pointtype 3 pointsize 2,a*x+b linetype -1 linewidth 5\n ";
close (MYFILE);

if ($requestedoutput eq "pdf") {system("gnuplot figure 2>/dev/null");}
if ($requestedoutput eq "svg") {system("gnuplot figuresvg 2>/dev/null");}

