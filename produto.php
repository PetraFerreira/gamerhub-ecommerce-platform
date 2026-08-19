<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Validar o ID recebido
|--------------------------------------------------------------------------
*/

$produtoId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$produtoId) {
    header("Location: produtos.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Procurar o produto
|--------------------------------------------------------------------------
*/

$sqlProduto = "
    SELECT
        products.id,
        products.nome,
        products.descricao,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca,
        products.categoria_id,
        products.data_lancamento,
        products.plataforma,
        products.genero,
        products.developer,
        products.publisher,
        products.pegi,
        categories.nome AS categoria,
        COALESCE(AVG(reviews.classificacao), 0) AS avaliacao_media,
        COUNT(reviews.id) AS total_avaliacoes
    FROM products
    INNER JOIN categories
        ON products.categoria_id = categories.id
    LEFT JOIN reviews
        ON reviews.product_id = products.id
    WHERE products.id = :produto_id
      AND products.ativo = 1
    GROUP BY
        products.id,
        products.nome,
        products.descricao,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca,
        products.categoria_id,
        products.data_lancamento,
        products.plataforma,
        products.genero,
        products.developer,
        products.publisher,
        products.pegi,
        categories.nome
    LIMIT 1
";

$stmtProduto = $conn->prepare($sqlProduto);
$stmtProduto->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
$stmtProduto->execute();

$produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Plataformas / variantes do produto
|--------------------------------------------------------------------------
*/

$sqlPlataformas = "
    SELECT
        id,
        plataforma,
        preco,
        preco_promocional,
        stock
    FROM product_platforms
    WHERE product_id = :produto_id
    ORDER BY id ASC
";

$stmtPlataformas = $conn->prepare($sqlPlataformas);
$stmtPlataformas->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
$stmtPlataformas->execute();

$plataformas = $stmtPlataformas->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Produto inexistente
|--------------------------------------------------------------------------
*/

if (!$produto) {
    http_response_code(404);

    require_once "includes/header.php";
    ?>

    <section class="product-not-found">
        <div class="container">

            <div class="empty-state">

                <i class="bi bi-controller"></i>

                <h1>Produto não encontrado</h1>

                <p>
                    O produto que procuras não existe ou já não está disponível.
                </p>

                <a
                    href="produtos.php"
                    class="btn btn-gamer-primary"
                >
                    Ver produtos
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

        </div>
    </section>

    <?php
    require_once "includes/footer.php";
    exit;
}

/*
|--------------------------------------------------------------------------
| Preços e stock
|--------------------------------------------------------------------------
*/

$temPlataformas = !empty($plataformas);
$plataformaInicial = $temPlataformas ? $plataformas[0] : null;

$precoBaseAtual = $temPlataformas
    ? (float) $plataformaInicial["preco"]
    : (float) $produto["preco"];

$precoPromocionalAtual = $temPlataformas
    ? $plataformaInicial["preco_promocional"]
    : $produto["preco_promocional"];

$stockAtual = $temPlataformas
    ? (int) $plataformaInicial["stock"]
    : (int) $produto["stock"];

$temPromocao =
    !empty($precoPromocionalAtual)
    && (float) $precoPromocionalAtual < $precoBaseAtual;

$precoAtual = $temPromocao
    ? (float) $precoPromocionalAtual
    : $precoBaseAtual;

$percentagemDesconto = $temPromocao && $precoBaseAtual > 0
    ? round(
        (($precoBaseAtual - (float) $precoPromocionalAtual)
        / $precoBaseAtual) * 100
    )
    : 0;

/*
|--------------------------------------------------------------------------
| Produtos relacionados
|--------------------------------------------------------------------------
*/

$sqlRelacionados = "
    SELECT
        products.id,
        products.nome,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca
    FROM products
    WHERE products.categoria_id = :categoria_id
      AND products.id != :produto_id
      AND products.ativo = 1
    ORDER BY products.destaque DESC, products.criado_em DESC
    LIMIT 4
";

$stmtRelacionados = $conn->prepare($sqlRelacionados);

$stmtRelacionados->bindValue(
    ":categoria_id",
    (int) $produto["categoria_id"],
    PDO::PARAM_INT
);

$stmtRelacionados->bindValue(
    ":produto_id",
    $produtoId,
    PDO::PARAM_INT
);

$stmtRelacionados->execute();

$produtosRelacionados = $stmtRelacionados->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header.php";
?>

<!-- Breadcrumb -->

<section class="product-breadcrumb-section">

    <div class="container">

        <nav aria-label="Navegação estrutural">

            <ol class="breadcrumb product-breadcrumb">

                <li class="breadcrumb-item">
                    <a href="index.php">Início</a>
                </li>

                <li class="breadcrumb-item">
                    <a href="produtos.php">Produtos</a>
                </li>

                <li class="breadcrumb-item">
                    <a
                        href="produtos.php?categoria=<?= (int) $produto["categoria_id"] ?>"
                    >
                        <?= htmlspecialchars($produto["categoria"]) ?>
                    </a>
                </li>

                <li
                    class="breadcrumb-item active"
                    aria-current="page"
                >
                    <?= htmlspecialchars($produto["nome"]) ?>
                </li>

            </ol>

        </nav>

    </div>

</section>

<!-- Detalhe do produto -->

<section class="product-detail-section">

    <div class="container">

        <div class="row g-5 align-items-start">

            <!-- Imagem -->

            <div class="col-lg-6">

                <div class="product-detail-image">

                    <?php if ($temPromocao): ?>

                        <span class="product-detail-discount">
                            -<?= $percentagemDesconto ?>%
                        </span>

                    <?php endif; ?>

                    <button
                        type="button"
                        class="product-detail-wishlist"
                        aria-label="Adicionar aos favoritos"
                    >
                        <i class="bi bi-heart"></i>
                    </button>

                    <?php if (!empty($produto["imagem"])): ?>

                        <img
                            src="<?= htmlspecialchars($produto["imagem"]) ?>?v=<?= time() ?>"
                            alt="<?= htmlspecialchars($produto["nome"]) ?>"
                        >

                    <?php else: ?>

                        <div class="product-detail-placeholder">
                            <i class="bi bi-controller"></i>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- Informação -->

            <div class="col-lg-6">

                <div class="product-detail-content">

                    <div class="product-detail-meta">

                        <span>
                            <?= htmlspecialchars($produto["marca"]) ?>
                        </span>

                        <span>
                            <?= htmlspecialchars($produto["categoria"]) ?>
                        </span>

                    </div>

                    <h1>
                        <?= htmlspecialchars($produto["nome"]) ?>
                    </h1>

                    <div class="product-detail-rating">

                        <div class="rating-stars">

                            <?php
                            $avaliacaoMedia = (float) $produto["avaliacao_media"];

                            for ($estrela = 1; $estrela <= 5; $estrela++):
                                ?>

                                <i
                                    class="bi <?= $estrela <= round($avaliacaoMedia)
                                        ? "bi-star-fill"
                                        : "bi-star" ?>"
                                ></i>

                            <?php endfor; ?>

                        </div>

                        <span>
                            <?= number_format($avaliacaoMedia, 1, ",", ".") ?>
                        </span>

                        <span class="rating-count">
                            (<?= (int) $produto["total_avaliacoes"] ?>
                            avaliações)
                        </span>

                    </div>

                    <p class="product-detail-description">
                        <?= nl2br(htmlspecialchars($produto["descricao"])) ?>
                    </p>

                    <?php if ($temPlataformas): ?>

                        <div class="product-platform-selector">

                            <span class="platform-selector-label">
                                Escolhe a plataforma
                            </span>

                            <div class="platform-options">

                                <?php foreach ($plataformas as $indice => $plataforma): ?>

                                    <button
                                        type="button"
                                        class="platform-option <?= $indice === 0 ? "active" : "" ?>"
                                        data-platform-id="<?= (int) $plataforma["id"] ?>"
                                        data-platform="<?= htmlspecialchars($plataforma["plataforma"]) ?>"
                                        data-price="<?= number_format((float) $plataforma["preco"], 2, ".", "") ?>"
                                        data-promo="<?= $plataforma["preco_promocional"] !== null
                                            ? number_format((float) $plataforma["preco_promocional"], 2, ".", "")
                                            : "" ?>"
                                        data-stock="<?= (int) $plataforma["stock"] ?>"
                                    >
                                        <?= htmlspecialchars($plataforma["plataforma"]) ?>
                                    </button>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endif; ?>

                    <div class="product-detail-price">

                        <span
                            id="product-old-price"
                            class="product-detail-old-price"
                            <?= !$temPromocao ? 'style="display:none;"' : "" ?>
                        >
                            <?= number_format($precoBaseAtual, 2, ",", ".") ?> €
                        </span>

                        <strong id="product-current-price">
                            <?= number_format($precoAtual, 2, ",", ".") ?> €
                        </strong>

                        <small>
                            IVA incluído
                        </small>

                    </div>

                    <div class="product-detail-stock">

                        <span
                            id="product-stock-status"
                            class="<?= $stockAtual > 0
                                ? "stock-available"
                                : "stock-unavailable" ?>"
                        >
                            <i class="bi bi-circle-fill"></i>
                            <?= $stockAtual > 0 ? "Em stock" : "Produto esgotado" ?>
                        </span>

                        <small id="product-stock-count">
                            <?= $stockAtual > 0
                                ? $stockAtual . " unidades disponíveis"
                                : "" ?>
                        </small>

                    </div>

                    <div id="purchase-form-wrapper" <?= $stockAtual <= 0 ? 'style="display:none;"' : "" ?>>

                        <form
                            action="carrinho.php"
                            method="post"
                            class="product-purchase-form"
                        >

                            <input
                                type="hidden"
                                name="produto_id"
                                value="<?= (int) $produto["id"] ?>"
                            >

                            <input
                                type="hidden"
                                id="plataforma_id"
                                name="plataforma_id"
                                value="<?= $temPlataformas ? (int) $plataformaInicial["id"] : "" ?>"
                            >

                            <input
                                type="hidden"
                                id="plataforma_nome"
                                name="plataforma"
                                value="<?= $temPlataformas
                                    ? htmlspecialchars($plataformaInicial["plataforma"])
                                    : htmlspecialchars($produto["plataforma"] ?? "") ?>"
                            >

                            <div class="quantity-field">

                                <label for="quantidade">
                                    Quantidade
                                </label>

                                <div class="quantity-control">

                                    <button
                                        type="button"
                                        class="quantity-button"
                                        data-action="decrease"
                                        aria-label="Diminuir quantidade"
                                    >
                                        <i class="bi bi-dash"></i>
                                    </button>

                                    <input
                                        id="quantidade"
                                        type="number"
                                        name="quantidade"
                                        value="1"
                                        min="1"
                                        max="<?= $stockAtual ?>"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="quantity-button"
                                        data-action="increase"
                                        aria-label="Aumentar quantidade"
                                    >
                                        <i class="bi bi-plus"></i>
                                    </button>

                                </div>

                            </div>

                            <button
                                type="submit"
                                name="adicionar_carrinho"
                                id="add-to-cart-button"
                                class="btn add-to-cart-button"
                            >
                                <i class="bi bi-cart-plus"></i>
                                Adicionar ao carrinho
                            </button>

                        </form>

                    </div>

                    <div class="product-detail-benefits">

                        <div>
                            <i class="bi bi-truck"></i>

                            <span>
                                <strong>Entrega rápida</strong>
                                24 a 48 horas úteis
                            </span>
                        </div>

                        <div>
                            <i class="bi bi-shield-check"></i>

                            <span>
                                <strong>Compra protegida</strong>
                                Pagamento seguro
                            </span>
                        </div>

                        <div>
                            <i class="bi bi-arrow-repeat"></i>

                            <span>
                                <strong>Devolução simples</strong>
                                Até 14 dias
                            </span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Características -->

        <div class="product-information-grid">

            <div class="product-description-box">

                <span class="section-eyebrow">
                    Sobre o produto
                </span>

                <h2>Descrição</h2>

                <p>
                    <?= nl2br(htmlspecialchars($produto["descricao"])) ?>
                </p>

            </div>

            <div class="product-specifications-box">

                <span class="section-eyebrow">
                    Informação
                </span>

                <h2>Características</h2>

                <dl class="product-specifications">

                    <div>
                        <dt>Marca</dt>
                        <dd><?= htmlspecialchars($produto["marca"]) ?></dd>
                    </div>

                    <div>
                        <dt>Categoria</dt>
                        <dd><?= htmlspecialchars($produto["categoria"]) ?></dd>
                    </div>

                    <?php if (!empty($produto["plataforma"])): ?>

                        <div>
                            <dt>Plataforma</dt>
                            <dd>
                                <?= htmlspecialchars($produto["plataforma"]) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (!empty($produto["genero"])): ?>

                        <div>
                            <dt>Género</dt>
                            <dd>
                                <?= htmlspecialchars($produto["genero"]) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (!empty($produto["developer"])): ?>

                        <div>
                            <dt>Desenvolvedor</dt>
                            <dd>
                                <?= htmlspecialchars($produto["developer"]) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (!empty($produto["publisher"])): ?>

                        <div>
                            <dt>Editora</dt>
                            <dd>
                                <?= htmlspecialchars($produto["publisher"]) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (!empty($produto["pegi"])): ?>

                        <div>
                            <dt>PEGI</dt>
                            <dd>
                                <?= htmlspecialchars($produto["pegi"]) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                    <?php if (!empty($produto["data_lancamento"])): ?>

                        <div>
                            <dt>Lançamento</dt>
                            <dd>
                                <?= date(
                                    "d/m/Y",
                                    strtotime($produto["data_lancamento"])
                                ) ?>
                            </dd>
                        </div>

                    <?php endif; ?>

                </dl>

            </div>

        </div>

    </div>

</section>

<!-- Produtos relacionados -->

<?php if (!empty($produtosRelacionados)): ?>

    <section class="related-products-section">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">
                        Também poderás gostar
                    </span>

                    <h2>Produtos relacionados</h2>
                </div>

                <a
                    href="produtos.php?categoria=<?= (int) $produto["categoria_id"] ?>"
                    class="section-link"
                >
                    Ver categoria
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

            <div class="row g-4">

                <?php foreach ($produtosRelacionados as $relacionado): ?>

                    <?php
                    $relacionadoPromocao =
                        !empty($relacionado["preco_promocional"])
                        && (float) $relacionado["preco_promocional"]
                            < (float) $relacionado["preco"];

                    $relacionadoPreco = $relacionadoPromocao
                        ? (float) $relacionado["preco_promocional"]
                        : (float) $relacionado["preco"];
                    ?>

                    <div class="col-sm-6 col-lg-3">

                        <article class="product-card">

                            <div class="product-image">

                                <?php if (!empty($relacionado["imagem"])): ?>

                                    <img
                                        src="<?= htmlspecialchars($relacionado["imagem"]) ?>?v=<?= time() ?>"
                                        alt="<?= htmlspecialchars($relacionado["nome"]) ?>"
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
                                        <?= htmlspecialchars($relacionado["marca"]) ?>
                                    </span>
                                </div>

                                <h3>

                                    <a
                                        href="produto.php?id=<?= (int) $relacionado["id"] ?>"
                                    >
                                        <?= htmlspecialchars($relacionado["nome"]) ?>
                                    </a>

                                </h3>

                                <div class="product-price-row">

                                    <div class="product-price">

                                        <?php if ($relacionadoPromocao): ?>

                                            <span class="old-price">
                                                <?= number_format(
                                                    (float) $relacionado["preco"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €
                                            </span>

                                        <?php endif; ?>

                                        <strong>
                                            <?= number_format(
                                                $relacionadoPreco,
                                                2,
                                                ",",
                                                "."
                                            ) ?> €
                                        </strong>

                                    </div>

                                    <a
                                        href="produto.php?id=<?= (int) $relacionado["id"] ?>"
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

        </div>

    </section>

<?php endif; ?>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const platformButtons = document.querySelectorAll(".platform-option");

    if (platformButtons.length === 0) {
        return;
    }

    const currentPrice = document.querySelector("#product-current-price");
    const oldPrice = document.querySelector("#product-old-price");
    const stockStatus = document.querySelector("#product-stock-status");
    const stockCount = document.querySelector("#product-stock-count");
    const quantityInput = document.querySelector("#quantidade");
    const platformIdInput = document.querySelector("#plataforma_id");
    const platformNameInput = document.querySelector("#plataforma_nome");
    const purchaseWrapper = document.querySelector("#purchase-form-wrapper");

    const formatPrice = (value) => {
        return Number(value).toLocaleString("pt-PT", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " €";
    };

    platformButtons.forEach((button) => {
        button.addEventListener("click", () => {
            platformButtons.forEach((item) => item.classList.remove("active"));
            button.classList.add("active");

            const basePrice = Number(button.dataset.price);
            const promoValue = button.dataset.promo;
            const promoPrice = promoValue !== "" ? Number(promoValue) : null;
            const stock = Number(button.dataset.stock);

            const hasPromotion =
                promoPrice !== null
                && promoPrice > 0
                && promoPrice < basePrice;

            currentPrice.textContent = formatPrice(
                hasPromotion ? promoPrice : basePrice
            );

            if (hasPromotion) {
                oldPrice.textContent = formatPrice(basePrice);
                oldPrice.style.display = "";
            } else {
                oldPrice.style.display = "none";
            }

            platformIdInput.value = button.dataset.platformId;
            platformNameInput.value = button.dataset.platform;

            if (stock > 0) {
                stockStatus.className = "stock-available";
                stockStatus.innerHTML =
                    '<i class="bi bi-circle-fill"></i> Em stock';

                stockCount.textContent =
                    stock + " unidades disponíveis";

                purchaseWrapper.style.display = "";
                quantityInput.max = stock;

                if (Number(quantityInput.value) > stock) {
                    quantityInput.value = stock;
                }
            } else {
                stockStatus.className = "stock-unavailable";
                stockStatus.innerHTML =
                    '<i class="bi bi-circle-fill"></i> Produto esgotado';

                stockCount.textContent = "";
                purchaseWrapper.style.display = "none";
            }
        });
    });
});
</script>

<?php require_once "includes/footer.php"; ?>