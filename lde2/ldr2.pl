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

use URI::Escape;

use Locale::gettext;
bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");
print VYSTUP2 "<?php\n";
open(MSG,">msg.html");

sub modifiedprint
{
    print VYSTUP "\\def\\".$_[0]."{".$_[1]."}\n";
    print VYSTUP2 "\$"."$_[0]='$_[1]';\n";
}


## podobne, ale argumentem je pole. pokud dojde k chybe, zapise se
## vystup do souboru output
sub maximatexlist
{
    my ($prikaz)=@_;
    $shorter=$mawhome."/common/shorter.mac";
    $mawtexlist=$mawhome."/common/mawtexlist.mac";
    my $retezec=`$mawtimeout -t 10 $maxima --batch-string=\"load(\\\"$shorter\\\")\$ load(\\\"$mawtexlist\\\")\$ mawtexlist($prikaz);\"`;

    #print "<pre>".$retezec;
    if (($retezec !~ /MAW summary/)||(maw::maximaerror($retezec)))    
    {
	if ($method == "0")
	{
	    print MSG"<span style='font-weight:bold'><span class='red'>".gettext("An error occurred when processing your input.<br>Check your formulas (perhaps using Preview button) and report the problem if you think that your input is correct and should be processed without any error.")."</span></span><hr><b>*** Maxima session ***</b><pre>".substr($retezec,0,2000)."<pre>";
	}
	else
	{
	    $fTeX= `echo "$f" | formconv `;
	    chomp($fTeX);
	    print MSG "<h2 class='red'>".sprintf(gettext("The equation with right hand side %s cannot be solved by undetermined coefficients"),"<img src=\"$texrender ".$fTeX."\" style=\"border-style:solid; border-width: 2px; padding: 8px\">")."</h2> ".sprintf(gettext("You may have a typing error in your mathematical expression or use too general right hand side of the equation.  This interface works only if the right hand side is in the form %s where P and Q are polynomials of order at most 4."),"<img src=\"$texrender P(x)\\sin(\\beta x)e^{\\alpha x}+Q(x)\\cos(\\beta x)e^{\\alpha x}\">")."<pre>",substr($retezec,0,2000),"<pre>";
	}
	saveoutput ("error");
	die();
    }

    @vystup = $retezec =~ /mawinta(.*?)mawinta/gs;
    $integralone=$vystup[1]; 
    $integralone =~ s/%//gs;
    
    @vystup = $retezec =~ /mawintb(.*?)mawintb/gs;
    $integraltwo=$vystup[1]; 
    $integraltwo =~ s/%//gs;
    
    @vystup = $retezec =~ /gensolh(.*?)gensolh/gs;
    $vystup[1] =~ s/\n//gs;
    $gensolh = $vystup[1]; 
    
    @vystup = $retezec =~ /keyfunda(.*?)keyfunda/gs;
    $funda = $vystup[1]; 
    
    @vystup = $retezec =~ /keyfundb(.*?)keyfundb/gs;
    $fundb = $vystup[1]; 
    
    @vystup = $retezec =~ /parsol(.*?)parsol/gs;
    $vystup[1] =~ s/\n//gs;
    $parsol = $vystup[1]; 

    @vystup = $retezec =~ /MAW summary: (.*?) items/gs;
    $number_of_items = $vystup[0]; 

    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;

    @pole=();

    @a = $retezec =~ /item (.*?) meti/gs;
    for ($i=0; $i<=$number_of_items-1; $i++)
    {
	$a[$i] =~ s/\$\$//g;
	push(@pole,($a[$i]));
    }
    return @pole;
}

@vystupmax=maximatexlist("[$f]");
$vystupmax[0] =~ s/ //g;
if ($vystupmax[0] eq "0") {$method="0";}

