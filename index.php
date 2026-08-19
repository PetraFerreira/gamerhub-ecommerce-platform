<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Produtos em destaque
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
        categories.nome AS categoria
    FROM products
    INNER JOIN categories
        ON products.categoria_id = categories.id
    WHERE products.ativo = 1
      AND products.destaque = 1
    ORDER BY products.criado_em DESC
    LIMIT 8
";

$stmtProdutos = $conn->prepare($sqlProdutos);
$stmtProdutos->execute();

$produtosDestaque = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

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
    ORDER BY categories.nome ASC
    LIMIT 8
";

$stmtCategorias = $conn->prepare($sqlCategorias);
$stmtCategorias->execute();

$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Jogos do carrossel infinito
|--------------------------------------------------------------------------
*/

$jogos = [
    [
        "titulo" => "Spider-Man Remastered",
        "imagem" => "assets/images/games/spiderman-2.jpg"
    ],
    [
        "titulo" => "God of War Ragnarök",
        "imagem" => "assets/images/games/god-of-war-ragnarok.jpg"
    ],
    [
        "titulo" => "The Last of Us Part I",
        "imagem" => "assets/images/games/the-last-of-us-part-1.jpg"
    ],
    [
        "titulo" => "Horizon Forbidden West",
        "imagem" => "assets/images/games/horizon-forbidden-west.jpg"
    ],
    [
        "titulo" => "Gran Turismo 7",
        "imagem" => "assets/images/games/gran-turismo-7.jpg"
    ],
    [
        "titulo" => "Ghost of Tsushima",
        "imagem" => "assets/images/games/ghost-of-tsushima.jpg"
    ]
];

require_once "includes/header.php";
?>


<section class="launch-hero">
    <div
        id="launchCarousel"
        class="carousel slide carousel-fade"
        data-bs-ride="carousel"
        data-bs-interval="6000"
        data-bs-pause="false"
    >
        <div class="carousel-indicators">
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="0" class="active"
                aria-current="true"
                aria-label="Grand Theft Auto VI"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="1"
                aria-label="Marvel's Wolverine"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="2"
                aria-label="Resident Evil Requiem"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="3"
                aria-label="Until Dawn 2"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="4"
                aria-label="007 First Light"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="5"
                aria-label="Nioh 3"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="6"
                aria-label="Silent Hill: Townfall"
            ></button>
            <button
                type="button"
                data-bs-target="#launchCarousel"
                data-bs-slide-to="7"
                aria-label="Metal Gear Solid: Master Collection Vol. 2"
            ></button>
        </div>

        <div class="carousel-inner">
            <article class="carousel-item active">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/gta-vi.jpg?v=2');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Lançamento de 2026
                            </span>

                            <h1>Grand Theft Auto VI</h1>

                            <p>
                                Regressa a Vice City numa nova geração de mundo aberto, ação e liberdade.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Explorar jogos
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#jogos-geracao"
                                    class="btn btn-launch-secondary"
                                >
                                    Ver destaques
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/wolverine.jpg?v=2');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                PlayStation 5
                            </span>

                            <h2>Marvel's Wolverine</h2>

                            <p>
                                Uma aventura intensa e cinematográfica protagonizada por um dos heróis mais ferozes da Marvel.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php?categoria=6"
                                    class="btn btn-launch-primary"
                                >
                                    Ver jogos PS5
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#jogos-geracao"
                                    class="btn btn-launch-secondary"
                                >
                                    Conhecer destaques
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/resident-evil-requiem.jpg');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Terror de nova geração
                            </span>

                            <h2>Resident Evil Requiem</h2>

                            <p>
                                Sobrevive a uma nova experiência de terror, mistério e ação.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Explorar catálogo
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#destaques"
                                    class="btn btn-launch-secondary"
                                >
                                    Produtos em destaque
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/until-dawn-2.jpg?v=2');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Previsto para 2027
                            </span>

                            <h2>Until Dawn 2</h2>

                            <p>
                                Um novo grupo de caçadores de fantasmas enfrenta fenómenos aterradores numa ilha remota.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Descobrir novidades
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#jogos-geracao"
                                    class="btn btn-launch-secondary"
                                >
                                    Jogos de terror
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/007-first-light.jpg');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Ação e espionagem
                            </span>

                            <h2>007 First Light</h2>

                            <p>
                                Descobre a história de origem de um jovem James Bond e conquista o teu lugar entre os agentes de elite do MI6.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Explorar jogo
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#destaques"
                                    class="btn btn-launch-secondary"
                                >
                                    Ver destaques
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/nioh-3.jpg?v=2');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                RPG de ação
                            </span>

                            <h2>Nioh 3</h2>

                            <p>
                                Domina estilos de combate samurai e ninja numa aventura sombria repleta de yokai e batalhas brutais.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Ver edição
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#jogos-geracao"
                                    class="btn btn-launch-secondary"
                                >
                                    Mais RPGs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/silent-hill-townfall.jpg');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Terror psicológico
                            </span>

                            <h2>Silent Hill: Townfall</h2>

                            <p>
                                Uma nova experiência de terror psicológico envolta em mistério, isolamento e inquietação.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Descobrir jogo
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#destaques"
                                    class="btn btn-launch-secondary"
                                >
                                    Jogos de terror
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="carousel-item">
                <div
                    class="launch-slide"
                    style="
                        background-image:
                            linear-gradient(
                                90deg,
                                rgba(5, 5, 12, 0.96) 0%,
                                rgba(5, 5, 12, 0.72) 40%,
                                rgba(5, 5, 12, 0.15) 75%
                            ),
                            url('assets/images/banners/metal-gear-master-collection-2.jpg?v=2');
                    "
                >
                    <div class="container">
                        <div class="launch-content">
                            <span class="launch-label">
                                Coleção clássica
                            </span>

                            <h2>Metal Gear Solid: Master Collection Vol. 2</h2>

                            <p>
                                Revive missões lendárias de espionagem tática numa coleção criada para uma nova geração de jogadores.
                            </p>

                            <div class="launch-actions">
                                <a
                                    href="produtos.php"
                                    class="btn btn-launch-primary"
                                >
                                    Explorar coleção
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                <a
                                    href="#jogos-geracao"
                                    class="btn btn-launch-secondary"
                                >
                                    Mais clássicos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <button
            class="carousel-control-prev"
            type="button"
            data-bs-target="#launchCarousel"
            data-bs-slide="prev"
            aria-label="Banner anterior"
        >
            <span class="launch-control">
                <i class="bi bi-chevron-left"></i>
            </span>
        </button>

        <button
            class="carousel-control-next"
            type="button"
            data-bs-target="#launchCarousel"
            data-bs-slide="next"
            aria-label="Banner seguinte"
        >
            <span class="launch-control">
                <i class="bi bi-chevron-right"></i>
            </span>
        </button>
    </div>
