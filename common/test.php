
<?php


function doit($a,$b="link")
{
  echo "\n<br>\n<a href=\"$a&amp;lang=en\">EN</a> \n<a href=\"$a&amp;lang=ca\">CA</a>\n<a href=\"$a&amp;lang=zh\">CN</a>\n<a href=\"$a&amp;lang=cs\">CZ</a>  \n<a href=\"$a&amp;lang=fr\">FR</a>\n<a href=\"$a&amp;lang=ru\">RU</a>\n<a href=\"$a&amp;lang=ua\">UA</a>  \n$b";
}

function testcommand($a)
{
  echo "<h3>$a</h3><pre>";
  system($a);
  echo "</pre>";
}

require("maw.php");

    echo("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html>\n<head>\n <meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\">\n <link rel=\"stylesheet\" type=\"text/css\" href=\"../common/styl.css\" >\n");
    echo("<title>".__("Mathematical Assistant on Web")."</title>");

if (file_exists('../common/custom.css')) 
{
  echo ("<link rel=\"stylesheet\" type=\"text/css\" href=\"../common/custom.css\" >\n");
}

echo $maw_html_custom_head;

echo("\n</head>\n<body><div class=\"test_maw\"><H1> MAW tests</H1>");




echo '
<h2>Are all the commands installed? Look at the outputs <input type="button" value="Show" onClick="document.getElementById(\'test_binaries\').style.display = \'block\';"> <input type="button" value="Hide" onClick="document.getElementById(\'test_binaries\').style.display = \'none\';"></h2>
<div id="test_binaries">
';
testcommand ("$maxima --version");
testcommand ("$maxima2 --version");
testcommand ("$pdflatex --version");
testcommand ("$formconv_bin --version");
testcommand("echo \$PATH");
testcommand("ls -l $epstopdf");
testcommand("ls -l $ps2pdf");

echo '</div>';

function makelink($function,$comment="")
{
if ($comment=="") {$comment=$function;}
return(doit("../integral/integral.php?funkce=".rawurlencode($function)."&amp;formconv=on&amp;pfeformat=on&amp;logarc=on",$comment));
}




echo '<h2>Error messages from input filter <input type="button" value="Show" onClick="document.getElementById(\'test_filter\').style.display = \'block\';"> <input type="button" value="Hide" onClick="document.getElementById(\'test_filter\').style.display = \'none\';"> </h2>';

echo '<div id="test_filter">';

doit ("../lde2/ldr2.php?p=2&amp;q=1&amp;f=".urlencode("x^2*(sin(2*x)+1")."&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","Parentheses");
doit ("../lde2/ldr2.php?p=2&amp;q=1&amp;f=sin^2%28x%29&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","sin^2(x)");
doit("../lde2/ldr2.php?p=2&amp;q=1&amp;f=erf%28x%29&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","Blacklisted function erf");
doit("../lde2/ldr2.php?p=2&amp;q=1&amp;f=acsc%28x%29&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","Unsupported function acsc");
doit("../lde2/ldr2.php?p=2&amp;q=1&amp;f=121x-1&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","121x error (missing *)");
doit("../lde2/ldr2.php?p=2&amp;q=1&amp;f=2//x&amp;akce=0&amp;x0=0&amp;y0=1&amp;y10=-1&amp;tlacitko=Odeslat","2//x");
makelink("abs(x)","No absolute value in integral calculus");

function testfilter($test)
{
  $test_text=$test;
  if ($test_text=="") {$test_text="empty string";}
  echo "\n<a href=\"../prubeh/zpracuj.php?funkce=".rawurlencode($test)."&amp;lang=en&amp;\">$test_text</a>";
}

echo '<br>';

array_map('testfilter',array("", "2*sin(ý)", "1+-x","1++x","1*/x","1/*x", "1-+x", "1+*x", "x²", "\\\\", "log10(x)", "2*π", "2.x", "2,x", "infinit", "1-sin 2x", "2+cosh^3(x)", "integral(x^2,x)", "(2*x+1)(x+4)"));

echo '</div>';


echo '<h2>Does each form work fine? <input type="button" value="Show" onClick="document.getElementById(\'test_forms\').style.display = \'block\';"> <input type="button" value="Hide" onClick="document.getElementById(\'test_forms\').style.display = \'none\';">  </h2>';

echo '<div><div id="test_forms"><div style="width:50%;float: left;">';

echo '<h3>Shifted and resized graphs</h3>';
doit("../graf/graf.php?funkce=coth%28x-1%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;naturallog=1&amp;logbase=3&amp;tlacitko=Odeslat","formconv not older than January 17, 2012 nedded to process cotanh");

