<?php
// COMPROBAMOS LA VERSI�N DE PHP
if (version_compare(phpversion(), '5.3.0', '<')) die ("<h1>Versi�n de PHP incompatible</h1>\n<p>Necesita PHP 5.3.0 o superior para poder utilizar esta aplicaci�n.</p>");

session_start();

include("config.php");
include_once("config/version.php");

// Comienzo de sesi�n
$_SESSION['autentificado'] = 0;

if (! isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;

// DESTRUIMOS LAS VARIABLES DE SESI�N
if (isset($_SESSION['profi'])) {
	$_SESSION = array();
	session_destroy();
}

// Entramos
if (isset($_POST['submit']) and ! ($_POST['idea'] == "" or $_POST['clave'] == "")) {
	$clave0 = $_POST['clave'];
	$clave = sha1 ( $_POST['clave'] );
	$pass0 = mysqli_query($db_con, "SELECT c_profes.pass, c_profes.profesor , departamentos.dni, c_profes.estado FROM c_profes, departamentos where c_profes.profesor = departamentos.nombre and c_profes.idea = '".$_POST['idea']."'" );
	
	$usuarioExiste = mysqli_num_rows($pass0);
	
	$pass1 = mysqli_fetch_array ( $pass0 );
	$codigo = $pass1 [0];
	$dni = $pass1 [2];
	$bloqueado = $pass1 [3];
	
	if (! $bloqueado) {
	
		// Si le Profesor entra por primera vez... (DNI es igual a Contrase�a)
		if ($dni == strtoupper ( $clave0 ) and (strlen ( $codigo ) < '12') and ! (empty ( $dni )) and ! (empty ( $codigo ))) {
			$_SESSION['autentificado'] = 1;
			$_SESSION['cambiar_clave'] = 1;	
			$_SESSION['profi'] = $pass1 [1];
			$profe = $_SESSION['profi'];
			
			// Departamento al que pertenece
			$dep0 = mysqli_query($db_con, "select departamento from departamentos where nombre = '$profe'" );
			
			$dep1 = mysqli_fetch_array ( $dep0 );
			$_SESSION['depto'] = $dep1 [0];
			// Registramos la entrada en la Intranet
			mysqli_query($db_con, "insert into reg_intranet (profesor, fecha,ip) values ('$profe',now(),'" . $_SERVER ['REMOTE_ADDR'] . "')" );
			$id_reg = mysqli_query($db_con, "select id from reg_intranet where profesor = '$profe' order by id desc limit 1" );
			$id_reg0 = mysqli_fetch_array ( $id_reg );
			$_SESSION['id_pag'] = $id_reg0 [0];
			
			include_once('actualizar.php');
			unset($_SESSION['intentos']);
			
			if (isset($mantenimiento) && $mantenimiento) {
				header("location:mantenimiento.php");
			}
			else {
				header("location:clave.php?tour=1");
			}
			
			exit();
		}
		
		// Si hay usuario y pertenece a alguien del Centro, comprobamos la contrase�a.
		if ($codigo == $clave) {
			$_SESSION['pass'] = $codigo;
			$pr0 = mysqli_query($db_con, "SELECT profesor FROM c_profes where idea = '".$_POST['idea']."'" );
			$pr1 = mysqli_fetch_array ( $pr0 );
			$_SESSION['profi'] = $pr1 [0];
			$profe = $_SESSION['profi'];
			// Comprobamos si da clase a alg�n grupo
			$cur0 = mysqli_query($db_con, "SELECT distinct nivel FROM profesores where profesor = '$profe'" );
			$cur1 = mysqli_num_rows ( $cur0 );
			$_SESSION['n_cursos'] = $cur1;
			// Departamento al que pertenece
			$dep0 = mysqli_query($db_con, "select departamento, cargo from departamentos where nombre = '$profe'" );
			$dep1 = mysqli_fetch_array ( $dep0 );
			$_SESSION['depto'] = $dep1 [0];
	
			// Registramos la entrada en la Intranet
			mysqli_query($db_con, "insert into reg_intranet (profesor, fecha,ip) values ('$profe',now(),'" . $_SERVER ['REMOTE_ADDR'] . "')" );
			$id_reg = mysqli_query($db_con, "select id from reg_intranet where profesor = '$profe' order by id desc limit 1" );
			$id_reg0 = mysqli_fetch_array ( $id_reg );
			$_SESSION['id_pag'] = $id_reg0 [0];
			
			include_once('actualizar.php');
			// Comprobamos si el usuario es Admin y entra por primera vez
			if ($profe=="admin" and $clave == sha1("12345678")) {
				$_SESSION['autentificado'] = 1;
				$_SESSION['cambiar_clave'] = 1;	
				unset($_SESSION['intentos']);		
				header("location:clave.php?tour=1");
			}
			else{
				//Abrimos la p�gina principal
				$_SESSION['autentificado'] = 1;
				unset($_SESSION['intentos']);
					
				include_once('actualizar.php');
				
				if (isset($mantenimiento) && $mantenimiento && (stristr($dep1[1],'1') == false)) {
					header("location:mantenimiento.php");
				}
				else {
					header("location:index.php");
				}
			}
			exit();
		}
		// La contrase�a no es correcta
		else {
			
			if ($_SESSION['intentos'] > 4) {
				mysqli_query($db_con, "UPDATE c_profes SET estado=1 WHERE idea='".$_POST['idea']."' LIMIT 1");
				
				$msg_error = "La cuenta de usuario ha sido bloqueada";
				unset($_SESSION['intentos']);
			}
			else {
				$msg_error = "Nombre de usuario y/o contrase�a incorrectos";
				
				if ($usuarioExiste) {
					$_SESSION['intentos']++;
				}
				else {
					unset($_SESSION['intentos']);
				}
			}
		}
	}
	else {
		$msg_error = "La cuenta de usuario est� bloqueada";
	}
}
?>
<!DOCTYPE html>  
<html lang="es">  
  <head>  
    <meta charset="iso-8859-1">  
    <title>Intranet &middot; <? echo $nombre_del_centro; ?></title>  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <meta name="description" content="Intranet del <? echo $nombre_del_centro; ?>">  
    <meta name="author" content="IESMonterroso (https://github.com/IESMonterroso/intranet/)">
      
    <link href="http://<? echo $dominio;?>/intranet/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://<? echo $dominio;?>/intranet/css/font-awesome.min.css" rel="stylesheet">
    <link href="http://<? echo $dominio;?>/intranet/css/otros.css" rel="stylesheet">            
</head>

<body id="login">

	<div id="wrap">
	
		<div class="container">
		        
		  <div class="text-center">
		    <h1><?php echo $nombre_del_centro; ?></h1>
		    <h4>Inicia sesi�n para acceder</h4>
		  </div>
		  
		  <form id="form-signin" class="form-signin well" method="POST" autocomplete="on">
		      <div class="text-center text-muted form-signin-heading">
		        <span class="fa-stack fa-4x">
		          <i class="fa fa-circle fa-stack-2x"></i>
		          <i class="fa fa-user fa-stack-1x fa-inverse"></i>
		        </span>
		      </div>
		      
		      <div id="form-group" class="form-group">
		        <input type="text" class="form-control" id="idea" name="idea" placeholder="Usuario IdEA" required autofocus>
		        <input type="password" class="form-control" id="clave" name="clave" placeholder="Contrase�a" required>
		        
		        <?php if($msg_error): ?>
		            <label class="control-label text-danger"><?php echo $msg_error; ?></label>
		        <?php endif; ?>
		      </div>
		      
		      
		      
		      <button class="btn btn-lg btn-primary btn-block" type="submit" name="submit">Iniciar sesi�n</button>
		      
		      <div class="form-signin-footer">
		        
		      </div>
		  </form>
		
		</div><!-- /.container -->
	
	</div><!-- /#wrap -->
	
	<footer class="hidden-print">
		<div class="container-fluid" role="footer">
			<hr>
			
			<p class="text-center">
				<small class="text-muted">Versi�n <?php echo INTRANET_VERSION; ?> - Copyright &copy; <?php echo date('Y'); ?> IESMonterroso</small><br>
				<small class="text-muted">Este programa es software libre, liberado bajo la GNU General Public License.</small>
			</p>
			<p class="text-center">
				<small>
					<a href="http://<?php echo $dominio; ?>/intranet/LICENSE.md" target="_blank">Licencia de uso</a>
					&nbsp;&nbsp;&nbsp;&middot;&nbsp;&nbsp;&nbsp;
					<a href="https://github.com/IESMonterroso/intranet" target="_blank">Github</a>
				</small>
			</p>
		</div>
	</footer>
	
  <script src="http://<? echo $dominio;?>/intranet/js/jquery-1.11.1.min.js"></script>  
  <script src="http://<? echo $dominio;?>/intranet/js/bootstrap.min.js"></script>
  
  <?php if($msg_error): ?>
      <script>$("#form-group").addClass( "has-error" );</script>
  <?php endif; ?>
  <script>
  $(function(){
        // Deshabilitamos el bot�n
        $("button[type=submit]").attr("disabled", "disabled");
   
        // Cuando se presione una tecla en un input del formulario
        // realizamos la validaci�n
        $('input').keyup(function(){
              // Validamos el formulario
              var validated = true;
              if($('#idea').val().length < 5) validated = false;
              if($('#clave').val().length < 8) validated = false;
   
              // Si el formulario es v�lido habilitamos el bot�n, en otro caso
              // lo volvemos a deshabilitar
              if(validated) $("button[type=submit]").removeAttr("disabled");
              else $("button[type=submit]").attr("disabled", "disabled");
                                          
        });
        
        $('input:first').trigger('keyup');
  })
  </script>
  
</body>
</html>
