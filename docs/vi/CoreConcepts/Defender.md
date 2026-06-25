# Defender

Defender vừa là bản ghi cấu hình trong Manager, vừa là WAF/reverse proxy được tạo từ
bản ghi đó. Trang này định nghĩa model và invariant state. Việc tạo container thuộc
[Hướng dẫn Orchestrator](../Orchestrator-Guide.md), thực thi traffic thuộc
[Hướng dẫn Defender](../Defender-Guide.md), còn key environment chính xác thuộc
[Biến môi trường](../Environment-Variables.md#biến-của-defender).

## Các trường

| Trường | Chủ sở hữu | Ý nghĩa |
| --- | --- | --- |
| `name` | User | Identity lowercase-gạch-ngang duy nhất để liên kết record, container, certificate và control hostname |
| `proxy_port` | User | Host port publish cho traffic được bảo vệ |
| `environment_variables` | User/hệ thống | Cấu hình common, control server và proxy đã validate, lưu trong một JSON object |
| `description` | User | Ngữ cảnh quản trị |
| `deployment_status` | Workflow deployment | Tiến độ create/cancel |
| `deployment_details` | Workflow deployment | Kết quả hoặc lỗi có thể xử lý |
| `status` | Runtime Defender | Doctor health (`normal`, `abnormal` hoặc chưa biết) |
| `details` | Runtime Defender | Bằng chứng sức khỏe |
| `last_response_details` | Workflow điều khiển policy | Response apply/revoke/implement/suspend gần nhất |

Manager hiển thị environment theo các nhóm key cố định. User sửa value, không thêm key
tùy ý; giá trị hệ thống như identity Defender và proxy port hiệu lực được inject lúc
deployment.

## Ba chiều state độc lập

Không gom các state sau thành một cờ “đang chạy”.

### Deployment state

| Giá trị | Ý nghĩa |
| --- | --- |
| `pending` | Công việc đã vào queue |
| `processing` | Worker/Orchestrator đang thực hiện |
| `failed` | Deployment operation lỗi; xem `deployment_details` |
| `successful` | Container đã được tạo thành công |
| `null` | Chưa có kết quả deployment đang hoạt động |

Record pending/processing không được redeploy. Record successful phải cancel trước
khi xóa.

### Runtime health

`status` và `details` đến từ Doctor/runtime của Defender. Deployment successful vẫn có
thể unhealthy; record database trông bình thường không chứng minh proxy/backend path
hoạt động. Dùng kiểm tra theo tầng tại [Vận hành](../Operations.md).

### Kích hoạt policy

Attach và activate là hai việc riêng:

| Quan hệ | Cờ pivot có thứ tự | Hoạt động khi |
| --- | --- | --- |
| [Principle](Principle.md) | `is_applied` | Đã attach, validate và apply thành công |
| [Decision](Decision.md) | `is_implemented` | Đã attach và implement thành công |

Attach chỉ tạo deployment intent. Action apply/revoke và implement/suspend trong
Manager gọi control API Defender; pivot chỉ đổi sau response thành công. Hãy
revoke/suspend trước khi detach item đang active.

## Ràng buộc policy

- Chỉ Principle có validation status `passed` đi vào apply flow chuẩn.
- Apply/implement yêu cầu Defender đã deployment successful.
- Principle và Decision giữ relationship order.
- Principle/Decision bị khóa khi còn attach vào bất kỳ Defender nào.
- Validation Principle và mọi action lifecycle/policy-control Defender là workflow
  thủ công trong Manager.

`last_response_details` giữ control response mới nhất riêng cho Principle và Decision
để không nhầm kết quả deployment với kết quả policy.

## Report

Defender sở hữu nhiều [Report](Report.md). Trong quan hệ này,
`reports.created_by` nhận diện Defender đã quan sát request, không phải User quản trị.

## Trước deployment

Kiểm tra input ở mức model:

- tên duy nhất và resolve được;
- host proxy port chưa dùng;
- backend URL/database hợp lệ từ network Defender;
- Principle đã validate và Decision có chủ đích;
- environment value vượt qua validation Manager.

Sau đó dùng [Cấu hình](../Configuration.md) cho contract giữa service,
[Hướng dẫn Orchestrator](../Orchestrator-Guide.md) cho hành vi container và
[Khắc phục sự cố](../Troubleshooting.md) khi có lỗi.