echo '<h3>Function investigation</h3>';
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E3%2F%28x-1%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","Rational function");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=sin%28x%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","sin(x), message inverse trig functions");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E2%2B2&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","polynomial");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E2%2B%2B&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","Error Incorrect syntax");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=%28x-2%29%5E2%2F%28x%5E2-1%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","Rational function with asymptote");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=%28x-1%29%5E2%2F%28%28x%2B1%29*%28x-1%29%5E3%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","Rational function");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=1%2F%28%28x%2B1%29*%28x-1%29%5E3%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10","Rational function");

doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E3%2F%28x-1%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML Rational function");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=sin%28x%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML sin(x), message inverse trig functions");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E2%2B2&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML polynomial");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=x%5E2%2B%2B&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML Error Incorrect syntax");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=%28x-2%29%5E2%2F%28x%5E2-1%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML Rational function with asymptote");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=%28x-1%29%5E2%2F%28%28x%2B1%29*%28x-1%29%5E3%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML Rational function");
doit("../prubeh/zpracuj.php?lang=cs&amp;funkce=1%2F%28%28x%2B1%29*%28x-1%29%5E3%29&amp;xmin=-5&amp;xmax=5&amp;ymin=-10&amp;ymax=10&amp;output=html","HTML Rational function");


echo '<h3>Bisection</h3>';

doit("../banach/banach.php?lang=cs&amp;method=bisection&amp;funkce=x&amp;a=0&amp;b=1&amp;n=10","Error  no change in sign");
doit("../banach/banach.php?lang=cs&amp;method=bisection&amp;funkce=sqrt(1/2-x)&amp;a=0&amp;b=1&amp;n=10","Error  outside of domain");
doit("../banach/banach.php?lang=cs&amp;method=bisection&amp;funkce=2*x-1&amp;a=0&amp;b=1&amp;n=10","Found exact zero");
doit("../banach/banach.php?lang=cs&amp;method=bisection&amp;funkce=cos%28x%29-4%2F5&amp;a=0&amp;b=1&amp;n=10");


echo '<h3>ODE\'s</h3>';
doit("../ode/ode.php?lang=cs&amp;ode=-2*y%2Fx%2Bx%5E4&amp;akce=1&amp;ode2=x*y%27%2By-x%3D0");

doit("../ode/ode.php?lang=cs&amp;akce=0&amp;ode=+-%28y-x%29%2Fx+&amp;ode2=x*y%27%2By-x%3D0");
doit("../ode/ode.php?lang=cs&amp;akce=0&amp;ode=+-%28y%29%2Fx%2B1+&amp;ode2=x*y%27%2By-x%3D0","linear");
doit("../ode/ode.php?lang=cs&amp;akce=0&amp;ode=+-%28y%29%2Fx+&amp;ode2=x*y%27%2By-x%3D0","linear homogeneous");
doit("../ode/ode.php?lang=cs&amp;akce=0&amp;ode=+-%28y%29%2Fx%2B%28y%2Fx%29%5E2+&amp;ode2=x*y%27%2By-x%3D0","homogeneous");
doit("../ode/ode.php?lang=cs&amp;akce=0&amp;ode=+-%28y%29%2Fx%2By%5E2+&amp;ode2=x*y%27%2By-x%3D0","bernoulli");
doit("../ode/ode.php?ode=-2%2Ay%2Fx%2Bx%5E4&akce=1&ode2=x%2Ay%27%27%27%2By-x%3D0","no higher derivative error");
doit("../ode/ode.php?akce=0&ode=-2%2Ay%2Fx%2Bx%5E4%3D&ode2=x%2Ay%27%27%2By-x%3D0","error: = on right hand side");
doit("../ode/ode.php?akce=0&ode=x%2By%27&ode2=x%2Ay%27%27%2By-x%3D0","error: y' on right hand side");
doit("../ode/ode.php?akce=1&ode2=".rawurlencode("x+y=1"),"missing derivative derivative error");
doit("../ode/ode.php?akce=1&ode2=".rawurlencode("x+y'^2=0"),"solving with respect to y' is not unique");
doit("../ode/ode.php?akce=1&ode2=".rawurlencode("x+y'=0"),"solving with respect to y' is unique");

function solveode($function,$comment="")
{
if ($comment=="") {$comment=$function;}
echo("<br>solveode: <a href='../ode/solveode.php?".rawurlencode($function).";cz'>$comment</a> ");
}

solveode("sqrt(x)");
solveode("y'+sqrt(x)");
solveode("y'+sqrt(x)=9");
solveode("y'^2+sqrt(x)=9");

echo '<h3>2dn order LDE</h3>';

doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2&amp;akce=0&amp;IVP=on&amp;x0=0&amp;y0=1&amp;y10=-1","variation of contants");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2%2Bsqrt%28x%29&amp;akce=1&amp;IVP=on&amp;x0=0&amp;y0=1&amp;y10=-1","undetermined coefficients - error");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2&amp;akce=1","undetermined coefficients: x^2");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2*exp%28x%29&amp;akce=1&amp;IVP=on&amp;x0=0&amp;y0=1&amp;y10=-1","undetermined coefficients: x^2*exp(x) and IC");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2*exp%28-x%29&amp;akce=1");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2*exp%28-x%29*sin%28x%29&amp;akce=1");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2*exp%28-x%29*sin%28x%29%2Bx&amp;akce=1");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x^2-sin(x)-cos(x)-exp(x)*sin(x)&amp;akce=1","Error, split right hand side, hangs with maxima 5.13");
doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x^2-sin(x)-cos(x)&amp;akce=1&amp;akce=1&amp;IVP=on&amp;x0=0&amp;y0=1&amp;y10=-1","Error, split right hand side");

doit("../lde2/ldr2.php?lang=cs&amp;p=2&amp;q=1&amp;f=x%5E2*exp%28-x%29*sin%28x%29%2Bx%2B%2B%2B&amp;akce=1");

echo '<h3>Autonomous systems</h3>';

doit("../autsyst/autsyst.php?lang=en&amp;funkcef=x%5E3&amp;funkceg=y%5E3&amp;akce=0&amp;xs=0&amp;ys=0");

doit("../autsyst/autsyst.php?lang=en&amp;funkcef=x&amp;funkceg=y&amp;akce=0&amp;xs=0&amp;ys=0");
doit("../autsyst/autsyst.php?lang=en&amp;funkcef=x&amp;funkceg=y&amp;akce=0&amp;xs=0&amp;ys=1");
doit("../autsyst/autsyst.php?lang=en&amp;funkcef=x*%284-x-y%29&amp;funkceg=y*%289-2*x-3*y%29&amp;akce=0&amp;xs=3&amp;ys=1");


echo '<h3>Local extrema in 3D</h3>';

doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x*y*%284-x-y%29&amp;akce=0&amp;xs=4%2F3&amp;ys=4%2F3&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F3%5D");
doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x*y*%284-x-y%29&amp;xs=4%2F3&amp;ys=4%2F3&amp;akce=2&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F3%5D");

doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x*y*%284-x-y%29&amp;xs=4%2F3&amp;ys=4%2F3&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F3%5D&amp;akce=1","find stationary point");
doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x*y*%284-x-y%29&amp;xs=4%2F3&amp;ys=4%2F3&amp;akce=2&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F2%5D");
doit("../minmax3d/minmax3d.php?lang=en&amp;funkcef=x*y*%284-x-y%29&amp;akce=0&amp;xs=4%2F3&amp;ys=4&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F2%5D","error - no stationary point");
doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x*y*%284-x-y%29&amp;xs=4%2F3&amp;ys=0&amp;akce=2&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F2%5D","error - no stationary point");
doit("../minmax3d/minmax3d.php?lang=cs&amp;funkcef=x&amp;xs=4%2F3&amp;ys=4%2F3&amp;stacbody=%5B0%2C0%5D+%3B+%5B0%2C4%5D+%3B+%5B4%2C0%5D+%3B+%5B4%2F3%2C4%2F3%5D&amp;akce=1","error - one variable only");
doit("../minmax3d/minmax3d.php?funkcef=(x-y)^2","stationary points not given, inifitely many stationary points");



echo '<h3>Lagrange</h3>';

doit("../lagrange/lagrange.php?lang=cs&amp;body=1%2C+2+%3B+2%2C3%3B+3%2C0");
doit("../lagrange/lagrange.php?lang=cs&amp;body=1%2C+2+%3B+2%2C3%3B+3%2C1");
doit("../lagrange/lagrange.php?lang=cs&amp;body=1%2C+0+%3B+2%2C0%3B+3%2C0","Error");
doit("../lagrange/lagrange.php?lang=cs&amp;body=1%2C+0+%3B+2%2C0%3B+2%2C0","Error");
doit("../lagrange/lagrange.php?lang=cs&amp;body=1%2C+0+%3B+2%2C0%3B+2%2C2","Error");

echo '<h3>Domains</h3>';
doit("../domf/domf.php?funkcef=x%2Alog%284-x%5E2%29%2F%28sqrt%28x-1%29%29&akce=5&onevar=1&xmin=-5&xmax=5&tlacitko=Submit","1 variable");

echo '</div><div style="width:50%;float: right;"><h3>Indefinite integral</h3>';

echo '<h4>Sum</h4>';
makelink("(x)*(x^2+1)");
makelink("(x + x^2+sin(2*x+6) + 1/sqrt(x^2+6))");

echo '<h4>Integration by formulas</h4>';
makelink("(1/4*x)/(1/16*x^2+1)","the hint should be improved here");
makelink("(x)/(x^2+1)");
makelink("(x+3)/(x^2+2*x+1)");
makelink("(x+3)^3/((x-3)*(x^2+2*x+1)*(x^2+7))");

