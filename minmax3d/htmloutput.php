<?php
printf("<h2>%s</h2>", __("Local extrema in two variables"));

?>

	<div class=logickyBlok>

<?php
printf ("<h4>%s: %s</h4>",__("Function"),"\$ z=$ftex \$");
 
if ($akce=="0")
{
printf ("<h4>%s: %s</h4>",__("Stationary point"), "\$[x,y]=\\left[ $stbod \\right]\$");
}

if ($akce=="2")
{
printf ("<h4>%s:</h4>",__("Tested stationary points")); echo (str_replace(["\\fboxrule 0pt","\boxed"],"",$body));
}


?>
</div>
	<div class=inlinediv>
		<div class=logickyBlok>

<?php printf("<h4>%s</h4>", __("The first derivatives")); ?>
  \begin{align}
    \frac{\partial f(x,y)}{\partial x}&=<?php echo $dfx; ?>\\
    \frac{\partial f(x,y)}{\partial y}&=<?php echo $dfy; ?>
  \end{align}
</div>
	</div>

	<div class=inlinediv>
		<div class=logickyBlok>
<?php printf("<h4>%s</h4>", __("The second derivatives")); ?>

 \begin{align}
    \frac{\partial^2 f(x,y)}{(\partial x)^2}&=<?php echo $dfxx; ?>\\
    \frac{\partial^2 f(x,y)}{\partial x\partial y}&=<?php echo $dfxy; ?>\\
    \frac{\partial^2 f(x,y)}{(\partial y)^2}&=<?php echo $dfyy; ?>
  \end{align}
</div>
	</div>

<?php if ($akce == "0"): ?>
	<div class=inlinediv>
		<div class=logickyBlok>

<?php printf ("<h4>%s</h4>",__("The second derivatives at stationary points")); ?>
  \begin{align}
    \left.\frac{\partial^2 f(x,y)}{(\partial x)^2}\right|_{[x,y]=\left[<?php echo $stbod; ?>\right]}&=<?php echo $dfxxs; ?>\\
    \left.\frac{\partial^2 f(x,y)}{\partial x\partial y}\right|_{[x,y]=\left[<?php echo $stbod; ?>\right]}&=<?php echo $dfxys; ?>\\
    \left.\frac{\partial^2 f(x,y)}{(\partial y)^2}\right|_{[x,y]=\left[<?php echo $stbod; ?>\right]}&=<?php echo $dfyys; ?>
  \end{align}
</div>
	</div>

	<div class=inlinediv>
		<div class=logickyBlok>
<?php

printf ("<h4>%s</h4> \$\$ H\\left[%s\\right]=\\left|\\matrix{%s&%s\\\\%s&%s}\\right|=\\left[%s\\right]\\cdot\\left[%s\\right]-\\left[%s\\right]^2=%s\$\$", __("Hessian"), $stbod, $dfxxs, $dfxys, $dfxys, $dfyys, $dfxxs, $dfyys, $dfxys, $determinant);

?>
</div>
	</div>
	<div class=logickyBlok>
<?php printf ("<h4>%s</h4>", preg_replace("/\\\\textbf{(.*?)}/","<span class=red>\\1</span>",$conclusion)); ?>
</div>

<?php endif; ?>
<?php if ($akce == "2"): ?>

	<div class=inlinediv>
		<div class=logickyBlok>
<?php
printf ("<h4>%s</h4>\\[%s\\]",__("Hessian"), "H\\left(x,y\\right)=$hess");
?>
</div>
	</div>

	<div class=logickyBlok>
		<center>
			<table>
				<tr>
					<th></th>
					<th><?php echo(str_replace("&", "</th><th>", $bodytab)); ?></th>
				</tr>
				<tr>
					<th>$f''_{xx}$</th>
					<td><?php echo(str_replace("&", "</td><td>", $fxxtab)); ?></td>
				</tr>
				<tr>
					<th>$f''_{xy}$</th>
					<td><?php echo(str_replace("&", "</td><td>", $fxytab)); ?></td>
				</tr>
				<tr>
					<th>$f''_{yy}$</th>
					<td><?php echo(str_replace("&", "</td><td>", $fyytab)); ?></td>
				</tr>
				<tr>
					<th><?php echo __("Hessian"); ?></th>
					<td><?php echo(str_replace("&", "</td><td>", $Htab)); ?></td>
				</tr>
				<tr>
					<th><?php echo __("Conclusion"); ?></th>
					<td class=bold><?php echo(str_replace("&", "</td><td class=bold>", $concltab)); ?></td>
				</tr>
			</table>
		</center>
	</div>

<?php endif; ?>