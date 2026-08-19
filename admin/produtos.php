<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";

if (
    empty($_SESSION["utilizador_id"])
    || ($_SESSION["tipo_utilizador"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$mensagem = "";
$erro = "";

$pesquisa = trim($_GET["pesquisa"] ?? "");

$categoriaId = filter_input(
    INPUT_GET,
    "categoria",
    FILTER_VALIDATE_INT
);

/*
|--------------------------------------------------------------------------
| Apagar produto
|--------------------------------------------------------------------------
*/

$apagarId = filter_input(
    INPUT_GET,
    "apagar",
    FILTER_VALIDATE_INT
);

if ($apagarId) {
    try {
        $stmtApagar = $conn->prepare("
            DELETE FROM products
            WHERE id = :id
        ");

        $stmtApagar->bindValue(
            ":id",
            $apagarId,
            PDO::PARAM_INT
        );

        $stmtApagar->execute();

        header("Location: produtos.php?apagado=1");
        exit;
    } catch (PDOException $e) {
        $erro =
            "Não foi possível apagar este produto. "
            . "Pode estar associado a uma encomenda.";
    }
}

if (isset($_GET["apagado"]) && $_GET["apagado"] === "1") {
    $mensagem = "Produto apagado com sucesso.";
}

/*
|--------------------------------------------------------------------------
| Ativar / desativar produto
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["guardar_estado"])
) {
    $produtoId = filter_input(
        INPUT_POST,
        "produto_id",
        FILTER_VALIDATE_INT
    );

    $ativo = isset($_POST["ativo"]) ? 1 : 0;

    if (!$produtoId) {
        $erro = "Produto inválido.";
    } else {
        $stmtEstado = $conn->prepare("
            UPDATE products
            SET ativo = :ativo
            WHERE id = :id
        ");

        $stmtEstado->bindValue(
            ":ativo",
            $ativo,
            PDO::PARAM_INT
        );

        $stmtEstado->bindValue(
            ":id",
            $produtoId,
            PDO::PARAM_INT
        );

        $stmtEstado->execute();

        $mensagem = "Estado do produto atualizado.";
    }
}

/*
|--------------------------------------------------------------------------
| Categorias
|--------------------------------------------------------------------------
*/

$stmtCategorias = $conn->prepare("
    SELECT
        id,
        nome
    FROM categories
    ORDER BY nome ASC
");

$stmtCategorias->execute();

$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Produtos
|--------------------------------------------------------------------------
*/

$sqlProdutos = "
    SELECT
        products.id,
        products.nome,
        products.preco,
        products.preco_promocional,
        products.imagem,
        products.stock,
        products.marca,
        products.ativo,
        products.destaque,
        categories.nome AS categoria
    FROM products
    INNER JOIN categories
        ON categories.id = products.categoria_id
    WHERE 1 = 1
";

$parametros = [];

if ($pesquisa !== "") {
    $sqlProdutos .= "
        AND (
            products.nome LIKE :pesquisa
            OR products.marca LIKE :pesquisa
            OR products.descricao LIKE :pesquisa
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

$sqlProdutos .= "
    ORDER BY products.id DESC
";

$stmtProdutos = $conn->prepare($sqlProdutos);
$stmtProdutos->execute($parametros);

$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

function obterPrecoAtual($produto)
{
    if (
        !empty($produto["preco_promocional"])
        && (float) $produto["preco_promocional"]
            < (float) $produto["preco"]
    ) {
        return (float) $produto["preco_promocional"];
    }

    return (float) $produto["preco"];
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

    <title>Produtos — GamerHub Admin</title>

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

    <style>
        body {
            min-height: 100vh;
            margin: 0;
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
            border: 1px solid transparent;
            border-radius: 0.8rem;
            color: #aaaabd;
            text-decoration: none;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            border-color: rgba(155, 77, 255, 0.35);
            background: rgba(155, 77, 255, 0.14);
            color: #ffffff;
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

        .admin-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .admin-header span {
            color: #9b4dff;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .admin-header h1 {
            margin: 0.45rem 0 0;
            font-family: "Orbitron", sans-serif;
            font-size: clamp(2rem, 4vw, 3.3rem);
        }

        .btn-purple {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0 1rem;
            border: 0;
            border-radius: 0.7rem;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: #ffffff;
            font-weight: 800;
            text-decoration: none;
        }

        .btn-purple:hover {
            color: #ffffff;
        }

        .admin-alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.8rem;
        }

        .admin-alert-success {
            border: 1px solid rgba(82, 212, 155, 0.35);
            background: rgba(25, 135, 84, 0.14);
            color: #7be2b3;
        }

        .admin-alert-error {
            border: 1px solid rgba(255, 92, 110, 0.35);
            background: rgba(220, 53, 69, 0.14);
            color: #ff8996;
        }

        .admin-filters {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px auto;
            gap: 0.8rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: #131322;
        }

        .admin-filters input,
        .admin-filters select {
            width: 100%;
            height: 46px;
            padding: 0 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.7rem;
            outline: none;
            background: #19192a;
            color: #ffffff;
        }

        .admin-panel {
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: #131322;
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
            vertical-align: middle;
        }

        .admin-table th {
            color: #858599;
            font-size: 0.73rem;
            text-transform: uppercase;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 280px;
        }

        .product-thumb {
            display: grid;
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            place-items: center;
            overflow: hidden;
            border-radius: 0.75rem;
            background: #1b1b2c;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .product-name {
            font-weight: 800;
        }

        .product-meta {
            color: #9393a8;
            font-size: 0.82rem;
        }

        .badge-soft {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .badge-active {
            background: rgba(25, 135, 84, 0.16);
            color: #63daa4;
        }

        .badge-inactive {
            background: rgba(220, 53, 69, 0.14);
            color: #ff8490;
        }

        .badge-featured {
            margin-top: 0.4rem;
            background: rgba(155, 77, 255, 0.18);
            color: #c38cff;
        }

        .stock-low {
            color: #ff7a86;
            font-weight: 800;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .icon-button {
            display: inline-grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            color: #ffffff;
            text-decoration: none;
        }

        .icon-button-danger {
            border-color: rgba(255, 92, 110, 0.35);
            color: #ff7b88;
        }

        .state-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .state-form input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .empty-state {
            padding: 4rem 1rem;
            color: #858599;
            text-align: center;
        }

        @media (max-width: 1000px) {
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

            .admin-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-filters {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="admin-shell">

    <aside class="admin-sidebar">

        <a href="index.php" class="admin-logo">
            <i class="bi bi-controller"></i>
            Gamer<span>Hub</span>
        </a>

        <nav class="admin-nav">

            <a href="index.php">
                <i class="bi bi-grid"></i>
                Dashboard
            </a>

            <a href="produtos.php" class="active">
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

        <header class="admin-header">

            <div>
                <span>Gestão da loja</span>
                <h1>Produtos</h1>
            </div>

            <a
                href="produto_criar.php"
                class="btn-purple"
            >
                <i class="bi bi-plus-lg"></i>
                Novo produto
            </a>

        </header>

        <?php if ($mensagem !== ""): ?>

            <div class="admin-alert admin-alert-success">
                <?= htmlspecialchars($mensagem) ?>
            </div>

        <?php endif; ?>

        <?php if ($erro !== ""): ?>

            <div class="admin-alert admin-alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>

        <form
            action="produtos.php"
            method="get"
            class="admin-filters"
        >

            <input
                type="search"
                name="pesquisa"
                value="<?= htmlspecialchars($pesquisa) ?>"
                placeholder="Pesquisar por nome, marca ou descrição..."
            >

            <select name="categoria">

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
                    </option>

                <?php endforeach; ?>

            </select>

            <button
                type="submit"
                class="btn-purple"
            >
                <i class="bi bi-search"></i>
                Filtrar
            </button>

        </form>

        <section class="admin-panel">

            <?php if (empty($produtos)): ?>

                <div class="empty-state">
                    <i class="bi bi-box-seam fs-1"></i>
                    <h2 class="mt-3">Nenhum produto encontrado</h2>
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Preço</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($produtos as $produto): ?>

                                <tr>

                                    <td>

                                        <div class="product-cell">

                                            <div class="product-thumb">

                                                <?php if (!empty($produto["imagem"])): ?>

                                                    <img
                                                        src="../<?= htmlspecialchars(
                                                            $produto["imagem"]
                                                        ) ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $produto["nome"]
                                                        ) ?>"
                                                    >

                                                <?php else: ?>

                                                    <i class="bi bi-controller"></i>

                                                <?php endif; ?>

                                            </div>

                                            <div>

                                                <div class="product-name">
                                                    <?= htmlspecialchars(
                                                        $produto["nome"]
                                                    ) ?>
                                                </div>

                                                <div class="product-meta">
                                                    <?= htmlspecialchars(
                                                        $produto["marca"]
                                                            ?: "Sem marca"
                                                    ) ?>
                                                </div>

                                                <?php if (
                                                    (int) $produto["destaque"] === 1
                                                ): ?>

                                                    <span class="badge-soft badge-featured">
                                                        Destaque
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $produto["categoria"]
                                        ) ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?= number_format(
                                                obterPrecoAtual($produto),
                                                2,
                                                ",",
                                                "."
                                            ) ?> €
                                        </strong>

                                        <?php if (
                                            !empty($produto["preco_promocional"])
                                            && (float) $produto["preco_promocional"]
                                                < (float) $produto["preco"]
                                        ): ?>

                                            <div class="product-meta text-decoration-line-through">
                                                <?= number_format(
                                                    (float) $produto["preco"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) ?> €
                                            </div>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <span
                                            class="<?= (int) $produto["stock"] <= 5
                                                ? "stock-low"
                                                : "" ?>"
                                        >
                                            <?= (int) $produto["stock"] ?>
                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            action="produtos.php"
                                            method="post"
                                            class="state-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="produto_id"
                                                value="<?= (int) $produto["id"] ?>"
                                            >

                                            <input
                                                type="checkbox"
                                                name="ativo"
                                                value="1"
                                                <?= (int) $produto["ativo"] === 1
                                                    ? "checked"
                                                    : "" ?>
                                            >

                                            <button
                                                type="submit"
                                                name="guardar_estado"
                                                class="btn btn-sm btn-outline-light"
                                            >
                                                Guardar
                                            </button>

                                        </form>

                                        <span
                                            class="badge-soft <?= (int) $produto["ativo"] === 1
                                                ? "badge-active"
                                                : "badge-inactive" ?> mt-2"
                                        >
                                            <?= (int) $produto["ativo"] === 1
                                                ? "Ativo"
                                                : "Inativo" ?>
                                        </span>

                                    </td>

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="../produto.php?id=<?= (int) $produto["id"] ?>"
                                                class="icon-button"
                                                title="Ver produto"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a
                                                href="produto_editar.php?id=<?= (int) $produto["id"] ?>"
                                                class="icon-button"
                                                title="Editar produto"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a
                                                href="produtos.php?apagar=<?= (int) $produto["id"] ?>"
                                                class="icon-button icon-button-danger"
                                                title="Apagar produto"
                                                onclick="return confirm('Apagar este produto definitivamente?');"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>