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

$userId = (int) $_SESSION["utilizador_id"];

$stmt = $conn->prepare("
    SELECT
        products.id,
        products.nome,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca,
        categories.nome AS categoria
    FROM wishlist
    INNER JOIN products
        ON products.id = wishlist.product_id
    INNER JOIN categories
        ON categories.id = products.categoria_id
    WHERE wishlist.user_id = :user_id
      AND products.ativo = 1
    ORDER BY wishlist.criado_em DESC
");

$stmt->execute([
    ":user_id" => $userId
]);

$favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header.php";

?>

<section class="section-padding favorites-page">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    Área de cliente
                </span>

                <h1>
                    Os meus favoritos
                </h1>

                <p>
                    Guarda os produtos de que mais gostas para consultares mais tarde.
                </p>

            </div>

            <span class="favorites-count">
                <?= count($favoritos) ?>
                <?= count($favoritos) === 1
                    ? "produto"
                    : "produtos" ?>
            </span>

        </div>

        <?php if (empty($favoritos)): ?>

            <div class="empty-state">

                <i class="bi bi-heart"></i>

                <h2>
                    Ainda não tens favoritos
                </h2>

                <p>
                    Explora o catálogo e utiliza o coração para guardares produtos.
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

                <?php foreach ($favoritos as $produto): ?>

                    <?php

                    $temPromocao =
                        !empty($produto["preco_promocional"])
                        && (float) $produto["preco_promocional"]
                            < (float) $produto["preco"];

                    $precoAtual = $temPromocao
                        ? (float) $produto["preco_promocional"]
                        : (float) $produto["preco"];

                    ?>

                    <div class="col-sm-6 col-lg-3">

                        <article class="product-card">

                            <div class="product-image">

                                <button
                                    type="button"
                                    class="wishlist-button active"
                                    data-product-id="<?= (int) $produto["id"] ?>"
                                    aria-label="Remover dos favoritos"
                                >
                                    <i class="bi bi-heart-fill"></i>
                                </button>

                                <?php if (!empty($produto["imagem"])): ?>

                                    <img
                                        src="<?= htmlspecialchars($produto["imagem"]) ?>"
                                        alt="<?= htmlspecialchars($produto["nome"]) ?>"
                                    >

                                <?php else: ?>

                                    <div class="product-placeholder">
                                        <i class="bi bi-controller"></i>
                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="product-content">

                                <div class="product-meta">

                                    <span>
                                        <?= htmlspecialchars(
                                            $produto["marca"] ?? ""
                                        ) ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars(
                                            $produto["categoria"]
                                        ) ?>
                                    </span>

                                </div>

                                <h3>

                                    <a href="produto.php?id=<?= (int) $produto["id"] ?>">
                                        <?= htmlspecialchars($produto["nome"]) ?>
                                    </a>

                                </h3>

                                <div class="product-price-row">

                                    <div class="product-price">

                                        <?php if ($temPromocao): ?>

                                            <span class="old-price">
                                                <?= number_format(
                                                    (float) $produto["preco"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €
                                            </span>

                                        <?php endif; ?>

                                        <strong>
                                            <?= number_format(
                                                $precoAtual,
                                                2,
                                                ",",
                                                "."
                                            ) ?> €
                                        </strong>

                                    </div>

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                        class="product-cart-button"
                                        aria-label="Ver produto"
                                    >
                                        <i class="bi bi-arrow-right"></i>
                                    </a>

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