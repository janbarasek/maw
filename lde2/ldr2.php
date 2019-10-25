<?php
/*
Mathematical Assistant on Web - web interface for mathematical          
computations including step by step solutions
Copyright 2007-2010 Robert Marik, Miroslava Tihlarikova
Copyright 2011-2012 Robert Marik

This file is part of Mathematical Assistant on Web.

Mathematical Assistant on Web is free software: you can
redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software
Foundation, either version 3 of the License, or
(at your option) any later version.

Mathematical Assistant on Web is distributed in the hope that it
will be useful, but WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Mathematical Assistant o Web.  If not, see 
<http://www.gnu.org/licenses/>.
*/

$scriptname = "ldr2";
require("../common/maw.php");
$maxima = $maxima2;

$p = $_REQUEST["p"];
$q = $_REQUEST["q"];
$f = $_REQUEST["f"];
$akce = $_REQUEST["akce"];
$IVP = $_REQUEST["IVP"];

$requestedoutput = "html";
if ($_REQUEST["output"] == "pdf") {
	$requestedoutput = "pdf";
}


if ($p == "") {
	$p = 0;
}
if ($q == "") {
	$q = 0;
}
if ($f == "") {
	$f = 0;
}

check_for_security("$f, $p, $q");

$f = str_replace(" ", "", $f);
$p = str_replace(" ", "", $p);
$q = str_replace(" ", "", $q);

$body = $p . $q;
$retezec = preg_match("~[^-0-9\.]~", $body);
if (($retezec != "") || ($p == "") || ($q == "") || ($f == "")) {
	maw_errmsgB("<h3 class='red'>" . __("Incorrect input (not a number or empty string)") . "</h3><b><span class='red'>" . __("Use only integers or decimal numbers for coefficients on the left hand side.") . "</span></b><br><br>" . sprintf(__("Your incorrect input is: %s and %s."), "<span class='red'>$p</span>", "<span class='red'>$q</span>"));
	die("</body></html>");
}

check_for_y($f . $p . $q);
$variables = 'x';
$parameters = ' ';

$f = input_to_maxima($f);
$datcasip = "$p, $q, prava strana:$f , metoda:$akce";
if ($IVP == "on") {
	$parameters = 'a|b';
	$variables = ' ';

	$x0 = input_to_maxima($_REQUEST["x0"], "x");
	$y0 = input_to_maxima($_REQUEST["y0"], "y");
	$y10 = input_to_maxima($_REQUEST["y10"], "y'");

	$datcasip = $datcasip . ", IVP: $x0, $y0, $y1";
}


/* Looking for general solution    */
/* We use temporary directory                                           */
/* ---------------------------------------------------------------------*/


$maw_tempdir = "/tmp/MAW_lde2" . getmypid() . "xx" . RandomName(6);
system("mkdir " . $maw_tempdir . "; chmod oug+rwx " . $maw_tempdir);


define("NAZEV_SOUBORU", $maw_tempdir . "/vstup");
$soubor = fopen(NAZEV_SOUBORU, "w");
fwrite($soubor, "$p\n");
fwrite($soubor, "$q\n");
fwrite($soubor, "$f\n");
fwrite($soubor, "$lang\n");
fwrite($soubor, "$akce\n$mawphphome\n$mawhome\n$texrender\n$maw_URI\n");
fclose($soubor);

system("cd $maw_tempdir; LANG=$locale_file.UTF-8 perl -s $mawhome/lde2/ldr2.pl -mawhome=$mawhome -f='$f' -p='radcan($p)' -q='radcan($q)' -mawserver='$mawphphome' -lang='$lang' -maw_URI='$maw_URI' -texrender='$texrender' -method='$akce' -maxima=$maxima -mawphphome=$mawphphome");

if (file_exists("$maw_tempdir/method")) {
	$method_file = file("$maw_tempdir/method");
	$TeXskeleton = $method_file[0];
	$parsol = $method_file[1];
	$gensol = $method_file[2] . "+(" . $parsol . ")";
} else {
	$TeXskeleton = -1;
	$parsol = 0;
}

