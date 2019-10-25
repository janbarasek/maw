# Mathematical Assistant on Web - web interface for mathematical          
# computations including step by step solutions
# Copyright 2007-2009 Robert Marik, Miroslava Tihlarikova
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

sub modifiedprint
{
    print VYSTUP "\\def\\".$_[0]."{".$_[1]."}\n";
    print VYSTUP2 "\$"."$_[0]='$_[1]';\n";
}
 
$shorter=$mawhome."/common/shorter.mac";

########################################################
## podobne, ale argumentem je pole. pokud dojde k chybe, zapise se
## vystup do souboru output
sub maximatexlist
{
    my ($prikaz)=@_;
    my $retezec=`$mawtimeout $maxima --batch-string=\"(load(\\\"$shorter\\\"),e:%e,pi:%pi,tex($prikaz));\"`;

    if (maw::maximaerror($retezec))    
    {
	$link=$mawhtmlhome."?form=minmax3d&function=".uri_escape($f)."&akce=1&auto=1&lang=".$lang;
	saveoutput("<h2 class='red'>".gettext("Error in math or the given point does not belong to the domain.")."</h2><br>\n<hr> ".sprintf(gettext("Click %shere%s to find stationary points by computer."),"<a href=\"$link\">","</a>")."<hr></body></html><pre>".$retezec."</pre>");
	die ();
    }
    $prikaz="echo \"<pre>".$retezec."</pre>\" >>outputmax";
    system ($prikaz);
    $retezec =~ s/\n//gs;
    $retezec =~ s/\\,/{}/gs;
    $retezec =~ s/\\log/\\ln/gs;
    @vystup = $retezec =~ /\$\$\\left\[(.*)\\right\] \$\$/gs;
    @pole = split (",",$vystup[0]);
    return @pole;
}

########################################################

$akce =~ s/ //g;

