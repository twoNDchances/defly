# Timeline

Timeline là nhật ký kiểm tra thao tác quản trị trong Defly Manager. Mỗi bản ghi cho biết ai đã làm gì, từ yêu cầu nào và trên tài nguyên nào.

Timeline khác [Report](Report.md): Report ghi sự kiện HTTP bị tường lửa phát hiện; Timeline ghi hành động quản trị lên dữ liệu Manager.

## Các trường

| Trường | Ý nghĩa |
| --- | --- |
| `created_at` | Thời điểm bản ghi kiểm tra được tạo. |
| `created_by` | User thực hiện, nếu nhận diện được. |
| `ipv4` | IPv4 máy khách nếu IP yêu cầu là IPv4 hợp lệ. |
| `ipv6` | IPv6 máy khách nếu IP yêu cầu là IPv6 hợp lệ. |
| `method` | Phương thức HTTP viết thường. |
| `path` | Đường dẫn yêu cầu không gồm tên miền. |
| `action` | Hành động nghiệp vụ. |
| `resource_type` | Lớp quan hệ đa hình Eloquent của tài nguyên. |
| `resource_id` | UUID/ID tài nguyên. |

IP của một yêu cầu chỉ đi vào `ipv4` hoặc `ipv6` tùy loại. Khi không có ngữ cảnh yêu cầu HTTP, các trường yêu cầu có thể là `null`.

## Hành động được hiển thị

| Hành động | Nguồn thường gặp |
| --- | --- |
| `create` | Bộ quan sát sau khi tạo tài nguyên. |
| `update` | Bộ quan sát sau khi cập nhật tài nguyên. |
| `delete` | Bộ quan sát sau khi xóa tài nguyên. |
| `clone` | Thao tác nhân bản. |
| `validate` | Kiểm tra Principle. |
| `deploy` | Đưa tác vụ triển khai Defender vào hàng đợi. |
| `cancel` | Đưa tác vụ hủy Defender vào hàng đợi. |
| `follow` | Xem nhật ký container Defender. |
| `refresh` | Làm mới phản hồi Defender. |
| `apply`, `revoke` | Quản lý Principle trong quá trình chạy. |
| `implement`, `suspend` | Quản lý Decision trong quá trình chạy. |
| `review` | Đánh dấu Report đã xem xét. |

Giao diện còn có các hành động hàng loạt tương ứng, nhưng Timeline lưu chuỗi hành động thực tế do lớp `Logger` ghi nhận.

## Tài nguyên được hỗ trợ

Timeline có thể trỏ đến:

- User, Group, Permission, Key, Label
- Wordlist, Pattern, Engine, Target
- Action, Rule, Principle, Decision
- Defender, Report

Quan hệ `resource()` là quan hệ đa hình. Nếu tài nguyên đã bị xóa, Timeline vẫn giữ `resource_type` và `resource_id`, nhưng nút mở tài nguyên có thể không còn đích hợp lệ.

## Cách Timeline được tạo

Trait quan sát `After` gọi lớp `Logger` khi mô hình dữ liệu được tạo, cập nhật hoặc xóa. Các hành động nghiệp vụ như triển khai, kiểm tra, áp dụng hoặc xem xét gọi `Logger` trực tiếp.

`Logger` chỉ ghi khi:

- Hành động không rỗng.
- Tài nguyên có khóa và đang tồn tại, hoặc hành động là `delete`.
- Tài nguyên không phải chính Timeline.
- Ứng dụng không chạy trong dòng lệnh.

Timeline được tạo bằng `withoutEvents` để tránh bản ghi kiểm tra tự tạo thêm bản ghi kiểm tra. Lỗi ghi Timeline được báo lại nhưng không làm hỏng thao tác chính.

`updated` bỏ qua mô hình dữ liệu vừa mới tạo để tránh ghi cả `create` và `update` cho cùng chu kỳ tạo.

## Quyền sở hữu và quyền thao tác

Timeline dùng trait Owner nên `created_by` liên kết User. Nó chỉ hỗ trợ liệt kê/xem/xóa trong mô hình Permission; không có quyền tạo/cập nhật vì bản ghi phải do hệ thống tạo.

Việc xóa Timeline làm mất dấu vết kiểm tra và nên được giới hạn. Timeline không phải nhật ký bất biến chỉ-ghi-thêm tuyệt đối vì bộ điều khiển hiện có điểm cuối xóa.

## Giới hạn

- Timeline ghi siêu dữ liệu thao tác, không lưu phần thay đổi trước/sau của trường.
- Không ghi nội dung yêu cầu, nội dung phản hồi hoặc kết quả Action.
- Action hàng đợi được ghi nhật ký tại lúc xếp hàng, không nhất thiết chứng minh tác vụ cuối cùng thành công. Trạng thái kết quả phải xem ở tài nguyên, ví dụ `deployment_status` hoặc `last_response_details`.
- Thao tác chạy trong dòng lệnh bị bỏ qua.

## Điều tra lịch sử thao tác

1. Xác định User, IP, phương thức và đường dẫn.
2. Mở tài nguyên qua `resource_type + resource_id` nếu còn tồn tại.
3. Với Action bất đồng bộ, kiểm tra trạng thái/chi tiết trên tài nguyên.
4. Đối chiếu nhiều Timeline liên tiếp để hiểu việc gắn/áp dụng hoặc triển khai/theo dõi/hủy.

## Danh sách kiểm tra vận hành

- Chỉ cấp quyền xóa Timeline cho quản trị viên kiểm tra.
- Không dùng Timeline thay cho nhật ký ứng dụng/container.
- Khi cần lưu phần thay đổi trường bất biến, hãy bổ sung một cơ chế kiểm tra chuyên dụng.
- Giữ múi giờ hiển thị nhất quán khi đối chiếu với Report và nhật ký Defender.
