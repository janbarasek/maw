# Mathematical Assistant on Web - web interface for mathematical          
# computations including step by step solutions
# Copyright 2007-2013 Robert Marik
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


open(VYSTUP,">outputf.tex");

open(VYSTUP2,">data.php");
print VYSTUP2 "<?php \n";


open(HLAVA,"output.tex");
@pole=<HLAVA>;

$retezec="";

foreach $radek (@pole)
{
    chomp $radek;
    $retezec=$retezec.$radek
}

@novepole=split(/\$\$/, $retezec);

# Function
print VYSTUP "\\def\\funkce{".$novepole[1],"}\n";
print VYSTUP2 "\$funkceHTML = '$novepole[1] ';\n";

# Derivative
print VYSTUP "\\def\\derivace{".$novepole[3],"}\n";
print VYSTUP2 "\$derivaceHTML = '$novepole[3] ';\n";


# Stationary points, we remove complex roots and expressions like exp(x)=0
# We allow inly expressions of the form "x= ....."
print VYSTUP "\\def\\stac{";
print VYSTUP2 "\$stacHTML = '";


$radek=$novepole[7];
$radek=~s/\\left\[//g;
$radek=~s/\\right\]//g;
$radek=~s/\\,//g;
@reseni=split(',',$radek);
$i=0;
foreach $radek (@reseni)
{
@koren=split('=',$radek);
if (($koren[1] =~ /mbox/) or ($koren[0] eq " x") and ($koren[1] !~ "x"))
{
$radekkopie=$radek;
$radekkopie=~s/\\right//g;
$radekkopie=~s/\\pi//g;
if ($radekkopie !~ "i")
{
$i=$i+1;
if ($i>1) {print VYSTUP ",\\ "; print VYSTUP2 ",\\ "; }
print VYSTUP "x_",$i,"=",$koren[1];
print VYSTUP2 "x_",$i,"=",$koren[1];
}
}
}

print VYSTUP "}\n";
print VYSTUP2 "';\n";


# Second derivative
print VYSTUP "\\def\\druha{".$novepole[5],"}\n";
print VYSTUP2 "\$druhaHTML='".$novepole[5],"';\n";

# Critical points, similar to stationary points
print VYSTUP "\\def\\krit{";
print VYSTUP2 "\$kritHTML='";

$radek=$novepole[9];
$radek=~s/\\left\[//g;
$radek=~s/\\right\]//g;
$radek=~s/\\,//g;
@reseni=split(',',$radek);
$i=0;
foreach $radek (@reseni)
{
@koren=split('=',$radek);
if (($koren[1] =~ /mbox/) or ($koren[0] eq " x") and ($koren[1] !~ "x"))
{
$radekkopie=$radek;
$radekkopie=~s/\\right//g;
$radekkopie=~s/\\pi//g;
if ($radekkopie !~ "i")
{
$i=$i+1;
if ($i>1) {print VYSTUP ",\\ "; print VYSTUP2 ",\\ ";}
print VYSTUP "x_",$i,"=",$koren[1];
print VYSTUP2 "x_",$i,"=",$koren[1];
}
}
}


print VYSTUP "}\n";
print VYSTUP2 "';\n";

# zero points
print VYSTUP "\\def\\nuly{";
print VYSTUP2 "\$nulyHTML='";

$radek=$novepole[11];
$radek=~s/\\left\[//g;
$radek=~s/\\right\]//g;
$radek=~s/\\,//g;
@reseni=split(',',$radek);
$i=0;
foreach $radek (@reseni)
{
@koren=split('=',$radek);
if (($koren[1] =~ /mbox/) or ($koren[0] eq " x") and ($koren[1] !~ "x"))
{
$radekkopie=$radek;
$radekkopie=~s/\\right//g;
$radekkopie=~s/\\pi//g;
if ($radekkopie !~ "i")
{
$i=$i+1;
if ($i>1) {print VYSTUP ",\\ "; print VYSTUP2 ",\\ ";}
print VYSTUP "x_",$i,"=",$koren[1];
print VYSTUP2 "x_",$i,"=",$koren[1];
}
}
}


print VYSTUP "}\n";
print VYSTUP2 "';\n";


print VYSTUP2 "?>";
