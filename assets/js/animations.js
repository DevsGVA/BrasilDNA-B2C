import { $, $$, pRM, isMobile, injectStyle } from './utils.js';

export function initReveal() {
   const els = $$("[data-reveal]");
   if (!els.length) return;
   if (pRM()) { els.forEach((el) => el.classList.add("revealed")); return; }

   const obs = new IntersectionObserver(
      (entries) => {
         entries.forEach(({ isIntersecting, target: el }) => {
            if (!isIntersecting) return;
            const delay = parseInt(el.dataset.revealDelay || el.dataset.reveal_delay || "0", 10);
            setTimeout(() => el.classList.add("revealed"), delay);
            obs.unobserve(el);
         });
      },
      { threshold: 0.1, rootMargin: "0px 0px -40px 0px" },
   );
   els.forEach((el) => obs.observe(el));
}

export function initParallax() {
   if (pRM()) return;
   const img = $("#heroImg");
   if (!img) return;
   let raf;
   window.addEventListener("scroll", () => {
      if (!raf) raf = requestAnimationFrame(() => {
         img.style.transform = `translateY(${window.scrollY * 0.28}px)`;
         raf = null;
      });
   }, { passive: true });
}

export function initParticles() {
   if (pRM() || isMobile()) return;
   const hero = $(".hero");
   if (!hero) return;

   injectStyle("particles", `
    .particle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      will-change: transform, opacity;
      z-index: 2;
    }
    @keyframes floatUp {
      0%   { transform: translateY(0) translateX(0) rotate(0deg) scale(1); opacity: .7; }
      50%  { transform: translateY(-45vh) translateX(var(--dx)) rotate(180deg) scale(.85); opacity: .5; }
      100% { transform: translateY(-90vh) translateX(calc(var(--dx) * 2)) rotate(360deg) scale(.6); opacity: 0; }
    }
  `);

   const colors = ["#f9b000", "#c8102e", "#1a7a40", "#ffd14d", "#2d9e58"];
   const sizes  = [5, 7, 4, 9, 6];

   function spawnParticle() {
      const p     = document.createElement("div");
      p.className = "particle";
      const size  = sizes[Math.floor(Math.random() * sizes.length)];
      const color = colors[Math.floor(Math.random() * colors.length)];
      const dx    = (Math.random() - 0.5) * 200;
      const dur   = 6 + Math.random() * 8;
      const delay = Math.random() * 4;
      p.style.cssText = `
        width:${size}px;height:${size}px;background:${color};
        left:${Math.random() * 100}%;bottom:10%;--dx:${dx}px;
        animation:floatUp ${dur}s ${delay}s ease-in-out forwards;opacity:0;
      `;
      hero.appendChild(p);
      setTimeout(() => p.remove(), (dur + delay + 0.5) * 1000);
   }

   for (let i = 0; i < 14; i++) spawnParticle();
   setInterval(spawnParticle, 1400);
}

export function initProgress() {
   const bar = document.createElement("div");
   bar.setAttribute("aria-hidden", "true");
   bar.style.cssText = `
    position:fixed;top:0;left:0;height:3px;width:0%;
    background:linear-gradient(90deg,#1a7a40 0%,#f9b000 50%,#c8102e 100%);
    z-index:9999;pointer-events:none;transition:width .08s linear;
  `;
   document.body.prepend(bar);
   let raf;
   window.addEventListener("scroll", () => {
      if (raf) return;
      raf = requestAnimationFrame(() => {
         const d = document.documentElement;
         bar.style.width = ((window.scrollY / (d.scrollHeight - d.clientHeight)) * 100).toFixed(1) + "%";
         raf = null;
      });
   }, { passive: true });
}

