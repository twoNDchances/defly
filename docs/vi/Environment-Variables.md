# Biến môi trường

Trang này giải thích các biến môi trường của Defly trong bốn phạm vi:

1. `.env` tại thư mục gốc, được Docker Compose đọc trước khi tạo dịch vụ.
2. `manager/.env`, dùng khi chạy Manager trực tiếp.
3. `orchestrator/.env`, dùng khi chạy Orchestrator trực tiếp.
4. Biến của Defender, được lưu trong bản ghi Defender hoặc truyền trực tiếp khi chạy chương trình.

Không dùng chung một tên biến chỉ vì giá trị giống nhau. Docker Compose có thể đổi tên biến khi truyền vào container, ví dụ `DB_USERNAME` ở `.env` gốc trở thành `DB_USER` trong Orchestrator và `DATABASE_USER` trong Defender.

## Quy ước giá trị

- Giá trị đúng/sai dùng `true` hoặc `false`.
- Giá trị `null` của Laravel phải viết đúng là `null`, không đặt trong một chuỗi có ý nghĩa khác.
- Danh sách IP, máy chủ hoặc mạng được phân cách bằng dấu phẩy và không nên có khoảng trắng thừa.
- Đường dẫn API là đường dẫn tương đối, không bắt đầu hoặc kết thúc bằng `/`, trừ khi phần mô tả nói khác.
- Mật khẩu, khóa ứng dụng và khóa API trong các tệp mẫu chỉ là giá trị minh họa. Phải thay chúng trước khi dùng trong môi trường thật.
- Sau khi đổi `.env` của Laravel, cần xóa hoặc tạo lại bộ nhớ đệm cấu hình. Với Compose, chạy `docker compose config` để xem giá trị cuối cùng trước khi khởi động lại dịch vụ.

## Những biến cần cấu hình lại

Sau khi sao chép `.env.example` thành `.env`, không nên giữ nguyên toàn bộ giá trị mẫu. Danh sách dưới đây giúp xác định biến nào cần sửa trước, thay vì phải đọc từng bảng tham chiếu.

### Bắt buộc đổi trước khi dùng thật

| Biến | Lý do cần đổi | Giá trị nên dùng |
| --- | --- | --- |
| `MARIADB_ROOT_PASSWORD` | Giá trị mẫu bảo vệ tài khoản quản trị MariaDB. | Mật khẩu mạnh, riêng biệt và lưu trong hệ thống quản lý bí mật. |
| `DB_PASSWORD` | Manager, Orchestrator và Defender cùng dùng mật khẩu này để truy cập dữ liệu. | Mật khẩu ứng dụng mạnh, khác mật khẩu `root`. |
| `ORCHESTRATOR_PASSWORD` | Bảo vệ API có quyền điều khiển Docker. | Mật khẩu ngẫu nhiên mạnh; phải khớp `SERVER_PASSWORD` phía Orchestrator. |
| `APP_KEY` | Dùng để mã hóa cookie, phiên và dữ liệu Laravel. Khóa tạm trong container không bền vững. | Kết quả của `php artisan key:generate --show`, giữ ổn định sau khi đưa hệ thống vào sử dụng. |
| `USER_EMAIL` | Tài khoản quản trị khởi tạo không nên dùng địa chỉ mẫu. | Email thật của quản trị viên ban đầu. |
| `USER_PASSWORD` | `random` phù hợp để khởi tạo cục bộ nhưng cần được lấy và lưu an toàn; mật khẩu cố định mẫu tuyệt đối không dùng thật. | Mật khẩu mạnh hoặc giữ `random` rồi lấy một lần từ `credentials.txt`. |
| `APP_URL` | Ảnh hưởng liên kết được sinh, cookie và tên máy chủ. | URL HTTPS thật của Manager, ví dụ `https://manager.example.com`. |

