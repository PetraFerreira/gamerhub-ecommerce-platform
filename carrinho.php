<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION["carrinho"])) {
    $_SESSION["carrinho"] = [];
}


function criarChaveCarrinho(int $produtoId, ?int $plataformaId = null): string
{
    return $plataformaId
        ? "produto-{$produtoId}-plataforma-{$plataformaId}"
        : "produto-{$produtoId}";
}


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["adicionar_carrinho"])
) {
    $produtoId = filter_input(INPUT_POST, "produto_id", FILTER_VALIDATE_INT);
    $plataformaId = filter_input(INPUT_POST, "plataforma_id", FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, "quantidade", FILTER_VALIDATE_INT);

    if (!$quantidade || $quantidade < 1) {
        $quantidade = 1;
    }

    if ($produtoId) {
        $stmtProduto = $conn->prepare("
            SELECT id, nome, preco, preco_promocional, imagem, stock, ativo
            FROM products
            WHERE id = :produto_id AND ativo = 1
            LIMIT 1
        ");
        $stmtProduto->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        $stmtProduto->execute();
        $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $stockDisponivel = (int) $produto["stock"];

            if ($plataformaId) {
                $stmtPlataforma = $conn->prepare("
                    SELECT id, plataforma, preco, preco_promocional, stock
                    FROM product_platforms
                    WHERE id = :plataforma_id
                      AND product_id = :produto_id
                    LIMIT 1
                ");
                $stmtPlataforma->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
                $stmtPlataforma->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
                $stmtPlataforma->execute();
                $plataformaSelecionada = $stmtPlataforma->fetch(PDO::FETCH_ASSOC);

                if (!$plataformaSelecionada) {
                    $_SESSION["mensagem_carrinho"] = "A plataforma selecionada não é válida.";
                    header("Location: produto.php?id=" . $produtoId);
                    exit;
                }

                $stockDisponivel = (int) $plataformaSelecionada["stock"];
            }

            if ($stockDisponivel > 0) {
                $chaveCarrinho = criarChaveCarrinho($produtoId, $plataformaId ?: null);
                $quantidadeAtual = (int) ($_SESSION["carrinho"][$chaveCarrinho] ?? 0);
                $_SESSION["carrinho"][$chaveCarrinho] = min(
                    $quantidadeAtual + $quantidade,
                    $stockDisponivel
                );
                $_SESSION["mensagem_carrinho"] = "Produto adicionado ao carrinho.";
            } else {
                $_SESSION["mensagem_carrinho"] = "Este produto está esgotado.";
            }
        }
    }

    header("Location: carrinho.php");
    exit;
}



if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["atualizar_carrinho"])
) {
    $quantidades = $_POST["quantidades"] ?? [];

    foreach ($quantidades as $chaveCarrinho => $quantidade) {
        if (!isset($_SESSION["carrinho"][$chaveCarrinho])) {
            continue;
        }

        $quantidade = filter_var($quantidade, FILTER_VALIDATE_INT);

        if (!$quantidade || $quantidade < 1) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        if (!preg_match('/^produto-(\d+)(?:-plataforma-(\d+))?$/', $chaveCarrinho, $m)) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        $produtoId = (int) $m[1];
        $plataformaId = isset($m[2]) ? (int) $m[2] : null;

        if ($plataformaId) {
            $stmtStock = $conn->prepare("
                SELECT product_platforms.stock
                FROM product_platforms
                INNER JOIN products ON products.id = product_platforms.product_id
                WHERE product_platforms.id = :plataforma_id
                  AND product_platforms.product_id = :produto_id
                  AND products.ativo = 1
                LIMIT 1
            ");
            $stmtStock->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
            $stmtStock->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        } else {
            $stmtStock = $conn->prepare("
                SELECT stock
                FROM products
                WHERE id = :produto_id AND ativo = 1
                LIMIT 1
            ");
            $stmtStock->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        }

        $stmtStock->execute();
        $stock = $stmtStock->fetchColumn();

        if ($stock === false || (int) $stock <= 0) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        $_SESSION["carrinho"][$chaveCarrinho] = min($quantidade, (int) $stock);
    }

    $_SESSION["mensagem_carrinho"] = "Carrinho atualizado com sucesso.";
    header("Location: carrinho.php");
    exit;
}



$removerChave = $_GET["remover"] ?? "";

if ($removerChave !== "" && isset($_SESSION["carrinho"][$removerChave])) {
    unset($_SESSION["carrinho"][$removerChave]);
    $_SESSION["mensagem_carrinho"] = "Produto removido do carrinho.";
    header("Location: carrinho.php");
    exit;
}



if (isset($_GET["esvaziar"])) {
    $_SESSION["carrinho"] = [];
    $_SESSION["mensagem_carrinho"] = "O carrinho foi esvaziado.";
    header("Location: carrinho.php");
    exit;
}



$produtosCarrinho = [];
$subtotalCarrinho = 0;

foreach ($_SESSION["carrinho"] as $chaveCarrinho => $quantidade) {
    if (!preg_match('/^produto-(\d+)(?:-plataforma-(\d+))?$/', $chaveCarrinho, $m)) {
        continue;
    }

    $produtoId = (int) $m[1];
    $plataformaId = isset($m[2]) ? (int) $m[2] : null;

    $stmtCarrinho = $conn->prepare("
        SELECT
            products.id,
            products.nome,
            products.preco,
            products.preco_promocional,
            products.imagem,
            products.stock,
            products.marca,
            categories.nome AS categoria
        FROM products
        INNER JOIN categories ON products.categoria_id = categories.id
        WHERE products.id = :produto_id
          AND products.ativo = 1
        LIMIT 1
    ");
    $stmtCarrinho->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
    $stmtCarrinho->execute();
    $produto = $stmtCarrinho->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        continue;
    }

    $produto["plataforma_id"] = null;
    $produto["plataforma"] = null;

    if ($plataformaId) {
        $stmtPlataforma = $conn->prepare("
            SELECT id, plataforma, preco, preco_promocional, stock
            FROM product_platforms
            WHERE id = :plataforma_id
              AND product_id = :produto_id
            LIMIT 1
        ");
        $stmtPlataforma->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
        $stmtPlataforma->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        $stmtPlataforma->execute();
        $variante = $stmtPlataforma->fetch(PDO::FETCH_ASSOC);

        if (!$variante) {
            continue;
        }

        $produto["plataforma_id"] = (int) $variante["id"];
        $produto["plataforma"] = $variante["plataforma"];
        $produto["preco"] = $variante["preco"];
        $produto["preco_promocional"] = $variante["preco_promocional"];
        $produto["stock"] = $variante["stock"];
    }

    $quantidade = min((int) $quantidade, (int) $produto["stock"]);

    if ($quantidade < 1) {
        continue;
    }

    $precoUnitario =
        !empty($produto["preco_promocional"])
        && (float) $produto["preco_promocional"] < (float) $produto["preco"]
            ? (float) $produto["preco_promocional"]
            : (float) $produto["preco"];

    $subtotal = $precoUnitario * $quantidade;

    $produto["chave_carrinho"] = $chaveCarrinho;
    $produto["quantidade"] = $quantidade;
    $produto["preco_unitario"] = $precoUnitario;
    $produto["subtotal"] = $subtotal;

    $subtotalCarrinho += $subtotal;
    $produtosCarrinho[] = $produto;
}

$portes = $subtotalCarrinho >= 50 || $subtotalCarrinho === 0 ? 0 : 4.99;
$totalCarrinho = $subtotalCarrinho + $portes;
$mensagemCarrinho = $_SESSION["mensagem_carrinho"] ?? null;
unset($_SESSION["mensagem_carrinho"]);

require_once "includes/header.php";
?>

<section class="cart-page">

    <div class="container">

        <div class="cart-heading">

            <div>
                <span class="section-eyebrow">
                    A tua seleção
                </span>

                <h1>Carrinho de compras</h1>
            </div>

            <?php if (!empty($produtosCarrinho)): ?>

                <a
                    href="carrinho.php?esvaziar=1"
                    class="empty-cart-link"
                    onclick="return confirm(
                        'Tens a certeza de que pretendes esvaziar o carrinho?'
                    );"
                >
                    <i class="bi bi-trash"></i>
                    Esvaziar carrinho
                </a>

            <?php endif; ?>

        </div>

        <?php if ($mensagemCarrinho): ?>

            <div class="cart-message">
                <i class="bi bi-check-circle"></i>

                <?= htmlspecialchars($mensagemCarrinho) ?>
            </div>

        <?php endif; ?>

        <?php if (empty($produtosCarrinho)): ?>

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    <i class="bi bi-cart-x"></i>
                </div>

                <h2>O teu carrinho está vazio</h2>

                <p>
                    Explora a GamerHub e encontra o próximo equipamento
                    para o teu setup.
                </p>

                <a
                    href="produtos.php"
                    class="btn btn-gamer-primary"
                >
                    Explorar produtos
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

        <?php else: ?>

            <div class="row g-4">

                <div class="col-lg-8">

                    <form
                        action="carrinho.php"
                        method="post"
                    >

                        <div class="cart-products">

                            <?php foreach (
                                $produtosCarrinho as $produto
                            ): ?>

                                <article class="cart-product">

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                        class="cart-product-image"
                                    >

                                        <?php if (
                                            !empty($produto["imagem"])
                                        ): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                    $produto["imagem"]
                                                ) ?>?v=<?= time() ?>"
                                                alt="<?= htmlspecialchars(
                                                    $produto["nome"]
                                                ) ?>"
                                            >

                                        <?php else: ?>

                                            <i class="bi bi-controller"></i>

                                        <?php endif; ?>

                                    </a>

                                    <div class="cart-product-info">

                                        <div class="cart-product-meta">
                                            <?= htmlspecialchars(
                                                $produto["marca"]
                                            ) ?>

                                            <span>•</span>

                                            <?= htmlspecialchars(
                                                $produto["categoria"]
                                            ) ?>
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

                                        <?php if (!empty($produto["plataforma"])): ?>

                                            <div class="cart-product-platform">
                                                <i class="bi bi-controller"></i>
                                                <?= htmlspecialchars($produto["plataforma"]) ?>
                                            </div>

                                        <?php endif; ?>

                                        <span class="stock-available">
                                            <i class="bi bi-circle-fill"></i>
                                            Em stock
                                        </span>

                                        <a
                                            href="carrinho.php?remover=<?= urlencode($produto["chave_carrinho"]) ?>"
                                            class="remove-cart-product"
                                            onclick="return confirm(
                                                'Remover este produto do carrinho?'
                                            );"
                                        >
                                            <i class="bi bi-trash"></i>
                                            Remover
                                        </a>

                                    </div>

                                    <div class="cart-product-actions">

                                        <label
                                            for="quantidade-<?= htmlspecialchars($produto["chave_carrinho"]) ?>"
                                        >
                                            Quantidade
                                        </label>

                                        <input
                                            id="quantidade-<?= htmlspecialchars($produto["chave_carrinho"]) ?>"
                                            type="number"
                                            name="quantidades[<?= htmlspecialchars($produto["chave_carrinho"]) ?>]"
                                            value="<?= (int) $produto["quantidade"] ?>"
                                            min="1"
                                            max="<?= (int) $produto["stock"] ?>"
                                        >

                                        <div class="cart-product-price">

                                            <small>
                                                <?= number_format(
                                                    (float) $produto["preco_unitario"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> € / unidade
                                            </small>

                                            <strong>
                                                <?= number_format(
                                                    (float) $produto["subtotal"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €
                                            </strong>

                                        </div>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                        <div class="cart-update-row">

                            <a
                                href="produtos.php"
                                class="continue-shopping"
                            >
                                <i class="bi bi-arrow-left"></i>
                                Continuar a comprar
                            </a>

                            <button
                                type="submit"
                                name="atualizar_carrinho"
                                class="btn update-cart-button"
                            >
                                <i class="bi bi-arrow-repeat"></i>
                                Atualizar carrinho
                            </button>

                        </div>

                    </form>

                </div>

                <div class="col-lg-4">

                    <aside class="cart-summary">

                        <span class="section-eyebrow">
                            Resumo
                        </span>

                        <h2>Resumo da encomenda</h2>

                        <div class="summary-row">
                            <span>Subtotal</span>

                            <strong>
                                <?= number_format(
                                    $subtotalCarrinho,
                                    2,
                                    ",",
                                    "."
                                ) ?> €
                            </strong>
                        </div>

                        <div class="summary-row">
                            <span>Portes de envio</span>

                            <strong>
                                <?= $portes === 0
                                    ? "Grátis"
                                    : number_format(
                                        $portes,
                                        2,
                                        ",",
                                        "."
                                    ) . " €" ?>
                            </strong>
                        </div>

                        <?php if (
                            $portes > 0
                            && $subtotalCarrinho < 50
                        ): ?>

                            <div class="free-shipping-notice">

                                <i class="bi bi-truck"></i>

                                Faltam

                                <strong>
                                    <?= number_format(
                                        50 - $subtotalCarrinho,
                                        2,
                                        ",",
                                        "."
                                    ) ?> €
                                </strong>

                                para obteres portes grátis.

                            </div>

                        <?php endif; ?>

                        <div class="summary-total">

                            <span>Total</span>

                            <strong>
                                <?= number_format(
                                    $totalCarrinho,
                                    2,
                                    ",",
                                    "."
                                ) ?> €
                            </strong>

                        </div>

                        <a
                            href="checkout.php"
                            class="btn checkout-button"
                        >
                            Finalizar compra
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <div class="summary-security">

                            <i class="bi bi-shield-lock"></i>

                            <span>
                                <strong>Pagamento seguro</strong>
                                Os teus dados estão protegidos.
                            </span>

                        </div>

                    </aside>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>
Biblioteca
/
carrinho_atualizado.php


<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION["carrinho"])) {
    $_SESSION["carrinho"] = [];
}



/*
|--------------------------------------------------------------------------
| Adicionar produto
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["adicionar_carrinho"])
) {
    $produtoId = filter_input(INPUT_POST, "produto_id", FILTER_VALIDATE_INT);
    $plataformaId = filter_input(INPUT_POST, "plataforma_id", FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, "quantidade", FILTER_VALIDATE_INT);

    if (!$quantidade || $quantidade < 1) {
        $quantidade = 1;
    }

    if ($produtoId) {
        $stmtProduto = $conn->prepare("
            SELECT id, nome, preco, preco_promocional, imagem, stock, ativo
            FROM products
            WHERE id = :produto_id AND ativo = 1
            LIMIT 1
        ");
        $stmtProduto->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        $stmtProduto->execute();
        $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $stockDisponivel = (int) $produto["stock"];

            if ($plataformaId) {
                $stmtPlataforma = $conn->prepare("
                    SELECT id, plataforma, preco, preco_promocional, stock
                    FROM product_platforms
                    WHERE id = :plataforma_id
                      AND product_id = :produto_id
                    LIMIT 1
                ");
                $stmtPlataforma->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
                $stmtPlataforma->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
                $stmtPlataforma->execute();
                $plataformaSelecionada = $stmtPlataforma->fetch(PDO::FETCH_ASSOC);

                if (!$plataformaSelecionada) {
                    $_SESSION["mensagem_carrinho"] = "A plataforma selecionada não é válida.";
                    header("Location: produto.php?id=" . $produtoId);
                    exit;
                }

                $stockDisponivel = (int) $plataformaSelecionada["stock"];
            }

            if ($stockDisponivel > 0) {
                $chaveCarrinho = criarChaveCarrinho($produtoId, $plataformaId ?: null);
                $quantidadeAtual = (int) ($_SESSION["carrinho"][$chaveCarrinho] ?? 0);
                $_SESSION["carrinho"][$chaveCarrinho] = min(
                    $quantidadeAtual + $quantidade,
                    $stockDisponivel
                );
                $_SESSION["mensagem_carrinho"] = "Produto adicionado ao carrinho.";
            } else {
                $_SESSION["mensagem_carrinho"] = "Este produto está esgotado.";
            }
        }
    }

    header("Location: carrinho.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Atualizar quantidades
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["atualizar_carrinho"])
) {
    $quantidades = $_POST["quantidades"] ?? [];

    foreach ($quantidades as $chaveCarrinho => $quantidade) {
        if (!isset($_SESSION["carrinho"][$chaveCarrinho])) {
            continue;
        }

        $quantidade = filter_var($quantidade, FILTER_VALIDATE_INT);

        if (!$quantidade || $quantidade < 1) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        if (!preg_match('/^produto-(\d+)(?:-plataforma-(\d+))?$/', $chaveCarrinho, $m)) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        $produtoId = (int) $m[1];
        $plataformaId = isset($m[2]) ? (int) $m[2] : null;

        if ($plataformaId) {
            $stmtStock = $conn->prepare("
                SELECT product_platforms.stock
                FROM product_platforms
                INNER JOIN products ON products.id = product_platforms.product_id
                WHERE product_platforms.id = :plataforma_id
                  AND product_platforms.product_id = :produto_id
                  AND products.ativo = 1
                LIMIT 1
            ");
            $stmtStock->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
            $stmtStock->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        } else {
            $stmtStock = $conn->prepare("
                SELECT stock
                FROM products
                WHERE id = :produto_id AND ativo = 1
                LIMIT 1
            ");
            $stmtStock->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        }

        $stmtStock->execute();
        $stock = $stmtStock->fetchColumn();

        if ($stock === false || (int) $stock <= 0) {
            unset($_SESSION["carrinho"][$chaveCarrinho]);
            continue;
        }

        $_SESSION["carrinho"][$chaveCarrinho] = min($quantidade, (int) $stock);
    }

    $_SESSION["mensagem_carrinho"] = "Carrinho atualizado com sucesso.";
    header("Location: carrinho.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Remover produto
|--------------------------------------------------------------------------
*/

$removerChave = $_GET["remover"] ?? "";

if ($removerChave !== "" && isset($_SESSION["carrinho"][$removerChave])) {
    unset($_SESSION["carrinho"][$removerChave]);
    $_SESSION["mensagem_carrinho"] = "Produto removido do carrinho.";
    header("Location: carrinho.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Esvaziar carrinho
|--------------------------------------------------------------------------
*/

if (isset($_GET["esvaziar"])) {
    $_SESSION["carrinho"] = [];
    $_SESSION["mensagem_carrinho"] = "O carrinho foi esvaziado.";
    header("Location: carrinho.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Obter produtos do carrinho
|--------------------------------------------------------------------------
*/

$produtosCarrinho = [];
$subtotalCarrinho = 0;

foreach ($_SESSION["carrinho"] as $chaveCarrinho => $quantidade) {
    if (!preg_match('/^produto-(\d+)(?:-plataforma-(\d+))?$/', $chaveCarrinho, $m)) {
        continue;
    }

    $produtoId = (int) $m[1];
    $plataformaId = isset($m[2]) ? (int) $m[2] : null;

    $stmtCarrinho = $conn->prepare("
        SELECT
            products.id,
            products.nome,
            products.preco,
            products.preco_promocional,
            products.imagem,
            products.stock,
            products.marca,
            categories.nome AS categoria
        FROM products
        INNER JOIN categories ON products.categoria_id = categories.id
        WHERE products.id = :produto_id
          AND products.ativo = 1
        LIMIT 1
    ");
    $stmtCarrinho->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
    $stmtCarrinho->execute();
    $produto = $stmtCarrinho->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        continue;
    }

    $produto["plataforma_id"] = null;
    $produto["plataforma"] = null;

    if ($plataformaId) {
        $stmtPlataforma = $conn->prepare("
            SELECT id, plataforma, preco, preco_promocional, stock
            FROM product_platforms
            WHERE id = :plataforma_id
              AND product_id = :produto_id
            LIMIT 1
        ");
        $stmtPlataforma->bindValue(":plataforma_id", $plataformaId, PDO::PARAM_INT);
        $stmtPlataforma->bindValue(":produto_id", $produtoId, PDO::PARAM_INT);
        $stmtPlataforma->execute();
        $variante = $stmtPlataforma->fetch(PDO::FETCH_ASSOC);

        if (!$variante) {
            continue;
        }

        $produto["plataforma_id"] = (int) $variante["id"];
        $produto["plataforma"] = $variante["plataforma"];
        $produto["preco"] = $variante["preco"];
        $produto["preco_promocional"] = $variante["preco_promocional"];
        $produto["stock"] = $variante["stock"];
    }

    $quantidade = min((int) $quantidade, (int) $produto["stock"]);

    if ($quantidade < 1) {
        continue;
    }

    $precoUnitario =
        !empty($produto["preco_promocional"])
        && (float) $produto["preco_promocional"] < (float) $produto["preco"]
            ? (float) $produto["preco_promocional"]
            : (float) $produto["preco"];

    $subtotal = $precoUnitario * $quantidade;

    $produto["chave_carrinho"] = $chaveCarrinho;
    $produto["quantidade"] = $quantidade;
    $produto["preco_unitario"] = $precoUnitario;
    $produto["subtotal"] = $subtotal;

    $subtotalCarrinho += $subtotal;
    $produtosCarrinho[] = $produto;
}

$portes = $subtotalCarrinho >= 50 || $subtotalCarrinho === 0 ? 0 : 4.99;
$totalCarrinho = $subtotalCarrinho + $portes;
$mensagemCarrinho = $_SESSION["mensagem_carrinho"] ?? null;
unset($_SESSION["mensagem_carrinho"]);

require_once "includes/header.php";
?>

<section class="cart-page">

    <div class="container">

        <div class="cart-heading">

            <div>
                <span class="section-eyebrow">
                    A tua seleção
                </span>

                <h1>Carrinho de compras</h1>
            </div>

            <?php if (!empty($produtosCarrinho)): ?>

                <a
                    href="carrinho.php?esvaziar=1"
                    class="empty-cart-link"
                    onclick="return confirm(
                        'Tens a certeza de que pretendes esvaziar o carrinho?'
                    );"
                >
                    <i class="bi bi-trash"></i>
                    Esvaziar carrinho
                </a>

            <?php endif; ?>

        </div>

        <?php if ($mensagemCarrinho): ?>

            <div class="cart-message">
                <i class="bi bi-check-circle"></i>

                <?= htmlspecialchars($mensagemCarrinho) ?>
            </div>

        <?php endif; ?>

        <?php if (empty($produtosCarrinho)): ?>

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    <i class="bi bi-cart-x"></i>
                </div>

                <h2>O teu carrinho está vazio</h2>

                <p>
                    Explora a GamerHub e encontra o próximo equipamento
                    para o teu setup.
                </p>

                <a
                    href="produtos.php"
                    class="btn btn-gamer-primary"
                >
                    Explorar produtos
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

        <?php else: ?>

            <div class="row g-4">

                <div class="col-lg-8">

                    <form
                        action="carrinho.php"
                        method="post"
                    >

                        <div class="cart-products">

                            <?php foreach (
                                $produtosCarrinho as $produto
                            ): ?>

                                <article class="cart-product">

                                    <a
                                        href="produto.php?id=<?= (int) $produto["id"] ?>"
                                        class="cart-product-image"
                                    >

                                        <?php if (
                                            !empty($produto["imagem"])
                                        ): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                    $produto["imagem"]
                                                ) ?>?v=<?= time() ?>"
                                                alt="<?= htmlspecialchars(
                                                    $produto["nome"]
                                                ) ?>"
                                            >

                                        <?php else: ?>

                                            <i class="bi bi-controller"></i>

                                        <?php endif; ?>

                                    </a>

                                    <div class="cart-product-info">

                                        <div class="cart-product-meta">
                                            <?= htmlspecialchars(
                                                $produto["marca"]
                                            ) ?>

                                            <span>•</span>

                                            <?= htmlspecialchars(
                                                $produto["categoria"]
                                            ) ?>
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

                                        <?php if (!empty($produto["plataforma"])): ?>

                                            <div class="cart-product-platform">
                                                <i class="bi bi-controller"></i>
                                                <?= htmlspecialchars($produto["plataforma"]) ?>
                                            </div>

                                        <?php endif; ?>

                                        <span class="stock-available">
                                            <i class="bi bi-circle-fill"></i>
                                            Em stock
                                        </span>

                                        <a
                                            href="carrinho.php?remover=<?= urlencode($produto["chave_carrinho"]) ?>"
                                            class="remove-cart-product"
                                            onclick="return confirm(
                                                'Remover este produto do carrinho?'
                                            );"
                                        >
                                            <i class="bi bi-trash"></i>
                                            Remover
                                        </a>

                                    </div>

                                    <div class="cart-product-actions">

                                        <label
                                            for="quantidade-<?= htmlspecialchars($produto["chave_carrinho"]) ?>"
                                        >
                                            Quantidade
                                        </label>

                                        <input
                                            id="quantidade-<?= htmlspecialchars($produto["chave_carrinho"]) ?>"
                                            type="number"
                                            name="quantidades[<?= htmlspecialchars($produto["chave_carrinho"]) ?>]"
                                            value="<?= (int) $produto["quantidade"] ?>"
                                            min="1"
                                            max="<?= (int) $produto["stock"] ?>"
                                        >

                                        <div class="cart-product-price">

                                            <small>
                                                <?= number_format(
                                                    (float) $produto["preco_unitario"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> € / unidade
                                            </small>

                                            <strong>
                                                <?= number_format(
                                                    (float) $produto["subtotal"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €
                                            </strong>

                                        </div>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                        <div class="cart-update-row">

                            <a
                                href="produtos.php"
                                class="continue-shopping"
                            >
                                <i class="bi bi-arrow-left"></i>
                                Continuar a comprar
                            </a>

                            <button
                                type="submit"
                                name="atualizar_carrinho"
                                class="btn update-cart-button"
                            >
                                <i class="bi bi-arrow-repeat"></i>
                                Atualizar carrinho
                            </button>

                        </div>

                    </form>

                </div>

                <div class="col-lg-4">

                    <aside class="cart-summary">

                        <span class="section-eyebrow">
                            Resumo
                        </span>

                        <h2>Resumo da encomenda</h2>

                        <div class="summary-row">
                            <span>Subtotal</span>

                            <strong>
                                <?= number_format(
                                    $subtotalCarrinho,
                                    2,
                                    ",",
                                    "."
                                ) ?> €
                            </strong>
                        </div>

                        <div class="summary-row">
                            <span>Portes de envio</span>

                            <strong>
                                <?= $portes === 0
                                    ? "Grátis"
                                    : number_format(
                                        $portes,
                                        2,
                                        ",",
                                        "."
                                    ) . " €" ?>
                            </strong>
                        </div>

                        <?php if (
                            $portes > 0
                            && $subtotalCarrinho < 50
                        ): ?>

                            <div class="free-shipping-notice">

                                <i class="bi bi-truck"></i>

                                Faltam

                                <strong>
                                    <?= number_format(
                                        50 - $subtotalCarrinho,
                                        2,
                                        ",",
                                        "."
                                    ) ?> €
                                </strong>

                                para obteres portes grátis.

                            </div>

                        <?php endif; ?>

                        <div class="summary-total">

                            <span>Total</span>

                            <strong>
                                <?= number_format(
                                    $totalCarrinho,
                                    2,
                                    ",",
                                    "."
                                ) ?> €
                            </strong>

                        </div>

                        <a
                            href="checkout.php"
                            class="btn checkout-button"
                        >
                            Finalizar compra
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <div class="summary-security">

                            <i class="bi bi-shield-lock"></i>

                            <span>
                                <strong>Pagamento seguro</strong>
                                Os teus dados estão protegidos.
                            </span>

                        </div>

                    </aside>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>