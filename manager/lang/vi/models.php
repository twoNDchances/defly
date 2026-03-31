<?php

return [
    'permission' => [
        'name' => 'Quyền',
        'fields' => [
            'name' => 'Tên',
            'description' => 'Mô tả',
            'applied_for' => 'Áp dụng cho',
            'action' => 'Hành động',
            'created_by' => 'Tạo bởi',
            'created_at' => 'Tạo lúc',
            'updated_at' => 'Cập nhật lúc',
        ],
    ],
    'policy' => [
        'name' => 'Chính sách',
        'fields' => [
            'name' => 'Tên',
            'description' => 'Mô tả',
            'created_by' => 'Tạo bởi',
            'created_at' => 'Tạo lúc',
            'updated_at' => 'Cập nhật lúc',
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
            'created_by' => 'Tạo bởi',
            'created_at' => 'Tạo lúc',
            'updated_at' => 'Cập nhật lúc',
        ],
    ],
];
