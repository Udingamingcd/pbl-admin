class RegisterLoadingAnimation {
    constructor() {
        this.loadingScreen = document.getElementById('loading');
        this.progressBar = document.querySelector('.progress-bar');
        this.progress = 0;
        this.maxProgress = 100;
        this.speed = 25;
        this.steps = [
            { name: 'Memuat komponen', progress: 20 },
            { name: 'Menyiapkan form', progress: 40 },
            { name: 'Memuat animasi', progress: 60 },
            { name: 'Menyiapkan validasi', progress: 80 },
            { name: 'Siap mendaftar', progress: 95 }
        ];
        this.currentStep = 0;
        this.stepElements = null;
    }

    init() {
        if (!this.loadingScreen) {
            console.warn('Loading screen element not found');
            this.triggerPageReady();
            return;
        }
        
        this.startLoading();
        this.createLoadingSteps();
    }

    startLoading() {
        const interval = setInterval(() => {
            // Check if we should update step
            if (this.currentStep < this.steps.length && 
                this.progress >= this.steps[this.currentStep].progress) {
                this.updateStep(this.currentStep);
                this.currentStep++;
            }

            // Increment progress
            this.progress += Math.random() * 12;
            
            if (this.progress >= this.maxProgress) {
                this.progress = this.maxProgress;
                clearInterval(interval);
                this.completeLoading();
            }
            
            this.updateProgressBar();
        }, this.speed);
    }

    createLoadingSteps() {
        if (!this.loadingScreen) return;

        const loadingContent = this.loadingScreen.querySelector('.loading-content');
        if (!loadingContent) return;
        
        const stepsContainer = document.createElement('div');
        stepsContainer.className = 'loading-steps mt-4';
        stepsContainer.style.maxWidth = '300px';
        stepsContainer.style.margin = '0 auto';
        
        this.steps.forEach((step, index) => {
            const stepElement = document.createElement('div');
            stepElement.className = 'loading-step mb-2';
            stepElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="step-text small text-white-50">${step.name}</span>
                    <div class="step-indicator">
                        <div class="spinner-border spinner-border-sm text-light d-none" role="status"></div>
                        <i class="fas fa-check text-success d-none"></i>
                    </div>
                </div>
            `;
            stepsContainer.appendChild(stepElement);
        });
        
        loadingContent.appendChild(stepsContainer);
        this.stepElements = stepsContainer.querySelectorAll('.loading-step');
    }

    updateStep(stepIndex) {
        if (!this.stepElements || !this.stepElements[stepIndex]) return;

        const stepElement = this.stepElements[stepIndex];
        const spinner = stepElement.querySelector('.spinner-border');
        const checkIcon = stepElement.querySelector('.fa-check');
        const stepText = stepElement.querySelector('.step-text');
        
        if (!spinner || !checkIcon || !stepText) return;

        // Show processing state
        spinner.classList.remove('d-none');
        stepText.style.opacity = '1';

        // Simulate processing time
        setTimeout(() => {
            spinner.classList.add('d-none');
            checkIcon.classList.remove('d-none');
            stepText.classList.add('text-white');
            stepText.style.fontWeight = '500';
        }, 300);
    }

    updateProgressBar() {
        if (!this.progressBar) return;

        this.progressBar.style.width = `${this.progress}%`;
        this.progressBar.setAttribute('aria-valuenow', this.progress);
        
        // Visual effects based on progress
        if (this.progress > 30) {
            this.progressBar.classList.add('progress-bar-striped');
        }
        if (this.progress > 60) {
            this.progressBar.classList.remove('bg-primary');
            this.progressBar.classList.add('bg-info');
        }
        if (this.progress > 85) {
            this.progressBar.classList.remove('bg-info');
            this.progressBar.classList.add('bg-success');
        }
    }

    completeLoading() {
        // Final progress update
        this.progress = 100;
        this.updateProgressBar();
        
        // Add completion effects
        this.createWelcomeEffect();
        
        setTimeout(() => {
            if (this.loadingScreen) {
                this.loadingScreen.style.opacity = '0';
                
                setTimeout(() => {
                    this.loadingScreen.style.display = 'none';
                    this.triggerPageReady();
                }, 500);
            } else {
                this.triggerPageReady();
            }
        }, 1000);
    }

    createWelcomeEffect() {
        const loadingContent = this.loadingScreen?.querySelector('.loading-content');
        if (!loadingContent) return;
        
        // Create welcome message
        const welcomeMessage = document.createElement('div');
        welcomeMessage.className = 'welcome-message mt-4';
        welcomeMessage.innerHTML = `
            <h5 class="text-white mb-3">Selamat Datang di Finansialku!</h5>
            <p class="text-white-50 small">Mari mulai perjalanan finansial Anda</p>
        `;
        
        loadingContent.appendChild(welcomeMessage);
        
        // Add floating coins animation
        this.createFloatingCoins();
    }

    createFloatingCoins() {
        const loadingContent = this.loadingScreen?.querySelector('.loading-content');
        if (!loadingContent) return;

        const coins = ['💰', '💵', '💳', '🪙', '💎'];
        
        for (let i = 0; i < 15; i++) {
            const coin = document.createElement('div');
            coin.className = 'floating-coin';
            coin.textContent = coins[Math.floor(Math.random() * coins.length)];
            coin.style.cssText = `
                position: absolute;
                font-size: 1.5rem;
                top: ${Math.random() * 100}%;
                left: ${Math.random() * 100}%;
                opacity: 0;
                animation: coin-float ${(Math.random() * 2) + 1}s ease-in-out forwards;
                animation-delay: ${Math.random() * 0.5}s;
            `;
            
            loadingContent.appendChild(coin);
        }

        // Add coin animation styles
        if (!document.querySelector('#coin-styles')) {
            const coinStyles = document.createElement('style');
            coinStyles.id = 'coin-styles';
            coinStyles.textContent = `
                @keyframes coin-float {
                    0% {
                        opacity: 0;
                        transform: translateY(0) scale(0.5);
                    }
                    50% {
                        opacity: 1;
                        transform: translateY(-20px) scale(1);
                    }
                    100% {
                        opacity: 0;
                        transform: translateY(-40px) scale(0.5);
                    }
                }
            `;
            document.head.appendChild(coinStyles);
        }
    }

    triggerPageReady() {
        const event = new CustomEvent('registerPageReady', {
            detail: { timestamp: new Date().toISOString() }
        });
        document.dispatchEvent(event);
    }
}

// Initialize loading animation when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure all elements are ready
    setTimeout(() => {
        const loadingAnimation = new RegisterLoadingAnimation();
        loadingAnimation.init();
    }, 100);
});

// Utility functions (keep but don't auto-initialize)
function makeDraggable(element) {
    if (!element) return;

    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    
    element.onmousedown = dragMouseDown;
    element.ontouchstart = dragTouchStart;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
        
        element.style.cursor = 'grabbing';
        element.style.zIndex = '1000';
    }

    function dragTouchStart(e) {
        e.preventDefault();
        const touch = e.touches[0];
        pos3 = touch.clientX;
        pos4 = touch.clientY;
        document.ontouchend = closeDragElement;
        document.ontouchmove = elementTouchDrag;
        
        element.style.zIndex = '1000';
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        
        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";
    }

    function elementTouchDrag(e) {
        e.preventDefault();
        const touch = e.touches[0];
        pos1 = pos3 - touch.clientX;
        pos2 = pos4 - touch.clientY;
        pos3 = touch.clientX;
        pos4 = touch.clientY;
        
        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
        document.ontouchend = null;
        document.ontouchmove = null;
        
        element.style.cursor = 'grab';
        element.style.zIndex = '';
    }
}

function createRippleEffect(element) {
    if (!element) return;

    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = '0px';
    ripple.style.top = '0px';
    ripple.classList.add('ripple-effect');
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.remove();
        }
    }, 600);
}