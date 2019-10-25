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

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 

open (PHP, '>data.php');
open (DATA, '>data');
print PHP "<?php\n";

#$vstupfunkce="sin(x)/x";
#$vstupa="1";
#$vstupb="sqrt(3)";
#$n="10";

open(VSTUP,"zadani");
@data=<VSTUP>;

$vstupfunkce=$data[0];
$vstupa=$data[1];
$vstupb=$data[2];
$n=$data[3];

if ($n>20) {$n=20;}

$funkce=$vstupfunkce;


### zaokrouhleni na tri desetinna mista
sub roundx
{
    my ($cislo) = shift;
    return sprintf("%.3f",$cislo);
}

sub roundfx
{
    my ($cislo) = shift;
    return sprintf("%.6f",$cislo);
}

sub roundres
{
    my ($cislo) = shift;
    return sprintf("%.10f",$cislo);
}

### prevod retezce "3.14 \times 10^{-15}" na tvar "3.14e-15"
sub prevedtex
{
    my ($cislo) = shift;
    if ($cislo =~ /\\times/)
    {
	$cislo =~ s/\\times//;
	$cislo =~ s/ //g;
	$cislo =~ s/{//;
	$cislo =~ s/}//;
	$cislo =~ s/10\^/e/;
    }
    return($cislo);
}

## argument se posle maxime, vysledek se prevede to TeXu a odchyti.
sub maximatex
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"tex($prikaz);\"`;
    
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$(.*)\$\$/gs;
    return ($vystup[0]);
}

