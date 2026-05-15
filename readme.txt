=== Simple Budget Plugin ===
Contributors: guilhermesilva
Tags: orçamento, whatsapp, carrinho, elementor, custom post types
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.3.4
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
- Templates de carrinho editáveis no Elementor via **Simple Budget → Templates**.
- Shell do carrinho configurável como modal central, carrinho lateral ou bottom sheet.
- Quantidades, posicionamento do botão remover e comportamento do WhatsApp vazio nos widgets.
- Integração com **WhatsApp** para envio automático do pedido.  
- Widgets nativos para **Elementor**: **Budget Button** e **Budget Listing**.
- Modo legado compatível com botão Elementor usando o ID `add-to-cart-button`.
- Suporte a múltiplos **Custom Post Types (CPTs)**.  
- Painel administrativo completo com opções de configuração.  
- Totalmente responsivo e traduzível.  
- Código modular, seguindo padrão PSR-4 e WordPress coding standards.

**EN-US:**  
- Create personalized quotes via an interactive popup.  
- Elementor-editable cart templates through **Simple Budget → Templates**.
- Configurable cart shell as centered modal, side cart, or bottom sheet.
- Quantities, remove button positioning, and empty WhatsApp behavior in widgets.
- **WhatsApp** integration for automatic message generation.  
- Native **Elementor** widgets: **Budget Button** and **Budget Listing**.
- Legacy mode remains compatible with an Elementor button using the `add-to-cart-button` ID.
- Supports multiple **Custom Post Types (CPTs)**.  
- Admin panel with full customization options.  
- Fully responsive and translation-ready.  
- Modular codebase following PSR-4 and WordPress coding standards.

---

== Installation ==
**PT-BR:**  
1. Faça o upload da pasta `simple-budget-plugin` para `/wp-content/plugins/`.  
2. Ative o plugin no menu **Plugins** do painel WordPress.  
3. Vá até **Simple Budget → Configurações SBP** para definir:
   - Número de WhatsApp  
   - Tipos de post aceitos (CPTs)  
4. No Elementor, use o widget **Budget Button** dentro do card/template do item.
5. Use o widget **Budget Listing** na página/template de orçamento para listar itens e remover produtos.
6. Para customizar o modal, acesse **Simple Budget → Templates**, crie um template de carrinho, edite no Elementor e selecione esse template em um **Budget Button** com ação **Open budget popup**.
7. Alternativamente, mantenha o modo legado com o shortcode `[sbp_cart]` e um botão Elementor com ID `add-to-cart-button`.

**EN-US:**  
1. Upload the `simple-budget-plugin` folder to `/wp-content/plugins/`.  
2. Activate the plugin through the **Plugins** menu in WordPress.  
3. Go to **Simple Budget → SBP Settings** and configure:
   - WhatsApp number  
   - Allowed post types (CPTs)  
4. In Elementor, use the **Budget Button** widget inside the item card/template.
5. Use the **Budget Listing** widget on the quote page/template to list and remove products.
6. To customize the modal, open **Simple Budget → Templates**, create a cart template, edit it in Elementor, and select it in a **Budget Button** configured as **Open budget popup**.
7. Alternatively, keep the legacy mode with the `[sbp_cart]` shortcode and an Elementor button with the `add-to-cart-button` ID.

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
= 2.3.4 =
**PT-BR:**
- Torna o controle **Panel Width** do Cart Shell responsivo no **Budget Button**.
- Adiciona valores separados para desktop, tablet e mobile usando o controle responsivo nativo do Elementor.
- O frontend agora escolhe a largura correta ao abrir o carrinho, respeitando os breakpoints ativos do Elementor quando disponíveis.

**EN-US:**
- Makes the Cart Shell **Panel Width** control responsive in **Budget Button**.
- Adds separate desktop, tablet, and mobile values using Elementor's native responsive control.
- The frontend now chooses the correct width when opening the cart, respecting Elementor active breakpoints when available.

= 2.3.3 =
**PT-BR:**
- Move o posicionamento do botão remover do **Budget Listing** para **Style → Remove Button**.
- Troca o seletor antigo por um controle visual no estilo do **Icon Box** do Elementor, com opções Start, End, Top e Bottom.
- Mantém o mesmo ID interno `remove_position` para preservar compatibilidade com templates existentes.

