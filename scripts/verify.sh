#!/usr/bin/env bash
set -euo pipefail

plugin_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
wp_root="$(cd "${plugin_dir}/../../.." && pwd)"

cd "$plugin_dir"

section() {
  printf '\n== %s ==\n' "$1"
}

warn() {
  printf 'WARN: %s\n' "$1" >&2
}

php_lint_with_host() {
  section "PHP syntax"
  find . \
    -path './.git' -prune -o \
    -path './vendor' -prune -o \
    -name '*.php' -print0 \
    | xargs -0 -n1 php -l
}

php_lint_with_docker() {
  section "PHP syntax via Docker"
  local php_files=()
  mapfile -d '' php_files < <(
    find . \
      -path './.git' -prune -o \
      -path './vendor' -prune -o \
      -name '*.php' -print0
  )

  for file in "${php_files[@]}"; do
    docker compose -f "${wp_root}/docker-compose.yml" exec -T wordpress \
      php -l "wp-content/plugins/simple-budget-plugin/${file#./}" < /dev/null
  done
}

section "JavaScript syntax"
if command -v node >/dev/null 2>&1; then
  node --check assets/js/sbp-script.js
else
  warn "node not found; skipping JS syntax check"
fi

if command -v php >/dev/null 2>&1; then
  php_lint_with_host
elif command -v docker >/dev/null 2>&1 && [[ -f "${wp_root}/docker-compose.yml" ]]; then
  php_lint_with_docker
else
  warn "php and Docker Compose WordPress runtime not found; skipping PHP syntax check"
fi

if [[ "${SBP_SKIP_WP_SMOKE:-0}" == "1" ]]; then
  warn "SBP_SKIP_WP_SMOKE=1; skipping WP smoke checks"
elif command -v docker >/dev/null 2>&1 && [[ -f "${wp_root}/docker-compose.yml" ]]; then
  section "WordPress smoke checks"
  (
    cd "$wp_root"
    docker compose run --rm wpcli eval 'echo "SBP_VERSION=" . SBP_VERSION . "\n";'
    docker compose run --rm wpcli eval 'echo class_exists("\\SBP\\Support\\CartRenderer") ? "CartRenderer=ok\n" : "CartRenderer=missing\n";'
    docker compose run --rm wpcli eval 'echo class_exists("\\SBP\\Support\\CartShellConfig") ? "CartShellConfig=ok\n" : "CartShellConfig=missing\n";'
    docker compose run --rm wpcli eval 'echo class_exists("\\SBP\\Support\\RuntimeAssets") ? "RuntimeAssets=ok\n" : "RuntimeAssets=missing\n";'
    docker compose run --rm wpcli eval '$html = \SBP\Support\CartRenderer::render_placeholder_items(2, ["show_quantity" => true, "show_remove" => true, "show_image" => true], 3); echo (false !== strpos($html, "sbp-cart-item--preview") && false !== strpos($html, "value=\"3\"")) ? "placeholder=ok\n" : "placeholder=fail\n";'
    docker compose run --rm wpcli eval '$attrs = \SBP\Support\CartShellConfig::get_data_attributes(["cart_panel_width" => ["size" => 640, "unit" => "px"], "cart_shell" => "drawer_right"]); echo ("drawer_right" === $attrs["data-sbp-shell"] && "640px" === $attrs["data-sbp-panel-width"]) ? "shell-config=ok\n" : "shell-config=fail\n";'
    docker compose run --rm wpcli eval 'do_action("wp_enqueue_scripts"); echo (wp_script_is("sbp-script", "registered") && wp_style_is("sbp-styles", "registered") && ! wp_script_is("sbp-script", "enqueued")) ? "asset-registration=ok\n" : "asset-registration=fail\n";'
    docker compose run --rm wpcli eval '$widget = new \SBP\Elementor\Widgets\BudgetButton(); $method = new ReflectionMethod($widget, "register_controls"); $method->setAccessible(true); $method->invoke($widget); $controls = $widget->get_controls(); echo isset($controls["cart_template_id"], $controls["cart_shell"], $controls["cart_panel_width"]) ? "button-controls=ok\n" : "button-controls=fail\n";'
    docker compose run --rm wpcli eval '$widget = new \SBP\Elementor\Widgets\BudgetList(); $method = new ReflectionMethod($widget, "register_controls"); $method->setAccessible(true); $method->invoke($widget); $controls = $widget->get_controls(); echo isset($controls["preview_items"], $controls["preview_post_type"], $controls["preview_item_ids"]) ? "listing-preview-controls=ok\n" : "listing-preview-controls=fail\n";'
  )
else
  warn "Docker Compose WordPress runtime unavailable; skipping WP smoke checks"
fi

section "Git whitespace"
git diff --check
