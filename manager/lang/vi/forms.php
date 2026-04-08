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
        'datatype' => [
            'array' => 'Kiểu mảng của dữ liệu chuỗi',
            'number' => 'Kiểu dữ liệu số, bao gồm số nguyên và số thập phân',
            'string' => 'Kiểu dữ liệu chuỗi',
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
            'type' => 'Chọn một loại của danh sách từ',
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
    'engine' => [
        'text_examples' => [
            'name' => 'dong-co-chuyen-doi',
            'description' => 'Một số mô tả về động cơ này',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho động cơ này',
            'input_datatype' => 'Kiểu dữ liệu đầu vào cần chuyển đổi',
            'type' => 'Chọn một loại động cơ phù hợp cho kiểu dữ liệu đầu vào',
            'configurations' => 'Tùy loại động cơ bạn chọn sẽ yêu cầu cấu hình tùy chỉnh cho động cơ đó',
            'output_datatype' => 'Kiểu dữ liệu đầu ra được chuyển đổi',
            'description' => 'Bạn có thể giải thích rõ hơn nếu động cơ phức tạp',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa động cơ',
                'fieldsets' => [
                    'a' => [
                        'title' => 'Bộ chuyển đổi',
                    ],
                    'b' => [
                        'title' => 'Các cấu hình',
                    ],
                ],
            ],
        ],
        'extras' => [
            'type' => [
                'indexOf' => 'Lấy vị trí của mảng ([...][index])',
                'merge' => 'Kết hợp tất cả phần tử lại với nhau, ngăn cách bằng ký tự ("abc,def")',
                'addition' => 'Cộng thêm (+)',
                'subtraction' => 'Trừ đi (-)',
                'multiplication' => 'Nhân lên (*)',
                'division' => 'Chia ra (/)',
                'powerOf' => 'Mũ (^)',
                'remainder' => 'Chia lấy dư (%)',
                'toString' => 'Đổi sang kiểu dữ liệu chuỗi ("1")',
                'lower' => 'Tất cả viết thường ("abc def")',
                'upper' => 'Tất cả viết hoa ("ABC DEF")',
                'capitalize' => 'Viết hoa chữ đầu ("Abc Def")',
                'trim' => 'Xóa khoảng trắng 2 bên ("abc def")',
                'trimLeft' => 'Xóa khoảng trắng bên trái ("abc def ")',
                'trimRight' => 'Xóa khoảng trắng bên phải (" abc def")',
                'removeWhitespace' => 'Xóa tất cả khoảng trắng ("abcdef")',
                'length' => 'Lấy độ dài chuỗi (7)',
                'hash' => 'Lấy giá trị băm ("e80b50...")',
                'split' => 'Tách chuỗi ra nhiều phần tử, tìm theo ký tự chỉ định (["a", "b", "c", ...])',
                'configurations' => [
                    'position' => 'Một vị trí cụ thể trong mảng',
                    'digit' => 'Một con số cụ thể để thực hiện toán tử',
                    'hash_method' => 'Một hàm băm để thực hiện băm giá trị đầu vào',
                    'separator' => 'Bạn có thể chọn một hoặc nhiều ký tự',
                ],
            ],
        ],
    ],
    'pattern' => [
        'descriptions' => [
            'name' => 'Tên của các mẫu dùng để xác định loại dữ liệu sẽ lấy',
            'phase' => 'Giai đoạn mà dữ liệu có thể lấy được',
            'type' => 'Loại phạm vi mà dữ liệu xuất hiện',
            'datatype' => 'Kiểu dữ liệu mà mẫu lấy được sẽ trả về',
            'description' => 'Mô tả mẫu',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa mẫu',
            ],
            'b' => [
                'title' => 'Các mục tiêu triển khai'
            ],
        ],
    ],
];
