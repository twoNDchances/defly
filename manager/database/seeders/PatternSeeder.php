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
        if (Pattern::query()->exists()) {
            return;
        }

        $patterns = [
            [
                'name' => 'request-full',
                'type' => Type::Full,
                'phase' => Phase::One,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Include headers and body of request.',
                    'Bao gồm toàn bộ header và nội dung body của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-header-keys',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Header keys of request.',
                    'Tên các header trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-header-values',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Header values of request.',
                    'Giá trị các header trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-query-keys',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Query keys of request.',
                    'Tên các tham số query trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-query-values',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Query values of request.',
                    'Giá trị các tham số query trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-header-size',
                'type' => Type::Header,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Header field count of request.',
                    'Số lượng header trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-url-port',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'URL port of request.',
                    'Cổng (port) trong URL của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-query-size',
                'type' => Type::Query,
                'phase' => Phase::Two,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Query field count of request.',
                    'Số lượng tham số query trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-protocol',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Protocol of request.',
                    'Phiên bản giao thức của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-ip',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'IP of request.',
                    'Địa chỉ IP của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-method',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Method of request.',
                    'Phương thức (method) của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-url-path',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'URL path of request.',
                    'Đường dẫn (path) trong URL của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-url-scheme',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'URL scheme of request.',
                    'Scheme trong URL của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-meta-url-host',
                'type' => Type::Meta,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'URL host of request.',
                    'Tên miền/host trong URL của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-full-headers',
                'type' => Type::Full,
                'phase' => Phase::Two,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Headers of request.',
                    'Toàn bộ header của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-body-keys',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Body keys of request.',
                    'Tên các trường trong body của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-keys',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'File keys of request.',
                    'Tên các trường file trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-body-values',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Body values of request.',
                    'Giá trị các trường trong body của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-values',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'File values of request.',
                    'Giá trị các trường file của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-names',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'File names of request.',
                    'Tên các tệp được gửi trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-extensions',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'File extensions of request.',
                    'Phần mở rộng của các tệp trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-body-size',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Body field count of request.',
                    'Số lượng trường trong body của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-size',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'File field count of request.',
                    'Số lượng trường file trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-name-size',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'File name count of request.',
                    'Số lượng tên tệp trong yêu cầu.',
                ),
            ],
            [
                'name' => 'request-body-length',
                'type' => Type::Body,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Body length of request.',
                    'Độ dài dữ liệu body của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-file-length',
                'type' => Type::File,
                'phase' => Phase::Three,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'File length of request.',
                    'Tổng độ dài dữ liệu file của yêu cầu.',
                ),
            ],
            [
                'name' => 'request-full-body',
                'type' => Type::Full,
                'phase' => Phase::Three,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Body of request.',
                    'Toàn bộ nội dung body của yêu cầu.',
                ),
            ],
            [
                'name' => 'response-header-keys',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Header keys of response.',
                    'Tên các header trong phản hồi.',
                ),
            ],
            [
                'name' => 'response-header-values',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Header values of response.',
                    'Giá trị các header trong phản hồi.',
                ),
            ],
            [
                'name' => 'response-header-size',
                'type' => Type::Header,
                'phase' => Phase::Four,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Header field count of response.',
                    'Số lượng header trong phản hồi.',
                ),
            ],
            [
                'name' => 'response-meta-status',
                'type' => Type::Meta,
                'phase' => Phase::Four,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Status of response.',
                    'Mã trạng thái của phản hồi.',
                ),
            ],
            [
                'name' => 'response-meta-protocol',
                'type' => Type::Meta,
                'phase' => Phase::Four,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Protocol of response.',
                    'Phiên bản giao thức của phản hồi.',
                ),
            ],
            [
                'name' => 'response-full-headers',
                'type' => Type::Full,
                'phase' => Phase::Four,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Headers of response.',
                    'Toàn bộ header của phản hồi.',
                ),
            ],
            [
                'name' => 'response-body-keys',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Body keys of response.',
                    'Tên các trường trong body của phản hồi.',
                ),
            ],
            [
                'name' => 'response-body-values',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Array,
                'description' => $this->description(
                    'Body values of response.',
                    'Giá trị các trường trong body của phản hồi.',
                ),
            ],
            [
                'name' => 'response-body-size',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Body field count of response.',
                    'Số lượng trường trong body của phản hồi.',
                ),
            ],
            [
                'name' => 'response-body-length',
                'type' => Type::Body,
                'phase' => Phase::Five,
                'datatype' => Datatype::Number,
                'description' => $this->description(
                    'Body length of response.',
                    'Độ dài dữ liệu body của phản hồi.',
                ),
            ],
            [
                'name' => 'response-full-body',
                'type' => Type::Full,
                'phase' => Phase::Five,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Body of response.',
                    'Toàn bộ nội dung body của phản hồi.',
                ),
            ],
            [
                'name' => 'response-full',
                'type' => Type::Full,
                'phase' => Phase::Six,
                'datatype' => Datatype::String,
                'description' => $this->description(
                    'Include headers and body of response.',
                    'Bao gồm toàn bộ header và nội dung body của phản hồi.',
                ),
            ],
        ];

        foreach ($patterns as $pattern) {
            Pattern::firstOrCreate(['name' => $pattern['name']], $pattern);
        }
    }

    private function description(string $eng, string $vie): string
    {
        return "Eng: {$eng}\nVie: {$vie}";
    }
}
