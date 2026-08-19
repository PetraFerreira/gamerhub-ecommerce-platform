<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

if (!empty($_SESSION["utilizador_id"])) {
    header("Location: index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$erros = [];

$nome = "";
$email = "";
$telefone = "";
$morada = "";
$cidade = "";
$codigoPostal = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmarPassword = $_POST["confirmar_password"] ?? "";
    $telefone = trim($_POST["telefone"] ?? "");
    $morada = trim($_POST["morada"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $codigoPostal = trim($_POST["codigo_postal"] ?? "");

    /*
    |--------------------------------------------------------------------------
    | Validação
    |--------------------------------------------------------------------------
    */

    if ($nome === "") {
        $erros[] = "Indica o teu nome.";
    }

    if (strlen($nome) > 100) {
        $erros[] = "O nome não pode ter mais de 100 caracteres.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Indica um endereço de email válido.";
    }

    if (strlen($email) > 150) {
        $erros[] = "O email não pode ter mais de 150 caracteres.";
    }

    if (strlen($password) < 8) {
        $erros[] = "A palavra-passe deve ter pelo menos 8 caracteres.";
    }

    if ($password !== $confirmarPassword) {
        $erros[] = "As palavras-passe não coincidem.";
    }

    if (strlen($telefone) > 20) {
        $erros[] = "O telefone não pode ter mais de 20 caracteres.";
    }

    if (strlen($morada) > 255) {
        $erros[] = "A morada não pode ter mais de 255 caracteres.";
    }

    if (strlen($cidade) > 100) {
        $erros[] = "A cidade não pode ter mais de 100 caracteres.";
    }

    if (strlen($codigoPostal) > 20) {
        $erros[] = "O código postal não pode ter mais de 20 caracteres.";
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar se o email já existe
    |--------------------------------------------------------------------------
    */

    if (empty($erros)) {
        $sqlEmail = "
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmtEmail = $conn->prepare($sqlEmail);
        $stmtEmail->bindValue(":email", $email);
        $stmtEmail->execute();

        if ($stmtEmail->fetch()) {
            $erros[] = "Já existe uma conta associada a este email.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Criar utilizador
    |--------------------------------------------------------------------------
    */

    if (empty($erros)) {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sqlRegisto = "
            INSERT INTO users (
                nome,
                email,
                password,
                tipo_utilizador,
                telefone,
                morada,
                cidade,
                codigo_postal
            )
            VALUES (
                :nome,
                :email,
                :password,
                'cliente',
                :telefone,
                :morada,
                :cidade,
                :codigo_postal
            )
        ";

        $stmtRegisto = $conn->prepare($sqlRegisto);

        $stmtRegisto->bindValue(":nome", $nome);
        $stmtRegisto->bindValue(":email", $email);
        $stmtRegisto->bindValue(":password", $passwordHash);

        $stmtRegisto->bindValue(
            ":telefone",
            $telefone !== "" ? $telefone : null
        );

        $stmtRegisto->bindValue(
            ":morada",
            $morada !== "" ? $morada : null
        );

        $stmtRegisto->bindValue(
            ":cidade",
            $cidade !== "" ? $cidade : null
        );

        $stmtRegisto->bindValue(
            ":codigo_postal",
            $codigoPostal !== "" ? $codigoPostal : null
        );

        $stmtRegisto->execute();

        $utilizadorId = (int) $conn->lastInsertId();

        session_regenerate_id(true);

        $_SESSION["utilizador_id"] = $utilizadorId;
        $_SESSION["utilizador_nome"] = $nome;
        $_SESSION["utilizador_email"] = $email;
        $_SESSION["tipo_utilizador"] = "cliente";

        header("Location: index.php");
        exit;
    }
}

require_once "includes/header.php";
?>

<section class="auth-page">

    <div class="container">

        <div class="auth-layout">

            <div class="auth-intro">

                <span class="section-eyebrow">
                    Junta-te à comunidade
                </span>

                <h1>Cria a tua conta GamerHub</h1>

                <p>
                    Guarda os teus favoritos, acompanha as encomendas e
                    descobre equipamento selecionado para o teu setup.
                </p>

                <div class="auth-benefits">

                    <div>
                        <i class="bi bi-heart"></i>

                        <span>
                            <strong>Lista de favoritos</strong>
                            Guarda os produtos de que mais gostas.
                        </span>
                    </div>

                    <div>
                        <i class="bi bi-box-seam"></i>

                        <span>
                            <strong>Acompanha encomendas</strong>
                            Consulta o estado das tuas compras.
                        </span>
                    </div>

                    <div>
                        <i class="bi bi-shield-check"></i>

                        <span>
                            <strong>Conta protegida</strong>
                            A tua palavra-passe é guardada de forma segura.
                        </span>
                    </div>

                </div>

            </div>

            <div class="auth-card">

                <div class="auth-card-heading">

                    <span class="section-eyebrow">
                        Nova conta
                    </span>

                    <h2>Registo</h2>

                    <p>
                        Preenche os teus dados para começares.
                    </p>

                </div>

                <?php if (!empty($erros)): ?>

                    <div class="auth-alert auth-alert-error">

                        <i class="bi bi-exclamation-circle"></i>

                        <div>

                            <?php foreach ($erros as $erro): ?>

                                <p>
                                    <?= htmlspecialchars($erro) ?>
                                </p>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <form
                    action="register.php"
                    method="post"
                    class="auth-form"
                >

                    <div class="auth-field auth-field-full">

                        <label for="nome">
                            Nome completo
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-person"></i>

                            <input
                                id="nome"
                                type="text"
                                name="nome"
                                value="<?= htmlspecialchars($nome) ?>"
                                maxlength="100"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>

                    <div class="auth-field auth-field-full">

                        <label for="email">
                            Email
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-envelope"></i>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                maxlength="150"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>

                    <div class="auth-fields-row">

                        <div class="auth-field">

                            <label for="password">
                                Palavra-passe
                            </label>

                            <div class="auth-input">

                                <i class="bi bi-lock"></i>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                            </div>

                        </div>

                        <div class="auth-field">

                            <label for="confirmar_password">
                                Confirmar palavra-passe
                            </label>

                            <div class="auth-input">

                                <i class="bi bi-lock-fill"></i>

                                <input
                                    id="confirmar_password"
                                    type="password"
                                    name="confirmar_password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                            </div>

                        </div>

                    </div>

                    <div class="auth-fields-row">

                        <div class="auth-field">

                            <label for="telefone">
                                Telefone
                            </label>

                            <div class="auth-input">

                                <i class="bi bi-telephone"></i>

                                <input
                                    id="telefone"
                                    type="tel"
                                    name="telefone"
                                    value="<?= htmlspecialchars($telefone) ?>"
                                    maxlength="20"
                                    autocomplete="tel"
                                >

                            </div>

                        </div>

                        <div class="auth-field">

                            <label for="codigo_postal">
                                Código postal
                            </label>

                            <div class="auth-input">

                                <i class="bi bi-mailbox"></i>

                                <input
                                    id="codigo_postal"
                                    type="text"
                                    name="codigo_postal"
                                    value="<?= htmlspecialchars($codigoPostal) ?>"
                                    maxlength="20"
                                    autocomplete="postal-code"
                                >

                            </div>

                        </div>

                    </div>

                    <div class="auth-field auth-field-full">

                        <label for="morada">
                            Morada
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-house"></i>

                            <input
                                id="morada"
                                type="text"
                                name="morada"
                                value="<?= htmlspecialchars($morada) ?>"
                                maxlength="255"
                                autocomplete="street-address"
                            >

                        </div>

                    </div>

                    <div class="auth-field auth-field-full">

                        <label for="cidade">
                            Cidade
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-geo-alt"></i>

                            <input
                                id="cidade"
                                type="text"
                                name="cidade"
                                value="<?= htmlspecialchars($cidade) ?>"
                                maxlength="100"
                                autocomplete="address-level2"
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn auth-submit-button"
                    >
                        Criar conta
                        <i class="bi bi-arrow-right"></i>
                    </button>

                </form>

                <div class="auth-footer">

                    Já tens conta?

                    <a href="login.php">
                        Iniciar sessão
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>