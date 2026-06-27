
<?php

use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('cookie-compliance', _s('Cookie compliance')); ?>
<div class="input-label">
    <label for="enable_cookie_law"><?php _se('Cookie law compliance'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_cookie_law" id="enable_cookie_law" class="text-input">
            <?php
                        echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_cookie_law')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this to display a message that complies with the EU Cookie law requirements. Note: You only need this if your website is hosted in the EU and if you add tracking cookies.'); ?></div>
</div>
