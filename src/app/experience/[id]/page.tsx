"use client";

import { use } from "react";
import Link from "next/link";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ArrowLeftIcon, CalendarIcon, UsersIcon } from "@/components/icons";

const blogPosts = [
  {
    id: 1,
    title: "Mẹo Chọn Giày Khi Đi Trekking Không Bị Đau Chân – Bí Quyết Dân Trekking Cần Biết",
    excerpt: "Việc chọn đúng đôi giày trekking có thể quyết định trải nghiệm của bạn. Hãy cùng Đôi Dép Adventure khám phá những bí quyết...",
    author: "Ne",
    date: "30/1/2026",
    category: "Kinh nghiệm",
    image: "/images/blog-1.jpg",
    content: `
      <p>Việc chọn đúng đôi giày trekking có thể quyết định hoàn toàn trải nghiệm của bạn trên đường đi. Một đôi giày phù hợp không chỉ giúp bạn di chuyển thoải mái mà còn bảo vệ đôi chân khỏi những chấn thương không đáng có.</p>

      <h2>1. Tại sao giày trekking quan trọng?</h2>
      <p>Khác với giày thể thao thông thường, giày trekking được thiết kế đặc biệt để đối phó với địa hình phức tạp: đá sỏi, bùn lầy, suối nước... Đế giày có độ bám cao, cổ giày bảo vệ mắt cá, và chất liệu chống thấm nước.</p>

      <h2>2. Cách chọn size giày phù hợp</h2>
      <p>Một sai lầm phổ biến là chọn giày vừa khít như giày hàng ngày. Khi trekking, chân sẽ sưng lên sau nhiều giờ đi bộ. Hãy chọn giày lớn hơn 0.5-1 size so với bình thường, đặc biệt nếu bạn mang vớ dày.</p>
      <p><strong>Mẹo:</strong> Thử giày vào buổi chiều khi chân đã nở to nhất. Mang vớ trekking và đi thử trong cửa hàng ít nhất 15 phút.</p>

      <h2>3. Loại đế giày</h2>
      <ul>
        <li><strong>Đế Vibram:</strong> Độ bám tốt nhất, bền, phù hợp địa hình đá và bùn</li>
        <li><strong>Đế cao su tổng hợp:</strong> Nhẹ hơn, phù hợp đường mòn dễ</li>
        <li><strong>Đế có rãnh sâu:</strong> Thoát nước tốt, chống trượt trên bề mặt ẩm</li>
      </ul>

      <h2>4. Chất liệu giày</h2>
      <p><strong>Da bò:</strong> Bền, chống nước tốt nhưng nặng và cần thời gian break-in.<br/>
      <strong>Vải tổng hợp:</strong> Nhẹ, thoáng khí, khô nhanh nhưng ít bền hơn.<br/>
      <strong>Da + Vải kết hợp:</strong> Cân bằng giữa độ bền và trọng lượng - lựa chọn phổ biến nhất.</p>

      <h2>5. Những lỗi thường gặp</h2>
      <ul>
        <li>Mang giày mới chưa break-in cho tour dài</li>
        <li>Không buộc dây đúng cách dẫn đến phồng rộp</li>
        <li>Chọn giày không thấm nước cho tour có vượt suối</li>
        <li>Quên mang vớ dự phòng</li>
      </ul>

      <h2>6. Bảo quản giày sau tour</h2>
      <p>Sau mỗi chuyến đi, hãy vệ sinh giày sạch sẽ, để khô tự nhiên (không phơi nắng trực tiếp), và xịt chống thấm định kỳ 2-3 tháng/lần. Điều này giúp giày bền hơn và sẵn sàng cho chuyến đi tiếp theo.</p>

      <p><em>Chúc bạn tìm được đôi giày trekking hoàn hảo cho hành trình sắp tới!</em></p>
    `,
  },
  {
    id: 2,
    title: "Trekking Tự Túc: Lợi Ích & Nguy Hiểm – Những Điều Cần Lưu Ý Trước Chuyến Đi",
    excerpt: "Trekking tự túc mang lại nhiều trải nghiệm độc đáo nhưng cũng tiềm ẩn không ít nguy hiểm. Cùng tìm hiểu...",
    author: "Mi",
    date: "30/1/2026",
    category: "An toàn",
    image: "/images/blog-2.jpg",
    content: `
      <p>Trekking tự túc đang trở thành xu hướng của nhiều bạn trẻ yêu thích khám phá. Tuy nhiên, giữa lợi ích và nguy hiểm chỉ cách nhau một ranh giới mong manh.</p>

      <h2>Lợi ích của trekking tự túc</h2>
      <p><strong>Tự do lịch trình:</strong> Bạn quyết định đi đâu, dừng đâu, ở lại bao lâu. Không bị gò bó theo tour.<br/>
      <strong>Tiết kiệm chi phí:</strong> Không phải trả phí hướng dẫn viên, có thể tự nấu ăn và cắm trại.<br/>
      <strong>Trải nghiệm thực tế:</strong> Tự mình xử lý mọi tình huống, rèn luyện kỹ năng sinh tồn.</p>

      <h2>Nguy hiểm tiềm ẩn</h2>
      <p><strong>Lạc đường:</strong> Đây là rủi ro phổ biến nhất. Nhiều cung trekking không có biển báo rõ ràng.<br/>
      <strong>Thời tiết bất ngờ:</strong> Mưa rừng, sương mù, lũ quét có thể xảy ra mà không báo trước.<br/>
      <strong>Thiếu kỹ năng sơ cứu:</strong> Khi bị thương giữa rừng, không có HDV hỗ trợ.<br/>
      <strong>Động vật hoang dã:</strong> Rắn, côn trùng, lợn rừng... có thể gây nguy hiểm.</p>

      <h2>Chuẩn bị trước khi đi</h2>
      <ul>
        <li>Nghiên cứu kỹ cung đường, tải offline map (Maps.me, Gaia GPS)</li>
        <li>Thông báo lịch trình cho người thân</li>
        <li>Mang đủ nước, lương khô, bộ sơ cứu</li>
        <li>Kiểm tra thời tiết 3 ngày trước khi đi</li>
        <li>Có phương án dự phòng (số điện thoại cứu hộ, đường rút ngắn)</li>
      </ul>

      <h2>Khi nào nên đi theo tour?</h2>
      <p>Nếu bạn là người mới, chưa có kinh nghiệm đi rừng, hoặc đi đến cung đường khó (độ cao > 2000m, địa hình hiểm trở), hãy đi theo tour có HDV. Chi phí bỏ ra xứng đáng với sự an toàn của bạn.</p>

      <p><em>Adventure is out there – but safety comes first!</em></p>
    `,
  },
  {
    id: 3,
    title: "Top 10 Đỉnh Núi Trekking Đẹp Nhất Việt Nam",
    excerpt: "Việt Nam có vô số đỉnh núi đẹp mê hồn, từ Bắc vào Nam. Cùng Đôi Dép Adventure khám phá top 10 đỉnh núi không thể bỏ qua...",
    author: "Admin",
    date: "25/1/2026",
    category: "Địa điểm",
    image: "/images/langbiang.jpg",
    content: `
      <p>Việt Nam sở hữu địa hình đa dạng với hàng trăm đỉnh núi tuyệt đẹp. Dưới đây là top 10 đỉnh núi trekking được yêu thích nhất:</p>

      <h2>1. Fansipan (3143m) - Lào Cai</h2>
      <p>"Nóc nhà Đông Dương" - đỉnh cao nhất bán đảo Đông Dương. Cung trekking qua Tram Ton hoặc Sin Chai đòi hỏi 2-3 ngày.</p>

      <h2>2. Putaleng (3049m) - Lai Châu</h2>
      <p>Đỉnh cao thứ 2 Việt Nam, nổi tiếng với rừng đỗ quyên nở rộ vào tháng 4-5.</p>

      <h2>3. Bạch Mộc Lương Tử (3045m) - Lai Châu</h2>
      <p>Cung trek khó với địa hình dốc đứng, nhưng view săn mây cực đẹp.</p>

      <h2>4. Tà Xùa (2865m) - Sơn La</h2>
      <p>Thiên đường săn mây, "sống lưng khủng long" huyền thoại.</p>

      <h2>5. Langbiang (2163m) - Lâm Đồng</h2>
      <p>Đỉnh núi biểu tượng của Đà Lạt, phù hợp người mới. Đôi Dép Adventure có tour Langbiang hàng tuần!</p>

      <h2>6. Tà Năng - Phan Dũng (1200m) - Bình Thuận</h2>
      <p>Cung trek xuyên đồi cỏ, được mệnh danh "đường mòn đẹp nhất Việt Nam".</p>

      <h2>7. Núi Chúa (1039m) - Ninh Thuận</h2>
      <p>Vườn quốc gia khô hạn nhất Đông Nam Á, hệ sinh thái độc đáo.</p>

      <h2>8. Yên Tử (1068m) - Quảng Ninh</h2>
      <p>Hành hương kết hợp trekking, đỉnh thiêng của Phật giáo Việt Nam.</p>

      <h2>9. Tây Côn Lĩnh (2428m) - Hà Giang</h2>
      <p>Đỉnh cao nhất Hà Giang, rừng nguyên sinh hoang sơ.</p>

      <h2>10. Ngọc Linh (2598m) - Kon Tum</h2>
      <p>Đỉnh cao nhất Tây Nguyên, nơi mọc cây sâm Ngọc Linh quý hiếm.</p>

      <p><em>Bạn đã chinh phục được bao nhiêu đỉnh trong danh sách này?</em></p>
    `,
  },
  {
    id: 4,
    title: "Camping 101: Hướng Dẫn Cho Người Mới Bắt Đầu",
    excerpt: "Bạn mới bắt đầu với camping? Đừng lo lắng! Đôi Dép Adventure sẽ hướng dẫn bạn từ A đến Z để có một chuyến camping hoàn hảo...",
    author: "Hoàng Nam",
    date: "20/1/2026",
    category: "Hướng dẫn",
    image: "/images/bu-gia-map.jpg",
    content: `
      <p>Camping là hoạt động cắm trại qua đêm ngoài trời, kết hợp hoàn hảo với trekking. Nếu bạn chưa từng camping, đây là hướng dẫn toàn tập:</p>

      <h2>1. Chọn địa điểm camping</h2>
      <p>Với người mới, hãy chọn khu cắm trại có sẵn tiện ích (nước sạch, nhà vệ sinh). Sau khi quen dần, bạn có thể thử wild camping.</p>

      <h2>2. Lều trại</h2>
      <p><strong>Lều 2 người:</strong> Phù hợp cho cặp đôi, trọng lượng 2-3kg.<br/>
      <strong>Lều 3-4 người:</strong> Cho nhóm bạn hoặc gia đình.<br/>
      <strong>Lưu ý:</strong> Kiểm tra lều trước khi đi, mang theo cọc dự phòng và dây dù.</p>

      <h2>3. Túi ngủ</h2>
      <p>Chọn túi ngủ phù hợp nhiệt độ địa điểm. Túi ngủ có 3 loại:</p>
      <ul>
        <li><strong>Mùa hè:</strong> Chịu được 15-25°C</li>
        <li><strong>3 mùa:</strong> Chịu được 5-15°C - phổ biến nhất</li>
        <li><strong>Mùa đông:</strong> Chịu được dưới 0°C</li>
      </ul>

      <h2>4. Đồ nấu ăn</h2>
      <p>Bếp gas mini + bình gas + nồi nhỏ là combo cơ bản. Mang theo lương khô, mì, đồ hộp cho bữa đầu tiên.</p>

      <h2>5. An toàn khi camping</h2>
      <ul>
        <li>Không cắm trại gần bờ sông/suội (nguy cơ lũ quét)</li>
        <li>Dọn sạch thức ăn thừa để tránh thú hoang</li>
        <li>Mang đèn pin, còi cứu hộ</li>
        <li>Không đốt lửa trong rừng khô</li>
      </ul>

      <h2>6. Leave No Trace</h2>
      <p>Luôn mang rác về, không xả rác, không phá cây cối. Giữ gìn thiên nhiên cho thế hệ sau.</p>

      <p><em>Chúc bạn có chuyến camping đầu tiên thật đáng nhớ!</em></p>
    `,
  },
  {
    id: 5,
    title: "Những Sai Lầm Thường Gặp Khi Đi Trekking Mùa Mưa",
    excerpt: "Đi trekking mùa mưa có những rủi ro riêng. Hãy tránh những sai lầm phổ biến để chuyến đi của bạn an toàn hơn...",
    author: "Thu Hà",
    date: "15/1/2026",
    category: "An toàn",
    image: "/images/rung-cat-tien.jpg",
    content: `
      <p>Mùa mưa là thời điểm nhiều trekker e ngại, nhưng nếu chuẩn bị đúng, bạn vẫn có thể có chuyến đi tuyệt vời. Dưới đây là những sai lầm cần tránh:</p>

      <h2>1. Không mang áo mưa</h2>
      <p>Đừng nghĩ "trời đẹp mà". Mưa rừng đến rất nhanh. Luôn mang áo mưa loại tốt trong balo, gói trong túi nylon.</p>

      <h2>2. Mang balo không có áo trùm mưa</h2>
      <p>Balo ướt = đồ đạc ướt = khó chịu cả ngày. Mua áo trùm mưa cho balo hoặc dùng túi nylon lớn bọc bên ngoài.</p>

      <h2>3. Đi giày vải trong mưa</h2>
      <p>Giày vải thấm nước nhanh, gây trượt và phồng rộp. Ưu tiên giày da hoặc giày có màng chống thấm (Gore-Tex).</p>

      <h2>4. Không mang quần áo dự phòng</h2>
      <p>Luôn có ít nhất 1 bộ đồ khô trong balo (gói kín trong túi nylon) để thay khi đến trại.</p>

      <h2>5. Xem nhẹ nguy cơ lũ quét</h2>
      <p>Không cắm trại gần suối, sông. Nếu nước suối bắt đầu đục và chảy mạnh, hãy di chuyển lên cao ngay lập tức.</p>

      <h2>6. Không kiểm tra thời tiết</h2>
      <p>Kiểm tra dự báo thời tiết 3 ngày trước khi đi. Nếu có cảnh báo mưa lớn hoặc bão, hãy hoãn chuyến đi.</p>

      <h2>7. Mang đồ điện tử không chống nước</h2>
      <p>Điện thoại, pin dự phòng, máy ảnh cần được bọc trong túi chống nước. Hoặc ít nhất là túi zip.</p>

      <p><em>Mùa mưa không đáng sợ - chỉ cần chuẩn bị kỹ!</em></p>
    `,
  },
  {
    id: 6,
    title: "Trải Nghiệm Trekking Đỉnh Langbiang - Ký Ức Không Quên",
    excerpt: "Chinh phục đỉnh Langbiang 2163m là một trong những trải nghiệm đáng nhớ nhất của nhiều trekker. Cùng lắng nghe...",
    author: "Văn Đức",
    date: "10/1/2026",
    category: "Trải nghiệm",
    image: "/images/ta-cu-ke-ga.jpg",
    content: `
      <p>Langbiang - cái tên đã quá quen thuộc với dân trekking miền Nam. Nhưng mỗi lần leo là một trải nghiệm khác biệt. Đây là câu chuyện của tôi:</p>

      <h2>Khởi đầu</h2>
      <p>5h30 sáng, xe đón tại Bến Thành. Trời còn mờ sương, nhưng ai cũng hào khởi. Sau 4 tiếng chạy xe, chúng tôi đến chân núi lúc 9h30.</p>

      <h2>Chặng 1: Rừng thông</h2>
      <p>30 phút đầu tiên là đường rừng thông mát mẻ. Dốc nhẹ, dễ đi. Mọi người còn cười nói rôm rả.</p>

      <h2>Chặng 2: Dốc đứng</h2>
      <p>Sau 1 tiếng, địa hình bắt đầu khó. Dốc 45-60 độ, rễ cây và đá trơn. Tôi phải dùng gậy trekking để giữ thăng bằng. Đoạn này mất khoảng 2 tiếng.</p>

      <h2>Chặng 3: Đỉnh Langbiang</h2>
      <p>12h30, chúng tôi lên đến đỉnh. Trời quang, view 360 độ tuyệt đẹp. Đà Lạt nằm gọn trong tầm mắt. Cảm giác lúc đó không gì tả nổi - mệt nhưng hạnh phúc!</p>

      <h2>Đỉnh Radar vs Đỉnh Langbiang</h2>
      <p>Nhiều người nhầm lẫn đỉnh Radar (1950m, có thể đi jeep lên) với đỉnh Langbiang thực sự (2163m, phải trekking). Đỉnh Langbiang cao hơn và view đẹp hơn nhiều.</p>

      <h2>Lời khuyên</h2>
      <ul>
        <li>Đi sớm để tránh nắng gắt buổi trưa</li>
        <li>Mang ít nhất 2 lít nước/người</li>
        <li>Thuê gậy trekking nếu chưa quen dốc</li>
        <li>Đi theo tour nếu chưa đi lần đầu</li>
      </ul>

      <p><em>Langbiang không quá khó, nhưng đủ để bạn tự hào khi chinh phục!</em></p>
    `,
  },
];

