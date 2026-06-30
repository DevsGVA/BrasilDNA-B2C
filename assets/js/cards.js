import { $, $$, pRM, isMobile, injectStyle } from './utils.js';

export function initTilt() {
   if (pRM() || isMobile()) return;
   $$(".pillar-card").forEach((card) => {
      card.style.willChange = "transform";
      card.addEventListener("mousemove", (e) => {
         const r  = card.getBoundingClientRect();
         const dx = (e.clientX - r.left - r.width  / 2) / (r.width  / 2);
         const dy = (e.clientY - r.top  - r.height / 2) / (r.height / 2);
         card.style.transform  = `perspective(900px) rotateX(${-dy * 7}deg) rotateY(${dx * 7}deg) translateZ(6px)`;
         card.style.transition = "transform .1s ease";
      });
      card.addEventListener("mouseleave", () => {
         card.style.transition = "transform .5s cubic-bezier(.34,1.56,.64,1)";
         card.style.transform  = "";
      });
   });
}

export function initDestCards() {
   $$(".dest-card").forEach((card) => {
      const media = $(".dest-media", card);
      const img   = $("img", card);
      if (!media || !img) return;
      img.style.transition = "transform .7s cubic-bezier(.25,.8,.25,1)";
      card.addEventListener("mouseenter", () => { img.style.transform = "scale(1.07)"; });
      card.addEventListener("mouseleave", () => { img.style.transform = ""; });
   });
}

export function initNewsCards() {
   const palette = ["#1a7a40", "#c8102e", "#f9b000", "#036830"];
   $$(".news-card").forEach((card, i) => {
      card.style.setProperty("--card-accent", palette[i % palette.length]);
   });

   injectStyle("news-accent", `
    .news-card {
      border-top: 3px solid transparent;
      transition: border-color .3s, transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
    }
    .news-card:hover {
      border-top-color: var(--card-accent, var(--green-600));
      transform: translateY(-8px);
    }
  `);
}

export function initClientModal() {
   const modal = document.getElementById("clientModal");
   if (!modal) return;

   const backdrop  = modal.querySelector(".client-modal__backdrop");
   const closeBtn  = modal.querySelector(".client-modal__close");
   const logoEl    = document.getElementById("clientModalLogo");
   const initials  = document.getElementById("clientModalInitials");
   const nameEl    = document.getElementById("clientModalName");
   const descEl    = document.getElementById("clientModalDesc");
   const linkEl    = document.getElementById("clientModalLink");
   const linkText  = document.getElementById("clientModalLinkText");
   const videoWrap = document.getElementById("clientModalVideo");
   const socialWrap = document.getElementById("clientModalSocial");
   const fbEl      = document.getElementById("clientModalFacebook");
   const igEl      = document.getElementById("clientModalInstagram");
   const liEl      = document.getElementById("clientModalLinkedin");
   const ytEl      = document.getElementById("clientModalYoutube");
   let lastFocused = null;

   function openModal(card) {
      const name   = card.dataset.name   || "";
      const logo   = card.dataset.logo   || "";
      const desc   = card.dataset.desc   || "";
      const site   = card.dataset.site   || "";
      const iframe = card.dataset.iframe || "";

      nameEl.textContent = name;
      descEl.innerHTML   = desc || "Parceiro estratégico do Brasil DNA.";
      if (linkText) linkText.textContent = "Explore " + name;

      if (logo) {
         logoEl.src = logo; logoEl.alt = name; logoEl.hidden = false;
         if (initials) initials.hidden = true;
      } else {
         logoEl.hidden = true;
         if (initials) initials.hidden = true;
      }

      if (videoWrap) {
         if (iframe) {
            videoWrap.innerHTML = iframe.trimStart().startsWith("<")
               ? iframe
               : `<iframe src="${iframe}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            videoWrap.hidden = false;
         } else {
            videoWrap.innerHTML = ""; videoWrap.hidden = true;
         }
      }

      linkEl.href = site || "#"; linkEl.hidden = !site;

      const fb = card.dataset.facebook  || "";
      const ig = card.dataset.instagram || "";
      const li = card.dataset.linkedin  || "";
      const yt = card.dataset.youtube   || "";
      if (fbEl) { fbEl.href = fb; fbEl.style.display = fb ? "" : "none"; }
      if (igEl) { igEl.href = ig; igEl.style.display = ig ? "" : "none"; }
      if (liEl) { liEl.href = li; liEl.style.display = li ? "" : "none"; }
      if (ytEl) { ytEl.href = yt; ytEl.style.display = yt ? "" : "none"; }
      if (socialWrap) socialWrap.style.display = (fb || ig || li || yt) ? "" : "none";

      lastFocused = document.activeElement;
      modal.removeAttribute("hidden");
      modal.offsetHeight;
      modal.classList.add("is-open");
      closeBtn.focus();
      document.body.style.overflow = "hidden";
   }

   function closeModal() {
      modal.classList.remove("is-open");
      if (videoWrap) { videoWrap.innerHTML = ""; videoWrap.hidden = true; }
      setTimeout(() => {
         modal.setAttribute("hidden", "");
         document.body.style.overflow = "";
         lastFocused && lastFocused.focus();
      }, 350);
   }

   document.addEventListener("click", (e) => {
      const card = e.target.closest("[data-modal-trigger]");
      if (card) openModal(card);
   });
   document.addEventListener("keydown", (e) => {
      if ((e.key === "Enter" || e.key === " ") && e.target.closest("[data-modal-trigger]")) {
         e.preventDefault(); openModal(e.target.closest("[data-modal-trigger]"));
      }
      if (e.key === "Escape" && modal.classList.contains("is-open")) closeModal();
   });
   closeBtn  && closeBtn.addEventListener("click", closeModal);
   backdrop  && backdrop.addEventListener("click", closeModal);
}
