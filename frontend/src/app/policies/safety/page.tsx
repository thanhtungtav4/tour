import { getSettings } from "@/lib/api";

export default async function SafetyPolicyPage() {
  let settings = null;
  try {
    settings = await getSettings();
  } catch (err) {
    console.error("Failed to load settings in SafetyPolicyPage", err);
  }

  const hotline = settings?.hotline || "096 180 43 59";

  return (
    <>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Chính sách an toàn</h1>
      <p className="text-sm text-gray-500 mb-8">Cập nhật lần cuối: 01/06/2026</p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Cam kết an toàn</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Đôi Dép Adventure cam kết đảm bảo an toàn tuyệt đối cho tất cả khách hàng tham gia các tour trekking, camping và phiêu lưu. 
        Chúng tôi tuân thủ nghiêm ngặt các quy định an toàn của Tổng cục Du lịch Việt Nam và các tiêu chuẩn quốc tế.
      </p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">2. Hướng dẫn viên (HDV)</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Tất cả HDV đều được chứng nhận nghiệp vụ hướng dẫn du lịch và sơ cấp cứu</li>
        <li>Tỷ lệ HDV/khách: tối thiểu 1/10 cho tour dễ, 1/6 cho tour trung bình và khó</li>
        <li>HDV mang theo bộ sơ cứu y tế và thiết bị liên lạc trong mọi tour</li>
        <li>HDV có quyền hủy tour nếu điều kiện thời tiết hoặc địa hình không an toàn</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Trang thiết bị an toàn</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Dây an toàn, mũ bảo hiểm, gậy trekking được cung cấp miễn phí cho tour khó</li>
        <li>Thiết bị liên lạc vệ tinh cho các tour vùng sâu, vùng xa</li>
        <li>GPS tracking cho tất cả đoàn trong các tour multi-day</li>
        <li>Phương tiện cứu hộ luôn sẵn sàng tại các điểm tập kết</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Bảo hiểm du lịch</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Tất cả khách hàng đều được mua bảo hiểm du lịch cơ bản trong giá tour. 
        Mức bồi thường tối đa 50.000.000đ/người/vụ. Khách hàng có thể nâng cấp bảo hiểm cao cấp 
        với mức bồi thường lên đến 200.000.000đ/người/vụ.
      </p>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Quy định cho khách hàng</h2>
      <ul className="list-disc pl-6 text-gray-600 space-y-2 mb-4">
        <li>Tuân thủ hướng dẫn của HDV trong suốt chuyến đi</li>
        <li>Không tự ý tách đoàn hoặc đi vào khu vực cấm</li>
        <li>Mang trang phục và giày dép phù hợp với loại tour đã đăng ký</li>
        <li>Khai báo trung thực tình trạng sức khỏe trước khi tham gia tour</li>
        <li>Không sử dụng chất kích thích trong suốt chuyến đi</li>
      </ul>

      <h2 className="text-xl font-semibold text-gray-900 mt-8 mb-4">6. Xử lý tình huống khẩn cấp</h2>
      <p className="text-gray-600 leading-relaxed mb-4">
        Trong trường hợp khẩn cấp, HDV sẽ kích hoạt quy trình cứu hộ và liên hệ với cơ quan chức năng địa phương. 
        Đôi Dép Adventure có đường dây nóng 24/7: <strong>{hotline}</strong> để hỗ trợ khách hàng và người thân.
      </p>

      <div className="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
        <p className="text-sm text-amber-800">
          <strong>Lưu ý:</strong> Chính sách này có thể được điều chỉnh tùy theo điều kiện thực tế của từng tour. 
          Vui lòng liên hệ chúng tôi để được tư vấn chi tiết.
        </p>
      </div>
    </>
  );
}
