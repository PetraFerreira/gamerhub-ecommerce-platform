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

$erro = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Indica um email válido.";
    } elseif ($password === "") {
        $erro = "Indica a tua palavra-passe.";
    } else {
        $sql = "
            SELECT
                id,
                nome,
                email,
                password,
                tipo_utilizador
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            !$utilizador
            || !password_verify(
                $password,
                $utilizador["password"]
            )
        ) {
            $erro = "Email ou palavra-passe incorretos.";
        } else {
            session_regenerate_id(true);

            $_SESSION["utilizador_id"] =
                (int) $utilizador["id"];

            $_SESSION["utilizador_nome"] =
                $utilizador["nome"];

            $_SESSION["utilizador_email"] =
                $utilizador["email"];

            $_SESSION["tipo_utilizador"] =
                $utilizador["tipo_utilizador"];

            header("Location: index.php");
            exit;
        }
    }
}

require_once "includes/header.php";
?>

<section class="auth-page">

    <div class="container">

        <div class="auth-layout">

            <div class="auth-intro">

                <span class="section-eyebrow">
                    Bem-vindo de volta
                </span>

                <h1>Entra na tua conta GamerHub</h1>

                <p>
                    Acede aos teus favoritos, encomendas e produtos
                    guardados.
                </p>

                <div class="auth-benefits">

                    <div>
                        <i class="bi bi-heart"></i>

                        <span>
                            <strong>Favoritos guardados</strong>
                            Retoma os produtos que marcaste.
                        </span>
                    </div>

                    <div>
                        <i class="bi bi-box-seam"></i>

                        <span>
                            <strong>Histórico de encomendas</strong>
                            Consulta as tuas compras.
                        </span>
                    </div>

                    <div>
                        <i class="bi bi-shield-check"></i>

                        <span>
                            <strong>Conta protegida</strong>
                            Sessão segura e palavra-passe encriptada.
                        </span>
                    </div>

                </div>

            </div>

            <div class="auth-card">

                <div class="auth-card-heading">

                    <span class="section-eyebrow">
                        Área de cliente
                    </span>

                    <h2>Iniciar sessão</h2>

                    <p>
                        Introduz os teus dados de acesso.
                    </p>

                </div>

                <?php if ($erro !== ""): ?>

                    <div class="auth-alert auth-alert-error">

                        <i class="bi bi-exclamation-circle"></i>

                        <div>
                            <p><?= htmlspecialchars($erro) ?></p>
                        </div>

                    </div>

                <?php endif; ?>

                <form
                    action="login.php"
                    method="post"
                    class="auth-form"
                >

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

                    <div class="auth-field auth-field-full">

                        <label for="password">
                            Palavra-passe
                        </label>

                        <div class="auth-input">

                            <i class="bi bi-lock"></i>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn auth-submit-button"
                    >
                        Entrar
                        <i class="bi bi-arrow-right"></i>
                    </button>

                </form>

                <div class="auth-footer">

                    Ainda não tens conta?

                    <a href="register.php">
                        Criar conta
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>