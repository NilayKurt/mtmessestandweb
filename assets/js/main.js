/* Header scroll effect */
window.addEventListener('scroll', () => {
  const header = document.getElementById('header');
  if (window.scrollY > 100) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});

/* Navbar close on link click (mobile) */
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    const navbar = document.getElementById('navbar');
    if (navbar.classList.contains('show')) {
      navbar.classList.remove('show');
    }
  });
});

/* Init GLightbox */
const lightbox = GLightbox({
  selector: '.glightbox'
});
