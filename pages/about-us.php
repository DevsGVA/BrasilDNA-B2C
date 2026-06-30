<?php
/**
 * Brasil DNA — About Us
 * Conteúdo de referência: https://brasildna.com/about-us/
 * Layout segue o mesmo design system de index.php / news.php / post.php
 */

$pageTitle   = 'About Us — Brasil DNA | The Concept & Initiative';
$currentPage = 'about';
require_once __DIR__ . '/../includes/site-header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/about-us.css">

<!-- ===== PAGE HERO ===== -->
<section class="page-hero">
  <div class="page-hero-bg">
    <img src="https://images.unsplash.com/photo-1518639192441-8fce0a366e2e?w=2200&q=80"
         alt="Floresta tropical brasileira vista de cima" class="page-hero-img" fetchpriority="high">
    <div class="page-hero-overlay"></div>
  </div>

  <div class="hero-flag-stripe" aria-hidden="true">
    <span class="stripe stripe--green"></span>
    <span class="stripe stripe--yellow"></span>
    <span class="stripe stripe--green"></span>
  </div>

  <div class="page-hero-body" data-reveal>
    <span class="label-tag label-tag--light">Concept &amp; Initiative</span>
    <h1>The Brasil <em>DNA</em></h1>
    <p class="page-hero-lead">A strategic, innovative project positioning Brazil as a unique and vibrant tourism destination for the world.</p>
  </div>
</section>

<!-- ===== INTRO ===== -->
<section class="about-intro">
  <div class="container about-intro-grid">

    <div class="about-intro-lead" data-reveal>
      <h1>Where identity<br>becomes <em>code</em>.</h1>
      <h3>Concept and Initiative</h3>
    </div>

    <div class="about-intro-copy" data-reveal data-reveal-delay="120">
      <p>The <strong>Brasil DNA</strong> initiative is a strategic, innovative project aimed at positioning Brazil as a unique and vibrant tourism destination globally, with a focus on the North American market — the United States and Canada.</p>
      <p>The concept is rooted in an analogy with the human DNA structure, symbolizing Brazil's core identity and the elements that define its cultural, natural, and experiential uniqueness. Just as DNA is built from four nitrogenous bases — A, G, C, T — that form the genetic code of life, <strong>Brasil DNA</strong> identifies four pillars that define the genetic code of Brazil as a tourism destination.</p>

      <div class="about-stats">
        <div class="about-stat">
          <span class="about-stat-num">4</span>
          <span class="about-stat-label">Genetic Pillars</span>
        </div>
        <div class="about-stat">
          <span class="about-stat-num">2<em>+</em></span>
          <span class="about-stat-label">Markets in Focus</span>
        </div>
        <div class="about-stat">
          <span class="about-stat-num">2027</span>
          <span class="about-stat-label">Full Expansion</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== DNA HELIX VISUAL + CONCEPT BAND ===== -->
<section class="about-concept-band">
  <div class="container">
    <div class="concept-band-inner" data-reveal>
      <div class="concept-band-text">
        <span class="label-tag">The Concept: Brasil DNA</span>
        <h2>Four bases. <em>One genetic code.</em></h2>
        <p>The idea behind "Brasil DNA" draws parallels between Brazil's cultural identity and the building blocks of life. Hover each base to feel the code.</p>
      </div>
      <div class="concept-band-letters">
        <a href="#authenticity-deep" class="cband-letter cband-letter--a" title="Autenticidade">A</a>
        <a href="#gastronomy-deep" class="cband-letter cband-letter--g" title="Gastronomia">G</a>
        <a href="#culture-deep" class="cband-letter cband-letter--c" title="Cultura">C</a>
        <a href="#treasures-deep" class="cband-letter cband-letter--t" title="Tesouros">T</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== PILLAR DEEP-DIVE: AUTENTICIDADE ===== -->
