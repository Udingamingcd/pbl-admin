class LoadingAnimation {
    constructor() {
        this.loadingScreen = document.getElementById('loading');
        this.progressBar = document.querySelector('.progress-bar');
        this.progress = 0;
        this.maxProgress = 100;
        this.speed = 20; // milliseconds per update
    }

    init() {
        this.startLoading();
    }

    startLoading() {
        const interval = setInterval(() => {
            // Simulate realistic loading progress
            this.progress += Math.random() * 15;
            
            if (this.progress >= this.maxProgress) {
                this.progress = this.maxProgress;
                clearInterval(interval);
                this.completeLoading();
            }
            
            this.updateProgressBar();
        }, this.speed);
    }

    updateProgressBar() {
        if (this.progressBar) {
            this.progressBar.style.width = `${this.progress}%`;
            this.progressBar.setAttribute('aria-valuenow', this.progress);
            
            // Add some visual effects based on progress
            if (this.progress > 50) {
                this.progressBar.classList.add('progress-bar-striped');
            }
            if (this.progress > 75) {
                this.progressBar.classList.add('bg-success');
            }
        }
    }

    completeLoading() {
        // Add completion animation
        if (this.loadingScreen) {
            this.loadingScreen.style.opacity = '0';
            
            setTimeout(() => {
                this.loadingScreen.style.display = 'none';
                
                // Trigger custom event for other scripts
                const event = new CustomEvent('loadingComplete', {
                    detail: { page: 'login' }
                });
                document.dispatchEvent(event);
            }, 500);
        }
    }
}

// Initialize loading animation when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const loadingAnimation = new LoadingAnimation();
    loadingAnimation.init();
    
    // Add some interactive elements after loading
    setTimeout(() => {
        addFloatingElementInteractions();
    }, 1000);
});

function addFloatingElementInteractions() {
    const floatingElements = document.querySelectorAll('.floating-element');
    
    floatingElements.forEach((element, index) => {
        // Add random delay for staggered animation
        element.style.animationDelay = `${index * 0.5}s`;
        
        // Make elements draggable
        makeDraggable(element);
        
        // Add click effects
        element.addEventListener('click', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'float 6s ease-in-out infinite';
            }, 10);
            
            // Add ripple effect
            createRippleEffect(this);
        });
    });
}

function makeDraggable(element) {
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
        ripple.remove();
    }, 600);
}

// Add ripple effect styles dynamically
const rippleStyles = `
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = rippleStyles;
document.head.appendChild(styleSheet);  