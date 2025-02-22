<nav class="navbar navbar-dark bg-primary mb-3">
    <a class="navbar-brand ms-2" href="<?= $baseUrl . 'index.php' ?>">Gastos de Mario</a>
    <a href="#" class="text-light me-2" onclick="confirmarCerrarSesion()">Cerrar sesión</a>
</nav>
<ul class="nav nav-pills nav-justified mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $indice == "inicio" ? "active" : "" ?>" aria-current=""
            href="<?= $baseUrl . 'index.php' ?>">Inicio</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $indice == "estadisticas" ? "active" : "" ?>"
            href="<?= $baseUrl . 'tabla_gastos/index.php' ?>">Estadísticas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $indice == "detalles" ? "active" : "" ?>"
            href="<?= $baseUrl . 'detalles/index.php' ?>">Detalles</a>
    </li>
</ul>
<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Cierre de Sesión</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true" class="text-light">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cerrar la sesión?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="<?= $baseUrl . 'login/cerrarsesion.php' ?>" class="btn btn-primary">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarCerrarSesion() {
        $('#confirmModal').modal('show');
    }
</script>