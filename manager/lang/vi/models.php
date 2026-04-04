<?php

return [
    'commons' => [
        'created_by' => 'Tạo bởi',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
    ],
    'permission' => [
        'name' => 'Quyền',
        'fields' => [
            'name' => 'Tên',
            'description' => 'Mô tả',
            'applied_for' => 'Áp dụng cho',
            'action' => 'Hành động',
        ],
    ],
    'policy' => [
        'name' => 'Chính sách',
        'fields' => [
            'name' => 'Tên',
            'description' => 'Mô tả',
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
            'description' => 'Mô tả',
        ],
    ],
];
