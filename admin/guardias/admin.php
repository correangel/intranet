<?
session_start();
include("../../config.php");
// COMPROBAMOS LA SESION
if ($_SESSION['autentificado'] != 1) {
	$_SESSION = array();
	session_destroy();
	header('Location:'.'http://'.$dominio.'/intranet/salir.php');
	exit();
}

if($_SESSION['cambiar_clave']) {
	header('Location:'.'http://'.$dominio.'/intranet/clave.php');
}

registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);


$profesor = $_SESSION['profi'];
if(!(stristr($_SESSION['cargo'],'1') == TRUE))
{
	header("location:http://$dominio/intranet/salir.php");
	exit;
}
?>

<?
include("../../menu.php");
if (isset($_GET['profeso'])) {$profeso = $_GET['profeso'];}elseif (isset($_POST['profeso'])) {$profeso = $_POST['profeso'];}else{$profeso="";}
if (isset($_GET['sustituido'])) {$sustituido = $_GET['sustituido'];}elseif (isset($_POST['sustituido'])) {$sustituido = $_POST['sustituido'];}else{$sustituido="";}
if (isset($_GET['hora'])) {$hora = $_GET['hora'];}elseif (isset($_POST['hora'])) {$hora = $_POST['hora'];}else{$hora="";}
if (isset($_GET['submit2'])) {$submit2 = $_GET['submit2'];}elseif (isset($_POST['submit2'])) {$submit2 = $_POST['submit2'];}else{$submit2="";}
if (isset($_GET['gu_fecha'])) {$gu_fecha = $_GET['gu_fecha'];}elseif (isset($_POST['gu_fecha'])) {$gu_fecha = $_POST['gu_fecha'];}else{$gu_fecha="";}

?>
<div class="container">
<div class="row">
<br />
<div class="page-header">
<h2>Guardias de Aula <small> Registro de guardias</small></h2>
</div>
<div class="col-sm-5 col-sm-offset-1"><br>
<? if ($mod_horario) {
	?>
<div class="well well-large">
<FORM action="admin.php" method="POST" name="Cursos">
<div class="form-group"><label> Selecciona Profesor </label> 
<SELECT
	name=profeso onChange="submit()" class="form-control">
	<option><? echo $profeso;?></option>
	<?
	$profe = mysql_query(" SELECT distinct prof FROM horw where a_asig = 'GU' order by prof asc");
	if ($filaprofe = mysql_fetch_array($profe))
	{
		do {

			$opcion1 = printf ("<OPTION>$filaprofe[0]</OPTION>");
			echo "$opcion1";

		} while($filaprofe = mysql_fetch_array($profe));
	}
	?>
</select></div>
</FORM>
	<?
	if ($profeso) {
		$pr=$profeso;
		$link="1";
		include("../../horario.php");
		?> <?
	}
	?></div>
<div class="well">
<p class="text-justify"><strong>Instrucciones de uso.</strong><br>
Selecciona el Profesor al que quieres apuntar una sustituci�n no
registrada. Te aparecer� el horario del Profesor, para que puedas
determinar con precisi�n la hora de la guardia (1� hora, 2� hora, etc)
del d�a en cuesti�n. Seleccionas a continuaci�n el Profesor sustituido.
Al hacer click en el campo de la fecha, aparecer� una nueva ventana con
el calendario en el que debes pinchar sobre la fecha elegida. Escribe la
hora de la guardia (1, 2, 3, etc) y env�a los datos.<br />
Si quieres consultar el historial de guardias de un Profesor, pincha en
<em>Consultar guardias y profesores</em>. Selecciona el Profesor y
aparecer� un hist�rico con todas las sustituciones realizadas. Si
pinchas en una de las fuardias de su horario, podr�s ver las
sutituciones de todos los profesores de esa guardia en esa hora a lo
largo del curso.</p>
</div>
</div>
<div class="col-sm-5">
<br>
<div class="well well-large">
<FORM action="guardias.php" method="POST" name="f1">
	<div class="form-group">
	<label>Profesor a sustituir</label>
              <SELECT  name="sustituido" class="form-control">
              <option><? echo $sustituido;?></option>
		        <?
  $profe = mysql_query(" SELECT distinct prof FROM horw order by prof asc");
  if ($filaprofe = mysql_fetch_array($profe))
        {
        do {

	      $opcion1 = printf ("<OPTION>$filaprofe[0]</OPTION>");
	      echo "$opcion1";

	} while($filaprofe = mysql_fetch_array($profe));
        }
	?>
              </select>
    </div>    
    
    <div class="form-group" id="datetimepicker1">     
	<label>Fecha de la sustituci�n</label>
	<input type="hidden" name="profeso" value="<? echo $profeso;?>">
	     <div class="input-group">
            <input name="gu_fecha" type="text" class="form-control" value="" data-date-format="DD-MM-YYYY" id="gu_fecha" >
  <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
</div>   
</div>


<div class="form-group">
<label>Hora de la Guardia: </label> 
<select	name="hora" class="form-control">
	<option>1</option>
	<option>2</option>
	<option>3</option>
	<option>4</option>
	<option>5</option>
	<option>6</option>
</select>
</div>

<input type="submit" name="submit2" value="Enviar datos"
	class="btn btn-success">
</form>
<br />

</div>
<a href='guardias_admin.php' class="btn btn-primary btn-block">Consultar Guardias
y Profesores</a>
</div>
</div>
</div>

	<?
}
else {
	echo '<div align="center"><div class="alert alert-warning alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>
El m�dulo de Horarios debe ser activado en la Configuraci�n general de la Intranet para poder acceder a estas p�ginas, y ahora mismo est� desactivado
          </div></div>';
}
?> <? include("../../pie.php");?> <script>  
$(function ()  
{ 
	$('#datetimepicker1').datetimepicker({
		language: 'es',
		pickTime: false
	})
});  
</script>
</BODY>
</HTML>