<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

if (empty($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$utilizadorId = (int) $_SESSION["utilizador_id"];
$erros = [];
$mensagemSucesso = "";

/*
|--------------------------------------------------------------------------
| Obter dados atuais
|--------------------------------------------------------------------------
*/

$sqlUtilizador = "
    SELECT
        id,
        nome,
        email,
        telefone,
        morada,
        cidade,
        codigo_postal,
        criado_em
    FROM users
    WHERE id = :utilizador_id
    LIMIT 1
";

$stmtUtilizador = $conn->prepare($sqlUtilizador);
$stmtUtilizador->bindValue(
    ":utilizador_id",
    $utilizadorId,
    PDO::PARAM_INT
);
$stmtUtilizador->execute();

$utilizador = $stmtUtilizador->fetch(PDO::FETCH_ASSOC);

if (!$utilizador) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$nome = $utilizador["nome"] ?? "";
$email = $utilizador["email"] ?? "";
$telefone = $utilizador["telefone"] ?? "";
$morada = $utilizador["morada"] ?? "";
$cidade = $utilizador["cidade"] ?? "";
$codigoPostal = $utilizador["codigo_postal"] ?? "";

/*
|--------------------------------------------------------------------------
| Atualizar perfil
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["guardar_perfil"])
) {
    $nome = trim($_POST["nome"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $morada = trim($_POST["morada"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $codigoPostal = trim($_POST["codigo_postal"] ?? "");

    if ($nome === "") {
        $erros[] = "Indica o teu nome.";
    }

    if (strlen($nome) > 100) {
        $erros[] = "O nome não pode ter mais de 100 caracteres.";
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

    if (empty($erros)) {
        $sqlAtualizar = "
            UPDATE users
            SET
                nome = :nome,
                telefone = :telefone,
                morada = :morada,
                cidade = :cidade,
                codigo_postal = :codigo_postal
            WHERE id = :utilizador_id
        ";

        $stmtAtualizar = $conn->prepare($sqlAtualizar);

        $stmtAtualizar->bindValue(":nome", $nome);
        $stmtAtualizar->bindValue(
            ":telefone",
            $telefone !== "" ? $telefone : null
        );
        $stmtAtualizar->bindValue(
            ":morada",
            $morada !== "" ? $morada : null
        );
        $stmtAtualizar->bindValue(
            ":cidade",
            $cidade !== "" ? $cidade : null
        );
        $stmtAtualizar->bindValue(
            ":codigo_postal",
            $codigoPostal !== "" ? $codigoPostal : null
        );
        $stmtAtualizar->bindValue(
            ":utilizador_id",
            $utilizadorId,
            PDO::PARAM_INT
        );

        $stmtAtualizar->execute();

        $_SESSION["utilizador_nome"] = $nome;

        $mensagemSucesso =
            "Os teus dados foram atualizados com sucesso.";
    }
}

require_once "includes/header.php";
?>

<section class="profile-page py-5">

    <div class="container">

        <div class="row g-4">

            <div class="col-lg-4">

                <aside class="auth-card h-100">

                    <span class="section-eyebrow">
                        Área de cliente
                    </span>

                    <h1 class="mt-3">
                        O meu perfil
                    </h1>

                    <p class="text-secondary">
                        Consulta e atualiza os teus dados pessoais.
                    </p>

                    <div class="mt-4">

                        <div class="d-flex align-items-center gap-3 mb-3">

                            <div class="profile-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>

                            <div>
                                <strong>
                                    <?= htmlspecialchars($nome) ?>
                                </strong>

                                <div class="text-secondary">
                                    <?= htmlspecialchars($email) ?>
                                </div>
                            </div>

                        </div>

                        <div class="profile-meta">

                            <span>
                                <i class="bi bi-calendar3"></i>
                                Cliente desde
                            </span>

                            <strong>
                                <?= date(
                                    "d/m/Y",
                                    strtotime($utilizador["criado_em"])
                                ) ?>
                            </strong>

                        </div>

                    </div>

                    <hr>

                    <div class="d-grid gap-2">

                        <a
                            href="encomendas.php"
                            class="btn btn-outline-light"
                        >
                            <i class="bi bi-box-seam me-2"></i>
                            As minhas encomendas
                        </a>

                        <a
                            href="logout.php"
                            class="btn btn-outline-danger"
                        >
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Terminar sessão
                        </a>

                    </div>

                </aside>

            </div>

            <div class="col-lg-8">

                <div class="auth-card">

                    <div class="auth-card-heading">

                        <span class="section-eyebrow">
                            Dados pessoais
                        </span>

                        <h2>Editar perfil</h2>

                        <p>
                            O email não pode ser alterado nesta área.
                        </p>

                    </div>

                    <?php if ($mensagemSucesso !== ""): ?>

                        <div class="alert alert-success">

                            <i class="bi bi-check-circle me-2"></i>

                            <?= htmlspecialchars($mensagemSucesso) ?>

                        </div>

                    <?php endif; ?>

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
                        action="perfil.php"
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
                                    value="<?= htmlspecialchars($email) ?>"
                                    disabled
                                >

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
                                        type="text"
                                        name="telefone"
                                        value="<?= htmlspecialchars($telefone) ?>"
                                        maxlength="20"
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
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            name="guardar_perfil"
                            class="btn auth-submit-button"
                        >
                            Guardar alterações
                            <i class="bi bi-check2"></i>
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>