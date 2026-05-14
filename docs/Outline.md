
## 1. Overview
- Defly là gì
- Use case chính
- Kiến trúc tổng quan
- Vai trò của `manager`, `orchestrator`, `defender`, `mariadb`, `worker`
- Luồng hoạt động từ tạo rule đến deploy Defender

## 2. Getting Started
- Yêu cầu hệ thống
- Cài nhanh bằng Docker Compose
- Đăng nhập lần đầu
- Tạo Defender đầu tiên
- Deploy Defender đầu tiên
- Kiểm tra proxy/WAF hoạt động

## 3. Installation
- Docker installation
- Manual installation
- Cấu hình database
- Build image Defender
- Cấu hình TLS
- Junction/symlink `.crt` và `.key`
- Các lỗi cài đặt thường gặp

## 4. Configuration
- Root `.env`
- Manager `.env`
- Orchestrator `.env`
- Defender environment variables
- Mail/Resend config
- API token config
- TLS verification config
- Ports, volumes, networks

## 5. Architecture
- System diagram
- Database ownership
- Manager -> Orchestrator flow
- Orchestrator -> Docker flow
- Manager -> Defender control API flow
- Defender proxy request/response flow
- TLS certificate sharing
- Queue worker flow

## 6. Manager Guide
- Dashboard
- Users
- Groups
- Permissions
- Keys/API access
- Labels
- Targets
- Engines
- Patterns
- Wordlists
- Actions
- Rules
- Principles
- Decisions
- Defenders
- Reports
- Timelines

## 7. Orchestrator Guide
- Orchestrator responsibilities
- Deployment lifecycle
- Follow logs
- Cancel deployment
- Docker socket/API access
- Docker volumes
- Docker networks
- Orchestrator API auth
- Failure states and recovery

## 8. Defender Guide
- Defender runtime overview
- Control API
- Proxy server
- WAF engine
- Request phases
- Rule matching
- Actions
- Decisions
- Reports
- Logs and error files
- TLS generation
- Environment variables

## 9. API Reference
- Manager API
- Orchestrator API
- Defender API
- Authentication
- Headers
- Request/response examples
- Error responses

## 10. Operations
- Start/stop/restart services
- View logs
- Backup/restore database
- Rotate credentials
- Rotate TLS files
- Upgrade images
- Scale worker
- Health checks
- Troubleshooting deployment failures

## 11. Development
- Local dev setup
- Running tests
- Code generation
- Laravel commands
- Django checks/Ruff
- Go tests
- Project conventions
- Adding a new model/resource
- Adding a new Defender rule/action

## 12. Security
- Authentication model
- Permission model
- API token usage
- Basic auth between services
- TLS trust model
- Docker socket risk
- Production hardening checklist

## 13. Troubleshooting
- Manager cannot connect to Orchestrator
- Orchestrator cannot access Docker
- Defender image not found
- TLS certificate verify failed
- Database connection failed
- Queue job stuck
- Port already in use
- Permission denied on storage

## 14. Reference
- Environment variable reference
- Docker Compose reference
- Directory structure
- Database tables overview
- Default ports
- Default credentials
- Glossary

Thứ tự viết hợp lý nhất: `Overview` -> `Getting Started` -> `Installation` -> `Configuration` -> `Architecture` -> từng guide chi tiết cho `Manager`, `Orchestrator`, `Defender`.