document.addEventListener("DOMContentLoaded", () => {
    const quantityInput = document.querySelector("#quantidade");
    const quantityButtons = document.querySelectorAll(".quantity-button");

    if (!quantityInput || quantityButtons.length === 0) {
        return;
    }

    quantityButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const action = button.dataset.action;
            const minimum = Number(quantityInput.min) || 1;
            const maximum = Number(quantityInput.max) || 999;
            let quantity = Number(quantityInput.value) || minimum;

            if (action === "increase" && quantity < maximum) {
                quantity++;
            }

            if (action === "decrease" && quantity > minimum) {
                quantity--;
            }

            quantityInput.value = quantity;
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const paymentInputs = document.querySelectorAll(
        'input[name="metodo_pagamento"]'
    );

    const paymentPanels = document.querySelectorAll(
        "[data-payment-panel]"
    );

    if (paymentInputs.length === 0) {
        return;
    }

    const updatePaymentPanel = (selectedMethod) => {
        paymentPanels.forEach((panel) => {
            const isActive =
                panel.dataset.paymentPanel === selectedMethod;

            panel.classList.toggle("active", isActive);

            panel.querySelectorAll("input").forEach((input) => {
                input.required = isActive;
            });
        });
    };

    paymentInputs.forEach((input) => {
        input.addEventListener("change", () => {
            updatePaymentPanel(input.value);
        });

        if (input.checked) {
            updatePaymentPanel(input.value);
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const wishlistButtons =
        document.querySelectorAll(".wishlist-button");

    console.log(
        "Botões de favoritos encontrados:",
        wishlistButtons.length
    );

    wishlistButtons.forEach((button) => {
        button.addEventListener("click", async (event) => {
            event.preventDefault();
            event.stopPropagation();

            const productId = button.dataset.productId;

            console.log(
                "Produto clicado:",
                productId
            );

            if (!productId) {
                alert(
                    "O botão não tem o ID do produto."
                );

                return;
            }

            const formData = new FormData();

            formData.append(
                "product_id",
                productId
            );

            try {
                const response = await fetch(
                    "favorito_toggle.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );

                const textoResposta =
                    await response.text();

                console.log(
                    "Resposta do PHP:",
                    textoResposta
                );

                let result;

                try {
                    result = JSON.parse(
                        textoResposta
                    );
                } catch (erroJson) {
                    alert(
                        "O PHP devolveu um erro. Abre a consola para veres a resposta."
                    );

                    return;
                }

                if (result.login_necessario) {
                    window.location.href =
                        "login.php";

                    return;
                }

                if (!result.sucesso) {
                    alert(
                        result.mensagem
                        || "Erro ao atualizar favoritos."
                    );

                    return;
                }

                const icon =
                    button.querySelector("i");

                button.classList.toggle(
    "active",
    result.favorito
);

icon.className =
    result.favorito
        ? "bi bi-heart-fill"
        : "bi bi-heart";

if (
    window.location.pathname.endsWith("favoritos.php")
    && !result.favorito
) {
    const productColumn = button.closest(
        ".col-sm-6, .col-md-6, .col-lg-3, .col-lg-4"
    );

    if (productColumn) {
        productColumn.remove();
    }
}

                alert(result.mensagem);
            } catch (erro) {
                console.error(erro);

                alert(
                    "Não foi possível contactar o ficheiro favorito_toggle.php."
                );
            }
        });
    });
});