</section>

<!-- =========================================================
     JOGOS QUE DEFINEM UMA GERAÇÃO
========================================================= -->

<section
    id="jogos-geracao"
    class="generation-section"
>

    <div class="container">

        <div class="games-showcase">

            <div class="games-showcase-header">

                <div>

                    <span class="section-eyebrow">
                        PlayStation 5
                    </span>

                    <h2>
                        Jogos que definem uma geração.
                    </h2>

                    <p>
                        Descobre alguns dos títulos mais marcantes disponíveis
                        para a PlayStation 5.
                    </p>

                </div>

                <a
                    href="produtos.php"
                    class="section-link"
                >
                    Ver todos

                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

            <div class="games-carousel">

                <div class="games-track">

                    <?php foreach (array_merge($jogos, $jogos) as $jogo): ?>

                        <article class="game-cover-card">

                            <img
                                src="<?= htmlspecialchars($jogo["imagem"]) ?>"
                                alt="<?= htmlspecialchars($jogo["titulo"]) ?>"
                            >

                            <div class="game-cover-overlay">

                                <span>PS5</span>

                                <h3>
                                    <?= htmlspecialchars($jogo["titulo"]) ?>
                                </h3>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- =========================================================
     MARCAS
========================================================= -->

<section class="brands-section">

    <div class="container">

        <p class="brands-label">
            As marcas favoritas da comunidade gaming
        </p>

