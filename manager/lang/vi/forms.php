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
        'specials' => [
            'phase' => [
                1 => 'Liên quan đến mọi thứ của yêu cầu',
                2 => 'Liên quan đến mọi thứ của phần tiêu đề trong yêu cầu',
                3 => 'Liên quan đến mọi thứ của phần nội dung trong yêu cầu',
                4 => 'Liên quan đến mọi thứ của phần tiêu đề trong phản hồi',
                5 => 'Liên quan đến mọi thứ của phần nội dung trong phản hồi',
                6 => 'Liên quan đến mọi thứ của phản hồi',
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
            'method' => [
                'get' => 'Phương thức GET khi thực hiện yêu cầu HTTP',
                'post' => 'Phương thức POST khi thực hiện yêu cầu HTTP',
                'put' => 'Phương thức PUT khi thực hiện yêu cầu HTTP',
                'patch' => 'Phương thức PATCH khi thực hiện yêu cầu HTTP',
                'delete' => 'Phương thức DELETE khi thực hiện yêu cầu HTTP',
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
            'output_datatype' => 'Kiểu dữ liệu đầu ra được chuyển đổi',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa động cơ',
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
    'action' => [
        'text_examples' => [
            'name' => 'hanh-dong',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho hành động này',
            'type' => 'Kiểu hành động sẽ được kích hoạt nếu khớp điều kiện của quy tắc',
            'wordlist' => 'Chọn một danh sách từ để định nghĩa cấu hình cho các loại hành động cần nhiều tham số',
        ],
        'sections' => [
            'a' => [
                'title' => 'Định nghĩa hành động',
            ],
        ],
        'extras' => [
            'type' => [
                'allow' => 'Dừng các hành động tiếp theo và cho phép yêu cầu hoặc phản hồi được tiếp tục',
                'deny' => 'Dừng các hành động tiếp theo và từ chối yêu cầu hoặc phản hồi được tiếp tục',
                'log' => 'Ghi nhật ký chi tiết của yêu cầu hoặc phản hồi',
                'request' => 'Gửi một yêu cầu HTTP',
                'report' => 'Gửi một báo cáo về Manager với các chi tiết',
                'suspect' => 'Vòng đời HTTP bao gồm chiều đi là yêu cầu và chiều về là phản hồi sẽ tăng điểm. Điểm được định nghĩa theo mức độ nghiêm trọng',
                'setter' => 'Thêm, bớt hoặc thay đổi các biến ảo trong một vòng đời HTTP',
                'score' => 'Cập nhật lại điểm vi phạm tối đa',
                'level' => 'Cập nhật lại cấp độ quy tắc được sử dụng',
            ],
            'configurations' => [
                'deny_status' => 'Chọn một trạng thái phản hồi khi từ chối',
                'deny_content_type' => 'Loại nội dung trả về khi từ chối',
                'deny_body' => 'Phần nội dung trả về khi từ chối',
                'log_format' => 'Định dạng mong muốn khi ghi nhật ký',
                'log_console' => 'Ghi nhật ký theo định dạng ra màn hình console',
                'log_file' => 'Ghi nhật ký theo định dạng ra tệp',
                'request_url' => 'Một đường dẫn URL muốn gửi yêu cầu đến',
                'request_method' => 'Phương thức HTTP để gửi yêu cầu',
                'request_headers' => 'Thêm hoặc cập nhật các tiêu đề trước khi gửi yêu cầu HTTP',
                'request_body' => 'Nội dung của yêu cầu HTTP. Nội dung sẽ được chuyển dạng tham số URL nếu phương thức là GET',
                'suspect_severity' => 'Tăng mức độ nghiêm trọng bằng cách cộng giá trị của mỗi mức',
                'setter_directive' => 'Chỉ thị cách quản lý các biến ảo',
                'setter_set' => 'Thêm hoặc cập nhật các biến ảo để giao tiếp giữa các quy tắc',
                'setter_unset' => 'Hủy các biến ảo để tăng điều kiện kiểm soát',
                'score_behavior' => 'Bạn có thể quyết định mức điểm vi phạm tăng hay giảm',
                'score_value' => 'Giá trị cho điểm vi phạm',
                'level_value' => 'Giá trị cho cấp vi phạm',
                'level_behavior' => 'Bạn có thể quyết định cấp độ vi phạm tăng hay giảm',
            ],
            'deny_content_type' => [
                'html' => 'Loại nội dung HTML sẽ được trả về khi từ chối',
                'json' => 'Loại nội dung JSON sẽ được trả về khi từ chối',
            ],
            'key' => 'Dùng để định danh nội dung',
            'value' => 'Dùng để lưu trữ nội dung',
            'set' => 'Thêm hoặc cập nhật dữ liệu',
            'unset' => 'Hủy dữ liệu',
            'score_behavior' => [
                'override' => 'Sử dụng khi bạn muốn cập nhật chính xác giá trị',
                '+' => 'Thực hiện toán tử cộng',
                '-' => 'Thực hiện toán tử trừ',
                '*' => 'Thực hiện toán tử nhân',
                '/' => 'Thực hiện toán tử chia',
            ],
            'level_behavior' => [
                'override' => 'Sử dụng khi bạn muốn cập nhật chính xác giá trị',
                'increase' => 'Thực hiện tăng theo số đơn vị',
                'decrease' => 'Thực hiện giảm theo số đơn vị',
            ],
        ],
    ],
    'rule' => [
        'text_examples' => [
            'name' => 'quy-tac',
        ],
        'descriptions' => [
            'name' => 'Tên duy nhất theo kiểu kebab đại diện cho quy tắc này',
            'phase' => 'Giai đoạn mà mục tiêu có thể lấy được',
            'target' => 'Mục tiêu được chọn để so sánh',
            'comparator' => 'So sánh giá trị của mục tiêu đã lấy với giá trị được cho theo các kiểu dữ liệu khác nhau',
            'is_inversed' => 'Nghịch đảo bộ so sánh',
            'wordlist' => 'Chọn một danh sách từ để bộ so sánh sử dụng cho dữ liệu cần so sánh',
        ],
        'steps' => [
            'a' => [
                'title' => 'Chuẩn bị quy tắc',
            ],
            'b' => [
                'title' => 'Định nghĩa quy tắc',
            ],
        ],
        'extras' => [
            'comparator' => [
                '@similar' => '[(Mục tiêu){Mảng} @ (Giá trị){Danh sách từ}] Đúng nếu phần tử đầu tiên trong mảng của mục tiêu trùng với phần từ đầu tiên trong danh sách từ được cho',
                '@contains' => '[(Mục tiêu){Mảng} @ (Giá trị){Chuỗi}] Đúng nếu phần tử đầu tiên trong mảng của mục tiêu trùng với giá trị chuỗi được cho',
                '@match' => '[(Mục tiêu){Mảng} @ (Giá trị){Chuỗi}] Đúng nếu phần tử đầu tiên trong mảng của mục tiêu trùng với giá trị chuỗi được cho bằng cách sử dụng biểu thức chính quy',
                '@search' => '[(Mục tiêu){Mảng} @ (Giá trị){Danh sách từ}] Đúng nếu phần tử đầu tiên trong mảng của mục tiêu trùng với phần tử đầu tiên trong danh sách từ được cho bằng cách sử dụng biểu thức chính quy',
                '@equal' => '[(Mục tiêu){Số} @ (Giá trị){Số}] Đúng nếu giá trị số của mục tiêu trùng với giá trị số được cho',
                '@greaterThan' => '[(Mục tiêu){Số} @ (Giá trị){Số}] Đúng nếu giá trị số của mục tiêu lớn hơn giá trị số được cho',
                '@lessThan' => '[(Mục tiêu){Số} @ (Giá trị){Số}] Đúng nếu giá trị số của mục tiêu nhỏ hơn giá trị số được cho',
                '@greaterThanOrEqual' => '[(Mục tiêu){Số} @ (Giá trị){Số}] Đúng nếu giá trị số của mục tiêu lớn hơn hoặc bằng giá trị số được cho',
                '@lessThanOrEqual' => '[(Mục tiêu){Số} @ (Giá trị){Số}] Đúng nếu giá trị số của mục tiêu nhỏ hơn hoặc bằng giá trị số được cho',
                '@inRange' => '[(Mục tiêu){Số} @ (Giá trị){Phạm vi số}] Đúng nếu giá trị số của mục tiêu nằm trong phạm vi từ số nhỏ đến số lớn',
                '@mirror' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Chuỗi}] Đúng nếu giá trị chuỗi của mục tiêu trùng với giá trị chuỗi được cho',
                '@startsWith' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Chuỗi}] Đúng nếu giá trị chuỗi của mục tiêu bắt đầu giống với giá trị chuỗi được cho',
                '@endsWith' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Chuỗi}] Đúng nếu giá trị chuỗi của mục tiêu kết thúc giống với giá trị chuỗi được cho',
                '@check' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Danh sách từ}] Đúng nếu giá trị chuỗi của mục tiêu trùng với phần tử đầu tiên trong danh sách từ được cho',
                '@regExp' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Chuỗi}] Đúng nếu giá trị chuỗi của mục tiêu trùng với giá trị chuỗi được cho bằng cách sử dụng biểu thức chính quy',
                '@checkRegExp' => '[(Mục tiêu){Chuỗi} @ (Giá trị){Danh sách từ}] Đúng nếu giá trị chuỗi của mục tiêu trùng với phần tử đầu tiên trong danh sách từ được cho bằng cách sử dụng biểu thức chính quy',
            ],
            'configurations' => [
                'string_value' => 'Giá trị có kiểu dữ liệu chuỗi dùng để so sánh với mục tiêu có kiểu dữ liệu cuối cùng là chuỗi',
                'number_value' => 'Giá trị có kiểu dữ liệu số dùng để so sánh với mục tiêu có kiểu dữ liệu cuối cùng là số',
                'number_from_value' => 'Giá trị từ số cụ thể',
                'number_to_value' => 'Giá trị đến số cụ thể',
            ],
        ],
    ],
];
