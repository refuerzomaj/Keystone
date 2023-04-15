<?php
if (!defined('ABSPATH')) exit;
$provider   = WPERECCP()->front->listing_provider;
$price      = $provider->get_meta_data('wperesds_pricing', get_the_ID());
echo "<h2>{$price}</h2>";
