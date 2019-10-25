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
use URI::Escape;

bindtextdomain("messages", "$mawhome/locale"); 
textdomain("messages"); 

use lib "$mawhome/common";
use maw; $mawtimeout=maw::mawtimeout();

$fullode =~ s/y\\\'/'diff(y,x)/gs;
$fullode =~ s/y\'/'diff(y,x)/gs;

open(VYSTUP,">data.tex");
open(VYSTUP2,">data.php");

sub remove_diff 
{
    my($rcee)=@_;
    $rcee =~ s/\\left\({{d}\\over{d{}x}}{}y\\right\)/y'/g;
    $rcee =~ s/\\left\({{d }\\over{d{}x}}{}y\\right\)/y'/g;
    $rcee =~ s/\\left\({{d}\\over{d{}x}} {}y\\right\)/y'/g;
    $rcee =~ s/{{d}\\over{d{}x}}{}y/y'/g;
    $rcee =~ s/{{d }\\over{d{}x}}{}y/y'/g;
    $rcee =~ s/\\left\(y\' \\right\)/y'/g;
    $rcee =~ s/{{d}\\over{d{}x}}{} y/y'/g;
    $rcee =~ s/{{d}\\over{d {}x}}{}y/y'/g;
    return $rcee;
}


$dodatek=1;

########################################################

my $integralone;
my $integraltwo;


sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`yes \"nonzero;positive;\" | $mawtimeout $maxima --batch-string=\"(display2d:false, load(\\\"$mawhome/common/maw_solve.mac\\\"));tex($prikaz);\"`;

    $testretezec=$retezec;
    $testretezec=~ s/php\?/php/gs;
    $testretezec=~ s/negative\?/negative\./gs;
    $testretezec=~ s/nonzero\?/nnonzero\./gs;
    if (($testretezec !~ /\$\$\\left\[/)||(maw::maximaerror($testretezec)))
    {
	saveoutput ("</pre><span style='font-weight:bold'><span class='red'>".gettext("An error occurred when processing your input.<br>Check your formulas (perhaps using Preview button) and report the problem if you think that your input is correct and should be processed without any error.")."</span></span><pre>\n".$retezec);
	die ();
    }
    saveoutput($retezec);

    @vystup = $retezec =~ /mawinta(.*?)mawinta/gs;
    $integralone=$vystup[1]; 
    $integralone =~ s/%//gs;
    
    @vystup = $retezec =~ /mawintb(.*?)mawintb/gs;
    $integraltwo=$vystup[1]; 
    $integraltwo =~ s/%//gs;
    
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}

$shorter=$mawhome."/common/shorter.mac";
$ode2=$mawhome."/ode/ode2_marik.mac";

@vystupmax=maximatexlist("[(load(\\\"$shorter\\\"),load(\\\"ode2.mac\\\"),load(\\\"$ode2\\\"),logexpand:false,$fullode),(logexpand:true,solution:ode2($fullode, y, x), shorter(solution)),(logexpand:false,method),intfactor,fullratsimp(trigsimp(($ode)*diff($ode,x,1,y,1)-diff($ode,x)*diff($ode,y))),is (freeof(x,fullratsimp(ev($ode,y=z*x))) and freeof(y,fullratsimp(ev($ode,y=z*x))))]");

$rce=remove_diff($vystupmax[0]);

$res=$vystupmax[1];
$res=~ s/\\%c/ C /gs;

$metoda=$vystupmax[2];
$intfactor=$vystupmax[3];
$determinant=$vystupmax[4];
$homeq=$vystupmax[5];

if ($metoda =~/none/)
{
    $hlaska="echo '</pre><h2 class='red'>".gettext("Error: The equation cannot be solved.")."</h2>".gettext("The equation does not belong to the set of supported types. <br>You may also have a typing error in your formula which changes the type of the equation and makes the equation unsolvable for Maxima CAS.</b> Check your data.")."<hr><pre>' > output";
    system($hlaska);
    exit;
}

print VYSTUP "\\par \\textbf{\\ODEproblem:} \$ $rce\$ \\medskip \\par \\textbf{\\ODEsolution:} \$ $res\$";
print VYSTUP2 maw::makediv("!!ODEproblem: \$ $rce\$");
print VYSTUP2 maw::makediv("!!ODEsolution: \$ $res\$ \n");



if (($metoda!~/linear/)&&($metoda!~/homogeneous/)&&($metoda!~/separa/)&&($metoda!~/exact/)&&($metoda!~/bernoulli/)&&($homeq!~/true/)&&$determinant!~/^ *0 *$/)
{
    print VYSTUP "\\par\n \\smallskip\\hrule\\smallskip \n \\let\\test\\relax";
    print VYSTUP "\\ODEunknownsolved {\\ignorespaces ".$metoda."}";
    print VYSTUP2 "!!ODEunknownsolved ".$metoda." \n";
    if (($intfactor!~/false/)&&($metoda =~/exact/))
    {
	print VYSTUP "\\ODEintfactormsg{\$ ".$intfactor." \$}";
	print VYSTUP2 "!!ODEintfactormsg \$ ".$intfactor." \$;";
    }
    print VYSTUP "\\end{document}\n ";
    exit;
}

print VYSTUP "\\par\n \\smallskip\\hrule\\smallskip \n \\ODEfootnote\\par ";


################################
#1. homogeneous, neither linear nor separable
if (($metoda=~/homogen/)||(($homeq=~/true/)&&($metoda!~ /linear/)&&($metoda!~ /separable/)))
{
#     print "MSG: solving as homogeneous f(y/x) \n";
    @vystupmaxb=maximatexlist("[(load(\\\"$shorter\\\"),load(\\\"ode2.mac\\\"),load(\\\"$ode2\\\"),vysledek:shorter(ode2('diff(y,x)=$ode, y, x))),
f:$ode,
f2:radcan(fullratsimp(ev(f,y:z*x))),
radcan(f2-z),
radcan(DZ/(f2-z)),
intf:integrate(1/(f2-z),z),
radcan(at(intf,z=y/x)),
print(\"mawinta\",radcan(1/(f2-z)),\"mawinta\")]");
    
    $expr="\\textbf{\\color{red}\\ODEhomogeneousequation} (\\ODEsubstitution \$ y=zx  \$, \$ y'=z+xz' \$)\\par";
    $expr2=maw::makediv("!!ODEhomogeneousequation (!!ODEsubstitution \$ y=zx  \$, \$ y'=z+xz' \$)");
    $konst_res="\\ODEsingularsolution";
    
    $vystupmaxb[0] =~ s/\\%c/ C /gs;
    $vystupmaxb[0] =~ s/=/ &= /gs;
    
    $vystupmaxb[4] =~ s/it DZ/text{d}z/gs;
    print VYSTUP $expr;
    print VYSTUP2 $expr2;
    $expr="\\begin{align*}\n y'&=$vystupmaxb[1]\\\\ z'x+z&=$vystupmaxb[2] \\\\ z'x &= $vystupmaxb[3]\\\\ $vystupmaxb[4]&=\\frac{\\text{d}x}{x}\\\\ \\href{\\mawserver/integral/integralz.php?$integralone;lang=$lang}{\\int $vystupmaxb[4]}&=\\int \\frac{\\text{d}x}{x}\\\\ $vystupmaxb[5]&=\\ln|x|+C_1\\\\ $vystupmaxb[6]&=\\ln|x|+C_1\\\\ $vystupmaxb[0] \\end{align*}\n";
    $expr2="\\begin{align*}\n y'&=$vystupmaxb[1]\\\\ z'x+z&=$vystupmaxb[2] \\\\ z'x &= $vystupmaxb[3]\\\\ $vystupmaxb[4]&=\\frac{\\text{d}x}{x}\\\\ \\href{/maw/integral/integralz.php?$integralone;lang=$lang}{\\int $vystupmaxb[4]}&=\\int \\frac{\\text{d}x}{x}\\\\ $vystupmaxb[5]&=\\ln|x|+C_1\\\\ $vystupmaxb[6]&=\\ln|x|+C_1\\\\ $vystupmaxb[0] \\end{align*}\n";
    print VYSTUP $expr;
    print VYSTUP2 maw::makediv($expr2);


    @konstrespole=maximatexlist("[(tempeq:radcan(ev($ode,y=z*x)-z),res:maw_solve_in_domain(tempeq,tempeq,z),map(lambda([tt],x*rhs(tt)),res))]");

    $konstresstring=join(",",@konstrespole);

    $konstresstring=~s/\\left\[//g;
    $konstresstring=~s/\\right\]//g;
    @konstrespole=split(',',$konstresstring);
    $n=0;
    $konstres="";
    $carka=" ";
    foreach $solution (@konstrespole)
    {
      $n=$n+1;
      if ($n>1) {$carka=",\\ ";}
      $konstres=$konstres."\\ $carka y_$n=$solution";
    }

    $konstresstring=~s/ //g;
    if ($konstresstring eq "") {$konstres="\\text\{".gettext("not found")."\}";}

    print VYSTUP "\\textit{\\ODEkonstresHOM}: \$ $konstres \$ \\par\\medskip\\hrule\\medskip\n";
    print VYSTUP2 maw::makediv("!!ODEkonstresHOM: \$ $konstres \$ ");

    print VYSTUP "\\let\\test\\relax";
    exit;
}


#######################################
#2. linear, but not y'=f(x)

if (($metoda=~/linea/) and ($ode=~/y/))
{
    print VYSTUP "\\constantmsgfalse ";
    print VYSTUP "\\explicitfalse ";
    #print VYSTUP2 "!!constantmsgfalse ";
    #print VYSTUP2 "!!explicitfalse ";

#    print "MSG: solving as linear \n";
    @vystupmaxb=maximatexlist("[(load(\\\"$shorter\\\"),vysledek:shorter(ode2('diff(y,x)=$ode, y, x))),afce:trigsimp(ratsimp(-diff($ode,y))), bfce:trigsimp(ratsimp($ode+y*afce)),minusa:trigsimp(ratsimp(-afce)),(print(\"mawinta\",minusa,\"mawinta\"), integrate(minusa,x)),ww:shorter(exp(integrate(minusa,x))), diff(ww,x), trigsimp(ratsimp(bfce/ww)), (print(\"mawintb\",ratsimp(bfce/ww),\"mawintb\"),integrate(ratsimp(bfce/ww),x)),Cecko:trigsimp(ratsimp(integrate(bfce/ww,x))), partres:shorter((Cecko * ww)),'diff(y,x)+y*(afce),C*ww,C*ww+partres,shorter(1/ww),shorter(-minusa),shorter(integrate(-minusa,x)), shorter(afce/ww)]");

    $reseni=$vystupmaxb[0];
    $koefa=$vystupmaxb[1];
    $koefb=$vystupmaxb[2];
    $koefaminus=$vystupmaxb[3];
    $koefaminusint=$vystupmaxb[4];
    $koefaminusintexp=$vystupmaxb[5];
    $koefaminusintexpder=$vystupmaxb[6];
    $pravastranaC=$vystupmaxb[7];
    $pravastranaCint=$vystupmaxb[8];
    $pravastranaCintrat=$vystupmaxb[9];
    $partres=$vystupmaxb[10];
    $obres=$vystupmaxb[13];
    
    $IF_integrala=$vystupmaxb[15];
    $IF_integrala_ev=$vystupmaxb[15];
    $IF_expintegrala=$vystupmaxb[14];
    $IF_b_expintegrala=$vystupmaxb[10];
    $IF_int_b_expintegrala=$vystupmaxb[13];
    $koefa_krat_IF_expintegrala=$vystupmaxb[17];
    
    $lhsrce = "y'+\\left($koefa\\right) y";
    
    print VYSTUP "{\\color{red}\\textbf{\\ODErovnicejelinearni}: {\\fboxsep=5pt\$\\boxed{$lhsrce =$koefb}\$}}\\par";
    print VYSTUP2 maw::makediv("!!ODErovnicejelinearni: \$$lhsrce =$koefb\$\n");
    
    
    $jehomogenni=1;
    print VYSTUP "\$ a(x)=$koefa\\ ,\\ \\ b(x)=$koefb \\ , \\ \\ \$";
    print VYSTUP2 maw::makediv("\$ a(x)=$koefa\$ , &nbsp;&nbsp;&nbsp;\$ b(x)=$koefb \$\n");
    $tempkoefb = $koefb;
    $tempkoefb =~ s/ //g;

#    print "MSG: b(x)=\"$tempkoefb\" \n";

    if ($tempkoefb eq "0") 
    {
#	print "MSG: homogenni \n";
	print VYSTUP "\\par \\ODErovnicejehomogenni";
	print VYSTUP2 "<div class=logickyBlok>!!ODErovnicejehomogenni";
	#$dodatek=0;
    }
    else
    {
#	print "MSG: nehomogenni \n";
	$jehomogenni=0;
	print VYSTUP "\\par \\ODEassochomrce: \\allowdisplaybreaks";
	print VYSTUP2 "<div class=logickyBlok><h2>!!Variacekonstant</h2>";
	print VYSTUP2 "<div class=logickyBlok>!!ODEassochomrce: ";
    }
    print VYSTUP "\\begin{align*}";
    print VYSTUP "y'&=\\left[$koefaminus\\right] y\\\\ ";
    print VYSTUP "\\frac1y \\,\\text{d}y&=\\left[$koefaminus\\right]\\,\\text{d}x\\\\ ";
    print VYSTUP "\\int\\frac1y \\,\\text{d}y&=\\href{\\mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int$koefaminus\\,\\text{d}x}\\\\ ";
    print VYSTUP "\\ln (y) &=$koefaminusint+\\widetilde C\\\\ ";
    print VYSTUP "y &= $vystupmaxb[12] \\\\ ";
    print VYSTUP "\\end{align*}";

    print VYSTUP2 "\\begin{align*}";
    print VYSTUP2 "y'&=\\left[$koefaminus\\right] y\\\\ ";
    print VYSTUP2 "\\frac1y \\,\\text{d}y&=\\left[$koefaminus\\right]\\,\\text{d}x\\\\ ";
    print VYSTUP2 "\\int\\frac1y \\,\\text{d}y&=\\href{$mawphphome/maw/integral/integralx.php?$integralone;lang=$lang}{\\int$koefaminus\\,\\text{d}x}\\\\ ";
    print VYSTUP2 "\\ln (y) &=$koefaminusint+\\widetilde C\\\\ ";
    print VYSTUP2 "y &= $vystupmaxb[12] \\\\ ";
    print VYSTUP2 "\\end{align*}\n</div>";
    if ($jehomogenni == 0)
    {
	print VYSTUP "\\ODEvarconst \\begin{align*}y_p&=C(x)\\left[ $koefaminusintexp\\right]\\\\ y'_p&= C'(x)\\left[ $koefaminusintexp\\right]+C(x)\\left[$koefaminusintexpder\\right] \\end{align*}";	
	print VYSTUP2 "<div class=logickyBlok>!!ODEvarconst \\begin{align*}y_p&=C(x)\\left[ $koefaminusintexp\\right]\\\\ y'_p&= C'(x)\\left[ $koefaminusintexp\\right]+C(x)\\left[$koefaminusintexpder\\right] \\end{align*}\n";
	
	$ttt= "\\begin{gather*}C'(x)\\left[ $koefaminusintexp\\right]+{\\color{blue}C(x)\\left[$koefaminusintexpder\\right]+\\left[$koefa\\right] \\cdot C(x)\\left[ $koefaminusintexp\\right]}=$koefb\\\\ C'(x)= $pravastranaC\\\\C(x)=\\href {\\mawserver/integral/integralx.php?$integraltwo;lang=$lang}{\\int $pravastranaC\\,\\text{d}x}\\\\ C(x)=	$pravastranaCint\\end{gather*}\\ODEpartres \\\\ \$ y_p(x)= \\left[$pravastranaCint\\right] \\cdot \\left[$koefaminusintexp\\right] = $partres\$ \\\\ \\ODEobres\\\\ \$ y(x)= $obres\$ ";
	$ttt2= "\\begin{gather*}C'(x)\\left[ $koefaminusintexp\\right]+{C(x)\\left[$koefaminusintexpder\\right]+\\left[$koefa\\right] \\cdot C(x)\\left[ $koefaminusintexp\\right]}=$koefb\\\\ C'(x)= $pravastranaC\\\\C(x)=\\href {$mawphphome/maw/integral/integralx.php?$integraltwo;lang=$lang}{\\int $pravastranaC\\,\\text{d}x}\\\\ C(x)=	$pravastranaCint\\end{gather*}\n</div> ".maw::makediv("!!ODEpartres: \$ y_p(x)= \\left[$pravastranaCint\\right] \\cdot \\left[$koefaminusintexp\\right] = $partres\$ <br>!!ODEobres: \$ y(x)= $obres\$ ");


	$ttt =~ s/\+ -/-/g;
	$ttt2 =~ s/\+ -/-/g;
	print VYSTUP $ttt;
	print VYSTUP2 $ttt2;

	print VYSTUP "\\let\\test\\relax ";
    }
    if ($tempkoefb eq "0") 
    {
	print VYSTUP "\\let\\test\\relax ";
	exit;
    }

    print VYSTUP2 "</div>";
    print VYSTUP2 "<div class=logickyBlok><h2>!!Integracnifaktor</h2>";
    print VYSTUP2 "<div class=logickyBlok>!!Vypocetintegracnihofaktoru";
    print VYSTUP2 "\\begin{align*}";
    print VYSTUP2 "\\int a(x) \\mathrm{d}x&=\\int $IF_integrala\\mathrm{d}x=$IF_integrala\\\\";
    print VYSTUP2 "e^{\\int a(x) \\mathrm{d}x}&=$IF_expintegrala";
    print VYSTUP2 "\\end{align*}";
    print VYSTUP2 "</div>";
    print VYSTUP2 "<div class=logickyBlok>!!Resenipomociintegracnihofaktoru";
    print VYSTUP2 "\\begin{align*}";
    print VYSTUP2 "$lhsrce &=$koefb&\\Bigl|\\qquad\\times \\left( $IF_expintegrala\\right)\\\\";
    print VYSTUP2 " y'\\cdot\\left($IF_expintegrala\\right)+\\left($koefa_krat_IF_expintegrala\\right) \\cdot y &= $pravastranaC \\\\";
    print VYSTUP2 "\\left[y \\cdot  \\left($IF_expintegrala\\right)\\right]'&= $pravastranaC\\\\";
    print VYSTUP2 "y \\cdot \\left($IF_expintegrala\\right)&=\\int $pravastranaC \\mathrm{d}x\\\\";
    print VYSTUP2 "y \\cdot \\left($IF_expintegrala\\right)&= $pravastranaCint +C&\\Bigl|\\qquad\\div \\left( $IF_expintegrala\\right)\\\\";
    print VYSTUP2 "y &= $obres";
    print VYSTUP2 "\\end{align*}";
    print VYSTUP2 "</div>";
    print VYSTUP2 "</div>";

}



$separaceAjeOK=1;

########################################################
# 3. separated variables
# y'=f(x)g(y), we try to find f and g
if ($determinant=~/^ *0 *$/)
{
#    print "MSG: zkousim separovat \n";

    @vystupmaxb=maximatexlist("[funkce:$ode,
(block([inflag:true],xpart:[],ypart:[],flag:false,
   eq: factor($ode),
   if atom(eq) or not(inpart(eq,0)=\\\"*\\\") then eq: [eq],
   for u in eq do
      if freeof(x,u) then (print (u),ypart: cons(u,ypart)) else
      if freeof(y,u) then (print (u),xpart: cons(u,xpart)) else return((print(\\\"Separation failed\\\"), flag: true)),
   if xpart = [] then xpart: 1 else xpart: apply(\\\"*\\\",xpart),
   if ypart = [] then ypart: 1 else ypart: apply(\\\"*\\\",ypart)
), if flag=true then fceg:fullratsimp(trigsimp((exp(integrate(fullratsimp(diff(log(funkce),y)),y))))) else 
fceg:ypart), if flag=true then fcef:fullratsimp(trigsimp(funkce/fceg))  else fcef:xpart,fcegA:fullratsimp(trigsimp(1/fceg)),intfcef:integrate(fcef,x),intg:integrate(fcegA,y), (load(\\\"$shorter\\\"),vyslf:shorter((intfcef))),vyslg:shorter((intg)),is (string(intg)=string(vyslg)) and is(string(intfcef)=string(vyslf)),print(\"mawinta\",fcef,\"mawinta\"),print(\"mawintb\",fcegA,\"mawintb\")]");


    $funkceg=$vystupmaxb[1];
    $funkcef=$vystupmaxb[2];
    $test = $funkceg;   ## osetrit exp
    $test =~ s/exp/ep/g;

    if (($test =~ /x/) or ($funkcef =~ /y/))
    {
	$separaceAjeOK=0;
    }
    else
    {
    $funkcegA=$vystupmaxb[3];
    $funkcefint=$vystupmaxb[4];
    $funkcegint=$vystupmaxb[5];
    $funkcefintupr=$vystupmaxb[6];
    $funkcegintupr=$vystupmaxb[7];

    print VYSTUP "{\\par\\color{red}\\textbf{\\ODEseparace }}\\par\n";
    print VYSTUP2 "<h3>!!ODEseparace</h3>\n";

    print VYSTUP "\\textit{\\ODEnekres}:";
    print VYSTUP2 "<div class=logickyBlok><h3>!!ODEnekres</h3>";

    print VYSTUP "\\begin{align*} \\frac{\\text{d}y}{\\text{d}x}&=\\left($funkcef\\right)\\cdot\\left($funkceg\\right) \\\\ \n \\left($funkcegA\\right) \\,\\text{d}y&=\\left($funkcef\\right)\\,\\text{d}x \\\\ \n";
    print VYSTUP2 "\\begin{align*} \\frac{\\text{d}y}{\\text{d}x}&=\\left($funkcef\\right)\\cdot\\left($funkceg\\right) \\\\ \n \\left($funkcegA\\right) \\,\\text{d}y&=\\left($funkcef\\right)\\,\\text{d}x \\\\ \n";

    print VYSTUP " \\href{\\mawserver/integral/integraly.php?$integraltwo;lang=$lang}{\\int$funkcegA \\,\\text{d}y}&=\\href{\\mawserver/integral/integralx.php?$integralone;lang=$lang}{\\int$funkcef\\,\\text{d}x} \\\\ \n";
    print VYSTUP2 " \\href{/maw/integral/integraly.php?$integraltwo;lang=$lang}{\\int$funkcegA \\,\\text{d}y}&=\\href{/maw/integral/integralx.php?$integralone;lang=$lang}{\\int$funkcef\\,\\text{d}x} \\\\ \n";

    print VYSTUP " $funkcegint&=$funkcefint+C \\\\ \n";
    print VYSTUP2 " $funkcegint&=$funkcefint+C \\\\ \n";

    if  ($vystupmaxb[8] =~ /false/)
    {
       print VYSTUP " $funkcegintupr&=$funkcefintupr+C \\\\ \n";
       print VYSTUP2 " $funkcegintupr&=$funkcefintupr+C \\\\ \n";
    }

    print VYSTUP "\\end{align*}\n";
    print VYSTUP2 "\\end{align*}\n</div>";

    @konstrespole=maximatexlist("[(res:maw_solve_in_domain($ode,[$ode],y),map(rhs,res))]");

    $konstresstring=join(",",@konstrespole);
    $konstresstring=~s/\\left\[//g;
    $konstresstring=~s/\\right\]//g;
    @konstrespole=split(',',$konstresstring);
    $n=0;
    $konstres="";
    $konstres2="";
    $carka=" ";
    foreach $solution (@konstrespole)
    {
      $n=$n+1;
      if ($n>1) {$carka=",\\ ";}
      $konstres=$konstres."\\ $carka y_$n=$solution";
      $konstres2=$konstres2."\\ $carka y_$n=$solution";
    }

    $konstresstring=~s/ //g;
    if ($konstresstring eq "") {$konstres="\\ODEnotfound";$konstres2="!!ODEnotfound";}

    print VYSTUP "\\textit{\\ODEkonstres}: \$ $konstres \$ \\par\\medskip\\hrule\\medskip\n";
    print VYSTUP2 maw::makediv("<h3>!!ODEkonstres</h3> \$ $konstres2 \$");

    print VYSTUP "\\let\\test\\relax ";
    exit;
    }
}


########################################################
#4. separated, do not know how to separate
## hlasi se jako separovana
## separated, but separation of variables failed
## probably never executed after adopting code from 
## ode2.mac to separation of variables.
if ($separaceAjeOK==0)
{
    if ($metoda=~/separa/)
    {
	print VYSTUP "{\\par\\color{red}\\textbf{\\ODEseparace }} \\par \\textit{\\ODEnekres}:";
	@vystupmaxb=maximatexlist("[vysledek:ode2('diff(y,x)=$ode, y, x),method,lsr:trigsimp(ratsimp(diff(first(vysledek),y))),psr:trigsimp(ratsimp(diff(last(vysledek),x))),integrate(lsr,y), integrate(psr,x),first(vysledek),last(vysledek),(print(\"mawinta\",lsr,\"mawinta\"),print(\"mawintb\",psr,\"mawintb\",$ode))]");
	
	$reseni=$vystupmaxb[0];
	$lstrder=$vystupmaxb[2];
	$pstrder=$vystupmaxb[3];
	$lstrneupr=$vystupmaxb[4];
	$pstrneupr=$vystupmaxb[5];
	$lstr=$vystupmaxb[6];
	$pstr=$vystupmaxb[7];
	$odetex=$vystupmaxb[8];
	$pstr=~ s/\\%c/ C /gs;

	$temprce = $rce;
	$temprce =~ s/=/&=/gs;
	$temprce =~ s/y\'/\\frac{\\text{d}y}{\\text{d}x}/gs;

	print VYSTUP "\\begin{align*}\n $temprce\\\\ \n";
	print VYSTUP " \\frac{\\text{d}y}{\\text{d}x}&=$odetex\\\\ \n";
	print VYSTUP " \\left($lstrder\\right)\\text{d}y  &=\\left($pstrder\\right)\\text{d}x\\\\\n";
	print VYSTUP " \\href{\\mawserver/integral/integraly.php?$integralone;lang=$lang}{\\int $lstrder\\,\\text{d}y} &= \\href{\\mawserver/integral/integralx.php?$integraltwo;lang=$lang}{\\int $pstrder\\,\\text{d}x}\\\\\n ";
	print VYSTUP " $lstrneupr  &=$pstrneupr+C\\\\\n ";
	print VYSTUP " $lstr  &=$pstr \n";
	print VYSTUP "\\end{align*}\n";

    @konstrespole=maximatexlist("[(res:maw_solve_in_domain($ode,[$ode,lhs($fullode),rhs($fullode)],y),map(rhs,res))]");

    $konstresstring=join(",",@konstrespole);
    $konstresstring=~s/\\left\[//g;
    $konstresstring=~s/\\right\]//g;
    @konstrespole=split(',',$konstresstring);
    $n=0;
    $konstres="";
    $carka=" ";
    foreach $solution (@konstrespole)
    {
      $n=$n+1;
      if ($n>1) {$carka=",\\ ";}
      $konstres=$konstres."\\ $carka y_$n=$solution";
    }

    $konstresstring=~s/ //g;
    if ($konstresstring eq "") {$konstres="\\ODEnotfound";}

    print VYSTUP "\\textit{\\ODEkonstres}: \$ $konstres \$ \\par\\medskip\\hrule\\medskip\n";

	print VYSTUP "\\let\\test\\relax ";
        exit;	
	
    }
}

if ($metoda=~/exact/)
{
    @vystupmaxb=maximatexlist("[(load(\\\"$shorter\\\"),vysledek:ode2($fullode, y, x),$ode),
f:lhs(vysledek),
derx:shorter(diff(f,x)),
dery:shorter(diff(f,y)),
(simp:false,derx*dx+dery*dy),
(simp:true,shorter(diff(derx,y))),
i1:integrate(derx,x),
diff(i1,y),
Cder:radcan(dery-diff(i1,y)),
if freeof(x,Cder) then print (\"OK\") else 0,
C:integrate(Cder,y),
ff:i1+C,
if is(radcan((diff(ff,x)/diff(ff,y))+($ode))=0) then print (\"OK\") else 0,
i2:integrate(dery,y),
diff(i2,x),
Cder:radcan(derx-diff(i2,x)),
if freeof(y,Cder) then print (\"OK\") else 0,
C:integrate(Cder,x),
ff:i2+C,
if is(radcan((diff(ff,x)/diff(ff,y))+($ode))=0) then print (\"OK\") else 0,
print(\"mawinta\",derx,\"mawinta\"),
print(\"mawintb\",psr,\"mawintb\")]");

$separator="\n\n\n";
print VYSTUP $separator;
print VYSTUP "\\parskip 10pt ";

#print VYSTUP2 maw::makediv("The equation is recognized as exact equation. Full steps in the solution are available, but not in html version. For more details switch to the PDF version in the submitted form");

printf VYSTUP (gettext("The equation %s is exact if and only if %s."),"\$ M(x,y)\\text{d}x+N(x,y)\\text{d}y=0\$","\$ M'_y(x,y)=N'_x(x,y) \$");
print VYSTUP2 "<div class=logickyBlok>".(sprintf(gettext("The equation %s is exact if and only if %s."),"\$ M(x,y)\\text{d}x+N(x,y)\\text{d}y=0\$","\$ M'_y(x,y)=N'_x(x,y) \$"));
print VYSTUP $separator;

$vystupmaxb[4]=~s/{\\it dx}/\\,\\text{d}x\\,/g;
$vystupmaxb[4]=~s/{\\it dy}/\\,\\text{d}y\\,/g;

if ((!($intfactor=="1"))&&($intfactor!~/false/))
{
    printf VYSTUP (gettext("The equation has integrating factor %s."),"\$ $intfactor\$");
    print VYSTUP2 "<br> ".(sprintf(gettext("The equation has integrating factor %s."),"\$ $intfactor\$"));
    print VYSTUP $separator;
}


printf VYSTUP (gettext("Our equation can be written in the form %s."),"\$ $vystupmaxb[4] =0\$");
print VYSTUP2 "<br> ".(sprintf(gettext("Our equation can be written in the form %s."),"\$ $vystupmaxb[4] =0\$"));

print VYSTUP $separator;

printf VYSTUP (gettext("Denote %s and %s."),"\$ M(x,y)= $vystupmaxb[2]\$","\$ N(x,y)= $vystupmaxb[3]\$");
print VYSTUP2 " ".(sprintf(gettext("Denote %s and %s."),"\$ M(x,y)= $vystupmaxb[2]\$","\$ N(x,y)= $vystupmaxb[3]\$"));

print VYSTUP $separator;

printf VYSTUP (gettext("We have %s"),"\$ M'_y(x,y)= $vystupmaxb[5]=N'_x(x,y)\$");
print VYSTUP2 " ".(sprintf(gettext("We have %s"),"\$ M'_y(x,y)= $vystupmaxb[5]=N'_x(x,y)\$"));

print VYSTUP $separator;
printf VYSTUP (gettext("If the function %s satisfies %s and %s, then the solution is %s, where %s."),'$F(x,y)$','$F\'_x(x,y)=M(x,y)$','$F\'_y(x,y)=N(x,y)$','$F(x,y)=C$','$C\in\mathbb{R}$');
print VYSTUP2 "<br> ".(sprintf(gettext("If the function %s satisfies %s and %s, then the solution is %s, where %s."),'$F(x,y)$','$F\'_x(x,y)=M(x,y)$','$F\'_y(x,y)=N(x,y)$','$F(x,y)=C$','$C\in\mathbb{R}$'))."</div>";
print VYSTUP "\\bigskip\\null ";

    print VYSTUP $separator;
    
$variant=0;

if (($vystupmaxb[9]=~/OK/) && ($vystupmaxb[12]=~/OK/))    
{
    $variant=$variant+1;
    print VYSTUP "\\hrule \\textbf{".sprintf(gettext("Variant %s"),$variant)."} \\par ";
    print VYSTUP2 "<h3>".sprintf(gettext("Variant %s"),$variant)."</h3>";
    print VYSTUP sprintf(gettext("Integrating with respect to \$ x\$ we get %s."),"\\\\\$ F(x,y)=\\int M(x,y)\\,\\text{d}x=\\int $vystupmaxb[2] \\,\\text{d}x = $vystupmaxb[6] +C(y)\$");
    print VYSTUP2 maw::makediv(sprintf(gettext("Integrating with respect to \$ x\$ we get %s."),"\$ F(x,y)=\\int M(x,y)\\,\\text{d}x=\\int $vystupmaxb[2] \\,\\text{d}x = $vystupmaxb[6] +C(y)\$"));
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Differentiating with respect to \$ y\$ we get %s."),"\$ F'_y(x,y) = $vystupmaxb[7] +C'(y)\$");
    print VYSTUP2 "<div class=logickyBlok>".sprintf(gettext("Differentiating with respect to \$ y\$ we get %s."),"\$ F'_y(x,y) = $vystupmaxb[7] +C'(y)\$");
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("We have %s."),"\$ F'_y(x,y)=N(x,y)=$vystupmaxb[3]\$");
    print VYSTUP2 " &nbsp;&nbsp;".sprintf(gettext("We have %s."),"\$ F'_y(x,y)=N(x,y)=$vystupmaxb[3]\$");
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Hence %s."),"\$ C'(y)=$vystupmaxb[8]\$");
    print VYSTUP2 " &nbsp;&nbsp;".sprintf(gettext("Hence %s."),"\$ C'(y)=$vystupmaxb[8]\$");
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Integrating with respect to \$ y\$ we get %s."),"\$ C(y)=\\int $vystupmaxb[8]\\,\\text{d}y=$vystupmaxb[10]\$");
    print VYSTUP2 " &nbsp;&nbsp;".sprintf(gettext("Integrating with respect to \$ y\$ we get %s."),"\$ C(y)=\\int $vystupmaxb[8]\\,\\text{d}y=$vystupmaxb[10]\$")."</div>";
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("The solution is %s."),"\$ $vystupmaxb[11]=C\$");
    print VYSTUP2 maw::makediv(sprintf(gettext("The solution is %s."),"\$ $vystupmaxb[11]=C\$"));
    print VYSTUP $separator;
    print VYSTUP "\\let\\test\\relax ";
    print VYSTUP $separator;
    print VYSTUP "\\bigskip\\null ";
}

if (($vystupmaxb[16]=~/OK/) && ($vystupmaxb[19]=~/OK/))    
{
    $variant=$variant+1;
    print VYSTUP "\\hrule \\textbf{".sprintf(gettext("Variant %s"),$variant)."} \\par ";
    print VYSTUP2 "<h3>".sprintf(gettext("Variant %s"),$variant)."</h3>";
    print VYSTUP sprintf(gettext("Integrating with respect to \$ y\$ we get %s."),"\\\\\$ F(x,y)=\\int N(x,y)\\,\\text{d}y=\\int $vystupmaxb[3] \\,\\text{d}y = $vystupmaxb[13] +C(x)\$");
    print VYSTUP2 maw::makediv(sprintf(gettext("Integrating with respect to \$ y\$ we get %s."),"\$ F(x,y)=\\int N(x,y)\\,\\text{d}y=\\int $vystupmaxb[3] \\,\\text{d}y = $vystupmaxb[13] +C(x)\$"));
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Differentiating with respect to \$ x\$ we get %s."),"\$ F'_x(x,y) = $vystupmaxb[14] +C'(x)\$");
    print VYSTUP2 "<div class=logickyBlok>".sprintf(gettext("Differentiating with respect to \$ x\$ we get %s."),"\$ F'_x(x,y) = $vystupmaxb[14] +C'(x)\$");
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("We have %s."),"\$ F'_x(x,y)=M(x,y)=$vystupmaxb[2]\$");
    print VYSTUP2 " &nbsp;&nbsp;".(sprintf(gettext("We have %s."),"\$ F'_x(x,y)=M(x,y)=$vystupmaxb[2]\$"));
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Hence %s."),"\$ C'(x)=$vystupmaxb[15]\$");
    print VYSTUP2 " &nbsp;&nbsp;".(sprintf(gettext("Hence %s."),"\$ C'(x)=$vystupmaxb[15]\$"));
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("Integrating with respect to \$ x\$ we get %s."),"\$ C(x)=\\int $vystupmaxb[15]\\,\\text{d}x=$vystupmaxb[17]\$");
    print VYSTUP2 " &nbsp;&nbsp;".sprintf(gettext("Integrating with respect to \$ x\$ we get %s."),"\$ C(x)=\\int $vystupmaxb[15]\\,\\text{d}x=$vystupmaxb[17]\$")."</div>";
    print VYSTUP $separator;
    print VYSTUP sprintf(gettext("The solution is %s."),"\$ $vystupmaxb[18]=C\$");
    print VYSTUP2 maw::makediv(sprintf(gettext("The solution is %s."),"\$ $vystupmaxb[18]=C\$"));
    print VYSTUP $separator;
    print VYSTUP "\\let\\test\\relax ";
    print VYSTUP "\\bigskip\\null ";
}

    print VYSTUP "\\integralfalse ";
    print VYSTUP "\\constantmsgfalse ";

    exit;

}


if ($metoda=~/bernoulli/)
{
    @vystupmaxb=maximatexlist("[(load(\\\"$shorter\\\"),vysledek:ode2($fullode, y, x),$ode),
n:odeindex,
a1: -coeff(eq: expand($ode),y,1),
a2: coeff(eq,y,odeindex),
'diff(y,x)+a1*y=a2*y^n,
'diff(y,x)/y^n+a1*y^(1-n)=a2,
w=y^(1-n),
(1-n)*y^(-n)*W,
W+(1-n)*a1*w=(1-n)*a2,
(inta1:integrate((1-n)*a1,x), solw:exp(-inta1)*(C+integrate((1-n)*a2*exp(inta1),x)),solw:shorter(solw)),
print(\"mawinta\",subst(w=y,W+(1-n)*a1*w=(1-n)*a2),\"mawinta\"),
y^(1-n)=solw,
y=shorter(solw^(1/(1-n)))
]");

#    print "<h1>Bernoulli ODE: Under construction</h1>";
#    print @vystupmaxb;

    printf VYSTUP ("\\par ".gettext("The normal form of the equation is %s.")," \$ ".remove_diff($vystupmaxb[4])." \$");
    printf VYSTUP2 ("<br> ".gettext("The normal form of the equation is %s.")," \$ ".remove_diff($vystupmaxb[4])." \$");

    printf VYSTUP ("\\par ".gettext("This equation can be written as %s.")," \$ ".remove_diff($vystupmaxb[5])." \$");
    printf VYSTUP2 ("<br> ".gettext("This equation can be written as %s.")," \$ ".remove_diff($vystupmaxb[5])." \$");

    printf VYSTUP ("\\par ".gettext("We use substitution %s."),"\$ $vystupmaxb[6] \$");
    printf VYSTUP2 ("<br> ".gettext("We use substitution %s."),"\$ $vystupmaxb[6] \$");

$vystupmaxb[7] =~ s/W/y'/g;
    printf VYSTUP ("\\par ".gettext("Differentiating we get %s."),"\$ w'=$vystupmaxb[7] \$");
    printf VYSTUP2 ("<br> ".gettext("Differentiating we get %s."),"\$ w'=$vystupmaxb[7] \$");

$vystupmaxb[8] =~ s/W/w'/g;
$integralone =~ s/W/y'/g;
    printf VYSTUP ("\\par ".gettext("Substituting we get linear equation  %s with general solution %s (click %s to get more details)."),"\$\$ ".($vystupmaxb[8])." \$\$","\$\$ w=$vystupmaxb[9] \$\$","\\href{\\mawserver/ode/solveode.php?$integralone;$lang}{".gettext("here")."}");
    printf VYSTUP2 ("<br> ".gettext("Substituting we get linear equation  %s with general solution %s (click %s to get more details)."),"\$\$ ".($vystupmaxb[8])." \$\$","\$\$ w=$vystupmaxb[9] \$\$","<a href='/maw/ode/solveode.php?".uri_escape($integralone).";".$lang."'>".gettext("here")."</a>");

$vystupmaxb[11] =~ s/\\it *\\%c/C/g;

    printf VYSTUP (" ".gettext("The back substitution gives %s and %s"),"\$\$ $vystupmaxb[11] \$\$","\$\$ $vystupmaxb[12] \$\$");
    printf VYSTUP2 ("<br> ".gettext("The back substitution gives %s and %s"),"\$\$ $vystupmaxb[11] \$\$","\$\$ $vystupmaxb[12] \$\$");

    print VYSTUP "\\let\\test\\relax ";
    print VYSTUP "\\test ";
    print VYSTUP "\\end{document} ";

    exit;

}
