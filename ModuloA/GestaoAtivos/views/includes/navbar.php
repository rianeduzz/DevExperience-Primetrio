<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard/index.php">Gestão de Ativos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../manutencao/index.php">Manutenções</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../monitoramento/index.php">Monitoramento</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../historico/index.php">Histórico</a>
                </li>
            </ul>
            <span class="navbar-text">
                Bem-vindo, <?php echo $_SESSION['user_name']; ?>
                <a href="../auth/logout.php" class="btn btn-outline-light ms-3">Sair</a>
            </span>
        </div>
    </div>
</nav>
