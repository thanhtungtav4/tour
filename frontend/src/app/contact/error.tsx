"use client";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50">
      <div className="text-center max-w-md mx-auto px-4">
        <h2 className="text-2xl font-bold text-slate-800 mb-4">
          Đã xảy ra lỗi!
        </h2>
        <p className="text-slate-600 mb-6">
          Rất tiếc, trang liên hệ đang gặp sự cố. Vui lòng thử lại sau.
        </p>
        <button
          onClick={reset}
          className="px-6 py-3 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors"
        >
          Thử lại
        </button>
      </div>
    </div>
  );
}