<section class="pillar-deep" id="authenticity-deep">
  <div class="container pillar-deep-grid">

    <div class="pillar-deep-media" data-reveal>
      <div class="pillar-deep-badge" style="background:var(--red);">A</div>
      <img src="https://bureaumundo.com/wp-content/uploads/2026/05/Bahia-Brasil-DNA-2026-2.png?w=1000&q=80"
           alt="Sorriso caloroso e hospitalidade brasileira" loading="lazy">
      <p class="pillar-deep-quote">"Hospitality is a natural expression of everyday life."</p>
    </div>

    <div class="pillar-deep-text" data-reveal data-reveal-delay="120">
      <span class="label-tag">Autenticidade</span>
      <h2><em>Authenticity</em></h2>
      <span class="pillar-deep-kicker">The Warm Soul of Brazil</span>
      <p>Brazilian authenticity is a powerful, intangible quality that can only be understood through experience. It is the genuine, heartfelt warmth of the Brazilian people — a culture where strangers are greeted like friends, and hospitality is a natural expression of everyday life.</p>

      <div class="pillar-deep-list">
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--red);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 21s-8-4.5-8-11a5 5 0 0110-1 5 5 0 0110 1c0 6.5-8 11-8 11z" fill="currentColor"/></svg>
          </div>
          <div>
            <h4>Unparalleled Connection</h4>
            <p>Visitors often speak of an emotional bond with Brazil — a sense of belonging fostered by the openness and friendliness of the people.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--red);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18V5l12-2v13M9 18a3 3 0 11-6 0 3 3 0 016 0zm12-2a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Cultural Warmth</h4>
            <p>From samba echoing through the streets to locals inviting travelers into their homes, authenticity lives in every interaction.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--red);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14h7l-1 8 11-12h-7l1-8z" fill="currentColor"/></svg>
          </div>
          <div>
            <h4>Vibrant Spirit</h4>
            <p>An unshakable positivity and resilience — Brazil's authenticity isn't just witnessed, it's felt deeply and profoundly.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== PILLAR DEEP-DIVE: GASTRONOMIA ===== -->
<section class="pillar-deep" id="gastronomy-deep">
  <div class="container pillar-deep-grid pillar-deep-grid--flip">

    <div class="pillar-deep-media" data-reveal>
      <div class="pillar-deep-badge" style="background:var(--gold);color:var(--ink);">G</div>
      <img src="https://bureaumundo.com/wp-content/uploads/2025/09/Moqueca_de_camarao-Foto_Rosilda_Cruz-97-1-scaled.jpg?w=1000&q=80"
           alt="Gastronomia brasileira colorida e diversa" loading="lazy">
      <p class="pillar-deep-quote">"A perfect marriage of nourishment and narrative."</p>
    </div>

    <div class="pillar-deep-text" data-reveal data-reveal-delay="120">
      <span class="label-tag">Gastronomia</span>
      <h2><em>Gastronomy</em></h2>
      <span class="pillar-deep-kicker">A Feast for the Senses</span>
      <p>Brazilian gastronomy is a celebration of history, biodiversity, and cultural fusion. The flavors, aromas, and stories of Brazilian cuisine make it a journey in itself, offering travelers a deep taste of the country's soul.</p>

      <div class="pillar-deep-list">
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--gold);color:var(--ink);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 12c0-4.97 4.03-9 9-9s9 4.03 9 9-4.03 9-9 9-9-4.03-9-9z" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Cultural Fusion</h4>
            <p>Shaped by indigenous traditions, African influences, and European techniques — a diverse, ever-evolving culinary landscape.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--gold);color:var(--ink);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2v6m0 0a6 6 0 016 6c0 4-2.5 8-6 8s-6-4-6-8a6 6 0 016-6z" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Unique Local Ingredients</h4>
            <p>From the exotic açaí of the Amazon to feijoada and brigadeiros — flavor reflecting the richness of Brazil's ecosystems.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--gold);color:var(--ink);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 21V11a8 8 0 1116 0v10M4 21h16" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Culinary Experiences</h4>
            <p>Cooking classes, local markets, and farm-to-table journeys turn gastronomy into a sensory adventure.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== PILLAR DEEP-DIVE: CULTURA ===== -->
<section class="pillar-deep" id="culture-deep">
  <div class="container pillar-deep-grid">

    <div class="pillar-deep-media" data-reveal>
      <div class="pillar-deep-badge" style="background:var(--green-700);">C</div>
      <img src="https://bureaumundo.com/wp-content/uploads/2026/06/Sp-Aparecida-do-Norte.jpg?w=1000&q=80"
           alt="Cultura e arte de rua brasileira vibrante" loading="lazy">
      <p class="pillar-deep-quote">"History meets innovation, diversity is identity."</p>
    </div>

    <div class="pillar-deep-text" data-reveal data-reveal-delay="120">
      <span class="label-tag">Cultura</span>
      <h2><em>Culture</em></h2>
      <span class="pillar-deep-kicker">The Living Tapestry of Brazil</span>
      <p>Brazil's culture is a dynamic, colorful mosaic of traditions, artistry, and creativity. It is where history meets innovation, and where diversity is celebrated as the essence of identity.</p>

      <div class="pillar-deep-list">
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--green-700);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18V5l12-2v13M9 18a3 3 0 11-6 0 3 3 0 016 0zm12-2a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Artistic Expression</h4>
            <p>From bold murals in São Paulo to delicate Bahian crafts — samba and bossa nova resonate as Brazil's heartbeat.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--green-700);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Historical Heritage</h4>
            <p>Colonial history and indigenous roots intertwine — from Ouro Preto's baroque churches to Quilombola communities.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--green-700);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2l3 7 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1 3-7z" fill="currentColor"/></svg>
          </div>
          <div>
            <h4>Cultural Immersion</h4>
            <p>Capoeira, Afro-Brazilian rituals, and festivals like Parintins reveal the country's multifaceted identity.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== PILLAR DEEP-DIVE: TESOUROS ===== -->
