<?
session_start ();
include ("../../../config.php");
setlocale('LC_TIME', 'es_ES');
// COMPROBAMOS LA SESION
if ($_SESSION['autentificado'] != 1) {
	$_SESSION = array();
	session_destroy();
	header('Location:'.'http://'.$dominio.'/intranet/salir.php');	
	exit();
}
registraPagina ( $_SERVER ['REQUEST_URI'], $db_host, $db_user, $db_pass, $db );
$tutor = $_SESSION ['profi'];
include_once ("../../../funciones.php");

if(!($_POST['id'])){$id = $_GET['id'];}else{$id = $_POST['id'];}
if(!($_POST['claveal'])){$claveal = $_GET['claveal'];}else{$claveal = $_POST['claveal'];}
if (isset($_POST['expulsion'])) { $expulsion = $_POST['expulsion']; }
if (isset($_POST['fechainicio'])) { $fechainicio = $_POST['fechainicio']; }
if (isset($_POST['fechafin'])) { $fechafin = $_POST['fechafin']; }

// Consulta  en curso.
$fechaesp = explode ( "-", $fechainicio );
$inicio_aula = "$fechaesp[2]-$fechaesp[1]-$fechaesp[0]";
$fechaesp1 = explode ( "-", $fechafin );
$fin_aula = "$fechaesp1[2]-$fechaesp1[1]-$fechaesp1[0]";
$actualizar = "UPDATE  Fechoria SET  recibido =  '1' WHERE  Fechoria.id = '$id'";
// echo $actualizar;
mysqli_query($db_con, $actualizar );
$result = mysqli_query($db_con, "select FALUMNOS.apellidos, FALUMNOS.nombre, FALUMNOS.unidad, Fechoria.fecha, Fechoria.notas, Fechoria.asunto, Fechoria.informa, 
  Fechoria.grave, Fechoria.medida, listafechorias.medidas2, Fechoria.expulsion, Fechoria.tutoria, Fechoria.claveal, alma.padre, alma.domicilio, alma.localidad, alma.codpostal, alma.provinciaresidencia,  alma.telefono, alma.telefonourgencia, Fechoria.id from Fechoria, FALUMNOS, alma, listafechorias where Fechoria.claveal = alma.claveal and Fechoria.claveal = FALUMNOS.claveal and listafechorias.fechoria = Fechoria.asunto  and Fechoria.id = '$id' order by Fechoria.fecha DESC" ) or die (mysqli_error($db_con));

if ($row = mysqli_fetch_array ( $result )) {
	$apellidos = $row [0];
	$nombre = $row [1];
	$unidad = $row [2];
	$fecha = $row [3];
	$notas = $row [4];
	$asunto = $row [5];
	$informa = $row [6];
	$grave = $row [7];
	$medida = $row [8];
	$medidas2 = $row [9];
	$expulsion = $row [10];
	$tutoria = $row [11];
	$claveal = $row [12];
	$padre = $row [13];
	$direccion = $row [14];
	$localidad = $row [15];
	$codpostal = $row [16];
	$provincia = $row [17];
	$tfno = $row [18];
	$tfno_u = $row [19];
}
$fechaesp = explode ( "/", $inicio_aula );
$hoy = strftime("%d.%m.%Y", strtotime($fecha));
$inicio1 = formatea_fecha ( $inicio_aula );
$fin1 = formatea_fecha ( $fin_aula );
$tutor = "Jefatura de Estudios";

require("../../../pdf/fpdf.php");

// Variables globales para el encabezado y pie de pagina
$GLOBALS['CENTRO_NOMBRE'] = $nombre_del_centro;
$GLOBALS['CENTRO_DIRECCION'] = $direccion_del_centro;
$GLOBALS['CENTRO_CODPOSTAL'] = $codigo_postal_del_centro;
$GLOBALS['CENTRO_LOCALIDAD'] = $localidad_del_centro;
$GLOBALS['CENTRO_TELEFONO'] = $telefono_del_centro;
$GLOBALS['CENTRO_FAX'] = $fax_del_centro;
$GLOBALS['CENTRO_CORREO'] = $email_del_centro;


if(substr($codigo_postal_del_centro,0,2)=="04") $GLOBALS['CENTRO_PROVINCIA'] = 'Almer�a';
if(substr($codigo_postal_del_centro,0,2)=="11") $GLOBALS['CENTRO_PROVINCIA'] = 'C�diz';
if(substr($codigo_postal_del_centro,0,2)=="14") $GLOBALS['CENTRO_PROVINCIA'] = 'C�rdoba';
if(substr($codigo_postal_del_centro,0,2)=="18") $GLOBALS['CENTRO_PROVINCIA'] = 'Granada';
if(substr($codigo_postal_del_centro,0,2)=="21") $GLOBALS['CENTRO_PROVINCIA'] = 'Huelva';
if(substr($codigo_postal_del_centro,0,2)=="23") $GLOBALS['CENTRO_PROVINCIA'] = 'Ja�n';
if(substr($codigo_postal_del_centro,0,2)=="29") $GLOBALS['CENTRO_PROVINCIA'] = 'M�laga';
if(substr($codigo_postal_del_centro,0,2)=="41") $GLOBALS['CENTRO_PROVINCIA'] = 'Sevilla';

# creamos la clase extendida de fpdf.php 
class GranPDF extends FPDF {
	function Header() {
		$this->SetTextColor(0, 122, 61);
		$this->Image( '../../../img/encabezado.jpg',25,14,53,'','jpg');
		$this->SetFont('ErasDemiBT','B',10);
		$this->SetY(15);
		$this->Cell(75);
		$this->Cell(80,5,'CONSEJER�A DE EDUCACI�N, CULTURA Y DEPORTE',0,1);
		$this->SetFont('ErasMDBT','I',10);
		$this->Cell(75);
		$this->Cell(80,5,$GLOBALS['CENTRO_NOMBRE'],0,1);
		$this->SetTextColor(255, 255, 255);
	}
	function Footer() {
		$this->SetTextColor(0, 122, 61);
		$this->Image( '../../../img/pie.jpg', 0, 245, 25, '', 'jpg' );
		$this->SetY(275);
		$this->SetFont('ErasMDBT','',8);
		$this->Cell(75);
		$this->Cell(80,4,$GLOBALS['CENTRO_DIRECCION'].'. '.$GLOBALS['CENTRO_CODPOSTAL'].', '.$GLOBALS['CENTRO_LOCALIDAD'].' ('.$GLOBALS['CENTRO_PROVINCIA'] .')',0,1);
		$this->Cell(75);
		$this->Cell(80,4,'Telf: '.$GLOBALS['CENTRO_TELEFONO'].'   Fax: '.$GLOBALS['CENTRO_FAX'],0,1);
		$this->Cell(75);
		$this->Cell(80,4,'Correo-e: '.$GLOBALS['CENTRO_CORREO'],0,1);
		$this->SetTextColor(255, 255, 255);
	}
}


# creamos el nuevo objeto partiendo de la clase ampliada
$A4="A4";
$MiPDF = new GranPDF ( 'P', 'mm', $A4 );
$MiPDF->AddFont('NewsGotT','','NewsGotT.php');
$MiPDF->AddFont('NewsGotT','B','NewsGotTb.php');
$MiPDF->AddFont('ErasDemiBT','','ErasDemiBT.php');
$MiPDF->AddFont('ErasDemiBT','B','ErasDemiBT.php');
$MiPDF->AddFont('ErasMDBT','','ErasMDBT.php');
$MiPDF->AddFont('ErasMDBT','I','ErasMDBT.php');

$MiPDF->SetMargins (25, 20, 20);
$MiPDF->SetDisplayMode ( 'fullpage' );

$titulo = "Comunicaci�n de expulsi�n al Aula de Convivencia";

$cuerpo = "Muy Srs. nuestros:

Pongo en su conocimiento que con fecha ".strftime("%e de %B de %Y", strtotime($fecha))." a su hijo/a $nombre $apellidos, alumno/a del grupo $unidad, se le ha impuesto la correcci�n de suspensi�n del derecho de asistencia a clase desde el d�a $inicio1 hasta el d�a $fin1, ambos inclusive, como consecuencia de su comportamiento en el Centro. Deber� permanecer en el Aula de Convivencia durante el tiempo de expulsi�n realizando las tareas encomendadas. En caso de que no las realice, se tomar�n nuevas medidas disciplinarias.

Asimismo, le comunico que, seg�n contempla el Plan de Convivencia del Centro, regulado por el Decreto 327/2010 de 13 de Julio por el que se aprueba el Reglamento Org�nico de los Institutos de Educaci�n Secundaria, de reincidir su hijo/a en este tipo de conductas contrarias a las normas de convivencia del Centro podr�a impon�rsele otra medida de correcci�n que podr�a llegar a ser la suspensi�n del derecho de asistencia al Centro.

En $localidad_del_centro, a ".strftime("%e de %B de %Y", strtotime($fecha)).".";


	# insertamos la primera pagina del documento
	$MiPDF->Addpage ();
	
	// INFORMACION DE LA CARTA
	$MiPDF->SetY(45);
	$MiPDF->SetFont ( 'NewsGotT', '', 12 );
	$MiPDF->Cell(75, 5, 'Fecha:  '.$hoy, 0, 0, 'L', 0 );
	$MiPDF->Cell(75, 5, $padre, 0, 1, 'L', 0 );
	$MiPDF->Cell(75, 12, 'Ref.:     Fec/'.$row['id'], 0, 0, 'L', 0 );
	$MiPDF->Cell(75, 5, $direccion, 0, 1, 'L', 0 );
	$MiPDF->Cell(75, 0, '', 0, 0, 'L', 0 );
	$MiPDF->Cell(75, 5, $codpostal.' '.mb_strtoupper($provincia, 'iso-8859-1'), 0, 1, 'L', 0 );
	$MiPDF->Cell(0, 12, 'Asunto: '.$titulo, 0, 1, 'L', 0 );
	$MiPDF->Ln(10);
	
	// CUERPO DE LA CARTA
	$MiPDF->SetFont('NewsGotT', 'B', 12);
	$MiPDF->Multicell(0, 5, mb_strtoupper($titulo, 'iso-8859-1'), 0, 'C', 0 );
	$MiPDF->Ln(5);

	$MiPDF->SetFont('NewsGotT', '', 12);
	$MiPDF->Multicell(0, 5, $cuerpo, 0, 'L', 0 );
	$MiPDF->Ln(10);
	
	//FIRMAS
	$MiPDF->Cell (90, 5, 'Representante legal', 0, 0, 'C', 0 );
	$MiPDF->Cell (55, 5, 'Director/a del centro', 0, 1, 'C', 0 );
	$MiPDF->Cell (55, 20, '', 0, 0, 'C', 0 );
	$MiPDF->Cell (55, 20, '', 0, 1, 'C', 0 );
	$MiPDF->SetFont('NewsGotT', '', 10);
	$MiPDF->Cell (90, 5, 'Fdo. '.$padre, 0, 0, 'C', 0 );
	$MiPDF->Cell (55, 5, 'Fdo. '.mb_convert_case($director_del_centro, MB_CASE_TITLE, "iso-8859-1"), 0, 1, 'C', 0 );
	
	// RECIBI
	$txt_recibi = "D./D�a. $nombre $apellidos, alumno/a del grupo $nivel-$grupo, he recibido la $titulo con referencia Fec/".$row['id']." registrado el ".strftime("%e de %B de %Y", strtotime($fecha)).".";
	
	$MiPDF->Ln(8);
	$MiPDF->Line(25, $MiPDF->GetY(), 190, $MiPDF->GetY());
	$MiPDF->Ln(5);
	
	$MiPDF->SetFont('NewsGotT', 'B', 12);
	$MiPDF->Multicell(0, 5, 'RECIB�', 0, 'C', 0 );
	$MiPDF->Ln(5);
	
	$MiPDF->SetFont('NewsGotT', '', 12);
	$MiPDF->Multicell(0, 5, $txt_recibi, 0, 'L', 0 );
	$MiPDF->Ln(15);
	$MiPDF->Cell (55, 25, '', 0, 0, 'L', 0 );
	$MiPDF->Cell (55, 10, 'Fdo. '.$nombre.' '.$apellidos, 0, 0, 'L', 0 );
	
  
$result1 = mysqli_query($db_con, "select distinct Fechoria.fecha, Fechoria.asunto, Fechoria.informa, Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and FALUMNOS.claveal = $claveal and Fechoria.fecha >= '".$inicio_curso."' order by Fechoria.fecha DESC, FALUMNOS.unidad, FALUMNOS.apellidos") or die (mysqli_error($db_con));
$num = mysqli_num_rows($result1);

$tit_fech = "PROBLEMAS DE CONVIVENCIA DEL ALUMNO EN EL CURSO ACTUAL";
$MiPDF->Addpage ();
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	$MiPDF->SetTextColor ( 0, 0, 0 );
	$MiPDF->Ln (15);
	$MiPDF->SetFont ( 'NewsGotT', 'B', 12);
	$MiPDF->Multicell ( 0, 4, $tit_fech, 0, 'L', 0 );
	$MiPDF->Ln ( 3 );
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	
$result = mysqli_query($db_con, "select distinct Fechoria.fecha, Fechoria.asunto, Fechoria.informa, Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and FALUMNOS.claveal = $claveal and Fechoria.fecha >= '".$inicio_curso."' order by Fechoria.fecha DESC, FALUMNOS.unidad, FALUMNOS.apellidos limit 0, 24");

 // print "$AUXSQL";
  while($row = mysqli_fetch_array($result))
                {
$dato = "$row[0]   $row[1]";
$MiPDF->Ln ( 4 );
$MiPDF->Multicell ( 0, 4, $dato, 0, 'J', 0 );              
                }

                
if ($num > '24' and $num < '49') 
{		
$tit_fech = "PROBLEMAS DE CONVIVENCIA DEL ALUMNO EN EL CURSO ACTUAL";
$MiPDF->Addpage ();
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	$MiPDF->SetTextColor ( 0, 0, 0 );
	$MiPDF->Ln (15);
	$MiPDF->SetFont ( 'NewsGotT', 'B', 12);
	$MiPDF->Multicell ( 0, 4, $tit_fech, 0, 'L', 0 );
	$MiPDF->Ln ( 3 );
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	
$result = mysqli_query($db_con, "select distinct Fechoria.fecha, Fechoria.asunto, Fechoria.informa, Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and FALUMNOS.claveal = $claveal and Fechoria.fecha >= '".$inicio_curso."' order by Fechoria.fecha DESC, FALUMNOS.nivel, FALUMNOS.grupo, FALUMNOS.apellidos limit 25, 24");
 // print "$AUXSQL";
  while($row = mysqli_fetch_array($result))
                {
$pr = explode(", ",$row[2]);
$dato = "$row[0]   $row[1]";
$MiPDF->Ln ( 4 );
$MiPDF->Multicell ( 0, 4, $dato, 0, 'J', 0 );             
                }
}


if ($num > '48' and $num < '73') 
{		
$tit_fech = "PROBLEMAS DE CONVIVENCIA DEL ALUMNO EN EL CURSO ACTUAL";
$MiPDF->Addpage ();
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	$MiPDF->SetTextColor ( 0, 0, 0 );
	$MiPDF->Ln (15);
	$MiPDF->SetFont ( 'NewsGotT', 'B', 12);
	$MiPDF->Multicell ( 0, 4, $tit_fech, 0, 'L', 0 );
	$MiPDF->Ln ( 3 );
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	
$result = mysqli_query($db_con, "select distinct Fechoria.fecha, Fechoria.asunto, Fechoria.informa, Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and FALUMNOS.claveal = $claveal and Fechoria.fecha >= '".$inicio_curso."' order by Fechoria.fecha DESC, FALUMNOS.nivel, FALUMNOS.grupo, FALUMNOS.apellidos limit 50,24");
 // print "$AUXSQL";
  while($row = mysqli_fetch_array($result))
                {
$pr = explode(", ",$row[2]);
$dato = "$row[0]   $row[1]";
$MiPDF->Ln ( 4 );
$MiPDF->Multicell ( 0, 4, $dato, 0, 'J', 0 );              
                }
}


if ($num > '74' and $num < '24') 
{		
$tit_fech = "PROBLEMAS DE CONVIVENCIA DEL ALUMNO EN EL CURSO ACTUAL";
$MiPDF->Addpage ();
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	$MiPDF->SetTextColor ( 0, 0, 0 );
	$MiPDF->Ln (15);
	$MiPDF->SetFont ( 'NewsGotT', 'B', 12);
	$MiPDF->Multicell ( 0, 4, $tit_fech, 0, 'L', 0 );
	$MiPDF->Ln ( 3 );
	$MiPDF->SetFont ( 'NewsGotT', '', 12);
	
$result = mysqli_query($db_con, "select distinct Fechoria.fecha, Fechoria.asunto, Fechoria.informa, Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and FALUMNOS.claveal = $claveal and Fechoria.fecha >= '".$inicio_curso."' order by Fechoria.fecha DESC, FALUMNOS.nivel, FALUMNOS.grupo, FALUMNOS.apellidos limit 75,24");
 // print "$AUXSQL";
  while($row = mysqli_fetch_array($result))
                {
$pr = explode(", ",$row[2]);
$dato = "$row[0]   $row[1]";
$MiPDF->Ln ( 4 );
$MiPDF->Multicell ( 0, 4, $dato, 0, 'J', 0 );              
                }
}
   
$MiPDF->Output ();

?>
