#!/bin/bash
#
# Copyright 2012, Robert Marik 
#
# This file is a part of Mathematical Asistant on Web (MAW),
# http://user.mendelu.cz/marik/maw. See the web page of MAW for
# information about license.
#
# Draws region $3 < x < $4 and $1 < y < $2, where $1 and $2 are
# functions of x The result is in the file a.png. The x bounds for the
# picture are $5 and $6 and y bounds are $7 and $8. These bounds are
# adjusted automatically, if the region is outside the bounds. If the
# parameter $9 is present, the region is $3 < y < $4 and $1 < x < $2,
# where $1 and $2 are functions of y. All expressions are in GNUplot
# format.

f=$1
g=$2
a=$3
b=$4
xmin=$5
xmax=$6
ymin=$7
ymax=$8
swap=$9
output=${10}
if [ "$output" == "" ]; then
    output="png transparent size 400,400 "
    output="svg size 500,500 "
fi
filename=${11}
if [ "$filename" == "" ]; then
    filename="a.png"
    filename="a.svg"
fi

echo "set xrange [$a:$b]" > temp

if [ "$swap" == "" ]; then
    echo "f(x)=$f" >> temp
    echo "g(x)=$g" >> temp;
else
    echo "f(y)=$f" >> temp
    echo "g(y)=$g" >> temp;
fi

echo "set samples 500" >> temp
echo "set table \"f.dat\"" >> temp
echo "plot f(x)" >> temp
echo "set table \"g.dat\"" >> temp 
echo "plot g(x)" >> temp

gnuplot temp
cp temp temp2

if [ "$swap" == "" ]; then
    join f.dat g.dat > both2.dat;
    grep -v '^#' both2.dat | grep -v '^$' | grep -v 'u' > both.dat;
    max1=`! perl -e '$max=-1e30; while (<>) {@t=split; $max=$t[1] if $t[1]>$max;} ($max<1e3) ? print $max : print 0' < both.dat`;
    max3=`! perl -e '$max=-1e30; while (<>) {@t=split; $max=$t[3] if $t[3]>$max;} ($max<1e3) ? print $max : print 0' < both.dat`;
    min1=`! perl -e '$min=1e30; while (<>) {@t=split; $min=$t[1] if $t[1]<$min;} ($min>-1e3) ? print $min : print 0' < both.dat`;
    min3=`! perl -e '$min=1e30; while (<>) {@t=split; $min=$t[3] if $t[3]<$min;} ($min>-1e3) ? print $min : print 0' < both.dat`;
else
    cat f.dat > both2.dat;
    tac g.dat >> both2.dat;
    grep -v '^#' both2.dat | grep -v '^$' | grep -v 'u' > both.dat;
    max1=`! perl -e '$max=-1e30; while (<>) {@t=split; $max=$t[1] if $t[1]>$max;} print $max' < both.dat`;
    min1=`! perl -e '$min=1e30; while (<>) {@t=split; $min=$t[1] if $t[1]<$min;} print $min' < both.dat`;
    #join f.dat g.dat > both2.dat;
    #join g.dat f.dat > both3.dat;
    #grep -v '^#' both2.dat | grep -v '^$' > both.dat;
    #tac both3.dat | grep -v '^#' | grep -v '^$' >> both.dat;
fi

echo "unset key" > temp
if [ "$swap" == "" ]; then
    echo "f(x)=$f" >> temp
    echo "g(x)=$g" >> temp;
else
    echo "f(y)=$f" >> temp
    echo "g(y)=$g" >> temp;
fi
echo "set samples 1000" >> temp
echo "set zeroaxis lt -1 " >> temp
echo "set xtics axis nomirror" >> temp
echo "set ytics axis nomirror" >> temp
echo "set noborder" >> temp
echo "set term $output" >> temp
echo "set output \"$filename\"" >> temp
echo "set style fill pattern 4 bo" >> temp

if [ "$swap" == "" ]; then
    echo "set xrange [($a>$xmin ? $xmin : $a):($b>$xmax ? $b : $xmax)]" >> temp
    echo "set yrange [($min1>=$min3 ? ($min3>$ymin ? $ymin : $min3) : ($min1>$ymin ? $ymin : $min1) ): ($max1>=$max3 ? ($max1>$ymax ? $max1 : $ymax) : ($max3>$ymax ? $max3 : $ymax))]" >> temp
    echo "plot \"both.dat\" using (\$2> \$4 ? \$1 : 1/0):2:4 with filledcurves lc rgb \"blue\", \"both.dat\" using (\$2< \$4 ? \$1 : 1/0):2:4 with filledcurves lc rgb \"red\", f(x) with lines lw 3 lc rgb \"black\", g(x) with lines lw 3 lc rgb \"red\"" >> temp;
else
    echo "set yrange [($a>$ymin ? $ymin : $a):($b>$ymax ? $b : $ymax)]" >> temp
    echo "set xrange [($min1>$xmin ? $xmin : $min1) : ($max1>=$xmax ? $max1 : $xmax)]" >> temp
    echo "set parametric" >> temp
    echo "set trange [($a>$ymin ? $ymin : $a):($b>$ymax ? $b : $ymax)]" >> temp
    echo "plot \"both.dat\" using 2:1 with filledcurves lc rgb \"red\", f(t),t with lines lw 3, g(t),t with lines lw 3 " >> temp    
fi

gnuplot temp

