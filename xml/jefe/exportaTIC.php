<?
if(!(stristr($_SESSION['cargo'],'1') == TRUE))
{
header("location:http://$dominio/intranet/salir.php");
exit;	
}

//$borrar = "truncate table usuarioalumno";
//mysql_query($borrar);
$alumnos = "select distinct CLAVEAL, APELLIDOS, NOMBRE, UNIDAD from alma where claveal not in (select claveal from usuarioalumno)";
$sqlal = mysql_query($alumnos);
while ($sqlprof0 = mysql_fetch_array($sqlal)) {
	$apellidos = $sqlprof0[1];
	$apellido = explode(" ",$sqlprof0[1]);
	$alternativo = strtolower(substr($sqlprof0[3],0,2));
	$nombreorig = $sqlprof0[2] . " " . $sqlprof0[1];
	$nombre = $sqlprof0[2];
	$claveal = $sqlprof0[0];
	if (substr($nombre,0,1) == "�") {$nombre = str_replace("�","A",$nombre);}
	if (substr($nombre,0,1) == "�") {$nombre = str_replace("�","E",$nombre);}
	if (substr($nombre,0,1) == "�") {$nombre = str_replace("�","I",$nombre);}
	if (substr($nombre,0,1) == "�") {$nombre = str_replace("�","O",$nombre);}
	if (substr($nombre,0,1) == "�") {$nombre = str_replace("�","U",$nombre);}
	
	$apellido[0] = str_replace("�","A",$apellido[0]);
	$apellido[0] = str_replace("�","E",$apellido[0]);
	$apellido[0] = str_replace("�","I",$apellido[0]);
	$apellido[0] = str_replace("�","O",$apellido[0]);
	$apellido[0] = str_replace("�","U",$apellido[0]);
	$apellido[0] = str_replace("�","a",$apellido[0]);
	$apellido[0] = str_replace("�","e",$apellido[0]);
	$apellido[0] = str_replace("�","i",$apellido[0]);
	$apellido[0] = str_replace("�","o",$apellido[0]);
	$apellido[0] = str_replace("�","u",$apellido[0]);
	$apellido[0] = str_replace("�","u",$apellido[0]);
	$apellido[0] = str_replace("�","o",$apellido[0]);

	
	$userpass = "a".strtolower(substr($nombre,0,1)).strtolower($apellido[0]);
	$userpass = str_replace("�","",$userpass);
	$userpass = str_replace("�","n",$userpass);
	$userpass = str_replace("-","",$userpass);
	$userpass = str_replace("-","",$userpass);
	$userpass = str_replace("'","",$userpass);
	$userpass = str_replace("�","",$userpass);
	
	$usuario  = $userpass;
	$passw = $userpass . preg_replace('/([ ])/e', 'rand(0,9)', '   ');
	$unidad = $sqlprof0[3];
	$claveal = $sqlprof0[0];
	
	$insertar = "insert into usuarioalumno set nombre = '$nombreorig', usuario = '$usuario', pass = '$passw', perfil = 'a', unidad = '$unidad', claveal = '$claveal'";
	mysql_query($insertar);
}


$repetidos = mysql_query("select usuario from usuarioalumno");
while($num = mysql_fetch_row($repetidos))
{
$n_a = "";
$repetidos1 = mysql_query("select usuario, claveal, unidad from usuarioalumno where usuario = '$num[0]'");
if (mysql_num_rows($repetidos1) > 1) {
while($num1 = mysql_fetch_row($repetidos1))
{
$n_a = $n_a +1;
$nuevo = $num1[0].$n_a;
mysql_query("update usuarioalumno set usuario = '$nuevo' where claveal = '$num1[1]'");
}	
}
}
echo '<br /><div align="center"><div class="alert alert-success alert-block fade in" style="max-width:500px;text-align:left">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
Los datos de los alumnos se han importado correctamente en la tabla "usuarioalumno".<br> Se ha generado un fichero (alumnos.txt) en el subdirectorio "xml/jefe/TIC/" preparado para el alta masiva en el Servidor TIC.
</div></div><br />';

// C�digo y abreviatura de la asignatura.
$codigo = "select  usuario, nombre, perfil from usuarioalumno";
//echo $codigo . "<br>";
$sqlcod = mysql_query ($codigo);
while($row = mysql_fetch_array($sqlcod))
{

$linea = "$row[0];$row[1];$row[2];\n";
$todo .= $linea;
		}
 if (!(file_exists("TIC/alumnos.txt")))
{
$fp=fopen("TIC/alumnos.txt","w+");
 }
 else
 {
 $fp=fopen("TIC/alumnos.txt","w+");
 }
 $pepito=fwrite($fp,$todo);
 fclose ($fp);
   
// Moodle
$codigo1 = "select usuario, pass, alma.apellidos, alma.nombre, alma.unidad from usuarioalumno, alma where alma.claveal=usuarioalumno.claveal";
$sqlcod1 = mysql_query ($codigo1);
$todos_moodle="username;password;firstname;lastname;email;city;country\n";
while($rowprof = mysql_fetch_array($sqlcod1))
{
$linea_moodle = "$rowprof[0];$rowprof[1];$rowprof[3];$rowprof[2];$rowprof[0]@$dominio;$localidad_del_centro;ES\n";
$todos_moodle.=$linea_moodle;
}

if (!(file_exists("TIC/alumnos_moodle.txt")))
{
$fpprof1=fopen("TIC/alumnos_moodle.txt","w+");
 }
 else
 {
 $fpprof1=fopen("TIC/alumnos_moodle.txt","w+") or die('<br /><div align="center"><div class="alert alert-danger alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCIN:</h5>
No se ha podido escribir en el archivo TIC/profesores.txt. Has concedido permiso de escritura en ese directorio?
</div></div><br />
<div align="center">
  <input type="button" value="Volver atr�s" name="boton" onClick="history.back(2)" class="btn btn-inverse" />
</div>'); 
 }
 $pepito1=fwrite($fpprof1,$todos_moodle);
 fclose ($fpprof1);
 echo '<div align="center"><div class="alert alert-success alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
 Se ha generado un fichero (alumnos_moodle.txt) en el subdirectorio "xml/jefe/TIC/" preparado para el alta masiva de usuarios en cualquier Plataforma Moodle distinta a la de la Red TIC de la Junta de Andaluc�a.
</div></div><br />'; 
 
 ?>

