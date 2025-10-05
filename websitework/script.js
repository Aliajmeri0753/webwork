document.addEventListener('DOMContentLoaded', function() {

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const animateElements = document.querySelectorAll('.animate-on-scroll');
    animateElements.forEach(element => {
        observer.observe(element);
    });

    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString();
            }
        };

        const counterObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        counterObserver.observe(counter);
    });

    const navbar = document.querySelector('.navbar');
    let lastScroll = 0;

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            navbar.style.background = 'rgba(15, 23, 42, 0.98)';
            navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.2)';
        } else {
            navbar.style.background = 'rgba(15, 23, 42, 0.95)';
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }

        lastScroll = currentScroll;
    });

    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            window.scrollTo({
                top: window.innerHeight,
                behavior: 'smooth'
            });
        });
    }

    const searchInput = document.getElementById('searchCourse');
    const filterCategory = document.getElementById('filterCategory');

    if (searchInput && filterCategory) {
        const filterCourses = () => {
            const searchTerm = searchInput.value.toLowerCase();
            const categoryFilter = filterCategory.value;
            const courseCards = document.querySelectorAll('.course-card');

            courseCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const category = card.querySelector('.course-category').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();

                const matchesSearch = title.includes(searchTerm) ||
                                    description.includes(searchTerm) ||
                                    category.includes(searchTerm);
                const matchesCategory = categoryFilter === 'all' ||
                                      category.includes(categoryFilter.toLowerCase());

                const parentCol = card.closest('.col-lg-4, .col-md-6');
                if (matchesSearch && matchesCategory) {
                    parentCol.style.display = 'block';
                } else {
                    parentCol.style.display = 'none';
                }
            });
        };

        searchInput.addEventListener('input', filterCourses);
        filterCategory.addEventListener('change', filterCourses);
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const alertDiv = document.getElementById('alertMessage');

            fetch('contact_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                    contactForm.reset();
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                }
            })
            .catch(error => {
                alertDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    An error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            });
        });
    }

    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarToggler && navbarCollapse) {
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });
        });
    }

    const enrollButtons = document.querySelectorAll('.course-card .btn-primary');
    enrollButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseTitle = this.closest('.course-card').querySelector('h3').textContent;
            alert(`Enrollment feature coming soon for: ${courseTitle}`);
        });
    });

    const allLinks = document.querySelectorAll('a[href^="#"]');
    allLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i.fas');
            if (icon) {
                if (this.classList.contains('collapsed')) {
                    icon.classList.remove('fa-minus-circle');
                    icon.classList.add('fa-question-circle');
                } else {
                    icon.classList.remove('fa-question-circle');
                    icon.classList.add('fa-minus-circle');
                }
            }
        });
    });

    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.display = 'flex';
            this.style.alignItems = 'center';
            this.style.justifyContent = 'center';
            this.alt = 'Image';
        });
    });

    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    const parallaxElements = document.querySelectorAll('.hero-section');
    if (parallaxElements.length > 0) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            parallaxElements.forEach(element => {
                const speed = 0.5;
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    }

    console.log('EduSphere - Learning Management System');
    console.log('All systems initialized successfully!');
});

window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        console.log('Progressive Web App features ready');
    });
}
