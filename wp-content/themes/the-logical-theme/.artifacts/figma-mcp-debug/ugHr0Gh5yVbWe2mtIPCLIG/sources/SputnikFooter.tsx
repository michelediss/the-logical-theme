const footerLinks = {
  Catalogo: ["Fumetti", "Graphic Novel", "Autoproduzioni Amiche"],
  "Autor*": ["Andrea Bruno", "Alpraz", "Francesco Pelosi"],
  Info: ["Chi siamo", "Sputnik Festival", "Contatti"]
};

export function SputnikFooter() {
  return (
    <footer className="bg-[#0A0A0A] border-t border-[#F5F0E8]/10">
      <div className="py-14 px-6">
        <div className="max-w-[1440px] mx-auto grid grid-cols-12 gap-8">
          <div className="col-span-12 md:col-span-4">
            <p>Casa editrice indipendente specializzata in fumetto underground.</p>
          </div>
          <div className="col-span-12 md:col-span-8 grid grid-cols-2 lg:grid-cols-4 gap-8">
            {Object.entries(footerLinks).map(([section, links]) => (
              <div key={section}>
                <div>{section}</div>
                <ul>
                  {links.map((link) => (
                    <li key={link}>{link}</li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
