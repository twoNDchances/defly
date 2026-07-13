<?php

return [
    'actions' => [
        'open' => 'Mở',
        'view_defender' => 'Xem defender',
        'view_principle' => 'Xem nguyên tắc',
    ],

    'defender' => [
        'deployment' => [
            'processing' => 'Đang xử lý triển khai.',
            'request_failed' => 'Yêu cầu triển khai tới Orchestrator thất bại.',
            'exception' => 'Có lỗi không xử lý được khi triển khai defender.',
            'completed' => [
                'title' => 'Triển khai defender hoàn tất',
                'body' => '":name" đã được triển khai thành công.',
            ],
            'failed' => [
                'title' => 'Triển khai defender thất bại',
            ],
        ],
        'cancellation' => [
            'queued' => 'Yêu cầu hủy đã được đưa vào hàng đợi.',
            'request_failed' => 'Yêu cầu hủy tới Orchestrator thất bại.',
            'exception' => 'Có lỗi không xử lý được khi hủy defender.',
            'completed' => [
                'title' => 'Hủy defender hoàn tất',
                'body' => '":name" đã được hủy thành công.',
            ],
            'failed' => [
                'title' => 'Hủy defender thất bại',
            ],
        ],
        'failure' => [
            'body' => '":name": :detail',
            'default_detail' => 'Xem chi tiết triển khai để biết thêm thông tin.',
            'http_status' => 'Mã HTTP: :status.',
        ],
        'guard' => [
            'denied' => 'Người yêu cầu không thuộc khiên còn hiệu lực của defender này.',
        ],
        'action' => [
            'skipped' => [
                'title' => 'Bỏ qua hành động defender',
                'missing' => 'Defender đã chọn không còn tồn tại.',
                'not_deployed' => '":name" phải được triển khai thành công trước khi chạy hành động này.',
                'empty' => 'Không có tài nguyên nào được chọn cho hành động này.',
                'guard_denied' => 'Người yêu cầu không thuộc khiên còn hiệu lực của ":name".',
            ],
        ],
    ],

    'principle' => [
        'action' => [
            'skipped' => [
                'title' => 'Bỏ qua hành động nguyên tắc',
                'not_attached' => 'Không có nguyên tắc đã chọn nào được gắn với ":name".',
            ],
        ],
        'validation' => [
            'skipped' => [
                'title' => 'Bỏ qua xác thực nguyên tắc',
                'missing' => 'Nguyên tắc đã chọn không còn tồn tại.',
            ],
            'passed' => [
                'title' => 'Xác thực nguyên tắc thành công',
            ],
            'failed' => [
                'title' => 'Xác thực nguyên tắc thất bại',
            ],
            'finished' => '":name" đã xác thực xong với :count lỗi.',
            'exception' => 'Có lỗi không xử lý được khi xác thực nguyên tắc.',
            'exception_body' => '":name": :message',
        ],
    ],

    'decision' => [
        'action' => [
            'skipped' => [
                'title' => 'Bỏ qua hành động phán quyết',
                'not_attached' => 'Không có phán quyết đã chọn nào được gắn với ":name".',
            ],
        ],
    ],

    'communication' => [
        'actions' => [
            'apply' => 'áp dụng',
            'revoke' => 'thu hồi',
            'implement' => 'thực hiện',
            'suspend' => 'đình chỉ',
        ],
        'resources' => [
            'principle' => '{1} nguyên tắc|[2,*] nguyên tắc',
            'decision' => '{1} phán quyết|[2,*] phán quyết',
        ],
        'result_body' => [
            'successful' => 'Đã :action :count :resource trên ":defender". Mã HTTP: :status.',
            'failed' => 'Không thể :action :count :resource trên ":defender". Mã HTTP: :status.',
            'exception' => 'Không thể :action :count :resource trên ":defender". :message',
        ],
        'titles' => [
            'principle' => [
                'apply' => [
                    'completed' => 'Áp dụng nguyên tắc hoàn tất',
                    'failed' => 'Áp dụng nguyên tắc thất bại',
                ],
                'revoke' => [
                    'completed' => 'Thu hồi nguyên tắc hoàn tất',
                    'failed' => 'Thu hồi nguyên tắc thất bại',
                ],
            ],
            'decision' => [
                'implement' => [
                    'completed' => 'Thực hiện phán quyết hoàn tất',
                    'failed' => 'Thực hiện phán quyết thất bại',
                ],
                'suspend' => [
                    'completed' => 'Đình chỉ phán quyết hoàn tất',
                    'failed' => 'Đình chỉ phán quyết thất bại',
                ],
            ],
        ],
    ],
];
