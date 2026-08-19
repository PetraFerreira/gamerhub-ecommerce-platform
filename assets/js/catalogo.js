$(function () {

    const pesquisa = $('input[name="pesquisa"]');

    if (pesquisa.length === 0) {
        return;
    }

    pesquisa.on("keyup", function () {

        const texto = $(this)
            .val()
            .toLowerCase();

        $(".catalog-product-card").each(function () {

            const produto = $(this)
                .text()
                .toLowerCase();

            if (produto.indexOf(texto) > -1) {
                $(this)
                    .parent()
                    .show();
            } else {
                $(this)
                    .parent()
                    .hide();
            }

        });

    });

});