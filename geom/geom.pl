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

open(VSTUP,"vstup");
open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");
print VYSTUP2 "<?php\n";

use Locale::gettext;
bindtextdomain("messages", "$mawhome/locale");
textdomain("messages");

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

@predfiltrem=<VSTUP>;

$f=@predfiltrem[0];
$g=@predfiltrem[1];
$meza=@predfiltrem[2];
$mezb=@predfiltrem[3];
$xmin=@predfiltrem[4];
$xmax=@predfiltrem[5];
$ymin=@predfiltrem[6];
$ymax=@predfiltrem[7];
$lang=@predfiltrem[8];
$akce=@predfiltrem[9];
$mawserver=@predfiltrem[10];
$MAWerr=@predfiltrem[11];

chomp($f);
chomp($g);
chomp($meza);
chomp($mezb);
chomp($xmin);
chomp($xmax);
chomp($ymin);
chomp($ymax);
chomp($lang);
chomp($akce);
chomp($mawserver);

$samplepoints=80;

########################################################
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
    $cislo =~ s/ //g;
    if ($cislo =~ /\\times/)
    {
	$cislo =~ s/\\times//;
	$cislo =~ s/{//;
	$cislo =~ s/}//;
	$cislo =~ s/10\^/e/;
    }
    return($cislo);
}

## podobne, ale argumentem je pole. pokud dojde k chybe, zapise se
## vystup do souboru output
sub maximatexlist
{
    my ($prikaz)=@_;
    $mawtexlist=$mawhome."/common/mawtexlist.mac";
    my $retezec=`$mawtimeout $maxima --batch-string=\"display2d:false; load(\\\"$mawtexlist\\\")\$ mawtexlist($prikaz);\"`;

    if (maw::maximaerror($retezec))    
    {
	saveoutput("</pre><span style='font-weight:bold'><span class='red'>".gettext("An error occurred when processing your input.")."<br>".gettext("Check your formulas (perhaps using Preview button) and report the problem if you think that your input is correct and should be processed without any error.")."</span></span><pre>\n"."$MAWerr<hr>\n".$retezec);
	die ();
    }

    @vystup = $retezec =~ /mawinta(.*?)mawinta/gs;
    $integralone=$vystup[1]; 
    $integralone =~ s/%//gs;
    
    return maw::maximaparseoutput($retezec);
}

##### vstup je funkce, xmin, xmax, pocet podintervalu, priznak kontroly mezi. 
##### vystup retezec { (x1,y_1),(x_2,y_2),atd (x_n,y_n) }
 sub cestamp
 {
     my ($funkce,$xdol,$xhor,$pocet,$flag)=@_;
     if ($flag!="1")
	 {
	     $xdol=$xdol-0.00000001;
	     $xhor=$xhor+0.00000001;
	 }
     my $delka=($xhor-$xdol)/$pocet;
     my $prikaz="";

     my $index=0;
     my $current=0;
     my $tempstring="";
     my $prikaz="";
     my @vystup=();
     my $vysledek="{";
     
     $index=0;
     do 
     {
	 $current=$xdol*($pocet-$index)/$pocet + $xhor*($index)/$pocet;
# 	$tempstring="float(".$funkce.")";
  	$tempstring="block(a:errcatch(float(".$funkce.")),if a=[] then %i elseif abs(a[1])>1000 then %i else a[1])";
 	$tempstring=~ s/exp/EXP/g;
 	$tempstring=~ s/x/($current)/g;
 	$tempstring=~ s/EXP/exp/g;
 	$prikaz=$prikaz.$tempstring;
 	if ($index<$pocet) {$prikaz=$prikaz." , ";}
 	$index++;
     }
     until ($index>$pocet);
     $prikaz=~ s/\n//g;
     @vystup=maximatexlist("[$prikaz]");

     $index=0;
     do 
     {
 	$current=$xdol+($delka*$index);
	if ($vystup[$index] !~ "i")
	{
            $tempfx=roundfx(prevedtex($vystup[$index]));
	    if ($flag=="1")
	    {
		if ($tempfx>$ymax) {$ymax=$tempfx};
		if ($tempfx<$ymin) {$ymin=$tempfx};
	    }
	    $vysledek=$vysledek."(".roundfx($current).",".$tempfx.")";
	    if ($index<$pocet) {$vysledek=$vysledek." , \n";}
	}
 	$index++;
     }
     until ($index>$pocet);
     $vysledek=$vysledek."}";
     return $vysledek;
 }


$savemaxima=$maxima;
$maxima=$maxima2;
########################################################

$a=$meza;
$b=$mezb;