<div class="brands-showcase">

    <div class="brands-showcase-heading">

        <span class="brands-showcase-star">
            <i class="bi bi-star-fill"></i>
        </span>

        <div>
            <strong>Mais de 50 marcas oficiais</strong>

            <p>
                Tecnologia das marcas mais reconhecidas pela comunidade gaming.
            </p>
        </div>

        <span class="brands-showcase-star">
            <i class="bi bi-star-fill"></i>
        </span>

    </div>

    <div class="brands-list">

        <a
            href="produtos.php?pesquisa=Logitech"
            class="brand-item brand-logitech"
        >
            <span>LOGITECH G</span>
        </a>

        <a
            href="produtos.php?pesquisa=Razer"
            class="brand-item brand-razer"
        >
            <span>RAZER</span>
        </a>

        <a
            href="produtos.php?pesquisa=ASUS"
            class="brand-item brand-asus"
        >
            <span>ASUS ROG</span>
        </a>

        <a
            href="produtos.php?pesquisa=HyperX"
            class="brand-item brand-hyperx"
        >
            <span>HYPERX</span>
        </a>

        <a
            href="produtos.php?pesquisa=SteelSeries"
            class="brand-item brand-steelseries"
        >
            <span>STEELSERIES</span>
        </a>

        <a
            href="produtos.php?pesquisa=Corsair"
            class="brand-item brand-corsair"
        >
            <span>CORSAIR</span>
        </a>

    </div>

    <div class="brands-trust-row">

        <span>
            <i class="bi bi-patch-check"></i>
            Produtos originais
        </span>

        <span>
            <i class="bi bi-shield-check"></i>
            Garantia oficial
        </span>

        <span>
            <i class="bi bi-truck"></i>
            Entrega rápida
        </span>

    </div>

</div>

    </div>

</section>

<!-- =========================================================
     CATEGORIAS
========================================================= -->

<section class="section-padding">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    Explora
                </span>

                <h2>
                    Compra por categoria
                </h2>

            </div>

            <a
                href="produtos.php"
                class="section-link"
            >
                Ver todas

                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

        <div class="row g-4">

            <?php foreach ($categorias as $categoria): ?>

                <?php

                $iconeCategoria = match ($categoria["nome"]) {
                    "Teclados" => "bi-keyboard",
                    "Ratos" => "bi-mouse",
                    "Headsets" => "bi-headset",
                    "Monitores" => "bi-display",
                    "Cadeiras Gaming" => "bi-person-workspace",
                    "Consolas" => "bi-controller",
                    "Comandos" => "bi-dpad",
                    "Componentes" => "bi-pc-display",
                    "Streaming" => "bi-broadcast",
                    "Iluminação RGB" => "bi-lightbulb",
                    default => "bi-grid"
                };

                ?>

                <div class="col-6 col-md-4 col-lg-3">

                    <a
                        href="produtos.php?categoria=<?= (int) $categoria["id"] ?>"
                        class="category-card"
                    >

                        <div class="category-icon">

                            <i class="bi <?= $iconeCategoria ?>"></i>

                        </div>

                        <div>

                            <h3>
                                <?= htmlspecialchars($categoria["nome"]) ?>
                            </h3>

                            <span>

                                <?= (int) $categoria["total_produtos"] ?>

                                <?= (int) $categoria["total_produtos"] === 1
                                    ? "produto"
                                    : "produtos" ?>

                            </span>

                        </div>

                        <i class="bi bi-arrow-up-right category-arrow"></i>

                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- =========================================================
     PRODUTOS EM DESTAQUE
========================================================= -->

<section
    id="destaques"
    class="section-padding products-section"
>

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    Escolhidos para ti
                </span>

                <h2>
                    Produtos em destaque
                </h2>

            </div>

            <a
                href="produtos.php?destaque=1"
                class="section-link"
            >
                Ver todos

                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

        <div class="row g-4">

            <?php if (empty($produtosDestaque)): ?>

                <div class="col-12">

                    <div class="empty-state">

                        <i class="bi bi-controller"></i>

                        <h3>
                            Ainda não existem produtos em destaque.
                        </h3>

                        <p>
                            Adiciona produtos através da área administrativa.
                        </p>

                    </div>

                </div>

            <?php else: ?>

                <?php foreach ($produtosDestaque as $produto): ?>

                    <?php

                    $temPromocao =
                        !empty($produto["preco_promocional"])
                        && (float) $produto["preco_promocional"]
                            < (float) $produto["preco"];

                    $precoAtual = $temPromocao
                        ? (float) $produto["preco_promocional"]
                        : (float) $produto["preco"];

                    $percentagemDesconto = $temPromocao
                        ? round(
                            (
                                (
                                    (float) $produto["preco"]
                                    - (float) $produto["preco_promocional"]
                                )
                                / (float) $produto["preco"]
                            ) * 100
                        )
                        : 0;

                    ?>

                    <div class="col-sm-6 col-lg-3">

                        <article class="product-card">

                            <div class="product-image">

                                <?php if ($temPromocao): ?>

                                    <span class="discount-badge">

                                        -<?= $percentagemDesconto ?>%

                                    </span>

                                <?php endif; ?>

                                <button
                                    class="wishlist-button"
                                    type="button"
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

                                    <div class="product-placeholder">

                                        <i class="bi bi-controller"></i>

                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="product-content">

                                <div class="product-meta">

                                    <span>
                                        <?= htmlspecialchars($produto["marca"]) ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars($produto["categoria"]) ?>
                                    </span>

                                </div>

                                <h3>

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                    >
                                        <?= htmlspecialchars($produto["nome"]) ?>
                                    </a>

                                </h3>

                                <div class="product-rating">

                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>

                                    <span>4.8</span>

                                </div>

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

            <?php endif; ?>

        </div>

    </div>

