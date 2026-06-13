export default function SectionSkeleton() {
  return (
    <section className="bg-white py-16 md:py-20 lg:py-[120px]" aria-hidden="true">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <div className="h-4 w-40 rounded-control bg-surface" />
        <div className="mt-5 h-12 max-w-2xl rounded-control bg-surface" />
        <div className="mt-12 grid gap-5 md:grid-cols-3">
          {[0, 1, 2].map((item) => (
            <div key={item} className="h-64 rounded-card bg-surface" />
          ))}
        </div>
      </div>
    </section>
  );
}