## podobne, ale argumentem je pole. pokud dojde k chybe, zapise se
## vystup do souboru output
sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"tex($prikaz);\"`;
    if ((maw::maximaerror($retezec)) || ($retezec=~/failed to converge/))    
    {
        $retezec =~ s/`//gs;
	saveoutput(gettext("Error in the function or bounds for integration or integration routine failed for unknown reason.")."<BR>\n".$retezec);
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}

## prevedeme meze na desetinna mista a do texu, funkci do texu,
## vypocteme urcity integral, neurcity integral a numerickou
## aproximaci

@poleab=maximatexlist("[float($vstupa),float($vstupb),$funkce,$vstupa,$vstupb,(primfunction:integrate($funkce,x),if numberp(ssearch(\\\"integrate\\\",string(primfunction))) then primfunction else ratsimp(ev(primfunction,x=$vstupb)-ev(primfunction,x=$vstupa))),quad_qags($funkce,x,float($vstupa),float($vstupb))[1],ratsimp(primfunction)]");
$a=$poleab[0];
$b=$poleab[1];
$texfunkce=$poleab[2];
$texa=$poleab[3];
$texb=$poleab[4];
$integral=$poleab[5];
$integralnum=$poleab[6];
$primfce=$poleab[7];


## pokud meze obsahuji nasobek mocninou deseti, prevedeme na tvar s napr. e-2

$scia=prevedtex($a); 
$scib=prevedtex($b);

## povedlo se najit neurcity integral?
if ($integral=~/\\int/)
{
    $integral="???";
}
if ($primfce=~/\\int/)
{
    $primfce="???";
}

## delka podintervalu
$h=($scib-$scia)/$n;


## vytiskne zadani 
print "\\msgA{$texfunkce}{$a}{$texa}{$b}{$texb}{$n}{$h}";
print PHP "\$msgA1='$texfunkce';\n\$msgA2='$a';\n\$msgA3='$texa';\n\$msgA4='$b';\n\$msgA5='$texb';\n\$msgA6='$n';\n\$msgA7='$h';\n";


$ymax=0;
$ymin=0;


# sestavime pole s hodnotami ktere je potreba vypocitat a posleme maxime
$i=0;
do 
{
    $tempcislo=$scia+($h*$i);
    $tempfce="float(ev($funkce,numer,x=$tempcislo))";
#    $tempfce=~ s/exp/EXP/g;
#    $tempfce=~ s/x/($tempcislo)/g;
#    $tempfce=~ s/EXP/exp/g;
    $tempprikaz=$tempprikaz.$tempfce;
    if ($i<$n) {$tempprikaz=$tempprikaz." , ";}
    $i++;
}
until ($i>$n);
$tempprikaz=~ s/\n//g;

@polehodnot=maximatexlist("[$tempprikaz]");


## vytiskneme potrebne hodnoty do tabulky
$i=0;
$S=0;
$allpoints="";
do 
{
    $temp=$scia+($h*$i);
    $m=2;
    if ($i==0 or $i==$n){$m=1;}
    $fcnihodnota = prevedtex($polehodnot[$i]);
    if ($ymin>$fcnihodnota) {$ymin=$fcnihodnota;}
    if ($ymax<$fcnihodnota) {$ymax=$fcnihodnota;}
    $prirustek=$m*$fcnihodnota;
    $S=$S+$prirustek;
    if ($i==0)
    {
	print "\\bigskip\n\\begin{tabular}{|c|c|c|c|c|}\n\\hline\n\$i\$ & \$x_i\$ & \$f(x_i)\$ & \$m\$ & \$ mf(x_i)\$ \\\\\n\\hline\n";
	print PHP "\$outputtable='<par><table><tr><th>\$i\$ </th><th> \$x_i\$ </th><th> \$f(x_i)\$ </th><th> \$ m\$ </th><th> \$ mf(x_i)\$</th></tr>\n";
    }
    print "\$$i\$   &   \$".(roundx($temp))."\$  &  \$".roundfx($fcnihodnota)."\$   &   \$ $m\$  &  \$".roundfx($prirustek)."\$ \\\\ \n";
    print PHP "<tr><th>\$$i\$ </th><td> \$".(roundx($temp))."\$ </td><td> \$".roundfx($fcnihodnota)."\$ </td><td> \$$m\$ </td><td> \$".roundfx($prirustek)."\$</td></tr> \n";
    $allpoints=$allpoints."; ".roundx($temp).", ".roundfx($fcnihodnota);
    $i++;
}
until ($i>$n);

# soucet a konec tabulky
print "\\hline&&&& \\vrule width 0 pt height 14pt\\hfill\\hbox to 0 pt{\\hss \\colorbox{white}{\\strut\$ \\Sigma=",(roundres($S)),"\$}}\\\\\n\\hline";
print "\\end{tabular}\n";
print PHP "<tr><th align=\"right\" colspan=\"5\" >\$ \\Sigma=",(roundres($S)),"\$</th></tr>";
print PHP "</table>';\n";

print PHP "\$ymax='$ymax';\n";
print PHP "\$ymin='$ymin';\n";
print PHP "\$allpoints='$allpoints';\n";



# dalsi varianty vypoctu - pro srovnani
print "\\fboxsep 4pt\n\\begin{equation*}\n\\boxed{\n\\int_{$texa}^{$texb} $texfunkce \\,\\mathrm{d}x\\approx{h\\over 2}\\Sigma =\n",roundres($S*$h/2),"\n}\n\\end{equation*}\n";
print PHP "\$result='\\begin{equation*}\n\\int_{$texa}^{$texb} $texfunkce \\,\\mathrm{d}x\\approx{h\\over 2}\\Sigma =\n",roundres($S*$h/2),"\\end{equation*}';\n";

print PHP "\$resultB='\$ \\int_{$texa}^{$texb} $texfunkce \\,\\mathrm{d}x=\\left\[$primfce\\right\]\^{$texb}_{$texa}=$integral \$';";

print PHP "\$resultC='\$ \\int_{$texa}^{$texb} $texfunkce \\,\\mathrm{d}x \\approx ".$integralnum." \$';";

if ($primfce=~/\?\?\?/)
{
    print "\\intfailed";
    print PHP "\$intfailed='1';";
}


if ($requestedoutput eq "html") {print PHP "\$test=\"OK\"; ?>\n";
exit;}

# pokud funkce neni zaporna a je malo intervalu, nakreslime obrazek
if (($n<=10) && ($ymax>0) && ($ymin>=0) && ($scia<$scib))
{

    $xpicmin=$scia-(($scib-$scia)/10);
    $xpicmax=$scib+(($scib-$scia)/10);
    
    $ypicmin=-0.1*$ymax;
    $ypicmax=1.1*$ymax;
    
    $magx=16/($xpicmax-$xpicmin);
    $magy=16/($ypicmax-$ypicmin);
    # if magy is too large, metapost gives error(arithmetic overflow)
    # hence we draw no picture in this case
    if (($magy>1000)||($ypicmax>1000)) {print "\\let\\test\\relax"; die;}
    
    print "\\par\n\\vbox{\\textbf{\\msgD}\\par\n\\begin{mfpic}[$magx][$magy]{$xpicmin}{$xpicmax}{$ypicmin}{$ypicmax}\n";
    
    $i=0;
    $barva=0;
    do
    {
	print "\\gfill[";
	if ($barva==0){
	    $barva=1;
	    print "rgb(1,0.3,0.3)";
	} else {
	    $barva=0;
	    print "rgb(1,0.5,0.5)";
	}
	print "]\\lclosed\\lines{";
	print "\n(",roundfx($scia+($h*$i)),",0),";
	print "\n(",roundfx($scia+($h*$i)),",",roundfx(prevedtex($polehodnot[$i])),"),";
	$j=$i+1;
	print "\n(",roundfx($scia+($h*$j)),",",roundfx(prevedtex($polehodnot[$j])),"),";
	print "\n(",roundfx($scia+($h*$j)),",0)}\n";
	$i++;
    }
    until ($i>($n-1));

    print "\\axes\n\\pen{1pt}\n";

    print "\\draw\\curve{";

    $n=50;
    $h=($xpicmax-$xpicmin)/$n;
    $tempprikaz="";

    $i=0;
    do 
    {
	$tempcislo=$xpicmin+($h*$i);
	$tempfce="float(".$funkce.")";
	$tempfce=~ s/exp/EXP/g;
	$tempfce=~ s/x/($tempcislo)/g;
	$tempfce=~ s/EXP/exp/g;
	$tempprikaz=$tempprikaz.$tempfce;
	if ($i<$n) {$tempprikaz=$tempprikaz." , ";}
	$i++;
    }
    until ($i>$n);
    $tempprikaz=~ s/\n//g;
    @polehodnot=maximatexlist("[$tempprikaz]");
    
    $i=0;
    do 
    {
	$tempcislo=$xpicmin+($h*$i);
	if ($polehodnot[$i] !~ "i")
	{
	    print "(";
	    print roundfx($tempcislo);
	    print ",";
	    print roundfx(prevedtex($polehodnot[$i]));
	    print ")";
	    if ($i<$n) {print" , \n";}
	}
	$i++;
    }
    until ($i>$n);
    print "}";

#    print "\\draw\\function{$xpicmin,$xpicmax,",(($xpicmin-$xpicmax)/50),"}{$funkce}";
    print "\\tlabelsep{3pt}\n\\tlabel[cl]($xpicmax,0){\$\\ \\ x\$}\n";
    print "\\tlabel[tc](",roundx($scia),",0){\\colorbox{white}{\$",$texa,"\$}}\n";
    print "\\tlabel[tc](",roundx($scib),",0){\\colorbox{white}{\$",$texb,"\$}}\n";
    print "\\end{mfpic}}";

}

print "\\let\\test\\relax";

    


