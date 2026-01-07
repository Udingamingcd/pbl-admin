document.addEventListener('DOMContentLoaded', function() {
    // Animasi teks ketikan dengan efek yang lebih menarik
    const typingText = document.getElementById('typing-text');
    const texts = [
        "Temui Tim Finansialku",
        "Inovasi dalam Keuangan Digital",
        "Solusi Keuangan yang Terjangkau",
        "Masa Depan Finansial yang Lebih Baik"
    ];
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let isPaused = false;
    
    function typeWriter() {
        const currentText = texts[textIndex];
        
        if (!isDeleting && charIndex <= currentText.length) {
            // Mengetik
            typingText.innerHTML = currentText.substring(0, charIndex);
            charIndex++;
            setTimeout(typeWriter, 100);
        } else if (isDeleting && charIndex >= 0) {
            // Menghapus
            typingText.innerHTML = currentText.substring(0, charIndex);
            charIndex--;
            setTimeout(typeWriter, 50);
        } else if (!isDeleting && charIndex > currentText.length) {
            // Jeda setelah selesai mengetik
            isPaused = true;
            setTimeout(() => {
                isPaused = false;
                isDeleting = true;
                setTimeout(typeWriter, 100);
            }, 2000);
        } else if (isDeleting && charIndex < 0) {
            // Pindah ke teks berikutnya
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
            setTimeout(typeWriter, 500);
        } else {
            setTimeout(typeWriter, 100);
        }
    }
    
    // Mulai animasi ketikan
    typeWriter();
    
    // Interaksi foto developer dengan efek 3D
    const developerCards = document.querySelectorAll('.developer-card');
    
    developerCards.forEach(card => {
        // Efek hover dengan rotasi 3D ringan
        card.addEventListener('mouseenter', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateY = (x - centerX) / 25;
            const rotateX = (centerY - y) / 25;
            
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
        
        // Animasi klik pada card
        card.addEventListener('click', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            }, 150);
        });
    });

    // Animasi counter untuk statistik
    const statNumbers = document.querySelectorAll('.stat-number');
    
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target + (element.getAttribute('data-count') === '100' ? '+' : '');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    // Animasi scroll untuk elemen
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Trigger counter animation for stat numbers
                if (entry.target.classList.contains('stat-number')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);

    // Terapkan animasi pada elemen yang diinginkan
    const animatedElements = document.querySelectorAll('.developer-card, .stat-card, .ai-partner-card, .timeline-item, .section-title');
    animatedElements.forEach(el => {
        el.classList.add('fade-in-up');
        observer.observe(el);
    });
    
    // Amati stat numbers secara terpisah
    statNumbers.forEach(number => {
        observer.observe(number);
    });

    // Efek hover untuk social media links di modal
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px) scale(1.05)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0) scale(1)';
        });
    });

    // Animasi timeline items dengan delay bertahap
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        item.style.transitionDelay = `${index * 0.2}s`;
    });

    // Efek parallax untuk hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero-section');
        const shapes = document.querySelectorAll('.shape');
        
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
        
        shapes.forEach((shape, index) => {
            const speed = 0.1 + (index * 0.05);
            shape.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.02}deg)`;
        });
    });

    // Efek ripple saat mengklik developer card
    developerCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(13, 110, 253, 0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                top: ${y}px;
                left: ${x}px;
                pointer-events: none;
            `;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Tambahkan style untuk efek ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
});