<section class="pillar-deep" id="treasures-deep">
  <div class="container pillar-deep-grid pillar-deep-grid--flip">

    <div class="pillar-deep-media" data-reveal>
      <div class="pillar-deep-badge" style="background:var(--blue-flag);">T</div>
      <img src="https://bureaumundo.com/wp-content/uploads/2024/11/Abismo-Anhumas-Creditos-Site-Abismo-Anhumas-1.jpg?w=1000&q=80"
           alt="Pantanal e biodiversidade dos tesouros naturais do Brasil" loading="lazy">
      <p class="pillar-deep-quote">"Nature's masterpiece, boundless and alive."</p>
    </div>

    <div class="pillar-deep-text" data-reveal data-reveal-delay="120">
      <span class="label-tag">Tesouros</span>
      <h2><em>Treasures</em></h2>
      <span class="pillar-deep-kicker">Nature's Masterpiece</span>
      <p>Brazil's natural treasures are unparalleled, with ecosystems so vast and diverse they seem almost otherworldly. Its biodiversity, landscapes, and conservation efforts showcase a commitment to preserving the earth's most extraordinary wonders.</p>

      <div class="pillar-deep-list">
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--blue-flag);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Natural Icons</h4>
            <p>The Amazon rainforest, the Pantanal, and Iguaçu Falls — each a wonder of nature offering awe-inspiring exploration.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--blue-flag);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2c4 4 6 7.5 6 11a6 6 0 11-12 0c0-3.5 2-7 6-11z" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Sustainable Tourism</h4>
            <p>Brazil leads in eco-tourism — eco-lodges and guided treks inviting travelers to connect in harmony with nature.</p>
          </div>
        </div>
        <div class="pillar-deep-item">
          <div class="pillar-deep-item-icon" style="background:var(--blue-flag);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
          </div>
          <div>
            <h4>Breathtaking Variety</h4>
            <p>Beyond famous landmarks lie countless hidden gems — pristine beaches, lush mountains, untouched forests.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== SUSTAINABILITY ===== -->
<section class="sustainability">
  <div class="sustainability-bg" aria-hidden="true"></div>
  <div class="container sustainability-grid">

    <div class="sustainability-text" data-reveal>
      <span class="label-tag label-tag--light">Sustainability Focus</span>
      <h2>Growth that <em>gives back</em></h2>
      <p>The Brasil DNA initiative incorporates sustainable practices at its core — because the destinations we celebrate deserve to be protected for generations of travelers to come.</p>
    </div>

    <div class="sustainability-cards" data-reveal data-reveal-delay="120">
      <div class="sustain-card">
        <div class="sustain-card-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2c4 4 6 7.5 6 11a6 6 0 11-12 0c0-3.5 2-7 6-11z" stroke="currentColor" stroke-width="1.8"/></svg>
        </div>
        <div>
          <h4>Carbon-Neutral Events</h4>
          <p>Offset programs that support environmental projects across Brazil, balancing impact with intention.</p>
        </div>
      </div>
      <div class="sustain-card">
        <div class="sustain-card-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 21s-8-4.5-8-11a5 5 0 0110-1 5 5 0 0110 1c0 6.5-8 11-8 11z" stroke="currentColor" stroke-width="1.8"/></svg>
        </div>
        <div>
          <h4>Social Impact Campaigns</h4>
          <p>Part of the profits support conservation and community development projects in tourism regions.</p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== IMPACT & FUTURE VISION ===== -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div class="section-head" data-reveal>
      <span class="label-tag">Impact &amp; Future Vision</span>
      <h2>Reimagining how Brazil <em>meets the world</em></h2>
      <p class="section-lead">The Brasil DNA initiative is not just about tourism promotion — it's about celebrating Brazil's uniqueness and fostering sustainable growth, through authentic storytelling and strategic partnerships.</p>
    </div>

    <div class="pillars-grid" style="background:transparent;">
      <article class="pillar-card" data-reveal data-reveal-delay="0" style="background:var(--sand);border-color:var(--sand-deep);">
        <div class="pillar-letter-wrap" style="background:var(--green-700);"><span>1</span></div>
        <div class="pillar-body">
          <h3 style="color:var(--ink);">Strengthen Positioning</h3>
          <p style="color:var(--ink-soft);">Establish Brazil as a top-tier destination in North America, built on a clear and differentiated identity.</p>
        </div>
      </article>

      <article class="pillar-card" data-reveal data-reveal-delay="80" style="background:var(--sand);border-color:var(--sand-deep);">
        <div class="pillar-letter-wrap" style="background:var(--red);"><span>2</span></div>
        <div class="pillar-body">
          <h3 style="color:var(--ink);">Inspire Travelers</h3>
          <p style="color:var(--ink-soft);">Move new and repeat visitors to explore Brazil's unparalleled cultural and natural diversity.</p>
        </div>
      </article>

      <article class="pillar-card" data-reveal data-reveal-delay="160" style="background:var(--sand);border-color:var(--sand-deep);">
        <div class="pillar-letter-wrap" style="background:var(--gold);"><span style="color:var(--ink);">3</span></div>
        <div class="pillar-body">
          <h3 style="color:var(--ink);">Create Lasting Impact</h3>
          <p style="color:var(--ink-soft);">Generate a positive, lasting effect on local communities, trade stakeholders, and the environment.</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ===== TIMELINE — FUTURE PLANS ===== -->
