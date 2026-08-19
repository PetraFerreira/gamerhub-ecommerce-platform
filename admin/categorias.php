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

$nome = "";
$descricao = "";
$categoriaEditarId = null;

/*
|--------------------------------------------------------------------------
| Carregar categoria para edição
|--------------------------------------------------------------------------
*/

$editarId = filter_input(
    INPUT_GET,
    "editar",
    FILTER_VALIDATE_INT
);

if ($editarId) {
    $stmtEditar = $conn->prepare("
        SELECT
            id,
            nome,
            descricao
        FROM categories
        WHERE id = :id
        LIMIT 1
    ");

    $stmtEditar->execute([
        ":id" => $editarId
    ]);

    $categoriaEditar = $stmtEditar->fetch(PDO::FETCH_ASSOC);

    if ($categoriaEditar) {
        $categoriaEditarId = (int) $categoriaEditar["id"];
        $nome = $categoriaEditar["nome"];
        $descricao = $categoriaEditar["descricao"];
    }
}

/*
|--------------------------------------------------------------------------
| Criar categoria
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["criar_categoria"])
) {
    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");

    if ($nome === "") {
        $erro = "Indica o nome da categoria.";
    } elseif (strlen($nome) > 100) {
        $erro = "O nome não pode ter mais de 100 caracteres.";
    } else {
        $stmtVerificar = $conn->prepare("
            SELECT id
            FROM categories
            WHERE nome = :nome
            LIMIT 1
        ");

        $stmtVerificar->execute([
            ":nome" => $nome
        ]);

        if ($stmtVerificar->fetch()) {
            $erro = "Já existe uma categoria com esse nome.";
        } else {
            $stmtCriar = $conn->prepare("
                INSERT INTO categories (
                    nome,
                    descricao
                )
                VALUES (
                    :nome,
                    :descricao
                )
            ");

            $stmtCriar->execute([
                ":nome" => $nome,
                ":descricao" => $descricao !== ""
                    ? $descricao
                    : null
            ]);

            header("Location: categorias.php?criada=1");
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Atualizar categoria
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["atualizar_categoria"])
) {
    $categoriaId = filter_input(
        INPUT_POST,
        "categoria_id",
        FILTER_VALIDATE_INT
    );

    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");

    if (!$categoriaId) {
        $erro = "Categoria inválida.";
    } elseif ($nome === "") {
        $erro = "Indica o nome da categoria.";
    } elseif (strlen($nome) > 100) {
        $erro = "O nome não pode ter mais de 100 caracteres.";
    } else {
        $stmtVerificar = $conn->prepare("
            SELECT id
            FROM categories
            WHERE nome = :nome
              AND id != :id
            LIMIT 1
        ");

        $stmtVerificar->execute([
            ":nome" => $nome,
            ":id" => $categoriaId
        ]);

        if ($stmtVerificar->fetch()) {
            $erro = "Já existe outra categoria com esse nome.";
        } else {
            $stmtAtualizar = $conn->prepare("
                UPDATE categories
                SET
                    nome = :nome,
                    descricao = :descricao
                WHERE id = :id
            ");

            $stmtAtualizar->execute([
                ":nome" => $nome,
                ":descricao" => $descricao !== ""
                    ? $descricao
                    : null,
                ":id" => $categoriaId
            ]);

            header("Location: categorias.php?atualizada=1");
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Apagar categoria
|--------------------------------------------------------------------------
*/

$apagarId = filter_input(
    INPUT_GET,
    "apagar",
    FILTER_VALIDATE_INT
);

if ($apagarId) {
    $stmtProdutos = $conn->prepare("
        SELECT COUNT(*)
        FROM products
        WHERE categoria_id = :id
    ");

    $stmtProdutos->execute([
        ":id" => $apagarId
    ]);

    $totalProdutosCategoria =
        (int) $stmtProdutos->fetchColumn();

    if ($totalProdutosCategoria > 0) {
        $erro =
            "Esta categoria tem produtos associados e não pode ser apagada.";
    } else {
        $stmtApagar = $conn->prepare("
            DELETE FROM categories
            WHERE id = :id
        ");

        $stmtApagar->execute([
            ":id" => $apagarId
        ]);

        header("Location: categorias.php?apagada=1");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Mensagens
|--------------------------------------------------------------------------
*/

if (isset($_GET["criada"]) && $_GET["criada"] === "1") {
    $mensagem = "Categoria criada com sucesso.";
}

if (
    isset($_GET["atualizada"])
    && $_GET["atualizada"] === "1"
) {
    $mensagem = "Categoria atualizada com sucesso.";
}

if (
    isset($_GET["apagada"])
    && $_GET["apagada"] === "1"
) {
    $mensagem = "Categoria apagada com sucesso.";
}

/*
|--------------------------------------------------------------------------
| Listagem
|--------------------------------------------------------------------------
*/

$sqlCategorias = "
    SELECT
        categories.id,
        categories.nome,
        categories.descricao,
        categories.criado_em,
        COUNT(products.id) AS total_produtos
    FROM categories
    LEFT JOIN products
        ON products.categoria_id = categories.id
    GROUP BY
        categories.id,
        categories.nome,
        categories.descricao,
        categories.criado_em
    ORDER BY categories.nome ASC
";

$categorias = $conn
    ->query($sqlCategorias)
    ->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Categorias — GamerHub Admin</title>

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
            border-right: 1px solid rgba(255,255,255,.08);
            background: #10101d;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
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
            gap: .55rem;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .85rem 1rem;
            border: 1px solid transparent;
            border-radius: .8rem;
            color: #aaaabd;
            text-decoration: none;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            border-color: rgba(155,77,255,.35);
            background: rgba(155,77,255,.14);
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
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .admin-header h1 {
            margin: .45rem 0 0;
            font-family: "Orbitron", sans-serif;
            font-size: clamp(2rem,4vw,3.3rem);
        }

        .admin-count {
            color: #aaaabd;
        }

        .admin-count strong {
            color: #a95cff;
            font-family: "Orbitron", sans-serif;
            font-size: 1.5rem;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 360px minmax(0,1fr);
            gap: 1rem;
        }

        .admin-panel {
            padding: 1.3rem;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 1rem;
            background: #131322;
        }

        .admin-panel h2 {
            margin: 0 0 1rem;
            font-family: "Orbitron", sans-serif;
            font-size: 1.15rem;
        }

        .form-field {
            margin-bottom: 1rem;
        }

        .form-field label {
            display: block;
            margin-bottom: .4rem;
            color: #c8c8d5;
            font-size: .82rem;
            font-weight: 700;
        }

        .form-field input,
        .form-field textarea {
            width: 100%;
            padding: .8rem .9rem;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: .7rem;
            outline: none;
            background: #19192a;
            color: #ffffff;
        }

        .form-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-purple {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: 0 1rem;
            border: 0;
            border-radius: .7rem;
            background: linear-gradient(135deg,#7c3aed,#a855f7);
            color: #ffffff;
            font-weight: 800;
            text-decoration: none;
        }

        .admin-alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: .8rem;
        }

        .admin-alert-success {
            border: 1px solid rgba(82,212,155,.35);
            background: rgba(25,135,84,.14);
            color: #7be2b3;
        }

        .admin-alert-error {
            border: 1px solid rgba(255,92,110,.35);
            background: rgba(220,53,69,.14);
            color: #ff8996;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: .9rem .7rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            text-align: left;
            vertical-align: middle;
        }

        .admin-table th {
            color: #858599;
            font-size: .73rem;
            text-transform: uppercase;
        }

        .category-name {
            font-weight: 800;
        }

        .category-description {
            margin-top: .25rem;
            color: #9292a7;
            font-size: .84rem;
        }

        .badge-soft {
            display: inline-flex;
            padding: .35rem .65rem;
            border-radius: 999px;
            background: rgba(155,77,255,.18);
            color: #c38cff;
            font-size: .72rem;
            font-weight: 800;
        }

        .actions {
            display: flex;
            gap: .5rem;
        }

        .icon-button {
            display: inline-grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: .65rem;
            color: #ffffff;
            text-decoration: none;
        }

        .icon-button-danger {
            border-color: rgba(255,92,110,.35);
            color: #ff7b88;
        }

        .empty-state {
            padding: 3rem 1rem;
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

            .admin-grid {
                grid-template-columns: 1fr;
            }

            .admin-header {
                align-items: flex-start;
                flex-direction: column;
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

            <a href="produtos.php">
                <i class="bi bi-box-seam"></i>
                Produtos
            </a>

            <a href="categorias.php" class="active">
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
                <h1>Categorias</h1>
            </div>

            <div class="admin-count">
                <strong><?= count($categorias) ?></strong>
                categoria(s)
            </div>

        </header>

        <?php if ($mensagem !== ""): ?>

            <div class="admin-alert admin-alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>

        <?php endif; ?>

        <?php if ($erro !== ""): ?>

            <div class="admin-alert admin-alert-error">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>

        <section class="admin-grid">

            <article class="admin-panel">

                <h2>
                    <?= $categoriaEditarId
                        ? "Editar categoria"
                        : "Nova categoria" ?>
                </h2>

                <form
                    action="categorias.php<?= $categoriaEditarId
                        ? "?editar=" . $categoriaEditarId
                        : "" ?>"
                    method="post"
                >

                    <?php if ($categoriaEditarId): ?>

                        <input
                            type="hidden"
                            name="categoria_id"
                            value="<?= $categoriaEditarId ?>"
                        >

                    <?php endif; ?>

                    <div class="form-field">

                        <label for="nome">
                            Nome
                        </label>

                        <input
                            id="nome"
                            type="text"
                            name="nome"
                            value="<?= htmlspecialchars($nome) ?>"
                            maxlength="100"
                            required
                        >

                    </div>

                    <div class="form-field">

                        <label for="descricao">
                            Descrição
                        </label>

                        <textarea
                            id="descricao"
                            name="descricao"
                        ><?= htmlspecialchars($descricao) ?></textarea>

                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        <button
                            type="submit"
                            name="<?= $categoriaEditarId
                                ? "atualizar_categoria"
                                : "criar_categoria" ?>"
                            class="btn-purple"
                        >
                            <i class="bi bi-check2"></i>

                            <?= $categoriaEditarId
                                ? "Guardar alterações"
                                : "Criar categoria" ?>
                        </button>

                        <?php if ($categoriaEditarId): ?>

                            <a
                                href="categorias.php"
                                class="btn btn-outline-light"
                            >
                                Cancelar
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </article>

            <article class="admin-panel">

                <h2>Lista de categorias</h2>

                <?php if (empty($categorias)): ?>

                    <div class="empty-state">
                        Ainda não existem categorias.
                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Produtos</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach (
                                    $categorias
                                    as $categoria
                                ): ?>

                                    <tr>

                                        <td>

                                            <div class="category-name">
                                                <?= htmlspecialchars(
                                                    $categoria["nome"]
                                                ) ?>
                                            </div>

                                            <div class="category-description">
                                                <?= htmlspecialchars(
                                                    $categoria["descricao"]
                                                        ?: "Sem descrição"
                                                ) ?>
                                            </div>

                                        </td>

                                        <td>

                                            <span class="badge-soft">
                                                <?= (int) $categoria[
                                                    "total_produtos"
                                                ] ?>
                                            </span>

                                        </td>

                                        <td>
                                            <?= date(
                                                "d/m/Y",
                                                strtotime(
                                                    $categoria["criado_em"]
                                                )
                                            ) ?>
                                        </td>

                                        <td>

                                            <div class="actions">

                                                <a
                                                    href="categorias.php?editar=<?= (int) $categoria[
                                                        "id"
                                                    ] ?>"
                                                    class="icon-button"
                                                    title="Editar categoria"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <a
                                                    href="categorias.php?apagar=<?= (int) $categoria[
                                                        "id"
                                                    ] ?>"
                                                    class="icon-button icon-button-danger"
                                                    title="Apagar categoria"
                                                    onclick="return confirm('Apagar esta categoria?');"
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

            </article>

        </section>

    </main>

</div>

</body>
</html>