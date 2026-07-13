# Tham chiếu API

Defly có API của [Manager](Manager-Guide.md), [Orchestrator](Orchestrator-Guide.md) và [Defender](CoreConcepts/Defender.md), mỗi API có mục đích cùng cơ chế xác thực khác nhau. Tất cả ví dụ dưới đây dùng tiền tố mặc định; xem [Cấu hình](Configuration.md) nếu hệ thống đã đổi đường dẫn hoặc phương thức.

## Manager API

Đường dẫn gốc mặc định:

```text
/api/v1
```

### Xác thực

Mỗi yêu cầu cần đồng thời:

1. HTTP Basic Auth, tên đăng nhập là email [User](CoreConcepts/User.md) và mật khẩu là mật khẩu người dùng.
2. [Key](CoreConcepts/Key.md) thuộc chính người dùng đó, mặc định nằm trong tiêu đề HTTP `X-Token-Key`.

Ví dụ:

```powershell
$headers = @{
    Accept = "application/json"
    "X-Token-Key" = "<api-token>"
}

Invoke-RestMethod `
    -Uri "https://localhost/api/v1/me" `
    -Authentication Basic `
    -Credential (Get-Credential) `
    -Headers $headers
```

Key phải chưa hết hạn. Basic Auth đúng nhưng thiếu hoặc sai khóa vẫn trả `401`.

### Thao tác thêm, đọc, sửa, xóa tài nguyên

Các tài nguyên chính:

```text
users
groups
guards
permissions
labels
wordlists
patterns
engines
targets
actions
rules
principles
decisions
defenders
timelines
```

Các thao tác thông thường dùng:

| Phương thức | Đường dẫn | Ý nghĩa |
| --- | --- | --- |
| `GET` | `/{resources}` | Danh sách có phân trang. |
| `POST` | `/{resources}` | Tạo mới. |
| `GET` | `/{resources}/{id}` | Xem chi tiết. |
| `PUT` | `/{resources}/{id}` | Thay toàn bộ dữ liệu bắt buộc. |
| `PATCH` | `/{resources}/{id}` | Cập nhật một phần. |
| `DELETE` | `/{resources}/{id}` | Xóa. |

`patterns` chỉ hỗ trợ liệt kê/xem. `timelines` chỉ hỗ trợ liệt kê/xem/xóa.

Nhiều tài nguyên có `GET /{resources}/payload` để trả ví dụ yêu cầu theo giao kèo hiện tại.

### Quan hệ

Quan hệ dùng mẫu đường dẫn:

| Phương thức | Đường dẫn | Ý nghĩa |
| --- | --- | --- |
| `GET` | `/{resources}/{id}/{relation}` | Danh sách liên kết. |
| `POST` | `/{resources}/{id}/{relation}` | Gắn các ID. |
| `DELETE` | `/{resources}/{id}/{relation}` | Gỡ các ID. |

Dữ liệu gửi khi gắn/gỡ thường chứa:

```json
{
  "ids": ["<uuid-1>", "<uuid-2>"]
}
```

Ví dụ quan hệ: người dùng-quyền, nhóm-người dùng, Guard-User/Defender, Target-Engine, Rule-Action, Principle-Rule, tài nguyên-Label và Defender-Principle/Decision.

### Điểm cuối chính sách và Defender

| Phương thức | Đường dẫn | Ý nghĩa |
| --- | --- | --- |
| `POST` | `/principles/{id}/validate` | Kiểm tra [Principle](CoreConcepts/Principle.md). |
| `POST` | `/defenders/{id}/deploy` | Tạo tác vụ triển khai. |
| `POST` | `/defenders/{id}/follow` | Tạo tác vụ theo dõi nhật ký. |
| `POST` | `/defenders/{id}/cancel` | Tạo tác vụ hủy triển khai. |
| `POST` | `/defenders/{d}/principles/{p}/apply` | Áp dụng Principle. |
| `POST` | `/defenders/{d}/principles/{p}/revoke` | Thu hồi Principle. |
| `POST` | `/defenders/{d}/decisions/{x}/implement` | Cài [Decision](CoreConcepts/Decision.md). |
| `POST` | `/defenders/{d}/decisions/{x}/suspend` | Tạm ngưng Decision. |
| `GET` | `/defenders/{d}/reports` | Danh sách [Report](CoreConcepts/Report.md). |