<section class="timeline-section">
  <div class="container">
    <div class="section-head" data-reveal style="margin: 0 auto 0; text-align:center; max-width: 620px;">
      <span class="label-tag">Future Plans</span>
      <h2>Growing in <em>phases</em></h2>
    </div>

    <div class="timeline">
      <div class="timeline-item" data-reveal data-reveal-delay="0">
        <div class="timeline-dot">2025</div>
        <h4>U.S. East Coast &amp; Canada</h4>
        <p>The journey begins where the connection to Brazil already runs deep.</p>
      </div>
      <div class="timeline-item" data-reveal data-reveal-delay="120">
        <div class="timeline-dot">2026</div>
        <h4>U.S. West Coast</h4>
        <p>Expanding the reach, bringing the Brasil DNA story to new audiences.</p>
      </div>
      <div class="timeline-item" data-reveal data-reveal-delay="240">
        <div class="timeline-dot">2027</div>
        <h4>Beyond &amp; Broader Markets</h4>
        <p>Reaching smaller markets across North America with the same warmth.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== VIDEO BAND ===== -->
<section class="about-video-band">
  <div class="container">
    <div class="section-head" data-reveal>
      <span class="label-tag">See It, Feel It</span>
      <h2>The DNA in <em>motion</em></h2>
    </div>
    <div data-reveal data-reveal-delay="100">
      <div class="video-wrap">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/yffDJtjn3y8?si=tbeb-FCOYYbWdEC-" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
      </div>
      <p class="video-caption">What do people who visit Brazil have to say?</p>
    </div>
  </div>
</section>

<!-- ===== TOGETHER: THE ESSENCE ===== -->
<section class="essence">
  <div class="essence-bg" aria-hidden="true">
    <img src="https://images.unsplash.com/photo-1551632811-561732d1e306?w=2200&q=80"
         alt="Pôr do sol sobre paisagem natural brasileira" loading="lazy">
    <div class="essence-overlay"></div>
  </div>

  <div class="container essence-inner" data-reveal>
    <span class="label-tag label-tag--light">Together</span>
    <h2>The essence of <em>Brasil DNA</em></h2>

    <div class="essence-flow">
      <span class="essence-flow-item">Autenticidade welcomes</span>
      <span class="essence-flow-arrow">→</span>
      <span class="essence-flow-item">Gastronomia tantalizes</span>
      <span class="essence-flow-arrow">→</span>
      <span class="essence-flow-item">Cultura immerses</span>
      <span class="essence-flow-arrow">→</span>
      <span class="essence-flow-item">Tesouros amazes</span>
    </div>

    <p class="essence-closing">Together, these elements create a destination that is not just visited but <strong>lived, felt, and cherished</strong>. Brazil is a country of boundless energy and soul, offering travelers a once-in-a-lifetime experience that stays with them forever. This is <strong>Brasil DNA</strong> — the code that defines Brazil as one of the most unique and inspiring destinations in the world.</p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/site-footer.php'; ?>
