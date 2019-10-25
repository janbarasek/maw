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

open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");
print VYSTUP2 "<?php \n";
open(NAVRKOD,">uspech");
$navrkod="postup bez problemu";
#1 - success
#2 - not true that the function is f(x)=u/v^m and u is a polynomial

$funkcegnuplot=`$mawtimeout echo "$f" | $formconv_bin -r -O gnuplot`;
chomp($funkcegnuplot);	
$kresleni="plot $funkcegnuplot linewidth 10";

sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"load(\\\"$mawhome/common/maw_solve.mac\\\")\$ tex($prikaz);\"`;
    if ((maw::maximaerror($retezec)))    
    {
	$retezec =~ s/\\,/ /g; 
	@vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
	$vystup[0] =~ s/,/\n/g;
	saveoutput ("<h3>".gettext("Error in the computation.")." ".gettext("Bad input?")." ".gettext("Check the notation of your function.")."</h3>\n$retezec \n$vystup[0]");
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/([0-9] *)\\,( *[0-9])/\1\\cdot \2/gs;
    $retezec =~ s/\\,/ /gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    return $vystup[0];
}


sub maximalist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"[hranice,$prikaz,hranice];\"`;
    if ((maw::maximaerror($retezec)))    
    {
	$retezec =~ s/\\,/ /g; 
	@vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
	$vystup[0] =~ s/,/\n/g;
	saveoutput ("<h3>".gettext("Error in the computation.")." ".gettext("Bad input?")." ".gettext("Check the notation of your function.")."</h3>\nError<BR>\n".$retezec."\n\n".$vystup[0]);
	die ();
    }
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/ /gs;
    $retezec =~ s/\\log/\\ln/gs;
    return $retezec;
}

sub filtr
{
    my($radek)=@_;
    $radek=~s/\\left\[//g;
    $radek=~s/\\right\]//g;
    $radek=~s/\\,//g;
    $testradek=$radek;
    $testradek =~ s/ //g;
#    print "x",$testradek,"x";
    if ($testradek eq ",{\\itnumericky},") {return "";}
    @vysledek=split(", *{\\\\it numericky} *,",$radek);
    @reseni=split(',',$vysledek[0]);
    @reseniN=split(',',$vysledek[1]);
#    print @reseni,"<br>";
#    print @reseniN,"<br><br><br>";
    $vystup="";
    $i=0;
    $j=0;
    $pocetreseni=@reseniN;    
    foreach $radektest (@reseniN)
    {
#	print $i;
	@korenN=split('=',$reseniN[$j]);
	@koren=split('=',$reseni[$j]);
#	print $korenN[1];
	if (#($koren[0] =~ " x") and 
	    ($koren[1] !~ "x"))
	{
	    if ($radektest !~ "i")
	    {
		$i=$i+1;
		if ($i>1) {$vystup=$vystup.",\\ ";}
#		print "AAAAA",$koren[1]."<br><br>"; length($koren[1])
		if (length($koren[1])>200) {$koren[1]="\\cdots";}
		$vystup=$vystup."x_".$i."=".$koren[1];
	    }
	}
	$j=$j+1;
    }
#    print "*",$vystup,"*";
    return $vystup;
}


#if diff(gcd(jmenovatel,diff(jmenovatel,x)))=0 then v:expand(jmenovatel) else v:ratsimp(jmenovatel/gcd(jmenovatel,diff(jmenovatel,x))),

### najde spolecny delitel koeficientu polynomu
#block(stupen:hipow(p,x),seznam:makelist(coeff(p,x,i),i,0,stupen),nasobek:seznam[stupen+1],for i:1 step 1 while i<(1+stupen) do nasobek:gcd(seznam[i],nasobek),nasobek);

