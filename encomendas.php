<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

if (empty($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$utilizadorId = (int) $_SESSION["utilizador_id"];

$sqlEncomendas = "
    SELECT
        orders.id,
        orders.data_encomenda,
        orders.estado,
        orders.total,
        orders.nome_envio,
        orders.morada_envio,
        orders.cidade_envio,
        orders.codigo_postal_envio,
        COALESCE(SUM(order_items.quantidade), 0) AS total_itens
    FROM orders
    LEFT JOIN order_items
        ON order_items.order_id = orders.id
    WHERE orders.user_id = :utilizador_id
    GROUP BY
        orders.id,
        orders.data_encomenda,
        orders.estado,
        orders.total,
        orders.nome_envio,
        orders.morada_envio,
        orders.cidade_envio,
        orders.codigo_postal_envio
    ORDER BY orders.data_encomenda DESC
";

$stmtEncomendas = $conn->prepare($sqlEncomendas);

$stmtEncomendas->bindValue(
    ":utilizador_id",
    $utilizadorId,
    PDO::PARAM_INT
);

$stmtEncomendas->execute();

$encomendas = $stmtEncomendas->fetchAll(PDO::FETCH_ASSOC);

$encomendaSucesso = filter_input(
    INPUT_GET,
    "sucesso",
    FILTER_VALIDATE_INT
);

require_once "includes/header.php";
?>

<section class="orders-page py-5">

    <div class="container">

        <div class="mb-5">

            <span class="section-eyebrow">
                Área de cliente
            </span>

            <h1>As minhas encomendas</h1>

            <p>
                Consulta as tuas compras e acompanha o respetivo estado.
            </p>

        </div>

        <?php if ($encomendaSucesso): ?>

            <div class="alert alert-success mb-4">

                <i class="bi bi-check-circle me-2"></i>

                A encomenda
                <strong>#<?= (int) $encomendaSucesso ?></strong>
                foi concluída com sucesso.

            </div>

        <?php endif; ?>

        <?php if (empty($encomendas)): ?>

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <h2>Ainda não tens encomendas</h2>

                <p>
                    Quando concluíres uma compra, ela aparecerá aqui.
                </p>

                <a
                    href="produtos.php"
                    class="btn btn-gamer-primary"
                >
                    Explorar produtos
                </a>

            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($encomendas as $encomenda): ?>

                    <div class="col-12">

                        <article class="auth-card">

                            <div
                                class="d-flex flex-wrap justify-content-between gap-3"
                            >

                                <div>

                                    <span class="section-eyebrow">
                                        Encomenda
                                    </span>

                                    <h2 class="mt-2">
                                        #<?= (int) $encomenda["id"] ?>
                                    </h2>

                                </div>

                                <div class="text-lg-end">

                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars(
                                            ucfirst(
                                                str_replace(
                                                    "_",
                                                    " ",
                                                    $encomenda["estado"]
                                                )
                                            )
                                        ) ?>
                                    </span>

                                    <div class="mt-3">

                                        <strong>
                                            <?= number_format(
                                                (float) $encomenda["total"],
                                                2,
                                                ",",
                                                "."
                                            ) ?> €
                                        </strong>

                                    </div>

                                </div>

                            </div>

                            <hr>

                            <div class="row g-3">

                                <div class="col-md-3">

                                    <small class="text-secondary">
                                        Data
                                    </small>

                                    <div>
                                        <?= date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $encomenda["data_encomenda"]
                                            )
                                        ) ?>
                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-secondary">
                                        Produtos
                                    </small>

                                    <div>
                                        <?= (int) $encomenda["total_itens"] ?>
                                        item(ns)
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-secondary">
                                        Entrega
                                    </small>

                                    <div>
                                        <?= htmlspecialchars(
                                            $encomenda["nome_envio"]
                                        ) ?>
                                    </div>

                                    <div>
                                        <?= htmlspecialchars(
                                            $encomenda["morada_envio"]
                                        ) ?>,
                                        <?= htmlspecialchars(
                                            $encomenda["codigo_postal_envio"]
                                        ) ?>
                                        <?= htmlspecialchars(
                                            $encomenda["cidade_envio"]
                                        ) ?>
                                    </div>

                                </div>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>