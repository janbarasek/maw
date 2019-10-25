#!/bin/bash
#
# Copyright 2012, Robert Marik 
#
# This file is a part of Mathematical Asistant on Web (MAW),
# http://user.mendelu.cz/marik/maw. See the web page of MAW for
# information about license.
#
# For polar coordinates r and phi draws region $3 < phi < $4 and $1 <
# r < $2, where $1 and $2 are functions of phi. The result is in the
# file a.png. The x bounds for the picture are $5 and $6 and y bounds
# are $7 and $8. These bounds are adjusted automatically, if the
# region is outside the bounds.  If the parameter $9 is present, the
# region is $3 < r < $4 and $1 < phi < $2, where $1 and $2 are functions
# of r. All expressions are in GNUplot format.

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

echo "set parametric" > temp
echo "set trange [$a:$b]" >> temp

if [ "$swap" == "" ]; then
    echo "f(phi)=$f" >> temp;
    echo "g(phi)=$g" >> temp;
else
    echo "f(r)=$f" >> temp;
    echo "g(r)=$g" >> temp;
fi

echo "set samples 500" >> temp
if [ "$swap" == "" ]; then
    echo "set table \"f.dat\"" >> temp;
    echo "plot f(t)*cos(t), f(t)*sin(t)" >> temp;
    echo "set table \"g.dat\"" >> temp;
    echo "plot g(t)*cos(t), g(t)*sin(t)" >> temp;
else
    echo "set table \"f.dat\"" >> temp;
    echo "plot t*cos(f(t)), t*sin(f(t))" >> temp;
    echo "set table \"g.dat\"" >> temp;
    echo "plot t*cos(g(t)), t*sin(g(t))" >> temp;
    echo "set trange [0:1]" >> temp;
    echo "set table \"a.dat\"" >> temp;
    echo "plot ($a)*cos(f($a)+(g($a)-f($a))*t),  ($a)*sin(f($a)+(g($a)-f($a))*t)" >> temp;
    echo "set table \"b.dat\"" >> temp;
    echo "plot ($b)*cos(f($b)+(g($b)-f($b))*t),  ($b)*sin(f($b)+(g($b)-f($b))*t)" >> temp;
fi

gnuplot temp
cp temp temp2

if [ "$swap" == "" ]; then
    cat f.dat > both2.dat;
    tac g.dat >> both2.dat;
else
    cat f.dat > both2.dat;
    cat b.dat >> both2.dat;
    tac g.dat >> both2.dat;
    tac a.dat >> both2.dat;
fi;

grep -v '^#' both2.dat | grep -v '^$' > both.dat;
max1=`! perl -e '$max=-1e30; while (<>) {@t=split; $max=$t[1] if $t[1]>$max;} print $max' < both.dat`;
min1=`! perl -e '$min=1e30; while (<>) {@t=split; $min=$t[1] if $t[1]<$min;} print $min' < both.dat`;
max0=`! perl -e '$max=-1e30; while (<>) {@t=split; $max=$t[0] if $t[0]>$max;} print $max' < both.dat`;
min0=`! perl -e '$min=1e30; while (<>) {@t=split; $min=$t[0] if $t[0]<$min;} print $min' < both.dat`;

echo "unset key" > temp
echo "set xlabel \"x\"" >> temp
echo "set ylabel \"y\"" >> temp
echo "set size ratio -1" >> temp
echo "set zeroaxis lt -1 " >> temp
echo "set xtics axis nomirror" >> temp
echo "set ytics axis nomirror" >> temp
echo "set noborder" >> temp
echo "set term $output" >> temp
echo "set output \"$filename\"" >> temp
echo "set style fill pattern 4 bo" >> temp

if [ "$swap" == "" ]; then
    echo "set xrange [($min0>$xmin ? $xmin : $min0):($max0>$xmax ? $max0 : $xmax)]" >> temp;
    echo "set yrange [($min1>$ymin ? $ymin : $min1):($max1>$ymax ? $max1 : $ymax)]" >> temp;
    echo "plot \"both.dat\" with filledcurves lc rgb \"red\", \"f.dat\" with lines , \"g.dat\" with lines " >> temp;
else
    echo "plot \"both.dat\" with filledcurves lc rgb \"red\", \"f.dat\" with lines , \"g.dat\" with lines " >> temp;
fi

gnuplot temp

