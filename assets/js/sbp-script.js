jQuery(document).ready(function ($) {
    var debugMode = Boolean(window.sbp_debug || (window.sbp_ajax && window.sbp_ajax.debug));

    function debugLog() {
        if (debugMode && window.console) {
            console.log.apply(console, arguments);
        }
    }

    function debugWarn() {
        if (debugMode && window.console) {
            console.warn.apply(console, arguments);
        }
    }

    debugLog("O script foi carregado e está funcionando!");

    // Função para abrir o popup do carrinho com verificações adicionais
    function openPopup() {
        debugLog("Abrindo popup do carrinho...");

        $('body').addClass('sbp-popup-open');

        if (!$('#sbp-custom-popup').length) {
            console.error(sbp_i18n_js.popup_not_found);
            return;
        }

        $('#sbp-custom-popup').fadeIn(function () {
            debugLog("Popup do carrinho exibido.");
        });

        renderCart();
    }

    // Função para fechar o popup do carrinho
    function closePopup() {
        debugLog("Fechando popup do carrinho...");

        $('body').removeClass('sbp-popup-open');

        if (!$('#sbp-custom-popup').is(':visible')) {
            debugWarn(sbp_i18n_js.popup_closed);
            return;
        }

        $('#sbp-custom-popup').fadeOut(function () {
            debugLog("Popup do carrinho fechado.");
        });
    }

    // Captura o clique no botão de abrir o carrinho
    $('#open-cart-button').on('click', function (e) {
        e.preventDefault();
        debugLog("Botão de abrir o carrinho clicado.");
        openPopup();
    });

    // Captura o clique no botão de fechar do popup
    $('.sbp-close-popup').on('click', function () {
        debugLog("Botão de fechar o carrinho clicado.");
        closePopup();
    });

    // Fecha o popup ao clicar fora do conteúdo
    $(window).on('click', function (event) {
        var popup = $('#sbp-custom-popup');
        if ($(event.target).is(popup)) {
            closePopup();
        }
    });

    // Função para obter o carrinho do localStorage
    function getCart() {
        var cart = localStorage.getItem('sbp_cart');

        if (!cart) {
            debugWarn("Carrinho vazio ou inexistente no localStorage.");
            return [];
        }

        try {
            cart = JSON.parse(cart);
        } catch (e) {
            console.error("Erro ao tentar parsear o carrinho:", e);
            return [];
        }

        debugLog("Carrinho carregado com sucesso:", cart);
        return cart;
    }

    // Função para salvar o carrinho
    function saveCart(cart) {
        if (!Array.isArray(cart)) {
            console.error("Erro: Tentativa de salvar um carrinho inválido no localStorage.");
            return;
        }

        localStorage.setItem('sbp_cart', JSON.stringify(cart));
        debugLog("Carrinho salvo com sucesso no localStorage:", cart);
    }

    // Função para renderizar o carrinho
    function renderCart() {
        var cart = getCart();

        if (cart.length === 0) {
            debugWarn("Carrinho está vazio. Exibindo mensagem de aviso.");
            $('#sbp-cart-items').html('<p>' + sbp_i18n_js.cart_empty + '</p>');
            $('#enviar-orcamento-whatsapp').hide();
            return;
        }

        debugLog("Iniciando renderização dos itens do carrinho...");

        $.ajax({
            url: sbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_get_cart_products',
                nonce: sbp_ajax.nonce,
                product_ids: cart
            },
            success: function (response) {
                debugLog("Resposta do servidor:", response);

                if (response.success) {
                    $('#sbp-cart-items').html(response.data);
                    $('#enviar-orcamento-whatsapp').show();
                    attachRemoveListeners();
                } else {
                    console.error("Erro ao carregar os produtos do carrinho:", response.data);
                    $('#sbp-cart-items').html('<p>' + sbp_i18n_js.load_error + '</p>');
                    $('#enviar-orcamento-whatsapp').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro na requisição AJAX:", status, error);
                $('#sbp-cart-items').html('<p>' + sbp_i18n_js.load_error + '</p>');
                $('#enviar-orcamento-whatsapp').hide();
            }
        });
    }

    // Captura o ID do produto atual
    function getCurrentProductId() {
        var productId = $('input[name="post_ID"]').val() || $('body').attr('class').match(/postid-(\d+)/)?.[1];

        if (!productId) {
            console.error("Erro: ID do produto não encontrado.");
            return null;
        }

        debugLog("ID do produto atual:", productId);
        return productId;
    }

    // Remove item do carrinho
    function removeFromCart(productId) {
        var cart = getCart();
        debugLog('Carrinho antes de remover:', cart);

        cart = cart.filter(id => String(id) !== String(productId));

        debugLog('Carrinho após remover item:', cart);
        saveCart(cart);
        debugLog('Carrinho salvo no localStorage:', JSON.parse(localStorage.getItem('sbp_cart')));

        renderCart();
        debugLog(`Produto ${productId} removido do carrinho.`);
    }

    // Adiciona produto ao carrinho
    $('#add-to-cart-button').on('click', function (e) {
        e.preventDefault();

        var productId = getCurrentProductId();

        if (productId) {
            var cart = getCart();
            if (!cart.includes(productId)) {
                cart.push(productId);
                saveCart(cart);
                debugLog("Produto adicionado ao carrinho:", productId);
                alert(sbp_i18n_js.product_added);
            } else {
                alert(sbp_i18n_js.product_exists);
            }
        } else {
            alert(sbp_i18n_js.product_add_error);
        }
    });

    // Eventos de remover item
    function attachRemoveListeners() {
        $('.sbp-remove-from-cart').off('click').on('click', function () {
            var productId = $(this).data('product-id');
            debugLog('Tentando remover produto com ID:', productId);
            removeFromCart(productId);
        });
    }

    // Gerar mensagem do WhatsApp
    function gerarMensagemWhatsApp() {
        var cart = getCart();
        if (cart.length > 0) {
            $.ajax({
                url: sbp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sbp_get_product_titles',
                    nonce: sbp_ajax.nonce,
                    product_ids: cart
                },
                success: function (response) {
                    if (response.success) {
                        var productTitles = response.data;
                        var message = sbp_i18n_js.whatsapp_intro;

                        productTitles.forEach(function (title, index) {
                            message += (index + 1) + " - " + title + ";\n";
                        });

                        if (sbp_ajax.whatsapp_number) {
                            var telefone = '+55' + sbp_ajax.whatsapp_number;
                            var url = "https://wa.me/" + telefone + "?text=" + encodeURIComponent(message);
                            window.open(url, '_blank');
                            debugLog("Abrindo WhatsApp com a mensagem:", message);
                        } else {
                            console.error(sbp_i18n_js.whatsapp_number_error);
                            alert(sbp_i18n_js.whatsapp_number_error);
                        }
                    } else {
                        console.error("Erro ao buscar títulos dos produtos:", response.data);
                        alert(sbp_i18n_js.whatsapp_error);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro na requisição AJAX:", status, error);
                    alert(sbp_i18n_js.whatsapp_error);
                }
            });
        } else {
            alert(sbp_i18n_js.cart_empty);
        }
    }

    // Clique no botão de WhatsApp
    $('#enviar-orcamento-whatsapp').on('click', function (e) {
        e.preventDefault();
        gerarMensagemWhatsApp();
    });

    // Exibir o carrinho ao carregar
    renderCart();
});
