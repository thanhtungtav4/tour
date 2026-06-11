import { getSettings } from "@/lib/api";

export default async function ExchangePolicyPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in ExchangePolicyPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách đổi vé, bảo lưu</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Đổi ngày khởi hành</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Khách hàng được phép đổi ngày khởi hành theo quy định sau:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Đổi trước 7 ngày so với ngày khởi hành: <strong>miễn phí</strong></li>
        <li>Đổi từ 3-7 ngày: phí đổi <strong>10% giá tour</strong></li>
        <li>Đổi trong vòng 48 giờ: phí đổi <strong>20% giá tour</strong></li>
        <li>Mỗi booking được đổi tối đa <strong>2 lần</strong></li>
        <li>Ngày mới phải còn chỗ trống và trong vòng 3 tháng kể từ ngày gốc</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Bảo lưu tour</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Khách hàng có thể bảo lưu tour nếu không thể tham gia vào ngày đã đăng ký:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Bảo lưu tối đa <strong>6 tháng</strong> kể từ ngày khởi hành gốc</li>
        <li>Phí bảo lưu: <strong>5% giá tour</strong></li>
        <li>Giá tour được giữ nguyên, không áp dụng tăng giá (nếu có)</li>
        <li>Chỉ được bảo lưu 1 lần, không gia hạn thêm</li>
        <li>Không áp dụng cho tour seasonal hoặc tour đặc biệt</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Đổi người tham gia</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Khách hàng có thể chuyển nhượng vé cho người khác:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Thông báo trước ít nhất 48 giờ so với ngày khởi hành</li>
        <li>Không phát sinh phí đổi tên</li>
        <li>Người mới phải đáp ứng điều kiện sức khỏe của tour</li>
        <li>Thông tin người mới sẽ được cập nhật trong hệ thống</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Quy trình đổi vé / bảo lưu</h2>
      <ol className="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
        <li>Gửi yêu cầu qua email hoặc gọi hotline</li>
        <li>Cung cấp mã booking và thông tin mới</li>
        <li>Thanh toán phí đổi/bảo lưu (nếu có)</li>
        <li>Nhận xác nhận qua email trong 24 giờ</li>
      </ol>

      <div className="mt-8 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
        <p className="text-sm text-emerald-800">
          <strong>Hotline hỗ trợ:</strong> {hotline} (T2-CN, 7:00 - 21:00)
        </p>
      </div>
    </>
  );
}
