# Tham chiếu nhanh

Trang này dùng để tra cứu. Phần giải thích đầy đủ nằm trong các trang được liên kết.

## Cấu trúc kho mã nguồn

```text
defly/
  manager/          Laravel/Filament, lược đồ và giao diện/API
  orchestrator/     Django ASGI, triển khai qua Docker
  defender/         Go, API điều khiển, proxy và tường lửa
  docs/vi/          Tài liệu tiếng Việt
  architectures/    Sơ đồ kiến trúc
  docker-compose.yml
  .env.example
```

## Cổng mặc định

| Thành phần | Cổng |
| --- | --- |
| Manager HTTP | `80` |
| Manager HTTPS | `443` |
| Manager chạy thủ công | `8080` |
| Orchestrator cục bộ | `8000` |
| Defender control API | `9947` |
| Defender proxy thủ công | `9948` |

Cổng proxy khi triển khai lấy từ bản ghi [Defender](CoreConcepts/Defender.md).

## URL mặc định

| API/giao diện | Đường dẫn |
| --- | --- |
| Manager UI | `/defly-manager` |
| Manager API | `/api/v1` |
| API triển khai Orchestrator | `/api/v1/deployments/{defender_id}` |
| API điều khiển Defender | `/api/v1/principles`, `/api/v1/decisions` |

## Docker

| Tài nguyên | Tên mặc định |
| --- | --- |
| Dự án Compose | `defly` |
| Mạng hạ tầng | `defly_infrastructure` |
| Image Defender | `defly-defender:latest` |
| Ổ dữ liệu TLS Defender | `defly_defender_tls` |

Tên thực tế thay đổi theo `COMPOSE_PROJECT_NAME` và `SERVER_DEFENDER_TLS_VOLUME`.

## Chuỗi xử lý WAF

```text
Wordlist/Pattern -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

Xem [mục lục khái niệm](CoreConcepts/README.md) để đọc theo thứ tự.

## Giai đoạn

| Số | Giai đoạn |
| --- | --- |
| `1` | Toàn bộ yêu cầu |
| `2` | Tiêu đề HTTP/truy vấn/siêu dữ liệu yêu cầu |
| `3` | Nội dung/tệp yêu cầu |
| `4` | Tiêu đề HTTP/siêu dữ liệu phản hồi |
| `5` | Nội dung phản hồi |
| `6` | Toàn bộ phản hồi |

Chi tiết loại hợp lệ tại [Target](CoreConcepts/Target.md#loại-hợp-lệ-theo-giai-đoạn).

## Trạng thái

Trạng thái kiểm tra Principle:

```text
pending | validating | failed | passed
```

Trạng thái triển khai Defender:

```text
pending | processing | failed | successful
```

Trạng thái chạy Defender:

```text
normal | abnormal
```

## Tài liệu theo nhu cầu

- Cài hệ thống: [Cài đặt](Installation.md)
- Cách tổ chức cấu hình: [Cấu hình](Configuration.md)
- Danh sách biến môi trường: [Biến môi trường](Environment-Variables.md)
- Dùng giao diện: [Hướng dẫn Manager](Manager-Guide.md)
- Vận hành container: [Hướng dẫn Orchestrator](Orchestrator-Guide.md)
- Hiểu quá trình chạy tường lửa: [Hướng dẫn Defender](Defender-Guide.md)
- Gọi API: [Tham chiếu API](API-Reference.md)
- Xử lý lỗi: [Khắc phục sự cố](Troubleshooting.md)
