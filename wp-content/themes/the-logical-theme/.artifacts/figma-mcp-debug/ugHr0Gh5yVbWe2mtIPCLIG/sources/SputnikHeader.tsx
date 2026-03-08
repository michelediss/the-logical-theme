import { useState } from "react";
import { Search, ShoppingBag, Menu, X } from "lucide-react";
import logoImg from "figma:asset/a720b8c5ed4881b94573d24a5998a5a13f0e7836.png";

export function SputnikHeader() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [cartCount] = useState(0);

  const navLinks = [
    { label: "Home", href: "#" },
    { label: "Catalogo", href: "#catalogo" },
    { label: "Autor*", href: "#autori" },
    { label: "Autoproduzioni", href: "#autoproduzioni" },
    { label: "Chi siamo", href: "#chi-siamo" }
  ];

  return (
    <header
      style={{ fontFamily: "var(--font-headline)" }}
      className="fixed top-0 left-0 right-0 z-50 bg-[#0A0A0A]/96 backdrop-blur-sm border-b border-[#F5F0E8]/10"
    >
      <div className="max-w-[1440px] mx-auto px-6 flex items-center justify-between h-16">
        <a href="#" className="flex items-center">
          <img
            src={logoImg}
            alt="Sputnik Press"
            className="h-8 w-auto"
            style={{ filter: "invert(1)" }}
          />
        </a>

        <nav className="hidden md:flex items-center gap-8">
          {navLinks.map((link) => (
            <a
              key={link.label}
              href={link.href}
              style={{ fontFamily: "var(--font-body)" }}
              className="text-[#F5F0E8]/60 hover:text-[#E8132A] text-xs tracking-widest uppercase transition-colors duration-200"
            >
              {link.label}
            </a>
          ))}
        </nav>
      </div>
    </header>
  );
}
