<?php

return [
    'commons' => [
        'description' => 'Mô tả',
        'created_by' => 'Tạo bởi',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
    ],
    'permission' => [
        'name' => 'Quyền',
        'fields' => [
            'name' => 'Tên',
            'applied_for' => 'Áp dụng cho',
            'action' => 'Hành động',
        ],
    ],
    'policy' => [
        'name' => 'Chính sách',
        'fields' => [
            'name' => 'Tên',
        ],
    ],
    'user' => [
        'name' => 'Người dùng',
        'fields' => [
            'name' => 'Tên',
            'email' => 'Email',
            'email_verified_at' => 'Xác minh lúc',
            'password' => 'Mật khẩu',
            'is_verified' => 'Được xác minh',
            'is_root' => 'Toàn quyền',
            'is_activated' => 'Được kích hoạt',
        ],
    ],
    'label' => [
        'name' => 'Nhãn',
        'fields' => [
            'name' => 'Tên',
            'color' => 'Màu sắc',
        ],
    ],
    'wordlist' => [
        'name' => 'Danh sách từ',
        'fields' => [
            'name' => 'Tên',
            'type' => 'Loại',
            'word_file' => 'Dữ liệu tệp',
            'word_json' => 'Dữ liệu JSON',
            'word_count' => 'Số từ',
        ],
        'extras' => [
            'type' => [
                'file' => 'Tệp',
                'json' => 'JSON',
            ],
            'word' => 'Từ',
        ],
    ],
    'engine' => [
        'name' => 'Động cơ',
        'fields' => [
            'name' => 'Tên',
            'input_datatype' => 'Kiểu dữ liệu đầu vào',
            'type' => 'Loại',
            'configurations' => 'Các cấu hình',
            'output_datatype' => 'Kiểu dữ liệu đầu ra',
        ],
        'extras' => [
            'datatype' => [
                'array' => 'Mảng',
                'number' => 'Số',
                'string' => 'Chuỗi',
            ],
            'type' => [
                'indexOf' => 'Tại vị trí',
                'merge' => 'Hợp nhất',
                'addition' => 'Cộng',
                'subtraction' => 'Trừ',
                'multiplication' => 'Nhân',
                'division' => 'Chia',
                'powerOf' => 'Mũ',
                'remainder' => 'Chia lấy dư',
                'toString' => 'Ép chuỗi',
                'lower' => 'Hạ xuống',
                'upper' => 'Nâng lên',
                'capitalize' => 'Viết hoa',
                'trim' => 'Cắt',
                'trimLeft' => 'Cắt trái',
                'trimRight' => 'Cắt phải',
                'removeWhitespace' => 'Xóa khoảng trắng',
                'length' => 'Độ dài',
                'hash' => 'Băm',
                'split' => 'Tách',
                'configurations' => [
                    'position' => 'Vị trí',
                    'digit' => 'Số',
                    'hash_method' => 'Hàm băm',
                    'separator' => 'Bộ phân tách',
                ],
            ],
        ],
    ],
];
