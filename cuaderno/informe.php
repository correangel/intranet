<?
session_start();
include("../config.php");
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


?>
<?
include("../menu.php");
?>
<div class="container">
<div class="row">
<?
echo "<br /><div class='page-header'>";
$n_profe = explode(", ",$pr);
$nombre_profe = "$n_profe[1] $n_profe[0]";
echo "<h2 class='no_imprimir'>Cuaderno de Notas&nbsp;&nbsp;<small> Informes personales</small></h2>";
echo "</div>";
echo '<div align="center">';
?>
<div class="col-sm-8 col-sm-offset-2">
<?


 foreach($_GET as $key => $val)
	{
		${$key} = $val;
	}
echo "<legend>$curso <span class='muted'>( $nom_asig )</span></legend><br>";	
	 
if($siguiente == '1'){
  $unidad = $curso;
  $adelante1 = "select nc, claveal from FALUMNOS where unidad = '$unidad' and nc>$nc order by nc asc limit 2";
  $adelante0 = mysql_query($adelante1);
  $adelante = mysql_fetch_array($adelante0);
  $nc = $adelante[0];
  $claveal = $adelante[1];
  }
   if($anterior == '1'){
  $unidad = $curso;
  $menor = $nc - 1;
  $anterior1 = "select nc, claveal from FALUMNOS where unidad = '$unidad' and nc < '$nc' order by nc desc limit 1";
  $anterior0 = mysql_query($anterior1);
  $anterior = mysql_fetch_array($anterior0);
  $nc = $anterior[0];
  $claveal = $anterior[1];
  }
  $alum = mysql_query("select nc, unidad , nombre, apellidos from FALUMNOS where claveal = '$claveal'");
  $alumno = mysql_fetch_array($alum);
  $nc = $alumno[0];
  $unidad = $alumno[1];
  $nombre = $alumno[2];
  $apellidos = $alumno[3];
  $curso = $unidad;

	
	$max_nc = mysql_query("select max(nc) from FALUMNOS where unidad = '$unidad'");
  	$max = mysql_fetch_row($max_nc);
	$ultimo = $max[0];
	
   	$foto = '../xml/fotos/'.$claveal.'.jpg';
	if (file_exists($foto)) {
		echo "<div style='width:150px;margin:auto;'>";
		echo "<img src='../xml/fotos/$claveal.jpg' border='2' width='100' height='119' class='img-thumbnail'  />";
		echo "</div>";
	}
	
  echo "<br /><div class='well'><h4 class='text-info'>";
  
  if($nc > 1){$mens_ant = "informe.php?profesor=$profesor&clave=$clave&nc=$nc&curso=$curso&asignatura=$asignatura&nombre=$nombre&apellidos=$apellidos&nom_asig=$nom_asig&anterior=1";
  echo '<button class="btn btn-primary btn-sm" name="anterior" onclick="window.location=\'';	
  echo $mens_ant;
  echo '\'" style="cursor: pointer;"><i class="fa fa-chevron-left">&nbsp; </i> Anterior</button>';}
  
  echo "&nbsp;&nbsp; $nombre $apellidos &nbsp;&nbsp;"; 
   
  if($nc < $ultimo){
 $mens_sig = "informe.php?profesor=$profesor&clave=$clave&nc=$nc&curso=$curso&asignatura=$asignatura&nombre=$nombre&apellidos=$apellidos&nom_asig=$nom_asig&siguiente=1";
	echo ' <button class="btn btn-primary btn-sm" name="siguiente" onclick="window.location=\'';	
	echo $mens_sig;
	echo '\'" style="cursor: pointer;">Siguiente &nbsp;<i class="fa fa-chevron-right "> </i> </button>';}

  echo "</h4></div>"; 

?>
<div class="tabbable" style="margin-bottom: 18px;">
<ul class="nav nav-tabs">
<li class="active"><a href="#tab1" data-toggle="tab">Notas del alumno</a></li>
<li><a href="#tab2" data-toggle="tab">Datos generales</a></li>
<li><a href="#tab3" data-toggle="tab">Datos académicos </a></li>
</ul>
<div class="tab-content" style="padding-bottom: 9px; border-bottom: 1px solid #ddd;">
<div class="tab-pane fade in active" id="tab1">
<br>
<?  
// Procesamos los datosxxxx
$datos1 = "select distinct fecha, nombre, nota from datos, notas_cuaderno where  notas_cuaderno.id = datos.id and profesor = '$profesor' and curso like '%$curso%,' and claveal = '$claveal' and asignatura = '$asignatura' order by orden";
$datos0 = mysql_query($datos1);
	if (mysql_num_rows($datos0) > 0) {
		?>
    <h4 class='text-info'>
 Notas en la Columnas</h4><br />
    <?
echo "<table align='center' class='table table-striped' style='width:auto'>\n"; 
echo "<tr><th>Fecha</td><th>Columna</td><th>Nota</td>";
		while($datos = mysql_fetch_array($datos0))
		{
		echo "<tr><td class='muted'>".cambia_fecha($datos[0])."</td><td class='muted'>$datos[1]</td><td align='center' class='text-success'> <strong>$datos[2]</strong></td></tr>";
		}
echo "</table>";
		}
		else	
		{
echo '<br /><div align="center"><div class="alert alert-danger alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCIÓN:</h5>
El alumno no tiene notas registradas.
</div></div>';		
		}
?>
</div>

<div class="tab-pane fade in" id="tab2">
<br>
<?		
   	include("informes/datos.php");
	echo '<hr style="width:400px;">';   
?>
    </div>
    
<div class="tab-pane fade in" id="tab3">
<br>
<div align="left">
<?	
include("informes/faltas.php");
echo '<hr style="width:400px;">';
include("informes/fechorias.php");
echo '<hr style="width:400px;">';
include("informes/notas.php");
?>
</div>
<br />
</div>
</div>
</div>
</div>
</div>
</div>
<? 
include("../pie.php");
?>
  </body>
</html>

