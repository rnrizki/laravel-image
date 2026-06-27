<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
read_the_docs_settings('login-providers', _s('Login providers'));
$tpl = <<<HTML
<div class="input-label">
    <label for="%name%"><span class="fab fa-%icon%"></span> %label%</label>
    <div class="c5 phablet-c1"><select type="text" name="%name%" id="%name%" class="text-input" data-combo="%name%-combo">
        %options%
    </select></div>
    <div class="input-warning red-warning">%optionError%</div>
</div>
<div id="%name%-combo">
    <div data-combo-value="1" class="switch-combo c9 phablet-c1%hidden%">
        <div class="input-label">
            <label for="%name%_connect_url"><span class="fas fa-link"></span> %connectUrl%</label>
            <div class="margin-bottom-10 position-relative">
                <input id="%name%_connect_url" type="text" class="text-input r-width" value="%connectUrlValue%" data-focus="select-all" readonly>
                <button type="button" class="input-action" data-action="copy" data-action-target="#%name%_connect_url"><i class="far fa-copy"></i> %copy%</button>
            </div>
        </div>
        <div class="input-label">
            <label for="%name%_id"><span class="fab fa-%icon%"></span> %appId%</label>
            <input type="text" name="%name%_id" id="%name%_id" class="text-input" value="%appIdValue%" placeholder="%label% %appId%" data-required>
            <div class="input-warning red-warning">%appIdError%</div>
        </div>
        <div class="input-label">
            <label for="%name%_secret"><span class="fab fa-%icon%"></span> %appSecret%</label>
            <input type="text" name="%name%_secret" id="%name%_secret" class="text-input" value="%appSecretValue%" placeholder="%label% %appSecret%" data-required>
            <div class="input-warning red-warning">%appSecretError%</div>
        </div>
    </div>
</div>
<hr class="line-separator">
HTML;
foreach (Login::getProviders('all') as $name => $provider) {
    if ($name === 'apple') {
        continue;
    }
    $icon = $name;
    if ($name === 'twitter') {
        $icon = 'x-twitter';
    }
    echo strtr($tpl, [
        '%name%' => $name,
        '%icon%' => $icon,
        '%label%' => $provider['label'],
        '%options%' => get_select_options_html(
            [
                1 => _s('Enabled'),
                0 => _s('Disabled')
            ],
            Handler::var('safe_post')
                ? Handler::var('safe_post')[$name]
                : $provider['is_enabled']
        ),
        '%optionError%' => Handler::var('input_errors')[$name] ?? '',
        '%hidden%' => (!(Handler::var('safe_post') ? Handler::var('safe_post')[$name] : $provider['is_enabled']))
            ? ' soft-hidden'
            : '',
        '%appId%' => _s('%s id', _s('Application')),
        '%appIdValue%' => Handler::var('safe_post')['%name%_id'] ?? $provider['key_id'],
        '%appIdError%' => Handler::var('input_errors')['%name%_id'] ?? '',
        '%appSecret%' => _s('%s secret', _s('Application')),
        '%appSecretValue%' => Handler::var('safe_post')[$name . '_secret'] ?? $provider['key_secret'],
        '%appSecretError%' => Handler::var('input_errors')[$name . '_secret'] ?? '',
        '%connectUrl%' => _s('Connect URL'),
        '%connectUrlValue%' => get_base_url('connect/' . $name . '/', true),
        '%copy%' => _s('Copy'),
    ]);
}