if ($TeXskeleton == 0) {
	$TeXfile = '\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{' . __("Second order linear differential equation") . '}

\input data.tex

\def\jedna{1}
\def\mjedna{-1}
\def\nula{0}

\initial

\ifx\pravastrana\nula
' . __("We solve the homogeneous differential equation") . '
\else
' . __("We solve the nonhomogeneous differential equation") . '
\begin{equation*}
  \rce =\pravastrana
%  y\'\'+\p y\'+\q y=0
\end{equation*}

\hrule
\medskip
\textbf{1.}
' . __("Associated homogeneous equation is") . '
\fi
\begin{equation*}
  \rce =0
%  y\'\'+\p y\'+\q y=0
\end{equation*}

' . __("The characteristic equation is") . ' $\charrce \ifx\q\mjedna 1\fi\ifx\q\jedna 1\fi=0$

' . __("Zeros of characteristic equation") . ': $\lambda_{1,2}=\frac{-\left(\p\right)\pm\sqrt{\left(\p\right)^2-4\left(\q\right)}}{2}=\frac{\minusp\pm\sqrt{\D}}{2}=
\ifx\jedna\concl \begin{cases}\lambdaa\\\\ \lambdab\end{cases}\fi
\ifx\mjedna\concl \resa\pm \ifx\resb\jedna\else \resb\fi i\fi
\ifx\nula\concl \lambdaa\fi
$

\ifx\jedna\concl ' . __("Characteristic equation has two real solutions.") . '\fi 
\ifx\nula\concl ' . __("Characteristic equation has one double solution.") . '\fi 
\ifx\mjedna\concl ' . __("Characteristic equation has two complex solutions.") . '\fi

' . sprintf(__("Two independent solutions are %s and %s."), "$ y_1=\\fundsa$", "$ y_2=\\fundsb$") . '

\ifx\pravastrana\nula
' . sprintf(__("The general solution is %s."), "\$\\reseni\$") . '

\else
' . sprintf(__("The general solution of the associated homogeneous equation is %s."), "\$\\reseni\$") . '

\medskip\hrule\medskip
\textbf{2.} ' . __("We use the variation of constants to find the particular solution in the form") . '
\begin{equation*}
y_p=\resenivar
\end{equation*}

' . __("We have to solve the linear system") . '
\renewcommand{\minalignsep}{0pt}
\begin{equation*}
\begin{aligned}
\arraycolsep=0pt
  &A\'(x) \left[\fundsa\right] &{}+{}&B\'(x)\left[\fundsb\right] &&{}= 0\\\\
  &A\'(x) \left[\fundsader\right]&{}+{}&B\'(x)\left[\fundsbder\right] &&{}= \pravastrana\\
\end{aligned}
\end{equation*}
' . sprintf(__("with unknowns %s and %s."), "$ A'(x)$", "$ B'(x)$") . '
\makeatletter
\def\matrix#1{\null\,\vcenter{\normalbaselines\m@th
    \ialign{\hfil$##$\hfil&&\quad\hfil$##$\hfil\crcr
       \mathstrut\crcr\noalign{\kern-\baselineskip}
       #1\crcr\mathstrut\crcr\noalign{\kern-\baselineskip}}}\,}
 \def\pmatrix#1{\left|\matrix{#1}\right|}
\makeatother

' . sprintf(__("Determinant of the coefficient matrix (wronskian of the solutions %s and %s) is"), "$ y_1$", "$ y_2$") . '


\parindent = -2em
\leftskip = 2 em

$
  W[y_1,y_2](x)=\pmatrix{y_1(x) & y_2(x)\cr y\'_1(x)&y\'_2(x)} =
  \wronskimat=\wronski
$

' . __("Auxiliary determinants are") . '

$  W_1(x)=\pmatrix{0 & y_2(x)\cr f(x)&y\'_2(x)} =
  \wronskimatA=\wronskiA
$

$  W_2(x)=\pmatrix{y_1(x) & 0\cr y\'_1(x)& f(x)} =
  \wronskimatB=\wronskiB
$

' . sprintf(__("Solution of the system for %s and %s is"), "$ A'(x)$", "$ B'(x)$") . '

$A\'(x)=\frac{W_1}{W}=\derA$

$B\'(x)=\frac{W_2}{W}=\derB$

{\parindent 0 pt 
\leftskip 0 pt

' . sprintf(__("Integration gives %s and %s (by clicking the integral you load the integral into tool for indefinite integration)"), "$ A(x)$", "$ B(x)$") . '

}

$A(x)=\intderA=\A$

$B(x)=\intderB=\B$


' . __("Particular solution (after substitution and simplification)") . ':

$%\begin{equation*}
  y_p(x)=A(x)y_1(x)+B(x)y_2(x)=\yp
$%\end{equation*}

' . __("General solution") . ':

$%\begin{equation*}
  y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=\yp +C_1 \fundsa+C_2 \fundsb.
$%\end{equation*}
\fi

\partsol
\end{document}
';
} elseif ($TeXskeleton == 1) {
	$TeXfile = '
\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{' . __("Second order linear differential equation") . '}

\input data.tex

\def\jedna{1}
\def\mjedna{-1}
\def\nula{0}
\def\jednam{ 1 }

\initial

\ifx\pravastrana\nula
' . __("We solve the homogeneous differential equation") . '
\begin{equation*}
  \rce =0
%  y\'\'+\p y\'+\q y=0
\end{equation*}
\else
' . __("We solve the nonhomogeneous differential equation") . '
\begin{equation*}\tag{1}
  \rce =
\ifx\pravastranaexp\jednam
\pravastranapol
\global\let\pravastrana\pravastranapol
\else
\pravastrana
\fi
%  y\'\'+\p y\'+\q y=0
\end{equation*}

\hrule
\medskip
\textbf{1. ' . __("We solve the associated homogeneous equation first") . '}
\begin{equation*}
  \rce =0 
%  y\'\'+\p y\'+\q y=0
\end{equation*}
\fi

' . __("Characteristic equation") . ': $\charrce \ifx\q\mjedna 1\fi\ifx\q\jedna 1\fi=0$

' . __("Zeros") . ': $\lambda_{1,2}=\frac{-\left(\p\right)\pm\sqrt{\left(\p\right)^2-4\left(\q\right)}}{2}=\frac{\minusp\pm\sqrt{\D}}{2}=
\ifx\jedna\concl \begin{cases}\lambdaa\\\\\lambdab\end{cases}\fi
\ifx\mjedna\concl \resa\pm \ifx\resb\jedna\else \resb\fi i\fi
\ifx\nula\concl \lambdaa\fi
$

\ifx\jedna\concl ' . __("Characteristic equation has two real solutions.") . '\fi 
\ifx\nula\concl ' . __("Characteristic equation has one double solution.") . '\fi 
\ifx\mjedna\concl ' . __("Characteristic equation has two complex solutions.") . '\fi

' . sprintf(__("Two independent solutions are %s and %s."), "$ y_1=\\fundsa$", "$ y_2=\\fundsb$") . '

\ifx\pravastrana\nula
' . sprintf(__("The general solution is %s."), "\$\\reseni\$") . '

\else
' . sprintf(__("The general solution of the associated homogeneous equation is %s."), "\$\\reseni\$") . '

\medskip\hrule\medskip 

\textbf{2. ' . __("We look for the particular solution of the nonhomogeneous equation.") . '}  

\\ifx\\pravastranaexpkoef\\nula
' . sprintf(__("The right hand side is polynomial %s, the degree of this polynomial is %s."), "$ P(x)=\\pravastranapol$", "$\\pravastranapolst$") . ' \else 
' . sprintf(__("The right hand side is product of polynomial %s and exponential function %s, the degree of the polynomial is %s."), "$ P(x)=\\pravastranapol$", "$\\pravastranaexp$", "$\\pravastranapolst$") . '  \fi


\ifx\nasobnost\nula
' . sprintf(__("The number %s is not a zero of the characteristic equation and the particular solution is in the form %s."), "$\\pravastranaexpkoef$", '$y=(\polynomtest) \ifx\pravastranaexp\jednam\else\pravastranaexp\fi$') . '
\else
\if \nasobnost\jedna
' . sprintf(__("The number %s is zero of multiplicity 1 of the characteristic equation and the particular solution has the form %s."), "$\\pravastranaexpkoef$", '$y=x(\polynomtest)\ifx\pravastranaexp\jednam\else\pravastranaexp\fi$') . '
\else
' . sprintf(__("The number %s is double zero of the characteristic equation and the particular solution has the form %s."), "$\\pravastranaexpkoef$", '$y=x^2(\polynomtest) \ifx\pravastranaexp\jednam\else\pravastranaexp\fi$') . '
\fi
\fi 

\def\mjednam{ -1 }
\ifx\znamenkoexponentu\mjednam
\def\prepinac#1{\left(#1\right)}
\else
\let\prepinac\relax
\fi

\def\prvni{  (\derpol)\pravastranaexp+(\pol)\prepinac{\derexp}=(\uprder)\pravastranaexp}
\setbox0=\hbox{$ y\'=\prvni$}
\ifdim\wd0>\hsize
\def\prvni{  (\derpol)\pravastranaexp+(\pol)\prepinac{\derexp}\\\\ =&(\uprder)\pravastranaexp}
\fi

\def\druhy{(\derb)\pravastranaexp+(\uprder)\prepinac{\derexp}=(\uprderb)\pravastranaexp}
\setbox0=\hbox{$ y\'\'=\druhy$}
\ifdim\wd0>\hsize
\def\druhy{(\derb)\pravastranaexp+(\uprder)\prepinac{\derexp}\\\\=&(\uprderb)\pravastranaexp}
\fi

\textbf{2a. ' . __("Preliminary") . '} (' . __("we have to find derivatives and put into the equation") . '):
\ifx\pravastranaexp\jednam
\begin{align*}
  y=&\pol \tag{2}\\\\
  y\'=&\derpol\\\\
  y\'\'=&\uprderb
\end{align*}
\else
\begin{align*}
  y=&(\pol)\pravastranaexp \tag{2}\\\\
  y\'=&\prvni\\\\
  y\'\'=&\druhy
\end{align*}
\fi

\textbf{2b. ' . __("Substitution into equation") . '} (' . __("we substitute into (1)") . ')

\setbox0=\hbox{$\rcedosaz=\pravastrana$}
\ifdim\wd0>\hsize
\begin{align*}
  \rcedosazB&=\pravastrana
\end{align*}
\else
\begin{equation*}
  \rcedosaz=\pravastrana
\end{equation*}
\fi
\ifx\pravastranaexp\jednam ' . __('and add like powers of  $ x $ ') . '\else ' . sprintf(__('and divide by the common exponential factor %s and add like powers of $x$'), '$\pravastranaexp$') . '\fi
\begin{align*}
  \uprls&=\pravastranapol\\\\
  \ifx\uprls\uprlsB \else \intertext{' . _("and collect the coefficients at the powers of $ x $ ") . '}\uprlsB&=\pravastranapol\fi
\end{align*}

\textbf{2c. ' . __("We find undetermined coefficients") . '} 
' . __("Comparing coefficients we get (the first equation is from the highest power)") . ' \def\netiskni{0&=0}
\begin{align*}
  \ifx\rovkoeff\netiskni\else\rovkoeff\\\\\fi
  \ifx\rovkoefd\netiskni\else\rovkoefd\\\\\fi
  \ifx\rovkoefc\netiskni\else\rovkoefc\\\\\fi
  \ifx\rovkoefb\netiskni\else\rovkoefb\\\\\fi
  \rovkoefa
\end{align*}
' . __("We solve this system with respect to unknown coefficients and get") . ' 
\begin{equation*}
\vysledek
\end{equation*}

\textbf{3. ' . __("Summary") . '} ' . __("The general solution is sum of the particular solution (obtained from (2)) and general solution of the associated homogeneous equation obtained in the first part of the computation") . '
\begin{equation*}
  y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=\partikularni +C_1 \fundsa+C_2 \fundsb.
\end{equation*}
\fi

\partsol
\end{document}
';
} elseif ($TeXskeleton == 2) {
	$TeXfile = '
\def\specpar{\leftskip 2cm \parindent -1.5cm}

\begin{document}

\parindent 0 pt
\pagestyle{empty}
\everymath{\displaystyle}

\MAWhead{' . __("Second order linear differential equation") . '}

\input data.tex

\def\jedna{1}
\def\mjedna{-1}
\def\nula{0}
\def\jednam{ 1 }

\initial

\ifx\pravastrana\nula
' . __("We solve the homogeneous differential equation") . '
\begin{equation*}
  \rce =0
%  y\'\'+\p y\'+\q y=0
\end{equation*}
\else
' . __("We solve the nonhomogeneous differential equation") . '
\begin{equation*}
  \rce =
\ifx\pravastranaexp\jednam
\pravastranapol
\global\let\pravastrana\pravastranapol
\else
\pravastrana
\fi\tag{1}
%  y\'\'+\p y\'+\q y=0
\end{equation*}

\hrule
\medskip
\textbf{1. ' . __("We solve the associated homogeneous equation first") . '}
\begin{equation*}
  \rce =0 
%  y\'\'+\p y\'+\q y=0
\end{equation*}
\fi

' . __("Characteristic equation") . ': $\charrce \ifx\q\mjedna 1\fi\ifx\q\jedna 1\fi=0$

' . __("Zeros") . ': $\lambda_{1,2}=\frac{-\left(\p\right)\pm\sqrt{\left(\p\right)^2-4\left(\q\right)}}{2}=\frac{\minusp\pm\sqrt{\D}}{2}=
\ifx\jedna\concl \begin{cases}\lambdaa\\\\\lambdab\end{cases}\fi
\ifx\mjedna\concl \resa\pm \ifx\resb\jedna\else \resb\fi i\fi
\ifx\nula\concl \lambdaa\fi
$

\ifx\jedna\concl ' . __("Characteristic equation has two real solutions.") . '\fi 
\ifx\nula\concl ' . __("Characteristic equation has one double solution.") . '\fi 
\ifx\mjedna\concl ' . __("Characteristic equation has two complex solutions.") . '\fi

' . sprintf(__("Two independent solutions are %s and %s."), "$ y_1=\\fundsa$", "$ y_2=\\fundsb$") . '

\ifx\pravastrana\nula
' . sprintf(__("The general solution is %s."), "\$\\reseni\$") . '

\else
' . sprintf(__("The general solution of the associated homogeneous equation is %s."), "\$\\reseni\$") . '

\medskip\hrule\medskip 
\textbf{2. ' . __("Nonhomogeneous equation") . '}


' . sprintf(__("The right hand side has the form %s, where %s."), "$ P(x)e^{\\alpha x}\\sin(\\beta x)+Q(x)e^{\\alpha x}\\cos(\\beta x)$", "$\\alpha=\\PA$, $\\beta=\\coeffsin$, $ P(x)=\\P$, $ Q(x)=\\Q$") . '

\medskip
\def\testk{0 }
\ifx\k\testk
' . sprintf(__("The number %s takes the value %s and it is not solution of the characteristic equation %s."), "$\\lambda=\\alpha+i\\beta$", "$\\lambda=\\testnumber$", '$\charrce \ifx\q\mjedna 1\fi\ifx\q\jedna 1\fi=0$') . ' 
\else
' . sprintf(__("The number %s takes the form %s and it is solution of the characteristic equation %s."), "$\\lambda=\\alpha+i\\beta$", "$\\lambda=\\testnumber$", '$\charrce \ifx\q\mjedna 1\fi\ifx\q\jedna 1\fi=0$') . sprintf(__("The multiplicity of this solution is %s."), "$\k$") . '
\fi

\bigskip
\textbf{' . __("Particular solution") . ':}

{\specpar $  y=\formpartsol$

}

\bigskip
\textbf{' . __("Derivative of particular solution (simplified)") . ':}

\def\spec#1{{\setbox0=\hbox{$#1$} \ifdim \wd0<\hsize \box0 
\else \let\left\relax \let\right\relax #1\fi}}

{\specpar $ y\'=\diffa$

}

{\specpar $ y\'\'=\diffb$

}

\bigskip
\textbf{' . __("Substitution into equation (1) and simplifications") . ':}

{\specpar $\dosazrce=\f$

}

\bigskip \textbf{' . __("Linear system for coefficients") . ':} ' . __("We put corresponding
terms on left and right equal (from smallest power)") . '
\begin{align*}
  \alleqs
\end{align*}


\bigskip
\textbf{' . __("Solution of the linear system") . ':}                
$\soleqs$

\bigskip
\textbf{' . __("Particular solution of the equation") . ':}                
$y_p=\partikularni$

\bigskip
\textbf{' . __("General solution of the equation") . ':}                
' . __("The general solution is sum of the particular solution and general solution of the associated homogeneous equation obtained in the first part of the computation") . '

{\specpar $ y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=\partikularni +C_1 \fundsa+C_2 \fundsb.$

}

\partsol
\end{document}

';
}


system("cd $maw_tempdir; touch output; $catchmawerrors");

if (($IVP == "on") && (filesize($maw_tempdir . "/errors") == 0)) {
	$output = `$mawtimeout -t 10 $maxima --batch-string="p:$p; q:$q; f(x):=$f; x0:$x0; y0:$y0; y10:$y10; gensol:$gensol ; load(\"$mawhome/lde2/homog.mac\");"; >> output`;

	$output = str_replace("\\\n", "", $output);
	$output = str_replace("{\it AAAA}", "y''", $output);
	$output = str_replace("{\it AA}", "y'", $output);

	check_for_errors($output, "IVP: bad input", "lde2", $msg = __("Bad input. Check your data."));

	function najdiretezec($klicoveslovo, $retezec, $key = "###")
	{
		$retezec = str_replace("\n", "", $retezec);
		preg_match("/\#\#\# *" . $klicoveslovo . " (.*?)(\#\#\#)/", $retezec, $matches);
		$vystup = $matches[0];
		$vystup = str_replace("### " . $klicoveslovo, "", $vystup);
		$vystup = str_replace("###", "", $vystup);

		return ($vystup);
	}

	function remove_dollars($string)
	{
		return (str_replace("$$", "", $string));
	}

	function removepercent($string)
	{
		return (str_replace("%", "", $string));
	}

	$TeXpartsol = "\\def\\partsol{\medskip\\hrule\kern 1pt\hrule\medskip";

	$x0t = remove_dollars(najdiretezec("ic0", $output));
	$y0t = remove_dollars(najdiretezec("ic1", $output));
	$y10t = remove_dollars(najdiretezec("ic2", $output));

	$TeXpartsol = $TeXpartsol . " " . __("Now we solve initial value problem") . " $" . remove_dollars(najdiretezec("eq", $output)) . "$;\qquad $ y($x0t)=$y0t$,\\quad $ y'($x0t)=$y10t$";

	function echoT($str)
	{
		global $TeXpartsol;
		$TeXpartsol = $TeXpartsol . $str;
	}

	echoT("\n\n \\par \\bigskip ");
	echoT(__("General solution is") . " " . "$ y(x)=" . remove_dollars(najdiretezec("gensol", $output)) . "$");

	echoT("\n\n\\par ");

	echoT(__("Derivative of general solution is") . " " . "$ y'(x)=" . remove_dollars(najdiretezec("dergensol", $output)) . "$");

	echoT("\n\n\\par \\bigskip ");

	$strA = remove_dollars(najdiretezec("eq1", $output));
	$strB = remove_dollars(najdiretezec("eq2", $output));
	$strC = remove_dollars(najdiretezec("eq11", $output));
	$strD = remove_dollars(najdiretezec("eq21", $output));
	echoT(__("Substituting initial values we get the following linear system") . " " . "$$ \\begin{cases}" . $strA . "\\\\" . $strB . "\\end{cases}$$");

	echoT("\n\n\\par ");

	if (($strA != $strC) || ($strB != $strD)) {
		echoT(__("Simplifying we get") . " " . "$$ \\begin{cases}" . $strC . "\\\\" . $strD . "\\end{cases}$$");
	}

	echoT("\n\n\\par ");

	echoT(__("The solution of this linear system is") . " " . najdiretezec("sol", $output));

	echoT("\n\n\\par ");

	echoT(__("Substituting these values into general solution we get particular solution") . " " . "\\par $ y(x)=" . remove_dollars(najdiretezec("partsol", $output)) . "$ ");

	echoT("}");

	echoT("\\def\\initial{" . __("We solve initial value problem") . " $" . remove_dollars(najdiretezec("eq", $output)) . "$;\qquad $ y($x0t)=$y0t$,\\quad $ y'($x0t)=$y10t$
\\par \\smallskip " . __("We find the general solution first and then we use the initial conditions to find particular solution.") . "\\par \medskip\\hrule\kern 1pt\hrule\medskip}\n");

} else {
	$TeXpartsol = "\\let\\partsol\\relax \\let\\initial\\relax";
}

file_put_contents("$maw_tempdir/ldr2.tex", $TeXheader . $TeXpartsol . $TeXfile);

if (function_exists("lde_before_latex")) {
	lde_before_latex();
}

system("cd $maw_tempdir; echo '<h4>*** Maxima output ****</h4>'>>output; cat data.tex>>output; echo '<h4>*** LaTeX ****</h4>'>>output; $pdflatex ldr2.tex>>output; cp * ..; $catchmawerrors");

/* Errors in compilation? We send PDF file or log of errors              */
/* ---------------------------------------------------------------------*/

$lastline = exec("cd " . $maw_tempdir . "; cat errors");

if ($lastline != "") {
	maw_errmsgB("");
	system("cd " . $maw_tempdir . "; cat msg.html; echo \"<pre>\"; cat output|$mawcat");
	if ((file_exists($maw_tempdir . "/msg.html")) && ($akce == "1")) {
		save_log("<span style='color: rgb(0, 100, 0);'>" . $datcasip . "</span>", "lde2");
	} else {
		save_log_err($datcasip, "lde2");
	}
	$lastline = exec("cd " . $maw_tempdir . "; grep \"Particular solution failed\" output");
	if ($lastline != "") {
		save_log($datcasip, "failed_lde2");
	}
	system("rm -r " . $maw_tempdir);
	die("</pre></body></html>");
} else {
	if ($requestedoutput == "html") {
		if ($mawISAjax == 0) {
			maw_html_head();
		}
		require("$maw_tempdir/data.php");
		require("htmloutput.php");
		if ($mawISAjax == 0) {
			echo('</body></html>');
		}
	} else {
		/* here we send PDF file into browser */
		send_PDF_file_to_browser("$maw_tempdir/ldr2.pdf");
	}
}

/* We clean the temp directory and write log information                */
/* ---------------------------------------------------------------------*/

system("rm -r " . $maw_tempdir);
save_log($datcasip, "lde2");

?>



