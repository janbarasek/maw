<?php

echo "<h2>".__("Autonomous system in plane")."</h2>";

?>

<div class=logickyBlok>
<?php echo __("Autonomous system"); ?>

\begin{align*}
 x'={}&<?php echo $ftex; ?>\\
 y'={}&<?php echo $gtex; ?>
\end{align*}
<?php echo __("Stationary point"); ?>
$$[x,y]=\left[<?php echo $stbod; ?>\right]$$
</div>
<div class=logickyBlok>
<?php echo __("Jacobi matrix"); ?>

 $$J(x,y)=
 <?php echo $jakobihomatice; ?>
 $$

<?php echo __("Jacobi matrix at stationary point"); ?>

 $$J\left(<?php echo $stbod; ?>\right)=
<?php echo $jakobihomatices; ?>
 $$
</div> 
<div class=logickyBlok>
<?php
echo __("Characteristic polynomial");

echo "<br><br> a)";
echo (sprintf(__("From the determinant of %s"),"\$J-\\lambda I\$")); 

?>

<br>&nbsp;&nbsp; $\Bigl|J-\lambda I\Bigr|=
 \left |\matrix{<?php echo $dfxs; ?>-\lambda&<?php echo $dfys; ?>\cr <?php echo $dgxs; ?> &<?php echo $dgys; ?>-\lambda\cr}\right|=
 \left(<?php echo $dfxs; ?>-\lambda\right)\left(<?php echo $dgys; ?>-\lambda\right)-(<?php echo $dfys?>)(<?php echo $dgxs; ?>)=
 <?php echo $charpoly; ?>$

 <br> b) <?php echo (sprintf(__("From trace and determinant of %s"),"\$J\$")); ?>

<br>&nbsp;&nbsp;$|J|=<?php echo $determinant; ?>$

<br>&nbsp;&nbsp;$\hbox{Tr } J=<?php echo $trace;?>$

<br>&nbsp;&nbsp;$\lambda^2-\text{Tr}(J)\lambda+|J|=<?php echo $charpoly; ?>$

</div>
<div class=logickyBlok>
<?php

function upravitVystup ($co)
{
return(str_replace(Array("\\\\", "\\null", "\\qquad"),Array(""),$co));
}

echo __("Eigenvalues");
echo upravitVystup($vlastnicisla);

?>

<br>

<?php
echo __("Eigenvalues (numerically)");
echo upravitVystup($vlastnicislanum);

?>
</div>
<div class=logickyBlok>
<?php
echo sprintf(__("Stationary point is %s."),"$decision");


?>
</div>