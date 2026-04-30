<?php

namespace App\Traits\Filament\Specifics\Defender;

use App\Traits\Filament\Generals\Components\Field;
use Filament\Forms\Components\CodeEditor\Enums\Language;

trait DefenderField
{
    use DefenderButton, DefenderData, Field;

    public static function setName()
    {
        return self::textInput(
            'name',
            __('models.defender.fields.name'),
            __('forms.defender.text_examples.name'),
        )
            ->helperText(__('forms.defender.descriptions.name'))
            ->unique(ignoreRecord: true)
            ->alphaDash()
            ->required();
    }

    public static function setProxyPort()
    {
        return self::textInput(
            'proxy_port',
            __('models.defender.fields.proxy_port'),
            __('forms.defender.text_examples.proxy_port'),
            [
                'integer', 'min:1', 'max:65535',
            ]
        )
            ->helperText(__('forms.defender.descriptions.proxy_port'))
            ->required()
            ->integer()
            ->default(9948)
            ->maxLength(null);
    }

    public static function setCommonEnvironmentVariables()
    {
        $variables = [
            ['key' => 'ABOUT_BANNER_ENABLE', 'value' => 'true'],
            ['key' => 'ERROR_FILE_ENABLE', 'value' => 'false'],
            ['key' => 'ERROR_DIRECTORY_PATH', 'value' => 'storage/errors'],
            ['key' => 'DATABASE_HOST', 'value' => '127.0.0.1'],
            ['key' => 'DATABASE_PORT', 'value' => '3306'],
            ['key' => 'DATABASE_NAME', 'value' => 'defly_manager'],
            ['key' => 'DATABASE_USER', 'value' => 'root'],
            ['key' => 'DATABASE_PASS', 'value' => ''],
            ['key' => 'DOCTOR_INTERVAL_UNIT', 'value' => 'minute'],
            ['key' => 'DOCTOR_INTERVAL_COUNT', 'value' => '1'],
        ];

        $directoryPathRule = function (string $attribute, mixed $value, $fail): void {
            if (! filled($value)) {
                return;
            }

            $basename = basename(str_replace('\\', '/', trim((string) $value)));
            if (in_array($basename, ['', '.', '..'], true)) {
                $fail('The :attribute must be a valid directory path.');
            }
        };

        $doctorIntervalCountRule = function (string $attribute, mixed $value, $fail): void {
            if (! is_numeric($value)) {
                return;
            }

            if ((int) $value < 30) {
                $fail('The :attribute must be at least 30 when DOCTOR_INTERVAL_UNIT is second.');
            }
        };

        $valueRules = [
            'ABOUT_BANNER_ENABLE' => ['required', 'in:true,false'],
            'ERROR_FILE_ENABLE' => ['required', 'in:true,false'],
            'ERROR_DIRECTORY_PATH' => ['nullable', 'string', 'max:255', $directoryPathRule],
            'DATABASE_HOST' => ['required', 'string', 'max:255', 'not_regex:/\s/'],
            'DATABASE_PORT' => ['required', 'integer', 'min:1', 'max:65535'],
            'DATABASE_NAME' => ['required', 'string', 'max:255', 'not_regex:/\s/'],
            'DATABASE_USER' => ['required', 'string', 'max:255', 'not_regex:/\s/'],
            'DATABASE_PASS' => ['nullable', 'string', 'max:255'],
            'DOCTOR_INTERVAL_UNIT' => ['required', 'in:second,minute,hour'],
            'DOCTOR_INTERVAL_COUNT' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];

        return self::repeater(
            'common_environment_variables',
            __('models.defender.extras.common_environment_variables'),
            'key',
            [
                self::textInput('key', __('models.defender.extras.key'), '', [
                    'required',
                    'in:'.implode(',', array_column($variables, 'key')),
                ])
                    ->readOnly()
                    ->required(),

                self::textInput('value', __('models.defender.extras.value'))
                    ->rules(function ($get) use ($doctorIntervalCountRule, $valueRules) {
                        $key = $get('key');
                        if ($key !== 'DOCTOR_INTERVAL_COUNT') {
                            return $valueRules[$key] ?? ['nullable', 'string'];
                        }

                        $commonVariables = $get('../../common_environment_variables');
                        if (! is_array($commonVariables)) {
                            return $valueRules[$key];
                        }

                        foreach ($commonVariables as $variable) {
                            if (($variable['key'] ?? null) !== 'DOCTOR_INTERVAL_UNIT') {
                                continue;
                            }

                            if (strtolower((string) ($variable['value'] ?? '')) === 'second') {
                                return [...$valueRules[$key], $doctorIntervalCountRule];
                            }
                        }

                        return $valueRules[$key];
                    })
                    ->maxLength(null)
                    ->password()
                    ->revealable(),
            ],
        )
            ->default($variables)
            ->addable(false)
            ->cloneable(false)
            ->deletable(false)
            ->reorderable(false)
            ->minItems(count($variables))
            ->maxItems(count($variables))
            ->collapsed();
    }

