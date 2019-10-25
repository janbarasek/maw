#!/bin/bash
#
# Copyright 2012, Robert Marik 
#
# This file is a part of Mathematical Asistant on Web (MAW),
# http://user.mendelu.cz/marik/maw. See the web page of MAW for
# information about license.
#
# Draws parametric curve in 3D
#

x=$1
y=$2
z=$3
tmin=$4
tmax=$5
output=$6
if [ "$output" == "" ]; then
    output="png transparent size 600,600"
    output="svg size 600,600"
fi
filename=$7
if [ "$filename" == "" ]; then
    filename="a.png"
    filename="a.svg"
fi

echo "unset key" > temp
echo "set parametric" >> temp
echo "set trange [$tmin:$tmax]" >> temp
echo "set samples 1000" >> temp
echo "set size square" >> temp
echo "set term $output" >> temp
echo "set output \"$filename\"" >> temp
echo "splot [t=$tmin:$tmax] $x,$y,$z  with lines lw 3 lc rgb \"red\"" >> temp

gnuplot temp