if ($akce eq "0")
{
    
    @vystupmax=maximatexlist("[$f,derx:shorter(diff($f,x)),dery:shorter(diff($f,y)),radcan(ev(derx,x=$xs,y=$ys)),radcan(ev(dery,x=$xs,y=$ys)),A:shorter(diff($f,x,2)),B:shorter(diff($f,x,1,y,1)),shorter(D:diff($f,y,2)),AA:ev(A,x=$xs,y=$ys),BB:ev(B,x=$xs,y=$ys),DD:ev(D,x=$xs,y=$ys),M:matrix([A,B],[B,D]),MM:matrix([AA,BB],[BB,DD]),H:determinant(MM),$xs,$ys,if (H<0) then -1 elseif (H=0) then 0 elseif (AA>0) then 1 else 2,H,AA,ev($f,x=$xs,y=$ys),if freeof(%i,float(ev($f,x=$xs,y=$ys))) then 1 else %i]");
    
#
    
    $test=$vystupmax[3].$vystupmax[4];
    $test =~ s/ //g;
    $stbod=$vystupmax[14].",".$vystupmax[15];
    
    $funkcnihodnota=$vystupmax[20];
    if ($funkcnihodnota=~/i/) {$test="11";}
    if (not (($test eq "00"))) 
    {
	#$link="../../maw/minmax3d/minmax3d.php?funkcef=".uri_escape($f)."&akce=1&lang=".$lang;
        $link=$mawhtmlhome."?form=minmax3d&function=".uri_escape($f)."&akce=1&auto=1&lang=".$lang;
    	saveoutput("<h2 class='red'>".sprintf(gettext("Error: The point %s is not a stationary point of the function %s"),"[$xs,$ys]","z=$f")."</h2>\n<hr>".sprintf(gettext("Click %shere%s to find stationary points by computer."),"<a href=\"$link\">","</a>")." <hr></body></html>");
	die;
    }

    $funkcetex=`echo "$f" | formconv `;
    
    print VYSTUP "\\jedentrue\n";
    &modifiedprint ("stbod", "$stbod");
    &modifiedprint ("ftex", "$funkcetex");
    &modifiedprint ("dfx", "$vystupmax[1]");
    &modifiedprint ("dfy", "$vystupmax[2]");
    &modifiedprint ("dfxx", "$vystupmax[5]");
    &modifiedprint ("dfxy", "$vystupmax[6]");
    &modifiedprint ("dfyy", "$vystupmax[7]");
    &modifiedprint ("dfxxs", "$vystupmax[8]");
    &modifiedprint ("dfxys", "$vystupmax[9]");
    &modifiedprint ("dfyys", "$vystupmax[10]");
    &modifiedprint ("hessovamatice", "$vystupmax[11]");
    &modifiedprint ("hessovamatices", "$vystupmax[12]");
    &modifiedprint ("determinant", "$vystupmax[13]");
    
    $flag=$vystupmax[16];
    $flag =~ s/ //g;
    
    chomp ($funkcetex);
    $ret=" \${z= ".$funkcetex."} \$ ";
    
    if ($flag=="-1")	
    { 
	&modifiedprint ("conclusion",sprintf(gettext("The function %s  has a \\textbf{saddle point} at %s, there is no local extremum at this point.  (Hessian is negative.)"),$ret,"\$\\left[".$stbod."\\right]\$"));
    }
    
    if ($flag=="0")
    { 
	&modifiedprint ("conclusion",sprintf(gettext("We can \\textbf{neither proove nor disprove} existence of extrem for the function %s and stationary point %s from second derivatives. (Hessian is zero.)"),$ret,"\$\\left[".$stbod."\\right]\$"));
    }
    
    if ($flag=="1")
    { 
	&modifiedprint ("conclusion",sprintf(gettext("The function %s  has a \\textbf{local minimum} at %s. (Hessian is positive and there are positive numbers in the main diagonal.)"),$ret,"\$\\left[".$stbod."\\right]\$"));
    }
    
    if ($flag=="2")
    { 
	&modifiedprint ("conclusion",sprintf(gettext("The function %s  has a \\textbf{local maximum} at %s. (Hessian is positive and there are negative numbers in the main diagonal.)"),$ret,"\$\\left[".$stbod."\\right]\$"));
    }
    
    
}
else
{
    $prikaz="[$f,derx:shorter(diff($f,x)),dery:shorter(diff($f,y)),A:shorter(diff($f,x,2)),B:shorter(diff($f,x,1,y,1)),D:shorter(diff($f,y,2)),M:matrix([A,B],[B,D])";
    print VYSTUP "\\jedenfalse\n";
    $xs =~ s/\]//g;
    $xs =~ s/\[//g;
    $xs =~ s/ //g;
    @polebodu=split (";",$xs);
    $pocetbodu=@polebodu;
    for ($i=0;$i<$pocetbodu;$i=$i+1)
    {
	@jedenbod=split(",",$polebodu[$i]);
	$xs=$jedenbod[0];
	$ys=$jedenbod[1];
	$prikaz=$prikaz.",$xs,$ys,AA:ratsimp(ev(A,x=$xs,y=$ys)),BB:ratsimp(ev(B,x=$xs,y=$ys)),DD:ratsimp(ev(D,x=$xs,y=$ys)),MM:matrix([AA,BB],[BB,DD]),H:determinant(MM),if (H<0) then -1 elseif (H=0) then 0 elseif (AA>0) then 1 else 2, radcan(ev(derx,x=$xs,y=$ys)),radcan(ev(dery,x=$xs,y=$ys)),ev($f,x=$xs,y=$ys),if freeof(%i,float(ev($f,x=$xs,y=$ys))) then 1 else %i";
    }
    $prikaz=$prikaz."]";
    @vystupmax=maximatexlist($prikaz);


    $kontrola="";
    $bodytab="";
    $fxxtab="";
    $fxytab="";
    $fyytab="";
    $Htab="";
    $concltab="";
    $templ="|c|";

    for ($i=0;$i<$pocetbodu;$i=$i+1)
    {
	$j=$i+1;
	$txx=12*$i+9;
	$txy=12*$i+10;
	$tyy=12*$i+11;
	$kx=12*$i+15;
	$ky=12*$i+16;
	$kf=12*$i+18;
	$templ=$templ."c\|";
	$bodytab=$bodytab."\$\\fboxrule 0pt\\boxed{S_{$j}=\\left[$vystupmax[(12*$i)+7],$vystupmax[(12*$i)+8]\\right]}\$";
	$fxxtab=$fxxtab."\$ \\boxed{$vystupmax[$txx]}\$";
	$fxytab=$fxytab."\$ \\boxed{$vystupmax[$txy]}\$";
	$fyytab=$fyytab."\$ \\boxed{$vystupmax[$tyy]}\$";
	$Htab=$Htab."\$ \\boxed{$vystupmax[$tyy+2]}\$";
	$zaver="$vystupmax[$tyy+3]";
	$zaver =~ s/ //g;
	if ($zaver eq "-1")	
	{$concltab=$concltab.gettext("saddle point");}
	elsif ($zaver eq "0")		{$concltab=$concltab.gettext("no conclusion");}
	elsif ($zaver eq "1")		{$concltab=$concltab.gettext("minimum");}
	else {$concltab=$concltab.gettext("maximum");}
	$kontrola="$vystupmax[$kx] $vystupmax[$ky]";
	$kontrola =~ s/ //g;
	$kontrola =~ s/0//g;
	if (not($kontrola eq "") || ($vystupmax[$kf]=~/i/))
	{
	    #$link="../../maw/minmax3d/minmax3d.php?funkcef=".uri_escape($f)."&akce=1&lang=".$lang;
            $link=$mawhtmlhome."?form=minmax3d&function=".uri_escape($f)."&akce=1&auto=1&lang=".$lang;
	    saveoutput("<h2 class='red'>".sprintf(gettext("Error: The point %s is not a stationary point of the function %s"),"[$polebodu[$i]]","z=$f")."</h2>\n<hr>".sprintf(gettext("Click %shere%s to find stationary points by computer."),"<a href=\"$link\">","</a>")." <hr></body></html>");
	    die;
	}
	
	if ($i<($pocetbodu-1)) 
	{
	    $bodytab=$bodytab." & "; 
	    $fxxtab=$fxxtab." & "; 
	    $fxytab=$fxytab." & "; 
	    $fyytab=$fyytab." & "; 
	    $Htab=$Htab." & "; 
	    $concltab=$concltab." & "; 
	}
	
    }
    
    $funkcetex=`echo "$f" | formconv `;
    
    $tabulka="\\begin{tabular}{$templ}\\hline & $bodytab\\\\\n\\hline\\hline \$f^{\\prime\\prime}_{xx}\$ & $fxxtab\\\\\n\\hline \$f^{\\prime\\prime}_{xy}\$ & $fxytab\\\\\n\\hline \$f^{\\prime\\prime}_{yy}\$ & $fyytab\\\\\n\\hline\\hline ".gettext("Hessian")." & $Htab\\\\\n\\hline ".gettext("Conclusion").":& $concltab \\\\ \\hline\\end{tabular}";

&modifiedprint ("tabulka", "$tabulka");
&modifiedprint ("ftex", "$funkcetex");
&modifiedprint ("dfx", "$vystupmax[1]");
&modifiedprint ("dfy", "$vystupmax[2]");
&modifiedprint ("dfxx", "$vystupmax[3]");
&modifiedprint ("dfxy", "$vystupmax[4]");
&modifiedprint ("dfyy", "$vystupmax[5]");
&modifiedprint ("hess", "$vystupmax[6]");
    $bodytabsave=$bodytab;
$bodytab =~ s/&/,/g;
&modifiedprint ("body", "$bodytab");

$fxxtab =~ s/\\boxed/ /g;
$fxytab =~ s/\\boxed/ /g;
$fyytab =~ s/\\boxed/ /g;
$Htab =~ s/\\boxed/ /g;
$bodytabsave =~ s/\\boxed/ /g;
$bodytabsave =~ s/\\fboxrule 0pt/ /g;
    print VYSTUP2 "\$bodytab='$bodytabsave';\n";
    print VYSTUP2 "\$fxxtab='$fxxtab';\n";
    print VYSTUP2 "\$fxytab='$fxytab';\n";
    print VYSTUP2 "\$fyytab='$fyytab';\n";
    print VYSTUP2 "\$Htab='$Htab';\n";
    print VYSTUP2 "\$concltab='$concltab';\n";
    
}


print VYSTUP2 "?>\n";


