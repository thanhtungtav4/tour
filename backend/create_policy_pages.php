<?php
/**
 * Script to create demo policy pages in WordPress
 * Run via WP-CLI: wp eval-file create_policy_pages.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "Starting creation of policy pages...\n";

$policies = [
    [
        'title' => 'Chính sách an toàn',
        'slug' => 'chinh-sach-an-toan',
        'content' => '
            <h2>1. Cam kết an toàn</h2>
            <p>Đôi Dép Adventure cam kết đảm bảo an toàn tuyệt đối cho tất cả khách hàng tham gia các tour trekking, camping và phiêu lưu. Chúng tôi tuân thủ nghiêm ngặt các quy định an toàn của Tổng cục Du lịch Việt Nam và các tiêu chuẩn quốc tế.</p>
            
            <h2>2. Hướng dẫn viên (HDV)</h2>
            <ul>
                <li>Tất cả HDV đều được chứng nhận nghiệp vụ hướng dẫn du lịch và sơ cấp cứu.</li>
                <li>Tỷ lệ HDV/khách: tối thiểu 1/10 cho tour dễ, 1/6 cho tour trung bình và khó.</li>
                <li>HDV mang theo bộ sơ cứu y tế và thiết bị liên lạc trong mọi tour.</li>
                <li>HDV có quyền hủy tour nếu điều kiện thời tiết hoặc địa hình không an toàn.</li>
            </ul>
            
            <h2>3. Trang thiết bị an toàn</h2>
            <ul>
                <li>Dây an toàn, mũ bảo hiểm, gậy trekking được cung cấp miễn phí cho tour khó.</li>
                <li>Thiết bị liên lạc vệ tinh cho các tour vùng sâu, vùng xa.</li>
                <li>GPS tracking cho tất cả đoàn trong các tour multi-day.</li>
                <li>Phương tiện cứu hộ luôn sẵn sàng tại các điểm tập kết.</li>
            </ul>
            
            <h2>4. Bảo hiểm du lịch</h2>
            <p>Tất cả khách hàng đều được mua bảo hiểm du lịch cơ bản trong giá tour. Mức bồi thường tối đa 50.000.000đ/người/vụ. Khách hàng có thể nâng cấp bảo hiểm cao cấp với mức bồi thường lên đến 200.000.000đ/người/vụ.</p>
            
            <h2>5. Quy định cho khách hàng</h2>
            <ul>
                <li>Tuân thủ hướng dẫn của HDV trong suốt chuyến đi.</li>
                <li>Không tự ý tách đoàn hoặc đi vào khu vực cấm.</li>
                <li>Mang trang phục và giày dép phù hợp với loại tour đã đăng ký.</li>
                <li>Khai báo trung thực tình trạng sức khỏe trước khi tham gia tour.</li>
                <li>Không sử dụng chất kích thích trong suốt chuyến đi.</li>
            </ul>
            
            <h2>6. Xử lý tình huống khẩn cấp</h2>
            <p>Trong trường hợp khẩn cấp, HDV sẽ kích hoạt quy trình cứu hộ và liên hệ với cơ quan chức năng địa phương. Đôi Dép Adventure có đường dây nóng 24/7: <strong>096 180 43 59</strong> để hỗ trợ khách hàng và người thân.</p>
        '
    ],
    [
        'title' => 'Chính sách hủy vé',
        'slug' => 'chinh-sach-huy-ve',
        'content' => '
            <h2>1. Quy định hủy tour</h2>
            <p>Khách hàng có thể hủy tour đã đăng ký theo các mức phí sau:</p>
            
            <table class="wp-block-table" style="width:100%; border-collapse:collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color:#f8f9fa;">
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">Thời gian hủy trước ngày khởi hành</th>
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">Phí hủy</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">Trên 14 ngày</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #10b981; font-weight: 500;">Miễn phí (hoàn 100%)</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">7 - 14 ngày</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #f59e0b; font-weight: 500;">Phí 30% giá tour</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">3 - 7 ngày</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #f97316; font-weight: 500;">Phí 50% giá tour</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">1 - 3 ngày</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #ef4444; font-weight: 500;">Phí 70% giá tour</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">Dưới 24 giờ hoặc không đến</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center; color: #ef4444; font-weight: 500;">Phí 100% giá tour</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>2. Hủy tour do bất khả kháng</h2>
            <p>Trong trường hợp tour bị hủy do thiên tai, dịch bệnh, hoặc lý do bất khả kháng khác:</p>
            <ul>
                <li>Khách hàng được hoàn 100% giá tour hoặc bảo lưu không thời hạn.</li>
                <li>Chi phí đã phát sinh (vé máy bay, khách sạn không hoàn) sẽ được trừ vào tiền hoàn.</li>
                <li>Đôi Dép Adventure sẽ thông báo và hỗ trợ khách hàng trong vòng 48 giờ.</li>
            </ul>
            
            <h2>3. Hủy tour do Đôi Dép Adventure</h2>
            <p>Nếu Đôi Dép Adventure phải hủy tour do không đủ số lượng khách đăng ký hoặc lý do khác:</p>
            <ul>
                <li>Hoàn 100% giá tour trong vòng 3-5 ngày làm việc.</li>
                <li>Hỗ trợ 10% giá tour cho lần đặt tiếp theo (áp dụng trong 6 tháng).</li>
                <li>Thông báo trước ít nhất 5 ngày so với ngày khởi hành.</li>
            </ul>
            
            <h2>4. Quy trình hủy tour</h2>
            <ol>
                <li>Gửi yêu cầu hủy qua email <strong>doidepadventure@gmail.com</strong> hoặc gọi <strong>096 180 43 59</strong>.</li>
                <li>Cung cấp mã đặt tour (booking ID) và lý do hủy.</li>
                <li>Nhận xác nhận hủy trong vòng 24 giờ.</li>
                <li>Tiền hoàn sẽ được chuyển vào tài khoản đã đăng ký.</li>
            </ol>
        '
    ],
    [
        'title' => 'Chính sách đổi vé, bảo lưu',
        'slug' => 'chinh-sach-doi-ve-bao-luu',
        'content' => '
            <h2>1. Đổi ngày khởi hành</h2>
            <p>Khách hàng được phép đổi ngày khởi hành theo quy định sau:</p>
            <ul>
                <li>Đổi trước 7 ngày so với ngày khởi hành: <strong>miễn phí</strong>.</li>
                <li>Đổi từ 3-7 ngày: phí đổi <strong>10% giá tour</strong>.</li>
                <li>Đổi trong vòng 48 giờ: phí đổi <strong>20% giá tour</strong>.</li>
                <li>Mỗi booking được đổi tối lại <strong>2 lần</strong>.</li>
                <li>Ngày mới phải còn chỗ trống và trong vòng 3 tháng kể từ ngày gốc.</li>
            </ul>
            
            <h2>2. Bảo lưu tour</h2>
            <p>Khách hàng có thể bảo lưu tour nếu không thể tham gia vào ngày đã đăng ký:</p>
            <ul>
                <li>Bảo lưu tối đa <strong>6 tháng</strong> kể từ ngày khởi hành gốc.</li>
                <li>Phí bảo lưu: <strong>5% giá tour</strong>.</li>
                <li>Giá tour được giữ nguyên, không áp dụng tăng giá (nếu có).</li>
                <li>Chỉ được bảo lưu 1 lần, không gia hạn thêm.</li>
                <li>Không áp dụng cho tour seasonal hoặc tour đặc biệt.</li>
            </ul>
            
            <h2>3. Đổi người tham gia</h2>
            <p>Khách hàng có thể chuyển nhượng vé cho người khác:</p>
            <ul>
                <li>Thông báo trước ít nhất 48 giờ so với ngày khởi hành.</li>
                <li>Không phát sinh phí đổi tên.</li>
                <li>Người mới phải đáp ứng điều kiện sức khỏe của tour.</li>
                <li>Thông tin người mới sẽ được cập nhật trong hệ thống.</li>
            </ul>
            
            <h2>4. Quy trình đổi vé / bảo lưu</h2>
            <ol>
                <li>Gửi yêu cầu qua email hoặc gọi hotline.</li>
                <li>Cung cấp mã booking và thông tin mới.</li>
                <li>Thanh toán phí đổi/bảo lưu (nếu có).</li>
                <li>Nhận xác nhận qua email trong 24 giờ.</li>
            </ol>
        '
    ],
    [
        'title' => 'Chính sách hoàn tiền',
        'slug' => 'chinh-sach-hoan-tien',
        'content' => '
            <h2>1. Điều kiện hoàn tiền</h2>
            <p>Khách hàng được hoàn tiền trong các trường hợp sau:</p>
            <ul>
                <li>Hủy tour theo đúng quy định chính sách hủy vé.</li>
                <li>Tour bị hủy do Đôi Dép Adventure hoặc bất khả kháng.</li>
                <li>Thanh toán nhầm hoặc trùng booking.</li>
                <li>Dịch vụ không được cung cấp như cam kết.</li>
            </ul>
            
            <h2>2. Thời gian hoàn tiền</h2>
            <table class="wp-block-table" style="width:100%; border-collapse:collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color:#f8f9fa;">
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">Phương thức thanh toán</th>
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">Thời gian hoàn</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">Chuyển khoản ngân hàng</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">3-5 ngày làm việc</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">VietQR / QR Code</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">1-3 ngày làm việc</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 12px;">Tiền mặt (tại văn phòng)</td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">Hoàn ngay trong ngày</td>
                    </tr>
                </tbody>
            </table>
            
            <h2>3. Quy trình hoàn tiền</h2>
            <ol>
                <li>Gửi yêu cầu hoàn tiền kèm mã booking và lý do.</li>
                <li>Đôi Dép Adventure xác nhận yêu cầu trong 24 giờ.</li>
                <li>Kiểm tra điều kiện hoàn tiền và tính toán số tiền.</li>
                <li>Chuyển tiền vào tài khoản khách hàng đã đăng ký.</li>
                <li>Gửi email xác nhận đã hoàn tiền.</li>
            </ol>
            
            <h2>4. Lưu ý</h2>
            <ul>
                <li>Số tài khoản nhận hoàn tiền phải trùng với tài khoản đã thanh toán.</li>
                <li>Phí chuyển khoản (nếu có) do Đôi Dép Adventure chịu.</li>
                <li>Không hoàn tiền mặt cho trường hợp đã thanh toán online.</li>
                <li>Trường hợp tranh chấp, Đôi Dép Adventure sẽ giải quyết trong 7 ngày làm việc.</li>
            </ul>
        '
    ],
    [
        'title' => 'Chính sách bảo mật',
        'slug' => 'chinh-sach-bao-mat',
        'content' => '
            <h2>1. Thu thập thông tin</h2>
            <p>Đôi Dép Adventure thu thập các thông tin cá nhân sau khi bạn đăng ký tour:</p>
            <ul>
                <li>Họ và tên, số điện thoại, email.</li>
                <li>Ngày sinh, giới tính (cho bảo hiểm du lịch).</li>
                <li>Thông tin thanh toán (xử lý qua cổng thanh toán bảo mật).</li>
                <li>Thông tin sức khỏe cơ bản (dị ứng, bệnh nền - nếu có).</li>
            </ul>
            
            <h2>2. Mục đích sử dụng</h2>
            <p>Thông tin được sử dụng cho các mục đích:</p>
            <ul>
                <li>Xác nhận booking và liên hệ trước tour.</li>
                <li>Mua bảo hiểm du lịch.</li>
                <li>Gửi thông tin ưu đãi, khuyến mãi (nếu khách hàng đồng ý).</li>
                <li>Cải thiện chất lượng dịch vụ.</li>
                <li>Giải quyết khiếu nại, tranh chấp.</li>
            </ul>
            
            <h2>3. Bảo vệ thông tin</h2>
            <ul>
                <li>Dữ liệu được mã hóa SSL/TLS khi truyền tải.</li>
                <li>Thông tin thanh toán không được lưu trữ trên server của Đôi Dép Adventure.</li>
                <li>Chỉ nhân viên được ủy quyền mới truy cập dữ liệu cá nhân.</li>
                <li>Không bán, chia sẻ thông tin cho bên thứ ba trừ khi có sự đồng ý.</li>
            </ul>
            
            <h2>4. Chia sẻ thông tin</h2>
            <p>Đôi Dép Adventure chỉ chia sẻ thông tin trong các trường hợp:</p>
            <ul>
                <li>Với công ty bảo hiểm để mua bảo hiểm du lịch.</li>
                <li>Với HDV để liên hệ và hỗ trợ khách hàng trong tour.</li>
                <li>Theo yêu cầu của cơ quan pháp luật có thẩm quyền.</li>
            </ul>
            
            <h2>5. Quyền của khách hàng</h2>
            <ul>
                <li>Yêu cầu truy cập, chỉnh sửa hoặc xóa thông tin cá nhân.</li>
                <li>Từ chối nhận email marketing bất cứ lúc nào.</li>
                <li>Yêu cầu bản sao dữ liệu cá nhân đã lưu trữ.</li>
                <li>Khiếu nại nếu phát hiện thông tin bị sử dụng sai mục đích.</li>
            </ul>
            
            <h2>6. Liên hệ</h2>
            <p>Mọi thắc mắc về chính sách bảo mật, vui lòng liên hệ:</p>
            <ul>
                <li><strong>Email:</strong> doidepadventure@gmail.com</li>
                <li><strong>Hotline:</strong> 096 180 43 59</li>
                <li><strong>Địa chỉ:</strong> TP. Hồ Chí Minh</li>
            </ul>
        '
    ],
    [
        'title' => 'Điều khoản sử dụng',
        'slug' => 'dieu-khoan-su-dung',
        'content' => '
            <h2>1. Giới thiệu</h2>
            <p>Chào mừng bạn đến với Đôi Dép Adventure - nền tảng đặt tour trekking, camping và phiêu lưu thiên nhiên. Bằng việc sử dụng website và dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau đây.</p>
            
            <h2>2. Đăng ký và đặt tour</h2>
            <ul>
                <li>Khách hàng từ 18 tuổi trở lên có quyền đặt tour.</li>
                <li>Khách hàng dưới 18 tuổi cần có sự đồng ý của người giám hộ.</li>
                <li>Thông tin đăng ký phải chính xác và đầy đủ.</li>
                <li>Booking chỉ được xác nhận sau khi thanh toán thành công.</li>
                <li>Đôi Dép Adventure có quyền từ chối booking nếu thông tin không hợp lệ.</li>
            </ul>
            
            <h2>3. Thanh toán</h2>
            <ul>
                <li>Giá tour đã bao gồm: HDV, bảo hiểm, nước uống, bữa ăn theo chương trình.</li>
                <li>Giá tour chưa bao gồm: chi phí cá nhân, tip, dịch vụ phát sinh ngoài chương trình.</li>
                <li>Thanh toán qua: chuyển khoản, VietQR, hoặc tiền mặt.</li>
                <li>Khách hàng thanh toán đủ trước ngày khởi hành ít nhất 3 ngày.</li>
            </ul>
            
            <h2>4. Trách nhiệm khách hàng</h2>
            <ul>
                <li>Đảm bảo sức khỏe phù hợp với loại tour đã đăng ký.</li>
                <li>Tuân thủ nội quy và hướng dẫn của HDV.</li>
                <li>Bảo vệ môi trường, không xả rác bừa bãi.</li>
                <li>Tôn trọng văn hóa bản địa và thiên nhiên.</li>
                <li>Không mang theo vật phẩm nguy hiểm, chất cấm.</li>
            </ul>
            
            <h2>5. Trách nhiệm Đôi Dép Adventure</h2>
            <ul>
                <li>Cung cấp dịch vụ đúng như mô tả trên website.</li>
                <li>Đảm bảo an toàn trong suốt chuyến đi.</li>
                <li>Thông báo kịp thời nếu có thay đổi lịch trình.</li>
                <li>Hỗ trợ khách hàng 24/7 trong trường hợp khẩn cấp.</li>
                <li>Bảo mật thông tin cá nhân của khách hàng.</li>
            </ul>
            
            <h2>6. Miễn trừ trách nhiệm</h2>
            <p>Đôi Dép Adventure không chịu trách nhiệm trong các trường hợp:</p>
            <ul>
                <li>Khách hàng không tuân thủ hướng dẫn của HDV dẫn đến tai nạn.</li>
                <li>Mất mát tài sản cá nhân do khách hàng không bảo quản.</li>
                <li>Chậm trễ do thời tiết, giao thông, hoặc bất khả kháng.</li>
                <li>Khách hàng tự ý tách đoàn hoặc đi vào khu vực cấm.</li>
            </ul>
            
            <h2>7. Sở hữu trí tuệ</h2>
            <p>Toàn bộ nội dung, hình ảnh, logo trên website thuộc quyền sở hữu của Đôi Dép Adventure. Không được sao chép, sử dụng khi chưa có sự đồng ý bằng văn bản.</p>
            
            <h2>8. Giải quyết tranh chấp</h2>
            <p>Mọi tranh chấp sẽ được giải quyết qua thương lượng. Nếu không đạt được thỏa thuận, tranh chấp sẽ được đưa ra Tòa án nhân dân TP. Hồ Chí Minh để giải quyết.</p>
        '
    ]
];

foreach ($policies as $page) {
    // Check if page already exists by slug
    $existing = new WP_Query([
        'post_type' => 'page',
        'name' => $page['slug'],
        'posts_per_page' => 1,
        'post_status' => 'any',
    ]);
    
    if ($existing->have_posts()) {
        echo "Page '{$page['title']}' already exists. Skipping...\n";
        continue;
    }
    
    // Insert page CPT
    $post_id = wp_insert_post([
        'post_type' => 'page',
        'post_title' => $page['title'],
        'post_name' => $page['slug'],
        'post_content' => trim($page['content']),
        'post_status' => 'publish',
    ]);
    
    if (is_wp_error($post_id)) {
        echo "Error creating page '{$page['title']}': " . $post_id->get_error_message() . "\n";
    } else {
        echo "Created page '{$page['title']}' with ID {$post_id}\n";
    }
}

echo "All policy pages created successfully!\n";
