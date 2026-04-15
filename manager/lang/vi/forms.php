<?php

return [
    'generals' => [
        'bases' => [
            'fields' => [
                'description' => [
                    'text_examples' => 'Một số mô tả về tài nguyên này',
                    'descriptions' => 'Bạn có thể giải thích rõ hơn nếu tài nguyên này phức tạp',
                ],
            ],
            'sections' => [
                'labels' => [
                    'title' => 'Gán nhãn tài nguyên',
                    'description' => 'Bạn cũng có thể phân loại dữ liệu cho loại tài nguyên này',
                ],
            ],
        ],
        'specifics' => [
            'phase' => [
                '1' => 'Liên quan đến mọi thứ của yêu cầu',
                '2' => 'Liên quan đến mọi thứ của phần tiêu đề trong yêu cầu',
                '3' => 'Liên quan đến mọi thứ của phần nội dung trong yêu cầu',
                '4' => 'Liên quan đến mọi thứ của phần tiêu đề trong phản hồi',
                '5' => 'Liên quan đến mọi thứ của phần nội dung trong phản hồi',
                '6' => 'Liên quan đến mọi thứ của phản hồi',
            ],
            'type' => [
                'getter' => 'Tìm theo khóa và lấy giá trị của một biến trong một vòng đời của yêu cầu hoặc phản hồi, biến này có truy cập ở mọi giai đoạn',
                'full' => 'Xây dựng dữ liệu đầy đủ của yêu cầu hoặc phản hồi',
                'header' => 'Tìm và lấy khóa hoặc giá trị liên quan đến tiêu đề của yêu cầu hoặc phản hồi',
                'meta' => 'Tìm và lấy giá trị liên quan đến siêu dữ liệu của yêu cầu hoặc phản hồi',
                'query' => 'Tìm và lấy khóa hoặc giá trị liên quan đến tham số URL của yêu cầu',
                'body' => 'Tìm và lấy khóa hoặc giá trị liên quan đến nội dung của yêu cầu hoặc phản hồi',
                'file' => 'Tìm và lấy khóa hoặc giá trị liên quan đến tệp của yêu cầu hoặc phản hồi',
            ],
            'datatype' => [
                'array' => 'Kiểu mảng của dữ liệu chuỗi',
                'number' => 'Kiểu dữ liệu số, bao gồm số nguyên và số thập phân',
                'string' => 'Kiểu dữ liệu chuỗi',
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
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất đại diện cho quyền này',
            'applied_for' => 'Phạm vi quyền áp dụng',
            'action' => 'Hành động được cho phép trong phạm vi đã chọn',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa quyền',
            ],
        ],
    ],
    'group' => [
        'text_examples' => [
            'name' => 'nhom-quan-ly',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho nhóm này',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa nhóm',
            ],
        ],
    ],
    'label' => [
        'text_examples' => [
            'name' => 'nhan-tai-nguyen',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho nhãn này',
            'color' => 'Mã màu cho nhãn này để dễ phân biệt hơn',
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
            'word' => 'abc',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho danh sách từ này',
            'type' => 'Chọn một loại của danh sách từ',
            'word_file' => 'Đường dẫn đến tệp nội dung của danh sách từ này, sử dụng khi bạn có một tệp chứa số lượng từ lớn. Các từ được nhận dạng bằng cách xuống dòng mới',
            'word_json' => 'Định dạng dữ liệu JSON của danh sách từ này, sử dụng khi bạn có thể định nghĩa nó ở đây',
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
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho động cơ này',
            'input_datatype' => 'Kiểu dữ liệu đầu vào cần chuyển đổi',
            'type' => 'Chọn một loại động cơ phù hợp cho kiểu dữ liệu đầu vào',
            'configurations' => 'Tùy loại động cơ bạn chọn sẽ yêu cầu cấu hình tùy chỉnh cho động cơ đó',
            'output_datatype' => 'Kiểu dữ liệu đầu ra được chuyển đổi',
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
            ],
            'configurations' => [
                'position' => 'Một vị trí cụ thể trong mảng',
                'digit' => 'Một con số cụ thể để thực hiện toán tử',
                'hash_method' => 'Một hàm băm để thực hiện băm giá trị đầu vào',
                'separator' => 'Bạn có thể chọn một hoặc nhiều ký tự',
            ],
        ],
    ],
    'pattern' => [
        'descriptions' => [
            'name' => 'Tên của các mẫu dùng để xác định loại dữ liệu sẽ lấy',
            'phase' => 'Giai đoạn mà dữ liệu có thể lấy được',
            'type' => 'Loại phạm vi mà dữ liệu xuất hiện',
            'datatype' => 'Kiểu dữ liệu mà mẫu lấy được sẽ trả về',
            'targets' => 'Chọn một hoặc nhiều mục tiêu áp dụng mẫu này',
            'description' => 'Mô tả mẫu',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa mẫu',
            ],
            'b' => [
                'title' => 'Các mục tiêu triển khai',
            ],
        ],
    ],
    'target' => [
        'text_examples' => [
            'name' => 'muc-tieu-dieu-tra',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho mục tiêu này',
            'phase' => 'Giai đoạn mà dữ liệu có thể lấy được',
            'type' => 'Loại phạm vi mà dữ liệu xuất hiện',
            'datatype' => 'Kiểu dữ liệu mục tiêu này trả về',
            'pattern' => 'Chọn một mẫu có sẵn để định nghĩa mục tiêu',
            'wordlist' => 'Chọn một danh sách từ để định nghĩa mục tiêu cho kiểu dữ liệu mảng',
        ],
        'steps' => [
            'a' => [
                'title' => 'Chuẩn bị mục tiêu',
            ],
            'b' => [
                'title' => 'Định nghĩa mục tiêu',
            ],
        ],
    ],
];
