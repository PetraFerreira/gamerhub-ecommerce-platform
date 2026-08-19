<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once "config/database.php";

if (empty($_SESSION["utilizador_id"])) {
    http_response_code(401);

    echo json_encode([
        "sucesso" => false,
        "login_necessario" => true,
        "mensagem" => "Inicia sessão para adicionares favoritos."
    ]);

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

$produtoId = filter_input(
    INPUT_POST,
    "product_id",
    FILTER_VALIDATE_INT
);

if (!$produtoId) {
    http_response_code(422);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Produto inválido."
    ]);

    exit;
}

$userId = (int) $_SESSION["utilizador_id"];

$db = new Database();
$conn = $db->connect();

$stmtProduto = $conn->prepare("
    SELECT id
    FROM products
    WHERE id = :id
      AND ativo = 1
    LIMIT 1
");

$stmtProduto->execute([
    ":id" => $produtoId
]);

if (!$stmtProduto->fetch()) {
    http_response_code(404);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Produto não encontrado."
    ]);

    exit;
}

$stmtFavorito = $conn->prepare("
    SELECT id
    FROM wishlist
    WHERE user_id = :user_id
      AND product_id = :product_id
    LIMIT 1
");

$stmtFavorito->execute([
    ":user_id" => $userId,
    ":product_id" => $produtoId
]);

$favorito = $stmtFavorito->fetch(PDO::FETCH_ASSOC);

if ($favorito) {
    $stmtRemover = $conn->prepare("
        DELETE FROM wishlist
        WHERE user_id = :user_id
          AND product_id = :product_id
    ");

    $stmtRemover->execute([
        ":user_id" => $userId,
        ":product_id" => $produtoId
    ]);

    echo json_encode([
        "sucesso" => true,
        "favorito" => false,
        "mensagem" => "Produto removido dos favoritos."
    ]);

    exit;
}

$stmtAdicionar = $conn->prepare("
    INSERT INTO wishlist (
        user_id,
        product_id
    )
    VALUES (
        :user_id,
        :product_id
    )
");

$stmtAdicionar->execute([
    ":user_id" => $userId,
    ":product_id" => $produtoId
]);

echo json_encode([
    "sucesso" => true,
    "favorito" => true,
    "mensagem" => "Produto adicionado aos favoritos."
]);