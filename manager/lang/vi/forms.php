<?php

return [
    'commons' => [
        'labels' => [
            'label' => 'Nhãn',
            'description' => 'Bạn cũng có thể phân loại dữ liệu cho loại tài nguyên này',
        ],
        'sections' => [
            'labels' => [
                'title' => 'Gán nhãn tài nguyên',
            ],
        ],
    ],
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
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa quyền',
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
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa chính sách',
            ],
        ],
    ],
    'label' => [
        'text_examples' => [
            'name' => 'nhan-tai-nguyen',
            'description' => 'Một số mô tả về nhãn này',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho nhãn này',
            'color' => 'Mã màu cho nhãn này để dễ phân biệt hơn',
            'description' => 'Bạn có thể giải thích rõ hơn nếu nhãn phức tạp',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa nhãn',
            ],
        ],
    ],
    'wordlist' => [
        'text_examples' => [
            'name' => 'danh-sach-tu',
            'description' => 'Một số mô tả về danh sách từ này',
            'word' => 'abc',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho danh sách từ này',
            'word_type' => 'Chọn một loại của danh sách từ',
            'word_file' => 'Đường dẫn đến tệp nội dung của danh sách từ này, sử dụng khi bạn có một tệp chứa số lượng từ lớn. Các từ được nhận dạng bằng cách xuống dòng mới',
            'word_json' => 'Định dạng dữ liệu JSON của danh sách từ này, sử dụng khi bạn có thể định nghĩa nó ở đây',
            'description' => 'Bạn có thể giải thích rõ hơn nếu danh sách từ phức tạp',
            'word' => 'Từ hoặc các chữ cái',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa danh sách từ',
            ],
            'b' => [
                'title' => 'Định nghĩa các từ',
            ],
        ],
    ],
];