export function initCursor() {
   if (pRM() || isMobile()) return;

   injectStyle("cursor", `
    .cursor-dot {
      position:fixed;top:0;left:0;width:10px;height:10px;border-radius:50%;
      background:var(--gold,#f9b000);pointer-events:none;z-index:99999;
      transform:translate(-50%,-50%);
      transition:width .2s,height .2s,background .3s,opacity .3s;
      mix-blend-mode:multiply;
    }
    .cursor-ring {
      position:fixed;top:0;left:0;width:36px;height:36px;border-radius:50%;
      border:2px solid rgba(249,176,0,.5);pointer-events:none;z-index:99998;
      transform:translate(-50%,-50%);
      transition:width .35s cubic-bezier(.25,.8,.25,1),height .35s,border-color .3s,opacity .3s;
    }
    body:has(a:hover) .cursor-ring,body:has(button:hover) .cursor-ring { width:52px;height:52px;border-color:var(--red,#c8102e); }
    body:has(a:hover) .cursor-dot,body:has(button:hover) .cursor-dot   { background:var(--red,#c8102e);width:8px;height:8px; }
  `);

   const dot  = document.createElement("div"); dot.className  = "cursor-dot";
   const ring = document.createElement("div"); ring.className = "cursor-ring";
   document.body.append(dot, ring);

   let mx = -100, my = -100, rx = -100, ry = -100;
   document.addEventListener("mousemove", (e) => { mx = e.clientX; my = e.clientY; });
   document.addEventListener("mouseleave", () => { dot.style.opacity = "0"; ring.style.opacity = "0"; });
   document.addEventListener("mouseenter", () => { dot.style.opacity = "1"; ring.style.opacity = "1"; });

   (function loop() {
      dot.style.left = mx + "px"; dot.style.top = my + "px";
      rx += (mx - rx) * 0.12; ry += (my - ry) * 0.12;
      ring.style.left = rx + "px"; ring.style.top = ry + "px";
      requestAnimationFrame(loop);
   })();
}

export function initArrow() {
   if (pRM()) return;
   injectStyle("arrow", `
    @keyframes arrowBob {
      0%,100%{ transform:translateX(-50%) translateY(0); }
      50%    { transform:translateX(-50%) translateY(8px); }
    }
    .scroll-arrow { animation:arrowBob 2s ease-in-out infinite; }
    .scroll-arrow:hover { color:var(--gold,#f9b000); }
  `);
}

export function initCounters() {
   const els = $$(".count-up");
   if (!els.length) return;

   const animate = (el) => {
      const target   = parseFloat(el.dataset.target || "0");
      const decimals = parseInt(el.dataset.decimals || "0", 10);
      const duration = 1400;
      const start    = performance.now();

      if (pRM()) { el.textContent = target.toFixed(decimals); return; }

      const tick = (now) => {
         const p     = Math.min((now - start) / duration, 1);
         const eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
         el.textContent = (target * eased).toFixed(decimals);
         if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
   };

   const obs = new IntersectionObserver(
      (entries) => {
         entries.forEach(({ isIntersecting, target: el }) => {
            if (!isIntersecting) return;
            animate(el);
            obs.unobserve(el);
         });
      },
      { threshold: 0.4 },
   );
   els.forEach((el) => obs.observe(el));
}

export function initSpotlightTilt() {
   if (pRM() || isMobile()) return;
   $$(".why-video__frame, .feel-video__frame").forEach((frame) => {
      frame.style.willChange = "transform";
      frame.addEventListener("mousemove", (e) => {
         const r  = frame.getBoundingClientRect();
         const dx = (e.clientX - r.left - r.width  / 2) / (r.width  / 2);
         const dy = (e.clientY - r.top  - r.height / 2) / (r.height / 2);
         frame.style.transition = "transform .1s ease";
         frame.style.transform  = `perspective(1100px) rotateX(${-dy * 4}deg) rotateY(${dx * 4}deg)`;
      });
      frame.addEventListener("mouseleave", () => {
         frame.style.transition = "transform .6s cubic-bezier(.34,1.56,.64,1)";
         frame.style.transform  = "";
      });
   });
}