$vystupmax=maximatexlist("[f(x):=$f,
f(x),
f2(x):=xthru(f(x)),
f2(x),
derivace:factor(ratsimp(diff(f2(x),x))),
num(derivace),
block(citatel:num(f2(x)),p:expand(citatel),nazkraceni:block(stupen:hipow(p,x),seznam:makelist(coeff(p,x,i),i,0,stupen),nasobek:seznam[stupen+1],for i:1 step 1 while i<(1+stupen) do nasobek:gcd(seznam[i],nasobek),nasobek),jmenovatel:denom(f2(x))),
v:ratsimp(jmenovatel/gcd(jmenovatel,diff(jmenovatel,x))),
for i: 0 step 1 while divide(jmenovatel,(v)^i,x)[2]=0 do m:i,
m,
block(pom:denom(fullratsimp(f2(x)*v^m)),u:num(fullratsimp(f2(x)*v^m))/nasobek,if denom(u)#1 then u:ratsimp(u),u),
block(load(linearalgebra),if not(polynomialp(u,[x])) then u:ratsimp(u), if polynomialp(u,[x]) then block(if diff(nasobek/pom,x)=0 then 1 else 0) else 0),
block(if expand(citatel=u*nasobek) and expand(jmenovatel=pom*v^m) then u:citatel/nasobek,if denom(u)#1 then u:ratsimp(u),dera:diff(u,x)),
derb:diff(v,x),
2*m,
v^(m-1),
v^(m+1),
expand(dera*v-u*m*derb),
v^m,
u/(v^m),
nasobek/pom,
hranice,
derivace2:factor(ratsimp(diff(f2(x),x,2))),
num(derivace2),
d2f2(x):=nasobek/pom*expand(dera*v-u*m*derb)/v^(m+1),
1,
d2derivace:factor(ratsimp(diff(f2(x),x,2))),
num(d2derivace),
block(d2citatel:num(d2f2(x)),d2p:expand(d2citatel),d2nazkraceni:block(d2stupen:hipow(d2p,x),d2seznam:makelist(coeff(d2p,x,i),i,0,d2stupen),d2nasobek:d2seznam[d2stupen+1],for i:1 step 1 while i<(1+d2stupen) do d2nasobek:gcd(d2seznam[i],d2nasobek),d2nasobek),d2jmenovatel:denom(d2f2(x))),
if diff(gcd(d2jmenovatel,diff(d2jmenovatel,x)))=0 then d2v:expand(d2jmenovatel) else d2v:ratsimp(d2jmenovatel/gcd(d2jmenovatel,diff(d2jmenovatel,x))),
for i: 0 step 1 while divide(d2jmenovatel,(d2v)^i,x)[2]=0 do d2m:i,
d2m,
block(d2pom:denom(fullratsimp(d2f2(x)*d2v^d2m)),d2u:num(fullratsimp(d2f2(x)*d2v^d2m))/d2nasobek,if denom(d2u)#1 then d2u:ratsimp(d2u),d2u),
block(load(linearalgebra),if not(polynomialp(d2u,[x])) then d2u:ratsimp(d2u), if polynomialp(d2u,[x]) then block(if diff(d2nasobek/d2pom,x)=0 then 1 else 0) else 0),
block(if expand(d2citatel=d2u*d2nasobek) and expand(d2jmenovatel=d2pom*d2v^d2m) then d2u:d2citatel/d2nasobek,if denom(d2u)#1 then d2u:ratsimp(d2u),d2dera:diff(d2u,x)),
d2derb:diff(d2v,x),
2*d2m,
d2v^(d2m-1),
d2v^(d2m+1),
expand(d2dera*d2v-d2u*d2m*d2derb),
d2v^d2m,
d2u/(d2v^d2m),
d2nasobek/d2pom,
hranice,
nulovebody:maw_solve_in_domain(f(x),f(x),x),
numericky,
ev(rectform(nulovebody),numer),
hranice,
stacbody:maw_solve_in_domain(derivace,f(x),x),
numericky,
ev(rectform(stacbody),numer),
hranice,
kritbody:maw_solve_in_domain(derivace2,f(x),x),
numericky,
ev(rectform(kritbody),numer),
hranice,
nesp:maw_solve(jmenovatel=0,x),
numericky,
ev(rectform(nesp),numer),
hranice,
block(zlomek:ratsimp(f2(x)),podil:divide(num(zlomek),denom(zlomek),x)[1], if hipow(podil,x)<2 then asymptota:podil else block(asymptota:0,nic)),
hranice,
block(simp:false,stardisp:true),
if derb=1 then dera*v^m-u*(m)*ev(v^(m-1),simp) else dera*v^m-u*(m)*ev(v^(m-1),simp)*derb,
if derb=1 then dera*v-u*m else dera*v-m*u*derb,
if derb=1 then dera*v-u else dera*v-u*derb,
block(ev(zlomek:nasobek/pom,simp), if zlomek=1 then block(if m=1 then (u/v) else u/(v^m)) else block(if m=1 then zlomek*(u/v) else zlomek*(u/(v^m)))) ,
hranice,
block(simp:false,stardisp:true),
if d2derb=1 then d2dera*d2v^d2m-d2u*(d2m)*ev(d2v^(ev(d2m-1,simp)),simp) else d2dera*d2v^d2m-d2u*(d2m)*ev(d2v^(ev(d2m-1,simp)),simp)*d2derb,
if d2derb=1 then d2dera*d2v-d2u*d2m else d2dera*d2v-d2m*d2u*d2derb,
if d2derb=1 then d2dera*d2v-d2u else d2dera*d2v-d2u*d2derb,
block(ev(d2zlomek:d2nasobek/d2pom,simp), if d2zlomek=1 then block(if d2m=1 then (d2u/d2v) else d2u/(d2v^d2m)) else block(if d2m=1 then d2zlomek*(d2u/d2v) else d2zlomek*(d2u/(d2v^d2m)))),
hranice,
asymptota, 
hranice,
ev(rectform(nesp),simp,numer),
hranice,
is(fullratsimp(f(x)+f(-x))=0),
hranice,
is(fullratsimp(f(x)-f(-x))=0)
]");

@pole=split(", *{\\\\it hranice} *,",$vystupmax);
#$pole[0] =~ s/,/\n/gs;print "<pre>",$pole[0];die;
#$vystupmax =~ s/,/\n/gs;print "<pre>",$vystupmax;die;

$funkcetex=`echo "$f" | formconv `;


print VYSTUP "\\def\\funkce{$funkcetex}\n";
print VYSTUP2 "\$funkceHTML = '$funkcetex ';\n";
$koreny=filtr($pole[2]);
print VYSTUP "\\def\\nuly{$koreny}\n";
print VYSTUP2 "\$nulyHTML = '$koreny ';\n";
$koreny=filtr($pole[3]);
print VYSTUP "\\def\\stac{$koreny}\n";
print VYSTUP2 "\$stacHTML = '$koreny ';\n";
$koreny=filtr($pole[4]);
print VYSTUP "\\def\\krit{$koreny}\n";
print VYSTUP2 "\$kritHTML='$koreny ';\n";
$koreny=filtr($pole[5]);
print VYSTUP "\\def\\nesp{$koreny}\n";
print VYSTUP2 "\$nespHTML = ' $koreny ';\n";
if ($pole[6]=~/nic/)
{
    print VYSTUP "\\def\\asymptota{}\n";
    print VYSTUP2 "\$asymptotaHTML = '';\n";
}
else
{
    print VYSTUP "\\def\\asymptota{y=$pole[6]}\n";
    print VYSTUP2 "\$asymptotaHTML='y=$pole[6]';\n";
}

if ($pole[11]=~/true/) {print VYSTUP "\\let\\parity\\odd"; print VYSTUP2 "\$parityHTML='odd';";}
if ($pole[12]=~/true/) {print VYSTUP "\\let\\parity\\even"; print VYSTUP2 "\$parityHTML='even';";}

## asymptota
$asout=maximalist("block(display2d:false,f2(x):=xthru($f),zlomek:ratsimp(f2(x)),podil:divide(num(zlomek),denom(zlomek),x)[1], if hipow(podil,x)<2 then asymptota:podil else 0,asymptota)");

@aslist = split("hranice",$asout);
$aslist[3] =~ s/,//g;

if ($aslist[3] !~ /asymptota/)
{
# prevod do GNUplotu
$asgnuplot=`$mawtimeout echo "$aslist[3]" | $formconv_bin -r -O gnuplot`;
chomp($asgnuplot);	
$kresleni="$kresleni, $asgnuplot lt -1 lc rgb \"blue\" lw 2";
}

## body pro svislou asymptotu 
$pole[10] =~ s/\\left|\\right|=|x|\[|\]|\\,//g;

@vypocty=split(",",$pole[0]);
@vypoctyB=split(",",$pole[1]);
@neupr=split(",",$pole[7]);
@neuprB=split(",",$pole[8]);

$test=$vypocty[20];
$test =~ s/ //g;

if ($test eq "1") 
{
    $nasobek="";
}
else
{ 
    $nasobek=$vypocty[20]."{}\\cdot{} ";
}

$test=$vypocty[9]; $test =~ s/ //g;
if ($test eq "1")
{
print VYSTUP "\\def\\temp{$nasobek\\frac{$vypocty[17]}{\\left($vypocty[18]\\right)^{2}}}\\def\\vypocetder{\\left[\\fb\\right]'=$nasobek\\frac{$neupr[3]}{\\left($vypocty[7]\\right)^{2}}=\\temp}\n";
print VYSTUP2 "\$vypocetderHTML='$nasobek\\frac{$neupr[3]}{\\left($vypocty[7]\\right)^{2}}={$nasobek\\frac{$vypocty[17]}{\\left($vypocty[18]\\right)^{2}}}';\n";

}
else
{
print VYSTUP "\\def\\temp{$nasobek\\frac{$vypocty[17]}{$vypocty[16]}}\\def\\vypocetder{\\left[\\fb\\right]'=$nasobek\\frac{$neupr[1]}{\\left($vypocty[7]\\right)^{$vypocty[14]}}=$nasobek\\frac{\\Bigl($vypocty[15]\\Bigr)\\Bigl($neupr[2]\\Bigr)}{\\left($vypocty[7]\\right)^{$vypocty[14]}}=$nasobek\\frac{$neupr[2]}{$vypocty[16]}=\\temp}\n";
print VYSTUP2 "\$vypocetderHTML='$nasobek\\frac{$neupr[1]}{\\left($vypocty[7]\\right)^{$vypocty[14]}}=$nasobek\\frac{\\Bigl($vypocty[15]\\Bigr)\\Bigl($neupr[2]\\Bigr)}{\\left($vypocty[7]\\right)^{$vypocty[14]}}=$nasobek\\frac{$neupr[2]}{$vypocty[16]}={$nasobek\\frac{$vypocty[17]}{$vypocty[16]}}';\n";
}

$test=$vypocty[11]; $test =~ s/ //g;
if ($test eq "0")
{
    $navrkod="PROBLEMY S POSTUPEM, koreny jmenovatele nemaji vsechny stejnou nasobnost";
    print VYSTUP "\\def\\vypocetder{}\\def\\temp{}\n";
}



$test=$vypoctyB[20];
$test =~ s/ //g;


#print $vypoctyB[20]; die;

if ($test eq "1") 
{
    $nasobekB="";
}
else
{ 
    $nasobekB=$vypoctyB[20]."{}\\cdot{} ";
}


$test=$vypoctyB[9]; $test =~ s/ //g;
if ($test eq "1")
{
#    print VYSTUP "\\def\\vypocetderB{\\left(\\temp\\right)'=$nasobekB\\frac{$neuprB[3]}{\\left($vypoctyB[7]\\right)^{2}}=$nasobekB\\frac{$vypoctyB[17]}{\\left($vypoctyB[18]\\right)^{2}}}\n";
    print VYSTUP "\\def\\vypocetderB{\\left[$neuprB[4]\\right]'=$nasobekB\\frac{$neuprB[3]}{\\left($vypoctyB[7]\\right)^{2}}=$nasobekB\\frac{$vypoctyB[17]}{\\left($vypoctyB[18]\\right)^{2}}}\n";
    print VYSTUP2 "\$vypocetderBHTML='$nasobekB\\frac{$neuprB[3]}{\\left($vypoctyB[7]\\right)^{2}}=$nasobekB\\frac{$vypoctyB[17]}{\\left($vypoctyB[18]\\right)^{2}}';\n";
}
else
{
    print VYSTUP "\\def\\vypocetderB{\\left[$neuprB[4]\\right]'=$nasobekB\\frac{$neuprB[1]}{\\left($vypoctyB[7]\\right)^{$vypoctyB[14]}}=$nasobekB\\frac{\\Bigl($vypoctyB[15]\\Bigr)\\Bigl($neuprB[2]\\Bigr)}{\\left($vypoctyB[7]\\right)^{$vypoctyB[14]}}=$nasobekB\\frac{$neuprB[2]}{$vypoctyB[16]}=$nasobekB\\frac{$vypoctyB[17]}{$vypoctyB[16]}}\n";
    print VYSTUP2 "\$vypocetderBHTML='$nasobekB\\frac{$neuprB[1]}{\\left($vypoctyB[7]\\right)^{$vypoctyB[14]}}=$nasobekB\\frac{\\Bigl($vypoctyB[15]\\Bigr)\\Bigl($neuprB[2]\\Bigr)}{\\left($vypoctyB[7]\\right)^{$vypoctyB[14]}}=$nasobekB\\frac{$neuprB[2]}{$vypoctyB[16]}=$nasobekB\\frac{$vypoctyB[17]}{$vypoctyB[16]}';\n";
}

$test=$vypoctyB[11]; $test =~ s/ //g;
if ($test eq "0")
{
    print VYSTUP "\\def\\vypocetderB{}\n";
}

print VYSTUP "\\def\\derivacesouc{$vypocty[4]}\n";
print VYSTUP "\\def\\derivacesoucB{$vypoctyB[0]}\n";
print VYSTUP "\\def\\druhaderivace{}\n";
print VYSTUP "\\def\\rcestac{$vypocty[5]}\n";
print VYSTUP "\\def\\rcenesp{$vypocty[6]}\n";
print VYSTUP "\\def\\rcekrit{$vypoctyB[1]}\n";
print VYSTUP "\\def\\rcenul{\\funkce=0}\n";
print VYSTUP "\\def\\fa{$vypocty[1]}\n";

print VYSTUP2 "\$derivacesoucHTML = '$vypocty[4] ';\n";
print VYSTUP2 "\$derivacesoucBHTML = '$vypoctyB[0] ';\n";
print VYSTUP2 "\$druhaderivaceHTML = ' ';\n";
print VYSTUP2 "\$rcestacHTML = '$vypocty[5] ';\n";
print VYSTUP2 "\$rcenespHTML = '$vypocty[6] ';\n";
print VYSTUP2 "\$rcekritHTML = '$vypoctyB[1] ';\n";
#print VYSTUP2 "\$rcenulHTML = '\\funkce=0 ';\n";
print VYSTUP2 "\$faHTML = '$vypocty[1] ';\n";


$testA=$vypocty[1];
$testB=$vypocty[19];
$testA =~ s/ //g;
$testB =~ s/ //g;

if (($testA eq $testB) || ($vypocty[20] =~ /x/))
{
    print VYSTUP "\\let\\fb\\fa\n";
    print VYSTUP2 "\$fbHTML=\$faHTML;\n";
}
else
{
#    print VYSTUP "\\def\\fb{$nasobek$vypocty[19]}\n";
    print VYSTUP "\\def\\fb{$neupr[4]}\n";
    print VYSTUP2 "\$fbHTML = '$neupr[4]';\n";
}

#    print VYSTUP "\\def\\fb{$neupr[4]}\n";


print VYSTUP2 "?>";


open(VYSTUPOBR,">obrazek");


print VYSTUPOBR "set zeroaxis lt -1 \n";
print VYSTUPOBR "set xtics axis nomirror \n";
print VYSTUPOBR "set ytics axis nomirror \n";
print VYSTUPOBR "set samples 1000 \n";
print VYSTUPOBR "set noborder \n";
print VYSTUPOBR "set term postscript eps color \n";
print VYSTUPOBR "unset key \n";
print VYSTUPOBR 'set output "graf.eps"'."\n";

@bodynesp=split(",",@pole[10]);
foreach $bod (@bodynesp)
{ 
    if ($bod !~ /i/)
    {
	print VYSTUPOBR "set arrow from $bod,graph 0 to $bod,graph 1 nohead lt -1 lc rgb \"blue\" lw 2\n";
    }
}

print VYSTUPOBR "set xrange [$xmin:$xmax]\n";
print VYSTUPOBR "set yrange [$ymin:$ymax]\n";
print VYSTUPOBR "set style function lines\n";
print VYSTUPOBR "$kresleni\n";


print NAVRKOD $navrkod."\n";

#print "<br>",@pole[0],"<br>\n\n\n",@pole[1],"<br>\n\n\n",@pole[2];

#$vystupmax =~ s/,/\n/gs;
#print "<pre>".$vystupmax;


