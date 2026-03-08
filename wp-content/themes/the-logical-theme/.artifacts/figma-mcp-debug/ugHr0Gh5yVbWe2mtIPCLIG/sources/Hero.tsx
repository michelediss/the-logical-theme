import { ArrowRight } from "lucide-react";
import doomedCover from "figma:asset/9963e25c971fd743a09ef72452e0ba90ee368ca2.png";
import inkArt from "figma:asset/75bdf17e66b82a175c08fb4f2abcc1b83f384722.png";

export function Hero() {
  return (
    <section className="relative min-h-screen bg-[#0A0A0A] flex items-end overflow-hidden pt-16">
      <div className="absolute inset-0">
        <img
          src={inkArt}
          alt=""
          className="w-full h-full object-cover opacity-15"
          style={{ filter: "contrast(1.4)" }}
        />
      </div>

      <div className="relative z-10 max-w-[1440px] mx-auto px-6 pb-20 w-full">
        <h1
          style={{
            fontFamily: "var(--font-headline)",
            fontSize: "clamp(4rem, 9vw, 8rem)",
            lineHeight: "0.88",
            letterSpacing: "-0.03em"
          }}
          className="text-[#F5F0E8] font-bold uppercase mb-4"
        >
          DOOMED
          <br />
          <span className="text-[#E8132A]">UNO</span>
        </h1>

        <button
          style={{ fontFamily: "var(--font-headline)" }}
          className="group flex items-center gap-3 bg-[#E8132A] text-[#F5F0E8] px-8 py-4 uppercase text-xs tracking-[0.18em]"
        >
          Scopri il libro
          <ArrowRight size={14} />
        </button>

        <img
          src={doomedCover}
          alt="DOOMED UNO"
          className="w-52 lg:w-64 object-cover"
        />
      </div>
    </section>
  );
}
