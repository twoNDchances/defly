<?php

return [
    'commons' => [
        'labels' => 'Nhãn',
        'created_by' => 'Tạo bởi',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
    ],
    'columns' => [
        'user' => [
            'name' => 'Tên',
            'email' => 'Email',
            'is_verified' => 'Được xác minh',
            'is_root' => 'Toàn quyền',
            'is_activated' => 'Được kích hoạt',
            'permissions' => 'Quyền áp dụng',
            'policies' => 'Chính sách áp dụng',
        ],
        'permission' => [
            'name' => 'Tên',
            'applied_for' => 'Phạm vi',
            'action' => 'Hành động',
            'users' => 'Áp dụng người dùng',
            'policies' => 'Thuộc chính sách',
        ],
        'policy' => [
            'name' => 'Tên',
            'users' => 'Người dùng được áp dụng',
            'permissions' => 'Quyền đang sử dụng',
        ],
        'label' => [
            'name' => 'Tên',
            'color' => 'Màu sắc',
            'preview' => 'Xem trước',
        ],
        'wordlist' => [
            'name' => 'Tên',
            'type' => 'Loại danh sách',
            'word_count' => 'Số từ',
        ],
        'engine' => [
            'name' => 'Tên',
            'input_datatype' => 'Kiểu dữ liệu trước',
            'type' => 'Loại bộ chuyển đổi',
            'output_datatype' => 'Kiểu dữ liệu sau',
        ],
        'pattern' => [
            'name' => 'Tên',
            'phase' => 'Giai đoạn thực thi',
            'type' => 'Loại phạm vi',
            'datatype' => 'Kiểu dữ liệu',
            'targets' => 'Các mục tiêu',
        ],
        'target' => [
            'name' => 'Tên',
            'phase' => 'Giai đoạn thực thi',
            'type' => 'Loại phạm vi',
            'datatype' => 'Kiểu dữ liệu',
            'pattern' => 'Mẫu',
            'wordlist' => 'Danh sách từ',
        ],
    ],
];
