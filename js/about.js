document.addEventListener('DOMContentLoaded', function() {
    // Animasi teks ketikan
    const typingText = document.getElementById('typing-text');
    const text = "Temui Tim Finansialku";
    let index = 0;
    
    function typeWriter() {
        if (index < text.length) {
            typingText.innerHTML += text.charAt(index);
            index++;
            setTimeout(typeWriter, 100);
        } else {
            // Setelah selesai mengetik, hilangkan kursor berkedip
            setTimeout(() => {
                typingText.style.borderRight = 'none';
            }, 500);
        }
    }
    
    // Mulai animasi ketikan
    typeWriter();
    
    // Interaksi foto developer
    const developerCards = document.querySelectorAll('.developer-card');
    
    developerCards.forEach(card => {
        // Animasi klik pada card
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Animasi scroll untuk elemen
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Terapkan animasi pada elemen yang diinginkan
    document.querySelectorAll('.developer-card, .process-step, .ai-partner-card').forEach(el => {
        el.classList.add('fade-in-up');
        observer.observe(el);
    });

    // Auto slide carousel setiap 5 detik
    const processCarousel = document.getElementById('processCarousel');
    if (processCarousel) {
        let carouselInstance = new bootstrap.Carousel(processCarousel, {
            interval: 5000,
            wrap: true
        });
    }

    // Efek hover untuk social media links di modal
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});