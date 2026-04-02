/* ═══════════════════════════════════════════════════════════════
   Hôtel Bariba Playa — Shared Components (Nav + Footer)
   ═══════════════════════════════════════════════════════════════ */

const NAV_HTML = `
<div class="topbar-strip">
  <div class="container">
    <div class="topbar-inner">
      <div class="topbar-left">
        <a href="tel:+22997856500"><i class="fa-solid fa-phone"></i>+229 97 85 65 00</a>
        <div class="topbar-divider"></div>
        <a href="mailto:hotelbaribaplaya.28@gmail.com"><i class="fa-regular fa-envelope"></i>hotelbaribaplaya.28@gmail.com</a>
      </div>
      <div class="topbar-right">
        <span><i class="fa-solid fa-clock"></i>Réception 24h/24 — 7j/7</span>
        <div class="topbar-divider"></div>
        <a href="https://www.facebook.com/BaribaPlaya/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f" style="margin:0"></i></a>
        <a href="https://wa.me/22997856500" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp" style="margin:0; color:#25d366"></i></a>
        <a href="https://www.tripadvisor.com/Hotel_Review-g297314-d4721933-Reviews-Hotel_Bariba_Playa-Cotonou_Littoral_Department.html" target="_blank" rel="noopener" aria-label="TripAdvisor"><i class="fa-brands fa-tripadvisor" style="margin:0; color:#34e0a1"></i></a>
      </div>
    </div>
  </div>
</div>
<nav id="navbar">
  <div class="container nav-inner">
    <a href="index.html" class="nav-logo">
      <span class="logo-main">Bariba Playa</span>
      <span class="logo-sub">Hôtel &amp; Lagune — Cotonou</span>
    </a>
    <div class="nav-links">
      <a href="index.html"      data-page="home">Accueil</a>
      <a href="chambres.html"   data-page="chambres">Chambres</a>
      <a href="restaurant.html" data-page="restaurant">Restaurant &amp; Bar</a>
      <a href="services.html"   data-page="services">Événements</a>
      <a href="tourisme.html"   data-page="tourisme">Tourisme</a>
      <a href="galerie.html"    data-page="galerie">Galerie</a>
      <a href="contact.html"    data-page="contact">Contact</a>
    </div>
    <div class="nav-right">
      <a href="tel:+22997856500" class="nav-tel">
        <i class="fa-solid fa-phone"></i> +229 97 85 65 00
      </a>
      <a href="contact.html" class="btn btn-primary btn-sm">
        <i class="fa-regular fa-calendar"></i> Réserver
      </a>
    </div>
    <div class="hamburger" id="hamburger" role="button" aria-label="Menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <span class="mobile-close" id="mobileClose"><i class="fa-solid fa-xmark"></i></span>
  <a href="index.html"      data-page="home">Accueil</a>
  <a href="chambres.html"   data-page="chambres">Chambres</a>
  <a href="restaurant.html" data-page="restaurant">Restaurant &amp; Bar</a>
  <a href="services.html"   data-page="services">Événements</a>
  <a href="tourisme.html"   data-page="tourisme">Tourisme</a>
  <a href="galerie.html"    data-page="galerie">Galerie</a>
  <a href="contact.html"    data-page="contact">Contact</a>
  <div style="padding:12px 0; border-top:1px solid rgba(255,255,255,.1); margin-top:8px;">
    <a href="tel:+22997856500" style="display:flex;align-items:center;gap:10px;font-size:.84rem;color:rgba(255,255,255,.7);padding:8px 0;">
      <i class="fa-solid fa-phone" style="color:var(--gold-l)"></i>+229 97 85 65 00
    </a>
    <a href="https://wa.me/22997856500" style="display:flex;align-items:center;gap:10px;font-size:.84rem;color:rgba(255,255,255,.7);padding:8px 0;" target="_blank">
      <i class="fa-brands fa-whatsapp" style="color:#25d366"></i>WhatsApp
    </a>
  </div>
  <a href="contact.html" class="btn btn-primary mobile-cta">
    <i class="fa-regular fa-calendar-check"></i> Réserver Maintenant
  </a>
</div>`;

