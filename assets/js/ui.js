import { $, $$, injectStyle } from './utils.js';

export function initSmoothScroll() {
   document.addEventListener("click", (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const target = document.querySelector(a.getAttribute("href"));
      if (!target) return;
      e.preventDefault();
      const offset = ($("#siteHeader")?.offsetHeight ?? 72) + 8;
      window.scrollTo({
         top: target.getBoundingClientRect().top + window.scrollY - offset,
         behavior: "smooth",
      });
   });
}

export function initNewsletter() {
   const form = $(".nl-form");
   if (!form) return;

   injectStyle("newsletter-ux", `
    .nl-success {
      display:flex;flex-direction:column;align-items:center;
      gap:16px;padding:32px 0;
      color:var(--gold-light,#ffd14d);text-align:center;
    }
    .nl-success svg { color:var(--gold-light,#ffd14d); }
    .nl-success p   { font-size:1.05rem;margin:0; }
    @keyframes shakeInput {
      0%,100%{ transform:translateX(0); }
      20%    { transform:translateX(-8px); }
      40%    { transform:translateX(8px); }
      60%    { transform:translateX(-5px); }
      80%    { transform:translateX(5px); }
    }
    .shake { animation:shakeInput .45s ease;border-color:var(--red,#c8102e) !important; }
  `);

   form.addEventListener("submit", (e) => {
      e.preventDefault();
      const btn   = $('button[type="submit"]', form);
      const email = form.querySelector('[name="email"]');
      const first = form.querySelector('[name="first_name"]')?.value.trim();

      if (!email?.value.includes("@")) {
         email?.classList.add("shake");
         email?.addEventListener("animationend", () => email.classList.remove("shake"), { once: true });
         return;
      }
      if (btn) { btn.disabled = true; btn.textContent = "Subscribing…"; }

      setTimeout(() => {
         form.innerHTML = `
          <div class="nl-success" role="status">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
              <path d="M7 12.5l3.5 3.5 6-7" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p>Obrigado${first ? ", " + first : ""}! Prepare-se para receber histórias direto do coração do Brasil. 🇧🇷</p>
          </div>`;
      }, 800);
   });
}

export function initActiveNav() {
   const navLinks = $$('.main-nav a[href^="#"]');
   if (!navLinks.length) return;
   const map = new Map(navLinks.map((a) => [a.getAttribute("href").slice(1), a]));

   injectStyle("active-nav", `
    .main-nav a.is-active:not(.nav-cta) { color:var(--gold,#f9b000) !important; }
    .main-nav a.is-active:not(.nav-cta)::after { width:100% !important;background:var(--gold,#f9b000) !important; }
  `);

   const obs = new IntersectionObserver(
      (entries) => {
         entries.forEach(({ isIntersecting, target }) => {
            map.get(target.id)?.classList.toggle("is-active", isIntersecting);
         });
      },
      { threshold: 0.35 },
   );
   $$("section[id], article[id]").forEach((s) => { if (map.has(s.id)) obs.observe(s); });
}

export function initMarquee() {
   const footer = $(".site-footer");
   if (!footer) return;

   const places = [
      "Rio de Janeiro","Salvador","Pantanal","Amazônia","Bonito",
      "Foz do Iguaçu","Lençóis Maranhenses","Fernando de Noronha",
      "Chapada Diamantina","Ouro Preto","Manaus","Recife",
      "Florianópolis","Morro de São Paulo","Jalapão","Serra Gaúcha","Ilhéus",
   ];

   const all   = [...places, ...places];
   const items = all.map((p) => `<span class="ticker-item"><em>✦</em> ${p}</span>`).join("");
   const ticker = document.createElement("div");
   ticker.className = "marquee-ticker";
   ticker.setAttribute("aria-hidden", "true");
   ticker.innerHTML = `<div class="marquee-track">${items}</div>`;
   footer.insertAdjacentElement("beforebegin", ticker);

   injectStyle("marquee", `
    .marquee-ticker {
      background:var(--green-800,#024022);overflow:hidden;
      padding:18px 0;
      border-top:2px solid var(--gold,#f9b000);
      border-bottom:2px solid var(--gold,#f9b000);
    }
    .marquee-track {
      display:flex;gap:0;width:max-content;
      animation:marqueeScroll 38s linear infinite;
    }
    .marquee-ticker:hover .marquee-track { animation-play-state:paused; }
    @keyframes marqueeScroll {
      from { transform:translateX(0); }
      to   { transform:translateX(-50%); }
    }
    .ticker-item {
      display:inline-flex;align-items:center;gap:10px;padding:0 40px;
      font-size:.88rem;font-weight:600;letter-spacing:.06em;
      text-transform:uppercase;color:rgba(255,255,255,.85);white-space:nowrap;
    }
    .ticker-item em { font-style:normal;color:var(--gold,#f9b000);font-size:.65rem; }
    @media (prefers-reduced-motion:reduce) { .marquee-track { animation:none; } }
  `);
}
