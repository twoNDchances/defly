<?php

use Filament\Support\Colors\Color;
use Illuminate\Support\Str;

return [
    'backend' => [
        'apis' => [
            'authentication' => [
                'token_location' => (function () {
                    $default = 'header';
                    $location = Str::lower(env('TOKEN_LOCATION', $default));
                    $all_locations = [$default, 'body'];
                    if (! in_array($location, $all_locations)) {
                        return $default;
                    }

                    return $location;
                })(),
                'token_key_name' => filled(env('TOKEN_KEY_NAME')) ? env('TOKEN_KEY_NAME') : 'X-Token-Key',
            ],
            'user_agent' => env('USER_AGENT', 'Defly/Manager'),
            'orchestrator' => [
                'base_url' => env('ORCHESTRATOR_BASE_URL', 'http://orchestrator:8000'),
                'paths' => [
                    'prefix' => env('ORCHESTRATOR_PATH_PREFIX', 'api/v1'),
                    'deployment' => [
                        'path' => env('ORCHESTRATOR_PATH_DEPLOYMENT', 'deployments'),
                        'methods' => [
                            'deploy' => (function () {
                                $default = 'post';
                                $method = Str::lower(env('ORCHESTRATOR_METHOD_DEPLOY', $default));
                                if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                                    return $default;
                                }

                                return $method;
                            })(),
                            'follow' => (function () {
                                $default = 'get';
                                $method = Str::lower(env('ORCHESTRATOR_METHOD_FOLLOW', $default));
                                if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                                    return $default;
                                }

                                return $method;
                            })(),
                            'cancel' => (function () {
                                $default = 'delete';
                                $method = Str::lower(env('ORCHESTRATOR_METHOD_CANCEL', $default));
                                if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                                    return $default;
                                }

                                return $method;
                            })(),
                        ],
                    ],
                ],
                'credentials' => [
                    'username' => env('ORCHESTRATOR_USERNAME', 'defly-orchestrator'),
                    'password' => env('ORCHESTRATOR_PASSWORD', 'P@55w0rd'),
                ],
                'tls' => [
                    'skip_verify' => (bool) env('ORCHESTRATOR_TLS_SKIP_VERIFY', false),
                    'cert_file' => env('ORCHESTRATOR_TLS_CERT_FILE', 'storage/tls/orchestrator/orchestrator.crt'),
                ],
                'headers' => [
                    'email_header_key' => env('ORCHESTRATOR_EMAIL_HEADER_KEY', 'X-Executor'),
                ],
            ],
            'defender' => [
                'tls' => [
                    'skip_verify' => (bool) env('DEFENDER_SERVER_TLS_SKIP_VERIFY', false),
                    'directory' => env('DEFENDER_SERVER_TLS_DIRECTORY', 'storage/tls/defenders'),
                ],
            ],
        ],
        'default_credentials' => [
            'user_name' => env('USER_NAME', 'root'),
            'user_email' => env('USER_EMAIL', 'root@defly.2ndproject.site'),
            'user_password' => env('USER_PASSWORD', 'random'),
        ],
        'default_label' => 'default-resources',
        'urls' => [
            'api_prefix' => Str::slug(Str::lower(env('API_PREFIX', 'api'))),
            'gui_prefix' => Str::slug(Str::lower(env('GUI_PREFIX', 'defly-manager'))),
        ],
    ],
    'gui' => [
        'theme_color' => (function () {
            $color = Str::lower(env('THEME_COLOR', 'violet'));
            $all_colors = Color::all();
            if (! array_key_exists($color, $all_colors)) {
                return Color::Violet;
            }

            return $all_colors[$color];
        })(),
    ],
];
