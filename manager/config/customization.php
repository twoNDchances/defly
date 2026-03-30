<?php

use Filament\Support\Colors\Color;
use Illuminate\Support\Str;

return [
    "backend" => [
        "apis" => [
            "authentication" => [
                "token_location" => (function ()
                {
                    $default = "header";
                    $location = Str::lower(env("TOKEN_LOCATION", $default));
                    $all_locations = [$default, "body"];
                    if (!in_array($location, $all_locations))
                    {
                        return $default;
                    }
                    return $location;
                })(),
                "token_key_name" => Str::slug(env("TOKEN_KEY_NAME", "X-Token-Key")),
            ],
            "user_agent" => env("USER_AGENT", "Defly/Manager"),
        ],
        "default_credentials" => [
            "user_name" => env("USER_NAME", "root"),
            "user_email" => env("USER_EMAIL", "root@defly.2ndproject.site"),
            "user_password" => env("USER_PASSWORD", "random"),
        ],
        "urls" => [
            "api_prefix" => Str::slug(Str::lower(env("API_PREFIX", "api"))),
            "gui_prefix" => Str::slug(Str::lower(env("GUI_PREFIX", "defly-manager"))),
        ],
    ],
    "gui" => [
        "theme_color" => (function()
        {
            $color = Str::lower(env("THEME_COLOR", "violet"));
            $all_colors = Color::all();
            if (!array_key_exists($color, $all_colors))
            {
                return Color::Violet;
            }
            return $all_colors[$color];
        })(),
    ],
];
