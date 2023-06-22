<div id="mbbxProductModal" class="<?= $data['style']['theme'] ?>">
    <div id="mbbxProductModalContent">
        <div id="mbbxProductModalHeader">
            <select name="mbbx-method-select" id="mbbx-method-select">
                <option id="0" value="0">Seleccione un método de pago</option>
                <?php foreach ($data['sources'] as $source) : ?>
                    <?php if (!empty($source['source']['name'])) : ?>
                        <option id="<?= $source['source']['reference'] ?>" value="<?= $source['source']['reference'] ?>"><?= $source['source']['name'] ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <span id="closembbxProduct">&times;</span>
        </div>
        <div id="mbbxProductModalBody">
            <?php foreach ($data['sources'] as $source) : ?>
                <?php if (!empty($source['source']['name'])) : ?>
                    <div id="<?= $source['source']['reference'] ?>" class="mobbexSource">
                        <p class="mobbexPaymentMethod">
                            <img src="https://res.mobbex.com/images/sources/jpg/<?= $source['source']['reference'] ?>.jpg"><?= $source['source']['name'] ?>
                        </p>
                        <?php if (!empty($source['installments']['list'])) : ?>
                            <table>
                                <?php foreach ($source['installments']['list'] as $installment) : ?>
                                    <tr>
                                        <td>
                                            <?= $installment['name'] ?>
                                            <?php if ($installment['totals']['installment']['count'] != 1) : ?>
                                                <small>
                                                    <?= $installment['totals']['installment']['count'] ?> cuotas de <?= $installment['totals']['installment']['amount'] ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right; "><?= isset($installment['totals']['total']) ? $installment['totals']['total'] : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else : ?>
                            <p class="mobbexSourceTotal">
                                <?= $data['price'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($data['style']['show_button']) : ?>
    <button id="mbbxProductBtn" class="button alt">
        <?php if ($data['style']['logo']) : ?>
            <img src="<?= $data['style']['logo'] ?>" alt="" width="40" height="40" style="margin-right: 15px; border-radius: 40px;">
        <?php endif; ?>
        <?= $data['style']['text'] ?>
    </button>
<?php endif; ?>

<!-- STYLES -->
<style>

    /* Custom Styles */
    <?= $data['style']['custom_styles'] ?>
    
    #mbbxProductBtn {
        margin: 10px;
        display: flex;
        align-items: center;
    }

    /* The Modal (background) */
    #mbbxProductModal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
        z-index: 99999999;
        place-items: center;
    }

    /* Modal Content/Box */
    #mbbxProductModalContent {
        background-color: #fefefe;
        padding: 20px;
        border: 1px solid #888;
        max-width: 650px;
        /* Could be more or less, depending on screen size */
        height: 90%;
        /* Full height */
        width: 100%;
        z-index: 10000;
        overflow-y: scroll;
        border-radius: 10px;
        box-sizing: border-box;
    }

    #mbbxProductModalHeader {
        display: flex;
        justify-content: space-between;
        flex-flow: wrap;
        align-items: center;
    }

    /* The Close Button */
    #closembbxProduct {
        color: #aaa;
        font-size: 28px;
        font-weight: 500;
    }

    #closembbxProduct:hover,
    #closembbxProduct:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    #mbbxProductBtn {
        padding: 20px;
    }

    /* Modal Scrollbar */
    #mbbxProductModalContent::-webkit-scrollbar {
        width: 20px;
    }

    #mbbxProductModalContent::-webkit-scrollbar-track {
        background-color: transparent;
    }

    #mbbxProductModalContent::-webkit-scrollbar-thumb {
        background-color: #d6dee1;
        border-radius: 20px;
        border: 6px solid transparent;
        background-clip: content-box;
    }

    #mbbxProductModalContent::-webkit-scrollbar-thumb:hover {
        background-color: #a8bbbf;
    }

    .mobbexSource {
        display: flex;
        justify-content: space-between;
        flex-flow: wrap;
    }

    .mobbexPaymentMethod {
        display: flex;
        align-items: center;
        padding: 1em 0;
        margin: 0;
        font-weight: bold;
    }

    .mobbexSourceTotal {
        display: flex;
        align-items: center;
        margin: 0;
        padding-right: 5% !important;
    }

    .mobbexSource td small {
        display: block;
        color: gray;
    }

    .mobbexSource table {
        border: none;
        width: 90%;
        margin: 0 auto;
    }

    #mbbxProductModalBody td {
        width: 65%;
        border: none;
        padding-right: 0;
    }

    .mobbexPaymentMethod img {
        height: 40px;
        border-radius: 100%;
        margin-right: 10px;
    }

    #mbbx-method-select {
        width: 94%;
        min-height: 40px;
        padding: 0.5rem;
        border: 1px #d8d8d8 solid;
        border-radius: 5px;
    }

    /* DARK MODE  */
    .dark #mbbxProductModalContent,
    .dark #mbbxProductModalContent table td {
        background-color: rgb(39, 31, 36);
        color: rgb(226, 226, 226);
    }

</style>

<!-- SCRIPTS -->
<script>

    (function(window, $) {
        /**
         * Hide/show element using grid.
         * 
         * @param {Element} element 
         */
        function toggleElement(element) {
            element.style.display = element.style.display != 'grid' ? 'grid' : 'none';
        }

        /**
         * Try to replace the previous modal, and positions it at the top of the document.
         */
        function replaceModal() {
            var modals = document.querySelectorAll('#mbbxProductModal');

            // If there are multiple modals, remove the first
            if (modals.length > 1)
                modals[0].remove();

            // Place new modal at the top of the document
            document.body.prepend(document.getElementById('mbbxProductModal'));
        }

        /**
         * Update the financial widget with the selected variant price.
         * 
         * @param {int} variantPrice 
         * @param {string} variantId 
         * @param {string} url 
         */
        function updateWidget(variantPrice, variantId, url) {
            $('#mbbxProductBtn').prop('disabled', true);
            $.ajax({
                dataType: 'json',
                method: 'POST',
                url: url,
                data: {
                    "variantId": variantId,
                    "variantPrice": variantPrice
                },
                success: (response) => {
                    $('#mbbxProductModal').replaceWith(response);
                },
                error: (error) => {
                    console.log(error);
                },
                complete: () => {
                    $('#mbbxProductBtn').prop('disabled', false);
                }
            });
        }

        window.addEventListener('load', function() {
            var modal = document.getElementById('mbbxProductModal');

            if (!modal)
                return false;

            // Add events to toggle modal
            document.querySelector('body').addEventListener('click', function(e) {
                var openBtn = document.getElementById('mbbxProductBtn');

                if (e.target == openBtn)
                    replaceModal();

                // Get new modal and close button
                var modal = document.getElementById('mbbxProductModal');
                var closeBtn = document.getElementById('closembbxProduct');

                if (e.target == openBtn || e.target == closeBtn || e.target == modal && !e.target.closest('#mbbxProductModalContent'))
                    toggleElement(modal);
            });

            //Trigger widget update when selected variation change
            $(document).on('found_variation', 'form.cart', function(event, variation) {
                updateWidget(variation.display_price, variation.variation_id, mobbexWidget.widgetUpdateUrl);
            });

            // Get sources and payment method selector 
            var sources = document.querySelectorAll('.mobbexSource');
            var methodSelect = document.getElementById('mbbx-method-select');

            // Filter payment methods in the modal
            methodSelect.addEventListener('change', function() {
                for (source of sources)
                    source.style.display = source.id != methodSelect.value && methodSelect.value != 0 ? 'none' : '';
            });
        });
    })(window, jQuery);

</script>