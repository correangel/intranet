<?php
include("../../../bootstrap.php");
require("../../../pdf/mc_table.php");

function obtener_calificacion_texto($nota) {
    switch ($nota) {
        case 10: 
        case 9: 
            $calificacion = 'SB';
            break;
        case 8: 
        case 7: 
            $calificacion = 'NT';
            break;
        case 6:
            $calificacion = 'BI';
            break;
        case 5:
            $calificacion = 'SU';
            break;
        default:
            $calificacion = 'IN';
            break;
    }

    return $calificacion;
}

acl_acceso($_SESSION['cargo'], array(1));

// Variables globales para el encabezado y pie de pagina
$GLOBALS['CENTRO_NOMBRE'] = $config['centro_denominacion'];
$GLOBALS['CENTRO_DIRECCION'] = $config['centro_direccion'];
$GLOBALS['CENTRO_CODPOSTAL'] = $config['centro_codpostal'];
$GLOBALS['CENTRO_LOCALIDAD'] = $config['centro_localidad'];
$GLOBALS['CENTRO_TELEFONO'] = $config['centro_telefono'];
$GLOBALS['CENTRO_FAX'] = $config['centro_fax'];
$GLOBALS['CENTRO_CORREO'] = $config['centro_email'];
$GLOBALS['CENTRO_PROVINCIA'] = $config['centro_provincia'];

