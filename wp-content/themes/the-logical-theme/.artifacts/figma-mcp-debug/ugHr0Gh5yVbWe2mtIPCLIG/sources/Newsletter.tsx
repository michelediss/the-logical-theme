import { useState } from "react";
import { ArrowRight } from "lucide-react";

export function Newsletter() {
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (event) => {
    event.preventDefault();
    if (email) {
      setSubmitted(true);
    }
  };

  return (
    <section className="bg-[#E8132A] py-24 px-6 relative overflow-hidden">
      <div className="relative z-10 max-w-[1440px] mx-auto">
        {!submitted ? (
          <form onSubmit={handleSubmit} className="space-y-4">
            <input
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="la-tua@email.it"
              required
            />
            <button type="submit" className="group">
              Iscriviti alla newsletter
              <ArrowRight size={16} />
            </button>
          </form>
        ) : (
          <div>
            <h3>Sei dentro.</h3>
            <p>Benvenuto nell'universo Sputnik Press.</p>
          </div>
        )}
      </div>
    </section>
  );
}