const FOOTER_HTML = `
<div class="newsletter-strip">
  <div class="container">
    <div class="newsletter-inner">
      <div class="newsletter-text">
        <h3>Restez Informé de Nos Offres</h3>
        <p>Recevez en exclusivité nos promotions, offres spéciales et actualités de l'hôtel.</p>
      </div>
      <form class="newsletter-form" onsubmit="handleNewsletter(event)">
        <input type="email" placeholder="Votre adresse e-mail" required />
        <button type="submit"><i class="fa-regular fa-paper-plane"></i> S'inscrire</button>
      </form>
    </div>
  </div>
</div>
<footer id="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="brand-name">Bariba Playa</div>
        <span class="brand-tagline">Hôtel &amp; Lagune — Cotonou, Bénin</span>
        <p>Votre havre de paix au cœur de Cotonou. Authenticité africaine et confort international pour des séjours inoubliables au bord de la lagune de Fidjrossè.</p>
        <div class="footer-socials">
          <a href="https://www.facebook.com/BaribaPlaya/" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="https://wa.me/22997856500" target="_blank" rel="noopener" class="social-btn" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="mailto:hotelbaribaplaya.28@gmail.com" class="social-btn" aria-label="Email"><i class="fa-regular fa-envelope"></i></a>
          <a href="https://www.tripadvisor.com/Hotel_Review-g297314-d4721933-Reviews-Hotel_Bariba_Playa-Cotonou_Littoral_Department.html" target="_blank" rel="noopener" class="social-btn" aria-label="TripAdvisor"><i class="fa-brands fa-tripadvisor"></i></a>
        </div>
        <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
          <div style="background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:8px 14px; font-size:.7rem; color:rgba(255,255,255,.6); display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-star" style="color:#34e0a1"></i> 5/5 TripAdvisor
          </div>
          <div style="background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:8px 14px; font-size:.7rem; color:rgba(255,255,255,.6); display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-award" style="color:var(--gold-l)"></i> Hôtel 3 Étoiles
          </div>
        </div>
      </div>
      <div class="footer-col">
        <h5>L'Hôtel</h5>
        <ul>
          <li><a href="index.html"><i class="fa-solid fa-chevron-right"></i> Accueil</a></li>
          <li><a href="chambres.html"><i class="fa-solid fa-chevron-right"></i> Chambres &amp; Suites</a></li>
          <li><a href="restaurant.html"><i class="fa-solid fa-chevron-right"></i> Restaurant &amp; Bar La Playa</a></li>
          <li><a href="services.html"><i class="fa-solid fa-chevron-right"></i> Séminaires &amp; Événements</a></li>
          <li><a href="services.html"><i class="fa-solid fa-chevron-right"></i> Services &amp; Équipements</a></li>
          <li><a href="galerie.html"><i class="fa-solid fa-chevron-right"></i> Galerie Photos</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Informations</h5>
        <ul>
          <li><a href="contact.html"><i class="fa-solid fa-chevron-right"></i> Réserver une Chambre</a></li>
          <li><a href="contact.html"><i class="fa-solid fa-chevron-right"></i> Réserver une Table</a></li>
          <li><a href="contact.html"><i class="fa-solid fa-chevron-right"></i> Check-in dès 14h00</a></li>
          <li><a href="contact.html"><i class="fa-solid fa-chevron-right"></i> Check-out avant 12h00</a></li>
          <li><a href="services.html"><i class="fa-solid fa-chevron-right"></i> Navette Aéroport</a></li>
          <li><a href="services.html"><i class="fa-solid fa-chevron-right"></i> Parking Gratuit</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Nous Contacter</h5>
        <div class="footer-contact-item">
          <div class="ico"><i class="fa-solid fa-location-dot"></i></div>
          <span>Rue 874 Fidjrossè, Route des Pêches<br>BP 2321, Cotonou, Bénin</span>
        </div>
        <div class="footer-contact-item">
          <div class="ico"><i class="fa-solid fa-phone"></i></div>
          <div>
            <a href="tel:+22997856500">+229 97 85 65 00</a><br>
            <a href="tel:+22996691110" style="font-size:.8rem;">+229 96 69 11 10</a>
          </div>
        </div>
        <div class="footer-contact-item">
          <div class="ico"><i class="fa-brands fa-whatsapp"></i></div>
          <a href="https://wa.me/22997856500" target="_blank">WhatsApp : +229 97 85 65 00</a>
        </div>
        <div class="footer-contact-item">
          <div class="ico"><i class="fa-regular fa-envelope"></i></div>
          <a href="mailto:hotelbaribaplaya.28@gmail.com">hotelbaribaplaya.28@gmail.com</a>
        </div>
        <div class="footer-contact-item">
          <div class="ico"><i class="fa-solid fa-map"></i></div>
          <a href="https://maps.google.com/?q=Hotel+Bariba+Playa+Cotonou" target="_blank" rel="noopener">Voir sur Google Maps</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Hôtel Bariba Playa — Cotonou, Bénin. Tous droits réservés.</p>
      <div class="footer-policies">
        <a href="#">Politique de Confidentialité</a>
        <a href="#">Conditions Générales</a>
        <a href="#">Politique d'Annulation</a>
        <a href="admin/login.php">Administration</a>
      </div>
    </div>
  </div>
</footer>`;

