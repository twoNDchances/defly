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
                'name' => 'request-full',
                'type' => Type::Full,
                'phase' => Phase::One,
                'datatype' => Datatype::String,
                'description' => "Include headers & body of request\nBao gồm toàn bộ header và nội dung body của yêu cầu",
            ],
            // ========================= //
            // ========================= //
            [
                'name' => 'request-header-keys',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => "Header keys of request\nTên các header trong yêu cầu",
            ],
            [
                'name' => 'request-header-values',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => "Header values of request\nGiá trị các header trong yêu cầu",
            ],
            [
                'name' => 'request-query-keys',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => "Query keys of request\nTên các tham số query trong yêu cầu",
            ],
            [
                'name' => 'request-query-values',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => "Query values of request\nGiá trị các tham số query trong yêu cầu",
            ],
            // ========================= //
            [
                'name' => 'request-header-size',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => "Header field number of request\nSố lượng header trong yêu cầu",
            ],
            [
                'name' => 'request-meta-url-port',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => "URL port of request\nCổng (port) trong URL của yêu cầu",
            ],
            [
                'name' => 'request-query-size',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => "Query field number of request\nSố lượng tham số query trong yêu cầu",
            ],
            // ========================= //
            [
                'name' => 'request-meta-protocol',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "Protocol of request\nPhiên bản giao thức của yêu cầu",
            ],
            [
                'name' => 'request-meta-ip',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "IP of request\nĐịa chỉ IP của yêu cầu",
            ],
            [
                'name' => 'request-meta-method',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "Method of request\nPhương thức (method) của yêu cầu",
            ],
            [
                'name' => 'request-meta-url-path',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "URL path of request\nĐường dẫn (path) trong URL của yêu cầu",
            ],
            [
                'name' => 'request-meta-url-scheme',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "URL scheme of request\nScheme trong URL của yêu cầu",
            ],
            [
                'name' => 'request-meta-url-host',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "URL host of request\nTên miền/host trong URL của yêu cầu",
            ],
            [
                'name' => 'request-full-headers',
                'type' => Type::Full,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => "Headers of request\nToàn bộ header của yêu cầu",
            ],
            // ========================= //
            // ========================= //
            [
                'name' => 'request-body-keys',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "Body keys of request\nTên các trường trong body của yêu cầu",
            ],
            [
                'name' => 'request-file-keys',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "File keys of request\nTên các trường file trong yêu cầu",
            ],
            [
                'name' => 'request-body-values',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "Body values of request\nGiá trị các trường trong body của yêu cầu",
            ],
            [
                'name' => 'request-file-values',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "File values of request\nGiá trị các trường file của yêu cầu",
            ],
            [
                'name' => 'request-file-names',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "File names of request\nTên các tệp được gửi trong yêu cầu",
            ],
            [
                'name' => 'request-file-extensions',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => "File extensions of request\nPhần mở rộng của các tệp trong yêu cầu",
            ],
            // ========================= //
            [
                'name' => 'request-body-size',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => "Body field number of request\nSố lượng trường trong body của yêu cầu",
            ],
            [
                'name' => 'request-file-size',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => "File field number of request\nSố lượng trường file trong yêu cầu",
            ],
            [
                'name' => 'request-file-name-size',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => "File name number of request\nSố lượng tên tệp trong yêu cầu",
            ],
            [
                'name' => 'request-body-length',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => "Body length of request\nĐộ dài dữ liệu body của yêu cầu",
            ],
            [
                'name' => 'request-file-length',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => "File length of request\nTổng độ dài dữ liệu file của yêu cầu",
            ],
            // ========================= //
            [
                'name' => 'request-full-body',
                'type' => Type::Full,
                'phase' => Phase::Three,
                'datatype' => Datatype::String,
                'description' => "Body of request\nToàn bộ nội dung body của yêu cầu",
            ],
            // ========================= //
            // ========================= //
            [
                'name' => 'response-header-keys',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Array,
                'description' => "Header keys of response\nTên các header trong phản hồi",
            ],
            [
                'name' => 'response-header-values',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Array,
                'description' => "Header values of response\nGiá trị các header trong phản hồi",
            ],
            // ========================= //
            [
                'name' => 'response-header-size',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Number,
                'description' => "Header field number of response\nSố lượng header trong phản hồi",
            ],
            [
                'name' => 'response-meta-status',
                'type' => Type::Meta,
                'phase' => Phase::Four,
                'datatype' => Datatype::Number,
                'description' => "Status of response\nMã trạng thái của phản hồi",
            ],
            // ========================= //
            [
                'name' => 'response-meta-protocol',
                'type' => Type::Meta,
                'phase' => Phase::Four,
                'datatype' => Datatype::String,
                'description' => "Protocol of response\nPhiên bản giao thức của phản hồi",
            ],
            [
                'name' => 'response-full-headers',
                'type' => Type::Full,
                'phase' => Phase::Four,
                'datatype' => Datatype::String,
                'description' => "Headers of response\nToàn bộ header của phản hồi",
            ],
            // ========================= //
            // ========================= //
            [
                'name' => 'response-body-keys',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Array,
                'description' => "Body keys of response\nTên các trường trong body của phản hồi",
            ],
            [
                'name' => 'response-body-values',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Array,
                'description' => "Body values of response\nGiá trị các trường trong body của phản hồi",
            ],
            // ========================= //
            [
                'name' => 'response-body-size',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Number,
                'description' => "Body field number of response\nSố lượng trường trong body của phản hồi",
            ],
            [
                'name' => 'response-body-length',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Number,
                'description' => "Body length of response\nĐộ dài dữ liệu body của phản hồi",
            ],
            // ========================= //
            [
                'name' => 'response-full-body',
                'type' => Type::Full,
                'phase' => Phase::Five,
                'datatype' => Datatype::String,
                'description' => "Body of response\nToàn bộ nội dung body của phản hồi",
            ],
            // ========================= //
            // ========================= //
            [
                'name' => 'response-full',
                'type' => Type::Full,
                'phase' => Phase::Six,
                'datatype' => Datatype::String,
                'description' => "Include headers & body of response\nBao gồm toàn bộ header và nội dung body của phản hồi",
            ],
        ];

        foreach ($patterns as $pattern) {
            Pattern::firstOrCreate(['name' => $pattern['name']], $pattern);
        }
    }
}
