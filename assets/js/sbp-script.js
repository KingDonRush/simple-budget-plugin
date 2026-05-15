jQuery(function ($) {
    var config = window.sbp_ajax || {};
    var i18n = window.sbp_i18n_js || {};
    var debugMode = Boolean(window.sbp_debug || config.debug);

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

    function getCart() {
        var storedCart = localStorage.getItem('sbp_cart');

        if (!storedCart) {
            return [];
        }

        try {
            var cart = JSON.parse(storedCart);
            return Array.isArray(cart) ? cart.map(String) : [];
        } catch (error) {
            debugWarn('Erro ao tentar parsear o carrinho:', error);
            return [];
        }
    }

    function saveCart(cart) {
        if (!Array.isArray(cart)) {
            return;
        }

        var uniqueCart = [];

        cart.map(String).forEach(function (id) {
            if (id && uniqueCart.indexOf(id) === -1) {
                uniqueCart.push(id);
            }
        });

        localStorage.setItem('sbp_cart', JSON.stringify(uniqueCart));
        debugLog('Carrinho salvo:', uniqueCart);
    }

    function getCurrentProductId($trigger) {
        var explicitId = $trigger.data('sbp-product-id') || $trigger.data('product-id');

        if (explicitId) {
            return String(explicitId);
        }

        var $context = $trigger.closest('[data-sbp-product-id], [data-product-id]');
        var contextId = $context.data('sbp-product-id') || $context.data('product-id');

        if (contextId) {
            return String(contextId);
        }

        var postInputId = $('input[name="post_ID"]').val();

        if (postInputId) {
            return String(postInputId);
        }

        var bodyMatch = document.body.className.match(/postid-(\d+)/);

        return bodyMatch ? String(bodyMatch[1]) : null;
    }

    function getLegacyFeedbackMode($trigger) {
        return $trigger.is('#add-to-cart-button') ? 'alert' : 'none';
    }

    function notify(message, mode) {
        if ('alert' === mode && message) {
            alert(message);
        }
    }

    function publishCartUpdated() {
        $(document).trigger('sbp:cart-updated', [getCart()]);
    }

    function addToCart(productId, feedbackMode) {
        if (!productId) {
            notify(i18n.product_add_error || 'Erro ao adicionar o produto ao carrinho.', feedbackMode);
            return;
        }

        var cart = getCart();

        if (cart.indexOf(String(productId)) === -1) {
            cart.push(String(productId));
            saveCart(cart);
            notify(i18n.product_added || 'Produto adicionado ao carrinho!', feedbackMode);
            publishCartUpdated();
        } else {
            notify(i18n.product_exists || 'Produto já está no carrinho!', feedbackMode);
        }
    }

    function removeFromCart(productId) {
        if (!productId) {
            return;
        }

        var cart = getCart().filter(function (id) {
            return String(id) !== String(productId);
        });

        saveCart(cart);
        publishCartUpdated();
    }

    function toggleCartItem(productId, feedbackMode) {
        var cart = getCart();

        if (cart.indexOf(String(productId)) === -1) {
            addToCart(productId, feedbackMode);
        } else {
            removeFromCart(productId);
        }
    }

    function getListingDisplay($listing) {
        return {
            show_image: $listing.data('sbp-show-image') || 'yes',
            show_remove: $listing.data('sbp-show-remove') || 'yes',
            remove_text: $listing.data('sbp-remove-text') || i18n.remove_text || 'Remover'
        };
    }

    function renderEmptyListing($listing) {
        var emptyMessage = $listing.data('sbp-empty-message') || i18n.cart_empty || 'Seu carrinho está vazio.';

        $listing.find('.sbp-budget-listing__items').html(
            '<p class="sbp-budget-listing__empty">' + escapeHtml(emptyMessage) + '</p>'
        );
        $listing.find('.sbp-budget-listing__submit').addClass('sbp-is-hidden');
    }

    function renderBudgetListing($listing) {
        var cart = getCart();

        if (!cart.length) {
            renderEmptyListing($listing);
            return;
        }

        $.ajax({
            url: config.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_get_cart_products',
                nonce: config.nonce,
                product_ids: cart,
                display: getListingDisplay($listing)
            },
            success: function (response) {
                if (response.success) {
                    $listing.find('.sbp-budget-listing__items').html(response.data);
                    $listing.find('.sbp-budget-listing__submit').removeClass('sbp-is-hidden');
                } else {
                    renderEmptyListing($listing);
                }
            },
            error: function () {
                $listing.find('.sbp-budget-listing__items').html(
                    '<p class="sbp-budget-listing__empty">' + escapeHtml(i18n.load_error || 'Erro ao carregar o carrinho.') + '</p>'
                );
                $listing.find('.sbp-budget-listing__submit').addClass('sbp-is-hidden');
            }
        });
    }

    function renderBudgetListings() {
        $('.sbp-budget-listing').each(function () {
            renderBudgetListing($(this));
        });
    }

    function renderLegacyCart() {
        var $legacyCart = $('#sbp-cart-items');

        if (!$legacyCart.length) {
            return;
        }

        var cart = getCart();

        if (!cart.length) {
            $legacyCart.html('<p>' + escapeHtml(i18n.cart_empty || 'Seu carrinho está vazio.') + '</p>');
            $('#enviar-orcamento-whatsapp').hide();
            return;
        }

        $.ajax({
            url: config.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_get_cart_products',
                nonce: config.nonce,
                product_ids: cart
            },
            success: function (response) {
                if (response.success) {
                    $legacyCart.html(response.data);
                    $('#enviar-orcamento-whatsapp').show();
                } else {
                    $legacyCart.html('<p>' + escapeHtml(i18n.load_error || 'Erro ao carregar o carrinho.') + '</p>');
                    $('#enviar-orcamento-whatsapp').hide();
                }
            },
            error: function () {
                $legacyCart.html('<p>' + escapeHtml(i18n.load_error || 'Erro ao carregar o carrinho.') + '</p>');
                $('#enviar-orcamento-whatsapp').hide();
            }
        });
    }

    function renderElementorTemplate($container, html) {
        $container.html(html);
        runElementorReadyTriggers($container);
        renderBudgetListings();
        updateActionStates();
    }

    function runElementorReadyTriggers($scope) {
        if (
            !window.elementorFrontend ||
            !window.elementorFrontend.elementsHandler ||
            typeof window.elementorFrontend.elementsHandler.runReadyTrigger !== 'function'
        ) {
            return;
        }

        $scope.find('.elementor-element').each(function () {
            window.elementorFrontend.elementsHandler.runReadyTrigger($(this));
        });
    }

    function showTemplateShell($popup) {
        $popup.find('#sbp-custom-popup-fallback').attr('hidden', true);
        $popup.find('#sbp-custom-popup-template').removeAttr('hidden');
    }

    function showLegacyShell($popup) {
        $popup.find('#sbp-custom-popup-template').attr('hidden', true).empty();
        $popup.find('#sbp-custom-popup-fallback').removeAttr('hidden');
    }

    function loadCartTemplate($popup, templateId) {
        var $template = $popup.find('#sbp-custom-popup-template');

        showTemplateShell($popup);
        $template.html('<p class="sbp-budget-listing__empty">' + escapeHtml(i18n.template_loading || 'Carregando orçamento...') + '</p>');

        $.ajax({
            url: config.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_render_cart_template',
                nonce: config.nonce,
                template_id: templateId
            },
            success: function (response) {
                if (response.success && response.data && response.data.html) {
                    renderElementorTemplate($template, response.data.html);
                } else {
                    $template.html('<p class="sbp-budget-listing__empty">' + escapeHtml(i18n.template_error || 'Erro ao carregar o template do carrinho.') + '</p>');
                }
            },
            error: function () {
                $template.html('<p class="sbp-budget-listing__empty">' + escapeHtml(i18n.template_error || 'Erro ao carregar o template do carrinho.') + '</p>');
            }
        });
    }

    function renderAllCarts() {
        renderBudgetListings();
        renderLegacyCart();
        updateActionStates();
    }

    function updateActionStates() {
        var cart = getCart();

        $('.sbp-budget-action[data-sbp-action="toggle"], .sbp-budget-action[data-sbp-action="add"], .sbp-budget-action[data-sbp-action="remove"]').each(function () {
            var $button = $(this);
            var productId = getCurrentProductId($button);
            var isActive = productId && cart.indexOf(String(productId)) !== -1;

            $button.toggleClass('sbp-is-active', Boolean(isActive));
            $button.attr('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function openPopup(templateId) {
        var $popup = $('#sbp-custom-popup');

        if (!$popup.length) {
            console.error(i18n.popup_not_found || 'Erro: Popup do carrinho não foi encontrado na página.');
            return;
        }

        $('body').addClass('sbp-popup-open');
        $popup.attr('aria-hidden', 'false');
        $popup.fadeIn();

        if (templateId) {
            loadCartTemplate($popup, templateId);
        } else {
            showLegacyShell($popup);
            renderLegacyCart();
        }

        setTimeout(function () {
            $popup.find('.sbp-custom-popup-content').trigger('focus');
        }, 50);
    }

    function closePopup() {
        var $popup = $('#sbp-custom-popup');

        $('body').removeClass('sbp-popup-open');
        $popup.attr('aria-hidden', 'true');

        if (!$popup.is(':visible')) {
            return;
        }

        $popup.fadeOut();
    }

    function sendWhatsAppBudget() {
        var cart = getCart();

        if (!cart.length) {
            alert(i18n.cart_empty || 'Seu carrinho está vazio.');
            return;
        }

        $.ajax({
            url: config.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_send_whatsapp_message',
                nonce: config.nonce,
                cart: cart
            },
            success: function (response) {
                if (response.success && response.data && response.data.url) {
                    window.open(response.data.url, '_blank');
                } else {
                    alert(response.data || i18n.whatsapp_error || 'Erro ao gerar a mensagem do WhatsApp.');
                }
            },
            error: function () {
                alert(i18n.whatsapp_error || 'Erro ao gerar a mensagem do WhatsApp.');
            }
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    $(document).on('click', '.sbp-budget-action, #add-to-cart-button, #open-cart-button, #enviar-orcamento-whatsapp', function (event) {
        var $trigger = $(this);
        var action = $trigger.data('sbp-action');

        if ($trigger.is('#add-to-cart-button')) {
            action = 'add';
        } else if ($trigger.is('#open-cart-button')) {
            action = 'open_cart';
        } else if ($trigger.is('#enviar-orcamento-whatsapp')) {
            action = 'send_whatsapp';
        }

        if (!action) {
            return;
        }

        event.preventDefault();

        var productId = getCurrentProductId($trigger);
        var feedbackMode = getLegacyFeedbackMode($trigger);

        if ('add' === action) {
            addToCart(productId, feedbackMode);
        } else if ('remove' === action) {
            removeFromCart(productId);
        } else if ('toggle' === action) {
            toggleCartItem(productId, feedbackMode);
        } else if ('open_cart' === action) {
            openPopup($trigger.data('sbp-template-id') || '');
        } else if ('close_cart' === action) {
            closePopup();
        } else if ('send_whatsapp' === action) {
            sendWhatsAppBudget();
        }
    });

    $(document).on('click', '.sbp-close-popup', function () {
        closePopup();
    });

    $(window).on('click', function (event) {
        var $popup = $('#sbp-custom-popup');

        if ($popup.length && $(event.target).is($popup)) {
            closePopup();
        }
    });

    $(document).on('keydown', function (event) {
        if ('Escape' === event.key) {
            closePopup();
        }
    });

    $(document).on('sbp:cart-updated', function () {
        renderAllCarts();
    });

    renderAllCarts();
});
