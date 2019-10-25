<?php

echo '<h2>' . __("Second order linear differential equation") . '</h2>';

if ($pravastrana == "0")
	echo __("We solve the homogeneous differential equation");

else {
	echo __("We solve the nonhomogeneous differential equation");

	echo "\\[  $rce = $pravastrana \\tag{1}\\]";
	echo __("Associated homogeneous equation is");
	echo "\\[  $rce =0 .\\]";
}

?>

<div class='logickyBlok'>
<?php
$temp="";
if ($q=="1") {$temp="1";}
if ($q=="-1") {$temp="1";}
echo __("The characteristic equation is").sprintf(" \\[ $charrce %s =0 .\\]",$temp);

echo "<br>";
echo __("Zeros of characteristic equation").": \$\$ \\lambda _{1,2}=\\frac{-\\left($p \\right)\\pm\\sqrt{\\left($p \\right)^2-4\\left($q \\right)}}{2}=\\frac{ $minusp \\pm\\sqrt{ ".$D." }}{2}= ";
if ($concl=="1") {echo " \begin{cases} $lambdaa\\\\ $lambdab\\end{cases} ";}
elseif ($concl=="-1") {$ttemp=""; if (($resb!="1")&&($resb!="-1")) {$ttemp=$resb;} $temp=sprintf(" $resa \\pm %s i ",$ttemp); echo $temp;}
else {echo $lambdaa;}
echo "\$\$ ";
?>

</div>
<div class='logickyBlok'>

<?php
if ($concl=="1") {echo __("Characteristic equation has two real solutions.");}
elseif ($concl=="0") {echo __("Characteristic equation has one double solution.");}
else {echo __("Characteristic equation has two complex solutions.");}
echo " ";
printf(__("Two independent solutions are %s and %s."),"$ y_1=$fundsa $","$ y_2=$fundsb $");

if ($pravastrana=='0')
{
echo " </div><div class=logickyBlok>";
echo (rtrim(sprintf(__("The general solution is %s."),"\$\$ y=C_1 $fundsa +C_2 $fundsb .\$\$"),"."));
echo "</div>";
}


if ($pravastrana!='0'):
echo " ";
echo (rtrim(sprintf(__("The general solution of the associated homogeneous equation is %s."),"\$\$ y=C_1 $fundsa +C_2 $fundsb . \$\$"),"."));

?>

</div>


<?php
if ($TeXskeleton == 0):
	?>

	<div class=logickyBlok>
<?php
echo __("We use the variation of constants to find the particular solution in the form");

echo sprintf("\\[ y_p= A(x) %s + B(x) %s \\]",$fundsa, $fundsb);