const FLOATING_HTML = `
<div class="floating-cta">
  <button class="float-btn back-top" id="backTop" aria-label="Haut de page">
    <i class="fa-solid fa-chevron-up"></i>
  </button>
  <a href="tel:+22997856500" class="float-btn float-phone" aria-label="Appeler">
    <i class="fa-solid fa-phone"></i>
  </a>
  <a href="https://wa.me/22997856500?text=Bonjour%2C%20je%20souhaite%20r%C3%A9server%20une%20chambre%20%C3%A0%20l'H%C3%B4tel%20Bariba%20Playa."
     target="_blank" rel="noopener" class="float-btn float-whatsapp" aria-label="WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
  </a>
</div>`;

/* ─── Inject Components ─────────────────────────────────────────── */
function initComponents(activePage) {
  // Inject nav
  const navPh = document.getElementById('nav-placeholder');
  if (navPh) navPh.innerHTML = NAV_HTML;

  // Inject footer
  const footerPh = document.getElementById('footer-placeholder');
  if (footerPh) footerPh.innerHTML = FOOTER_HTML;

  // Inject floating CTA
  const floatingPh = document.getElementById('floating-placeholder');
  if (floatingPh) floatingPh.innerHTML = FLOATING_HTML;

  // Set active nav link
  if (activePage) {
    document.querySelectorAll('[data-page]').forEach(el => {
      if (el.dataset.page === activePage) el.classList.add('active');
    });
  }

  // Navbar scroll behavior
  const navbar = document.getElementById('navbar');
  const isHero = document.getElementById('hero');
  const backTop = document.getElementById('backTop');

  if (navbar) {
    if (!isHero) navbar.classList.add('solid');
    window.addEventListener('scroll', () => {
      if (isHero) {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
      }
      if (backTop) backTop.classList.toggle('visible', window.scrollY > 400);
    });
  }

  // Back to top
  if (backTop) {
    backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // Hamburger
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileClose = document.getElementById('mobileClose');
  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      mobileMenu.classList.add('open');
      hamburger.classList.add('open');
    });
    mobileClose?.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      hamburger.classList.remove('open');
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        hamburger.classList.remove('open');
      });
    });
  }

  // Scroll reveal
  const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
  if (revealEls.length) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    revealEls.forEach(el => observer.observe(el));
  }
}

function handleNewsletter(e) {
  e.preventDefault();
  const input = e.target.querySelector('input');
  const btn   = e.target.querySelector('button');
  btn.innerHTML = '<i class="fa-solid fa-check"></i> Inscrit !';
  btn.style.background = '#2d7a5f';
  input.value = '';
  setTimeout(() => {
    btn.innerHTML = '<i class="fa-regular fa-paper-plane"></i> S\'inscrire';
    btn.style.background = '';
  }, 3000);
}
