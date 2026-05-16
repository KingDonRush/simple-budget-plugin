jQuery(function ($) {
    var config = window.sbp_ajax || {};
    var i18n = window.sbp_i18n_js || {};
    var debugMode = Boolean(window.sbp_debug || config.debug);
    var maxCartItems = parseInt(config.max_cart_items, 10) || 100;
    var maxItemQuantity = parseInt(config.max_item_quantity, 10) || 999;

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
            return normalizeCartIds(cart);
        } catch (error) {
            debugWarn('Erro ao tentar parsear o carrinho:', error);
            return [];
        }
    }

    function getCartQuantities() {
        var storedQuantities = localStorage.getItem('sbp_cart_quantities');
        var quantities = {};

        if (!storedQuantities) {
            return quantities;
        }

        try {
            quantities = JSON.parse(storedQuantities) || {};
        } catch (error) {
            debugWarn('Erro ao tentar parsear as quantidades:', error);
            return {};
        }

        Object.keys(quantities).forEach(function (id) {
            var quantity = parseInt(quantities[id], 10);

            if (!id || !quantity || quantity < 1) {
                delete quantities[id];
            } else {
                quantities[id] = Math.min(quantity, maxItemQuantity);
            }
        });

        return quantities;
    }

    function saveCart(cart) {
        if (!Array.isArray(cart)) {
            return;
        }

        var uniqueCart = [];

        normalizeCartIds(cart).forEach(function (id) {
            if (id && uniqueCart.indexOf(id) === -1) {
                uniqueCart.push(id);
            }
        });

        uniqueCart = uniqueCart.slice(0, maxCartItems);
        localStorage.setItem('sbp_cart', JSON.stringify(uniqueCart));
        pruneQuantities(uniqueCart);
        debugLog('Carrinho salvo:', uniqueCart);
    }

    function saveCartQuantities(quantities) {
        if (!quantities || 'object' !== typeof quantities) {
            quantities = {};
        }

        localStorage.setItem('sbp_cart_quantities', JSON.stringify(quantities));
    }

    function pruneQuantities(cart) {
        var quantities = getCartQuantities();
        var cartIds = Array.isArray(cart) ? cart.map(String) : [];
        var changed = false;

        Object.keys(quantities).forEach(function (id) {
            if (cartIds.indexOf(String(id)) === -1) {
                delete quantities[id];
                changed = true;
            }
        });

        if (changed) {
            saveCartQuantities(quantities);
        }
    }

    function normalizeQuantity(value) {
        var quantity = parseInt(value, 10);

        return quantity && quantity > 0 ? Math.min(quantity, maxItemQuantity) : 1;
    }

    function normalizeCartIds(cart) {
        if (!Array.isArray(cart)) {
            return [];
        }

        var ids = [];

        cart.forEach(function (id) {
            id = String(id);

            if (/^[1-9][0-9]*$/.test(id) && ids.indexOf(id) === -1) {
                ids.push(id);
            }
        });

        return ids.slice(0, maxCartItems);
    }

    function getTriggerQuantity($trigger) {
        return normalizeQuantity($trigger.data('sbp-quantity') || 1);
    }

    function setCartQuantity(productId, quantity, shouldPublish) {
        if (!productId) {
            return;
        }

        var cart = getCart();
        var productKey = String(productId);

        if (cart.indexOf(productKey) === -1) {
            return;
        }

        var quantities = getCartQuantities();
        quantities[productKey] = normalizeQuantity(quantity);
        saveCartQuantities(quantities);

        if (false !== shouldPublish) {
            publishCartUpdated();
        }
    }

    function getCurrentProductId($trigger) {
        var explicitId = $trigger.data('sbp-product-id');

        if (explicitId) {
            return String(explicitId);
        }

        var $context = $trigger.closest('[data-sbp-product-id]');
        var contextId = $context.data('sbp-product-id');

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

    function publishCartUpdated() {
        $(document).trigger('sbp:cart-updated', [getCart()]);
    }

    function addToCart(productId, quantity) {
        if (!productId) {
            return;
        }

        var cart = getCart();
        var productKey = String(productId);

        if (cart.indexOf(productKey) === -1) {
            cart.push(productKey);
            saveCart(cart);
            setCartQuantity(productKey, quantity || 1, false);
            publishCartUpdated();
        } else {
            var quantities = getCartQuantities();

            if (!quantities[productKey]) {
                quantities[productKey] = normalizeQuantity(quantity || 1);
                saveCartQuantities(quantities);
                publishCartUpdated();
            }
        }
    }

    function removeFromCart(productId) {
        if (!productId) {
            return;
        }

        var cart = getCart().filter(function (id) {
            return String(id) !== String(productId);
        });
        var quantities = getCartQuantities();

        delete quantities[String(productId)];

        saveCart(cart);
        saveCartQuantities(quantities);
        publishCartUpdated();
    }

    function toggleCartItem(productId, quantity) {
        var cart = getCart();

        if (cart.indexOf(String(productId)) === -1) {
            addToCart(productId, quantity);
        } else {
            removeFromCart(productId);
        }
    }

    function getListingDisplay($listing) {
        return {
            show_image: $listing.data('sbp-show-image') || 'yes',
            show_remove: $listing.data('sbp-show-remove') || 'yes',
            remove_text: $listing.data('sbp-remove-text') || i18n.remove_text || 'Remover',
            remove_position: getResponsiveData($listing, 'sbp-remove-position', 'inline_end'),
            show_quantity: $listing.data('sbp-show-quantity') || 'no',
            quantity_label: $listing.data('sbp-quantity-label') || i18n.quantity_label || 'Quantidade'
        };
    }

    function renderEmptyListing($listing) {
        var emptyMessage = $listing.data('sbp-empty-message') || i18n.cart_empty || 'Seu carrinho está vazio.';

        $listing.find('.sbp-budget-listing__items').html(
            '<p class="sbp-budget-listing__empty">' + escapeHtml(emptyMessage) + '</p>'
        );
        updateActionStates();
    }

    function renderBudgetListing($listing) {
        if ('yes' === $listing.attr('data-sbp-editor-preview')) {
            return;
        }

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
                quantities: getCartQuantities(),
                display: getListingDisplay($listing)
            },
            success: function (response) {
                if (response.success) {
                    $listing.find('.sbp-budget-listing__items').html(response.data);
                    $listing.find('.sbp-budget-listing__submit').removeClass('sbp-is-hidden');
                } else {
                    renderEmptyListing($listing);
                }
                updateActionStates();
            },
            error: function () {
                $listing.find('.sbp-budget-listing__items').html(
                    '<p class="sbp-budget-listing__empty">' + escapeHtml(i18n.load_error || 'Erro ao carregar o carrinho.') + '</p>'
                );
                updateActionStates();
            }
        });
    }

    function renderBudgetListings() {
        $('.sbp-budget-listing').each(function () {
            renderBudgetListing($(this));
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

    function showSetupFallback($popup) {
        $popup.find('#sbp-custom-popup-template').attr('hidden', true).empty();
        $popup.find('.sbp-template-setup h2').text(i18n.template_setup_title || 'Create a cart template');
        $popup.find('.sbp-template-setup p').text(
            i18n.template_setup_text ||
            'This Budget Button needs a Simple Budget template. Create one in Simple Budget > Templates, edit it with Elementor, then select it in the button settings.'
        );
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
                    showSetupFallback($popup);
                }
            },
            error: function () {
                showSetupFallback($popup);
            }
        });
    }

    function renderAllCarts() {
        renderBudgetListings();
        updateActionStates();
    }

    function updateActionStates() {
        var cart = getCart();

        $('.sbp-budget-action[data-sbp-action="toggle"], .sbp-budget-action[data-sbp-action="add"], .sbp-budget-action[data-sbp-action="remove"]').each(function () {
            var $button = $(this);

            if ($button.closest('[data-sbp-editor-preview="yes"]').length) {
                return;
            }

            var productId = getCurrentProductId($button);
            var isActive = productId && cart.indexOf(String(productId)) !== -1;

            $button.toggleClass('sbp-is-active', Boolean(isActive));
            $button.attr('aria-pressed', isActive ? 'true' : 'false');
        });

        updateSendWhatsAppStates(cart);
    }

    function updateSendWhatsAppStates(cart) {
        var isEmpty = !cart.length;

        $('.sbp-budget-action[data-sbp-action="send_whatsapp"]').each(function () {
            var $button = $(this);

            if ($button.closest('[data-sbp-editor-preview="yes"]').length) {
                return;
            }

            var behavior = $button.data('sbp-empty-behavior') || ($button.hasClass('sbp-budget-listing__submit') ? 'hide' : 'show_error');

            if (isEmpty) {
                if ('hide' === behavior) {
                    $button.addClass('sbp-is-hidden').removeClass('sbp-is-disabled');
                    $button.removeAttr('aria-disabled');
                    return;
                }

                $button.removeClass('sbp-is-hidden');

                if ('disable' === behavior) {
                    $button.addClass('sbp-is-disabled');
                    $button.attr('aria-disabled', 'true');
                    return;
                }

                $button.removeClass('sbp-is-disabled');
                $button.removeAttr('aria-disabled');
                return;
            }

            $button.removeClass('sbp-is-hidden sbp-is-disabled');
            $button.removeAttr('aria-disabled');
        });
    }

    function readPopupSettings($trigger) {
        if (!$trigger || !$trigger.length) {
            return {
                shell: 'modal',
                animation: 'fade_scale',
                panelWidth: '560px',
                overlayColor: '#000000',
                overlayOpacity: 50,
                closeOverlay: 'yes',
                closeEscape: 'yes',
                showClose: 'yes'
            };
        }

        return {
            shell: getResponsiveData($trigger, 'sbp-shell', 'modal'),
            animation: getResponsiveData($trigger, 'sbp-animation', 'fade_scale'),
            panelWidth: getResponsiveData($trigger, 'sbp-panel-width', '560px'),
            overlayColor: $trigger.data('sbp-overlay-color') || '#000000',
            overlayOpacity: getResponsiveData($trigger, 'sbp-overlay-opacity', 50),
            closeOverlay: $trigger.data('sbp-close-overlay') || 'yes',
            closeEscape: $trigger.data('sbp-close-escape') || 'yes',
            showClose: $trigger.data('sbp-show-close') || 'yes'
        };
    }

    function getResponsiveData($element, baseName, fallback) {
        var desktop = getDataOrFallback($element, baseName, fallback);
        var tablet = getDataOrFallback($element, baseName + '-tablet', desktop);
        var mobile = getDataOrFallback($element, baseName + '-mobile', tablet);
        var breakpoints = getElementorBreakpoints();

        if (window.matchMedia && window.matchMedia('(max-width: ' + breakpoints.mobile + 'px)').matches) {
            return mobile;
        }

        if (window.matchMedia && window.matchMedia('(max-width: ' + breakpoints.tablet + 'px)').matches) {
            return tablet;
        }

        return desktop;
    }

    function getDataOrFallback($element, name, fallback) {
        var value = $element.data(name);

        return 'undefined' === typeof value || '' === value ? fallback : value;
    }

    function getElementorBreakpoints() {
        var responsiveConfig = window.elementorFrontend &&
            window.elementorFrontend.config &&
            window.elementorFrontend.config.responsive &&
            window.elementorFrontend.config.responsive.activeBreakpoints
            ? window.elementorFrontend.config.responsive.activeBreakpoints
            : {};

        return {
            mobile: parseInt(responsiveConfig.mobile && responsiveConfig.mobile.value, 10) || 767,
            tablet: parseInt(responsiveConfig.tablet && responsiveConfig.tablet.value, 10) || 1024
        };
    }

    function applyPopupSettings($popup, settings) {
        $popup.attr('data-sbp-shell', settings.shell);
        $popup.attr('data-sbp-animation', settings.animation);
        $popup.attr('data-sbp-close-overlay', settings.closeOverlay);
        $popup.attr('data-sbp-close-escape', settings.closeEscape);
        $popup.attr('data-sbp-show-close', settings.showClose);
        $popup.css('--sbp-popup-panel-width', settings.panelWidth);
        $popup.css('--sbp-popup-overlay-color', getOverlayColor(settings.overlayColor, settings.overlayOpacity));
    }

    function getOverlayColor(color, opacity) {
        var normalizedOpacity = Math.max(0, Math.min(100, parseFloat(opacity) || 0)) / 100;
        var match = String(color || '#000000').replace('#', '').match(/^([0-9a-f]{3}|[0-9a-f]{6})$/i);
        var hex = match ? match[1] : '000000';

        if (hex.length === 3) {
            hex = hex.split('').map(function (character) {
                return character + character;
            }).join('');
        }

        return 'rgba(' + [
            parseInt(hex.substr(0, 2), 16),
            parseInt(hex.substr(2, 2), 16),
            parseInt(hex.substr(4, 2), 16),
            normalizedOpacity
        ].join(', ') + ')';
    }

    function openPopup(templateId, $trigger) {
        var $popup = $('#sbp-custom-popup');

        if (!$popup.length) {
            console.error(i18n.popup_not_found || 'Erro: Popup do carrinho não foi encontrado na página.');
            return;
        }

        var settings = readPopupSettings($trigger);

        applyPopupSettings($popup, settings);
        $('body').addClass('sbp-popup-open');
        $popup.attr('aria-hidden', 'false');
        $popup.removeClass('sbp-is-open');
        $popup.stop(true, true).css('display', 'bottom_sheet' === settings.shell ? 'flex' : 'block').hide().fadeIn(120);

        window.requestAnimationFrame(function () {
            $popup.addClass('sbp-is-open');
        });

        if (templateId) {
            loadCartTemplate($popup, templateId);
        } else {
            showSetupFallback($popup);
        }

        setTimeout(function () {
            $popup.find('.sbp-custom-popup-content').trigger('focus');
        }, 50);
    }

    function closePopup() {
        var $popup = $('#sbp-custom-popup');

        $('body').removeClass('sbp-popup-open');
        $popup.attr('aria-hidden', 'true');
        $popup.removeClass('sbp-is-open');

        if (!$popup.is(':visible')) {
            return;
        }

        window.setTimeout(function () {
            $popup.fadeOut(120);
        }, 180);
    }

    function triggerBlockedAnimation($button) {
        var animation = $button.data('sbp-empty-animation') || 'none';

        if ('none' === animation) {
            return;
        }

        $button.removeClass('sbp-empty-animate--shake sbp-empty-animate--pulse');
        void $button.get(0).offsetWidth;
        $button.addClass('sbp-empty-animate--' + animation);

        setTimeout(function () {
            $button.removeClass('sbp-empty-animate--' + animation);
        }, 650);
    }

    function sendWhatsAppBudget($trigger) {
        var cart = getCart();

        if (!cart.length) {
            if ($trigger && 'disable' === ($trigger.data('sbp-empty-behavior') || '')) {
                triggerBlockedAnimation($trigger);
                return;
            }

            alert(i18n.cart_empty || 'Seu carrinho está vazio.');
            return;
        }

        $.ajax({
            url: config.ajax_url,
            type: 'POST',
            data: {
                action: 'sbp_send_whatsapp_message',
                nonce: config.nonce,
                cart: cart,
                quantities: getCartQuantities()
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

    $(document).on('click', '.sbp-budget-action', function (event) {
        var $trigger = $(this);
        var action = $trigger.data('sbp-action');

        if ($trigger.closest('[data-sbp-editor-preview="yes"]').length) {
            event.preventDefault();
            return;
        }

        if (!action) {
            return;
        }

        event.preventDefault();

        var productId = getCurrentProductId($trigger);
        var quantity = getTriggerQuantity($trigger);

        if ('add' === action) {
            addToCart(productId, quantity);
        } else if ('remove' === action) {
            removeFromCart(productId);
        } else if ('toggle' === action) {
            toggleCartItem(productId, quantity);
        } else if ('open_cart' === action) {
            openPopup($trigger.data('sbp-template-id') || '', $trigger);
        } else if ('close_cart' === action) {
            closePopup();
        } else if ('send_whatsapp' === action) {
            if (!$trigger.hasClass('sbp-is-hidden') && 'true' === $trigger.attr('aria-disabled')) {
                triggerBlockedAnimation($trigger);
                return;
            }

            sendWhatsAppBudget($trigger);
        }
    });

    $(document).on('change', '.sbp-quantity-field, .sbp-quantity', function () {
        var $field = $(this);

        if ($field.closest('[data-sbp-editor-preview="yes"]').length) {
            return;
        }

        var productId = $field.data('sbp-product-id');

        setCartQuantity(productId, $field.val());
    });

    $(document).on('click', '.sbp-close-popup', function () {
        closePopup();
    });

    $(window).on('click', function (event) {
        var $popup = $('#sbp-custom-popup');

        if ($popup.length && $(event.target).is($popup) && 'no' !== $popup.attr('data-sbp-close-overlay')) {
            closePopup();
        }
    });

    $(document).on('keydown', function (event) {
        var $popup = $('#sbp-custom-popup');

        if ('Escape' === event.key && $popup.length && 'false' === $popup.attr('aria-hidden') && 'no' !== $popup.attr('data-sbp-close-escape')) {
            closePopup();
        }
    });

    $(document).on('sbp:cart-updated', function () {
        renderAllCarts();
    });

    renderAllCarts();
});
