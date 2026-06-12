export default function Loading() {
  return (
    <div className="min-h-screen bg-white animate-pulse">
      <div className="h-20 bg-slate-100" />
      <div className="max-w-6xl mx-auto px-4 py-12">
        <div className="h-8 w-48 bg-slate-200 rounded mb-8" />
        <div className="grid md:grid-cols-2 gap-12">
          <div className="space-y-4">
            <div className="h-4 w-32 bg-slate-200 rounded" />
            <div className="h-10 bg-slate-200 rounded" />
            <div className="h-4 w-32 bg-slate-200 rounded" />
            <div className="h-10 bg-slate-200 rounded" />
            <div className="h-4 w-32 bg-slate-200 rounded" />
            <div className="h-24 bg-slate-200 rounded" />
            <div className="h-10 w-32 bg-slate-200 rounded" />
          </div>
          <div className="space-y-4">
            <div className="h-4 w-32 bg-slate-200 rounded" />
            <div className="h-64 bg-slate-200 rounded" />
          </div>
        </div>
      </div>
    </div>
  );
}
