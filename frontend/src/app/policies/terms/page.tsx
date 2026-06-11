import { getSettings } from "@/lib/api";

export default async function TermsPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in TermsPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";
  const email = settings?.contact_email || "doidepadventure@gmail.com";
  const address = settings?.company_address || "TP. Hồ Chí Minh";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Điều khoản sử dụng</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Giới thiệu</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Chào mừng bạn đến với Đôi Dép Adventure - nền tảng đặt tour trekking, camping và phiêu lưu thiên nhiên. 
        Bằng việc sử dụng website và dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau đây.
      </p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Đăng ký và đặt tour</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Khách hàng từ 18 tuổi trở lên có quyền đặt tour</li>
        <li>Khách hàng dưới 18 tuổi cần có sự đồng ý của người giám hộ</li>
        <li>Thông tin đăng ký phải chính xác và đầy đủ</li>
        <li>Booking chỉ được xác nhận sau khi thanh toán thành công</li>
        <li>Đôi Dép Adventure có quyền từ chối booking nếu thông tin không hợp lệ</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Thanh toán</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Giá tour đã bao gồm: HDV, bảo hiểm, nước uống, bữa ăn theo chương trình</li>
        <li>Giá tour chưa bao gồm: chi phí cá nhân, tip, dịch vụ phát sinh ngoài chương trình</li>
        <li>Thanh toán qua: chuyển khoản, VietQR, hoặc tiền mặt</li>
        <li>Khách hàng thanh toán đủ trước ngày khởi hành ít nhất 3 ngày</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Trách nhiệm khách hàng</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Đảm bảo sức khỏe phù hợp với loại tour đã đăng ký</li>
        <li>Tuân thủ nội quy và hướng dẫn của HDV</li>
        <li>Bảo vệ môi trường, không xả rác bừa bãi</li>
        <li>Tôn trọng văn hóa bản địa và thiên nhiên</li>
        <li>Không mang theo vật phẩm nguy hiểm, chất cấm</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Trách nhiệm Đôi Dép Adventure</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Cung cấp dịch vụ đúng như mô tả trên website</li>
        <li>Đảm bảo an toàn trong suốt chuyến đi</li>
        <li>Thông báo kịp thời nếu có thay đổi lịch trình</li>
        <li>Hỗ trợ khách hàng 24/7 trong trường hợp khẩn cấp</li>
        <li>Bảo mật thông tin cá nhân của khách hàng</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Miễn trừ trách nhiệm</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Đôi Dép Adventure không chịu trách nhiệm trong các trường hợp:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Khách hàng không tuân thủ hướng dẫn của HDV dẫn đến tai nạn</li>
        <li>Mất mát tài sản cá nhân do khách hàng không bảo quản</li>
        <li>Chậm trễ do thời tiết, giao thông, hoặc bất khả kháng</li>
        <li>Khách hàng tự ý tách đoàn hoặc đi vào khu vực cấm</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">7. Sở hữu trí tuệ</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Toàn bộ nội dung, hình ảnh, logo trên website thuộc quyền sở hữu của Đôi Dép Adventure. 
        Không được sao chép, sử dụng khi chưa có sự đồng ý bằng văn bản.
      </p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">8. Giải quyết tranh chấp</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Mọi tranh chấp sẽ được giải quyết qua thương lượng. Nếu không đạt được thỏa thuận, 
        tranh chấp sẽ được đưa ra Tòa án nhân dân {address} để giải quyết.
      </p>

      <div className="mt-8 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
        <p className="text-sm text-emerald-800">
          <strong>Liên hệ:</strong> {hotline} | {email} | {address}
        </p>
      </div>
    </>
  );
}