Nếu Defender được bảo vệ bằng [Guard](CoreConcepts/Guard.md), các điểm cuối vòng đời
và điều khiển chính sách yêu cầu User đã xác thực là chủ sở hữu Defender hoặc thuộc
một Guard khớp và chưa hết hạn. Nếu không, Manager trả `403` hoặc tác vụ đã vào hàng
đợi sẽ dừng trước khi gọi Orchestrator/Defender.

## Orchestrator API

Orchestrator có hai nhóm endpoint nội bộ: assistant cho AI và deployments cho Docker.

```text
/api/v1/assistant/{conservation_id}
/api/v1/deployments/{defender_id}
```

API dùng Basic Auth. Thông tin xác thực `ORCHESTRATOR_USERNAME`/`ORCHESTRATOR_PASSWORD` của Manager phải khớp `SERVER_USERNAME`/`SERVER_PASSWORD`.

| Đường dẫn | Phương thức mặc định | Hành động | Phản hồi điển hình |
| --- | --- | --- | --- |
| `/assistant/{conservation_id}` | `GET` | Trả lời hội thoại AI của Manager. | `200`, nội dung assistant và model. |
| `/deployments/{defender_id}` | `POST` | Triển khai Defender. | `200`, chi tiết triển khai. |
| `/deployments/{defender_id}` | `GET` | Theo dõi nhật ký Defender. | `200`, phần nhật ký gần nhất. |
| `/deployments/{defender_id}` | `DELETE` | Hủy Defender. | `200`, chi tiết hủy. |

Phương thức và đường dẫn có thể đổi bằng biến môi trường ở cả hai phía. Orchestrator kiểm tra bên gọi, nhận email người thực hiện qua header đã cấu hình, kiểm tra quyền Guard khi thao tác Defender và kiểm tra quyền `Conservation:chat` khi gọi AI.

Mã lỗi thường gặp:

- `400`: thiếu ID hội thoại hoặc biến môi trường Defender không hợp lệ.
- `401`/`403`: xác thực hoặc bên gọi không hợp lệ.
- `404`: hội thoại, Defender, container hoặc nhật ký không tồn tại.
- `409`: theo dõi khi trạng thái triển khai chưa là `successful`.
- `500`/`502`/`503`: Docker, quá trình triển khai hoặc nhà cung cấp AI thất bại/chưa cấu hình.

## API điều khiển Defender

Địa chỉ gốc mặc định:

```text
http://<defender-host>:9947/api/v1
```

API này dùng để đồng bộ chính sách trên một Defender đang chạy:

| Phương thức mặc định | Đường dẫn | Ý nghĩa |
| --- | --- | --- |
| `PUT` | `/principles` | Áp dụng Principle. |
| `DELETE` | `/principles` | Thu hồi Principle. |
| `PUT` | `/decisions` | Cài Decision. |
| `DELETE` | `/decisions` | Tạm ngưng Decision. |

Phương thức và đường dẫn có thể đổi bằng biến môi trường máy chủ của Defender. Yêu cầu được bảo vệ bởi lớp xác thực quyền, tư cách thành viên Guard và thông tin người thực thi; API này là nội bộ, không nên công bố ra Internet.

## Tiêu đề HTTP và nội dung dùng chung

```text
Accept: application/json
Content-Type: application/json
Accept-Language: vi
```

Manager API hỗ trợ ngôn ngữ qua `Accept-Language`. Lỗi kiểm tra dữ liệu thường trả `422` kèm danh sách lỗi theo trường. Tác vụ triển khai có thể trả thành công ở tầng Manager trước khi Orchestrator hoàn tất, vì vậy cần theo dõi `deployment_status` thay vì chỉ dựa vào phản hồi lúc tạo tác vụ.