?>
</div>
	<div class=logickyBlok>

	  <?php
	  echo __("We have to solve the linear system");

	  ?>
		\[
		\begin{aligned}
		&A'(x) && \left[<?php echo $fundsa; ?>\right] &{}+{}&B'(x)&&\left[<?php echo $fundsb; ?>\right] &&{}= 0\\
		&A'(x) && \left[<?php echo $fundsader; ?>\right]&{}+{}&B'(x)&&\left[<?php echo $fundsbder; ?>\right]
		&&{}= <?php echo $pravastrana; ?>
		\end{aligned}
		\]

	  <?php echo sprintf(__("with unknowns %s and %s."), "$ A'(x)$", "$ B'(x)$");

	  echo " ";
	  echo sprintf(__("Determinant of the coefficient matrix (wronskian of the solutions %s and %s) is"), "$ y_1$", "$ y_2$");

	  ?>
		\[
		W[y_1,y_2](x)=\left|\matrix{y_1(x) & y_2(x)\cr y'_1(x)&y'_2(x)}\right| =\left |
	  <?php echo str_replace("pmatrix", "matrix", $wronskimat); ?> \right|=<?php echo $wronski; ?>
		\]

		<br>
	  <?php echo __("Auxiliary determinants are"); ?>
		<br>


		\begin{align}
		& W_1(x)=\left| \matrix{0 & y_2(x)\cr f(x)&y'_2(x)} \right|=\left|
	  <?php echo str_replace("pmatrix", "matrix", $wronskimatA); ?>\right|=<?php echo $wronskiA; ?>
		,\\
		& W_2(x)=\left| \matrix{y_1(x) & 0\cr y'_1(x)& f(x)} \right|=\left|
	  <?php echo str_replace("pmatrix", "matrix", $wronskimatB); ?>\right|=<?php echo $wronskiB; ?>.
		\end{align}

		<br>
	  <?php echo sprintf(__("Solution of the system for %s and %s is"), "$ A'(x)$", " $ B'(x)$"); ?>

		\begin{align}
		&A'(x)=\frac{W_1}{W}=<?php echo $derA; ?>\\
		&B'(x)=\frac{W_2}{W}=<?php echo $derB; ?>
		\end{align}
	</div>
	<div class=logickyBlok>


<?php echo sprintf(__("Integration gives %s and %s (by clicking the integral you load the integral into tool for indefinite integration)"),"$ A(x)$","$ B(x)$"); ?>

$$A(x)=<?php echo $intderA; ?>=<?php echo $A; ?>$$

$$B(x)=<?php echo $intderB; ?>=<?php echo $B; ?>$$

</div>
	<div class=logickyBlok>
<?php

echo __("Particular solution (after substitution and simplification)");

echo " \\[   y_p(x)=A(x)y_1(x)+B(x)y_2(x)= $yp \\]";
?>
</div>

	<div class=logickyBlok>
<?php

echo __("General solution").":";

echo " \[   y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=$yp +C_1 $fundsa+C_2 $fundsb. \] ";

?>
</div>

<?php endif;


endif;   // TeXskeleton =0
?>


<?php if ($TeXskeleton == 1):

	?>

	<div class=logickyBlok>

	  <?php printf("<h4>%s</h4>", __("We look for the particular solution of the nonhomogeneous equation.")); ?>

		<br>
	  <?php if ($pravastranaexpkoef == "0") {
		  printf(__("The right hand side is polynomial %s, the degree of this polynomial is %s."), "\$ P(x)=$pravastranapol\$", "\$ $pravastranapolst \$");
	  } else {
		  printf(__("The right hand side is product of polynomial %s and exponential function %s, the degree of the polynomial is %s."), "\$ P(x)=$pravastranapol \$", "\$ $pravastranaexp \$", " \$ $pravastranapolst \$");
	  }

	  ?>

	  <?php

	  if ($pravastranaexp == "1") {
		  $temp = "";
	  } else {
		  $temp = $pravastranaexp;
	  }

	  if ($nasobnost == "0") {
		  echo(rtrim(sprintf(__("The number %s is not a zero of the characteristic equation and the particular solution is in the form %s."), "\$ $pravastranaexpkoef \$", " \$\$ y=($polynomtest) $temp .\$\$"), "."));
	  }
	  ?>

	  <?php
	  if ($nasobnost == "1") {
		  printf(__("The number %s is zero of multiplicity 1 of the characteristic equation and the particular solution has the form %s."), "\$ $pravastranaexpkoef \$", " \$ y=x($polynomtest)$temp \$");
	  } elseif ($nasobnost == "2") {
		  printf(__("The number %s is double zero of the characteristic equation and the particular solution has the form %s."), "\$ $pravastranaexpkoef \$", " \$ y=x^2($polynomtest) $temp \$");
	  }

	  ?>


	  <?php function prepinac($co)
	  {
		  global $prepinacpred, $prepinacza;

		  return $prepinacpred . " " . $co . " " . $prepinacza;
	  }

	  if ($znamenkoexponentu == '-1') {
		  $prepinacpred = "\\left( ";
		  $prepinacza = "\\right) ";
	  }

	  $prvni = "($derpol)$pravastranaexp+($pol)" . prepinac($derexp) . "=($uprder)$pravastranaexp";

	  $druhy = "($derb)$pravastranaexp+($uprder)" . prepinac($derexp) . "=($uprderb)$pravastranaexp";

	  ?>

	  <?php
	  printf("<h4>%s</h4>", __("Preliminary"));

	  echo __("we have to find derivatives and put into the equation");

	  if ($pravastranaexp == '1') {
		  echo "\\begin{align*}  y=&$pol \\tag{2}\\\\ y'=&" . str_replace("=", "\\\\=&", $derpol) . "\\\\ y''=&" . str_replace("=", "\\\\=&", $uprderb) . " \\end{align*}";
	  } else {
		  echo "\\begin{align*} y=&($pol)$pravastranaexp \\tag{2}\\\\ y'=&" . str_replace("=", "\\\\=&", $prvni) . " \\\\  y''=&" . str_replace("=", "\\\\=&", $druhy) . " \\end{align*}";
	  }

	  printf("<h4>%s</h4>", __("Substitution into equation"));

	  echo __("we substitute into (1)");

	  if ($pravastranaexp == "1") {

		  $tempecho = "\\[ \\underbrace{($uprderb)}_{y''} ";
		  if ($p != '0') {
			  $tempecho = $tempecho . "+ $p \\underbrace{($uprder)}_{y'} ";
		  }
		  $tempecho = $tempecho . "+ $q \\underbrace{($pol)}_{y}=$pravastrana \\]";
		  echo(str_replace("+ -", "-", $tempecho));

	  } else {
		  $tempecho = "\\[ \\underbrace{($uprderb)$pravastranaexp}_{y''} ";
		  if ($p != '0') {
			  $tempecho = $tempecho . "+ $p \\underbrace{($uprder)$pravastranaexp}_{y'}";
		  }
		  $tempecho = $tempecho . "+ $q \\underbrace{($pol)$pravastranaexp}_{y}=$pravastrana \\]";
		  echo(str_replace("+ -", "-", $tempecho));
	  }

	  if ($pravastranaexp == "1") {
		  echo __('and add like powers of  $ x $ ');
	  } else {
		  printf(__('and divide by the common exponential factor %s and add like powers of $x$'), "\$ $pravastranaexp\$");
	  }

	  echo "\\[  $uprls=$pravastranapol\\]";

	  if ($uprls != $uprlsB) {
		  echo _("and collect the coefficients at the powers of $ x $ ");
		  echo "\\[$uprlsB=$pravastranapol\\]";
	  }


	  printf("<h4>%s</h4>", __("We find undetermined coefficients"));

	  echo __("Comparing coefficients we get (the first equation is from the highest power)"); // .' \def\netiskni{0&=0}

	  function optecho($co)
	  {
		  if ($co != "0&=0") {
			  return ($co . "\\\\ ");
		  }
	  }

	  echo "\\begin{align*} " . optecho($rovkoeff) . optecho($rovkoefd) . optecho($rovkoefc) . optecho($rovkoefb) . " $rovkoefa \\end{align*}";

	  echo __("We solve this system with respect to unknown coefficients and get");

	  echo "\\[ $vysledek \\]";

	  ?>
	</div>
	<div class=logickyBlok>
<?php
printf ("<h4>%s</h4>", __("Summary"));
echo __("The general solution is sum of the particular solution (obtained from (2)) and general solution of the associated homogeneous equation obtained in the first part of the computation");

echo "\\begin{equation*}  y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=$partikularni +C_1 $fundsa+C_2 $fundsb. \\end{equation*}";

?>
</div>
<?php
endif;

if ($TeXskeleton == 2):

	?>

	<script type="text/x-mathjax-config">
MathJax.Hub.Config({
  "HTML-CSS": { linebreaks: { automatic: true } },
   SVG: { linebreaks: { automatic: true } }
   });


	</script>

	<?php
	printf("<h4>%s</h4>", __("Nonhomogeneous equation"));

	printf(__("The right hand side has the form %s, where %s."), "\$ P(x)e^{\\alpha x}\\sin(\\beta x)+Q(x)e^{\\alpha x}\\cos(\\beta x)\$", "\$ \\alpha=$PA\$, \$\\beta=$coeffsin\$, \$ P(x)=$P\$, \$ Q(x)=$Q\$");

	if ($k == '0 ') {
		$temp = "";
		if (($q == "1") || ($q == "-1")) {
			$temp = "1";
		}
		printf(__("The number %s takes the value %s and it is not solution of the characteristic equation %s."), "\$\\lambda=\\alpha+i\\beta\$", "\$\\lambda=$testnumber\$", "\$ $charrce $temp=0\$");
	} else {
		$temp = "";
		if (($q == "1") || ($q == "-1")) {
			$temp = "1";
		}
		printf(__("The number %s takes the form %s and it is solution of the characteristic equation %s."), "$\\lambda=\\alpha+i\\beta$", "$\\lambda=$testnumber$", "\$ $charrce $temp =0\$") . sprintf(__("The multiplicity of this solution is %s."), "$$k$");
	}

	printf("<h4>%s</h4>", __("Particular solution"));

	echo "\\[  y=$formpartsol \\]";

	printf("<h4>%s</h4>", __("Derivative of particular solution (simplified)"));


	echo "<br>\\( y^{\\prime}=$diffa\\)";
	echo "<br>\\( y^{\\prime\\prime}=$diffb\\)";

	printf("<h4>%s</h4>", __("Substitution into equation (1) and simplifications"));

	echo "\\($dosazrce=$f\\)";

	printf("<h4>%s</h4>", __("Linear system for coefficients"));

	echo __("We put corresponding terms on left and right equal (from smallest power)");

	echo "\\begin{align} $alleqs \\end{align}";


	echo __("Solution of the linear system");
	echo "\\[ " . str_replace("\\penalty0", "", $soleqs) . " \\]";

	echo __("Particular solution of the equation");
	echo "\\[ y_p=$partikularni \\]";
	?>

	<div class=logickyBlok>

<?php
printf ("<h4>%s</h4>", __("General solution of the equation"));
echo __("The general solution is sum of the particular solution and general solution of the associated homogeneous equation obtained in the first part of the computation");

echo "\\begin{equation*}  y(x)=y_p(x)+C_1y_1(x)+C_2 y_2(x)=$partikularni +C_1 $fundsa+C_2 $fundsb. \\end{equation*}";

?>
</div>

<?php endif; ?>


<?php

if (($IVP == "on") && (filesize($maw_tempdir . "/errors") == 0)) {
	echo "<div class=logickyBlok>";

	echo __("Now we solve initial value problem") . " \$" . remove_dollars(najdiretezec("eq", $output)) . "\$; \$ y($x0t)=$y0t\$, \$ y'($x0t)=$y10t \$";

	echo "<br>";
	echo(__("General solution is") . " " . "\$\$ y(x)=" . remove_dollars(najdiretezec("gensol", $output)) . "\$\$");

	echo "<br>";
	echo(__("Derivative of general solution is") . " " . "\$\$ y'(x)=" . remove_dollars(najdiretezec("dergensol", $output)) . "\$\$");

	$strA = remove_dollars(najdiretezec("eq1", $output));
	$strB = remove_dollars(najdiretezec("eq2", $output));
	$strC = remove_dollars(najdiretezec("eq11", $output));
	$strD = remove_dollars(najdiretezec("eq21", $output));
	echo "<br>";
	echo(__("Substituting initial values we get the following linear system") . " " . "$$ \\begin{cases}" . $strA . "\\\\" . $strB . "\\end{cases}$$");

	echo "<br>";
	if (($strA != $strC) || ($strB != $strD)) {
		echo(__("Simplifying we get") . " " . "$$ \\begin{cases}" . $strC . "\\\\" . $strD . "\\end{cases}$$");
	}


	echo(__("The solution of this linear system is") . " " . najdiretezec("sol", $output));


	echo(__("Substituting these values into general solution we get particular solution") . " " . "$ y(x)=" . remove_dollars(najdiretezec("partsol", $output)) . "$ ");

	echo "</div>";
}


?>


