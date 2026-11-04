<?php
// Local dev only: block outbound HTTP; skip plugins that phone home or only matter on the server.
add_filter('pre_http_request', function(){ return new WP_Error('local','blocked'); }, 10, 3);
add_filter('option_active_plugins', function($p){ return array_values(array_diff((array)$p, ['sucuri-scanner/sucuri.php','google-site-kit/google-site-kit.php','updraftplus/updraftplus.php','sg-cachepress/sg-cachepress.php','sg-ai-studio/sg-ai-studio.php','wp-mail-smtp/wp_mail_smtp.php','embedpress/embedpress.php','enable-media-replace/enable-media-replace.php','akismet/akismet.php'])); });