Sau khi đổi thông tin cơ sở dữ liệu hoặc Orchestrator, phải cập nhật đồng thời mọi phía theo [bảng ánh xạ](#ánh-xạ-giữa-các-dịch-vụ).

### Cần đổi theo hạ tầng triển khai

| Biến | Khi nào cần đổi |
| --- | --- |
| `COMPOSE_PROJECT_NAME` | Đổi trước lần khởi động đầu tiên nếu muốn tên dự án khác `defly`; không nên đổi sau đó vì sẽ tạo bộ mạng/ổ dữ liệu mới. |
| `MANAGER_IMAGE`, `ORCHESTRATOR_IMAGE`, `SERVER_DEFENDER_IMAGE` | Khi dùng registry, namespace hoặc thẻ phiên bản riêng. `SERVER_DEFENDER_IMAGE` phải tồn tại trên Docker host của Orchestrator. |
| `MANAGER_HTTP_PORT`, `MANAGER_HTTPS_PORT` | Khi cổng `80`/`443` đã được dùng hoặc Manager nằm sau proxy ngược. |
| `MANAGER_TLS_COMMON_NAME` | Khi chứng chỉ tự ký cần khớp tên miền/IP khác `localhost`. Với chứng chỉ do hệ thống bên ngoài quản lý, cấu hình nơi kết thúc TLS tương ứng. |
| `ORCHESTRATOR_ALLOWED_HOSTS` | Bổ sung đúng tên miền/IP dùng để gọi Orchestrator; không mở rộng thành `*` trong môi trường thật nếu không cần thiết. |
| `ORCHESTRATOR_ALLOWED_CLIENTS` | Phải chứa nguồn thực sự gọi Orchestrator. Với Compose mặc định cần `worker`; khi chạy thủ công có thể là IP/tên máy khác. |
| `TIMEZONE`, `LANGUAGE_CODE` | Khi hệ thống dùng múi giờ hoặc ngôn ngữ khác. Giữ múi giờ đồng nhất giúp đối chiếu Timeline, Report và nhật ký. |
| `SERVER_DEFENDER_TLS_VOLUME` | Chỉ đổi khi đã chủ động đặt tên ổ TLS khác và cập nhật cả Compose lẫn Orchestrator. |
| `WORKER_TIMEOUT`, `WORKER_TRIES`, `WORKER_MAX_TIME` | Khi tải image hoặc triển khai Defender thường lâu hơn giới hạn mặc định, hoặc cần chính sách thử lại khác. |

### Khi sử dụng Resend

Compose mặc định đặt `MAIL_MAILER=resend`. Nếu muốn Manager gửi thư xác minh hoặc thông báo qua Resend, cần cấu hình:

| Biến | Yêu cầu |
| --- | --- |
| `MAIL_MAILER` | Giữ `resend`. |
| `RESEND_API_KEY` | Bắt buộc; dùng khóa API thật từ Resend và không đưa vào kho mã nguồn. |
| `MAIL_FROM_ADDRESS` | Địa chỉ thuộc miền đã xác minh trong Resend. `onboarding@resend.dev` chỉ phù hợp để thử nghiệm theo giới hạn của Resend. |
| `MAIL_FROM_NAME` | Tên người gửi hiển thị cho người nhận. |

`RESEND_DOMAIN`, `RESEND_PATH`, `RESEND_WEBHOOK_SECRET` và `RESEND_WEBHOOK_TOLERANCE` đang có trong tệp mẫu nhưng mã ứng dụng hiện chưa đọc trực tiếp. Không cần đặt chúng chỉ để gửi thư. Nếu chưa muốn gửi thư thật, có thể dùng `MAIL_MAILER=log` để nội dung thư đi vào nhật ký thay vì gọi Resend.

### Khi tạo Defender

Mỗi bản ghi Defender cần được xem lại ít nhất các biến sau trong Manager:

| Biến | Yêu cầu |
| --- | --- |
| `PROXY_BACKEND_URL` | Bắt buộc trỏ tới máy chủ phía sau mà **container Defender** truy cập được; không dùng `localhost` nếu máy chủ phía sau nằm ở container/máy khác. |
| `DATABASE_HOST`, `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASS` | Phải trỏ cùng cơ sở dữ liệu với Manager. Manager tự điền theo kết nối hiện tại, nhưng vẫn cần kiểm tra khi chạy ngoài Compose. |
| `SERVER_SECURITY_USERNAME`, `SERVER_SECURITY_PASSWORD` | Đổi thông tin Basic Auth mẫu và bảo đảm phía Manager dùng đúng giá trị khi gọi API điều khiển. |
| `SERVER_SECURITY_MANAGER` | Với Compose mặc định là `worker`; khi chạy thủ công phải là tên/IP nguồn thực sự gọi Defender. |
| `PROXY_TRUSTED_ENABLE`, `PROXY_TRUSTED_LIST` | Chỉ bật khi Defender nằm sau proxy tin cậy và danh sách IP/CIDR đã được giới hạn đúng. |
| `PROXY_SEVERITY_*`, `PROXY_VIOLATION_LEVEL`, `PROXY_VIOLATION_SCORE` | Giữ mặc định khi bắt đầu; chỉ đổi sau khi đã thiết kế cách cộng điểm và ngưỡng Decision. |

`DEFENDER_NAME` và `PROXY_PORT` được Orchestrator ghi đè từ bản ghi Defender khi triển khai, vì vậy không cần sửa thủ công trong đối tượng biến môi trường.

### Không nên bật để xử lý nhanh lỗi TLS

`ORCHESTRATOR_TLS_SKIP_VERIFY=true` và `DEFENDER_SERVER_TLS_SKIP_VERIFY=true` chỉ nên dùng tạm trong môi trường phát triển để xác định lỗi chứng chỉ. Môi trường thật nên giữ cả hai là `false`, cung cấp đúng chứng chỉ và sửa tên máy chủ/đường dẫn tin cậy thay vì bỏ xác minh.

## `.env` gốc của Docker Compose

Tạo `.env` từ `.env.example` tại thư mục gốc. Các giá trị trong phần này chỉ được Docker Compose nội suy; container nhận tên biến được khai báo trong `docker-compose.yml`.

### Dự án, image và MariaDB

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `COMPOSE_PROJECT_NAME` | `defly` | Tiền tố của container, mạng, ổ dữ liệu và nhãn Compose. Không nên đổi sau lần khởi tạo đầu tiên vì Compose sẽ xem đó là dự án khác. |
| `MANAGER_IMAGE` | `defly-manager:latest` | Image dùng chung cho dịch vụ `manager` và `worker`. |
| `ORCHESTRATOR_IMAGE` | `defly-orchestrator:latest` | Image của Orchestrator. |
| `SERVER_DEFENDER_IMAGE` | `defly-defender:latest` | Image mà Orchestrator dùng để tạo Defender động; phải tồn tại trên Docker host. |
| `MARIADB_VERSION` | `11.4` | Thẻ phiên bản của image MariaDB. |
| `MARIADB_ROOT_PASSWORD` | `defly_root_secret` | Mật khẩu tài khoản `root` của MariaDB. Chỉ dùng cho quản trị cơ sở dữ liệu. |
| `DB_DATABASE` | `defly_manager` | Tên cơ sở dữ liệu dùng chung. |
| `DB_USERNAME` | `defly` | Người dùng ứng dụng của MariaDB. |
| `DB_PASSWORD` | `defly_secret` | Mật khẩu người dùng ứng dụng. |

Trong Compose, máy chủ cơ sở dữ liệu luôn là `mariadb` và cổng nội bộ luôn là `3306`.

### Manager và Worker

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `APP_NAME` | `Defly Manager` | Tên ứng dụng Laravel và tên mặc định của thư gửi đi. |
| `APP_ENV` | `production` | Tên môi trường Laravel. |
| `APP_KEY` | rỗng | Khóa mã hóa của Laravel. Môi trường thật phải dùng một khóa cố định được tạo bằng `php artisan key:generate --show`. |
| `APP_DEBUG` | `false` | Hiển thị thông tin gỡ lỗi. Không bật trong môi trường thật. |
| `APP_URL` | `https://localhost` | URL gốc dùng để sinh liên kết và suy ra tên chứng chỉ Manager khi cần. |
| `APP_LOCALE` | `vi` | Ngôn ngữ mặc định của Manager. |
| `APP_FALLBACK_LOCALE` | `en` | Ngôn ngữ dự phòng khi thiếu bản dịch. |
| `APP_FAKER_LOCALE` | `en_US` | Ngôn ngữ của dữ liệu giả dùng khi phát triển hoặc kiểm thử. |
| `MANAGER_HTTP_PORT` | `80` | Cổng HTTP công bố trên Docker host. |
| `MANAGER_HTTPS_PORT` | `443` | Cổng HTTPS công bố trên Docker host. |
| `MANAGER_TLS_COMMON_NAME` | `localhost` | Tên chung và SAN của chứng chỉ tự ký do container Manager tạo. |
| `MANAGER_RUN_MIGRATIONS` | `true` | Chạy migration khi container Manager khởi động. |
| `MANAGER_RUN_SEEDERS` | `true` | Chạy seeder khi container Manager khởi động. |
| `MANAGER_RUN_OPTIMIZE` | `true` | Chạy `php artisan optimize` khi khởi động. |
| `GENERATE_APP_KEY` | `true` | Tạo `APP_KEY` tạm cho tiến trình nếu `APP_KEY` đang rỗng. Khóa tạm không thích hợp cho môi trường thật. |
| `WORKER_TRIES` | `3` | Số lần Worker thử lại một tác vụ thất bại. |
| `WORKER_TIMEOUT` | `90` | Số giây tối đa cho mỗi tác vụ Worker. |
| `WORKER_MAX_TIME` | `3600` | Tổng số giây Worker chạy trước khi tự khởi động lại. |

### Nhật ký và trạng thái Laravel

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `LOG_CHANNEL` | `stack` | Kênh nhật ký chính. |
| `LOG_STACK` | `single` | Danh sách kênh nằm trong `stack`, phân cách bằng dấu phẩy. |
| `LOG_DEPRECATIONS_CHANNEL` | `null` | Kênh nhận cảnh báo tính năng sắp bị loại bỏ. |
| `LOG_LEVEL` | `debug` | Mức nhật ký thấp nhất được ghi. |
| `SESSION_DRIVER` | `database` | Nơi lưu phiên đăng nhập. Compose đã có bảng cơ sở dữ liệu tương ứng. |
| `SESSION_LIFETIME` | `120` | Thời gian tồn tại của phiên, tính bằng phút. |
| `SESSION_ENCRYPT` | `false` | Mã hóa toàn bộ dữ liệu phiên trước khi lưu. |
| `SESSION_DOMAIN` | `null` | Miền của cookie phiên; để `null` để dùng máy chủ hiện tại. |
| `BROADCAST_CONNECTION` | `log` | Kết nối phát sự kiện. |
| `FILESYSTEM_DISK` | `local` | Ổ lưu trữ mặc định của Laravel. |
| `QUEUE_CONNECTION` | `database` | Kết nối hàng đợi; Worker Compose đọc các tác vụ từ cơ sở dữ liệu. |
| `CACHE_STORE` | `database` | Nơi lưu bộ nhớ đệm. |

### Thư điện tử và Resend

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `MAIL_MAILER` | `resend` | Bộ gửi thư mặc định. |
| `MAIL_SCHEME` | `null` | Giao thức cho kết nối SMTP khi dùng bộ gửi SMTP. |
| `MAIL_HOST` | `127.0.0.1` | Máy chủ SMTP. Không được dùng khi `MAIL_MAILER=resend`. |
| `MAIL_PORT` | `2525` | Cổng SMTP. |
| `MAIL_USERNAME` | `null` | Tên đăng nhập SMTP. |
| `MAIL_PASSWORD` | `null` | Mật khẩu SMTP. |
| `MAIL_FROM_ADDRESS` | `onboarding@resend.dev` | Địa chỉ người gửi mặc định. Môi trường thật phải dùng miền đã được xác minh. |
| `MAIL_FROM_NAME` | `Defly Manager` | Tên người gửi mặc định. |
| `RESEND_API_KEY` | rỗng | Khóa API Resend. Bắt buộc khi thực sự gửi thư qua Resend. |
| `RESEND_DOMAIN` | `null` | Miền Resend dự kiến dùng cho thư. Biến đang có trong tệp mẫu nhưng mã ứng dụng hiện chưa đọc trực tiếp. |
| `RESEND_PATH` | `resend` | Đoạn đường dẫn dành cho tích hợp Resend. Mã ứng dụng hiện chưa đọc trực tiếp. |
| `RESEND_WEBHOOK_SECRET` | rỗng | Bí mật xác minh webhook Resend. Mã ứng dụng hiện chưa đọc trực tiếp. |
| `RESEND_WEBHOOK_TOLERANCE` | `300` | Khoảng sai lệch thời gian webhook cho phép, tính bằng giây. Mã ứng dụng hiện chưa đọc trực tiếp. |

### Người dùng khởi tạo và giao diện

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `USER_NAME` | `root` | Tên User được seeder khởi tạo. |
| `USER_EMAIL` | `root@defly.2ndproject.site` | Email đăng nhập của User khởi tạo. |
| `USER_PASSWORD` | `random` | Mật khẩu User khởi tạo. Phải thay bằng bí mật mạnh và ổn định. |
| `TOKEN_LOCATION` | `header` | Vị trí Manager API đọc Key: `header` hoặc `body`; giá trị khác trở về `header`. |
| `TOKEN_KEY_NAME` | `X-Token-Key` | Tên tiêu đề HTTP hoặc trường nội dung chứa Key. |
| `USER_AGENT` | `Defly/Manager` | Giá trị `User-Agent` khi Manager gọi dịch vụ khác. |
| `TIMEZONE` | `Asia/Ho_Chi_Minh` | Múi giờ của Manager; Compose cũng truyền giá trị này thành `TIME_ZONE` cho Orchestrator. |
| `API_PREFIX` | `v1` | Tiền tố đường dẫn Manager API; Manager chuẩn hóa về dạng slug viết thường. |
| `GUI_PREFIX` | `defly-manager` | Tiền tố đường dẫn giao diện Filament. |
| `THEME_COLOR` | `violet` | Màu Filament. Giá trị không tồn tại trong bảng màu Filament trở về `violet`. |

### Orchestrator trong Compose

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `ORCHESTRATOR_SECRET_KEY_FILE` | `storage/secret/key.txt` | Đường dẫn tệp chứa Django `SECRET_KEY` bên trong container. Entrypoint tạo tệp nếu chưa tồn tại. |
| `ORCHESTRATOR_ALLOWED_HOSTS` | `127.0.0.1,localhost,manager,orchestrator` | Danh sách giá trị `Host` mà Django chấp nhận. |
| `ORCHESTRATOR_ALLOWED_CLIENTS` | `manager,worker` | Tên hoặc địa chỉ nguồn được phép gọi Orchestrator. `worker` là bên thực hiện tác vụ triển khai trong Compose. |
| `ORCHESTRATOR_PATH_PREFIX` | `api/v1` | Tiền tố API; được truyền thành `SERVER_PATH_PREFIX`. |
| `ORCHESTRATOR_PATH_DEPLOYMENT` | `deployments` | Đường dẫn tài nguyên triển khai; được truyền thành `SERVER_PATH_DEPLOYMENT`. |
| `ORCHESTRATOR_METHOD_DEPLOY` | `post` | Phương thức triển khai Defender. |
| `ORCHESTRATOR_METHOD_FOLLOW` | `get` | Phương thức theo dõi nhật ký Defender. |
| `ORCHESTRATOR_METHOD_CANCEL` | `delete` | Phương thức hủy Defender. Ba phương thức của cùng đường dẫn phải khác nhau. |
| `ORCHESTRATOR_USERNAME` | `defly-orchestrator` | Tên đăng nhập Basic Auth dùng giữa Manager/Worker và Orchestrator. |
| `ORCHESTRATOR_PASSWORD` | `P@55w0rd` | Mật khẩu Basic Auth. Phải thay ở môi trường thật. |
| `ORCHESTRATOR_TLS_SKIP_VERIFY` | `false` | Khi `true`, Manager bỏ xác minh chứng chỉ Orchestrator. Chỉ dùng tạm khi phát triển. |
| `ORCHESTRATOR_EMAIL_HEADER_KEY` | `X-Executor` | Tiêu đề HTTP mang email User thực hiện thao tác. Hai phía phải dùng cùng tên. |
| `LANGUAGE_CODE` | `vi-vn` | Ngôn ngữ Django. Compose hỗ trợ biến này dù `.env.example` gốc hiện không khai báo sẵn. |

### Defender được triển khai

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `SERVER_DEFENDER_TLS_VOLUME` | `defender_tls` | Khóa ổ dữ liệu chứa chứng chỉ Defender. Tên thật là `${COMPOSE_PROJECT_NAME}_${SERVER_DEFENDER_TLS_VOLUME}`. |
| `DEFENDER_SERVER_TLS_SKIP_VERIFY` | `false` | Khi `true`, Manager bỏ xác minh chứng chỉ của Defender. |

Các biến chi tiết của từng Defender không lấy trực tiếp từ `.env` gốc. Chúng được cấu hình trong bản ghi Defender ở Manager, rồi Orchestrator truyền vào container khi triển khai.

## `manager/.env`

Khi chạy Manager thủ công, sao chép `manager/.env.example` thành `manager/.env`. Các biến trùng tên với phần Compose giữ nguyên ý nghĩa. Khác biệt quan trọng là `DB_HOST`, `DB_PORT` và URL dịch vụ phải trỏ tới địa chỉ mà tiến trình Manager thực sự truy cập được.

### Ứng dụng và cơ sở dữ liệu

| Biến | Mặc định trong mẫu | Ý nghĩa |
| --- | --- | --- |
| `APP_MAINTENANCE_DRIVER` | `file` | Nơi Laravel lưu trạng thái bảo trì. |
| `APP_MAINTENANCE_STORE` | `database` | Kho dùng khi bộ điều khiển bảo trì yêu cầu kho dùng chung. Dòng này đang được chú thích trong tệp mẫu. |
| `PHP_CLI_SERVER_WORKERS` | `4` | Số tiến trình phục vụ của máy chủ PHP CLI khi phát triển; đang được chú thích trong mẫu. |
| `BCRYPT_ROUNDS` | `12` | Số vòng băm bcrypt dự kiến. Cấu hình Manager hiện không có `config/hashing.php`, nên biến này chưa được mã ứng dụng đọc trực tiếp. |
| `DB_CONNECTION` | `mysql` | Trình điều khiển cơ sở dữ liệu Laravel. |
| `DB_HOST` | `127.0.0.1` | Máy chủ cơ sở dữ liệu. |
| `DB_PORT` | `3306` | Cổng cơ sở dữ liệu. |
| `DB_DATABASE` | `defly_manager` | Tên cơ sở dữ liệu. |
| `DB_USERNAME` | `root` | Người dùng cơ sở dữ liệu. |
| `DB_PASSWORD` | rỗng | Mật khẩu cơ sở dữ liệu. |

### Phiên, bộ nhớ đệm và tệp

| Biến | Mặc định trong mẫu | Ý nghĩa |
| --- | --- | --- |
| `SESSION_PATH` | `/` | Phạm vi đường dẫn của cookie phiên. |
| `CACHE_PREFIX` | sinh từ `APP_NAME` | Tiền tố khóa bộ nhớ đệm; đang được chú thích trong mẫu. |
| `MEMCACHED_HOST` | `127.0.0.1` | Máy chủ Memcached khi `CACHE_STORE=memcached`. |
| `AWS_ACCESS_KEY_ID` | rỗng | Khóa truy cập AWS cho S3, SQS hoặc DynamoDB. |
| `AWS_SECRET_ACCESS_KEY` | rỗng | Khóa bí mật AWS. |
| `AWS_DEFAULT_REGION` | `us-east-1` | Vùng AWS. |
| `AWS_BUCKET` | rỗng | Tên bucket S3. |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` | Dùng URL kiểu đường dẫn cho S3 hoặc dịch vụ tương thích S3. |
| `VITE_APP_NAME` | `${APP_NAME}` | Tên ứng dụng dự kiến đưa vào mã giao diện khi Vite tham chiếu biến này. Mã giao diện hiện chưa đọc trực tiếp. |

### Kết nối Orchestrator và Defender

| Biến | Mặc định trong mẫu | Ý nghĩa |
| --- | --- | --- |
| `ORCHESTRATOR_BASE_URL` | `https://orchestrator:8000` | URL đầy đủ của Orchestrator. Khi chạy ngoài Compose thường phải đổi thành `https://127.0.0.1:8000` hoặc máy chủ tương ứng. |
| `ORCHESTRATOR_TLS_CERT_FILE` | `storage/tls/orchestrator/orchestrator.crt` | Tệp chứng chỉ CA/chứng chỉ tự ký dùng để xác minh Orchestrator khi không bỏ xác minh TLS. |
| `DEFENDER_SERVER_TLS_DIRECTORY` | `storage/tls/defenders` | Thư mục chứng chỉ Defender; Manager tìm `<defender-name>.crt` trong thư mục này. |

Các biến `ORCHESTRATOR_PATH_*`, `ORCHESTRATOR_METHOD_*`, `ORCHESTRATOR_USERNAME`, `ORCHESTRATOR_PASSWORD` và `ORCHESTRATOR_EMAIL_HEADER_KEY` phải khớp với nhóm `SERVER_*` của Orchestrator. `DEFENDER_SERVER_TLS_SKIP_VERIFY` chỉ điều khiển phía Manager, không bật hoặc tắt HTTPS trong Defender.

### Biến khởi động container Manager

Các biến sau được `manager/entrypoint.sh` đọc. Compose dùng tên `MANAGER_RUN_*` bên ngoài rồi ánh xạ sang `RUN_*` bên trong container.

| Biến nội bộ | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `RUN_MIGRATIONS` | `true` | Chạy migration trước khi Apache khởi động. Worker Compose ghi đè thành `false`. |
| `RUN_SEEDERS` | `true` | Chạy seeder. Worker Compose ghi đè thành `false`. |
| `RUN_OPTIMIZE` | `true` | Tạo bộ nhớ đệm tối ưu của Laravel. Worker Compose ghi đè thành `false`. |
| `FIX_PERMISSIONS` | `true` | Sửa người sở hữu của `storage` và `bootstrap/cache` thành `www-data`. |
| `GENERATE_APP_KEY` | `true` | Tạo khóa khi `APP_KEY` rỗng. |
| `TLS_COMMON_NAME` | lấy từ `SERVER_NAME`, rồi `APP_URL`, cuối cùng `localhost` | Tên dùng để sinh chứng chỉ Apache tự ký. |
| `SERVER_NAME` | rỗng | Tên máy chủ Apache; nếu có thì cũng là giá trị dự phòng cho `TLS_COMMON_NAME`. |
| `TLS_DAYS` | `3650` | Số ngày hiệu lực của chứng chỉ Apache tự ký. |
| `APACHE_SERVER_NAME` | tự sinh | Biến đầu ra do entrypoint tạo cho cấu hình Apache; thông thường không cần đặt thủ công. |

### Biến Laravel nâng cao

Những biến dưới đây được các tệp `manager/config/*.php` hỗ trợ nhưng không nằm đầy đủ trong `manager/.env.example`. Chỉ đặt chúng khi thay trình điều khiển mặc định.

| Nhóm | Biến | Ý nghĩa |
| --- | --- | --- |
| Khóa ứng dụng | `APP_PREVIOUS_KEYS` | Danh sách khóa cũ, phân cách bằng dấu phẩy, dùng để giải mã dữ liệu đã mã hóa bằng khóa trước. |
| Xác thực | `AUTH_GUARD`, `AUTH_PASSWORD_BROKER`, `AUTH_MODEL`, `AUTH_PASSWORD_RESET_TOKEN_TABLE`, `AUTH_PASSWORD_TIMEOUT` | Chọn guard, broker, lớp User, bảng token đặt lại mật khẩu và thời gian xác nhận lại mật khẩu. |
| Cơ sở dữ liệu | `DB_URL`, `DB_SOCKET`, `DB_CHARSET`, `DB_COLLATION`, `DB_FOREIGN_KEYS`, `DB_SSLMODE`, `MYSQL_ATTR_SSL_CA` | URL kết nối, socket, bảng mã, đối chiếu, khóa ngoại SQLite, chế độ SSL PostgreSQL và chứng chỉ CA MySQL. |
| Phiên | `SESSION_EXPIRE_ON_CLOSE`, `SESSION_CONNECTION`, `SESSION_TABLE`, `SESSION_STORE`, `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE`, `SESSION_PARTITIONED_COOKIE` | Điều khiển tuổi thọ, kết nối, bảng, kho và thuộc tính bảo mật cookie phiên. |
| Bộ nhớ đệm cơ sở dữ liệu | `DB_CACHE_CONNECTION`, `DB_CACHE_TABLE`, `DB_CACHE_LOCK_CONNECTION`, `DB_CACHE_LOCK_TABLE` | Kết nối và bảng cho dữ liệu/khóa của bộ nhớ đệm. |
| Memcached | `MEMCACHED_PERSISTENT_ID`, `MEMCACHED_USERNAME`, `MEMCACHED_PASSWORD`, `MEMCACHED_PORT` | Kết nối Memcached nâng cao. |
| DynamoDB | `DYNAMODB_CACHE_TABLE`, `DYNAMODB_ENDPOINT` | Bảng và URL DynamoDB khi dùng bộ nhớ đệm DynamoDB. |
| Redis | `REDIS_CLIENT`, `REDIS_CLUSTER`, `REDIS_PREFIX`, `REDIS_PERSISTENT`, `REDIS_URL`, `REDIS_HOST`, `REDIS_USERNAME`, `REDIS_PASSWORD`, `REDIS_PORT`, `REDIS_DB`, `REDIS_CACHE_DB`, `REDIS_MAX_RETRIES`, `REDIS_BACKOFF_ALGORITHM`, `REDIS_BACKOFF_BASE`, `REDIS_BACKOFF_CAP`, `REDIS_CACHE_CONNECTION`, `REDIS_CACHE_LOCK_CONNECTION` | Trình khách, cụm, tiền tố, kết nối, cơ sở dữ liệu và chiến lược thử lại Redis. |
| Hàng đợi cơ sở dữ liệu | `DB_QUEUE_CONNECTION`, `DB_QUEUE_TABLE`, `DB_QUEUE`, `DB_QUEUE_RETRY_AFTER` | Kết nối, bảng, tên hàng đợi và thời gian cho phép thử lại. |
| Beanstalkd | `BEANSTALKD_QUEUE_HOST`, `BEANSTALKD_QUEUE`, `BEANSTALKD_QUEUE_RETRY_AFTER` | Kết nối hàng đợi Beanstalkd. |
| SQS | `SQS_PREFIX`, `SQS_QUEUE`, `SQS_SUFFIX` | URL, tên và hậu tố hàng đợi SQS. |
| Redis queue | `REDIS_QUEUE_CONNECTION`, `REDIS_QUEUE`, `REDIS_QUEUE_RETRY_AFTER` | Kết nối, tên và thời gian thử lại cho hàng đợi Redis. |
| Tác vụ thất bại | `QUEUE_FAILED_DRIVER` | Trình lưu tác vụ thất bại; mặc định Laravel là `database-uuids`. |
| Thư điện tử | `MAIL_URL`, `MAIL_EHLO_DOMAIN`, `MAIL_SENDMAIL_PATH`, `MAIL_LOG_CHANNEL` | URL SMTP, miền EHLO, lệnh sendmail và kênh nhật ký thư. |
| Dịch vụ thư | `POSTMARK_API_KEY` | Khóa Postmark khi dùng bộ gửi Postmark. |
| Nhật ký | `LOG_DEPRECATIONS_TRACE`, `LOG_DAILY_DAYS`, `LOG_SLACK_WEBHOOK_URL`, `LOG_SLACK_USERNAME`, `LOG_SLACK_EMOJI`, `LOG_PAPERTRAIL_HANDLER`, `PAPERTRAIL_URL`, `PAPERTRAIL_PORT`, `LOG_STDERR_FORMATTER`, `LOG_SYSLOG_FACILITY` | Vết cảnh báo, số ngày giữ, Slack, Papertrail, bộ định dạng stderr và cơ sở syslog. |
| Slack | `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL` | Token bot và kênh mặc định của tích hợp Slack. |
| S3 | `AWS_URL`, `AWS_ENDPOINT` | URL công khai và điểm cuối S3 hoặc dịch vụ tương thích. |
| Livewire | `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` | Ổ lưu tạm cho tệp tải lên của Livewire. |

Các biến `DB_ENCRYPT`, `DB_TRUST_SERVER_CERTIFICATE`, `POSTMARK_MESSAGE_STREAM_ID`, `VITE_PUSHER_APP_CLUSTER`, `VITE_PUSHER_APP_KEY`, `VITE_PUSHER_HOST`, `VITE_PUSHER_PORT` và `VITE_PUSHER_SCHEME` chỉ xuất hiện trong dòng cấu hình đang được chú thích; chúng chưa có hiệu lực cho đến khi mã cấu hình tương ứng được bật lại.

## `orchestrator/.env`

Orchestrator đọc tệp này qua `django-environ`. Giá trị trong bảng là mặc định của mã nguồn, không phải lúc nào cũng giống giá trị minh họa trong tệp mẫu.

### Django và cơ sở dữ liệu

| Biến | Mặc định mã nguồn | Ý nghĩa |
| --- | --- | --- |
| `SECRET_KEY_FILE` | `storage/secret/key.txt` | Tệp chứa Django `SECRET_KEY`. Tệp phải tồn tại và không được rỗng; entrypoint container có thể tạo tệp. |
| `ALLOWED_HOSTS` | `*` | Danh sách máy chủ Django chấp nhận. Tệp mẫu dùng danh sách giới hạn hơn. |
| `DB_HOST` | `localhost` | Máy chủ MariaDB/MySQL. |
| `DB_PORT` | `3306` | Cổng cơ sở dữ liệu. |
| `DB_USER` | `root` | Người dùng cơ sở dữ liệu. |
| `DB_PASS` | rỗng | Mật khẩu cơ sở dữ liệu. |
| `DB_NAME` | `defly` | Tên cơ sở dữ liệu. Tệp mẫu và Compose dùng `defly_manager`. |
| `LANGUAGE_CODE` | `vi-vn` | Ngôn ngữ Django. |
| `TIME_ZONE` | `Asia/Ho_Chi_Minh` | Múi giờ Django. |
| `USE_I18N` | `true` | Bật hệ thống dịch của Django. |
| `USE_TZ` | `false` | Bật datetime nhận biết múi giờ của Django. |

### API và Docker

| Biến | Mặc định mã nguồn | Ý nghĩa |
| --- | --- | --- |
| `SERVER_MANAGER` | `manager` | Danh sách nguồn được middleware cho phép. Compose đặt `manager,worker`. Không để rỗng. |
| `SERVER_USERNAME` | `defly-orchestrator` | Tên Basic Auth; không được rỗng hoặc chứa `:`. |
| `SERVER_PASSWORD` | `P@55w0rd` | Mật khẩu Basic Auth; không được rỗng. |
| `SERVER_EMAIL_HEADER_KEY` | `X-Executor` | Tên tiêu đề HTTP mang email người thực thi. |
| `SERVER_PATH_PREFIX` | `api/v1` | Tiền tố API, không có `/` ở đầu/cuối và không có đoạn rỗng. |
| `SERVER_PATH_DEPLOYMENT` | `deployments` | Đường dẫn triển khai với cùng quy tắc như tiền tố. |
| `SERVER_METHOD_DEPLOY` | `post` | Phương thức triển khai. |
| `SERVER_METHOD_FOLLOW` | `get` | Phương thức lấy nhật ký. |
| `SERVER_METHOD_CANCEL` | `delete` | Phương thức hủy. Ba phương thức phải thuộc `get`, `post`, `put`, `patch`, `delete` và không được trùng nhau. |
| `SERVER_DEFENDER_IMAGE` | `defly-defender:latest` | Image Defender đã có trên Docker host. |
| `SERVER_DEFENDER_TLS_VOLUME` | `defender_tls` | Khóa ổ dữ liệu TLS dùng khi tạo Defender. |
| `SERVER_DOCKER_BASE_URL` | `tcp://localhost:2375` | Điểm kết nối Docker chỉ dành cho cấu hình phát triển. `configs.production` cố định `unix:///var/run/docker.sock`. |

### Biến entrypoint Orchestrator

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `ORCHESTRATOR_TLS_CERT_FILE` | `storage/tls/orchestrator.crt` | Đường dẫn chứng chỉ HTTPS của Uvicorn. Entrypoint tạo chứng chỉ nếu thiếu. |
| `ORCHESTRATOR_TLS_KEY_FILE` | `storage/tls/orchestrator.key` | Đường dẫn khóa riêng HTTPS. |
| `DJANGO_SETTINGS_MODULE` | `configs.production` trong image | Chọn bộ cấu hình Django. Khi chạy `manage.py` ngoài image, mặc định của dự án là `configs.development`. |

## Biến của Defender

Defender kiểm tra kiểu, giới hạn và điều kiện của biến khi khởi động. Khi triển khai từ Manager, `environment_variables` của bản ghi được gửi tới Orchestrator; Orchestrator luôn ghi đè `DEFENDER_NAME` và `PROXY_PORT` bằng tên/cổng của bản ghi.

### Dùng chung

| Biến | Mặc định | Điều kiện và ý nghĩa |
| --- | --- | --- |
| `DEFENDER_NAME` | `defender` | Tên an toàn cho tệp, dùng để tạo chứng chỉ và tên tài nguyên. Orchestrator tự đặt khi triển khai. |
| `ABOUT_BANNER_ENABLE` | `true` | Hiển thị banner lúc Defender khởi động. |
| `ERROR_FILE_ENABLE` | `false` | Ghi lỗi Defender vào tệp. |
| `ERROR_DIRECTORY_PATH` | `storage/errors` | Thư mục lỗi; bắt buộc và phải là đường dẫn thư mục hợp lệ khi `ERROR_FILE_ENABLE=true`. |
| `WORDLIST_ROOT` | `storage/wordlists` | Thư mục gốc chứa tệp Wordlist được gắn vào Defender. |

### Cơ sở dữ liệu và Doctor

| Biến | Mặc định | Điều kiện và ý nghĩa |
| --- | --- | --- |
| `DATABASE_HOST` | `127.0.0.1` | Máy chủ cơ sở dữ liệu Report/chính sách; không chứa khoảng trắng. Manager thường điền `mariadb` khi dùng Compose. |
| `DATABASE_PORT` | `3306` | Cổng cơ sở dữ liệu hợp lệ từ `1` đến `65535`. |
| `DATABASE_NAME` | `defly_manager` | Tên cơ sở dữ liệu; không chứa khoảng trắng. |
| `DATABASE_USER` | `root` | Người dùng cơ sở dữ liệu; không chứa khoảng trắng. |
| `DATABASE_PASS` | rỗng | Mật khẩu cơ sở dữ liệu. |
| `DOCTOR_INTERVAL_UNIT` | `minute` | Đơn vị kiểm tra sức khỏe: `second`, `minute` hoặc `hour`. |
| `DOCTOR_INTERVAL_COUNT` | `1` | Số đơn vị giữa hai lần kiểm tra, từ `1` đến `1000000`; tối thiểu `30` nếu đơn vị là `second`. |

### Máy chủ điều khiển Defender

| Biến | Mặc định mã nguồn | Điều kiện và ý nghĩa |
| --- | --- | --- |
| `SERVER_HTTPS_ENABLE` | `true` | Bật HTTPS cho API điều khiển. |
| `SERVER_PORT` | `9947` | Cổng API điều khiển, từ `1` đến `65535`. |
| `SERVER_CONTROLLER_PATH_PREFIX` | `api/v1` | Tiền tố API tương đối. |
| `SERVER_CONTROLLER_PATH_PRINCIPLES` | `principles` | Đường dẫn Principle; phải khác đường dẫn Decision. |
| `SERVER_CONTROLLER_METHOD_APPLY` | `put` | Phương thức áp dụng Principle. |
| `SERVER_CONTROLLER_METHOD_REVOKE` | `delete` | Phương thức thu hồi Principle; phải khác phương thức áp dụng. |
| `SERVER_CONTROLLER_PATH_DECISIONS` | `decisions` | Đường dẫn Decision. |
| `SERVER_CONTROLLER_METHOD_IMPLEMENT` | `put` | Phương thức cài Decision. |
| `SERVER_CONTROLLER_METHOD_SUSPEND` | `delete` | Phương thức tạm ngưng Decision; phải khác phương thức cài. |
| `SERVER_CONTROLLER_AUTHORIZATION_EMAIL` | `X-Executor` | Tiêu đề HTTP chứa email User thực thi; phải là tên tiêu đề hợp lệ. |
| `SERVER_SECURITY_MANAGER` | `manager` | Máy chủ được phép gọi API điều khiển. Mặc định của biểu mẫu Manager là `worker`, còn mặc định dự phòng trong Defender là `manager`. Không chứa khoảng trắng, `/`, `\` hoặc `:`. |
| `SERVER_SECURITY_USERNAME` | `defly-defender` | Tên Basic Auth, tối thiểu 4 ký tự. |
| `SERVER_SECURITY_PASSWORD` | `P@55w0rd` | Mật khẩu Basic Auth, tối thiểu 4 ký tự; phải đổi trong môi trường thật. |

Các phương thức điều khiển chỉ nhận `post`, `put`, `patch` hoặc `delete`. Manager phải gọi đúng đường dẫn, phương thức, thông tin Basic Auth và tên tiêu đề HTTP đã cấu hình trong bản ghi Defender.

### Nhật ký Defender

| Biến | Mặc định | Điều kiện và ý nghĩa |
| --- | --- | --- |
| `SERVER_LOGGER_FILE_ENABLE` | `false` | Ghi nhật ký API điều khiển vào tệp. |
| `SERVER_LOGGER_FILE_PATH` | `storage/logs/server.log` | Tệp nhật ký có thể ghi; bắt buộc khi bật ghi tệp. |
| `SERVER_LOGGER_FORMAT` | `[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%` | Mẫu nhật ký Fiber cho API điều khiển. |
| `SERVER_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Múi giờ hiển thị trong nhật ký API điều khiển. |
| `PROXY_LOGGER_FILE_ENABLE` | `false` | Ghi nhật ký proxy vào tệp. |
| `PROXY_LOGGER_FILE_PATH` | `storage/logs/proxy.log` | Tệp nhật ký có thể ghi; bắt buộc khi bật ghi tệp. |
| `PROXY_LOGGER_FORMAT` | giống `SERVER_LOGGER_FORMAT` | Mẫu nhật ký Fiber cho proxy. |
| `PROXY_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Múi giờ hiển thị trong nhật ký proxy. |

### Proxy và điểm vi phạm

| Biến | Mặc định | Điều kiện và ý nghĩa |
| --- | --- | --- |
| `PROXY_BACKEND_URL` | `http://localhost` | URL máy chủ phía sau nhận yêu cầu sau khi WAF xử lý. |
| `PROXY_PORT` | `9948` | Cổng proxy, từ `1` đến `65535`. Orchestrator tự đặt theo cổng của bản ghi Defender. |
| `PROXY_TRUSTED_ENABLE` | `false` | Tin cậy thông tin địa chỉ máy khách từ proxy phía trước. |
| `PROXY_TRUSTED_LIST` | rỗng | Danh sách IP hoặc CIDR phân cách bằng dấu phẩy; bắt buộc khi `PROXY_TRUSTED_ENABLE=true`. |
| `PROXY_PRESERVE_HOST` | `true` | Giữ tiêu đề HTTP `Host` gốc khi chuyển tiếp tới máy chủ phía sau. |
| `PROXY_SEVERITY_INFO` | `1` | Điểm cộng của mức `INFO`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_NOTICE` | `2` | Điểm cộng của mức `NOTICE`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_WARNING` | `3` | Điểm cộng của mức `WARNING`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_ERROR` | `4` | Điểm cộng của mức `ERROR`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_CRITICAL` | `5` | Điểm cộng của mức `CRITICAL`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_ALERT` | `6` | Điểm cộng của mức `ALERT`, từ `1` đến `1000`. |
| `PROXY_SEVERITY_EMERGENCY` | `7` | Điểm cộng của mức `EMERGENCY`, từ `1` đến `1000`. |
| `PROXY_VIOLATION_LEVEL` | `1` | Cấp độ Principle ban đầu, từ `1` đến `1000000`. |
| `PROXY_VIOLATION_SCORE` | `5` | Ngưỡng dự phòng cho Decision có `score = 0`, từ `5` đến `100000`. |

## Ánh xạ giữa các dịch vụ

Các cặp sau phải cùng giá trị hoặc mô tả cùng giao kèo:

| Phía cấu hình | Phía nhận | Quan hệ |
| --- | --- | --- |
| `DB_DATABASE` | `DB_NAME`, `DATABASE_NAME` | Cùng tên cơ sở dữ liệu. |
| `DB_USERNAME` | `DB_USER`, `DATABASE_USER` | Cùng người dùng cơ sở dữ liệu. |
| `DB_PASSWORD` | `DB_PASS`, `DATABASE_PASS` | Cùng mật khẩu cơ sở dữ liệu. |
| `ORCHESTRATOR_USERNAME` | `SERVER_USERNAME` của Orchestrator | Basic Auth Manager/Worker -> Orchestrator. |
| `ORCHESTRATOR_PASSWORD` | `SERVER_PASSWORD` của Orchestrator | Mật khẩu tương ứng. |
| `ORCHESTRATOR_EMAIL_HEADER_KEY` | `SERVER_EMAIL_HEADER_KEY` | Tên tiêu đề HTTP mang người thực thi. |
| `ORCHESTRATOR_PATH_PREFIX` | `SERVER_PATH_PREFIX` | Tiền tố API Orchestrator. |
| `ORCHESTRATOR_PATH_DEPLOYMENT` | `SERVER_PATH_DEPLOYMENT` | Đường dẫn triển khai. |
| `ORCHESTRATOR_METHOD_*` | `SERVER_METHOD_*` | Phương thức của các thao tác triển khai. |
| Cấu hình gọi Defender trong Manager | `SERVER_CONTROLLER_*`, `SERVER_SECURITY_*` | Đường dẫn, phương thức và Basic Auth của API điều khiển Defender. |
| `SERVER_DEFENDER_IMAGE` | `SERVER_DEFENDER_IMAGE` của Orchestrator | Image dùng để triển khai Defender. |
| `SERVER_DEFENDER_TLS_VOLUME` | ổ `defender_tls` của Compose | Nơi chia sẻ chứng chỉ giữa Defender và Manager. |

## Biến được hệ thống tự quản lý

- Docker tự đặt `HOSTNAME` cho container Orchestrator. Orchestrator dùng nó để tìm nhãn và mạng của dự án Compose hiện tại.
- Orchestrator luôn ghi đè `DEFENDER_NAME` và `PROXY_PORT` khi tạo container Defender.
- Compose cố định `DB_HOST=mariadb`, `DB_PORT=3306` cho Manager/Orchestrator và cố định socket Docker ở `/var/run/docker.sock` cho Orchestrator môi trường thật.
- Compose cố định `ORCHESTRATOR_BASE_URL=https://orchestrator:8000` và các đường dẫn chứng chỉ bên trong container.
- Worker kế thừa môi trường Manager nhưng tắt migration, seeder và tối ưu hóa lúc khởi động.

## Kiểm tra sau khi thay đổi

1. Chạy `docker compose config` và kiểm tra giá trị sau nội suy.
2. Đảm bảo các cặp Manager/Orchestrator và Manager/Defender trong bảng ánh xạ khớp nhau.
3. Không để bí mật mẫu như `P@55w0rd`, `defly_secret` hoặc `random` trong môi trường thật.
4. Khởi động lại dịch vụ đọc biến đó; triển khai lại Defender nếu biến nằm trong bản ghi Defender.
5. Kiểm tra quyền đọc/ghi của tệp khóa, chứng chỉ, nhật ký, lỗi và Wordlist.
6. Kiểm tra nhật ký khởi động: Defender và Orchestrator từ chối nhiều giá trị không hợp lệ ngay khi nạp cấu hình.

Xem thêm [Cấu hình](Configuration.md), [Cài đặt](Installation.md), [Hướng dẫn Orchestrator](Orchestrator-Guide.md) và [Defender](CoreConcepts/Defender.md).