    public static function setServerEnvironmentVariables()
    {
        $variables = [
            ['key' => 'SERVER_HTTPS_ENABLE', 'value' => 'true'],
            ['key' => 'SERVER_LOGGER_FILE_ENABLE', 'value' => 'false'],
            ['key' => 'SERVER_LOGGER_FILE_PATH', 'value' => 'storage/logs/server.log'],
            ['key' => 'SERVER_LOGGER_FORMAT', 'value' => '[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%'],
            ['key' => 'SERVER_LOGGER_TIMEZONE', 'value' => 'Asia/Ho_Chi_Minh'],
            ['key' => 'SERVER_PORT', 'value' => '9947'],
            ['key' => 'SERVER_CONTROLLER_PATH_PREFIX', 'value' => 'api/v1'],
            ['key' => 'SERVER_CONTROLLER_PATH_PRINCIPLES', 'value' => 'principles'],
            ['key' => 'SERVER_CONTROLLER_METHOD_APPLY', 'value' => 'put'],
            ['key' => 'SERVER_CONTROLLER_METHOD_REVOKE', 'value' => 'delete'],
            ['key' => 'SERVER_CONTROLLER_PATH_DECISIONS', 'value' => 'decisions'],
            ['key' => 'SERVER_CONTROLLER_METHOD_IMPLEMENT', 'value' => 'put'],
            ['key' => 'SERVER_CONTROLLER_METHOD_SUSPEND', 'value' => 'delete'],
            ['key' => 'SERVER_CONTROLLER_PERMISSION_EMAIL', 'value' => 'X-Executor'],
            ['key' => 'SERVER_SECURITY_MANAGER', 'value' => 'manager'],
            ['key' => 'SERVER_SECURITY_USERNAME', 'value' => 'defly-defender'],
            ['key' => 'SERVER_SECURITY_PASSWORD', 'value' => 'P@55w0rd'],
        ];

        $filePathRule = function (string $attribute, mixed $value, $fail): void {
            if (! filled($value)) {
                return;
            }

            $path = str_replace('\\', '/', trim((string) $value));
            $basename = basename($path);

            if (in_array($basename, ['', '.', '..'], true) || str_ends_with($path, '/')) {
                $fail('The :attribute must be a valid file path.');
            }
        };

        $valueRules = [
            'SERVER_HTTPS_ENABLE' => ['required', 'in:true,false'],
            'SERVER_LOGGER_FILE_ENABLE' => ['required', 'in:true,false'],
            'SERVER_LOGGER_FILE_PATH' => ['nullable', 'string', 'max:255', $filePathRule],
            'SERVER_LOGGER_FORMAT' => ['required', 'string', 'max:2048'],
            'SERVER_LOGGER_TIMEZONE' => ['required', 'timezone'],
            'SERVER_PORT' => ['required', 'integer', 'min:1', 'max:65535'],
            'SERVER_CONTROLLER_PATH_PREFIX' => ['required', 'regex:/^[A-Za-z0-9._~-]+(?:\/[A-Za-z0-9._~-]+)*$/', 'not_regex:/(^|\/)\.{1,2}(\/|$)/'],
            'SERVER_CONTROLLER_PATH_PRINCIPLES' => ['required', 'regex:/^[A-Za-z0-9._~-]+(?:\/[A-Za-z0-9._~-]+)*$/', 'not_regex:/(^|\/)\.{1,2}(\/|$)/'],
            'SERVER_CONTROLLER_PATH_DECISIONS' => ['required', 'regex:/^[A-Za-z0-9._~-]+(?:\/[A-Za-z0-9._~-]+)*$/', 'not_regex:/(^|\/)\.{1,2}(\/|$)/'],
            'SERVER_CONTROLLER_METHOD_APPLY' => ['required', 'in:post,put,patch,delete'],
            'SERVER_CONTROLLER_METHOD_REVOKE' => ['required', 'in:post,put,patch,delete'],
            'SERVER_CONTROLLER_METHOD_IMPLEMENT' => ['required', 'in:post,put,patch,delete'],
            'SERVER_CONTROLLER_METHOD_SUSPEND' => ['required', 'in:post,put,patch,delete'],
            'SERVER_CONTROLLER_PERMISSION_EMAIL' => ['required', 'regex:/^[!#$%&\'*+\-.^_`|~0-9A-Za-z]+$/'],
            'SERVER_SECURITY_MANAGER' => ['required', 'string', 'max:255', 'not_regex:/[\s\/\\\\:]/'],
            'SERVER_SECURITY_USERNAME' => ['required', 'string', 'min:4', 'max:255'],
            'SERVER_SECURITY_PASSWORD' => ['required', 'string', 'min:4', 'max:255'],
        ];

        return self::repeater(
            'server_environment_variables',
            __('models.defender.extras.server_environment_variables'),
            'key',
            [
                self::textInput('key', __('models.defender.extras.key'), '', [
                    'required',
                    'in:'.implode(',', array_column($variables, 'key')),
                ])
                    ->readOnly()
                    ->required(),

                self::textInput('value', __('models.defender.extras.value'))
                    ->rules(fn ($get) => $valueRules[$get('key')] ?? ['nullable', 'string'])
                    ->maxLength(null)
                    ->password()
                    ->revealable(),
            ],
        )
            ->default($variables)
            ->addable(false)
            ->cloneable(false)
            ->deletable(false)
            ->reorderable(false)
            ->minItems(count($variables))
            ->maxItems(count($variables))
            ->collapsed();
    }

