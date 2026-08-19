<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Proteger área administrativa
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION["utilizador_id"])
    || ($_SESSION["tipo_utilizador"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Estatísticas
|--------------------------------------------------------------------------
*/

$totalProdutos = (int) $conn
    ->query("SELECT COUNT(*) FROM products")
    ->fetchColumn();

$totalCategorias = (int) $conn
    ->query("SELECT COUNT(*) FROM categories")
    ->fetchColumn();

$totalUtilizadores = (int) $conn
    ->query("SELECT COUNT(*) FROM users")
    ->fetchColumn();

$totalEncomendas = (int) $conn
    ->query("SELECT COUNT(*) FROM orders")
    ->fetchColumn();

$totalVendas = (float) $conn
    ->query("
        SELECT COALESCE(SUM(total), 0)
        FROM orders
        WHERE estado IN ('pago', 'em_preparacao', 'enviado')
    ")
    ->fetchColumn();

$totalPendentes = (int) $conn
    ->query("
        SELECT COUNT(*)
        FROM orders
        WHERE estado = 'pendente'
    ")
    ->fetchColumn();

/*
|--------------------------------------------------------------------------
| Últimas encomendas
|--------------------------------------------------------------------------
*/

$sqlUltimasEncomendas = "
    SELECT
        orders.id,
        orders.data_encomenda,
        orders.estado,
        orders.total,
        users.nome AS cliente
    FROM orders
    INNER JOIN users
        ON users.id = orders.user_id
    ORDER BY orders.data_encomenda DESC
    LIMIT 5
";

$ultimasEncomendas = $conn
    ->query($sqlUltimasEncomendas)
    ->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Produtos com pouco stock
|--------------------------------------------------------------------------
*/

$sqlPoucoStock = "
    SELECT
        id,
        nome,
        stock
    FROM products
    WHERE ativo = 1
      AND stock <= 5
    ORDER BY stock ASC, nome ASC
    LIMIT 5
";

$produtosPoucoStock = $conn
    ->query($sqlPoucoStock)
    ->fetchAll(PDO::FETCH_ASSOC);

$nomeAdministrador =
    $_SESSION["utilizador_nome"] ?? "Administrador";

function classeEstadoAdmin($estado)
{
    $classes = [
        "pendente" => "status-pendente",
        "pago" => "status-pago",
        "em_preparacao" => "status-preparacao",
        "enviado" => "status-enviado"
    ];

    return $classes[$estado] ?? "status-pendente";
}

function nomeEstadoAdmin($estado)
{
    return ucfirst(str_replace("_", " ", $estado));
}

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Administração — GamerHub</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css?v=<?= filemtime(
            __DIR__ . "/../assets/css/style.css"
        ) ?>"
    >

    <style>
        body {
            min-height: 100vh;
            background: #090914;
            color: #ffffff;
            font-family: "Roboto", sans-serif;
        }

        .admin-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }

        .admin-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 1.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            background: #10101d;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            color: #ffffff;
            font-family: "Orbitron", sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
        }

        .admin-logo i,
        .admin-logo span {
            color: #9b4dff;
        }

        .admin-nav {
            display: grid;
            gap: 0.55rem;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.85rem 1rem;
            border-radius: 0.8rem;
            color: #a9a9bb;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(155, 77, 255, 0.14);
            color: #ffffff;
        }

        .admin-nav a.active {
            border: 1px solid rgba(155, 77, 255, 0.35);
        }

        .admin-sidebar-footer {
            position: absolute;
            right: 1.5rem;
            bottom: 1.5rem;
            left: 1.5rem;
        }

        .admin-main {
            padding: 2rem;
        }

        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .admin-title span {
            color: #9b4dff;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .admin-title h1 {
            margin: 0.45rem 0 0;
            font-family: "Orbitron", sans-serif;
            font-size: clamp(1.8rem, 4vw, 3rem);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #c4c4d2;
        }

        .admin-user i {
            color: #9b4dff;
            font-size: 1.4rem;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .admin-stat-card,
        .admin-panel {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: #131322;
        }

        .admin-stat-card {
            padding: 1.35rem;
        }

        .admin-stat-icon {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            margin-bottom: 1rem;
            border-radius: 0.8rem;
            background: rgba(155, 77, 255, 0.16);
            color: #a95cff;
            font-size: 1.25rem;
        }

        .admin-stat-card small {
            color: #858599;
        }

        .admin-stat-card strong {
            display: block;
            margin-top: 0.35rem;
            font-family: "Orbitron", sans-serif;
            font-size: 1.8rem;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.8fr);
            gap: 1rem;
        }

        .admin-panel {
            padding: 1.3rem;
        }

        .admin-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .admin-panel-heading h2 {
            margin: 0;
            font-family: "Orbitron", sans-serif;
            font-size: 1.15rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.9rem 0.7rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            text-align: left;
        }

        .admin-table th {
            color: #858599;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .admin-table td {
            color: #ececf4;
        }

        .status-badge {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .status-pendente {
            background: rgba(255, 193, 7, 0.16);
            color: #ffcf40;
        }

        .status-pago {
            background: rgba(25, 135, 84, 0.16);
            color: #52d49b;
        }

        .status-preparacao {
            background: rgba(13, 110, 253, 0.16);
            color: #67a7ff;
        }

        .status-enviado {
            background: rgba(111, 66, 193, 0.18);
            color: #b48cff;
        }

        .low-stock-list {
            display: grid;
            gap: 0.8rem;
        }

        .low-stock-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem;
            border-radius: 0.8rem;
            background: rgba(255, 255, 255, 0.035);
        }

        .low-stock-item span {
            color: #ff7a86;
            font-weight: 800;
        }

        .admin-empty {
            padding: 2rem 1rem;
            color: #858599;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .admin-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                position: static;
                height: auto;
            }

            .admin-sidebar-footer {
                position: static;
                margin-top: 1.5rem;
            }

            .admin-main {
                padding: 1rem;
            }

            .admin-stats {
                grid-template-columns: 1fr;
            }

            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="admin-shell">

    <aside class="admin-sidebar">

        <a
            href="index.php"
            class="admin-logo"
        >
            <i class="bi bi-controller"></i>
            Gamer<span>Hub</span>
        </a>

        <nav class="admin-nav">

            <a
                href="index.php"
                class="active"
            >
                <i class="bi bi-grid"></i>
                Dashboard
            </a>

            <a href="produtos.php">
                <i class="bi bi-box-seam"></i>
                Produtos
            </a>

            <a href="categorias.php">
                <i class="bi bi-tags"></i>
                Categorias
            </a>

            <a href="encomendas.php">
                <i class="bi bi-bag-check"></i>
                Encomendas
            </a>

            <a href="utilizadores.php">
                <i class="bi bi-people"></i>
                Utilizadores
            </a>

        </nav>

        <div class="admin-sidebar-footer">

            <a
                href="../index.php"
                class="btn btn-outline-light w-100 mb-2"
            >
                <i class="bi bi-shop me-2"></i>
                Ver loja
            </a>

            <a
                href="../logout.php"
                class="btn btn-outline-danger w-100"
            >
                <i class="bi bi-box-arrow-right me-2"></i>
                Sair
            </a>

        </div>

    </aside>

    <main class="admin-main">

        <div class="admin-topbar">

            <div class="admin-title">

                <span>Área administrativa</span>

                <h1>Dashboard</h1>

            </div>

            <div class="admin-user">

                <i class="bi bi-person-circle"></i>

                <div>
                    <small>Administrador</small>
                    <div>
                        <?= htmlspecialchars($nomeAdministrador) ?>
                    </div>
                </div>

            </div>

        </div>

        <section class="admin-stats">

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <small>Produtos</small>

                <strong>
                    <?= $totalProdutos ?>
                </strong>

            </article>

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-tags"></i>
                </div>

                <small>Categorias</small>

                <strong>
                    <?= $totalCategorias ?>
                </strong>

            </article>

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-people"></i>
                </div>

                <small>Utilizadores</small>

                <strong>
                    <?= $totalUtilizadores ?>
                </strong>

            </article>

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-bag-check"></i>
                </div>

                <small>Encomendas</small>

                <strong>
                    <?= $totalEncomendas ?>
                </strong>

            </article>

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <small>Pendentes</small>

                <strong>
                    <?= $totalPendentes ?>
                </strong>

            </article>

            <article class="admin-stat-card">

                <div class="admin-stat-icon">
                    <i class="bi bi-currency-euro"></i>
                </div>

                <small>Vendas concluídas</small>

                <strong>
                    <?= number_format(
                        $totalVendas,
                        2,
                        ",",
                        "."
                    ) ?> €
                </strong>

            </article>

        </section>

        <section class="admin-grid">

            <article class="admin-panel">

                <div class="admin-panel-heading">

                    <h2>Últimas encomendas</h2>

                    <a
                        href="encomendas.php"
                        class="btn btn-sm btn-outline-light"
                    >
                        Ver todas
                    </a>

                </div>

                <?php if (empty($ultimasEncomendas)): ?>

                    <div class="admin-empty">
                        Ainda não existem encomendas.
                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach (
                                    $ultimasEncomendas
                                    as $encomenda
                                ): ?>

                                    <tr>

                                        <td>
                                            #<?= (int) $encomenda["id"] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $encomenda["cliente"]
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= date(
                                                "d/m/Y H:i",
                                                strtotime(
                                                    $encomenda[
                                                        "data_encomenda"
                                                    ]
                                                )
                                            ) ?>
                                        </td>

                                        <td>

                                            <span
                                                class="status-badge <?= classeEstadoAdmin(
                                                    $encomenda["estado"]
                                                ) ?>"
                                            >
                                                <?= htmlspecialchars(
                                                    nomeEstadoAdmin(
                                                        $encomenda[
                                                            "estado"
                                                        ]
                                                    )
                                                ) ?>
                                            </span>

                                        </td>

                                        <td>
                                            <?= number_format(
                                                (float) $encomenda["total"],
                                                2,
                                                ",",
                                                "."
                                            ) ?> €
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </article>

            <article class="admin-panel">

                <div class="admin-panel-heading">

                    <h2>Stock reduzido</h2>

                    <a
                        href="produtos.php"
                        class="btn btn-sm btn-outline-light"
                    >
                        Gerir
                    </a>

                </div>

                <?php if (empty($produtosPoucoStock)): ?>

                    <div class="admin-empty">
                        Não existem produtos com stock reduzido.
                    </div>

                <?php else: ?>

                    <div class="low-stock-list">

                        <?php foreach (
                            $produtosPoucoStock
                            as $produto
                        ): ?>

                            <div class="low-stock-item">

                                <div>
                                    <?= htmlspecialchars(
                                        $produto["nome"]
                                    ) ?>
                                </div>

                                <span>
                                    <?= (int) $produto["stock"] ?>
                                </span>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </article>

        </section>

    </main>

</div>

</body>
</html>