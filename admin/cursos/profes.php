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


if (isset($_POST['profeso'])) {$profeso = $_POST['profeso'];} elseif (isset($_GET['profeso'])) {$profeso = $_GET['profeso'];} else{$profeso="";}

$profe = explode(", ",$profeso);

include("../../menu.php");
?>

	<div class="container">
		
		<!-- TITULO DE LA PAGINA -->
		<div class="page-header">
			<h2><?php echo $profe[1].' '.$profe[0]; ?> <small>Consulta de horario</small></h2>
		</div>
		
		<!-- SCAFFOLDING -->
		<div class="row">
		
			<div class="col-sm-12">
				
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Lunes</th>
								<th>Martes</th>
								<th>Mi�rcoles</th>
								<th>Jueves</th>
								<th>Viernes</th>
							</tr>
						</thead>
						<tbody>
						<?php $dia = ""; ?>
						<?php $horas = array(1 => "1�", 2 => "2�", 3 => "3�", 4 => "4�", 5 => "5�", 6 => "6�" ); ?>
						<?php foreach($horas as $hora => $desc): ?>
							<tr>
								<th><?php echo $desc; ?></th>
								<?php for($i = 1; $i < 6; $i++): ?>
								<?php $result = mysql_query("SELECT DISTINCT a_asig, asig, a_grupo, a_aula, n_aula FROM horw WHERE prof='$profeso' AND dia='$i' AND hora='$hora'"); ?>
								<td width="20%">
						 			<?php while($row = mysql_fetch_array($result)): ?>
						 			<abbr data-bs="tooltip" title="<?php echo $row['asig']; ?>"><?php echo $row['a_asig']; ?></abbr><br>
						 			<?php echo (!empty($row['n_aula']) && $row['n_aula'] != 'Sin asignar o sin aula' && $row['n_aula'] != 'NULL') ? '<abbr class="pull-right text-danger" data-bs="tooltip" title="'.$row['n_aula'].'">'.$row['a_aula'].'</abbr>' : ''; ?>
						 			<?php echo (!empty($row['a_grupo'])) ? '<span class="text-warning">'.$row['a_grupo'].'</span>' : ''; ?><br>
						 			<?php endwhile; ?>
						 			<?php mysql_free_result($result); ?>
						 		</td>
						 		<?php endfor; ?>
						 	</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				
				<div class="hidden-print">
					<a class="btn btn-primary" href="#" onclick="javascript:print();">Imprimir</a>
					<a class="btn btn-default" href="chorarios.php">Volver</a>
				</div>
				
			</div><!-- /.col-sm-12 -->
			
		</div><!-- /.row -->
		
	</div><!-- /.container -->

<?php include("../../pie.php"); ?>

</body>
</html>
