<?
if (isset($_POST['submit1']))
{
include("fechorias.php");
exit();
}
else
{
session_start();
include("../../config.php");
if($_SESSION['autentificado']!='1')
{
session_destroy();
header("location:http://$dominio/intranet/salir.php");	
exit;
}
registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);

?>
<?php
include("../../menu.php");
include("menu.php");

if(isset($_POST['nivel'])){$nivel = $_POST['nivel'];}else{ $nivel=""; }
if(isset($_POST['grupo'])){$grupo = $_POST['grupo'];}else{ $grupo=""; }
if(isset($_POST['c_escolar'])){$c_escolar = $_POST['c_escolar'];}else{ $c_escolar=""; }
if(isset($_POST['APELLIDOS'])){$APELLIDOS = $_POST['APELLIDOS'];}else{ $APELLIDOS=""; }
if(isset($_POST['NOMBRE'])){$NOMBRE = $_POST['NOMBRE'];}else{ $NOMBRE=""; }
if(isset($_POST['DIA'])){$DIA = $_POST['DIA'];}else{ $DIA=""; }
if(isset($_POST['MES'])){$MES = $_POST['MES'];}else{ $MES=""; }
if(isset($_POST['clase'])){$clase = $_POST['clase'];}else{ $clase=""; }
?>

<div aligna="center">
<div class="page-header" align="center">
  <h2>Problemas de Convivencia <small> Consultas</small></h2>
</div>
</div>
<br />

<div align="center" style="width:600px; margin:auto;" class="well">
<div align="left">
<div class="row-fluid">
<div class="span6">
  <FORM action="cfechorias.php" method="POST" name="Fechorias" class="">
   <div class="row-fluid">
    <div class="span6">
    <label>Nivel:      
    <SELECT name="nivel" id="NIVEL"  onChange="submit()" class="span6" style="display:inline">
        <OPTION><? echo $nivel;?></OPTION>
        <? nivel();?>
      </SELECT>
    </label>
    </div>
    <div class="span6">
    <label style="display:inline">Grupo:     
    <SELECT name="grupo" id="GRUPO" class="span6" style="display:inline">
        <OPTION><? echo $grupo;?></OPTION>
        <? grupo($nivel);?>
      </SELECT>
    </label>
    </div>
    </div>
    <label>Apellidos:<br />      
    <INPUT type="text" name="APELLIDOS" size="40" maxlength="32" alt="Apellidos">
    </label>
    <label>Nombre:<br />      
    <INPUT type="text" name="NOMBRE" size="40" maxlength="25">
    </label>
   
    </div>
    <div class="span6">
    
    
    <div class="row-fluid">
    <div class="span5">
    <label>Mes:
    <select name="MES" class="input-mini">
    <option></option>
    <?
    for($i=1;$i<13;$i++){
    ?>
    <option><? echo $i; ?></option>
    <? } ?>
    </select>
    </label>
    </div>
    <div class="span7">

    <label>D�a: 
<div class="input-append">
  <input name="DIA" type="text" class="input input-small" value="" data-date-format="dd-mm-yyyy" id="DIA" style="display:inline">
  <span class="add-on"><i class="fa fa-calendar"></i></span>
</div>
</label>
    </div>
    </div>
        
    <label>Otros criterios:<br />      
    <select size="5" class="input-block-level" name = "clase[]" >
        <option>Expulsion del Centro</option>
        <option>Expulsion del Aula</option>
        <option>Aula de Convivencia</option>
        <option>Falta Grave</option>
        <option>Falta Muy Grave</option>
      </select>
    </label>
    
  <? }?>
  </div>
  </div>
  </div>
  <div align="center">
  <input name="submit1" type='submit' value='Enviar Datos' class="btn btn-primary btn-block">
  </FORM>
  </div>
</div>

<?php
	include("../../pie.php");
?>
<script>  
	$(function ()  
	{ 
		$('#DIA').datepicker()
		.on('changeDate', function(ev){
			$('#DIA').datepicker('hide');
		});
		});  
	</script>
</BODY></HTML>