echo '<h4>Integration by parts</h4>';
makelink("x*sin(x)");
makelink("sinh(2x-1)");
makelink("log(x)");
makelink("sqrt(x)*log(x)");
makelink("sqrt(x)*atanh(x)");
makelink("sec(x)");
makelink("asinh(x)","asinh(x), v'=1 (not available automatically)");

echo '<h4>Substitution</h4>';
makelink("x*exp(-x^2)");
makelink("x*cos(-x^2)");
makelink("x*cosh(-x^2)");
makelink("x*sqrt((x-1)*(x-2))","x*sqrt((x-1)*(x-2)), substitution t^2=(x-1)/(x-2)");
makelink("x*sqrt(-x^2+x+6)","x*sqrt(-x^2+x+6), substitution t=sqrt((x+2)/(3-x))");

echo '<h4>Heuristics</h4>';
function heu($a,$b) {makelink($a,"$a, $b");}

heu("(x+3)/(x^2+6x+17)","f'(x)/f(x)");
heu("(3)/(x^2+6x+17)","complete square");
heu("(3)/sqrt(x^2+6x+17)","complete square under square root");
heu("(3)/sqrt(-x^2+6x+17)","complete square under square root");
heu("(3x-6)/(x^2+6x+17)","split fraction");
heu("sqrt(x+sin(x))(1+cos(x))","substitution (under root)");
heu("sqrt(sin(x)+cos(x))*(sin(x)-cos(x))","substitution (under root)");


echo '<h3>Definite integral in geometry</h3>';

doit("../geom/geom.php?lang=en&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2&amp;funkceg=1-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");

doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2&amp;funkceg=1%2F2-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2&amp;funkceg=1%2F2-x%5E2&amp;meza=0&amp;mezb=2&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2&amp;funkceg=1%2F2-x%5E2&amp;meza=0&amp;mezb=2&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");

doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2&amp;funkceg=1-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2%2B1%2F2&amp;funkceg=1-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");
doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2%2B1%2F4&amp;funkceg=1-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");

doit("../geom/geom.php?lang=en&amp;funkcef=%281-x%29%5E2%2B1%2F4&amp;funkceg=1-x%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3");

doit("../geom/geom.php?lang=cs&amp;funkcef=2*x-1&amp;funkceg=-2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Error - region intersects x axis");

doit("../geom/geom.php?lang=cs&amp;funkcef=2*x-1&amp;funkceg=0&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Error - intersections");
doit("../geom/geom.php?lang=cs&amp;funkcef=-2&amp;funkceg=0&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Error - interchanged functions");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=0&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Area below");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=0&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Volume below");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=2&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Volume between");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=300&amp;ymin=-1&amp;ymax=3","Error Arithmetic overflow");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3000&amp;ymin=-1&amp;ymax=3","Error: dimension too large");
doit("../geom/geom.php?lang=cs&amp;funkcef=1-x%5E2&amp;funkceg=%281-x%29%5E2&amp;meza=0&amp;mezb=1&amp;akce=0&amp;xmin=-1&amp;xmax=3&amp;ymin=-1&amp;ymax=3","Area between");
doit("../geom/geom.php?funkcef=1-x^2&funkceg=%281-x%29^2&meza=ln%281%29&mezb=1&akce=0&xmin=0.5&xmax=1&ymin=-1&ymax=3&tlacitko=Submit","Change xmin error");
doit("../geom/geom.php?funkcef=1-x^2&funkceg=%281-x%29^2&meza=0&mezb=ln%28e%29&akce=0&xmin=0&xmax=0.5&ymin=-1&ymax=3&tlacitko=Submit","Change xmax error");
doit("../geom/geom.php?funkcef=1-x^2&funkceg=%281-x%29^2&meza=2&mezb=1&akce=0&xmin=0&xmax=0.5&ymin=-1&ymax=3&tlacitko=Submit","Interchange limits error");
doit("../geom/geom.php?funkcef=1-x^2&funkceg=%281-x%29^2&meza=1&mezb=2&akce=0&xmin=0&xmax=0.5&ymin=-1&ymax=3&tlacitko=Submit","Interchange functions error");
doit("../geom/geom.php?funkcef=1-x^2&funkceg=%281-x%29^2&meza=0&mezb=2&akce=0&xmin=0&xmax=0.5&ymin=-1&ymax=3&tlacitko=Submit","Intersections error");
doit("../geom/geom.php?funkcef=1%2Fx&funkceg=0&meza=0&mezb=1&akce=0&xmin=0&xmax=1&ymin=-1&ymax=3&tlacitko=Submit","Dicontinuity error");

echo '</div>';
echo '</div>';


echo '</div></div>';


?>
