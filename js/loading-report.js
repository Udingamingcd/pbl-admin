// loading-report.js - Professional Loading Screen untuk Laporan
class ProfessionalReportLoading {
    constructor() {
        this.loadingScreen = document.getElementById('loading');
        this.progressBar = document.querySelector('.improved-progress-bar');
        this.progressPercentage = document.getElementById('progressPercentage');
        this.loadingText = document.getElementById('loadingText');
        this.loadingSubtext = document.getElementById('loadingSubtext');
        this.progress = 0;
        this.stepsCompleted = 0;
        this.totalSteps = 4;
        this.currentStep = 0;
        
        this.professionalSteps = [
            { 
                name: 'Menginisialisasi Sistem Laporan', 
                progress: 20,
                subtext: 'Memuat modul laporan finansial',
                duration: 1200
            },
            { 
                name: 'Memproses Data Laporan', 
                progress: 45,
                subtext: 'Menyusun data untuk laporan lengkap',
                duration: 1500
            },
            { 
                name: 'Membangun Format Laporan', 
                progress: 75,
                subtext: 'Menyiapkan layout dan template profesional',
                duration: 1800
            },
            { 
                name: 'Menyiapkan Ekspor', 
                progress: 90,
                subtext: 'Mempersiapkan opsi download dan print',
                duration: 1200
            }
        ];

        this.reportIcons = ['📋', '📊', '📑', '📈', '📉', '💵', '💳', '🏦'];
    }

    init() {
        this.setupProfessionalLayout();
        this.createFloatingReports();
        this.startLoadingSequence();
    }

    setupProfessionalLayout() {
        // Add professional styling
        this.loadingScreen.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
        
        // Add responsive handling
        window.addEventListener('resize', () => this.handleResize());
        this.handleResize();
    }

    createFloatingReports() {
        // Create floating report elements
        for (let i = 0; i < 6; i++) {
            setTimeout(() => this.createFloatingElement(), i * 300);
        }
    }

    createFloatingElement() {
        const element = document.createElement('div');
        element.className = 'floating-report';
        element.innerHTML = this.getRandomReportIcon();
        
        element.style.cssText = `
            position: fixed;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            font-size: ${Math.random() * 20 + 16}px;
            opacity: ${Math.random() * 0.4 + 0.1};
            animation: professional-float ${Math.random() * 15 + 10}s infinite ease-in-out;
            z-index: 1;
            pointer-events: none;
            user-select: none;
        `;
        
        this.loadingScreen.appendChild(element);
    }

    getRandomReportIcon() {
        const icons = ['📋', '📊', '📑', '📈', '📉', '💵', '💳', '🏦'];
        return icons[Math.floor(Math.random() * icons.length)];
    }

    startLoadingSequence() {
        this.updateProgressSteps();
        
        setTimeout(() => {
            this.executeStepByStep();
        }, 1000);
    }

    executeStepByStep() {
        const executeNextStep = () => {
            if (this.currentStep < this.professionalSteps.length) {
                this.executeStep(this.currentStep).then(() => {
                    this.currentStep++;
                    executeNextStep();
                });
            } else {
                this.completeLoading();
            }
        };
        
        executeNextStep();
    }

    executeStep(stepIndex) {
        return new Promise((resolve) => {
            const step = this.professionalSteps[stepIndex];
            
            // Update UI for current step
            this.updateStepUI(stepIndex, step);
            
            // Simulate processing time
            setTimeout(() => {
                this.stepsCompleted++;
                resolve();
            }, step.duration);
        });
    }

    updateStepUI(stepIndex, step) {
        // Update progress bar
        this.progress = step.progress;
        this.updateProgressBar();
        
        // Update steps visual
        this.updateProgressSteps(stepIndex);
        
        // Update text content
        if (this.loadingText) {
            this.loadingText.innerHTML = `
                <span class="step-indicator">${stepIndex + 1}</span>
                ${step.name}
            `;
        }
        
        if (this.loadingSubtext) {
            this.loadingSubtext.textContent = step.subtext;
        }
        
        // Add micro-interaction
        this.animateCurrentStep(stepIndex);
    }

    updateProgressBar() {
        if (this.progressBar && this.progressPercentage) {
            this.progressBar.style.width = `${this.progress}%`;
            this.progressPercentage.textContent = `${Math.round(this.progress)}%`;
            
            // Update color based on progress
            this.updateProgressBarColor();
            
            // Add shine effect
            this.createShineEffect();
        }
    }

    updateProgressBarColor() {
        if (!this.progressBar) return;
        
        let gradient;
        if (this.progress < 30) {
            gradient = 'linear-gradient(90deg, #ef4444, #f59e0b)';
        } else if (this.progress < 60) {
            gradient = 'linear-gradient(90deg, #f59e0b, #84cc16)';
        } else if (this.progress < 90) {
            gradient = 'linear-gradient(90deg, #84cc16, #10b981)';
        } else {
            gradient = 'linear-gradient(90deg, #10b981, #3b82f6)';
        }

        this.progressBar.style.background = gradient;
    }

    createShineEffect() {
        const shine = document.createElement('div');
        shine.className = 'progress-shine';
        shine.style.cssText = `
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: professional-shine 1.5s ease-out;
        `;
        
        if (this.progressBar.parentElement) {
            this.progressBar.parentElement.appendChild(shine);
            setTimeout(() => shine.remove(), 1500);
        }
    }

