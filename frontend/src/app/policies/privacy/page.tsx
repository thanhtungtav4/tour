export default function PrivacyPolicyPage() {
  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách bảo mật</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Thu thập thông tin</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Đôi Dép Adventure thu thập các thông tin cá nhân sau khi bạn đăng ký tour:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Họ và tên, số điện thoại, email</li>
        <li>Ngày sinh, giới tính (cho bảo hiểm du lịch)</li>
        <li>Thông tin thanh toán (xử lý qua cổng thanh toán bảo mật)</li>
        <li>Thông tin sức khỏe cơ bản (dị ứng, bệnh nền - nếu có)</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Mục đích sử dụng</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Thông tin được sử dụng cho các mục đích:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Xác nhận booking và liên hệ trước tour</li>
        <li>Mua bảo hiểm du lịch</li>
        <li>Gửi thông tin ưu đãi, khuyến mãi (nếu khách hàng đồng ý)</li>
        <li>Cải thiện chất lượng dịch vụ</li>
        <li>Giải quyết khiếu nại, tranh chấp</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Bảo vệ thông tin</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Dữ liệu được mã hóa SSL/TLS khi truyền tải</li>
        <li>Thông tin thanh toán không được lưu trữ trên server của Đôi Dép Adventure</li>
        <li>Chỉ nhân viên được ủy quyền mới truy cập dữ liệu cá nhân</li>
        <li>Không bán, chia sẻ thông tin cho bên thứ ba trừ khi có sự đồng ý</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Chia sẻ thông tin</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Đôi Dép Adventure chỉ chia sẻ thông tin trong các trường hợp:
      </p>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Với công ty bảo hiểm để mua bảo hiểm du lịch</li>
        <li>Với HDV để liên hệ và hỗ trợ khách hàng trong tour</li>
        <li>Theo yêu cầu của cơ quan pháp luật có thẩm quyền</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Quyền của khách hàng</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Yêu cầu truy cập, chỉnh sửa hoặc xóa thông tin cá nhân</li>
        <li>Từ chối nhận email marketing bất cứ lúc nào</li>
        <li>Yêu cầu bản sao dữ liệu cá nhân đã lưu trữ</li>
        <li>Khiếu nại nếu phát hiện thông tin bị sử dụng sai mục đích</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Liên hệ</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Mọi thắc mắc về chính sách bảo mật, vui lòng liên hệ:
      </p>
      <ul className="list-none text-gray-600 space-y-1 mb-4">
        <li><strong>Email:</strong> doidepadventure@gmail.com</li>
        <li><strong>Hotline:</strong> 0928 382 087</li>
        <li><strong>Địa chỉ:</strong> TP. Hồ Chí Minh, Việt Nam</li>
      </ul>

      <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <p className="text-sm text-blue-800">
          Đôi Dép Adventure tuân thủ Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân tại Việt Nam.
        </p>
      </div>
    </>
  );
}