class GranPDF extends PDF_MC_Table {
	function Header() {
		$this->SetTextColor(0, 122, 61);
		$this->Image( '../../../img/encabezado.jpg',25,14,53,'','jpg');
		$this->SetFont('ErasDemiBT','B',10);
		$this->SetY(15);
		$this->Cell(75);
		$this->Cell(80,5,'CONSEJERÍA DE EDUCACIÓN',0,1);
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
    
    function Rotate($angle, $x=-1, $y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
}

$MiPDF = new GranPDF('P', 'mm', 'A4');
$MiPDF->AddFont('NewsGotT','','NewsGotT.php');
$MiPDF->AddFont('NewsGotT','B','NewsGotTb.php');
$MiPDF->AddFont('ErasDemiBT','','ErasDemiBT.php');
$MiPDF->AddFont('ErasDemiBT','B','ErasDemiBT.php');
$MiPDF->AddFont('ErasMDBT','','ErasMDBT.php');
$MiPDF->AddFont('ErasMDBT','I','ErasMDBT.php');

$MiPDF->SetMargins(25, 20, 20);
$MiPDF->SetDisplayMode('fullpage');

// OBTENEMOS TODOS LOS NIVELES DEL CENTRO EDUCATIVO
$niveles = array();
if (acl_permiso($_SESSION['cargo'], array(1))) {
    $result = mysqli_query($db_con, "SELECT `idcurso`, `nomcurso` FROM `cursos` WHERE `nomcurso` ORDER BY `idcurso` ASC");
}
else {
    $result = mysqli_query($db_con, "SELECT `cursos`.`idcurso`, `cursos`.`nomcurso` FROM `cursos` JOIN `unidades` ON `cursos`.`idcurso` = `unidades`.`idcurso` WHERE `unidades`.`nomunidad` = '".$_SESSION['mod_tutoria']['unidad']."' LIMIT 1");
}
while ($row = mysqli_fetch_array($result)) {

    $nivel = array(
        'id'          => $row['idcurso'],
        'nombre'      => $row['nomcurso']
    );

    array_push($niveles, $nivel);
}
mysqli_free_result($result);
unset($nivel);

// Procesamos la variable de selección de curso

if (isset($_POST['curso']) && ! empty($_POST['curso'])) {
    $curso = urldecode($_POST['curso']);

    if (! in_array($curso, array_column($niveles, 'nombre'))) {
        $curso = "Todos los cursos";
    }
} 
else {
    $curso = "Todos los cursos";
}

// OBTENEMOS TODAS LAS UNIDADES DEL CENTRO EDUCATIVO
$unidades = array();
if (acl_permiso($_SESSION['cargo'], array(1)) && $curso != "Todos los cursos") {
    $result = mysqli_query($db_con, "SELECT `unidades`.`idunidad`, `unidades`.`nomunidad` FROM `unidades` JOIN `cursos` ON `unidades`.`idcurso` = `cursos`.`idcurso` WHERE `cursos`.`nomcurso` = '$curso' ORDER BY `unidades`.`nomunidad` ASC");
}
elseif (acl_permiso($_SESSION['cargo'], array(1)) && $curso == "Todos los cursos") {
    $result = mysqli_query($db_con, "SELECT `unidades`.`idunidad`, `unidades`.`nomunidad` FROM `unidades` JOIN `cursos` ON `unidades`.`idcurso` = `cursos`.`idcurso` ORDER BY `cursos`.`idcurso` ASC, `unidades`.`nomunidad` ASC");
}
else {
    $result = mysqli_query($db_con, "SELECT `unidades`.`idunidad`, `unidades`.`nomunidad` FROM `unidades` JOIN `cursos` ON `unidades`.`idcurso` = `cursos`.`idcurso` WHERE `unidades`.`nomunidad` = '".$_SESSION['mod_tutoria']['unidad']."' LIMIT 1");
}
while ($row = mysqli_fetch_array($result)) {

    $unidad = array(
        'id'          => $row['idunidad'],
        'nombre'      => $row['nomunidad']
    );

    array_push($unidades, $unidad);
}
mysqli_free_result($result);
unset($unidad);

// Procesamos la variable de selección de unidad
if (acl_permiso($_SESSION['cargo'], array(2))) {
    $grupo = $_SESSION['mod_tutoria']['unidad'];
}
elseif (isset($_POST['grupo']) && ! empty($_POST['grupo'])) {
    $grupo = urldecode($_POST['grupo']);

    if (! in_array($grupo, array_column($unidades, 'nombre'))) {
        unset($grupo);
    }
}

// OBTENEMOS LOS ALUMNOS DE LOS CURSOS Y UNIDADES SELECCIONADAS
$alumnos = array();
if ($curso == "Todos los cursos") {
    $result = mysqli_query($db_con, "SELECT `curso`, `unidad`, `apellidos`, `nombre`, `claveal`, `claveal1`, `numeroexpediente`, `combasi`, `primerapellidotutor`, `segundoapellidotutor`, `nombretutor`, `sexoprimertutor`, `domicilio`, `codpostal`, `localidad`, `provinciaresidencia`, `sexo` FROM `alma` ORDER BY `curso` ASC, `unidad` ASC, `apellidos` ASC, `nombre` ASC") or die (mysqli_error($db_con));
}
elseif (isset($curso) && ! isset($grupo)) {
    $result = mysqli_query($db_con, "SELECT `curso`, `unidad`, `apellidos`, `nombre`, `claveal`, `claveal1`, `numeroexpediente`, `combasi`, `primerapellidotutor`, `segundoapellidotutor`, `nombretutor`, `sexoprimertutor`, `domicilio`, `codpostal`, `localidad`, `provinciaresidencia`, `sexo` FROM `alma` WHERE `curso` = '$curso' ORDER BY `curso` ASC, `unidad` ASC, `apellidos` ASC, `nombre` ASC");
}
else {
    $result = mysqli_query($db_con, "SELECT `curso`, `unidad`, `apellidos`, `nombre`, `claveal`, `claveal1`, `numeroexpediente`, `combasi`, `primerapellidotutor`, `segundoapellidotutor`, `nombretutor`, `sexoprimertutor`, `domicilio`, `codpostal`, `localidad`, `provinciaresidencia`, `sexo` FROM `alma` WHERE `curso` = '$curso' AND `unidad` = '$grupo' ORDER BY `curso` ASC, `unidad` ASC, `apellidos` ASC, `nombre` ASC");
}

while ($row = mysqli_fetch_array($result)) {
    if ($row['nombretutor'] != "") {
        $tutor_legal = (($row['sexoprimertutor'] == 'H') ? 'Don ' : 'Doña ').trim($row['nombretutor'].' '.$row['primerapellidotutor'].' '.$row['segundoapellidotutor']);
    }
    else {
        $tutor_legal = (($row['sexo'] == 'H') ? 'Don ' : 'Doña ').trim($row['nombre'].' '.$row['apellidos']);
    }

    $alumno = array(
        'curso'       => $row['curso'],
        'unidad'      => $row['unidad'],
        'nombre'      => $row['apellidos'].', '.$row['nombre'],
        'unidad'      => $row['unidad'],
        'claveal'     => $row['claveal'],
        'claveal1'    => $row['claveal1'],
        'numexp'      => $row['numeroexpediente'],
        'combasi'     => rtrim($row['combasi'], ':'),
        'tutorlegal'  => trim($tutor_legal),
        'direccion1'  => $row['domicilio'],
        'direccion2'  => $row['localidad'].' - '.$row['codpostal'].' ('.$row['provinciaresidencia'].')',
    );

    array_push($alumnos, $alumno);
}
mysqli_free_result($result);
unset($alumno);

// OBTENEMOS TODAS LAS EVALUACIONES IMPORTADAS
$array_convocatorias = array('EVI' => 'Evaluación Inicial', '1EV' => '1ª Evaluación', '2EV' => '2ª Evaluación', 'ORD' => 'Evaluación Ordinaria', 'EXT' => 'Evaluación Extraordinaria');
$convocatorias = array();
$result = mysqli_query($db_con, "SELECT COUNT(`notas0`) AS notas0, COUNT(`notas1`) AS notas1, COUNT(`notas2`) AS notas2, COUNT(`notas3`) AS notas3, COUNT(`notas4`) AS notas4 FROM notas");
$row = mysqli_fetch_array($result);
for ($i = 0; $i < 5; $i++) {
    
    if ($row['notas'.$i] > 0) {
        $convocatoria = array(
            'abrev'       => key($array_convocatorias),
            'nombre'      => current($array_convocatorias)
        );

        array_push($convocatorias, $convocatoria);
    }
    next($array_convocatorias);
    
}
mysqli_free_result($result);
unset($convocatoria);

// Procesamos la variable de selección de evaluación
if (isset($_POST['evaluacion']) && ! empty($_POST['evaluacion'])) {
    $evaluacion = urldecode($_POST['evaluacion']);

    switch ($evaluacion) {
        case 'Evaluación Inicial':
            $convocatoria = array(
                'abrev'     => 'EVI',
                'nombre'    => 'Evaluación Inicial'
            );
            break;
        
        case '1ª Evaluación':
            $convocatoria = array(
                'abrev'     => '1EV',
                'nombre'    => '1ª Evaluación'
            );
            break;
        
        case '2ª Evaluación':
            $convocatoria = array(
                'abrev'     => '2EV',
                'nombre'    => '2ª Evaluación'
            );
            break;
        
        case 'Evaluación Ordinaria':
            $convocatoria = array(
                'abrev'     => 'ORD',
                'nombre'    => 'Evaluación Ordinaria'
            );
            break;
        
        case 'Evaluación Extraordinaria':
            $convocatoria = array(
                'abrev'     => 'ORD',
                'nombre'    => 'Evaluación Extraordinaria'
            );
            break;
        
    }
}
else {
    // Obtenemos la última convocatoria importada
    $convocatoria = end($convocatorias);
    $evaluacion = $convocatoria['nombre'];
}

$anio_academico = (substr($config['curso_actual'], 0, 4).'/'.(substr($config['curso_actual'], 0, 4) + 1));

foreach ($alumnos as $alumno) {

    $MiPDF->Addpage();

    $MiPDF->Ln(10);

    $MiPDF->SetFont('NewsGotT', 'BU', 10);
    $MiPDF->Cell(0, 5, 'BOLETÍN DE CALIFICACIONES', 0, 0, 'C', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);

    $curso_alumno = str_replace('(Humanidades y Ciencias Sociales (Lomce))', '(Humanid. y CC.SS.)', $alumno['curso']);

    // INFORMACION DE LA CARTA
    $MiPDF->SetY(45);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'ALUMNO/A:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $alumno['nombre'], 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'NÚMERO EXP:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $alumno['numexp'], 0, 0, 'L', 0);
    $MiPDF->Cell(85, 5, $alumno['tutorlegal'], 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'CURSO:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $curso_alumno, 0, 0, 'L', 0);
    $MiPDF->Cell(85, 5, $alumno['direccion1'], 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'UNIDAD:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $alumno['unidad'], 0, 0, 'L', 0);
    $MiPDF->Cell(85, 5, $alumno['direccion2'], 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'CONVOCATORIA:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $convocatoria['abrev'].' ('.$convocatoria['nombre'].')', 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(28, 5, 'AÑO ACADÉMICO:', 0, 0, 'R', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(58, 5, $anio_academico, 0, 1, 'L', 0);

    $MiPDF->Ln(5);

    // Asignaturas matriculadas
    $combasi = explode(':', $alumno['combasi']);

    // Calificaciones por convocatoria
    $tabla_alineacion = array('L');
    $tabla_anchos = array(80);
    $tabla_encabezado = array('MATERIAS');
    $notas_evi = mysqli_query($db_con, "SELECT `notas0` FROM `notas` WHERE `claveal` = '".$alumno['claveal1']."'");
    $row_notas_evi = mysqli_fetch_array($notas_evi);
    $notas_evi = explode(';', rtrim($row_notas_evi['notas0'], ';'));
    if (count($notas_evi) > 1) {
        $tabla_alineacion = array_merge($tabla_alineacion, array('C'));
        $tabla_anchos = array_merge($tabla_anchos, array(13));
        $tabla_encabezado = array_merge($tabla_encabezado, array('EVI'));
    }

    $notas_1ev = mysqli_query($db_con, "SELECT `notas1` FROM `notas` WHERE `claveal` = '".$alumno['claveal1']."'");
    $row_notas_1ev = mysqli_fetch_array($notas_1ev);
    $notas_1ev = explode(';', rtrim($row_notas_1ev['notas1'], ';'));
    if (count($notas_1ev) > 1 && ($evaluacion == '1ª Evaluación' || $evaluacion == '2ª Evaluación' || $evaluacion == 'Evaluación Ordinaria' || $evaluacion == 'Evaluación Extraordinaria')) {
        $tabla_alineacion = array_merge($tabla_alineacion, array('C'));
        $tabla_anchos = array_merge($tabla_anchos, array(13));
        $tabla_encabezado = array_merge($tabla_encabezado, array('1EV'));
    }
    
    $notas_2ev = mysqli_query($db_con, "SELECT `notas2` FROM `notas` WHERE `claveal` = '".$alumno['claveal1']."'");
    $row_notas_2ev = mysqli_fetch_array($notas_2ev);
    $notas_2ev = explode(';', rtrim($row_notas_2ev['notas2'], ';'));
    if (count($notas_2ev) > 1 && ($evaluacion == '2ª Evaluación' || $evaluacion == 'Evaluación Ordinaria' || $evaluacion == 'Evaluación Extraordinaria')) {
        $tabla_alineacion = array_merge($tabla_alineacion, array('C'));
        $tabla_anchos = array_merge($tabla_anchos, array(13));
        $tabla_encabezado = array_merge($tabla_encabezado, array('2EV'));
    }

    $notas_ord = mysqli_query($db_con, "SELECT `notas3` FROM `notas` WHERE `claveal` = '".$alumno['claveal1']."'");
    $row_notas_ord = mysqli_fetch_array($notas_ord);
    $notas_ord = explode(';', rtrim($row_notas_ord['notas3'], ';'));
    if (count($notas_ord) > 1 && ($evaluacion == 'Evaluación Ordinaria' || $evaluacion == 'Evaluación Extraordinaria')) {
        $tabla_alineacion = array_merge($tabla_alineacion, array('C'));
        $tabla_anchos = array_merge($tabla_anchos, array(13));
        $tabla_encabezado = array_merge($tabla_encabezado, array('ORD'));
    }

    $notas_ext = mysqli_query($db_con, "SELECT `notas4` FROM `notas` WHERE `claveal` = '".$alumno['claveal1']."'");
    $row_notas_ext = mysqli_fetch_array($notas_ext);
    $notas_ext = explode(';', rtrim($row_notas_ext['notas4'], ';'));
    if (count($notas_ext) > 1 && ($evaluacion == 'Evaluación Extraordinaria')) {
        $tabla_alineacion = array_merge($tabla_alineacion, array('C'));
        $tabla_anchos = array_merge($tabla_anchos, array(13));
        $tabla_encabezado = array_merge($tabla_encabezado, array('EXT'));
    }

    // TABLA CON CALIFICACIONES
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(0, 5, 'EVALUACIÓN', 0, 1, 'C', 0);

    $MiPDF->SetAligns($tabla_alineacion);
    $MiPDF->SetWidths($tabla_anchos);
    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->SetFillColor(200, 200, 200);
    $MiPDF->Row($tabla_encabezado, 0, 4.5);
    $MiPDF->SetFont('NewsGotT', '', 10);

    foreach ($combasi as $idasignatura) {
        $result_asignatura = mysqli_query($db_con, "SELECT `nombre`, `curso` FROM `asignaturas` WHERE `codigo` = '$idasignatura' LIMIT 1");
        $row_asignatura = mysqli_fetch_array($result_asignatura);
        $asignatura = $row_asignatura['nombre'];
        $curso_asignatura = $row_asignatura['curso'];
        $tieneCalificacion = 0;

        foreach ($notas_evi as $nota) {
            $exp_nota = explode(':', $nota);
            
            if ($exp_nota[0] == $idasignatura) {
                $result_calificaciones = mysqli_query($db_con, "SELECT `abreviatura` FROM `calificaciones` WHERE `codigo` = '".$exp_nota[1]."' LIMIT 1");
                $row_calificacion = mysqli_fetch_array($result_calificaciones);
                if ($row_calificacion['abreviatura'] != "") {
                    if (is_numeric($row_calificacion['abreviatura'])) {
                        $calificacion0 = obtener_calificacion_texto($row_calificacion['abreviatura']).' | '.$row_calificacion['abreviatura'];
                    }
                    else {
                        $calificacion0 = $row_calificacion['abreviatura'];
                    }
                    $tieneCalificacion = 1;
                }
            }
        }

        foreach ($notas_1ev as $nota) {
            $exp_nota = explode(':', $nota);

            if ($exp_nota[0] == $idasignatura) {
                $result_calificaciones = mysqli_query($db_con, "SELECT `abreviatura` FROM `calificaciones` WHERE `codigo` = '".$exp_nota[1]."' LIMIT 1");
                $row_calificacion = mysqli_fetch_array($result_calificaciones);
                if ($row_calificacion['abreviatura'] != "") {
                    if (is_numeric($row_calificacion['abreviatura'])) {
                        $calificacion1 = obtener_calificacion_texto($row_calificacion['abreviatura']).' | '.$row_calificacion['abreviatura'];
                    }
                    else {
                        $calificacion1 = $row_calificacion['abreviatura'];
                    }
                    $tieneCalificacion = 1;
                }
            } 
        }

        foreach ($notas_2ev as $nota) {
            $exp_nota = explode(':', $nota);

            if ($exp_nota[0] == $idasignatura) {
                $result_calificaciones = mysqli_query($db_con, "SELECT `abreviatura` FROM `calificaciones` WHERE `codigo` = '".$exp_nota[1]."' LIMIT 1");
                $row_calificacion = mysqli_fetch_array($result_calificaciones);
                if ($row_calificacion['abreviatura'] != "") {
                    if (is_numeric($row_calificacion['abreviatura'])) {
                        $calificacion2 = obtener_calificacion_texto($row_calificacion['abreviatura']).' | '.$row_calificacion['abreviatura'];
                    }
                    else {
                        $calificacion2 = $row_calificacion['abreviatura'];
                    }
                    $tieneCalificacion = 1;
                }
            }
        }

        foreach ($notas_ord as $nota) {
            $exp_nota = explode(':', $nota);

            if ($exp_nota[0] == $idasignatura) {
                $result_calificaciones = mysqli_query($db_con, "SELECT `abreviatura` FROM `calificaciones` WHERE `codigo` = '".$exp_nota[1]."' LIMIT 1");
                $row_calificacion = mysqli_fetch_array($result_calificaciones);
                if ($row_calificacion['abreviatura'] != "") {
                    if (is_numeric($row_calificacion['abreviatura'])) {
                        $calificacion3 = obtener_calificacion_texto($row_calificacion['abreviatura']).' | '.$row_calificacion['abreviatura'];
                    }
                    else {
                        $calificacion3 = $row_calificacion['abreviatura'];
                    }
                    $tieneCalificacion = 1;
                }
            }
        }

        foreach ($notas_ext as $nota) {
            $exp_nota = explode(':', $nota);

            if ($exp_nota[0] == $idasignatura) {
                $result_calificaciones = mysqli_query($db_con, "SELECT `abreviatura` FROM `calificaciones` WHERE `codigo` = '".$exp_nota[1]."' LIMIT 1");
                $row_calificacion = mysqli_fetch_array($result_calificaciones);
                if ($row_calificacion['abreviatura'] != "") {
                    if (is_numeric($row_calificacion['abreviatura'])) {
                        $calificacion4 = obtener_calificacion_texto($row_calificacion['abreviatura']).' | '.$row_calificacion['abreviatura'];
                    }
                    else {
                        $calificacion4 = $row_calificacion['abreviatura'];
                    }
                    $tieneCalificacion = 1;
                }
            }
        }

        if ($alumno['curso'] != $curso_asignatura) {
            $curso_asignatura = str_replace('(Humanidades y Ciencias Sociales (Lomce))', '(Humanid. y CC.SS.)', $curso_asignatura);
            $tabla_calificaciones = array($asignatura.' ('.$curso_asignatura.')');
        }
        else {
            $tabla_calificaciones = array($asignatura);
        }
        
        if (count($notas_evi) > 1) $tabla_calificaciones = array_merge($tabla_calificaciones, array($calificacion0));
        if (count($notas_1ev) > 1) $tabla_calificaciones = array_merge($tabla_calificaciones, array($calificacion1));
        if (count($notas_2ev) > 1) $tabla_calificaciones = array_merge($tabla_calificaciones, array($calificacion2));
        if (count($notas_ord) > 1) $tabla_calificaciones = array_merge($tabla_calificaciones, array($calificacion3));
        if (count($notas_ext) > 1) $tabla_calificaciones = array_merge($tabla_calificaciones, array($calificacion4));

        if ($tieneCalificacion) {
            $MiPDF->Row($tabla_calificaciones, 1, 4.5);
        }

        unset($calificacion0);
        unset($calificacion1);
        unset($calificacion2);
        unset($calificacion3);
        unset($calificacion4);
        
    }

    if ($convocatoria['nombre'] != "Evaluación Extraordinaria") {
        $MiPDF->Ln(3);

        switch ($convocatoria['nombre']) {
            case 'Evaluación Inicial':
                $fecha_inicio_faltas = $config['curso_inicio'];
                $fecha_fin_faltas = substr($config['curso_actual'], 0, 4).'-09-31';
                break;

            case '1ª Evaluación':
                
                // Obtenemos inicio festivo Navidad
                $result_festivo = mysqli_query($db_con, "SELECT `fecha` FROM `festivos` WHERE `nombre` LIKE '%Navidad' ORDER BY `fecha` ASC LIMIT 1");
                $row_festivo = mysqli_fetch_array($result_festivo);

                $fecha_inicio_faltas = substr($config['curso_actual'], 0, 4).'-10-01';
                $fecha_fin_faltas = $row_festivo['fecha'];
                break;
            
            case '2ª Evaluación':
                
                // Obtenemos fin festivo Navidad
                $result_festivo = mysqli_query($db_con, "SELECT `fecha` FROM `festivos` WHERE `nombre` LIKE '%Navidad' ORDER BY `fecha` DESC LIMIT 1");
                $row_festivo = mysqli_fetch_array($result_festivo);

                $fecha_inicio_faltas = $row_festivo['fecha'];

                // Obtenemos inicio festivo Semana Santa
                $result_festivo = mysqli_query($db_con, "SELECT `fecha` FROM `festivos` WHERE `nombre` LIKE '%Semana Santa' ORDER BY `fecha` ASC LIMIT 1");
                $row_festivo = mysqli_fetch_array($result_festivo);

                $fecha_fin_faltas = $row_festivo['fecha'];
                break;

            case 'Evaluación Ordinaria':

                // Obtenemos fin festivo Semana Santa
                $result_festivo = mysqli_query($db_con, "SELECT `fecha` FROM `festivos` WHERE `nombre` LIKE '%Semana Santa' ORDER BY `fecha` DESC LIMIT 1");
                $row_festivo = mysqli_fetch_array($result_festivo);

                $fecha_inicio_faltas = $row_festivo['fecha'];
                $fecha_fin_faltas = $config['curso_fin'];
                break;
        }

        $MiPDF->SetFont('NewsGotT', 'B', 10);
        $MiPDF->Cell(0, 5, 'Resumen de faltas de asistencia desde '.(strftime('%d/%m/%Y', strtotime($fecha_inicio_faltas))).' hasta '.(strftime('%d/%m/%Y', strtotime($fecha_fin_faltas))).':', 0, 1, 'L', 0);
        $MiPDF->SetFont('NewsGotT', '', 10);

        $total_dias_justificadas = 0;
        $result_dias_faltas_justificadas = mysqli_query($db_con, "SELECT `fecha`, COUNT(`hora`) AS 'horas' FROM `FALTAS` WHERE `claveal` = '".$alumno['claveal']."' AND `falta` = 'J' GROUP BY `fecha` HAVING `horas` = 6 ORDER BY `fecha` ASC, `hora` ASC");
        $total_dias_justificadas = mysqli_num_rows($result_dias_faltas_justificadas);

        $total_dias_injustificadas = 0;
        $result_dias_faltas_injustificadas = mysqli_query($db_con, "SELECT `fecha`, COUNT(`hora`) AS 'horas' FROM `FALTAS` WHERE `claveal` = '".$alumno['claveal']."' AND `falta` = 'F' GROUP BY `fecha` HAVING `horas` = 6 ORDER BY `fecha` ASC, `hora` ASC");
        $total_dias_injustificadas = mysqli_num_rows($result_dias_faltas_injustificadas);

        $total_justificadas = 0;
        $result_faltas_justificadas = mysqli_query($db_con, "SELECT COUNT(`fecha`) AS 'total_justificadas' FROM `FALTAS` WHERE `claveal` = '".$alumno['claveal']."' AND `fecha` BETWEEN '".$fecha_inicio_faltas."' AND '".$fecha_fin_faltas."' AND `falta` = 'J'");
        $row_faltas_justificadas = mysqli_fetch_array($result_faltas_justificadas);
        $total_justificadas = $row_faltas_justificadas['total_justificadas'];

        $total_injustificadas = 0;
        $result_faltas_injustificadas = mysqli_query($db_con, "SELECT COUNT(`fecha`) AS 'total_injustificadas' FROM `FALTAS` WHERE `claveal` = '".$alumno['claveal']."' AND `fecha` BETWEEN '".$fecha_inicio_faltas."' AND '".$fecha_fin_faltas."' AND `falta` = 'F'");
        $row_faltas_injustificadas = mysqli_fetch_array($result_faltas_injustificadas);
        $total_injustificadas = $row_faltas_injustificadas['total_injustificadas'];

        $total_retrasos = 0;
        $result_faltas_retrasos = mysqli_query($db_con, "SELECT COUNT(`fecha`) AS 'total_retrasos' FROM `FALTAS` WHERE `claveal` = '".$alumno['claveal']."' AND `fecha` BETWEEN '".$fecha_inicio_faltas."' AND '".$fecha_fin_faltas."' AND `falta` = 'F'");
        $row_faltas_retrasos = mysqli_fetch_array($result_faltas_retrasos);
        $total_retrasos = $row_faltas_retrasos['total_retrasos'];

        $MiPDF->Rect($MiPDF->GetX()+80, $MiPDF->GetY()+4.5, 25, 4.5, 'F');
        $MiPDF->SetAligns(array('L', 'C', 'C', 'C'));
        $MiPDF->SetWidths(array(30, 25, 25, 25));
        $MiPDF->Row(array('', 'Justificadas', 'Injustificadas', 'Retrasos'), 1, 4.5);
        $MiPDF->Row(array('Día/s Completo/s',  $total_dias_justificadas, $total_dias_injustificadas, ''), 1, 4.5);
        $MiPDF->Row(array('Tramo/s Horario/s', $total_justificadas, $total_injustificadas, $total_retrasos), 1, 4.5);

        $MiPDF->Ln(3);
    }
    else {
        $MiPDF->Ln(5);
    }

    $MiPDF->SetFont('NewsGotT', 'B', 10);
    $MiPDF->Cell(0, 5, 'Observaciones:', 0, 1, 'L', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Ln(10);

    // OBTENEMOS EL/LA TUTOR/A DE LA UNIDAD
    $result_tutor = mysqli_query($db_con, "SELECT `tutor` FROM `FTUTORES` WHERE `unidad` = '".$alumno['unidad']."' LIMIT 1");
    $row_tutor = mysqli_fetch_array($result_tutor);
    $tutor = nomprofesor($row_tutor['tutor']);

    // OBTENEMOS LA HORA DE TUTORÍA DE ATENCIÓN A PADRES
    $horario_tutoria = "";
    $result_horario_tutoria = mysqli_query($db_con, "SELECT `horw`.`dia`, `tramos`.`hora_inicio`, `tramos`.`hora_fin` FROM `horw` JOIN `tramos` ON `horw`.`hora` = `tramos`.`hora` WHERE `horw`.`c_asig` = '117' AND `horw`.`prof` = '".$row_tutor['tutor']."' LIMIT 1");
    
    if (mysqli_num_rows($result_horario_tutoria)) {
        $row_horario_tutoria = mysqli_fetch_array($result_horario_tutoria);
        switch ($row_horario_tutoria['dia']) {
            case 1:
                $dia = 'Lunes';
                break;
            case 2:
                $dia = 'Martes';
                break;
            case 3:
                $dia = 'Miércoles';
                break;
            case 4:
                $dia = 'Jueves';
                break;
            case 5:
                $dia = 'Viernes';
                break;
            
            default:
                $dia = '';
                break;
        }
        $horario_tutoria = $dia.', '.substr($row_horario_tutoria['hora_inicio'], 0, 5).' h - '.substr($row_horario_tutoria['hora_fin'], 0, 5).' h.';
    } 

    //FIRMAS
    $MiPDF->Cell(90, 5, 'Sello del Centro', 0, 0, 'L', 0);
    $MiPDF->Cell(55, 5, 'Les saluda cordialmente,', 0, 1, 'L', 0);
    $MiPDF->Cell(55, 15, '', 0, 0, 'C', 0);
    $MiPDF->Cell(55, 15, '', 0, 1, 'C', 0);
    $MiPDF->SetFont('NewsGotT', '', 10);
    $MiPDF->Cell(90, 5, 'Firma del Padre, Madre, o Tutor/a', 0, 0, 'L', 0);
    $MiPDF->Cell(55, 5, 'Tutor/a: '.$tutor, 0, 1, 'L', 0);
    if ($horario_tutoria != "") {
        $MiPDF->Cell(90, 5, '', 0, 0, 'L', 0);
        $MiPDF->Cell(55, 5, 'Horario de tutoría: '.$horario_tutoria, 0, 1, 'L', 0);
    }

    $MiPDF->SetFont('NewsGotT', '', 8);
    $MiPDF->RotatedText(10, 100, 'Ref.Doc: BolCalAluInfInd', 90);
    $MiPDF->RotatedText(10, 150, 'Cód.Centro: '.$config['centro_codigo'], 90);
    $MiPDF->RotatedText(10, 220, 'Fecha Generación: '.date('d/m/Y H:i:s'), 90);
}

$MiPDF->Output('I', 'Calificaciones', true);