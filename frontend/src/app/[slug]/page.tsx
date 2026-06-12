import { getSettings, getPageBySlug } from "@/lib/api";
import { notFound } from "next/navigation";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import Link from "next/link";

interface PageProps {
  params: Promise<{ slug: string }>;
}

const policyLinks = [
  { href: "/chinh-sach-an-toan", label: "Chính sách an toàn" },
  { href: "/chinh-sach-huy-ve", label: "Chính sách hủy vé" },
  { href: "/chinh-sach-doi-ve-bao-luu", label: "Chính sách đổi vé, bảo lưu" },
  { href: "/chinh-sach-hoan-tien", label: "Chính sách hoàn tiền" },
  { href: "/chinh-sach-bao-mat", label: "Chính sách bảo mật" },
  { href: "/dieu-khoan-su-dung", label: "Điều khoản sử dụng" },
];

export default async function DynamicPage({ params }: PageProps) {
  const { slug } = await params;

  // Fetch settings for fallback rendering
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in dynamic page", err);
  }
  const hotline = settings?.hotline || "096 180 43 59";
  const email = settings?.contact_email || "doidepadventure@gmail.com";
  const address = settings?.company_address || "TP. Hồ Chí Minh, Việt Nam";

  // Fetch page by slug from WordPress
  let pageData = null;
  try {
    pageData = await getPageBySlug(slug);
  } catch (err) {
    console.error(`Error loading page for slug: ${slug}`, err);
  }

  // Define static fallbacks if backend is offline or page not created
  const getStaticFallback = (slugStr: string) => {
    switch (slugStr) {
      case "chinh-sach-an-toan":
        return {
          title: "Chính sách an toàn",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Cam kết an toàn</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Đôi Dép Adventure cam kết đảm bảo an toàn tuyệt đối cho tất cả khách hàng tham gia các tour trekking, camping và phiêu lưu. 
              Chúng tôi tuân thủ nghiêm ngặt các quy định an toàn của Tổng cục Du lịch Việt Nam và các tiêu chuẩn quốc tế.
            </p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Hướng dẫn viên (HDV)</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Tất cả HDV đều được chứng nhận nghiệp vụ hướng dẫn du lịch và sơ cấp cứu</li>
              <li>Tỷ lệ HDV/khách: tối thiểu 1/10 cho tour dễ, 1/6 cho tour trung bình và khó</li>
              <li>HDV mang theo bộ sơ cứu y tế và thiết bị liên lạc trong mọi tour</li>
              <li>HDV có quyền hủy tour nếu điều kiện thời tiết hoặc địa hình không an toàn</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Trang thiết bị an toàn</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Dây an toàn, mũ bảo hiểm, gậy trekking được cung cấp miễn phí cho tour khó</li>
              <li>Thiết bị liên lạc vệ tinh cho các tour vùng sâu, vùng xa</li>
              <li>GPS tracking cho tất cả đoàn trong các tour multi-day</li>
              <li>Phương tiện cứu hộ luôn sẵn sàng tại các điểm tập kết</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Bảo hiểm du lịch</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Tất cả khách hàng đều được mua bảo hiểm du lịch cơ bản trong giá tour. 
              Mức bồi thường tối đa 50.000.000đ/người/vụ. Khách hàng có thể nâng cấp bảo hiểm cao cấp 
              với mức bồi thường lên đến 200.000.000đ/người/vụ.
            </p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Quy định cho khách hàng</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Tuân thủ hướng dẫn của HDV trong suốt chuyến đi</li>
              <li>Không tự ý tách đoàn hoặc đi vào khu vực cấm</li>
              <li>Mang trang phục và giày dép phù hợp với loại tour đã đăng ký</li>
              <li>Khai báo trung thực tình trạng sức khỏe trước khi tham gia tour</li>
              <li>Không sử dụng chất kích thích trong suốt chuyến đi</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Xử lý tình huống khẩn cấp</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Trong trường hợp khẩn cấp, HDV sẽ kích hoạt quy trình cứu hộ và liên hệ với cơ quan chức năng địa phương. 
              Đôi Dép Adventure có đường dây nóng 24/7: <strong>${hotline}</strong> để hỗ trợ khách hàng và người thân.
            </p>
          `
        };
      case "chinh-sach-huy-ve":
        return {
          title: "Chính sách hủy vé",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Quy định hủy tour</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Khách hàng có thể hủy tour đã đăng ký theo các mức phí sau:
            </p>
            <table class="w-full border-collapse text-sm mb-6">
              <thead>
                <tr class="bg-gray-50">
                  <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Thời gian hủy trước ngày khởi hành</th>
                  <th class="border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Phí hủy</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">Trên 14 ngày</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-emerald-600 font-semibold">Miễn phí (hoàn 100%)</td>
                </tr>
                <tr class="bg-gray-50">
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">7 - 14 ngày</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-amber-600 font-semibold">Phí 30% giá tour</td>
                </tr>
                <tr>
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">3 - 7 ngày</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-orange-600 font-semibold">Phí 50% giá tour</td>
                </tr>
                <tr class="bg-gray-50">
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">1 - 3 ngày</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-red-600 font-semibold">Phí 70% giá tour</td>
                </tr>
                <tr>
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">Dưới 24 giờ hoặc không đến</td>
                  <td class="border border-gray-200 px-4 py-3 text-center text-red-600 font-semibold">Phí 100% giá tour</td>
                </tr>
              </tbody>
            </table>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Hủy tour do bất khả kháng</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Trong trường hợp tour bị hủy do thiên tai, dịch bệnh, hoặc lý do bất khả kháng khác:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Khách hàng được hoàn 100% giá tour hoặc bảo lưu không thời hạn</li>
              <li>Chi phí đã phát sinh (vé máy bay, khách sạn không hoàn) sẽ được trừ vào tiền hoàn</li>
              <li>Đôi Dép Adventure sẽ thông báo và hỗ trợ khách hàng trong vòng 48 giờ</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Hủy tour do Đôi Dép Adventure</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Nếu Đôi Dép Adventure phải hủy tour do không đủ số lượng khách đăng ký hoặc lý do khác:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Hoàn 100% giá tour trong vòng 3-5 ngày làm việc</li>
              <li>Hỗ trợ 10% giá tour cho lần đặt tiếp theo (áp dụng trong 6 tháng)</li>
              <li>Thông báo trước ít nhất 5 ngày so với ngày khởi hành</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Quy trình hủy tour</h2>
            <ol class="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
              <li>Gửi yêu cầu hủy qua email <strong>${email}</strong> hoặc gọi <strong>${hotline}</strong></li>
              <li>Cung cấp mã đặt tour (booking ID) và lý do hủy</li>
              <li>Nhận xác nhận hủy trong vòng 24 giờ</li>
              <li>Tiền hoàn sẽ được chuyển vào tài khoản đã đăng ký</li>
            </ol>
          `
        };
      case "chinh-sach-doi-ve-bao-luu":
        return {
          title: "Chính sách đổi vé, bảo lưu",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Đổi ngày khởi hành</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Khách hàng được phép đổi ngày khởi hành theo quy định sau:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Đổi trước 7 ngày so với ngày khởi hành: <strong>miễn phí</strong></li>
              <li>Đổi từ 3-7 ngày: phí đổi <strong>10% giá tour</strong></li>
              <li>Đổi trong vòng 48 giờ: phí đổi <strong>20% giá tour</strong></li>
              <li>Mỗi booking được đổi tối đa <strong>2 lần</strong></li>
              <li>Ngày mới phải còn chỗ trống và trong vòng 3 tháng kể từ ngày gốc</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Bảo lưu tour</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Khách hàng có thể bảo lưu tour nếu không thể tham gia vào ngày đã đăng ký:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Bảo lưu tối đa <strong>6 tháng</strong> kể từ ngày khởi hành gốc</li>
              <li>Phí bảo lưu: <strong>5% giá tour</strong></li>
              <li>Giá tour được giữ nguyên, không áp dụng tăng giá (nếu có)</li>
              <li>Chỉ được bảo lưu 1 lần, không gia hạn thêm</li>
              <li>Không áp dụng cho tour seasonal hoặc tour đặc biệt</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Đổi người tham gia</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Khách hàng có thể chuyển nhượng vé cho người khác:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Thông báo trước ít nhất 48 giờ so với ngày khởi hành</li>
              <li>Không phát sinh phí đổi tên</li>
              <li>Người mới phải đáp ứng điều kiện sức khỏe của tour</li>
              <li>Thông tin người mới sẽ được cập nhật trong hệ thống</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Quy trình đổi vé / bảo lưu</h2>
            <ol class="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
              <li>Gửi yêu cầu qua email hoặc gọi hotline</li>
              <li>Cung cấp mã booking và thông tin mới</li>
              <li>Thanh toán phí đổi/bảo lưu (nếu có)</li>
              <li>Nhận xác nhận qua email trong 24 giờ</li>
            </ol>
          `
        };
      case "chinh-sach-hoan-tien":
        return {
          title: "Chính sách hoàn tiền",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Điều kiện hoàn tiền</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Khách hàng được hoàn tiền trong các trường hợp sau:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Hủy tour theo đúng quy định chính sách hủy vé</li>
              <li>Tour bị hủy do Đôi Dép Adventure hoặc bất khả kháng</li>
              <li>Thanh toán nhầm hoặc trùng booking</li>
              <li>Dịch vụ không được cung cấp như cam kết</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Thời gian hoàn tiền</h2>
            <table class="w-full border-collapse text-sm mb-6">
              <thead>
                <tr class="bg-gray-50">
                  <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Phương thức thanh toán</th>
                  <th class="border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Thời gian hoàn</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">Chuyển khoản ngân hàng</td>
                  <td class="border border-gray-200 px-4 py-3 text-center font-medium">3-5 ngày làm việc</td>
                </tr>
                <tr class="bg-gray-50">
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">VietQR / QR Code</td>
                  <td class="border border-gray-200 px-4 py-3 text-center font-medium">1-3 ngày làm việc</td>
                </tr>
                <tr>
                  <td class="border border-gray-200 px-4 py-3 text-gray-600">Tiền mặt (tại văn phòng)</td>
                  <td class="border border-gray-200 px-4 py-3 text-center font-medium">Hoàn ngay trong ngày</td>
                </tr>
              </tbody>
            </table>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Quy trình hoàn tiền</h2>
            <ol class="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
              <li>Gửi yêu cầu hoàn tiền kèm mã booking và lý do</li>
              <li>Đôi Dép Adventure xác nhận yêu cầu trong 24 giờ</li>
              <li>Kiểm tra điều kiện hoàn tiền và tính toán số tiền</li>
              <li>Chuyển tiền vào tài khoản khách hàng đã đăng ký</li>
              <li>Gửi email xác nhận đã hoàn tiền</li>
            </ol>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Lưu ý</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Số tài khoản nhận hoàn tiền phải trùng với tài khoản đã thanh toán</li>
              <li>Phí chuyển khoản (nếu có) do Đôi Dép Adventure chịu</li>
              <li>Không hoàn tiền mặt cho trường hợp đã thanh toán online</li>
              <li>Trường hợp tranh chấp, Đôi Dép Adventure sẽ giải quyết trong 7 ngày làm việc</li>
            </ul>
          `
        };
      case "chinh-sach-bao-mat":
        return {
          title: "Chính sách bảo mật",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Thu thập thông tin</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Đôi Dép Adventure thu thập các thông tin cá nhân sau khi bạn đăng ký tour:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Họ và tên, số điện thoại, email</li>
              <li>Ngày sinh, giới tính (cho bảo hiểm du lịch)</li>
              <li>Thông tin thanh toán (xử lý qua cổng thanh toán bảo mật)</li>
              <li>Thông tin sức khỏe cơ bản (dị ứng, bệnh nền - nếu có)</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Mục đích sử dụng</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Thông tin được sử dụng cho các mục đích:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Xác nhận booking và liên hệ trước tour</li>
              <li>Mua bảo hiểm du lịch</li>
              <li>Gửi thông tin ưu đãi, khuyến mãi (nếu khách hàng đồng ý)</li>
              <li>Cải thiện chất lượng dịch vụ</li>
              <li>Giải quyết khiếu nại, tranh chấp</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Bảo vệ thông tin</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Dữ liệu được mã hóa SSL/TLS khi truyền tải</li>
              <li>Thông tin thanh toán không được lưu trữ trên server của Đôi Dép Adventure</li>
              <li>Chỉ nhân viên được ủy quyền mới truy cập dữ liệu cá nhân</li>
              <li>Không bán, chia sẻ thông tin cho bên thứ ba trừ khi có sự đồng ý</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Chia sẻ thông tin</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Đôi Dép Adventure chỉ chia sẻ thông tin trong các trường hợp:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Với công ty bảo hiểm để mua bảo hiểm du lịch</li>
              <li>Với HDV để liên hệ và hỗ trợ khách hàng trong tour</li>
              <li>Theo yêu cầu của cơ quan pháp luật có thẩm quyền</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Quyền của khách hàng</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Yêu cầu truy cập, chỉnh sửa hoặc xóa thông tin cá nhân</li>
              <li>Từ chối nhận email marketing bất cứ lúc nào</li>
              <li>Yêu cầu bản sao dữ liệu cá nhân đã lưu trữ</li>
              <li>Khiếu nại nếu phát hiện thông tin bị sử dụng sai mục đích</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Liên hệ</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Mọi thắc mắc về chính sách bảo mật, vui lòng liên hệ:
            </p>
            <ul class="list-none text-gray-600 space-y-1 mb-4">
              <li><strong>Email:</strong> ${email}</li>
              <li><strong>Hotline:</strong> ${hotline}</li>
              <li><strong>Địa chỉ:</strong> ${address}</li>
            </ul>
          `
        };
      case "dieu-khoan-su-dung":
        return {
          title: "Điều khoản sử dụng",
          content: `
            <p class="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Giới thiệu</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Chào mừng bạn đến với Đôi Dép Adventure - nền tảng đặt tour trekking, camping và phiêu lưu thiên nhiên. 
              Bằng việc sử dụng website và dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau đây.
            </p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Đăng ký và đặt tour</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Khách hàng từ 18 tuổi trở lên có quyền đặt tour</li>
              <li>Khách hàng dưới 18 tuổi cần có sự đồng ý của người giám hộ</li>
              <li>Thông tin đăng ký phải chính xác và đầy đủ</li>
              <li>Booking chỉ được xác nhận sau khi thanh toán thành công</li>
              <li>Đôi Dép Adventure có quyền từ chối booking nếu thông tin không hợp lệ</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Thanh toán</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Giá tour đã bao gồm: HDV, bảo hiểm, nước uống, bữa ăn theo chương trình</li>
              <li>Giá tour chưa bao gồm: chi phí cá nhân, tip, dịch vụ phát sinh ngoài chương trình</li>
              <li>Thanh toán qua: chuyển khoản, VietQR, hoặc tiền mặt</li>
              <li>Khách hàng thanh toán đủ trước ngày khởi hành ít nhất 3 ngày</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Trách nhiệm khách hàng</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Đảm bảo sức khỏe phù hợp với loại tour đã đăng ký</li>
              <li>Tuân thủ nội quy và hướng dẫn của HDV</li>
              <li>Bảo vệ môi trường, không xả rác bừa bãi</li>
              <li>Tôn trọng văn hóa bản địa và thiên nhiên</li>
              <li>Không mang theo vật phẩm nguy hiểm, chất cấm</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Trách nhiệm Đôi Dép Adventure</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Cung cấp dịch vụ đúng như mô tả trên website</li>
              <li>Đảm bảo an toàn trong suốt chuyến đi</li>
              <li>Thông báo kịp thời nếu có thay đổi lịch trình</li>
              <li>Hỗ trợ khách hàng 24/7 trong trường hợp khẩn cấp</li>
              <li>Bảo mật thông tin cá nhân của khách hàng</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Miễn trừ trách nhiệm</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Đôi Dép Adventure không chịu trách nhiệm trong các trường hợp:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
              <li>Khách hàng không tuân thủ hướng dẫn của HDV dẫn đến tai nạn</li>
              <li>Mất mát tài sản cá nhân do khách hàng không bảo quản</li>
              <li>Chậm trễ do thời tiết, giao thông, hoặc bất khả kháng</li>
              <li>Khách hàng tự ý tách đoàn hoặc đi vào khu vực cấm</li>
            </ul>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">7. Sở hữu trí tuệ</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Toàn bộ nội dung, hình ảnh, logo trên website thuộc quyền sở hữu của Đôi Dép Adventure. 
              Không được sao chép, sử dụng khi chưa có sự đồng ý bằng văn bản.
            </p>
            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">8. Giải quyết tranh chấp</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              Mọi tranh chấp sẽ được giải quyết qua thương lượng. Nếu không đạt được thỏa thuận, 
              tranh chấp sẽ được đưa ra Tòa án nhân dân ${address} để giải quyết.
            </p>
          `
        };
      default:
        return null;
    }
  };

  const fallbackData = getStaticFallback(slug);

  // If both WP page and static fallback do not exist, trigger 404
  if (!pageData && !fallbackData) {
    return notFound();
  }

  const title = pageData?.title || fallbackData?.title || "";
  const htmlContent = pageData?.content || fallbackData?.content || "";

  // Check if this page is one of our standard policies (should show policies sidebar)
  const isPolicyPage = policyLinks.some((link) => link.href === `/${slug}`);

  if (isPolicyPage) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-grow pt-[81px]">
          {/* Hero Banner */}
          <section className="py-12 px-4 bg-gradient-to-b from-emerald-50 to-white border-b border-gray-100">
            <div className="container mx-auto text-center">
              <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Chính sách</h1>
              <p className="text-gray-500">Thông tin chi tiết về các chính sách của Đôi Dép Adventure</p>
            </div>
          </section>

          {/* Grid Layout with Sidebar */}
          <div className="container mx-auto px-4 py-8">
            <div className="grid lg:grid-cols-4 gap-8">
              {/* Sidebar */}
              <aside className="lg:col-span-1">
                <nav className="sticky top-[120px] bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                  <h3 className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3 px-2">
                    Danh mục
                  </h3>
                  <ul className="space-y-1">
                    {policyLinks.map((link) => {
                      const isActive = link.href === `/${slug}`;
                      return (
                        <li key={link.href}>
                          <Link
                            href={link.href}
                            className={`block px-3 py-2 text-sm rounded-lg transition-colors ${
                              isActive
                                ? "bg-emerald-50 text-emerald-700 font-medium"
                                : "text-gray-600 hover:bg-emerald-50 hover:text-emerald-700"
                            }`}
                          >
                            {link.label}
                          </Link>
                        </li>
                      );
                    })}
                  </ul>
                </nav>
              </aside>

              {/* Dynamic Content */}
              <div className="lg:col-span-3">
                <article className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-10 prose prose-gray max-w-none">
                  <h1 className="text-2xl font-bold text-gray-900 mb-6">{title}</h1>
                  <div 
                    dangerouslySetInnerHTML={{ __html: htmlContent }} 
                    className="dynamic-policy-content" 
                  />
                </article>
              </div>
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  // General Dynamic Page (Full width)
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Banner */}
        <section className="py-12 px-4 bg-gradient-to-b from-emerald-50 to-white border-b border-gray-100">
          <div className="container mx-auto text-center max-w-4xl">
            <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
              {title}
            </h1>
          </div>
        </section>

        {/* Content */}
        <div className="container mx-auto px-4 py-12 max-w-4xl">
          <article className="prose prose-gray max-w-none bg-white rounded-2xl border border-gray-100 p-6 lg:p-10 shadow-sm">
            <div 
              dangerouslySetInnerHTML={{ __html: htmlContent }} 
              className="dynamic-policy-content" 
            />
          </article>
        </div>
      </main>
      <Footer />
    </div>
  );
}
