const categories = [
  {
    id: 1,
    title: "Fumetti",
    count: "9 titoli"
  },
  {
    id: 2,
    title: "Graphic Novel",
    count: "4 titoli"
  }
];

export function CatalogGrid() {
  return (
    <section className="bg-[#0A0A0A] py-24 px-6 border-t border-[#F5F0E8]/8">
      <div className="max-w-[1440px] mx-auto">
        <h2
          style={{
            fontFamily: "var(--font-headline)",
            fontSize: "clamp(2.5rem, 5vw, 4.5rem)",
            lineHeight: "0.92",
            letterSpacing: "-0.02em"
          }}
          className="text-[#F5F0E8] font-bold uppercase"
        >
          Esplora
          <br />
          <span className="text-[#E8132A]">il Catalogo</span>
        </h2>

        <div className="grid grid-cols-12 gap-4">
          {categories.map((category) => (
            <div key={category.id} className="col-span-12 md:col-span-5">
              <span>{category.count}</span>
              <h3>{category.title}</h3>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
