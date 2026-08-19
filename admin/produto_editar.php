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

$produtoId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$produtoId) {
    header("Location: produtos.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$categorias = $conn->query("
    SELECT id, nome
    FROM categories
    ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

$stmtProduto = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = :id
    LIMIT 1
");
$stmtProduto->execute([":id" => $produtoId]);
$dados = $stmtProduto->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    header("Location: produtos.php");
    exit;
}

$erros = [];
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $camposTexto = [
        "nome", "descricao", "preco", "preco_promocional",
        "imagem", "stock", "categoria_id", "marca",
        "data_lancamento", "plataforma", "genero",
        "developer", "publisher", "pegi"
    ];

    foreach ($camposTexto as $campo) {
        $dados[$campo] = trim($_POST[$campo] ?? "");
    }

    $dados["destaque"] = isset($_POST["destaque"]) ? 1 : 0;
    $dados["proximo_lancamento"] =
        isset($_POST["proximo_lancamento"]) ? 1 : 0;
    $dados["ativo"] = isset($_POST["ativo"]) ? 1 : 0;

    if ($dados["nome"] === "") {
        $erros[] = "Indica o nome do produto.";
    }

    if ($dados["descricao"] === "") {
        $erros[] = "Indica a descrição.";
    }

    if (
        $dados["preco"] === ""
        || !is_numeric($dados["preco"])
        || (float) $dados["preco"] < 0
    ) {
        $erros[] = "Indica um preço válido.";
    }

    if (
        $dados["preco_promocional"] !== ""
        && (
            !is_numeric($dados["preco_promocional"])
            || (float) $dados["preco_promocional"] < 0
        )
    ) {
        $erros[] = "O preço promocional é inválido.";
    }

    if (
        $dados["stock"] === ""
        || filter_var($dados["stock"], FILTER_VALIDATE_INT) === false
        || (int) $dados["stock"] < 0
    ) {
        $erros[] = "Indica um stock válido.";
    }

    if (!$erros) {
        $stmt = $conn->prepare("
            UPDATE products
            SET
                nome = :nome,
                descricao = :descricao,
                preco = :preco,
                preco_promocional = :preco_promocional,
                imagem = :imagem,
                stock = :stock,
                categoria_id = :categoria_id,
                marca = :marca,
                data_lancamento = :data_lancamento,
                plataforma = :plataforma,
                genero = :genero,
                developer = :developer,
                publisher = :publisher,
                pegi = :pegi,
                destaque = :destaque,
                proximo_lancamento = :proximo_lancamento,
                ativo = :ativo
            WHERE id = :id
        ");

        $stmt->execute([
            ":nome" => $dados["nome"],
            ":descricao" => $dados["descricao"],
            ":preco" => (float) $dados["preco"],
            ":preco_promocional" =>
                $dados["preco_promocional"] !== ""
                    ? (float) $dados["preco_promocional"]
                    : null,
            ":imagem" =>
                $dados["imagem"] !== "" ? $dados["imagem"] : null,
            ":stock" => (int) $dados["stock"],
            ":categoria_id" => (int) $dados["categoria_id"],
            ":marca" =>
                $dados["marca"] !== "" ? $dados["marca"] : null,
            ":data_lancamento" =>
                $dados["data_lancamento"] !== ""
                    ? $dados["data_lancamento"]
                    : null,
            ":plataforma" =>
                $dados["plataforma"] !== ""
                    ? $dados["plataforma"]
                    : null,
            ":genero" =>
                $dados["genero"] !== "" ? $dados["genero"] : null,
            ":developer" =>
                $dados["developer"] !== ""
                    ? $dados["developer"]
                    : null,
            ":publisher" =>
                $dados["publisher"] !== ""
                    ? $dados["publisher"]
                    : null,
            ":pegi" =>
                $dados["pegi"] !== "" ? $dados["pegi"] : null,
            ":destaque" => $dados["destaque"],
            ":proximo_lancamento" => $dados["proximo_lancamento"],
            ":ativo" => $dados["ativo"],
            ":id" => $produtoId
        ]);

        $mensagem = "Produto atualizado com sucesso.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar produto — GamerHub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body{min-height:100vh;margin:0;background:#090914;color:#fff;font-family:"Roboto",sans-serif}
        .admin-shell{display:grid;grid-template-columns:260px minmax(0,1fr);min-height:100vh}
        .admin-sidebar{position:sticky;top:0;height:100vh;padding:1.5rem;border-right:1px solid rgba(255,255,255,.08);background:#10101d}
        .admin-logo{display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;color:#fff;font-family:"Orbitron",sans-serif;font-size:1.25rem;font-weight:700;text-decoration:none}
        .admin-logo i,.admin-logo span{color:#9b4dff}
        .admin-nav{display:grid;gap:.55rem}
        .admin-nav a{display:flex;align-items:center;gap:.8rem;padding:.85rem 1rem;border:1px solid transparent;border-radius:.8rem;color:#aaaabd;text-decoration:none}
        .admin-nav a:hover,.admin-nav a.active{border-color:rgba(155,77,255,.35);background:rgba(155,77,255,.14);color:#fff}
        .admin-sidebar-footer{position:absolute;right:1.5rem;bottom:1.5rem;left:1.5rem}
        .admin-main{padding:2rem}
        .admin-header{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:2rem}
        .admin-header span{color:#9b4dff;font-size:.75rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase}
        .admin-header h1{margin:.45rem 0 0;font-family:"Orbitron",sans-serif;font-size:clamp(2rem,4vw,3.2rem)}
        .form-card{padding:1.4rem;border:1px solid rgba(255,255,255,.08);border-radius:1rem;background:#131322}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
        .form-field.full{grid-column:1/-1}
        .form-field label{display:block;margin-bottom:.4rem;color:#c8c8d5;font-size:.82rem;font-weight:700}
        .form-field input,.form-field select,.form-field textarea{width:100%;padding:.8rem .9rem;border:1px solid rgba(255,255,255,.12);border-radius:.7rem;outline:none;background:#19192a;color:#fff}
        .form-field textarea{min-height:140px;resize:vertical}
        .checkboxes{display:flex;flex-wrap:wrap;gap:1rem;padding:1rem 0}
        .checkboxes label{display:flex;align-items:center;gap:.5rem;color:#ddd}
        .actions{display:flex;justify-content:flex-end;gap:.8rem;margin-top:1.2rem}
        .btn-purple{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:.5rem;padding:0 1rem;border:0;border-radius:.7rem;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800;text-decoration:none}
        .alert-error,.alert-success{margin-bottom:1rem;padding:1rem;border-radius:.8rem}
        .alert-error{border:1px solid rgba(255,92,110,.35);background:rgba(220,53,69,.14);color:#ff9ba5}
        .alert-success{border:1px solid rgba(82,212,155,.35);background:rgba(25,135,84,.14);color:#7be2b3}
        @media(max-width:900px){.admin-shell{grid-template-columns:1fr}.admin-sidebar{position:static;height:auto}.admin-sidebar-footer{position:static;margin-top:1.5rem}.admin-main{padding:1rem}.form-grid{grid-template-columns:1fr}.admin-header{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="index.php" class="admin-logo"><i class="bi bi-controller"></i>Gamer<span>Hub</span></a>
        <nav class="admin-nav">
            <a href="index.php"><i class="bi bi-grid"></i>Dashboard</a>
            <a href="produtos.php" class="active"><i class="bi bi-box-seam"></i>Produtos</a>
            <a href="categorias.php"><i class="bi bi-tags"></i>Categorias</a>
            <a href="encomendas.php"><i class="bi bi-bag-check"></i>Encomendas</a>
            <a href="utilizadores.php"><i class="bi bi-people"></i>Utilizadores</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="../index.php" class="btn btn-outline-light w-100 mb-2">Ver loja</a>
            <a href="../logout.php" class="btn btn-outline-danger w-100">Sair</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div><span>Gestão da loja</span><h1>Editar produto</h1></div>
            <a href="produtos.php" class="btn btn-outline-light">Voltar</a>
        </header>

        <?php if ($mensagem !== ""): ?>
            <div class="alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($erros): ?>
            <div class="alert-error">
                <?php foreach ($erros as $erro): ?>
                    <div><?= htmlspecialchars($erro) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="produto_editar.php?id=<?= (int) $produtoId ?>" class="form-card">
            <div class="form-grid">
                <div class="form-field full">
                    <label for="nome">Nome *</label>
                    <input id="nome" name="nome" value="<?= htmlspecialchars($dados["nome"]) ?>" required>
                </div>

                <div class="form-field full">
                    <label for="descricao">Descrição *</label>
                    <textarea id="descricao" name="descricao" required><?= htmlspecialchars($dados["descricao"]) ?></textarea>
                </div>

                <div class="form-field">
                    <label for="preco">Preço *</label>
                    <input id="preco" type="number" step="0.01" min="0" name="preco" value="<?= htmlspecialchars($dados["preco"]) ?>" required>
                </div>

                <div class="form-field">
                    <label for="preco_promocional">Preço promocional</label>
                    <input id="preco_promocional" type="number" step="0.01" min="0" name="preco_promocional" value="<?= htmlspecialchars($dados["preco_promocional"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="stock">Stock *</label>
                    <input id="stock" type="number" min="0" name="stock" value="<?= htmlspecialchars($dados["stock"]) ?>" required>
                </div>

                <div class="form-field">
                    <label for="categoria_id">Categoria *</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria["id"] ?>" <?= (int) $dados["categoria_id"] === (int) $categoria["id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($categoria["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field full">
                    <label for="imagem">Caminho da imagem</label>
                    <input id="imagem" name="imagem" value="<?= htmlspecialchars($dados["imagem"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="marca">Marca</label>
                    <input id="marca" name="marca" value="<?= htmlspecialchars($dados["marca"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="data_lancamento">Data de lançamento</label>
                    <input id="data_lancamento" type="date" name="data_lancamento" value="<?= htmlspecialchars($dados["data_lancamento"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="plataforma">Plataforma</label>
                    <input id="plataforma" name="plataforma" value="<?= htmlspecialchars($dados["plataforma"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="genero">Género</label>
                    <input id="genero" name="genero" value="<?= htmlspecialchars($dados["genero"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="developer">Developer</label>
                    <input id="developer" name="developer" value="<?= htmlspecialchars($dados["developer"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="publisher">Publisher</label>
                    <input id="publisher" name="publisher" value="<?= htmlspecialchars($dados["publisher"] ?? "") ?>">
                </div>

                <div class="form-field">
                    <label for="pegi">PEGI</label>
                    <input id="pegi" name="pegi" value="<?= htmlspecialchars($dados["pegi"] ?? "") ?>">
                </div>
            </div>

            <div class="checkboxes">
                <label><input type="checkbox" name="destaque" value="1" <?= (int) $dados["destaque"] === 1 ? "checked" : "" ?>> Destaque</label>
                <label><input type="checkbox" name="proximo_lancamento" value="1" <?= (int) $dados["proximo_lancamento"] === 1 ? "checked" : "" ?>> Próximo lançamento</label>
                <label><input type="checkbox" name="ativo" value="1" <?= (int) $dados["ativo"] === 1 ? "checked" : "" ?>> Ativo</label>
            </div>

            <div class="actions">
                <a href="produtos.php" class="btn btn-outline-light">Cancelar</a>
                <button type="submit" class="btn-purple"><i class="bi bi-check2"></i> Guardar alterações</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>