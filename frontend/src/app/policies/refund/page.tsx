import { getSettings } from "@/lib/api";

export default async function RefundPolicyPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in RefundPolicyPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";
  const email = settings?.contact_email || "doidepadventure@gmail.com";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách hoàn tiền</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Điều kiện hoàn tiền</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Khách hàng được hoàn tiền trong các trường hợp sau:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Hủy tour theo đúng quy định chính sách hủy vé</li>
        <li>Tour bị hủy do Đôi Dép Adventure hoặc bất khả kháng</li>
        <li>Thanh toán nhầm hoặc trùng booking</li>
        <li>Dịch vụ không được cung cấp như cam kết</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Thời gian hoàn tiền</h2>
      <div className="overflow-x-auto mb-6">
        <table className="w-full border-collapse text-sm">
          <thead>
            <tr className="bg-gray-50">
              <th className="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Phương thức thanh toán</th>
              <th className="border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Thời gian hoàn</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td className="border border-gray-200 px-4 py-3 text-gray-600">Chuyển khoản ngân hàng</td>
              <td className="border border-gray-200 px-4 py-3 text-center font-medium">3-5 ngày làm việc</td>
            </tr>
            <tr className="bg-gray-50">
              <td className="border border-gray-200 px-4 py-3 text-gray-600">VietQR / QR Code</td>
              <td className="border border-gray-200 px-4 py-3 text-center font-medium">1-3 ngày làm việc</td>
            </tr>
            <tr>
              <td className="border border-gray-200 px-4 py-3 text-gray-600">Tiền mặt (tại văn phòng)</td>
              <td className="border border-gray-200 px-4 py-3 text-center font-medium">Hoàn ngay trong ngày</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Quy trình hoàn tiền</h2>
      <ol className="list-decimal pl-6 text-gray-600 space-y-2 mb-4">
        <li>Gửi yêu cầu hoàn tiền kèm mã booking và lý do</li>
        <li>Đôi Dép Adventure xác nhận yêu cầu trong 24 giờ</li>
        <li>Kiểm tra điều kiện hoàn tiền và tính toán số tiền</li>
        <li>Chuyển tiền vào tài khoản khách hàng đã đăng ký</li>
        <li>Gửi email xác nhận đã hoàn tiền</li>
      </ol>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Lưu ý</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Số tài khoản nhận hoàn tiền phải trùng với tài khoản đã thanh toán</li>
        <li>Phí chuyển khoản (nếu có) do Đôi Dép Adventure chịu</li>
        <li>Không hoàn tiền mặt cho trường hợp đã thanh toán online</li>
        <li>Trường hợp tranh chấp, Đôi Dép Adventure sẽ giải quyết trong 7 ngày làm việc</li>
      </ul>

      <div className="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
        <p className="text-sm text-amber-800">
          <strong>Liên hệ:</strong> Nếu có thắc mắc về hoàn tiền, vui lòng gọi <strong>{hotline}</strong> hoặc email <strong>{email}</strong>
        </p>
      </div>
    </>
  );
}
