<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Favoritos do utilizador
|--------------------------------------------------------------------------
*/

$idsFavoritos = [];

if (!empty($_SESSION["utilizador_id"])) {
    $stmtFavoritos = $conn->prepare("
        SELECT product_id
        FROM wishlist
        WHERE user_id = :user_id
    ");

    $stmtFavoritos->execute([
        ":user_id" => (int) $_SESSION["utilizador_id"]
    ]);

    $idsFavoritos = array_map(
        "intval",
        $stmtFavoritos->fetchAll(PDO::FETCH_COLUMN)
    );
}

/*
|--------------------------------------------------------------------------
| Filtros recebidos pela URL
|--------------------------------------------------------------------------
*/

$pesquisa = trim($_GET["pesquisa"] ?? "");

$categoriaId = filter_input(
    INPUT_GET,
    "categoria",
    FILTER_VALIDATE_INT
);

$ordenacao = $_GET["ordenacao"] ?? "recentes";
$destaque = isset($_GET["destaque"]) && $_GET["destaque"] === "1";


$ordenacoesPermitidas = [
    "recentes",
    "preco_asc",
    "preco_desc",
    "nome_asc",
    "nome_desc"
];

if (!in_array($ordenacao, $ordenacoesPermitidas, true)) {
    $ordenacao = "recentes";
}

/*
|--------------------------------------------------------------------------
| Categorias
|--------------------------------------------------------------------------
*/

$sqlCategorias = "
    SELECT
        categories.id,
        categories.nome,
        COUNT(products.id) AS total_produtos
    FROM categories
    LEFT JOIN products
        ON products.categoria_id = categories.id
       AND products.ativo = 1
    GROUP BY categories.id, categories.nome
    HAVING total_produtos > 0
    ORDER BY categories.nome ASC
";

$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute();

$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Construção da consulta dos produtos
|--------------------------------------------------------------------------
*/

$sqlProdutos = "
    SELECT
        products.id,
        products.nome,
        products.descricao,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca,
        products.plataforma,
        products.genero,
        products.destaque,
        products.criado_em,
        categories.nome AS categoria,

        (
            SELECT GROUP_CONCAT(
                product_platforms.plataforma
                ORDER BY product_platforms.plataforma
                SEPARATOR ', '
            )
            FROM product_platforms
            WHERE product_platforms.product_id = products.id
        ) AS plataformas_disponiveis,

        (
            SELECT MIN(
                CASE
                    WHEN product_platforms.preco_promocional IS NOT NULL
                     AND product_platforms.preco_promocional
                         < product_platforms.preco
                    THEN product_platforms.preco_promocional
                    ELSE product_platforms.preco
                END
            )
            FROM product_platforms
            WHERE product_platforms.product_id = products.id
        ) AS menor_preco

    FROM products

    INNER JOIN categories
        ON products.categoria_id = categories.id

    WHERE products.ativo = 1
";

$parametros = [];

if ($pesquisa !== "") {
    $sqlProdutos .= "
        AND (
            products.nome LIKE :pesquisa
            OR products.descricao LIKE :pesquisa
            OR products.marca LIKE :pesquisa
            OR products.genero LIKE :pesquisa
        )
    ";

    $parametros[":pesquisa"] = "%" . $pesquisa . "%";
}

if ($categoriaId) {
    $sqlProdutos .= "
        AND products.categoria_id = :categoria_id
    ";

    $parametros[":categoria_id"] = $categoriaId;
}

if ($destaque) {
    $sqlProdutos .= "
        AND products.destaque = 1
    ";
}

/*
|--------------------------------------------------------------------------
| Ordenação
|--------------------------------------------------------------------------
*/

$sqlProdutos .= match ($ordenacao) {
    "preco_asc" => "
        ORDER BY
            COALESCE(
                menor_preco,
                products.preco_promocional,
                products.preco
            ) ASC
    ",

    "preco_desc" => "
        ORDER BY
            COALESCE(
                menor_preco,
                products.preco_promocional,
                products.preco
            ) DESC
    ",

    "nome_asc" => "
        ORDER BY products.nome ASC
    ",

    "nome_desc" => "
        ORDER BY products.nome DESC
    ",

    default => "
        ORDER BY products.criado_em DESC
    "
};


$stmtProdutos = $conn->prepare($sqlProdutos);
$stmtProdutos->execute($parametros);

$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header.php";
?>

<section class="catalog-page">

    <div class="container">

        <div class="catalog-header">

            <div>

                <span class="section-eyebrow">
                    Explora a GamerHub
                </span>

                <h1>
                 <?= $destaque ? "Produtos em destaque" : "Todos os produtos" ?>
                </h1>

                <p>
                    Descobre jogos, equipamento e acessórios para construíres
                    o teu setup.
                </p>

            </div>

            <div class="catalog-result-count">

                <strong>
                    <?= count($produtos) ?>
                </strong>

                <?= count($produtos) === 1
                    ? "produto encontrado"
                    : "produtos encontrados" ?>

            </div>

        </div>

        <!-- Pesquisa e filtros -->

        <form
            action="produtos.php"
            method="get"
            class="catalog-filters"
        >

            <div class="catalog-search">

                <i class="bi bi-search"></i>

                <input
                    type="search"
                    name="pesquisa"
                    value="<?= htmlspecialchars($pesquisa) ?>"
                    placeholder="Pesquisar jogos e produtos..."
                    aria-label="Pesquisar produtos"
                >

            </div>

            <div class="catalog-select-group">

                <label for="categoria">
                    Categoria
                </label>

                <select
                    id="categoria"
                    name="categoria"
                >

                    <option value="">
                        Todas as categorias
                    </option>

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?= (int) $categoria["id"] ?>"
                            <?= $categoriaId === (int) $categoria["id"]
                                ? "selected"
                                : "" ?>
                        >
                            <?= htmlspecialchars($categoria["nome"]) ?>

                            (<?= (int) $categoria["total_produtos"] ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="catalog-select-group">

                <label for="ordenacao">
                    Ordenar por
                </label>

                <select
                    id="ordenacao"
                    name="ordenacao"
                >

                    <option
                        value="recentes"
                        <?= $ordenacao === "recentes"
                            ? "selected"
                            : "" ?>
                    >
                        Mais recentes
                    </option>

                    <option
                        value="preco_asc"
                        <?= $ordenacao === "preco_asc"
                            ? "selected"
                            : "" ?>
                    >
                        Preço: mais baixo
                    </option>

                    <option
                        value="preco_desc"
                        <?= $ordenacao === "preco_desc"
                            ? "selected"
                            : "" ?>
                    >
                        Preço: mais alto
                    </option>

                    <option
                        value="nome_asc"
                        <?= $ordenacao === "nome_asc"
                            ? "selected"
                            : "" ?>
                    >
                        Nome: A–Z
                    </option>

                    <option
                        value="nome_desc"
                        <?= $ordenacao === "nome_desc"
                            ? "selected"
                            : "" ?>
                    >
                        Nome: Z–A
                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="btn catalog-filter-button"
            >
                Aplicar filtros
            </button>

            <?php if (
                $pesquisa !== ""
                || $categoriaId
                || $ordenacao !== "recentes"
            ): ?>

                <a
                    href="produtos.php"
                    class="catalog-clear-button"
                >
                    <i class="bi bi-x-lg"></i>
                    Limpar
                </a>

            <?php endif; ?>

        </form>

        <!-- Produtos -->

        <?php if (empty($produtos)): ?>

            <div class="catalog-empty">

                <i class="bi bi-search"></i>

                <h2>Nenhum produto encontrado</h2>

                <p>
                    Experimenta alterar a pesquisa ou remover alguns filtros.
                </p>

                <a
                    href="produtos.php"
                    class="btn btn-gamer-primary"
                >
                    Ver todos os produtos
                </a>

            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($produtos as $produto): ?>

                    <?php

                    $precoBase = (float) $produto["preco"];

                    $temPromocao =
                        !empty($produto["preco_promocional"])
                        && (float) $produto["preco_promocional"]
                            < $precoBase;

                    $precoProduto = $temPromocao
                        ? (float) $produto["preco_promocional"]
                        : $precoBase;

                    if (
                        $produto["menor_preco"] !== null
                        && (float) $produto["menor_preco"] > 0
                    ) {
                        $precoProduto =
                            (float) $produto["menor_preco"];
                    }

                    $percentagemDesconto = $temPromocao
                        ? round(
                            (
                                (
                                    $precoBase
                                    - (float) $produto["preco_promocional"]
                                )
                                / $precoBase
                            ) * 100
                        )
                        : 0;

                    ?>

                    <div class="col-sm-6 col-lg-4 col-xl-3">

                        <article class="catalog-product-card">

                            <div class="catalog-product-image">

                                <?php if ($temPromocao): ?>

                                    <span class="discount-badge">

                                        -<?= $percentagemDesconto ?>%

                                    </span>

                                <?php elseif ((int) $produto["destaque"] === 1): ?>

                                    <span class="featured-badge">
                                        Destaque
                                    </span>

                                <?php endif; ?>

<?php
$produtoFavorito = in_array(
    (int) $produto["id"],
    $idsFavoritos,
    true
);
?>

<button
    type="button"
    class="wishlist-button <?= $produtoFavorito ? "active" : "" ?>"
    data-product-id="<?= (int) $produto["id"] ?>"
    aria-label="<?= $produtoFavorito
        ? "Remover dos favoritos"
        : "Adicionar aos favoritos" ?>"
>
    <i class="bi <?= $produtoFavorito
        ? "bi-heart-fill"
        : "bi-heart" ?>"></i>
</button>

                                <a
                                    href="produto.php?id=<?= (int) $produto["id"] ?>"
                                >

                                    <?php if (!empty($produto["imagem"])): ?>

                                        <img
                                            src="<?= htmlspecialchars(
                                                $produto["imagem"]
                                            ) ?>?v=<?= time() ?>"
                                            alt="<?= htmlspecialchars(
                                                $produto["nome"]
                                            ) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="product-placeholder">

                                            <i class="bi bi-controller"></i>

                                        </div>

                                    <?php endif; ?>

                                </a>

                            </div>

                            <div class="catalog-product-content">

                                <div class="catalog-product-meta">

                                    <span>
                                        <?= htmlspecialchars(
                                            $produto["marca"] ?: "GamerHub"
                                        ) ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars(
                                            $produto["categoria"]
                                        ) ?>
                                    </span>

                                </div>

                                <h2>

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                    >
                                        <?= htmlspecialchars(
                                            $produto["nome"]
                                        ) ?>
                                    </a>

                                </h2>

                                <?php if (
                                    !empty(
                                        $produto["plataformas_disponiveis"]
                                    )
                                ): ?>

                                    <div class="catalog-platforms">

                                        <i class="bi bi-controller"></i>

                                        <?= htmlspecialchars(
                                            $produto[
                                                "plataformas_disponiveis"
                                            ]
                                        ) ?>

                                    </div>

                                <?php elseif (
                                    !empty($produto["plataforma"])
                                ): ?>

                                    <div class="catalog-platforms">

                                        <i class="bi bi-controller"></i>

                                        <?= htmlspecialchars(
                                            $produto["plataforma"]
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                                <div class="product-rating">

                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>

                                    <span>4.8</span>

                                </div>

                                <div class="catalog-product-bottom">

                                    <div class="product-price">

                                        <?php if ($temPromocao): ?>

                                            <span class="old-price">

                                                <?= number_format(
                                                    $precoBase,
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €

                                            </span>

                                        <?php endif; ?>

                                        <?php if (
                                            !empty(
                                                $produto[
                                                    "plataformas_disponiveis"
                                                ]
                                            )
                                        ): ?>

                                            <small>A partir de</small>

                                        <?php endif; ?>

                                        <strong>

                                            <?= number_format(
                                                $precoProduto,
                                                2,
                                                ",",
                                                "."
                                            ) ?> €

                                        </strong>

                                    </div>

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                        class="catalog-product-button"
                                        aria-label="Ver <?= htmlspecialchars(
                                            $produto["nome"]
                                        ) ?>"
                                    >
                                        <i class="bi bi-arrow-right"></i>
                                    </a>

                                </div>

                                <span
                                    class="<?= (int) $produto["stock"] > 0
                                        ? "stock-available"
                                        : "stock-unavailable" ?>"
                                >

                                    <i class="bi bi-circle-fill"></i>

                                    <?= (int) $produto["stock"] > 0
                                        ? "Em stock"
                                        : "Esgotado" ?>

                                </span>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>