    public static function setProxyEnvironmentVariables()
    {
        $variables = [
            ['key' => 'PROXY_BACKEND_URL', 'value' => 'http://localhost'],
            ['key' => 'PROXY_LOGGER_FILE_ENABLE', 'value' => 'false'],
            ['key' => 'PROXY_LOGGER_FILE_PATH', 'value' => 'storage/logs/proxy.log'],
            ['key' => 'PROXY_LOGGER_FORMAT', 'value' => '[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%'],
            ['key' => 'PROXY_LOGGER_TIMEZONE', 'value' => 'Asia/Ho_Chi_Minh'],
            ['key' => 'PROXY_PORT', 'value' => '9948'],
            ['key' => 'PROXY_TRUSTED_ENABLE', 'value' => 'false'],
            ['key' => 'PROXY_TRUSTED_LIST', 'value' => null],
            ['key' => 'PROXY_PRESERVE_HOST', 'value' => 'true'],
            ['key' => 'PROXY_SEVERITY_ALERT', 'value' => '6'],
            ['key' => 'PROXY_SEVERITY_CRITICAL', 'value' => '5'],
            ['key' => 'PROXY_SEVERITY_EMERGENCY', 'value' => '7'],
            ['key' => 'PROXY_SEVERITY_ERROR', 'value' => '4'],
            ['key' => 'PROXY_SEVERITY_INFO', 'value' => '1'],
            ['key' => 'PROXY_SEVERITY_NOTICE', 'value' => '2'],
            ['key' => 'PROXY_SEVERITY_WARNING', 'value' => '3'],
            ['key' => 'PROXY_VIOLATION_LEVEL', 'value' => '1'],
            ['key' => 'PROXY_VIOLATION_SCORE', 'value' => '5'],
        ];

        $filePathRule = function (string $attribute, mixed $value, $fail): void {
            if (! filled($value)) {
                return;
            }

            $path = str_replace('\\', '/', trim((string) $value));
            $basename = basename($path);

            if (in_array($basename, ['', '.', '..'], true) || str_ends_with($path, '/')) {
                $fail('The :attribute must be a valid file path.');
            }
        };

        $trustedProxyListRule = function (string $attribute, mixed $value, $fail): void {
            if (! filled($value)) {
                return;
            }

            foreach (explode(',', (string) $value) as $item) {
                $item = trim($item);

                if ($item === '') {
                    $fail('The :attribute must be a comma-separated list of IP addresses or CIDR blocks.');

                    return;
                }

                if (filter_var($item, FILTER_VALIDATE_IP)) {
                    continue;
                }

                [$ip, $prefix] = array_pad(explode('/', $item, 2), 2, null);

                if (
                    ($prefix !== null)
                    && preg_match('/^\d+$/', $prefix)
                    && (
                        (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ((int) $prefix >= 0) && ((int) $prefix <= 32))
                        || (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && ((int) $prefix >= 0) && ((int) $prefix <= 128))
                    )
                ) {
                    continue;
                }

                $fail('The :attribute must be a comma-separated list of IP addresses or CIDR blocks.');

                return;
            }
        };

        $valueRules = [
            'PROXY_BACKEND_URL' => ['required', 'url', 'max:2048'],
            'PROXY_LOGGER_FILE_ENABLE' => ['required', 'in:true,false'],
            'PROXY_LOGGER_FILE_PATH' => ['nullable', 'string', 'max:255', $filePathRule],
            'PROXY_LOGGER_FORMAT' => ['required', 'string', 'max:2048'],
            'PROXY_LOGGER_TIMEZONE' => ['required', 'timezone'],
            'PROXY_PORT' => ['required', 'integer', 'min:1', 'max:65535'],
            'PROXY_TRUSTED_ENABLE' => ['required', 'in:true,false'],
            'PROXY_TRUSTED_LIST' => ['nullable', 'string', $trustedProxyListRule],
            'PROXY_PRESERVE_HOST' => ['required', 'in:true,false'],
            'PROXY_SEVERITY_ALERT' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_CRITICAL' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_EMERGENCY' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_ERROR' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_INFO' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_NOTICE' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_SEVERITY_WARNING' => ['required', 'integer', 'min:1', 'max:1000'],
            'PROXY_VIOLATION_LEVEL' => ['required', 'integer', 'min:1', 'max:1000000'],
            'PROXY_VIOLATION_SCORE' => ['required', 'integer', 'min:5', 'max:100000'],
        ];

        return self::repeater(
            'proxy_environment_variables',
            __('models.defender.extras.proxy_environment_variables'),
            'key',
            [
                self::textInput('key', __('models.defender.extras.key'), '', [
                    'required',
                    'in:'.implode(',', array_column($variables, 'key')),
                ])
                    ->readOnly()
                    ->required(),

                self::textInput('value', __('models.defender.extras.value'))
                    ->rules(fn ($get) => $valueRules[$get('key')] ?? ['nullable', 'string'])
                    ->maxLength(null)
                    ->password()
                    ->revealable(),
            ],
        )
            ->default($variables)
            ->addable(false)
            ->cloneable(false)
            ->deletable(false)
            ->reorderable(false)
            ->minItems(count($variables))
            ->maxItems(count($variables))
            ->collapsed();
    }

