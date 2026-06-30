export const $   = (sel, ctx = document) => ctx.querySelector(sel);
export const $$  = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
export const pRM = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;
export const isMobile = () => window.matchMedia("(hover: none)").matches;

export function injectStyle(id, css) {
   if (document.getElementById("bdna-style-" + id)) return;
   const s = document.createElement("style");
   s.id = "bdna-style-" + id;
   s.textContent = css;
   document.head.appendChild(s);
}
