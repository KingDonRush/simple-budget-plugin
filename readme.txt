=== Simple Budget Plugin ===
Contributors: guilhermesilva
Tags: orçamento, whatsapp, carrinho, elementor, custom post types
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
**PT-BR:**  
O **Simple Budget Plugin** permite que visitantes criem orçamentos personalizados diretamente no seu site WordPress.  
Ele se integra perfeitamente com o **Elementor** e com **Custom Post Types (CPTs)**, permitindo adicionar produtos e serviços ao carrinho e gerar um link de orçamento via **WhatsApp**.  
Ideal para sites de catálogos, lojas sem checkout e prestadores de serviços que desejam automatizar cotações.

**EN-US:**  
The **Simple Budget Plugin** allows visitors to build custom quotes directly on your WordPress site.  
It integrates seamlessly with **Elementor** and **Custom Post Types (CPTs)**, letting users add products or services to a cart and generate a quote link via **WhatsApp**.  
Perfect for catalog sites, service providers, and businesses that want to automate quotation requests.

---

== Features ==
**PT-BR:**  
- Criação de orçamentos personalizados via popup interativo.  
- Integração com **WhatsApp** para envio automático do pedido.  
- Compatível com **Elementor** (botão de “Adicionar ao Carrinho”).  
- Suporte a múltiplos **Custom Post Types (CPTs)**.  
- Painel administrativo completo com opções de configuração.  
- Totalmente responsivo e traduzível.  
- Código modular, seguindo padrão PSR-4 e WordPress coding standards.

**EN-US:**  
- Create personalized quotes via an interactive popup.  
- **WhatsApp** integration for automatic message generation.  
- Works perfectly with **Elementor** (Add to Cart button).  
- Supports multiple **Custom Post Types (CPTs)**.  
- Admin panel with full customization options.  
- Fully responsive and translation-ready.  
- Modular codebase following PSR-4 and WordPress coding standards.

---

== Installation ==
**PT-BR:**  
1. Faça o upload da pasta `simple-budget-plugin` para `/wp-content/plugins/`.  
2. Ative o plugin no menu **Plugins** do painel WordPress.  
3. Vá até **Configurações → Configurações SBP** para definir:  
   - Número de WhatsApp  
   - Tipos de post aceitos (CPTs)  
4. Adicione o shortcode `[sbp_cart]` em qualquer página para exibir o carrinho.  
5. Use o botão do Elementor com ID `add-to-cart-button` para adicionar produtos ao carrinho.

**EN-US:**  
1. Upload the `simple-budget-plugin` folder to `/wp-content/plugins/`.  
2. Activate the plugin through the **Plugins** menu in WordPress.  
3. Go to **Settings → SBP Settings** and configure:  
   - WhatsApp number  
   - Allowed post types (CPTs)  
4. Add the `[sbp_cart]` shortcode to any page to display the quote cart.  
5. Use an Elementor button with the CSS ID `add-to-cart-button` to add products to the cart.

---

== Frequently Asked Questions ==
**PT-BR:**  
= O plugin precisa do WooCommerce? =  
Não. O SBP foi criado para sites que desejam orçamentos personalizados sem o uso de checkout.

= Funciona com qualquer tema WordPress? =  
Sim, desde que o tema siga os padrões do WordPress (hooks `wp_footer` e `wp_enqueue_scripts`).

= Posso usar com meus próprios tipos de post (CPTs)? =  
Sim! Você pode definir quais post types o plugin deve usar na tela de configurações.

**EN-US:**  
= Does this plugin require WooCommerce? =  
No. SBP is built for custom quotation systems without checkout.  

= Is it compatible with any WordPress theme? =  
Yes, as long as the theme follows standard WordPress hooks (`wp_footer`, `wp_enqueue_scripts`).  

= Can I use it with my own Custom Post Types? =  
Absolutely. You can select which post types are used from the plugin settings page.

---

== Screenshots ==
1. Página de configuração do plugin no painel administrativo / Admin settings page.  
2. Popup do carrinho com lista de produtos / Cart popup interface.  
3. Integração com Elementor e botão personalizado / Elementor integration.

---

== Changelog ==
= 2.0.0 =
**PT-BR:**  
- Reestruturação completa com namespaces e autoloader PSR-4.  
- Painel administrativo remodelado e sanitização de campos aprimorada.  
- Melhorias no desempenho e na segurança.  
- Novo sistema de hooks centralizado (Loader).  
- Suporte aprimorado a Elementor e CPTs personalizados.  

**EN-US:**  
- Complete refactor with PSR-4 namespaces and autoloader.  
- Redesigned admin panel with improved sanitization.  
- Performance and security improvements.  
- Centralized hook management system (Loader).  
- Enhanced Elementor and CPT integration.

---

== Upgrade Notice ==
**PT-BR:**  
Antes de atualizar para a versão 2.0.0, remova quaisquer versões antigas do plugin.  
Essa atualização é uma reestruturação completa e requer reconfiguração das opções no painel.

**EN-US:**  
Before upgrading to version 2.0.0, remove any previous plugin versions.  
This update is a complete refactor and requires resetting settings from the admin panel.

---

== License ==
**PT-BR:**  
Este plugin é software livre e segue a licença **GPLv2 ou posterior**.  
Você pode modificá-lo e distribuí-lo livremente.

**EN-US:**  
This plugin is open-source software licensed under **GPLv2 or later**.  
You are free to modify and redistribute it.

---
