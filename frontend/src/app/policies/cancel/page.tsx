import { getSettings } from "@/lib/api";

export default async function CancelPolicyPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in CancelPolicyPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";
  const email = settings?.contact_email || "doidepadventure@gmail.com";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách hủy vé</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Quy định hủy tour</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Khách hàng có thể hủy tour đã đăng ký theo các mức phí sau:
      </p>

      <div className="overflow-x-auto mb-6">
        <table className="w-full border-collapse text-sm">
          <thead>
            <tr className="bg-gray-50">
              <th className="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Thời gian hủy trước ngày khởi hành</th>
              <th className="border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Phí hủy</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td className="border border-gray-200 px-4 py-3 text-gray-600">Trên 14 ngày</td>
              <td className="border border-gray-200 px-4 py-3 text-center text-emerald-600 font-medium">Miễn phí (hoàn 100%)</td>
            </tr>
            <tr className="bg-gray-50">
              <td className="border border-gray-200 px-4 py-3 text-gray-600">7 - 14 ngày</td>
              <td className="border border-gray-200 px-4 py-3 text-center text-amber-600 font-medium">Phí 30% giá tour</td>
            </tr>
            <tr>
              <td className="border border-gray-200 px-4 py-3 text-gray-600">3 - 7 ngày</td>
              <td className="border border-gray-200 px-4 py-3 text-center text-orange-600 font-medium">Phí 50% giá tour</td>
            </tr>
            <tr className="bg-gray-50">
              <td className="border border-gray-200 px-4 py-3 text-gray-600">1 - 3 ngày</td>
              <td className="border border-gray-200 px-4 py-3 text-center text-red-600 font-medium">Phí 70% giá tour</td>
            </tr>
            <tr>
              <td className="border border-gray-200 px-4 py-3 text-gray-600">Dưới 24 giờ hoặc không đến</td>
              <td className="border border-gray-200 px-4 py-3 text-center text-red-600 font-medium">Phí 100% giá tour</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Hủy tour do bất khả kháng</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Trong trường hợp tour bị hủy do thiên tai, dịch bệnh, hoặc lý do bất khả kháng khác:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Khách hàng được hoàn 100% giá tour hoặc bảo lưu không thời hạn</li>
        <li>Chi phí đã phát sinh (vé máy bay, khách sạn không hoàn) sẽ được trừ vào tiền hoàn</li>
        <li>Đôi Dép Adventure sẽ thông báo và hỗ trợ khách hàng trong vòng 48 giờ</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Hủy tour do Đôi Dép Adventure</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Nếu Đôi Dép Adventure phải hủy tour do không đủ số lượng khách đăng ký hoặc lý do khác:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Hoàn 100% giá tour trong vòng 3-5 ngày làm việc</li>
        <li>Hỗ trợ 10% giá tour cho lần đặt tiếp theo (áp dụng trong 6 tháng)</li>
        <li>Thông báo trước ít nhất 5 ngày so với ngày khởi hành</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Quy trình hủy tour</h2>
      <ol className="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
        <li>Gửi yêu cầu hủy qua email <strong>{email}</strong> hoặc gọi <strong>{hotline}</strong></li>
        <li>Cung cấp mã đặt tour (booking ID) và lý do hủy</li>
        <li>Nhận xác nhận hủy trong vòng 24 giờ</li>
        <li>Tiền hoàn sẽ được chuyển vào tài khoản đã đăng ký</li>
      </ol>

      <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <p className="text-sm text-blue-800">
          <strong>Mẹo:</strong> Nếu chưa chắc chắn lịch trình, bạn có thể chọn dịch vụ bảo hiểm hủy tour 
          (phí 5% giá tour) để được hoàn 90% khi hủy trước 48 giờ.
        </p>
      </div>
    </>
  );
}