    updateProgressSteps(currentStepIndex = -1) {
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            
            if (index < currentStepIndex) {
                step.classList.add('completed');
            } else if (index === currentStepIndex) {
                step.classList.add('active');
            }
        });
    }

    animateCurrentStep(stepIndex) {
        const currentStepElement = document.querySelector(`.step[data-step="${stepIndex + 1}"]`);
        if (currentStepElement) {
            currentStepElement.style.transform = 'scale(1.1)';
            currentStepElement.style.boxShadow = '0 8px 25px rgba(59, 130, 246, 0.5)';
            
            setTimeout(() => {
                currentStepElement.style.transform = 'scale(1.05)';
                currentStepElement.style.boxShadow = '0 4px 15px rgba(59, 130, 246, 0.4)';
            }, 300);
        }
    }

    completeLoading() {
        // Final progress animation
        this.animateTo100Percent().then(() => {
            this.showCompletionCelebration();
            
            setTimeout(() => {
                this.fadeOutLoading();
            }, 2000);
        });
    }

    animateTo100Percent() {
        return new Promise((resolve) => {
            let finalProgress = this.progress;
            const interval = setInterval(() => {
                finalProgress += 1;
                this.progress = finalProgress;
                this.updateProgressBar();
                
                if (finalProgress >= 100) {
                    clearInterval(interval);
                    resolve();
                }
            }, 20);
        });
    }

    showCompletionCelebration() {
        const loadingContent = this.loadingScreen.querySelector('.loading-content');
        
        // Create completion message
        const celebration = document.createElement('div');
        celebration.className = 'completion-celebration';
        celebration.innerHTML = `
            <div class="completion-badge">
                <i class="fas fa-file-alt"></i>
                <h4>Laporan Siap!</h4>
                <p>Sistem laporan finansial telah siap digunakan</p>
            </div>
        `;
        
        loadingContent.appendChild(celebration);
        
        // Update text
        if (this.loadingText) {
            this.loadingText.innerHTML = '<i class="fas fa-rocket me-2"></i>Sistem Siap Digunakan';
        }
        
        if (this.loadingSubtext) {
            this.loadingSubtext.textContent = 'Mengalihkan ke dashboard laporan...';
        }
        
        // Create confetti effect
        this.createProfessionalConfetti();
    }

    createProfessionalConfetti() {
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                this.createConfettiPiece();
            }, i * 100);
        }
    }

    createConfettiPiece() {
        const confetti = document.createElement('div');
        confetti.className = 'professional-confetti';
        confetti.innerHTML = this.getRandomReportIcon();
        
        confetti.style.cssText = `
            position: fixed;
            left: ${Math.random() * 100}%;
            top: -50px;
            font-size: ${Math.random() * 16 + 14}px;
            animation: professional-confetti-fall ${Math.random() * 2 + 2}s ease-in forwards;
            z-index: 2;
            pointer-events: none;
        `;
        
        document.body.appendChild(confetti);
        
        setTimeout(() => {
            confetti.remove();
        }, 3000);
    }

    fadeOutLoading() {
        this.loadingScreen.style.opacity = '0';
        this.loadingScreen.style.transition = 'opacity 0.8s ease';
        
        setTimeout(() => {
            this.loadingScreen.style.display = 'none';
            
            // Dispatch ready event
            const event = new CustomEvent('reportReady', {
                detail: { 
                    timestamp: new Date().toISOString(),
                    loadTime: performance.now(),
                    stepsCompleted: this.stepsCompleted
                }
            });
            document.dispatchEvent(event);
        }, 800);
    }

    handleResize() {
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            this.loadingScreen.classList.add('mobile-view');
        } else {
            this.loadingScreen.classList.remove('mobile-view');
        }
    }
}

// Add professional CSS animations
const professionalStyles = document.createElement('style');
professionalStyles.textContent = `
    @keyframes professional-float {
        0%, 100% { 
            transform: translateY(0px) rotate(0deg) translateX(0px); 
        }
        33% { 
            transform: translateY(-15px) rotate(120deg) translateX(5px); 
        }
        66% { 
            transform: translateY(8px) rotate(240deg) translateX(-5px); 
        }
    }
    
    @keyframes professional-shine {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    @keyframes professional-confetti-fall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
    
    .floating-report {
        animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .professional-confetti {
        animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .step-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    
    .completion-celebration {
        animation: fadeInUp 0.6s ease;
    }
    
    .completion-badge {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        margin-top: 1rem;
    }
    
    .completion-badge i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .completion-badge h4 {
        margin: 0.5rem 0;
        font-weight: 600;
    }
    
    .completion-badge p {
        margin: 0;
        opacity: 0.9;
    }
    
    .mobile-view .loading-content {
        padding: 1.5rem;
    }
    
    .mobile-view .logo-frame {
        width: 80px;
        height: 80px;
    }
    
    .mobile-view .logo-image {
        font-size: 2rem;
    }
    
    .mobile-view .loading-text {
        font-size: 1.2rem;
    }
    
    .mobile-view .step {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        min-width: 80px;
    }
`;

document.head.appendChild(professionalStyles);

// Initialize professional loading
document.addEventListener('DOMContentLoaded', function() {
    const professionalLoading = new ProfessionalReportLoading();
    professionalLoading.init();
    
    document.addEventListener('reportReady', function(e) {
        console.log('Professional report loading completed:', e.detail);
    });
});