**EN-US:**
- Moves the **Budget Listing** remove-button position control into **Style → Remove Button**.
- Replaces the previous selector with an Elementor **Icon Box**-style visual control for Start, End, Top, and Bottom.
- Keeps the same internal `remove_position` ID to preserve compatibility with existing templates.

= 2.3.2 =
**PT-BR:**
- Adiciona remoção individual de templates na tela **Simple Budget → Templates**.
- Valida nonce, permissão e ownership do template antes de remover qualquer item.
- Move o template para a lixeira quando o WordPress tiver lixeira ativa, evitando exclusão irreversível por engano.

**EN-US:**
- Adds individual template removal to **Simple Budget → Templates**.
- Validates nonce, permissions, and template ownership before removing any item.
- Moves the template to trash when WordPress trash is enabled, avoiding accidental irreversible deletion.

= 2.3.1 =
**PT-BR:**
- Move os controles de posição e espaçamento do ícone do **Budget Button** para **Style → Icon**.
- Mantém compatibilidade com templates existentes preservando os mesmos IDs internos dos controles.
- Reforça o autoloader interno para aceitar apenas classes `SBP\` válidas e evitar exposição de paths em logs de debug.

**EN-US:**
- Moves **Budget Button** icon position and spacing controls into **Style → Icon**.
- Preserves existing templates by keeping the same internal control IDs.
- Hardens the internal autoloader to accept only valid `SBP\` classes and avoid exposing paths in debug logs.

= 2.3.0 =
**PT-BR:**
- Adiciona controles de shell do carrinho no **Budget Button**: modal central, carrinho lateral esquerdo/direito e bottom sheet.
- Adiciona quantidade ao fluxo do orçamento, com armazenamento local, renderização no **Budget Listing** e envio no texto do WhatsApp.
- Move a UX principal de remoção para o **Budget Listing**, com posicionamento do botão remover por item.
- Adiciona comportamento configurável para WhatsApp com carrinho vazio: esconder, desabilitar ou mostrar mensagem.
- Adiciona controles de cor/tamanho do ícone do **Budget Button** e preview visual de shell no editor de templates do carrinho.

**EN-US:**
- Adds cart shell controls to **Budget Button**: centered modal, left/right side cart, and bottom sheet.
- Adds quantity support to the budget flow, including local storage, **Budget Listing** rendering, and WhatsApp message output.
- Moves the primary remove UX into **Budget Listing**, with per-item remove button positioning.
- Adds configurable behavior for WhatsApp actions when the budget is empty: hide, disable, or show a message.
- Adds **Budget Button** icon color/size controls and a visual shell preview for cart templates in the Elementor editor.

= 2.2.1 =
**PT-BR:**
- Garante que templates de modal do carrinho sejam criados como Elementor Canvas, sem header/footer do tema no editor.
- Normaliza templates de carrinho existentes para o layout Canvas ao listar templates do plugin.

**EN-US:**
- Ensures cart modal templates are created as Elementor Canvas, without theme header/footer in the editor.
- Normalizes existing cart templates to the Canvas layout when plugin templates are listed.

= 2.2.0 =
**PT-BR:**
- Adiciona tela **Simple Budget → Templates** para criar templates de carrinho editáveis no Elementor.
- Adiciona seleção de template no widget **Budget Button** quando a ação é abrir o carrinho.
- Adiciona ação `close_cart` para fechar o modal a partir de botões criados no Elementor.
- Renderiza templates do carrinho via AJAX mantendo fallback legado.

**EN-US:**
- Adds **Simple Budget → Templates** to create Elementor-editable cart templates.
- Adds template selection to the **Budget Button** widget when the action opens the cart.
- Adds the `close_cart` action so Elementor-built buttons can close the modal.
- Renders cart templates through AJAX while preserving the legacy fallback.

= 2.1.0 =
**PT-BR:**
- Adiciona widgets Elementor nativos para ações de orçamento e listagem de itens.
- Adiciona botão de remoção reutilizável na listagem do orçamento.
- Mantém compatibilidade com o modo legado por ID/shortcode.

**EN-US:**
- Adds native Elementor widgets for budget actions and item listing.
- Adds a reusable remove button in the budget listing.
- Keeps compatibility with the legacy ID/shortcode mode.

= 2.0.1 =
**PT-BR:**
- Silencia mensagens esperadas de carrinho vazio no console, mantendo logs de debug atrás de uma flag.

**EN-US:**
- Silences expected empty-cart console messages while keeping debug logs behind a flag.

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
