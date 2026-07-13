# Tài liệu Defly

Defly tách quản lý chính sách, tác vụ nội bộ và thực thi lưu lượng cho Manager, Orchestrator và Defender. Hãy bắt đầu từ đúng nhu cầu; mỗi chủ đề chỉ có một tài liệu chịu trách nhiệm chính.

## Sử dụng lần đầu

1. [Tổng quan](Overview.md): mục tiêu sản phẩm và ranh giới service.
2. [Cài đặt](Installation.md): cài bằng Docker Compose hoặc source.
3. [Bắt đầu nhanh](Getting-Started.md): tạo và kiểm tra chính sách đầu tiên sau khi cài.

## Tài liệu chịu trách nhiệm

| Chủ đề | Tài liệu chịu trách nhiệm |
| --- | --- |
| Ownership service và data flow | [Kiến trúc](Architecture.md) |
| Chiến lược cấu hình và contract giữa service | [Cấu hình](Configuration.md) |
| Tên biến, mặc định và validation | [Biến môi trường](Environment-Variables.md) |
| Ý nghĩa model và semantics chính sách | [Khái niệm cốt lõi](CoreConcepts/README.md) |
| Workflow giao diện Manager | [Hướng dẫn Manager](Manager-Guide.md) |
| Tác vụ nội bộ Docker và AI | [Hướng dẫn Orchestrator](Orchestrator-Guide.md) |
| Thực thi request/response WAF | [Hướng dẫn Defender](Defender-Guide.md) |
| Endpoint HTTP và payload contract | [Tham chiếu API](API-Reference.md) |
| Quy trình vận hành | [Vận hành](Operations.md) |
| Ranh giới tin cậy và kiểm soát production | [Bảo mật](Security.md) |
| Chẩn đoán theo triệu chứng | [Khắc phục sự cố](Troubleshooting.md) |
| Workflow đóng góp mã nguồn | [Phát triển](Development.md) |

Tài liệu ngoài phạm vi chỉ cung cấp ngữ cảnh vừa đủ và dẫn về nơi chịu trách nhiệm, không chép lại bảng hay quy tắc chi tiết.