</section>

<!-- =========================================================
     VANTAGENS
========================================================= -->

<section class="section-padding">

    <div class="container">

        <div class="advantages-grid">

            <div class="advantage-item">

                <div>
                    <i class="bi bi-truck"></i>
                </div>

                <h3>Entrega rápida</h3>

                <p>
                    Recebe a tua encomenda em 24 a 48 horas úteis.
                </p>

            </div>

            <div class="advantage-item">

                <div>
                    <i class="bi bi-shield-check"></i>
                </div>

                <h3>Compra protegida</h3>

                <p>
                    Pagamentos seguros e proteção dos teus dados.
                </p>

            </div>

            <div class="advantage-item">

                <div>
                    <i class="bi bi-award"></i>
                </div>

                <h3>Garantia oficial</h3>

                <p>
                    Produtos originais com garantia válida em Portugal.
                </p>

            </div>

            <div class="advantage-item">

                <div>
                    <i class="bi bi-headset"></i>
                </div>

                <h3>Suporte especializado</h3>

                <p>
                    Uma equipa preparada para ajudar em todas as fases.
                </p>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     TESTEMUNHOS
========================================================= -->

<section class="testimonials-section">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    Comunidade GamerHub
                </span>

                <h2>
                    O que dizem os nossos clientes
                </h2>

            </div>

        </div>

        <div class="row g-4">

            <div class="col-lg-4">

                <article class="testimonial-card">

                    <div class="testimonial-stars">

                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>

                    </div>

                    <p>

                        "Comprei o meu novo teclado mecânico e chegou muito
                        rapidamente. O website é extremamente intuitivo."

                    </p>

                    <div class="testimonial-user">

                        <div class="testimonial-avatar">

                            MR

                        </div>

                        <div>

                            <strong>Miguel Rodrigues</strong>

                            <span>Cliente Verificado</span>

                        </div>

                    </div>

                </article>

            </div>

            <div class="col-lg-4">

                <article class="testimonial-card">

                    <div class="testimonial-stars">

                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>

                    </div>

                    <p>

                        "Gostei bastante da organização por categorias e da
                        possibilidade de acompanhar todas as encomendas."

                    </p>

                    <div class="testimonial-user">

                        <div class="testimonial-avatar">

                            CS

                        </div>

                        <div>

                            <strong>Carla Sousa</strong>

                            <span>Cliente Verificado</span>

                        </div>

                    </div>

                </article>

            </div>

            <div class="col-lg-4">

                <article class="testimonial-card">

                    <div class="testimonial-stars">

                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>

                    </div>

                    <p>

                        "Excelente experiência. O design da loja é moderno,
                        rápido e muito agradável de utilizar."

                    </p>

                    <div class="testimonial-user">

                        <div class="testimonial-avatar">

                            JP

                        </div>

                        <div>

                            <strong>João Pereira</strong>

                            <span>Cliente Verificado</span>

                        </div>

                    </div>

                </article>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     NEWSLETTER
========================================================= -->

<section class="newsletter-section">

    <div class="container">

        <div class="newsletter-box">

            <div class="newsletter-glow"></div>

            <div class="newsletter-content">

                <span class="section-eyebrow">
                    Junta-te à comunidade
                </span>

                <h2>
                    Não percas nenhuma promoção.
                </h2>

                <p>
                    Recebe novidades, lançamentos e ofertas exclusivas
                    diretamente no teu email.
                </p>

            </div>

            <form
                class="newsletter-form"
                action="#"
                method="post"
            >

                <label
                    class="visually-hidden"
                    for="newsletter-email"
                >
                    Endereço de email
                </label>

                <input
                    id="newsletter-email"
                    type="email"
                    name="email"
                    placeholder="O teu endereço de email"
                    required
                >

                <button type="submit">

                    Subscrever

                    <i class="bi bi-send"></i>

                </button>

            </form>

        </div>

    </div>

</section>

</section>


<?php require_once "includes/footer.php"; ?>