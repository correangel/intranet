<h4><span class="fa fa-search fa-fw"></span> Buscar alumno</h4>

<form method="get" action="admin/noticias/buscar.php">
	<input type="text" class="form-control" name="q" id="buscarAlumnos" onkeyup="javascript:buscar('list_alumnos',this.value);" placeholder="Buscar...">
</form>

<div id="list_alumnos"></div>
