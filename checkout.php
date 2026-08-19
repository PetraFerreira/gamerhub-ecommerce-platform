<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "config/database.php";

if (empty($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION["carrinho"])) {
    header("Location: carrinho.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$userId = (int) $_SESSION["utilizador_id"];
$erros = [];

$stmt = $conn->prepare("
    SELECT nome, email, telefone, morada, cidade, codigo_postal
    FROM users
    WHERE id = :id
    LIMIT 1
");
$stmt->execute([":id" => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$nome = $user["nome"] ?? "";
$email = $user["email"] ?? "";
$telefone = $user["telefone"] ?? "";
$morada = $user["morada"] ?? "";
$cidade = $user["cidade"] ?? "";
$codigoPostal = $user["codigo_postal"] ?? "";

$itens = [];
$subtotal = 0;

foreach ($_SESSION["carrinho"] as $chave => $quantidade) {
    if (!preg_match('/^produto-(\d+)(?:-plataforma-(\d+))?$/', $chave, $m)) {
        continue;
    }

    $produtoId = (int) $m[1];
    $plataformaId = isset($m[2]) ? (int) $m[2] : null;

    $stmt = $conn->prepare("
        SELECT id, nome, preco, preco_promocional, stock
        FROM products
        WHERE id = :id AND ativo = 1
        LIMIT 1
    ");
    $stmt->execute([":id" => $produtoId]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) continue;

    $produto["plataforma"] = null;
    $produto["plataforma_id"] = null;

    if ($plataformaId) {
        $stmt = $conn->prepare("
            SELECT id, plataforma, preco, preco_promocional, stock
            FROM product_platforms
            WHERE id = :plataforma_id AND product_id = :produto_id
            LIMIT 1
        ");
        $stmt->execute([
            ":plataforma_id" => $plataformaId,
            ":produto_id" => $produtoId
        ]);

        $variante = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$variante) continue;

        $produto["plataforma_id"] = (int) $variante["id"];
        $produto["plataforma"] = $variante["plataforma"];
        $produto["preco"] = $variante["preco"];
        $produto["preco_promocional"] = $variante["preco_promocional"];
        $produto["stock"] = $variante["stock"];
    }

    $quantidade = min(max(1, (int) $quantidade), (int) $produto["stock"]);
    if ($quantidade < 1) continue;

    $precoUnitario =
        !empty($produto["preco_promocional"])
        && (float) $produto["preco_promocional"] < (float) $produto["preco"]
            ? (float) $produto["preco_promocional"]
            : (float) $produto["preco"];

    $linha = $precoUnitario * $quantidade;

    $produto["quantidade"] = $quantidade;
    $produto["preco_unitario"] = $precoUnitario;
    $produto["subtotal"] = $linha;

    $itens[] = $produto;
    $subtotal += $linha;
}

if (!$itens) {
    $_SESSION["carrinho"] = [];
    header("Location: carrinho.php");
    exit;
}

$portes = $subtotal >= 50 ? 0 : 4.99;
$total = $subtotal + $portes;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome_envio"] ?? "");
    $email = trim($_POST["email_envio"] ?? "");
    $telefone = trim($_POST["telefone_envio"] ?? "");
    $morada = trim($_POST["morada_envio"] ?? "");
    $cidade = trim($_POST["cidade_envio"] ?? "");
    $codigoPostal = trim($_POST["codigo_postal_envio"] ?? "");
    $metodoPagamento =
    $_POST["metodo_pagamento"] ?? "";

    if ($nome === "") $erros[] = "Indica o nome.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "Indica um email válido.";
    if ($telefone === "") $erros[] = "Indica o telefone.";
    if ($morada === "") $erros[] = "Indica a morada.";
    if ($cidade === "") $erros[] = "Indica a cidade.";
    if ($codigoPostal === "") $erros[] = "Indica o código postal.";
    $metodosPermitidos = [
    "cartao",
    "mbway",
    "multibanco",
    "paypal"
];

if (!in_array(
    $metodoPagamento,
    $metodosPermitidos,
    true
)) {
    $erros[] = "Seleciona um método de pagamento.";
}

    if (!$erros) {
        try {
            $conn->beginTransaction();

$stmt = $conn->prepare("
    INSERT INTO orders (
        user_id,
        estado,
        metodo_pagamento,
        total,
        nome_envio,
        email_envio,
        telefone_envio,
        morada_envio,
        cidade_envio,
        codigo_postal_envio
    )
    VALUES (
        :user_id,
        'pendente',
        :metodo_pagamento,
        :total,
        :nome,
        :email,
        :telefone,
        :morada,
        :cidade,
        :codigo_postal
    )
");

$stmt->execute([
    ":user_id" => $userId,
    ":metodo_pagamento" => $metodoPagamento,
    ":total" => $total,
    ":nome" => $nome,
    ":email" => $email,
    ":telefone" => $telefone,
    ":morada" => $morada,
    ":cidade" => $cidade,
    ":codigo_postal" => $codigoPostal
]);

            $orderId = (int) $conn->lastInsertId();

            $stmtItem = $conn->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantidade,
                    preco_unitario, subtotal
                ) VALUES (
                    :order_id, :product_id, :quantidade,
                    :preco_unitario, :subtotal
                )
            ");

            foreach ($itens as $produto) {
                $stmtItem->execute([
                    ":order_id" => $orderId,
                    ":product_id" => (int) $produto["id"],
                    ":quantidade" => (int) $produto["quantidade"],
                    ":preco_unitario" => $produto["preco_unitario"],
                    ":subtotal" => $produto["subtotal"]
                ]);

                if (!empty($produto["plataforma_id"])) {
                    $stmtStock = $conn->prepare("
                        UPDATE product_platforms
                        SET stock = stock - :qtd
                        WHERE id = :id AND stock >= :qtd
                    ");
                    $stmtStock->execute([
                        ":qtd" => (int) $produto["quantidade"],
                        ":id" => (int) $produto["plataforma_id"]
                    ]);
                } else {
                    $stmtStock = $conn->prepare("
                        UPDATE products
                        SET stock = stock - :qtd
                        WHERE id = :id AND stock >= :qtd
                    ");
                    $stmtStock->execute([
                        ":qtd" => (int) $produto["quantidade"],
                        ":id" => (int) $produto["id"]
                    ]);
                }

                if ($stmtStock->rowCount() !== 1) {
                    throw new Exception("Stock insuficiente.");
                }
            }

            $conn->commit();
            $_SESSION["carrinho"] = [];
            header("Location: encomendas.php?sucesso=" . $orderId);
            exit;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $erros[] = "Não foi possível concluir a encomenda.";
        }
    }
}

require_once "includes/header.php";
?>

<section class="checkout-page py-5">
<div class="container">
    <div class="mb-4">
        <span class="section-eyebrow">Finalizar compra</span>
        <h1>Dados de envio</h1>
    </div>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= htmlspecialchars($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" class="auth-card">
                <div class="auth-fields-row">
                    <div class="auth-field">
                        <label>Nome</label>
                        <div class="auth-input">
                            <i class="bi bi-person"></i>
                            <input type="text" name="nome_envio"
                                value="<?= htmlspecialchars($nome) ?>" required>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Email</label>
                        <div class="auth-input">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email_envio"
                                value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="auth-fields-row mt-3">
                    <div class="auth-field">
                        <label>Telefone</label>
                        <div class="auth-input">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="telefone_envio"
                                value="<?= htmlspecialchars($telefone) ?>" required>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Código postal</label>
                        <div class="auth-input">
                            <i class="bi bi-mailbox"></i>
                            <input type="text" name="codigo_postal_envio"
                                value="<?= htmlspecialchars($codigoPostal) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="auth-field mt-3">
                    <label>Morada</label>
                    <div class="auth-input">
                        <i class="bi bi-house"></i>
                        <input type="text" name="morada_envio"
                            value="<?= htmlspecialchars($morada) ?>" required>
                    </div>
                </div>

                <div class="auth-field mt-3">
    <label>Cidade</label>

    <div class="auth-input">
        <i class="bi bi-geo-alt"></i>

        <input
            type="text"
            name="cidade_envio"
            value="<?= htmlspecialchars($cidade) ?>"
            required
        >
    </div>
</div>

<div class="checkout-payment-section">

    <h2 class="checkout-section-title">
        Método de pagamento
    </h2>

    <div class="payment-options">

        <label class="payment-option">
            <input
                type="radio"
                name="metodo_pagamento"
                value="cartao"
                required
            >

            <span class="payment-option-content">
                <i class="bi bi-credit-card"></i>

                <span>
                    <strong>Cartão bancário</strong>
                    <small>Visa ou Mastercard</small>
                </span>
            </span>
        </label>

        <label class="payment-option">
            <input
                type="radio"
                name="metodo_pagamento"
                value="mbway"
                required
            >

            <span class="payment-option-content">
                <i class="bi bi-phone"></i>

                <span>
                    <strong>MB WAY</strong>
                    <small>Pagamento pelo telemóvel</small>
                </span>
            </span>
        </label>

        <label class="payment-option">
            <input
                type="radio"
                name="metodo_pagamento"
                value="multibanco"
                required
            >

            <span class="payment-option-content">
                <i class="bi bi-bank"></i>

                <span>
                    <strong>Multibanco</strong>
                    <small>Pagamento por referência</small>
                </span>
            </span>
        </label>

        <label class="payment-option">
            <input
                type="radio"
                name="metodo_pagamento"
                value="paypal"
                required
            >

            <span class="payment-option-content">
                <i class="bi bi-paypal"></i>

                <span>
                    <strong>PayPal</strong>
                    <small>Pagamento com conta PayPal</small>
                </span>
            </span>
        </label>

    </div>

<div id="payment-extra-fields">

    <div
        class="payment-extra-panel"
        data-payment-panel="cartao"
    >
        <h3>Dados do cartão</h3>

        <div class="payment-extra-grid">

            <div class="auth-field full-width">
                <label>Número do cartão</label>

                <div class="auth-input">
                    <i class="bi bi-credit-card-2-front"></i>

                    <input
                        type="text"
                        name="numero_cartao"
                        maxlength="19"
                        placeholder="0000 0000 0000 0000"
                    >
                </div>
            </div>

            <div class="auth-field">
                <label>Validade</label>

                <div class="auth-input">
                    <i class="bi bi-calendar"></i>

                    <input
                        type="text"
                        name="validade_cartao"
                        maxlength="5"
                        placeholder="MM/AA"
                    >
                </div>
            </div>

            <div class="auth-field">
                <label>CVV</label>

                <div class="auth-input">
                    <i class="bi bi-lock"></i>

                    <input
                        type="password"
                        name="cvv_cartao"
                        maxlength="4"
                        placeholder="123"
                    >
                </div>
            </div>

            <div class="auth-field full-width">
                <label>Nome no cartão</label>

                <div class="auth-input">
                    <i class="bi bi-person"></i>

                    <input
                        type="text"
                        name="nome_cartao"
                        placeholder="Nome completo"
                    >
                </div>
            </div>

        </div>
    </div>

    <div
        class="payment-extra-panel"
        data-payment-panel="mbway"
    >
        <h3>Pagamento por MB WAY</h3>

        <div class="auth-field">
            <label>Número de telemóvel</label>

            <div class="auth-input">
                <i class="bi bi-phone"></i>

                <input
                    type="tel"
                    name="telefone_mbway"
                    maxlength="15"
                    placeholder="+351 912 345 678"
                >
            </div>
        </div>

        <p class="payment-panel-note">
            Será apresentada uma confirmação simulada de pagamento.
        </p>
    </div>

    <div
        class="payment-extra-panel"
        data-payment-panel="multibanco"
    >
        <h3>Referência Multibanco</h3>

        <div class="multibanco-reference">

            <div>
                <span>Entidade</span>
                <strong>12345</strong>
            </div>

            <div>
                <span>Referência</span>
                <strong>
                    <?= random_int(100, 999) ?>
                    <?= random_int(100, 999) ?>
                    <?= random_int(100, 999) ?>
                </strong>
            </div>

            <div>
                <span>Valor</span>
                <strong>
                    <?= number_format($total, 2, ",", ".") ?> €
                </strong>
            </div>

        </div>

        <p class="payment-panel-note">
            Entidade e referência geradas apenas para demonstração.
        </p>
    </div>

    <div
        class="payment-extra-panel"
        data-payment-panel="paypal"
    >
        <h3>Entrar no PayPal</h3>

        <div class="payment-extra-grid">

            <div class="auth-field full-width">
                <label>Email PayPal</label>

                <div class="auth-input">
                    <i class="bi bi-envelope"></i>

                    <input
                        type="email"
                        name="paypal_email"
                        placeholder="email@exemplo.com"
                    >
                </div>
            </div>

            <div class="auth-field full-width">
                <label>Palavra-passe</label>

                <div class="auth-input">
                    <i class="bi bi-lock"></i>

                    <input
                        type="password"
                        name="paypal_password"
                        placeholder="Palavra-passe"
                    >
                </div>
            </div>

        </div>

        <p class="payment-panel-note">
            Login simulado. Nenhum dado é enviado para o PayPal.
        </p>
    </div>

</div>


    <p class="payment-demo-note">
        <i class="bi bi-info-circle"></i>
        Pagamento simulado para fins académicos.
    </p>

</div>

<button
    type="submit"
    class="btn auth-submit-button mt-4"
>
    Confirmar encomenda
</button>

        <div class="col-lg-5">
            <aside class="cart-summary">
                <span class="section-eyebrow">Resumo</span>
                <h2>A tua encomenda</h2>

                <?php foreach ($itens as $produto): ?>
                    <div class="summary-row">
                        <span>
                            <?= htmlspecialchars($produto["nome"]) ?>
                            × <?= (int) $produto["quantidade"] ?>
                            <?php if (!empty($produto["plataforma"])): ?>
                                <small class="d-block">
                                    <?= htmlspecialchars($produto["plataforma"]) ?>
                                </small>
                            <?php endif; ?>
                        </span>
                        <strong>
                            <?= number_format($produto["subtotal"], 2, ",", ".") ?> €
                        </strong>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong><?= number_format($subtotal, 2, ",", ".") ?> €</strong>
                </div>

                <div class="summary-row">
                    <span>Portes</span>
                    <strong>
                        <?= $portes == 0
                            ? "Grátis"
                            : number_format($portes, 2, ",", ".") . " €" ?>
                    </strong>
                </div>

                <div class="summary-total">
                    <span>Total</span>
                    <strong><?= number_format($total, 2, ",", ".") ?> €</strong>
                </div>
            </aside>
        </div>
    </div>
</div>
</section>

<?php require_once "includes/footer.php"; ?>