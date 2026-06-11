import { Suspense } from "react";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { BookingSuccessContent } from "@/components/BookingSuccessContent";

interface BookingSuccessPageProps {
  searchParams: Promise<{
    bookingId?: string;
    tour?: string;
    date?: string;
    participants?: string;
    total?: string;
    email?: string;
  }>;
}

export default async function BookingSuccessPage({ searchParams }: BookingSuccessPageProps) {
  const params = await searchParams;
  const bookingId = params.bookingId || "NTR-XXXXXXXX";
  const tourName = params.tour || "Tour";
  const date = params.date || "";
  const participants = params.participants || "1";
  const total = params.total || "0";
  const email = params.email || "";

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />

      <main className="pt-[81px] pb-16">
        <Suspense
          fallback={
            <div className="container mx-auto px-4 py-8 max-w-3xl text-center">
              <div className="animate-pulse space-y-4">
                <div className="h-24 bg-gray-200 rounded-full mx-auto w-24" />
                <div className="h-8 bg-gray-200 rounded w-64 mx-auto" />
                <div className="h-4 bg-gray-200 rounded w-96 mx-auto" />
              </div>
            </div>
          }
        >
          <BookingSuccessContent
            bookingId={bookingId}
            tourName={tourName}
            date={date}
            participants={participants}
            total={total}
            email={email}
          />
        </Suspense>
      </main>

      <Footer />
    </div>
  );
}
