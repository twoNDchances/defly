<?php

return [
    'generals' => [
        'bases' => [
            'description' => 'Mô tả',
            'created_by' => 'Tạo bởi',
            'created_at' => 'Tạo lúc',
            'updated_at' => 'Cập nhật lúc',
        ],
        'specials' => [
            'phase' => [
                '1' => '1. Tất cả ở yêu cầu',
                '2' => '2. Các tiêu đề ở yêu cầu',
                '3' => '3. Nội dung yêu cầu',
                '4' => '4. Các tiêu đề ở phản hồi',
                '5' => '5. Nội dung phản hồi',
                '6' => '6. Tất cả ở phản hồi',
            ],
            'type' => [
                'getter' => 'Bộ lấy',
                'full' => 'Tất cả',
                'header' => 'Tiêu đề',
                'meta' => 'Siêu dữ liệu',
                'query' => 'Tham số',
                'body' => 'Nội dung',
                'file' => 'Tệp',
            ],
            'datatype' => [
                'array' => 'Mảng',
                'number' => 'Số',
                'string' => 'Chuỗi',
            ],
            'locked' => 'Bị khóa',
        ],
    ],
    'permission' => [
        'name' => 'Quyền',
        'fields' => [
            'name' => 'Tên',
            'applied_for' => 'Áp dụng cho',
            'action' => 'Hành động',
        ],
    ],
    'group' => [
        'name' => 'Nhóm',
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
            'type' => 'Loại danh sách',
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
            'type' => 'Loại bộ chuyển đổi',
            'configurations' => 'Các cấu hình',
            'output_datatype' => 'Kiểu dữ liệu đầu ra',
        ],
        'extras' => [
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
            ],
            'configurations' => [
                'position' => 'Vị trí',
                'digit' => 'Số',
                'hash_method' => 'Hàm băm',
                'separator' => 'Bộ phân tách',
            ],
        ],
    ],
    'pattern' => [
        'name' => 'Mẫu',
        'fields' => [
            'name' => 'Tên',
            'phase' => 'Giai đoạn thực thi',
            'type' => 'Loại phạm vi',
            'datatype' => 'Kiểu dữ liệu',
            'targets' => 'Các mục tiêu',
        ],
    ],
    'target' => [
        'name' => 'Mục tiêu',
        'fields' => [
            'name' => 'Tên',
            'phase' => 'Giai đoạn thực thi',
            'type' => 'Loại phạm vi',
            'datatype' => 'Kiểu dữ liệu',
            'pattern' => 'Mẫu',
            'wordlist' => 'Danh sách từ',
        ],
    ],
];
