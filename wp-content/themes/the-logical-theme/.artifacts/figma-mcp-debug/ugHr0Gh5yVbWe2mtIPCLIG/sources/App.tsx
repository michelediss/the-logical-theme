import "../styles/fonts.css";
import { SputnikHeader } from "./components/SputnikHeader";
import { Hero } from "./components/Hero";
import { NewReleases } from "./components/NewReleases";
import { FeaturedAuthors } from "./components/FeaturedAuthors";
import { CatalogGrid } from "./components/CatalogGrid";
import { IndieScene } from "./components/IndieScene";
import { Festival } from "./components/Festival";
import { Newsletter } from "./components/Newsletter";
import { SputnikFooter } from "./components/SputnikFooter";

export default function App() {
  return (
    <div
      className="min-h-screen"
      style={{ fontFamily: "var(--font-body)", backgroundColor: "#0A0A0A" }}
    >
      <SputnikHeader />
      <main>
        <Hero />
        <NewReleases />
        <FeaturedAuthors />
        <CatalogGrid />
        <IndieScene />
        <Festival />
        <Newsletter />
      </main>
      <SputnikFooter />
    </div>
  );
}
