<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/database.php";

if (empty($_SESSION["utilizador_id"]) || ($_SESSION["tipo_utilizador"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$mensagem = "";
$erro = "";
$estadosPermitidos = ["pendente", "pago", "em_preparacao", "enviado"];

function nomeEstado($estado) {
    $nomes = [
        "pendente" => "Pendente",
        "pago" => "Pago",
        "em_preparacao" => "Em preparação",
        "enviado" => "Enviado"
    ];
    return $nomes[$estado] ?? ucfirst($estado);
}

function classeEstado($estado) {
    $classes = [
        "pendente" => "status-pendente",
        "pago" => "status-pago",
        "em_preparacao" => "status-preparacao",
        "enviado" => "status-enviado"
    ];
    return $classes[$estado] ?? "status-pendente";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar_estado"])) {
    $encomendaId = filter_input(INPUT_POST, "encomenda_id", FILTER_VALIDATE_INT);
    $novoEstado = $_POST["estado"] ?? "";

    if (!$encomendaId || !in_array($novoEstado, $estadosPermitidos, true)) {
        $erro = "Não foi possível atualizar a encomenda.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET estado = :estado WHERE id = :id");
        $stmt->execute([":estado" => $novoEstado, ":id" => $encomendaId]);
        $mensagem = "Estado da encomenda #{$encomendaId} atualizado.";
    }
}

$sql = "
    SELECT
        orders.id,
        orders.data_encomenda,
        orders.estado,
        orders.total,
        orders.nome_envio,
        orders.email_envio,
        orders.telefone_envio,
        orders.morada_envio,
        orders.cidade_envio,
        orders.codigo_postal_envio,
        users.nome AS cliente,
        users.email AS cliente_email,
        COALESCE(SUM(order_items.quantidade), 0) AS total_itens
    FROM orders
    INNER JOIN users ON users.id = orders.user_id
    LEFT JOIN order_items ON order_items.order_id = orders.id
    GROUP BY
        orders.id,
        orders.data_encomenda,
        orders.estado,
        orders.total,
        orders.nome_envio,
        orders.email_envio,
        orders.telefone_envio,
        orders.morada_envio,
        orders.cidade_envio,
        orders.codigo_postal_envio,
        users.nome,
        users.email
    ORDER BY orders.data_encomenda DESC
";

$encomendas = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encomendas — GamerHub Admin</title>
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
        .admin-header h1{margin:.45rem 0 0;font-family:"Orbitron",sans-serif;font-size:clamp(2rem,4vw,3.5rem)}
        .admin-header-count{color:#aaaabd}.admin-header-count strong{color:#a95cff;font-family:"Orbitron",sans-serif;font-size:1.5rem}
        .admin-alert{margin-bottom:1rem;padding:1rem 1.1rem;border-radius:.8rem}
        .admin-alert-success{border:1px solid rgba(82,212,155,.35);background:rgba(25,135,84,.14);color:#7be2b3}
        .admin-alert-error{border:1px solid rgba(255,92,110,.35);background:rgba(220,53,69,.14);color:#ff8996}
        .order-card{margin-bottom:1rem;padding:1.35rem;border:1px solid rgba(255,255,255,.08);border-radius:1rem;background:#131322}
        .order-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.2rem}
        .order-number{margin:.3rem 0 0;font-family:"Orbitron",sans-serif;font-size:1.65rem}
        .order-client{margin-top:.45rem;color:#aaaabd}
        .order-total{text-align:right}.order-total strong{display:block;margin-top:.55rem;font-family:"Orbitron",sans-serif;font-size:1.35rem}
        .status-badge{display:inline-flex;padding:.35rem .7rem;border-radius:999px;font-size:.72rem;font-weight:800}
        .status-pendente{background:rgba(255,193,7,.16);color:#ffcf40}
        .status-pago{background:rgba(25,135,84,.16);color:#52d49b}
        .status-preparacao{background:rgba(13,110,253,.16);color:#67a7ff}
        .status-enviado{background:rgba(111,66,193,.18);color:#b48cff}
        .order-grid{display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:1rem;padding:1rem 0;border-top:1px solid rgba(255,255,255,.07);border-bottom:1px solid rgba(255,255,255,.07)}
        .order-detail small{display:block;margin-bottom:.3rem;color:#858599}.order-detail div{line-height:1.5}
        .order-actions{display:flex;align-items:end;justify-content:flex-end;gap:.8rem;margin-top:1rem}
        .order-actions label{display:block;margin-bottom:.35rem;color:#858599;font-size:.75rem;font-weight:700}
        .order-actions select{min-width:190px;height:44px;padding:0 .8rem;border:1px solid rgba(255,255,255,.12);border-radius:.65rem;outline:none;background:#19192a;color:#fff}
        .btn-purple{height:44px;padding:0 1rem;border:0;border-radius:.65rem;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:800}
        .admin-empty{padding:4rem 1rem;border:1px solid rgba(255,255,255,.08);border-radius:1rem;background:#131322;color:#858599;text-align:center}
        @media(max-width:900px){.admin-shell{grid-template-columns:1fr}.admin-sidebar{position:static;height:auto}.admin-sidebar-footer{position:static;margin-top:1.5rem}.admin-main{padding:1rem}.order-grid{grid-template-columns:1fr}.order-actions{align-items:stretch;flex-direction:column}.order-actions select,.btn-purple{width:100%}}
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="index.php" class="admin-logo"><i class="bi bi-controller"></i>Gamer<span>Hub</span></a>
        <nav class="admin-nav">
            <a href="index.php"><i class="bi bi-grid"></i>Dashboard</a>
            <a href="produtos.php"><i class="bi bi-box-seam"></i>Produtos</a>
            <a href="categorias.php"><i class="bi bi-tags"></i>Categorias</a>
            <a href="encomendas.php" class="active"><i class="bi bi-bag-check"></i>Encomendas</a>
            <a href="utilizadores.php"><i class="bi bi-people"></i>Utilizadores</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="../index.php" class="btn btn-outline-light w-100 mb-2"><i class="bi bi-shop me-2"></i>Ver loja</a>
            <a href="../logout.php" class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-2"></i>Sair</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div><span>Gestão da loja</span><h1>Encomendas</h1></div>
            <div class="admin-header-count"><strong><?= count($encomendas) ?></strong> encomenda(s)</div>
        </header>

        <?php if ($mensagem !== ""): ?>
            <div class="admin-alert admin-alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($erro !== ""): ?>
            <div class="admin-alert admin-alert-error"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (empty($encomendas)): ?>
            <div class="admin-empty"><i class="bi bi-bag-x fs-1"></i><h2 class="mt-3">Ainda não existem encomendas</h2></div>
        <?php else: ?>
            <?php foreach ($encomendas as $encomenda): ?>
                <article class="order-card">
                    <div class="order-top">
                        <div>
                            <span class="section-eyebrow">Encomenda</span>
                            <h2 class="order-number">#<?= (int) $encomenda["id"] ?></h2>
                            <div class="order-client"><?= htmlspecialchars($encomenda["cliente"]) ?> · <?= htmlspecialchars($encomenda["cliente_email"]) ?></div>
                        </div>
                        <div class="order-total">
                            <span class="status-badge <?= classeEstado($encomenda["estado"]) ?>"><?= htmlspecialchars(nomeEstado($encomenda["estado"])) ?></span>
                            <strong><?= number_format((float) $encomenda["total"], 2, ",", ".") ?> €</strong>
                        </div>
                    </div>

                    <div class="order-grid">
                        <div class="order-detail">
                            <small>Data</small>
                            <div><?= date("d/m/Y H:i", strtotime($encomenda["data_encomenda"])) ?></div>
                        </div>
                        <div class="order-detail">
                            <small>Quantidade</small>
                            <div><?= (int) $encomenda["total_itens"] ?> item(ns)</div>
                        </div>
                        <div class="order-detail">
                            <small>Dados de entrega</small>
                            <div><?= htmlspecialchars($encomenda["nome_envio"]) ?></div>
                            <div><?= htmlspecialchars($encomenda["telefone_envio"]) ?></div>
                            <div><?= htmlspecialchars($encomenda["morada_envio"]) ?>, <?= htmlspecialchars($encomenda["codigo_postal_envio"]) ?> <?= htmlspecialchars($encomenda["cidade_envio"]) ?></div>
                        </div>
                    </div>

                    <form action="encomendas.php" method="post" class="order-actions">
                        <input type="hidden" name="encomenda_id" value="<?= (int) $encomenda["id"] ?>">
                        <div>
                            <label for="estado-<?= (int) $encomenda["id"] ?>">Alterar estado</label>
                            <select id="estado-<?= (int) $encomenda["id"] ?>" name="estado">
                                <?php foreach ($estadosPermitidos as $estado): ?>
                                    <option value="<?= htmlspecialchars($estado) ?>" <?= $estado === $encomenda["estado"] ? "selected" : "" ?>>
                                        <?= htmlspecialchars(nomeEstado($estado)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="atualizar_estado" class="btn-purple">
                            <i class="bi bi-check2 me-2"></i>Guardar estado
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>