    public static function setStatus()
    {
        return self::toggleButtons(
            'status',
            __('models.defender.fields.status'),
            self::statusOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::statusDescriptions()[$state])
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setDetails()
    {
        return self::codeEditor('details', __('models.defender.fields.details'), Language::Json)
            ->helperText(__('forms.defender.descriptions.details'))
            ->formatStateUsing(function ($state) {
                if ($state === null) {
                    return null;
                }

                if (is_string($state)) {
                    return $state;
                }

                if (is_array($state)) {
                    return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return (string) $state;
            })
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setDeploymentStatus()
    {
        return self::toggleButtons(
            'deployment_status',
            __('models.defender.fields.deployment_status'),
            self::deploymentStatusOptionsAndColors(),
        )
            ->helperText(fn ($state) => self::deploymentStatusDescriptions()[$state])
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setDeploymentDetails()
    {
        return self::codeEditor('deployment_details', __('models.defender.fields.deployment_details'), Language::Json)
            ->helperText(__('forms.defender.descriptions.deploymnet_details'))
            ->formatStateUsing(function ($state) {
                if ($state === null) {
                    return null;
                }

                if (is_string($state)) {
                    return $state;
                }

                if (is_array($state)) {
                    return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return (string) $state;
            })
            ->disabled()
            ->visibleOn(['view', 'edit']);
    }

    public static function setLog()
    {
        return self::codeEditor(
            'log',
            __('models.defender.fields.log'),
            Language::Json,
        )
        ->helperText(__('forms.defender.descriptions.log'))
        ->disabled();
    }
}
