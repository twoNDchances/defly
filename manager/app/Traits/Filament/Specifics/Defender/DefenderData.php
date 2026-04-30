<?php

namespace App\Traits\Filament\Specifics\Defender;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;

trait DefenderData
{
    public static function statusOptionsAndColors()
    {
        return [
            'options' => [
                Status::Normal->value => __('models.defender.extras.status.normal'),
                Status::Abnormal->value => __('models.defender.extras.status.abnormal'),
            ],
            'colors' => [
                Status::Normal->value => 'info',
                Status::Abnormal->value => 'danger',
            ],
        ];
    }

    public static function statusDescriptions()
    {
        return [
            null => __('forms.defender.descriptions.status'),
            Status::Normal->value => __('forms.defender.extras.status.normal'),
            Status::Abnormal->value => __('forms.defender.extras.status.abnormal'),
        ];
    }

    public static function deploymentStatusOptionsAndColors()
    {
        return [
            'options' => [
                DeploymentStatus::Pending->value => __('models.defender.extras.deployment_status.pending'),
                DeploymentStatus::Processing->value => __('models.defender.extras.deployment_status.processing'),
                DeploymentStatus::Failed->value => __('models.defender.extras.deployment_status.failed'),
                DeploymentStatus::Successful->value => __('models.defender.extras.deployment_status.successful'),
            ],
            'colors' => [
                DeploymentStatus::Pending->value => 'secondary',
                DeploymentStatus::Processing->value => 'info',
                DeploymentStatus::Failed->value => 'danger',
                DeploymentStatus::Successful->value => 'success',
            ],
        ];
    }

    public static function deploymentStatusDescriptions()
    {
        return [
            null => __('forms.defender.descriptions.deployment_status'),
            DeploymentStatus::Pending->value => __('forms.defender.extras.deployment_status.pending'),
            DeploymentStatus::Processing->value => __('forms.defender.extras.deployment_status.processing'),
            DeploymentStatus::Failed->value => __('forms.defender.extras.deployment_status.failed'),
            DeploymentStatus::Successful->value => __('forms.defender.extras.deployment_status.successful'),
        ];
    }

    public static function saveForm($data)
    {
        if (
            array_key_exists('common_environment_variables', $data)
            || array_key_exists('server_environment_variables', $data)
            || array_key_exists('proxy_environment_variables', $data)
        ) {
            $data['environment_variables'] = [
                ...self::environmentVariablesToMap(
                    self::mergeEnvironmentVariables(
                        self::commonEnvironmentVariables(),
                        $data['common_environment_variables'] ?? [],
                    ),
                ),
                ...self::environmentVariablesToMap(
                    self::mergeEnvironmentVariables(
                        self::serverEnvironmentVariables(),
                        $data['server_environment_variables'] ?? [],
                    ),
                ),
                ...self::environmentVariablesToMap(
                    self::mergeEnvironmentVariables(
                        self::proxyEnvironmentVariables(),
                        $data['proxy_environment_variables'] ?? [],
                    ),
                ),
            ];
        }

        unset(
            $data['common_environment_variables'],
            $data['server_environment_variables'],
            $data['proxy_environment_variables'],
        );

        return $data;
    }

    public static function loadForm($data)
    {
        $environmentVariables = is_array($data['environment_variables'] ?? null)
            ? $data['environment_variables']
            : [];

        $data['common_environment_variables'] = self::mergeEnvironmentVariables(
            self::commonEnvironmentVariables(),
            $environmentVariables,
        );

        $data['server_environment_variables'] = self::mergeEnvironmentVariables(
            self::serverEnvironmentVariables(),
            $environmentVariables,
        );

        $data['proxy_environment_variables'] = self::mergeEnvironmentVariables(
            self::proxyEnvironmentVariables(),
            $environmentVariables,
        );

        return $data;
    }

    protected static function mergeEnvironmentVariables(array $variables, array $state): array
    {
        $values = self::environmentVariablesToMap($state);

        return array_map(
            fn (array $variable) => [
                'key' => $variable['key'],
                'value' => array_key_exists($variable['key'], $values)
                    ? $values[$variable['key']]
                    : $variable['value'],
            ],
            $variables,
        );
    }

    protected static function environmentVariablesToMap(array $variables): array
    {
        $values = [];

        foreach ($variables as $key => $item) {
            if (is_string($key) && (! is_array($item))) {
                $values[$key] = $item;

                continue;
            }

            if (is_array($item) && array_key_exists('key', $item)) {
                $values[$item['key']] = $item['value'] ?? null;
            }
        }

        return $values;
    }

    protected static function commonEnvironmentVariables(): array
    {
        return [
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
    }

    protected static function serverEnvironmentVariables(): array
    {
        return [
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
    }

    protected static function proxyEnvironmentVariables(): array
    {
        return [
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
    }
}
