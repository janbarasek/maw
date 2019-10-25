#!/bin/bash
#
# Copyright 2012, Robert Marik 
#
# This file is a part of Mathematical Asistant on Web (MAW),
# http://user.mendelu.cz/marik/maw. See the web page of MAW for
# information about license.
#
# Draws parametric curve in 2D
#

x=$1
y=$2
tmin=$3
tmax=$4
output=$5
if [ "$output" == "" ]; then
    output="png transparent "
    output="svg "
fi
filename=$6
if [ "$filename" == "" ]; then
    filename="a.png"
    filename="a.svg"
fi


echo "unset key" > temp
echo "set parametric" >> temp
echo "set trange [$tmin:$tmax]" >> temp
echo "set samples 1000" >> temp
echo "set size ratio -1" >> temp
echo "set term $output" >> temp
echo "set output \"$filename\"" >> temp
echo "plot $x,$y with lines lw 3 lc rgb \"red\"" >> temp

gnuplot temp

cp * /tmp