if ($method == "0")
{
@vystupmax=maximatexlist("
[(display2d:false,$p),
$q, 
D:ratsimp(($p)^2-4*$q),
if D>0 then 1 elseif D<0 then -1 else 0, 
resa:ratsimp((-$p+sqrt(D))/2),
resb:ratsimp((-$p-sqrt(D))/2),
vysledek:expand(ode2('diff(y,x,2) + $p*'diff(y,x)+$q*y = 0,y,x)), 
if D<0 then realpart(resa) else resa, 
if D<0 then abs(imagpart(resb)) else resb , 
x^3+$p*x^2+$q*x,
-$p,
$f,
funda:if D<0 then rhs(ev(vysledek,%k1=0,%k2=1)) else rhs(ev(vysledek,%k1=1,%k2=0)),
fundb:if D<0 then rhs(ev(vysledek,%k1=1,%k2=0)) else rhs(ev(vysledek,%k1=0,%k2=1)),
fundsader:ratsimp(diff(funda,x)),
fundsbder:ratsimp(diff(fundb,x)),
Wm:matrix([funda,fundb],[fundsader,fundsbder]),
W:trigsimp(ratsimp(determinant(Wm))),
Wma:matrix([0,fundb],[$f,fundsbder]),
Wa:xthru(determinant(Wma)),
Wmb:matrix([funda,0],[fundsader,$f]),
Wb:xthru(determinant(Wmb)),
Ader:(trigsimp(ratsimp(Wa/W))),
Bder:(trigsimp(ratsimp(Wb/W))),
A:ratsimp(integrate(Ader,x)),
B:ratsimp(integrate(Bder,x)),
yp:expandwrt(trigsimp(trigexpand(radcan(A*funda+B*fundb))),sin,cos),
(print (\"gensolh\", C[1]*funda+C[2]*fundb,\"gensolh\"),A*funda+B*fundb+yp),
$f,
if (zeroequiv(radcan(diff(yp,x,2)+($p)*diff(yp,x)+($q)*yp-($f)),x)) then 1 else 0,
print(\"mawinta\",Ader,\"mawinta\"),
print(\"mawintb\",Bder,\"mawintb\"),
print(\"parsol\",yp,\"parsol\")]");

$vystupmax[29] =~ s/ //g;
if ($vystupmax[29] eq "0")
{
    saveoutput ("</pre><b>".sprintf(gettext("The check that the particular solution %s is correct failed.")," <img src=\"$texrender$vystupmax[26]\"> ")."</b><BR><BR><span style='font-weight:bold'><span class='red'>".gettext("Sorry, report this problem please. We will investigate and (hope) we fix it.")."</span></span>\n<pre>Particular solution failed.\n\n".$retezec);
    die ();
}

$vystupmax[0] =~ s/ //g;
if ($vystupmax[0] eq "")
{
    saveoutput ("</pre><b>".gettext("Something is wrong. Probably too difficult integrals.")."</b><BR>".gettext("Sorry, report this problem please. We will investigate and (hope) we fix it.")."\n<pre><pre>".$retezec."</pre>");
    die ();
}

$vystupmax[1] =~ s/ //g;
$vystupmax[2] =~ s/ //g;
$vystupmax[3] =~ s/ //g;
$vystupmax[8] =~ s/ //g;
$vystupmax[6] =~ s/\\it \\%k/C/g;
$reseni=$vystupmax[6];
$charrce=$vystupmax[9];
$charrce =~ s/x\^3/\\lambda\^2/g;
$charrce =~ s/x\^2/\\lambda/g;
$charrce =~ s/x//g;
$vystupmax[9] =~ s/x\^3/y^{\\prime\\prime}/g;
$vystupmax[9] =~ s/x\^2/y^{\\prime}/g;
$vystupmax[9] =~ s/x/y/g;
$vystupmax[11] =~ s///g;
#$vystupmax[26] =~ s/left/Bigl/g;
#$vystupmax[26] =~ s/right/Bigr/g;

#$vystupmax[12] =~ s/y=//g;
#$vystupmax[13] =~ s/y=//g;

&modifiedprint ("p", "$vystupmax[0]");
&modifiedprint ("q", "$vystupmax[1]");
&modifiedprint ("D", "$vystupmax[2]");
&modifiedprint ("concl", "$vystupmax[3]");
&modifiedprint ("lambdaa", "$vystupmax[4]");
&modifiedprint ("lambdab", "$vystupmax[5]");
###&modifiedprint ("reseni" "$vystupmax[6]");
&modifiedprint ("resa", "$vystupmax[7]");
&modifiedprint ("resb", "$vystupmax[8]");
&modifiedprint ("rce", "$vystupmax[9]");
&modifiedprint ("minusp", "$vystupmax[10]");

&modifiedprint ("charrce", "$charrce");
&modifiedprint ("fundsa", "$vystupmax[12]");
&modifiedprint ("fundsb", "$vystupmax[13]");
&modifiedprint ("fundsader", "$vystupmax[14]");
&modifiedprint ("fundsbder", "$vystupmax[15]");
print VYSTUP "\\def\\reseni{y=C_1 \\fundsa +C_2 \\fundsb}\n";
print VYSTUP "\\def\\resenivar{A(x) \\fundsa + B(x) \\fundsb}\n";
&modifiedprint ("wronskimat", "$vystupmax[16]");
&modifiedprint ("wronski", "$vystupmax[17]");
&modifiedprint ("wronskimatA", "$vystupmax[18]");
&modifiedprint ("wronskiA", "$vystupmax[19]");
&modifiedprint ("wronskimatB", "$vystupmax[20]");
&modifiedprint ("wronskiB", "$vystupmax[21]");
&modifiedprint ("derA", "$vystupmax[22]");
&modifiedprint ("derB", "$vystupmax[23]");
print VYSTUP "\\def\\intderA{\\href{$mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int $vystupmax[22] \\,\\text{d}x}}\n";
print VYSTUP "\\def\\intderB{\\href{$mawserver/integral/integralx.php?$integraltwo;lang=$lang}{\\int $vystupmax[23] \\,\\text{d}x}}\n";
print VYSTUP2 "\$intderA='\\href{/maw/integral/integralx.php?$integralone;lang=$lang}{\\int $vystupmax[22] \\,\\text{d}x}';\n";
print VYSTUP2 "\$intderB='\\href{/maw/integral/integralx.php?$integraltwo;lang=$lang}{\\int $vystupmax[23] \\,\\text{d}x}';\n";
&modifiedprint ("A", "$vystupmax[24]");
&modifiedprint ("B", "$vystupmax[25]");
&modifiedprint ("yp", "$vystupmax[26]");
&modifiedprint ("yob", "$vystupmax[27]");

$vystupmax[28] =~ s/ //g;

if ($vystupmax[28] eq "0")
{
    &modifiedprint ("pravastrana", "0");
}
else
{
    $pravastrana= `echo "$f" | formconv `;
    chomp($pravastrana);
    &modifiedprint ("pravastrana", "$pravastrana")
;
#    print VYSTUP "\\def\\pravastrana{$vystupmax[28]}\n";
}


if ($method == "0") {
  open(METHOD,">method");
  print METHOD "0\n".$parsol."\n".$gensolh;
}
print VYSTUP2 "?>\n";
exit ;
}
## end of undetermined coefficients


## here we solve homogeneous equation
 
    @vystupmax=maximatexlist("[(display2d:false,$p),
$q, 
D:ratsimp(($p)^2-4*$q),
if D>0 then 1 elseif D<0 then -1 else 0, 
resa:ratsimp((-$p+sqrt(D))/2),
resb:ratsimp((-$p-sqrt(D))/2),
vysledek:expand(ode2('diff(y,x,2) + $p*'diff(y,x)+$q*y = 0,y,x)), 
if D<0 then realpart(resa) else resa, 
if D<0 then abs(imagpart(resb)) else resb , 
x^3+$p*x^2+$q*x,
-$p,
$f,
funda:if D<0 then rhs(ev(vysledek,%k1=0,%k2=1)) else rhs(ev(vysledek,%k1=1,%k2=0)),
fundb:if D<0 then rhs(ev(vysledek,%k1=1,%k2=0)) else rhs(ev(vysledek,%k1=0,%k2=1)),
print(\"keyfunda\",funda,\"keyfunda\"),
print(\"keyfundb\",fundb,\"keyfundb\"),
print(\"gensolh\",C[1]*funda+C[2]*fundb,\"gensolh\")]");

$gensolh_save=$gensolh;

	$vystupmax[0] =~ s/ //g;
	$vystupmax[1] =~ s/ //g;
	$vystupmax[2] =~ s/ //g;
	$vystupmax[3] =~ s/ //g;
	$vystupmax[8] =~ s/ //g;
	$vystupmax[6] =~ s/\\it \\%k/C/g;
	$reseni=$vystupmax[6];
	$charrce=$vystupmax[9];
	$charrce =~ s/x\^3/\\lambda\^2/g;
	$charrce =~ s/x\^2/\\lambda/g;
	$charrce =~ s/x//g;
	$vystupmax[9] =~ s/x\^3/y^{\\prime\\prime}/g;
	$vystupmax[9] =~ s/x\^2/y^{\\prime}/g;
	$vystupmax[9] =~ s/x/y/g;
	$vystupmax[11] =~ s///g;
	$rce=$vystupmax[9];
	
	&modifiedprint ("p","$vystupmax[0]");
	&modifiedprint ("q","$vystupmax[1]");
	&modifiedprint ("D","$vystupmax[2]");
	&modifiedprint ("concl","$vystupmax[3]");
	&modifiedprint ("lambdaa","$vystupmax[4]");
	&modifiedprint ("lambdab","$vystupmax[5]");
	&modifiedprint ("resa","$vystupmax[7]");
	&modifiedprint ("resb","$vystupmax[8]");
	&modifiedprint ("rce","$vystupmax[9]");
	&modifiedprint ("minusp","$vystupmax[10]");
	
	&modifiedprint ("charrce","$charrce");
	&modifiedprint ("fundsa","$vystupmax[12]");
	&modifiedprint ("fundsb","$vystupmax[13]");
	print VYSTUP "\\def\\reseni{y=C_1 \\fundsa +C_2 \\fundsb}\n";


## here we test if the rhs is 4-degree polynomial times exp(ax+b)
	
if (($f !~ /sin/)&&($f !~ /cos/))
{
    @vystupmax=maximatexlist("[f(x):=$f,
A:limit(radcan(t*log(abs(f(1/t)))), t, 0, plus),
B:(exp(A*x)),
C:ratsimp(radcan(f(x)/B)),
if zeroequiv(ratsimp((C*B)-f(x)),x) then (if (diff(C,x)=0) then 0 elseif (diff(C,x,2)=0) then 1 elseif (diff(C,x,3)=0) then 2 elseif (diff(C,x,4)=0) then 3 elseif (diff(C,x,5)=0) then 4  else undefined) else undefined,
A^2+$p*A+$q,
2*A+$p,
f(x)]");
}
else
{
    $vystupmax[4]="undefined";
}

if ($vystupmax[4]!~/undefined/)
{
    $vystupmax[1] =~ s/ //g;
    $vystupmax[4] =~ s/ //g;
    $vystupmax[5] =~ s/ //g;
    $vystupmax[6] =~ s/ //g;
    
    $pravastrana= `echo "($vystupmax[3])$vystupmax[2]" | formconv `;
    chomp($pravastrana);
    $pravastrana=$vystupmax[7];
    &modifiedprint ("pravastrana","$pravastrana");
    &modifiedprint ("pravastranaexpkoef","$vystupmax[1]");
    &modifiedprint ("pravastranaexp","$vystupmax[2]");
    &modifiedprint ("pravastranapol","$vystupmax[3]");
    &modifiedprint ("pravastranapolst","$vystupmax[4]");
    $pravastranaexp=$vystupmax[2];
    $pravastranapol=$vystupmax[3];
    
    $stupen=$vystupmax[4];
    if ($stupen eq "0")
    {$test="a";$koefficients="['a=a]";}
    elsif ($stupen eq "1")
    {$test="a*x+b";$koefficients="['a=a,'b=b]";}
    elsif ($stupen eq "2")
    {$test="a*x^2+b*x+c";$koefficients="['a=a,'b=b,'c=c]";}
    elsif ($stupen eq "3")
    {$test="a*x^3+b*x^2+c*x+d";$koefficients="['a=a,'b=b,'c=c,'d=d]";}
    else
    {$test="a*x^4+b*x^3+c*x^2+d*x+f";$koefficients="['a=a,'b=b,'c=c,'d=d,'f=f]";}
    
    $ttt=$test; $ttt =~ s/\*//g;
    print VYSTUP "\\def\\polynomtest{$ttt}\n";
    print VYSTUP2 "\$polynomtest='$ttt';\n";
    
    if ($vystupmax[5] eq "0")
    {
	if ($vystupmax[6] eq "0")
	{
	    print VYSTUP "\\def\\nasobnost{2}\n";
	    print VYSTUP2 "\$nasobnost='2';\n";
	    $test="x^2*($test)";
	}
	else
	{
	    print VYSTUP "\\def\\nasobnost{1}\n";
	    print VYSTUP2 "\$nasobnost='1';\n";
	    $test="x*($test)";
	}
    }
    else
    {
	print VYSTUP "\\def\\nasobnost{0}\n";
	print VYSTUP2 "\$nasobnost='0';\n";
    }
    
    $testexp=$vystupmax[2];
    
    @vystupmax=maximatexlist("[(display2d:false,expand(diff($test,x))),
f(x):=expand($f),
A:limit(radcan(t*log(abs(f(1/t)))), t, 0, plus),
testexp:exp(A*x),
testexpder:expand(diff(exp(A*x),x)),
aa:expand(diff(($test)*(testexp),x)/testexp),
diff(aa,x),
expand(diff(($test)*(testexp),x,2)/testexp),
expand(radcan($test)),
tt:expand((diff(($test)*(testexp),x,2)+($p)*diff(($test)*(testexp),x)+($q)*($test)*(testexp))/testexp),
factorout(ratsimp(tt),a,b,c,d,f),
(fovertestexp:radcan(f(x)/testexp),coeff(tt,x,0)),
coeff(expand((fovertestexp)),x,0),
coeff(tt,x,1),coeff(expand((fovertestexp)),x,1),
coeff(tt,x,2),coeff(expand((fovertestexp)),x,2),
coeff(tt,x,3),coeff(expand((fovertestexp)),x,3),
coeff(tt,x,4),coeff(expand((fovertestexp)),x,4),
ev(linsolve([coeff(tt,x,0)=coeff(expand((fovertestexp)),x,0),
coeff(tt,x,1)=coeff(expand((fovertestexp)),x,1),
coeff(tt,x,2)=coeff(expand((fovertestexp)),x,2),
coeff(tt,x,3)=coeff(expand((fovertestexp)),x,3),
coeff(tt,x,4)=coeff(expand((fovertestexp)),x,4)
],[a,b,c,d,f]),globalsolve:true),
(yp:($test)*exp(A*x), yp),
if A<0 then -1 else 0,
if (zeroequiv(radcan(diff(yp,x,2)+($p)*diff(yp,x)+($q)*yp-(f(x))),x)) then 1 else 0,
print(\"parsol\",yp,\"parsol\"),
$koefficients]");

$vystupmax[9] =~ s/ //g;
$vystupmax[10] =~ s/ //g;

&modifiedprint ("derpol","$vystupmax[0]");
&modifiedprint ("derexp","$vystupmax[4]");
&modifiedprint ("uprder","$vystupmax[5]");
&modifiedprint ("derb","$vystupmax[6]");
&modifiedprint ("uprderb","$vystupmax[7]");
&modifiedprint ("pol","$vystupmax[8]");
&modifiedprint ("uprls","$vystupmax[9]");
&modifiedprint ("uprlsB","$vystupmax[10]");
&modifiedprint ("rovkoefa","$vystupmax[11]&=$vystupmax[12]");
&modifiedprint ("rovkoefb","$vystupmax[13]&=$vystupmax[14]");
&modifiedprint ("rovkoefc","$vystupmax[15]&=$vystupmax[16]");
&modifiedprint ("rovkoefd","$vystupmax[17]&=$vystupmax[18]");
&modifiedprint ("rovkoeff","$vystupmax[19]&=$vystupmax[20]");

$ko= "\\def\\vysledek{$vystupmax[26]}";
print VYSTUP $ko;
print VYSTUP2 "\$vysledek='$vystupmax[26]';\n";


if ($pravastranaexp eq " 1 ")
{print VYSTUP "\\def\\partikularni{$vystupmax[22]}";
print VYSTUP2 "\$partikularni='$vystupmax[22]';";
$rcedosaz=$rce;
$rcedosaz=~ s/y''/\\underbrace{(\\uprderb)}_{\\Y''}/;
$rcedosaz=~ s/y'/\\underbrace{(\\uprder)}_{\\Y'}/;
$rcedosaz=~ s/y/\\underbrace{(\\pol)}_{\\Y}/;
print VYSTUP "\\def\\Y{y}\\def\\rcedosaz{$rcedosaz}\n";
$rcedosaz=$rce;
$rcedosaz=~ s/y''/\\underbrace{(\\uprderb)}_{\\Y''}\\\\/;
$rcedosaz=~ s/y'/\\underbrace{(\\uprder)}_{\\Y'}\\\\/;
$rcedosaz=~ s/y/\\underbrace{(\\pol)}_{\\Y}/;
print VYSTUP "\\def\\Y{y}\\def\\rcedosazB{$rcedosaz}\n";
}
else
{
print VYSTUP "\\def\\partikularni{$vystupmax[22]}";
print VYSTUP2 "\$partikularni='$vystupmax[22]';";
$rcedosaz=$rce;
$rcedosaz=~ s/y''/\\underbrace{(\\uprderb)\\pravastranaexp}_{\\Y''}/;
$rcedosaz=~ s/y'/\\underbrace{(\\uprder)\\pravastranaexp}_{\\Y'}/;
$rcedosaz=~ s/y/\\underbrace{(\\pol)\\pravastranaexp}_{\\Y}/;
print VYSTUP "\\def\\Y{y}\\def\\rcedosaz{$rcedosaz}\n";
$rcedosaz=$rce;
$rcedosaz=~ s/y''/\\underbrace{(\\uprderb)\\pravastranaexp}_{\\Y''}\\\\/;
$rcedosaz=~ s/y'/\\underbrace{(\\uprder)\\pravastranaexp}_{\\Y'}\\\\/;
$rcedosaz=~ s/y/\\underbrace{(\\pol)\\pravastranaexp}_{\\Y}/;
print VYSTUP "\\def\\Y{y}\\def\\rcedosazB{$rcedosaz}\n";
}

print VYSTUP "\\def\\znamenkoexponentu{$vystupmax[23]}";
print VYSTUP2 "\$znamenkoexponentu='$vystupmax[23]';";

$vystupmax[24] =~ s/ //g;
if ($vystupmax[24] eq "0")
{
    saveoutput("</pre><b>".sprintf(gettext("The check that the particular solution %s is correct failed.")," <img src=\"$texrender$vystupmax[26]\"> ")."</b><BR><BR><span style='font-weight:bold'><span class='red'>".gettext("Sorry, report this problem please. We will investigate and (hope) we fix it.")."</span>"."</span>\n<pre><pre>Particular solution failed.\n\n".$retezec."</pre>");
    die ();
}

open(METHOD,">method");
print METHOD "1\n".$parsol."\n".$gensolh_save;
exit ;
}

$batchfile=$mawhome."/lde2/ldr.mac";
$outputmax=`$mawtimeout -t 10 $maxima --batch-string=\"f:$f; bbb:$p; ccc:$q;load(\\\"$batchfile\\\");\"`;

$outputmax =~ s/\\\n//g;
$outputmax =~ s/0 errors, 0 warnings//g;

sub najdi
{
    my ($where,$what)=@_;
    $tempoutputmax=$where;
    $tempoutputmax =~ s/\n//g;
    @outnajdi = $where =~ /### $what (.*?)\#/gs;
    $outnajdi[0] =~ s/\$\$//g;
    $outnajdi[0] =~ s/\n//g;
    chomp($outnajdi[0]);
    return($outnajdi[0]);
};


if ($outputmax=~/Particular solution failed/)
{
    $res=najdi($outputmax,"result");
    print MSG "</pre><b>".sprintf(gettext("The check that the particular solution %s is correct failed.")," <img src=\"$texrender$res\"> ")."</b><BR><BR><span style='font-weight:bold'><span class='red'>".gettext("Sorry, report this problem please. We will investigate and (hope) we fix it.")."</span></span>\n<pre>";
    saveoutput("<pre>Particular solution failed.\n\n".$retezec."</pre>error");
    print MSG "<h2>Maxima output</h2><pre>".substr($outputmax,0,2000000)."</pre>";
    die();
}


if ((maw::maximaerror($outputmax)) || ($outputmax=~/### different exps/) || ($outputmax=~/### *nonspecial rhs/) || ($outputmax!~/### sol_eqs/))    
{
    $link=$maw_URI;
    $link =~ s/\/maw//g;
    $link =~ s/\/dev-maw//g;
    $oldlink=$link;
    $link =~ s/akce=1/akce=0/;
#    $link =~ s/&/amper/g;
#    $link = uri_escape($link);
    $link =~ s/amper/&/g;
#    $link =~ s/%3D/=/g;
#    $link =~ s/%3F/\?/g;
#    $link =~ s/%25/%/g;
    $fTeX= `echo "$f" | formconv `;
    chomp($fTeX);
    print MSG "<h2 class='red'>".sprintf(gettext("The equation with right hand side %s cannot be solved by undetermined coefficients"),"<img src=\"$texrender".$fTeX."\" style=\"border-style:solid; border-width: 2px; padding: 8px\">")."</h2> ".sprintf(gettext("You may have a typing error in your mathematical expression or use too general right hand side of the equation.  This interface works only if the right hand side is in the form %s where P and Q are polynomials of order at most 4."),"<img src=\"$texrender P(x)\\sin(\\beta x)e^{\\alpha x}+Q(x)\\cos(\\beta x)e^{\\alpha x}\">")."<pre>",substr($retezec,0,2000),"</pre>";

    print MSG sprintf(gettext("For more general right hand sides use %svariation of constants%s."), "<a target=\"_blank\" title=\"".gettext("Computation opens in new panel or window.")."\" href=\"".$mawserver.$link."\">","</a>");
    if ($outputmax=~/### *can split rhs/)
    {
	print MSG "<h2>".gettext("However, the right hand side can be written as sum of two or more right hand sides which are fine for undetermined coefficients as follows.")."</h2>".gettext("(Click each equation to solve it using undetermined coefficients and then sum up all particular solutions to and general solution of associated homogeneous equation to get general solution. To find solution of initial value problem use variation of constants.)");
    }
    if ($outputmax=~/### *can rewrite rhs/)
    {
	print MSG "<h2>".gettext("However, the right hand side can be written in the form as required.")."</h2>".gettext("(click the equation to solve it using undetermined coefficients)");
    }
    if (($outputmax=~/### *can split rhs/)||($outputmax=~/### *can rewrite rhs/))
    {
	@equations=split(/###begin###/,$outputmax);
        foreach $rhs (@equations)
	{
            if ($rhs !~ /Maxima/)
	    {
		$rhs=~ s/\$\$//g;
		$rhs=~ s/###end###//;
                @out=split(/TeX:/,$rhs);        
		print MSG "<br><a target=\"_blank\" title=\"".gettext("Computation opens in new panel or window.")."\" href=\"".$mawserver.$oldlink."&f=".uri_escape($out[0])."&IVP=off\"><img src=\"$texrender",$out[2],"\" style=\"border-style:solid; border-width: 2px; padding: 8px\"></a><br>";
	    }
	}
	$res = najdi($outputmax,"partsol");
	$res =~ s/\\it \\%k/C/g;
	print MSG "<br><br>";
	print MSG gettext("General solution is")." ";
	print MSG "<img src=\"$texrender",$res,"\" style=\"border-style:none; border-width: 2px; padding: 8px; vertical-align=middle;\">";
    }    
    print MSG "<h2>Maxima output</h2><pre>".substr($outputmax,0,2000)."</pre>";
    saveoutput("error");
    die();
}


$pravastrana=najdi($outputmax,"f");
if ($pravastrana eq "0")
{
    &modifiedprint ("pravastrana","0");
}
else
{
    $pravastrana= `echo "$f" | formconv `;
    chomp($pravastrana);
    &modifiedprint ("pravastrana","$pravastrana");
}

&modifiedprint( "formpartsol",najdi($outputmax,"form of part sol"));

$difa=najdi($outputmax,"dif1");
$difa=~ s/\\left/\\Bigl/g;
$difa=~ s/\\right/\\Bigr/g;
&modifiedprint ("diffa",$difa);

$difb=najdi($outputmax,"dif2");
$difb=~ s/\\left/\\Bigl/g;
$difb=~ s/\\right/\\Bigr/g;
&modifiedprint ("diffb",$difb);

$dosazrce=najdi($outputmax,"lhs_of_eq_true");
$dosazrce=~ s/\\left/\\Bigl/g;
$dosazrce=~ s/\\right/\\Bigr/g;
&modifiedprint ("dosazrce", $dosazrce);

&modifiedprint ("testnumber", najdi($outputmax,"testnumber"));
&modifiedprint ("k", najdi($outputmax,"k"));
&modifiedprint ("f", najdi($outputmax,"f"));
&modifiedprint ("P", najdi($outputmax,"P"));
&modifiedprint ("Q", najdi($outputmax,"Q"));
&modifiedprint ("PA", najdi($outputmax,"PA"));
&modifiedprint ("coeffsin", najdi($outputmax,"coeff_sin"));
&modifiedprint ("partikularni", najdi($outputmax,"result"));

$alleqs=najdi($outputmax,"all_eqs");
$alleqs=~ s/ , / \\\\\\\\ /g;
$alleqs=~ s/\\left\[//g;
$alleqs=~ s/\\right\]//g;
&modifiedprint ("alleqs", $alleqs);
$alleqs=najdi($outputmax,"all_eqs");
$alleqs=~ s/ , / \\\\ /g;
$alleqs=~ s/\\left\[//g;
$alleqs=~ s/\\right\]//g;
print VYSTUP "\\def\\alleqs{".$alleqs."}\n";


$soleqs=najdi($outputmax,"sol_eqs");
$soleqs=~ s/ , / },\\quad\\penalty0{ /g;
$soleqs=~ s/\\left\[//g;
$soleqs=~ s/\\right\]//g;

&modifiedprint ("soleqs", "{ $soleqs }");

open(METHOD,">method");
print METHOD "2\n".najdi($outputmax,"parsol")."\n".najdi($outputmax,"gensolh");
