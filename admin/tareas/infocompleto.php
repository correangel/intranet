<?
session_start ();
include ("../../config.php");
if ($_SESSION ['autentificado'] != '1') {
	session_destroy ();
	header ( "location:http://$dominio/intranet/salir.php" );
	exit ();
}
registraPagina ( $_SERVER ['REQUEST_URI'], $db_host, $db_user, $db_pass, $db );

$profesor = $_SESSION ['profi'];
$cargo = $_SESSION ['cargo'];

include("../../menu.php");
include("menu.php");  
  
if (isset($_POST['id'])) {
	$id = $_POST['id'];
} 
elseif (isset($_GET['id'])) {
	$id = $_GET['id'];
} 
else
{
$id="";
}
?>
<div class="page-header">
  <h2>Informes de Tareas <small> Informe por asignaturas</small></h2>
</div>
<br />
<?php
echo "<div align='center'>";

  if (isset($_POST['llenar'])) {
	$llenar = $_POST['llenar'];
} 
elseif (isset($_GET['llenar'])) {
	$llenar = $_GET['llenar'];
} 
else
{
$llenar="";
}

if(empty($llenar)){}else{$id = $llenar;}

$c = mysql_connect ( $db_host, $db_user, $db_pass );
echo "<div align='center'>";
$alumno = mysql_query ( "SELECT APELLIDOS,NOMBRE,tareas_alumnos.unidad,tareas_alumnos.id, tutor, FECHA, duracion, claveal FROM tareas_alumnos, FTUTORES WHERE FTUTORES.unidad = tareas_alumnos.unidad and ID='$id'", $c );
$dalumno = mysql_fetch_array ( $alumno );
$claveal = $dalumno [7];
$fecha_t = $dalumno[5];
   	$foto = '../../xml/fotos/'.$claveal.'.jpg';
	if (file_exists($foto) and !(empty($dalumno[0]))) {
	echo "<div style='width:150px;margin:auto;'>";
	echo "<img src='../../xml/fotos/$claveal.jpg' border='2' width='100' height='119' style='margin-top:10px;border:1px solid #bbb;''  />";
	echo "</div><br />";
}
if (empty ( $dalumno [0] )) {
	echo '<div align="center"><div class="alert alert-warning alert-block fade in" style="max-width:500px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>
Debes seleccionar un alumno en primer lugar.<br>Vuelve atr�s e int�ntalo de nuevo<br><br />
<input type="button" onClick="history.back(1)" value="Volver" class="btn btn-danger">
</div></div><hr>';
	exit();
}

echo "<h4>$dalumno[1] $dalumno[0] <span>($dalumno[2])</span><br><br /> <span>Fecha de Expulsi�n:</span> $dalumno[5] ($dalumno[6] d�as)<br><span>Tutor:</span> $dalumno[4]</h4><br />";

$datos = mysql_query ( "SELECT asignatura, tarea, confirmado, profesor FROM tareas_profesor WHERE id_alumno='$id'", $c );
if (mysql_num_rows ( $datos ) > 0) {
echo "<table class='table table-striped table-bordered' align='center' style='width:900px'>";
	while ( $informe = mysql_fetch_array ( $datos ) ) {
		echo "<tr><td style='width:160px;'><strong>$informe[0]</strong></td>
		<td style='width:220px;'>$informe[3]</td>
		  <td>$informe[1]</td>";
		echo "<td>$informe[2]</td>";
		echo "</tr>";
	}
	

	
$combas = mysql_query("select combasi from alma where claveal = '$claveal'");
$combasi = mysql_fetch_array($combas);
$tr_comb = explode(":",$combasi[0]);
$frase=" and (";
foreach ($tr_comb as $codasi)
{
	$frase.="codigo = '$codasi' or ";
}
$frase = substr($frase,0,-19).")";

$datos1 = mysql_query("SELECT distinct materia, profesor from profesores, asignaturas WHERE materia = nombre and profesores.grupo = '$dalumno[2]' and profesor not in (SELECT profesor FROM tareas_profesor WHERE id_alumno='$id') and materia not in (SELECT asignatura FROM tareas_profesor WHERE id_alumno='$id')  and abrev not like '%\_%' $frase");
while($informe1 = mysql_fetch_array($datos1))
{
	echo "<tr><td style='width:160px;'><strong>$informe1[0]</strong></td>
		<td style='width:220px;'>$informe1[1]</td>
		  <td></td><td></td>";
	echo"</tr>";
}

	
	echo "</table>";
} else {
	echo '<br /><div align="center"><div class="alert alert-warning alert-block fade in" style="max-width:500px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>
Los Profesores no han rellenado a�n su Informe de tareas.<br /><br />
<input name="volver" type="button" onClick="history.go(-1)" value="Volver" class="btn btn-danger">
</div></div><hr>';
}
mysql_close ( $c );
?>
</div>
	<? include("../../pie.php");?>								
</body>
</html>