interface PageProps {
  params: Promise<{ id: string }>;
}

export default function ExperienceDetailPage({ params }: PageProps) {
  const { id } = use(params);
  const postId = parseInt(id);
  const post = blogPosts.find(p => p.id === postId);

  if (!post) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-grow pt-[81px] flex items-center justify-center">
          <div className="text-center py-20">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">Bài viết không tìm thấy</h1>
            <Link href="/experience" className="text-emerald-600 hover:underline inline-flex items-center gap-2">
              <ArrowLeftIcon className="w-4 h-4" />
              Quay lại danh sách blog
            </Link>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-grow pt-[81px]">
        {/* Hero Image */}
        <div className="relative h-64 sm:h-80 lg:h-96 overflow-hidden">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={post.image}
            alt={post.title}
            className="w-full h-full object-cover"
            onError={(e) => {
              const target = e.target as HTMLImageElement;
              target.style.display = 'none';
              const parent = target.parentElement;
              if (parent) parent.style.background = 'linear-gradient(135deg, #16a249 0%, #10b981 100%)';
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
          <div className="absolute bottom-0 left-0 right-0 p-6 lg:p-12">
            <div className="container mx-auto">
              <span className="inline-block px-3 py-1 bg-emerald-500 text-white text-xs font-semibold rounded-full mb-3">
                {post.category}
              </span>
              <h1 className="text-2xl lg:text-4xl font-bold text-white mb-4">
                {post.title}
              </h1>
              <div className="flex items-center gap-4 text-white/80 text-sm">
                <div className="flex items-center gap-2">
                  <div className="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                    <span className="text-white text-xs font-semibold">{post.author[0]}</span>
                  </div>
                  <span>{post.author}</span>
                </div>
                <div className="flex items-center gap-1">
                  <CalendarIcon className="w-4 h-4" />
                  <span>{post.date}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="container mx-auto px-4 py-12">
          <div className="max-w-3xl mx-auto">
            {/* Back Link */}
            <Link href="/experience" className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 mb-8 transition-colors">
              <ArrowLeftIcon className="w-4 h-4" />
              Quay lại danh sách blog
            </Link>

            {/* Article */}
            <article
              className="prose prose-gray max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-strong:text-gray-900 prose-a:text-emerald-600 prose-li:text-gray-600"
              dangerouslySetInnerHTML={{ __html: post.content }}
            />

            {/* Author Box */}
            <div className="mt-12 p-6 bg-gray-50 rounded-2xl border border-gray-100">
              <div className="flex items-center gap-4">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                  <span className="text-white text-xl font-bold">{post.author[0]}</span>
                </div>
                <div>
                  <p className="font-semibold text-gray-900">{post.author}</p>
                  <p className="text-sm text-gray-500">Tác giả Đôi Dép Adventure</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
}
