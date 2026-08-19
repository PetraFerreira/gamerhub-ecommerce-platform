<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = basename($_SERVER["PHP_SELF"]);

$totalItensCarrinho = 0;

if (!empty($_SESSION["carrinho"])) {
    $totalItensCarrinho = array_sum($_SESSION["carrinho"]);
}

$utilizadorAutenticado =
    !empty($_SESSION["utilizador_id"]);

$utilizadorAdmin =
    $utilizadorAutenticado
    && ($_SESSION["tipo_utilizador"] ?? "") === "admin";

$nomeUtilizador =
    $_SESSION["utilizador_nome"] ?? "Utilizador";

$caminhoCss = __DIR__ . "/../assets/css/style.css";

$versaoCss = file_exists($caminhoCss)
    ? filemtime($caminhoCss)
    : time();

$paginaDestaques =
    $paginaAtual === "produtos.php"
    && isset($_GET["destaque"])
    && $_GET["destaque"] === "1";

$paginaProdutos =
    $paginaAtual === "produtos.php"
    && !$paginaDestaques;

?>

<!DOCTYPE html>

<html lang="pt-PT">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="GamerHub — equipamentos, periféricos, acessórios e jogos."
    >

    <title>GamerHub</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=<?= (int) $versaoCss ?>"
    >

</head>

<body>

<header>

    <nav class="navbar navbar-expand-lg navbar-dark gamer-navbar">

        <div class="container">

            <a
                class="navbar-brand gamer-logo"
                href="index.php"
            >
                <i class="bi bi-controller"></i>
                Gamer<span>Hub</span>
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarGamerHub"
                aria-controls="navbarGamerHub"
                aria-expanded="false"
                aria-label="Abrir menu"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div
                class="collapse navbar-collapse"
                id="navbarGamerHub"
            >

                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

                    <li class="nav-item">

                        <a
                            class="nav-link <?= $paginaAtual === "index.php"
                                ? "active"
                                : "" ?>"
                            href="index.php"
                        >
                            Início
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?= $paginaProdutos
                                ? "active"
                                : "" ?>"
                            href="produtos.php"
                        >
                            Produtos
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?= $paginaDestaques
                                ? "active"
                                : "" ?>"
                            href="produtos.php?destaque=1"
                        >
                            Destaques
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="index.php#contactos"
                        >
                            Contactos
                        </a>

                    </li>

                </ul>

                <div
                    class="navbar-actions d-flex align-items-center gap-2 flex-wrap"
                >

                    <?php if ($utilizadorAutenticado): ?>

                        <?php if ($utilizadorAdmin): ?>

                            <a
                                class="btn btn-gamer-primary btn-sm"
                                href="admin/index.php"
                                aria-label="Abrir painel administrativo"
                            >
                                <i class="bi bi-speedometer2"></i>
                                Admin
                            </a>

                        <?php endif; ?>

                        <a
                            class="btn btn-outline-light btn-sm"
                            href="favoritos.php"
                            aria-label="Abrir favoritos"
                        >
                            <i class="bi bi-heart"></i>
                            Favoritos
                        </a>

                        <a
                            class="navbar-user-name text-decoration-none"
                            href="perfil.php"
                            aria-label="Abrir perfil"
                        >
                            <i class="bi bi-person-check"></i>

                            Olá,
                            <?= htmlspecialchars($nomeUtilizador) ?>
                        </a>

                        <a
                            class="btn btn-outline-light btn-sm"
                            href="logout.php"
                            aria-label="Terminar sessão"
                        >
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </a>

                    <?php else: ?>

                        <a
                            class="btn btn-outline-light btn-sm"
                            href="login.php"
                            aria-label="Entrar na conta"
                        >
                            <i class="bi bi-person"></i>
                            Entrar
                        </a>

                        <a
                            class="btn btn-gamer-primary btn-sm"
                            href="register.php"
                        >
                            Criar conta
                        </a>

                    <?php endif; ?>

                    <a
                        class="btn cart-button btn-sm"
                        href="carrinho.php"
                        aria-label="Abrir carrinho"
                    >
                        <i class="bi bi-cart3"></i>

                        <span class="cart-count">
                            <?= (int) $totalItensCarrinho ?>
                        </span>
                    </a>

                </div>

            </div>

        </div>

    </nav>

</header>

<main></main>