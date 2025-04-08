/**
 * Fayda SACCO - Main JavaScript File
 * Contains all the core functionality for the website
 */

class FaydaSacco {
  constructor() {
    this.init();
  }

  init() {
    // Initialize all components
    this.initDateTime();
    this.initLanguageSelector();
    this.initRegistrationForm();
    this.initMobileMenu();
    this.initCookieConsent();
    this.initPasswordToggle();
    this.initTestimonialsCarousel();
    this.initBackToTop();
    this.initLoanCalculator();
    this.initSmoothScrolling();
    this.initVisitorCounter();
    this.initFloatingHeader();
    this.initSubmenuToggle();
  }

  // 1. Date and Time Display
  initDateTime() {
    const updateDateTime = () => {
      const now = new Date();
      const options = { 
        weekday: 'short', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      const datetimeElement = document.getElementById('datetime');
      if (datetimeElement) {
        datetimeElement.textContent = now.toLocaleDateString('en-US', options);
      }
    };

    updateDateTime();
    setInterval(updateDateTime, 60000);
  }

  // 2. Language Selector
  initLanguageSelector() {
    document.querySelectorAll('[data-lang]').forEach(item => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        const lang = e.target.getAttribute('data-lang');
        const dropdownToggle = e.target.closest('.dropdown-menu').previousElementSibling;
        dropdownToggle.innerHTML = `<i class="fas fa-language me-1"></i> ${e.target.textContent}`;
        // Add actual language change logic here
      });
    });
  }

  // 3. Registration Form Validation
  initRegistrationForm() {
    const form = document.getElementById('registrationForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      let isValid = true;

      // Validate required fields
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          isValid = false;
        }
      });

      // Validate email
      const emailField = form.querySelector('input[type="email"]');
      if (emailField && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
        emailField.classList.add('is-invalid');
        isValid = false;
      }

      // Validate password match
      const password = form.querySelector('#password');
      const confirmPassword = form.querySelector('#confirmPassword');
      if (password && confirmPassword && password.value !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
        isValid = false;
      }

      // Validate age
      const dobField = form.querySelector('#dob');
      if (dobField?.value) {
        const dob = new Date(dobField.value);
        const ageDiff = Date.now() - dob.getTime();
        const ageDate = new Date(ageDiff);
        if (Math.abs(ageDate.getUTCFullYear() - 1970) < 18) {
          dobField.classList.add('is-invalid');
          isValid = false;
        }
      }

      if (!isValid) {
        e.preventDefault();
        form.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      form.classList.add('was-validated');
    });
  }

  // 4. Mobile Menu
  initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
      navbarToggler.addEventListener('click', () => {
        document.body.classList.toggle('mobile-menu-open');
        navbarCollapse.classList.toggle('show');
      });
      
      document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => navbarCollapse.classList.remove('show'));
      });
    }
  }

  // 5. Cookie Consent
  initCookieConsent() {
    const cookieConsent = document.getElementById('cookieConsent');
    const acceptCookies = document.getElementById('acceptCookies');
    
    if (cookieConsent && acceptCookies && !localStorage.getItem('cookiesAccepted')) {
      setTimeout(() => cookieConsent.style.display = 'block', 2000);
      
      acceptCookies.addEventListener('click', () => {
        localStorage.setItem('cookiesAccepted', 'true');
        cookieConsent.style.display = 'none';
      });
    }
  }

  // 6. Password Toggle
  initPasswordToggle() {
    document.querySelectorAll('.password-toggle').forEach(field => {
      const toggleBtn = field.nextElementSibling?.classList.contains('toggle-password') 
        ? field.nextElementSibling : null;
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
          const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
          field.setAttribute('type', type);
          const icon = toggleBtn.querySelector('i');
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        });
      }
    });
  }

  // 7. Testimonials Carousel
  initTestimonialsCarousel() {
    const carousel = document.querySelector('#testimonialCarousel');
    if (carousel && typeof bootstrap?.Carousel === 'function') {
      new bootstrap.Carousel(carousel, { interval: 5000, pause: 'hover' });
    }
  }

  // 8. Back to Top Button
  initBackToTop() {
    const backToTopBtn = document.querySelector('.btn-back-to-top');
    if (!backToTopBtn) return;
    
    window.addEventListener('scroll', () => {
      backToTopBtn.classList.toggle('active', window.scrollY > 300);
    });

    backToTopBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 9. Loan Calculator
  initLoanCalculator() {
    const calculator = document.getElementById('loanCalculator');
    if (!calculator) return;
    
    calculator.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const amount = parseFloat(document.getElementById('loanAmount').value);
      const interest = parseFloat(document.getElementById('interestRate').value) / 100 / 12;
      const term = parseInt(document.getElementById('loanTerm').value);
      
      if ([amount, interest, term].some(isNaN) || amount <= 0 || term <= 0) {
        alert('Please enter valid values for all fields');
        return;
      }
      
      const x = Math.pow(1 + interest, term);
      const monthly = (amount * x * interest) / (x - 1);
      const totalInterest = (monthly * term) - amount;
      
      document.getElementById('monthlyPayment').textContent = monthly.toFixed(2);
      document.getElementById('totalInterest').textContent = totalInterest.toFixed(2);
      document.getElementById('loanResult').style.display = 'block';
      
      document.getElementById('loanResult').scrollIntoView({ behavior: 'smooth' });
    });
  }

  // 10. Smooth Scrolling
  initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        const targetId = anchor.getAttribute('href');
        if (targetId === '#') return;
        
        const target = document.querySelector(targetId);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  }

  // 11. Visitor Counter
  initVisitorCounter() {
    const counterElement = document.getElementById('visitor-count');
    if (!counterElement) return;

    // Initialize with default settings or from API
    new VisitorCounter({
      counterElementId: 'visitor-count',
      apiEndpoint: '/api/visitors',
      animationDuration: 2000,
      debug: false
    });
  }

  // 12. Floating Header
  initFloatingHeader() {
    const navbar = document.querySelector('.navbar');
    const topBar = document.querySelector('.top-bar');
    
    if (navbar && topBar) {
      window.addEventListener('scroll', () => {
        const shouldShrink = window.scrollY > 50;
        navbar.classList.toggle('navbar-scrolled', shouldShrink);
        topBar.style.display = shouldShrink ? 'none' : 'block';
      });
    }
  }

  // 13. Submenu Toggle for Mobile
  initSubmenuToggle() {
    document.querySelectorAll('.dropdown-submenu > a').forEach(element => {
      element.addEventListener('click', (e) => {
        if (window.innerWidth < 992) {
          e.preventDefault();
          const submenu = e.target.nextElementSibling;
          submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        }
      });
    });
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new FaydaSacco();
  
  // Update copyright year
  document.getElementById('current-year').textContent = new Date().getFullYear();
});