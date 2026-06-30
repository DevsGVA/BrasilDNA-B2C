import { $, $$, injectStyle } from './utils.js';

export function initHeader() {
   const header = $("#siteHeader");
   const toggle = $("#navToggle");
   const nav    = $("#mainNav");
   if (!header) return;

   injectStyle("header-styles", `

    @media (max-width: 768px) {
      .nav-overlay {
        display: block;
        position: fixed; inset: 0;
        background: transparent;
        z-index: 929;
        opacity: 0; pointer-events: none;
        transition: opacity .35s ease;
      }
      .nav-overlay.is-open { opacity: 1; pointer-events: all; }

      #mainNav {
        position: fixed; top: 0; right: 0;
        height: 100dvh; width: min(80vw, 300px);
        z-index: 960;
        background: var(--green-900, #012a15);
        flex-direction: column;
        justify-content: center; align-items: flex-start;
        gap: 28px; padding: 40px 36px;
        transform: translateX(110%);
        transition: transform .42s cubic-bezier(.25,.8,.25,1);
        box-shadow: -8px 0 40px rgba(0,0,0,.4);
        z-index: 940;
        display: flex !important;
      }
      #mainNav.is-open { transform: translateX(0); }
      #mainNav > a:not(.nav-cta) {
        color: rgba(255,255,255,.85) !important;
        font-size: 1.15rem;
      }
      #mainNav > a:not(.nav-cta)::after { display: none; }
      .nav-cta { align-self: flex-start; }

      .nav-toggle span {
        display: block; width: 26px; height: 2px;
        background: currentColor; border-radius: 2px;
        transition: transform .3s ease, opacity .25s;
        transform-origin: center;
      }
      .nav-toggle.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
      .nav-toggle.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
      .nav-toggle.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    }
  `);

   const overlay = document.createElement("div");
   overlay.className = "nav-overlay";
   document.body.appendChild(overlay);

   let rafId = null;

   const handleScroll = () => {
      header.classList.toggle("scrolled", window.scrollY > 60);
      rafId = null;
   };

   window.addEventListener("scroll", () => {
      if (!rafId) rafId = requestAnimationFrame(handleScroll);
   }, { passive: true });
   handleScroll();

   function closeNav() {
      toggle?.setAttribute("aria-expanded", "false");
      nav?.classList.remove("is-open");
      toggle?.classList.remove("is-open");
      overlay.classList.remove("is-open");
      document.body.classList.remove("nav-open");
   }
   function openNav() {
      toggle?.setAttribute("aria-expanded", "true");
      nav?.classList.add("is-open");
      toggle?.classList.add("is-open");
      overlay.classList.add("is-open");
      document.body.classList.add("nav-open");
   }

   toggle?.addEventListener("click", () => {
      toggle.getAttribute("aria-expanded") === "true" ? closeNav() : openNav();
   });
   overlay.addEventListener("click", closeNav);
   document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeNav(); });
   nav?.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => closeNav()));
}
