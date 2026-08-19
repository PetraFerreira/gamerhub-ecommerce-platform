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

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["alterar_tipo"])
) {
    $utilizadorId = filter_input(
        INPUT_POST,
        "utilizador_id",
        FILTER_VALIDATE_INT
    );

    $novoTipo = $_POST["tipo_utilizador"] ?? "";

    if (
        !$utilizadorId
        || !in_array($novoTipo, ["cliente", "admin"], true)
    ) {
        $erro = "Não foi possível atualizar o utilizador.";
    } elseif ($utilizadorId === (int) $_SESSION["utilizador_id"]) {
        $erro = "Não podes alterar o tipo da conta que está em sessão.";
    } else {
        $stmt = $conn->prepare("
            UPDATE users
            SET tipo_utilizador = :tipo
            WHERE id = :id
        ");

        $stmt->execute([
            ":tipo" => $novoTipo,
            ":id" => $utilizadorId
        ]);

        $mensagem = "Tipo de utilizador atualizado com sucesso.";
    }
}

if (
    isset($_GET["apagar"])
    && filter_var($_GET["apagar"], FILTER_VALIDATE_INT)
) {
    $utilizadorId = (int) $_GET["apagar"];

    if ($utilizadorId === (int) $_SESSION["utilizador_id"]) {
        $erro = "Não podes apagar a conta que está em sessão.";
    } else {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM orders
            WHERE user_id = :id
        ");
        $stmt->execute([":id" => $utilizadorId]);

        $temEncomendas = (int) $stmt->fetchColumn() > 0;

        if ($temEncomendas) {
            $erro =
                "Este utilizador tem encomendas e não pode ser apagado.";
        } else {
            $stmt = $conn->prepare("
                DELETE FROM users
                WHERE id = :id
            ");
            $stmt->execute([":id" => $utilizadorId]);

            $mensagem = "Utilizador apagado com sucesso.";
        }
    }
}

$sql = "
    SELECT
        users.id,
        users.nome,
        users.email,
        users.telefone,
        users.cidade,
        users.tipo_utilizador,
        users.criado_em,
        COUNT(orders.id) AS total_encomendas
    FROM users
    LEFT JOIN orders
        ON orders.user_id = users.id
    GROUP BY
        users.id,
        users.nome,
        users.email,
        users.telefone,
        users.cidade,
        users.tipo_utilizador,
        users.criado_em
    ORDER BY users.criado_em DESC
";

$utilizadores = $conn
    ->query($sql)
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

    <title>Utilizadores — GamerHub Admin</title>

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
            font-size: clamp(2rem, 4vw, 3.5rem);
        }

        .admin-count {
            color: #aaaabd;
        }

        .admin-count strong {
            color: #a95cff;
            font-family: "Orbitron", sans-serif;
            font-size: 1.5rem;
        }

        .admin-alert {
            margin-bottom: 1rem;
            padding: 1rem 1.1rem;
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

        .admin-panel {
            padding: 1.3rem;
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
            padding: 0.95rem 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            text-align: left;
            vertical-align: middle;
        }

        .admin-table th {
            color: #858599;
            font-size: 0.74rem;
            text-transform: uppercase;
        }

        .admin-table td {
            color: #ececf4;
        }

        .user-name {
            font-weight: 800;
        }

        .user-email,
        .user-meta {
            color: #9292a7;
            font-size: 0.85rem;
        }

        .role-badge {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .role-admin {
            background: rgba(155, 77, 255, 0.18);
            color: #bf86ff;
        }

        .role-cliente {
            background: rgba(13, 110, 253, 0.16);
            color: #75adff;
        }

        .role-form {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .role-form select {
            height: 40px;
            padding: 0 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.6rem;
            outline: none;
            background: #19192a;
            color: #ffffff;
        }

        .btn-purple {
            height: 40px;
            padding: 0 0.85rem;
            border: 0;
            border-radius: 0.6rem;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: #ffffff;
            font-weight: 800;
        }

        .btn-delete {
            display: inline-flex;
            height: 40px;
            align-items: center;
            justify-content: center;
            padding: 0 0.85rem;
            border: 1px solid rgba(255, 92, 110, 0.35);
            border-radius: 0.6rem;
            color: #ff7584;
            text-decoration: none;
        }

        .btn-delete:hover {
            background: rgba(220, 53, 69, 0.12);
            color: #ff9ba5;
        }

        @media (max-width: 900px) {
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

            <a href="categorias.php">
                <i class="bi bi-tags"></i>
                Categorias
            </a>

            <a href="encomendas.php">
                <i class="bi bi-bag-check"></i>
                Encomendas
            </a>

            <a href="utilizadores.php" class="active">
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
                <h1>Utilizadores</h1>
            </div>

            <div class="admin-count">
                <strong><?= count($utilizadores) ?></strong>
                utilizador(es)
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

        <section class="admin-panel">

            <div class="table-responsive">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Utilizador</th>
                            <th>Contacto</th>
                            <th>Tipo</th>
                            <th>Encomendas</th>
                            <th>Registo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($utilizadores as $utilizador): ?>

                            <tr>

                                <td>
                                    <div class="user-name">
                                        <?= htmlspecialchars(
                                            $utilizador["nome"]
                                        ) ?>
                                    </div>

                                    <div class="user-email">
                                        <?= htmlspecialchars(
                                            $utilizador["email"]
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        <?= htmlspecialchars(
                                            $utilizador["telefone"]
                                                ?: "Sem telefone"
                                        ) ?>
                                    </div>

                                    <div class="user-meta">
                                        <?= htmlspecialchars(
                                            $utilizador["cidade"]
                                                ?: "Sem cidade"
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="role-badge <?= $utilizador[
                                            "tipo_utilizador"
                                        ] === "admin"
                                            ? "role-admin"
                                            : "role-cliente" ?>"
                                    >
                                        <?= $utilizador[
                                            "tipo_utilizador"
                                        ] === "admin"
                                            ? "Administrador"
                                            : "Cliente" ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int) $utilizador[
                                        "total_encomendas"
                                    ] ?>
                                </td>

                                <td>
                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $utilizador["criado_em"]
                                        )
                                    ) ?>
                                </td>

                                <td>

                                    <?php if (
                                        (int) $utilizador["id"]
                                        === (int) $_SESSION[
                                            "utilizador_id"
                                        ]
                                    ): ?>

                                        <span class="user-meta">
                                            Conta em sessão
                                        </span>

                                    <?php else: ?>

                                        <div class="d-flex flex-wrap gap-2">

                                            <form
                                                action="utilizadores.php"
                                                method="post"
                                                class="role-form"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="utilizador_id"
                                                    value="<?= (int) $utilizador[
                                                        "id"
                                                    ] ?>"
                                                >

                                                <select
                                                    name="tipo_utilizador"
                                                >
                                                    <option
                                                        value="cliente"
                                                        <?= $utilizador[
                                                            "tipo_utilizador"
                                                        ] === "cliente"
                                                            ? "selected"
                                                            : "" ?>
                                                    >
                                                        Cliente
                                                    </option>

                                                    <option
                                                        value="admin"
                                                        <?= $utilizador[
                                                            "tipo_utilizador"
                                                        ] === "admin"
                                                            ? "selected"
                                                            : "" ?>
                                                    >
                                                        Admin
                                                    </option>
                                                </select>

                                                <button
                                                    type="submit"
                                                    name="alterar_tipo"
                                                    class="btn-purple"
                                                >
                                                    Guardar
                                                </button>

                                            </form>

                                            <a
                                                href="utilizadores.php?apagar=<?= (int) $utilizador[
                                                    "id"
                                                ] ?>"
                                                class="btn-delete"
                                                onclick="return confirm('Apagar este utilizador?');"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>