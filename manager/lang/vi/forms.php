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
            'policies' => 'Bạn có thể gán nhiều chính sách cho người dùng này',
            'permissions' => 'Bạn có thể gán nhiều quyền cho người dùng này',
        ],
        'buttons' => [
            'generate_password' => 'Tạo mật khẩu',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa người dùng',
            ],
        ],
    ],
    'permission' => [
        'text_examples' => [
            'name' => 'Pham vi:Quyen',
            'description' => 'Một số mô tả về quyền này',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất đại diện cho quyền này',
            'applied_for' => 'Phạm vi quyền áp dụng',
            'action' => 'Hành động được cho phép trong phạm vi đã chọn',
            'description' => 'Bạn có thể giải thích rõ hơn nếu quyền phức tạp',
            'users' => 'Bạn có thể gán nhiều người dùng cho quyền này',
            'policies' => 'Bạn có thể gán nhiều chính sách cho quyền này',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa quyền',
            ],
            'b' => [
                'title' => 'Kiểm soát bảo mật',
            ],
        ],
    ],
    'policy' => [
        'text_examples' => [
            'name' => 'chinh-sach-quan-ly',
            'description' => 'Một số mô tả về chính sách này',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho chính sách này',
            'description' => 'Bạn có thể giải thích rõ hơn nếu chính sách phức tạp',
            'users' => 'Bạn có thể gán nhiều người dùng cho chính sách này',
            'permissions' => 'Bạn có thể gán nhiều quyền cho chính sách này',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa chính sách',
            ],
            'b' => [
                'title' => 'Kiểm soát bảo mật',
            ],
        ],
    ],
];
