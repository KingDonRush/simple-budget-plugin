<?php
if ( ! defined('WP_UNINSTALL_PLUGIN') ) exit;
delete_option('sbp_whatsapp_number');
delete_option('sbp_product_post_types');
delete_option('sbp_version');