if ($akce=~/0/)
{
    $S="S";
    $pi=" ";
    $maximapi="";
@vystupmax=maximatexlist("[(pfeformat:true,$f),
$g,
primfunction:ratsimp(integrate($f-($g),x)),
if numberp(ssearch(\\\"integrate\\\",string(primfunction))) then defint:primfunction else defint:radcan(ratsimp(ev(primfunction,x=$b)-ev(primfunction,x=$a))),
rectform(float (defint)), 
block(load(stringproc),testa:ratsimp($f-($g)),testb:($f-($g)),if (slength(string(testa))>(slength(string(testb)))) then shorter:testb else shorter:testa, print (\"mawinta\",shorter,\"mawinta\"),shorter), 
block(testa:ratsimp(integrate($f-($g),x)),testb:integrate($f-($g),x),if (slength(string(testa))>(slength(string(testb)))) then testb else testa), 
$meza, 
$mezb, 
float($meza), 
float($mezb),
quad_qags($f-($g),x,float($a),float($b))[1]]");
}
else 
{
    $S="V";
    $pi="\\pi";
    $maximapi="pi";
@vystupmax=maximatexlist("[(pfeformat:true,$f),
$g,
primfunction:ratsimp(integrate((($f)**2-($g)**2),x)),
if numberp(ssearch(\\\"integrate\\\",string(primfunction))) then defint:%pi*primfunction else defint:%pi*radcan(ratsimp(ev(primfunction,x=$b)-ev(primfunction,x=$a))),
rectform(float(defint)), 
block(load(stringproc),testa:ratsimp(($f)**2-($g)**2),testb:(($f)**2-($g)**2),if (slength(string(testa))>(slength(string(testb)))) then shorter:testb else shorter:testa, print (\"mawinta\",shorter,\"mawinta\"),shorter), 
block(testa:ratsimp(integrate(($f)**2-($g)**2,x)),testb:integrate(($f)**2-($g)**2,x),if (slength(string(testa))>(slength(string(testb)))) then testb else testa), 
$meza, 
$mezb, 
float($meza), 
float($mezb),
quad_qags(%pi*(($f)^2-($g)^2),x,float($a),float($b))[1]]");
}

$ftex=$vystupmax[0];
$gtex=$vystupmax[1];
$primfce=$vystupmax[2];
$integral=$vystupmax[3];
$integralfloat=roundres(prevedtex($vystupmax[4]));
$fminusgtex=$vystupmax[5];
$primfce=$vystupmax[6];

$atex=$vystupmax[7];
$btex=$vystupmax[8];
$a=roundres(prevedtex($vystupmax[9]));
$b=roundres(prevedtex($vystupmax[10]));


if ($integral=~/\\int/)
{
    $integral="???";
    $integralfloat=$vystupmax[11];
}
if ($primfce=~/\\int/)
{
    $primfce="???";
}


# print VYSTUP "\\par \\msgC".$S."{} ";


if ($gtex eq "0") 
{
    $commonTeX="\\\\ &= \\left[$primfce\\right]_{$atex}^{$btex}\\\\ &=$integral\\approx $integralfloat\\end{align*}";
    $commonTeX2="\\\\\\\\ &= \\left[$primfce\\right]_{$atex}^{$btex}\\\\\\\\ &=$integral\\approx $integralfloat\\end{align}";

    if ($akce=~/0/)
    {
	print VYSTUP "\\AreaA{\$f :y=$ftex\$}{\$$atex\$}{\$$btex\$}\\par","\\begin{align*}S&= \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} $ftex  \\,\\mathrm{d}x}".$commonTeX;
	print VYSTUP2 "AreaA('\$ f :y=$ftex\$','\$ $atex\$','\$ $btex\$');\n","\$computation='\\begin{align}S&= \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} $ftex  \\,\\mathrm{d}x}".$commonTeX2."';\n";
    }
    else
    {
	print VYSTUP "\\VolumeA{\$f :y=$ftex\$}{\$ $atex\$}{\$ $btex\$}\\par";
	print VYSTUP2 "VolumeA('\$f :y=$ftex\$','\$ $atex\$','\$ $btex\$');\n";
	print VYSTUP " \\begin{align*}V&=\\pi \\int_{$atex}^{$btex} \\left($ftex\\right)^2\\,\\mathrm{d}x\\\\&=\\pi \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} {$fminusgtex}   \\,\\mathrm{d}x}".$commonTeX;
	print VYSTUP2 "\$computation='\\begin{align}V&=\\pi \\int_{$atex}^{$btex} \\left($ftex\\right)^2\\,\\mathrm{d}x\\\\&=\\pi \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} {$fminusgtex}   \\,\\mathrm{d}x}".$commonTeX2."';\n";
    }
}
else
{
    $commonTeX="\\\\ &=$pi \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} $fminusgtex \\,\\mathrm{d}x}\\\\ &=$pi\\left[$primfce\\right]_{$atex}^{$btex}\\\\&=$integral\\approx ".$integralfloat."\\end{align*}";
    $commonTeX2="\\\\\\\\ &=$pi \\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int_{$atex}^{$btex} $fminusgtex \\,\\mathrm{d}x}\\\\\\\\ &=$pi\\left[$primfce\\right]_{$atex}^{$btex}\\\\\\\\ &=$integral\\approx ".$integralfloat."\\end{align}";
    if ($akce=~/0/)
    {
	$pi="";
	print VYSTUP "\\AreaB{\$f :y=$ftex\$}{\$g: y=$gtex\$}{\$$atex\$}{\$$btex\$}\\par","\\begin{align*}S&= {\\int_{$atex}^{$btex} \\left[ $ftex -\\left($gtex\\right) \\right] \\,\\mathrm{d}x}".$commonTeX;
	print VYSTUP2 "AreaB('\$ f :y=$ftex\$','\$g: y=$gtex\$','\$ $atex\$','\$ $btex\$');\n","\$computation='\\begin{align}S&= {\\int_{$atex}^{$btex} \\left[ $ftex -\\left($gtex\\right) \\right] \\,\\mathrm{d}x}".$commonTeX2."';\n";
    }
    else
    {
	$pi="\\pi ";
	print VYSTUP "\\VolumeB{\$f :y=$ftex\$}{\$g: y=$gtex\$}{\$$atex\$}{\$$btex\$}\\par","\\begin{align*}V&=\\pi {\\int_{$atex}^{$btex} \\left[ \\left($ftex\\right)^2 -\\left($gtex\\right)^2 \\right] \\,\\mathrm{d}x}".$commonTeX;
	print VYSTUP2 "VolumeB('\$f :y=$ftex\$','\$g: y=$gtex\$','\$$atex\$','\$$btex\$');\n","\$computation='\\begin{align}V&=\\pi {\\int_{$atex}^{$btex} \\left[ \\left($ftex\\right)^2 -\\left($gtex\\right)^2 \\right] \\,\\mathrm{d}x}".$commonTeX2."';\n";
    }
}

if ($primfce=~/\?\?\?/)
{
    print VYSTUP "\\intfailed";
    print VYSTUP2 "\$intfailed='".gettext("Maxima failed to find the primitive function.")."';\n";
}


$outputTeX="";
$maxima=$savemaxima;

if (not($gtex eq " 0 "))
{
    $outputTeX=$outpuTeXt."\n\\gfill[rgb(0.5,0.5,1)]\\lclosed\\begin{connect}\\curve".cestamp($f,$a,$b,$samplepoints,1)."\\reverse\\curve".cestamp($g,$a,$b,$samplepoints,1)."\\end{connect}";
    $outputTeX=$outputTeX."\\pen{2pt}\n\\draw\\curve".cestamp($f,$xmin,$xmax,$samplepoints,0);
    $outputTeX=$outputTeX."\\draw[red]\\curve".cestamp($g,$xmin,$xmax,$samplepoints,0);
}
else
{
    $outputTeX=$outputTeX."\n\\gfill[rgb(0.5,0.5,1)]\\lclosed\\begin{connect}\\curve".cestamp($f,$a,$b,$samplepoints,1)."\\lines{($b,0),($a,0)}\\end{connect}";
    $outputTeX=$outputTeX."\n\\pen{2pt}\n\\draw\\curve".cestamp($f,$xmin,$xmax,$samplepoints,0);
}

$outputTeX=$outputTeX."\n\\tlabelsep{3pt}\n\\tlabel[cl]($xmax,0){\$\\ \\ x\$}\n";
$outputTeX=$outputTeX."\n\\tlabel[tc](".roundx($a).",0){\\colorbox{white}{\$".$atex."\$}}\n";
$outputTeX=$outputTeX."\n\\tlabel[tc](".roundx($b).",0){\\colorbox{white}{\$".$btex."\$}}\n";

$outputTeX=$outputTeX."\n\\pen{0.7pt}\\axes\n\\end{mfpic}";

$magx=12/($xmax-$xmin);
$magy=12/($ymax-$ymin);
$outputTeX="\\par\\information\\par\\begin{mfpic}[$magx][$magy]{$xmin}{$xmax}{$ymin}{$ymax}\n\\tlabel[br](".$xmax.",".$ymin."){\\vbox{\\hbox to 0 pt{\\hss\\small \\( ".roundx($xmin)."\\leq x\\leq ".roundx($xmax)." \\)}\\hbox to 0 pt{\\hss\\small \\( ".roundx($ymin)."\\leq y\\leq ".roundx($ymax)." \\)}}}".$outputTeX;

print VYSTUP $outputTeX;

print VYSTUP "\\let\\test\\relax";
print VYSTUP2 "?>\n";
