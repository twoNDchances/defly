# Tài liệu Defly

Defly là hệ thống quản trị và thực thi tường lửa ứng dụng web (WAF), gồm [Manager](Manager-Guide.md), [Orchestrator](Orchestrator-Guide.md) và các [Defender](CoreConcepts/Defender.md). Bộ tài liệu này được sắp theo tuyến đọc từ tổng quan đến vận hành.

## Bắt đầu

1. [Tổng quan](Overview.md): Defly giải quyết vấn đề gì và các dịch vụ phối hợp ra sao.
2. [Bắt đầu nhanh](Getting-Started.md): chạy hệ thống bằng Docker Compose và tạo Defender đầu tiên.
3. [Cài đặt](Installation.md): cài đầy đủ bằng Compose hoặc chạy từng dịch vụ thủ công.
4. [Cấu hình](Configuration.md): cách tổ chức cấu hình, cơ sở dữ liệu, TLS và Docker.
5. [Biến môi trường](Environment-Variables.md): toàn bộ biến `.env`, giá trị mặc định, ràng buộc và ánh xạ giữa các dịch vụ.
6. [Kiến trúc](Architecture.md): ranh giới dịch vụ, dữ liệu và vòng đời HTTP.

## Khái niệm cốt lõi

Đọc [mục lục khái niệm](CoreConcepts/README.md) trước khi tạo chính sách. Tuyến WAF được khuyến nghị:

1. [Wordlist](CoreConcepts/Wordlist.md) và [Pattern](CoreConcepts/Pattern.md)
2. [Target](CoreConcepts/Target.md)
3. [Engine](CoreConcepts/Engine.md)
4. [Rule](CoreConcepts/Rule.md)
5. [Action](CoreConcepts/Action.md)
6. [Principle](CoreConcepts/Principle.md)
7. [Decision](CoreConcepts/Decision.md)
8. [Defender](CoreConcepts/Defender.md)

Các khái niệm quản trị gồm [User](CoreConcepts/User.md), [Group](CoreConcepts/Group.md), [Permission](CoreConcepts/Permission.md), [Key](CoreConcepts/Key.md) và [Label](CoreConcepts/Label.md).

## Sử dụng hệ thống

- [Hướng dẫn Manager](Manager-Guide.md)
- [Hướng dẫn Orchestrator](Orchestrator-Guide.md)
- [Hướng dẫn Defender](Defender-Guide.md)
- [Tham chiếu API](API-Reference.md)

## Vận hành và phát triển

- [Vận hành](Operations.md)
- [Bảo mật](Security.md)
- [Khắc phục sự cố](Troubleshooting.md)
- [Phát triển](Development.md)
- [Tham chiếu nhanh](Reference.md)

Nếu chỉ cần chạy thử, bắt đầu tại [Bắt đầu nhanh](Getting-Started.md). Nếu cần xây chính sách WAF, đọc [các khái niệm cốt lõi](CoreConcepts/README.md) theo thứ tự trước khi mở biểu mẫu trong Manager.
