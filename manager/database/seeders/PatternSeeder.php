<?php

namespace Database\Seeders;

use App\Enums\Datatype;
use App\Enums\Phase;
use App\Enums\Type;
use App\Models\Pattern;
use Illuminate\Database\Seeder;

class PatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patterns = [
            [
                'name'        => 'request-full',
                'type'        => Type::Full,
                'phase'       => Phase::One,
                'datatype'    => Datatype::String,
                'description' => 'Include headers & body of request\nBao gồm tiêu đề và nội dung của yêu cầu',
            ],
            // ========================= //
            // ========================= //
            [
                'name'        => 'request-header-keys',
                'type'        => Type::Header,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Array,
                'description' => 'Header keys of request\nCác khóa tiêu đề của yêu cầu',
            ],
            [
                'name'        => 'request-header-values',
                'type'        => Type::Header,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Array,
                'description' => 'Header values of request\nCác giá trị tiêu đề của yêu cầu',
            ],
            [
                'name'        => 'request-query-keys',
                'type'        => Type::Query,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Array,
                'description' => 'Query key of request\nKhóa tham số truy vấn của yêu cầu',
            ],
            [
                'name'        => 'request-query-values',
                'type'        => Type::Query,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Array,
                'description' => 'Query values of request\nGiá trị truy vấn của yêu cầu',
            ],
            // ========================= //
            [
                'name'        => 'request-header-size',
                'type'        => Type::Header,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Number,
                'description' => 'Header field number of request\nSố trường tiêu đề của yêu cầu',
            ],
            [
                'name'        => 'request-meta-url-port',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Number,
                'description' => 'URL Port of request\nCổng URL của yêu cầu',
            ],
            [
                'name'        => 'request-query-size',
                'type'        => Type::Query,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::Number,
                'description' => 'Query field number of request\nSố trường tham số truy vấn của yêu cầu',
            ],
            // ========================= //
            [
                'name'        => 'request-meta-protocol',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'Protocol of request\nPhiên bản giao thức của yêu cầu',
            ],
            [
                'name'        => 'request-meta-ip',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'IP of request\nIP của yêu cầu',
            ],
            [
                'name'        => 'request-meta-method',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'Method of request\nPhương thức của yêu cầu',
            ],
            [
                'name'        => 'request-meta-url-path',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'URL path of request\nĐường dẫn trong URL của yêu cầu',
            ],
            [
                'name'        => 'request-meta-url-scheme',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'URL scheme of request\nGiao thức trong URL của yêu cầu',
            ],
            [
                'name'        => 'request-meta-url-host',
                'type'        => Type::Meta,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'URL host of request\nURL máy chủ của yêu cầu',
            ],
            [
                'name'        => 'request-full-headers',
                'type'        => Type::Full,
                'phase'       => Phase::Two,
                'datatype'    => Datatype::String,
                'description' => 'Headers of request\nTiêu đề đầy đủ của yêu cầu',
            ],
            // ========================= //
            // ========================= //
            [
                'name'        => 'request-body-keys',
                'type'        => Type::Body,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'Body keys of Request',
            ],
            [
                'name'        => 'request-file-keys',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'File keys of Request',
            ],
            [
                'name'        => 'request-body-values',
                'type'        => Type::Body,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'Body values of Request',
            ],
            [
                'name'        => 'request-file-values',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'File values of Request',
            ],
            [
                'name'        => 'request-file-names',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'File names of Request',
            ],
            [
                'name'        => 'request-file-extensions',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Array,
                'description' => 'File extensions of Request',
            ],
            // ========================= //
            [
                'name'        => 'request-body-size',
                'type'        => Type::Body,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Number,
                'description' => 'Body field number of Request',
            ],
            [
                'name'        => 'request-file-size',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Number,
                'description' => 'File field number of Request',
            ],
            [
                'name'        => 'request-file-name-size',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Number,
                'description' => 'File name number of Request',
            ],
            [
                'name'        => 'request-body-length',
                'type'        => Type::Body,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Number,
                'description' => 'Body length of Request',
            ],
            [
                'name'        => 'request-file-length',
                'type'        => Type::File,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::Number,
                'description' => 'File length of Request',
            ],
            // ========================= //
            [
                'name'        => 'request-full-body',
                'type'        => Type::Full,
                'phase'       => Phase::Three,
                'datatype'    => Datatype::String,
                'description' => 'Body of Request',
            ],
            // ========================= //
            // ========================= //
            [
                'name'        => 'response-header-keys',
                'type'        => Type::Header,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::Array,
                'description' => 'Header keys of Response',
            ],
            [
                'name'        => 'response-header-values',
                'type'        => Type::Header,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::Array,
                'description' => 'Header values of Response',
            ],
            // ========================= //
            [
                'name'        => 'response-header-size',
                'type'        => Type::Header,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::Number,
                'description' => 'Header field number of Response',
            ],
            [
                'name'        => 'response-meta-status',
                'type'        => Type::Meta,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::Number,
                'description' => 'Status of Response',
            ],
            // ========================= //
            [
                'name'        => 'response-meta-protocol',
                'type'        => Type::Meta,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::String,
                'description' => 'Protocol of Response',
            ],
            [
                'name'        => 'response-full-headers',
                'type'        => Type::Full,
                'phase'       => Phase::Four,
                'datatype'    => Datatype::String,
                'description' => 'Headers of Response',
            ],
            // ========================= //
            // ========================= //
            [
                'name'        => 'response-body-keys',
                'type'        => Type::Body,
                'phase'       => Phase::Five,
                'datatype'    => Datatype::Array,
                'description' => 'Body keys of Response',
            ],
            [
                'name'        => 'response-body-values',
                'type'        => Type::Body,
                'phase'       => Phase::Five,
                'datatype'    => Datatype::Array,
                'description' => 'Body values of Response',
            ],
            // ========================= //
            [
                'name'        => 'response-body-size',
                'type'        => Type::Body,
                'phase'       => Phase::Five,
                'datatype'    => Datatype::Number,
                'description' => 'Body field number of Response',
            ],
            [
                'name'        => 'response-body-length',
                'type'        => Type::Body,
                'phase'       => Phase::Five,
                'datatype'    => Datatype::Number,
                'description' => 'Body length of Response',
            ],
            // ========================= //
            [
                'name'        => 'response-full-body',
                'type'        => Type::Full,
                'phase'       => Phase::Five,
                'datatype'    => Datatype::String,
                'description' => 'Body of Response',
            ],
            // ========================= //
            // ========================= //
            [
                'name'        => 'response-full',
                'type'        => Type::Full,
                'phase'       => Phase::Six,
                'datatype'    => Datatype::String,
                'description' => 'Include Headers & Body of Response',
            ],
        ];

        foreach ($patterns as $pattern) {
            Pattern::firstOrCreate(['name' => $pattern['name']], $pattern);
        }
    }
}
