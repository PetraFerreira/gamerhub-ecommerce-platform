<?php require_once "includes/header.php"; ?>

<section class="container py-5">

    <div class="text-center mb-5">

        <h1 class="fw-bold">Perguntas Frequentes (FAQ)</h1>

        <p class="text-muted">
            Encontra aqui as respostas às dúvidas mais frequentes sobre a GamerHub.
        </p>

    </div>

    <div class="accordion" id="faqAccordion">

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq1">

                    Como posso efetuar uma encomenda?

                </button>

            </h2>

            <div id="faq1" class="accordion-collapse collapse show"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Basta adicionar os produtos ao carrinho e concluir o checkout.

                </div>

            </div>

        </div>

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq2">

                    Posso acompanhar a minha encomenda?

                </button>

            </h2>

            <div id="faq2"
                class="accordion-collapse collapse"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Sim. Depois de iniciares sessão, consulta a página
                    <strong>As Minhas Encomendas</strong> para acompanhar o estado.

                </div>

            </div>

        </div>

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq3">

                    Quais os métodos de pagamento disponíveis?

                </button>

            </h2>

            <div id="faq3"
                class="accordion-collapse collapse"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Visa, Mastercard, MB WAY, Multibanco e PayPal
                    (simulação para fins académicos).

                </div>

            </div>

        </div>

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq4">

                    Quanto tempo demora a entrega?

                </button>

            </h2>

            <div id="faq4"
                class="accordion-collapse collapse"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Entre 2 e 5 dias úteis para Portugal Continental e Madeira.

                </div>

            </div>

        </div>

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq5">

                    Posso devolver um produto?

                </button>

            </h2>

            <div id="faq5"
                class="accordion-collapse collapse"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Sim. Aceitamos devoluções no prazo de 14 dias,
                    desde que o produto esteja em perfeitas condições.

                </div>

            </div>

        </div>

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq6">

                    Este website é uma loja real?

                </button>

            </h2>

            <div id="faq6"
                class="accordion-collapse collapse"
                data-bs-parent="#faqAccordion">

                <div class="accordion-body">

                    Não. A GamerHub foi desenvolvida como projeto académico
                    para demonstrar competências em desenvolvimento Full Stack
                    Web Development.

                </div>

            </div>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>