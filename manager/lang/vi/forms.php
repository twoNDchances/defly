<?php

return [
    'user' => [
        'text_examples' => [
            'name' => 'Nguyen Van A',
            'email' => 'nguoidung@tenmien.com',
            'password' => 'M4tkh4u123',
        ],
        'descriptions' => [
            'name' => 'Tên đơn giản cho người dùng này',
            'email' => 'Địa chỉ email không trùng để xác thực',
            'password' => 'Mật khẩu mạnh để xác thực',
            'is_verified' => 'Tắt nếu bạn muốn người dùng phải xác minh bằng địa chỉ email trước khi đăng nhập, bật sẽ đặt đã xác minh',
            'is_root' => 'Bật nếu bạn muốn người dùng này có thể toàn quyền trong hệ thống',
            'is_activated' => 'Tắt nếu bạn muốn người dùng này không thể dùng hệ thống tạm thời',
        ],
        'actions' => [
            'generate_password' => 'Tạo mật khẩu'
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa người dùng',
            ]
        ],
    ],
];
