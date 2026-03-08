import { ShoppingBag, ArrowUpRight } from "lucide-react";

const books = [
  {
    id: 1,
    title: "DOOMED UNO",
    author: "Collettivo Doomed",
    price: "€12,00",
    tag: "Fumetto"
  },
  {
    id: 2,
    title: "PUNICA FIDES",
    author: "Vari autori",
    price: "€22,00",
    tag: "Fumetto"
  }
];

export function NewReleases() {
  return (
    <section id="catalogo" className="bg-[#F5F0E8] py-24 px-6">
      <div className="max-w-[1440px] mx-auto">
        <div className="grid grid-cols-12 gap-6 mb-16">
          <div className="col-span-12 md:col-span-8">
            <h2
              style={{
                fontFamily: "var(--font-headline)",
                fontSize: "clamp(2.5rem, 5vw, 4.5rem)",
                lineHeight: "0.92",
                letterSpacing: "-0.02em"
              }}
              className="text-[#0A0A0A] font-bold uppercase"
            >
              Ultime
              <br />
              <span className="text-[#E8132A]">Pubblicazioni</span>
            </h2>
          </div>
          <div className="col-span-12 md:col-span-4 flex md:items-end md:justify-end pb-1">
            <a href="#" className="group flex items-center gap-2 text-xs tracking-widest uppercase">
              Catalogo completo
              <ArrowUpRight size={12} />
            </a>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 lg:gap-5">
          {books.map((book) => (
            <div key={book.id} className="group cursor-pointer">
              <h3>{book.title}</h3>
              <p>{book.author}</p>
              <span>{book.price}</span>
              <button>
                <ShoppingBag size={11} />
                Acquista
              </button>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
