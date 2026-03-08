const authors = [
  {
    id: 1,
    name: "Andrea Bruno",
    role: "Fumettista · Illustratore"
  },
  {
    id: 2,
    name: "Alpraz",
    role: "Fumettista · Illustratrice · Animazione"
  }
];

export function FeaturedAuthors() {
  return (
    <section id="autori" className="bg-[#0A0A0A] py-24 px-6">
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
          I Nostri
          <br />
          <span className="text-[#F57C20]">Autor*</span>
        </h2>

        <div className="space-y-px">
          {authors.map((author) => (
            <div key={author.id} className="group grid grid-cols-12 gap-0">
              <div className="col-span-8 md:col-span-7 p-5 md:p-7">
                <h3>{author.name}</h3>
                <span>{author.role}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
