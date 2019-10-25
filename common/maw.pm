# Mathematical Assistant on Web - web interface for mathematical
# computations including step by step solutions
# Copyright 2010 Robert Marik, Miroslava Tihlarikova
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


package maw;

use Exporter;
@ISA = qw(Exporter);
@EXPORT = qw(&saveoutput);

sub saveoutput
{
 my ($message)=@_;
 open(FILE, ">>output");    
 print FILE $message;
 close (FILE);
 return 1;
}

sub makediv
{
  my ($message)=@_;
  return "<div class=logickyBlok> $message </div>";
}

sub mawtimeout
{
    return "/var/www/maw/support/timeout ";
}


sub maximaerror
{
    my ($string)=@_;
    $string =~ s/0 errors, 0 warnings//;
    $string =~ s/0 errors, 0 warnings//;
    if (($string=~/[Ii]ncorrect/) || ($string=~/[Ee]rror/)) # || ($string=~/\?/))
    {return 1;}
    else
    {return 0;}
}


sub maximaparseoutput
{
    my ($mretezec)=@_;
    @vystup = $mretezec =~ /MAW summary: (.*?) items/gs;
    $number_of_items = $vystup[0]; 

    $mretezec =~ s/\n//gs;
    $mretezec =~ s/\\,/{}/gs;
    $mretezec =~ s/\\log/\\ln/gs;

    @pole=();

    @a = $mretezec =~ /item (.*?) meti/gs;
    for ($i=0; $i<=$number_of_items-1; $i++)
    {
	$a[$i] =~ s/\$\$//g;
	push(@pole,($a[$i]));
    }
    return @pole;
}


1;
