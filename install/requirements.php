<?php
function isEnabled($func) {
    return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
}
$requirements = [
    'php_version' => [
        'title' => 'PHP Version',
        'description' => 'Requires at least PHP version 5.5.0 or higher. We recommend using PHP 8.0 or higher.',
        'required' => true,
    ],
    'server_software' => [
        'title' => 'Server Software',
        'description' => 'We recommend Apache or NGINX for our script, however, any server that supports PHP and MySQL will do.',
    ],
    'curl' => [
        'title' => 'cURL PHP Extension',
        'required' => true,
    ],
    'mysqli' => [
        'title' => 'Mysqli PHP Extension',
        'required' => true,
    ],
    'openssl' => [
        'title' => 'OpenSSL PHP Extension',
        'required' => true,
    ],
    'mbstring' => [
        'title' => 'MBString PHP Extension',
        'required' => true,
    ],
    'gd' => [
        'title' => 'GD PHP Extension',
        'required' => true,
    ],
    'zip' => [
        'title' => 'Zip PHP Extension',
        'required' => true,
    ],
    'mod_rewrite' => [
        'title' => 'Mod rewrite Extension',
        'required' => true,
    ],
    'allow_url_fopen' => [
        'title' => 'Allow url fopen',
        'required' => true,
    ],
    'config' => [
        'title' => 'Config PHP File',
        'description' => 'Required config.php to be writeable for the full script installation. Located in the sys folder',
        'required' => true,
    ],
    'output_buffering' => [
        'title' => 'Output buffering (Optional)',
    ],
    'ffmpeg' => [
        'title' => 'FFmpeg (Optional)',
        'description' => 'Required for uploading video and Tiles. We recommend asking your hosting service to install one for you.',
    ],
];

if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "apache") === false && strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "litespeed") === false) {
    unset($requirements['mod_rewrite']);
} elseif (!isEnabled('apache_get_modules')) {
    unset($requirements['mod_rewrite']);
}
if (!file_exists('.htaccess')) {
    if (file_exists('htaccess.backup')) {
        rename('htaccess.backup', '.htaccess');
    }
} ?>
<div class="requirements">
    <div class="list-group">
        <?php
        $proceed = true;
        foreach ($requirements as $index => $requirement) {
            $image = 'assets/img/warning.png';
            $output = 'Disabled';
            $result = false;
            if (isset($requirement['required']) && $requirement['required']) {
                $image = 'assets/img/error.png';
            }
            if (!isset($requirement['description'])) {
                $requirement['description'] = '';
            }
            if ($index === 'php_version') {
                $output = PHP_VERSION;
                if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                    $result = true;
                }
            } elseif ($index === 'server_software') {
                $output = $_SERVER["SERVER_SOFTWARE"];
                if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "nginx") !== false) {
                    $output .= '<br><br>You are using Fumigram with Nginx Server, you will need to setup Nginx specific rewrite rules.';
                }
                if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "apache") !== false || strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "litespeed") !== false || strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "nginx") !== false) {
                    $result = true;
                }
            } elseif ($index === 'mod_rewrite') {
                if (in_array('mod_rewrite', apache_get_modules())) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'mysqli') {
                if (function_exists('mysqli_connect')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'gd') {
                if (extension_loaded('gd') && function_exists('gd_info')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'mbstring') {
                if (extension_loaded('mbstring')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'zip') {
                if (extension_loaded('zip')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'curl') {
                if (function_exists('curl_init')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'output_buffering') {
                if (ini_get('output_buffering')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'allow_url_fopen') {
                if (ini_get('allow_url_fopen')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'ffmpeg') {
                $output = 'Unrecognizable';
                if (isEnabled('shell_exec')) {
                    $ffmpeg = shell_exec('ffmpeg');
                    if (!empty($ffmpeg)) {
                        $result = true;
                        $output = 'Enabled';
                    }
                }
            } elseif ($index === 'config') {
                if (is_writable('../core/sync/config.php')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif (extension_loaded($index)) {
                $output = 'Enabled';
                $result = true;
            }
            if ($result) {
                $image = 'assets/img/tick.png';
            } else {
                if (isset($requirement['required']) && $requirement['required']) {
                    $proceed = false;
                } 
            } ?>
            <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                <img src="<?php echo $image; ?>" width="38" height="38" class="flex-shrink-0" />
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0 title"><?php echo $requirement['title']; ?></h6>
                        <p class="mb-10 description">
                            <?php echo $requirement['description']; ?>
                        </p>
                        <p class="mb-0 result">
                            Result : <?php echo $output; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if (!$proceed) { ?>
        <div class="error mt-4 text-center">
            <div class="alert alert-danger" role="alert">
                <strong>NOTICE :</strong> This server does not meet the minimum requirements for installing this application.
            </div>
        </div>
    <?php } else { ?>
        <div class="proceed mt-4 text-center">
            <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" class="feed-icon feather-compass" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Proceed</span>
        </div>
    <?php } ?>
</div>