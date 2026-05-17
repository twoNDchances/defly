<?php

namespace Tests\Feature\Gui\FilamentComponents;

use Tests\Support\FilamentTestHelpers;
use Tests\TestCase;

class DefenderEnvironmentVariableRulesTest extends TestCase
{
    use FilamentTestHelpers;

    public function test_defender_environment_variable_validation_rules_cover_edge_cases(): void
    {
        $failures = [];
        $fail = function (string $message) use (&$failures): void {
            $failures[] = $message;
        };

        $commonRules = $this->repeaterValueRules(\App\Filament\Components\Defender\DefenderForm::setCommonEnvironmentVariables());
        $directoryRules = $commonRules(fn (string $path) => $path === 'key' ? 'ERROR_DIRECTORY_PATH' : null);
        $directoryRule = collect($directoryRules)->first(fn ($rule) => $rule instanceof \Closure);
        $directoryRule('path', '', $fail);
        $directoryRule('path', '..', $fail);

        $countRules = $commonRules(fn (string $path) => match ($path) {
            'key' => 'DOCTOR_INTERVAL_COUNT',
            '../../common_environment_variables' => [['key' => 'DOCTOR_INTERVAL_UNIT', 'value' => 'second']],
            default => null,
        });
        $countRule = collect($countRules)->first(fn ($rule) => $rule instanceof \Closure);
        $countRule('count', 'not-number', $fail);
        $countRule('count', 1, $fail);
        $this->assertNotEmpty($commonRules(fn (string $path) => $path === 'key' ? 'DOCTOR_INTERVAL_COUNT' : null));
        $this->assertNotEmpty($commonRules(fn () => null));

        $serverRules = $this->repeaterValueRules(\App\Filament\Components\Defender\DefenderForm::setServerEnvironmentVariables());
        $serverFileRules = $serverRules(fn (string $path) => $path === 'key' ? 'SERVER_LOGGER_FILE_PATH' : null);
        $serverFileRule = collect($serverFileRules)->first(fn ($rule) => $rule instanceof \Closure);
        $serverFileRule('server_file', '', $fail);
        $serverFileRule('server_file', 'logs/', $fail);

        $proxyRules = $this->repeaterValueRules(\App\Filament\Components\Defender\DefenderForm::setProxyEnvironmentVariables());
        $proxyFileRules = $proxyRules(fn (string $path) => $path === 'key' ? 'PROXY_LOGGER_FILE_PATH' : null);
        $proxyFileRule = collect($proxyFileRules)->first(fn ($rule) => $rule instanceof \Closure);
        $proxyFileRule('proxy_file', '', $fail);
        $proxyFileRule('proxy_file', '../', $fail);

        $trustedListRules = $proxyRules(fn (string $path) => $path === 'key' ? 'PROXY_TRUSTED_LIST' : null);
        $trustedListRule = collect($trustedListRules)->first(fn ($rule) => $rule instanceof \Closure);
        $trustedListRule('trusted', '', $fail);
        $trustedListRule('trusted', '127.0.0.1, 10.0.0.0/24, 2001:db8::/32', $fail);
        $trustedListRule('trusted', '127.0.0.1,,10.0.0.1', $fail);
        $trustedListRule('trusted', '10.0.0.0/99', $fail);

        $this->assertNotEmpty($failures);
    }
}
