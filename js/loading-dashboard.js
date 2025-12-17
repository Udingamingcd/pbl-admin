// loading-dashboard.js - Enhanced loading screen tanpa toast notification di kanan atas
class DashboardLoading {
    constructor() {
        this.loadingScreen = document.getElementById('loading');
        this.progressBar = document.querySelector('.improved-progress-bar');
        this.progressPercentage = document.getElementById('progressPercentage');
        this.loadingText = document.getElementById('loadingText');
        this.loadingSubtext = document.getElementById('loadingSubtext');
        this.progress = 0;
        this.stepsCompleted = 0;
        this.totalSteps = 5;
        this.currentStep = 0;
        this.isWaitingForCompletion = false;
        this.finalCompletionTriggered = false;
        
        this.simulationSteps = [
            { 
                name: 'Memuat data pengguna', 
                progress: 20,
                subtext: 'Mengambil informasi profil dan preferensi'
            },
            { 
                name: 'Mengambil transaksi', 
                progress: 40,
                subtext: 'Memuat riwayat transaksi terbaru'
            },
            { 
                name: 'Memproses budget', 
                progress: 60,
                subtext: 'Menganalisis anggaran dan pengeluaran'
            },
            { 
                name: 'Menganalisis data', 
                progress: 80,
                subtext: 'Menghasilkan insight dan rekomendasi'
            },
            { 
                name: 'Menyiapkan dashboard', 
                progress: 95,
                subtext: 'Mengatur tata letak dan komponen'
            }
        ];

        this.financialIcons = ['💰', '💵', '💳', '📈', '💰', '💸', '💎', '🏦', '📊', '💼'];
    }

    init() {
        this.setupResponsiveLayout();
        this.updateProgressSteps();
        
        // Wait for initial render
        setTimeout(() => {
            this.simulateDataLoading();
        }, 800);
    }

    setupResponsiveLayout() {
        // Setup responsive event listeners
        window.addEventListener('resize', () => this.handleResize());
        this.handleResize(); // Initial call
    }

    handleResize() {
        const isMobile = window.innerWidth < 768;
        const statsGrid = document.querySelector('.loading-stats-improved .stats-grid');
        
        if (statsGrid) {
            if (isMobile) {
                statsGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(150px, 1fr))';
                statsGrid.style.gap = '0.5rem';
            } else {
                statsGrid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                statsGrid.style.gap = '1rem';
            }
        }
    }

    updateProgressSteps() {
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            step.classList.toggle('active', index === 0);
        });
    }

    simulateDataLoading() {
        const stepInterval = setInterval(() => {
            if (this.currentStep < this.simulationSteps.length) {
                this.updateStep(this.currentStep);
                this.progress = this.simulationSteps[this.currentStep].progress;
                this.updateProgressBar();
                this.currentStep++;
            } else {
                clearInterval(stepInterval);
                this.waitForAllStepsCompletion();
            }
        }, 800);
    }

    waitForAllStepsCompletion() {
        const completionCheck = setInterval(() => {
            if (this.stepsCompleted === this.totalSteps && !this.finalCompletionTriggered) {
                clearInterval(completionCheck);
                this.finalCompletionTriggered = true;
                this.initiateFinalCompletion();
            }
        }, 100);
    }

    initiateFinalCompletion() {
        // Update loading text untuk final stage
        if (this.loadingText) {
            this.loadingText.textContent = 'Menyelesaikan proses...';
            this.loadingText.classList.add('text-success');
        }

        if (this.loadingSubtext) {
            this.loadingSubtext.textContent = 'Hanya beberapa detik lagi';
        }

        this.animateFinalProgress();
    }

    animateFinalProgress() {
        let finalProgress = 95;
        const finalInterval = setInterval(() => {
            finalProgress += 1;
            this.progress = finalProgress;
            this.updateProgressBar();

            if (finalProgress >= 100) {
                clearInterval(finalInterval);
                setTimeout(() => {
                    this.showCompletionNotification();
                    this.startFinancialConfetti();
                    setTimeout(() => {
                        this.showCelebrationOverlay();
                        setTimeout(() => {
                            this.completeLoading();
                        }, 2000);
                    }, 1500);
                }, 500);
            }
        }, 60);
    }

    updateStep(stepIndex) {
        // Update progress steps visual
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            step.classList.toggle('active', index <= stepIndex);
        });

        // Update loading text
        if (this.loadingText) {
            this.loadingText.textContent = this.simulationSteps[stepIndex].name;
        }
        if (this.loadingSubtext) {
            this.loadingSubtext.textContent = this.simulationSteps[stepIndex].subtext;
        }

        // Simulate processing time
        setTimeout(() => {
            this.stepsCompleted++;

            // Add subtle celebration effect for each completed step
            if (this.stepsCompleted === this.totalSteps) {
                this.createMiniCelebration();
            }
        }, 800);
    }

    updateProgressBar() {
        if (this.progressBar && this.progressPercentage) {
            this.progressBar.style.width = `${this.progress}%`;
            this.progressPercentage.textContent = `${Math.round(this.progress)}%`;
            
            this.updateProgressBarColor();
            
            // Visual effects based on progress
            if (this.progress > 30) {
                this.progressBar.classList.add('progress-bar-striped');
            }
            if (this.progress > 60) {
                this.progressBar.classList.add('progress-bar-animated');
            }
            
            // Special effect at 95% - sebelum completion
            if (this.progress >= 95 && this.progress < 100) {
                this.progressBar.style.animation = 'pulse 0.8s infinite';
            }
        }
    }

    updateProgressBarColor() {
        if (!this.progressBar) return;
        
        // Change gradient based on progress
        const gradients = {
            20: 'linear-gradient(90deg, #ef4444, #f59e0b)',
            40: 'linear-gradient(90deg, #f59e0b, #84cc16)',
            60: 'linear-gradient(90deg, #84cc16, #10b981)',
            80: 'linear-gradient(90deg, #10b981, #3b82f6)',
            100: 'linear-gradient(90deg, #3b82f6, #8b5cf6)'
        };

        let gradient = gradients[100]; // default
        if (this.progress < 20) gradient = gradients[20];
        else if (this.progress < 40) gradient = gradients[40];
        else if (this.progress < 60) gradient = gradients[60];
        else if (this.progress < 80) gradient = gradients[80];

        this.progressBar.style.background = gradient;
    }

    showCompletionNotification() {
        const loadingContent = this.loadingScreen.querySelector('.loading-content');
        const celebration = document.createElement('div');
        celebration.className = 'celebration-message mt-3';
        celebration.innerHTML = `
            <div class="success-badge animate-pulse">
                <i class="fas fa-check-circle me-2"></i>
                Semua proses selesai! Dashboard siap digunakan.
            </div>
        `;
        loadingContent.appendChild(celebration);

        // Update loading text final
        if (this.loadingText) {
            this.loadingText.innerHTML = '<i class="fas fa-check-circle me-2"></i>Dashboard Siap!';
        }
        if (this.loadingSubtext) {
            this.loadingSubtext.textContent = 'Mempersiapkan pengalaman terbaik...';
        }
    }

    startFinancialConfetti() {
        // Create multiple financial confetti elements
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                this.createFinancialConfetti();
            }, i * 100);
        }
    }

    createFinancialConfetti() {
        const financialIcon = this.financialIcons[Math.floor(Math.random() * this.financialIcons.length)];
        const confetti = document.createElement('div');
        confetti.className = 'financial-confetti';
        confetti.textContent = financialIcon;
        confetti.style.cssText = `
            left: ${Math.random() * 100}%;
            font-size: ${Math.random() * 20 + 20}px;
            animation-duration: ${Math.random() * 2 + 2}s;
            animation-delay: ${Math.random() * 0.5}s;
        `;
        
        document.body.appendChild(confetti);
        
        // Remove confetti after animation
        setTimeout(() => {
            confetti.remove();
        }, 3000);
    }

    showCelebrationOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'celebration-overlay';
        overlay.innerHTML = `
            <div class="celebration-content">
                <div class="celebration-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h2 class="celebration-title">Dashboard Siap!</h2>
                <p class="celebration-subtitle">Selamat menikmati pengelolaan keuangan yang lebih baik</p>
                <button class="btn btn-primary btn-lg" onclick="this.closest('.celebration-overlay').remove()">
                    <i class="fas fa-rocket me-2"></i>Mulai Jelajahi
                </button>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Add financial confetti to celebration
        for (let i = 0; i < 30; i++) {
            setTimeout(() => {
                this.createCelebrationConfetti();
            }, i * 150);
        }
    }

    createCelebrationConfetti() {
        const financialIcon = this.financialIcons[Math.floor(Math.random() * this.financialIcons.length)];
        const confetti = document.createElement('div');
        confetti.className = 'financial-confetti';
        confetti.textContent = financialIcon;
        confetti.style.cssText = `
            left: ${Math.random() * 100}%;
            font-size: ${Math.random() * 25 + 25}px;
            animation-duration: ${Math.random() * 3 + 2}s;
            z-index: 10001;
        `;
        
        document.body.appendChild(confetti);
        
        // Remove confetti after animation
        setTimeout(() => {
            confetti.remove();
        }, 5000);
    }

    createMiniCelebration() {
        // Add subtle confetti effect untuk completion semua steps
        for (let i = 0; i < 10; i++) {
            setTimeout(() => {
                this.createFinancialConfetti();
            }, i * 200);
        }
    }

    completeLoading() {
        // Remove celebration overlay if still exists
        const overlay = document.querySelector('.celebration-overlay');
        if (overlay) {
            overlay.remove();
        }
        
        // Final transition out
        setTimeout(() => {
            if (this.loadingScreen) {
                this.loadingScreen.style.opacity = '0';
                this.loadingScreen.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    this.loadingScreen.style.display = 'none';
                    
                    // Dispatch custom event untuk dashboard ready
                    const event = new CustomEvent('dashboardReady', {
                        detail: { 
                            timestamp: new Date().toISOString(),
                            stepsCompleted: this.stepsCompleted,
                            totalProgress: this.progress
                        }
                    });
                    document.dispatchEvent(event);
                    
                    // TIDAK MENAMPILKAN TOAST NOTIFICATION DI KANAN ATAS
                    // Hanya celebration overlay yang sudah ditampilkan
                }, 500);
            }
        }, 1000);
    }
}

// Initialize dashboard loading
document.addEventListener('DOMContentLoaded', function() {
    const dashboardLoading = new DashboardLoading();
    dashboardLoading.init();
    
    // Listen for dashboard ready event
    document.addEventListener('dashboardReady', function(e) {
        console.log('Dashboard ready at:', e.detail.timestamp, 
                   'Steps:', e.detail.stepsCompleted, 
                   'Progress:', e.detail.totalProgress + '%');
        
        // TIDAK MENAMPILKAN TOAST NOTIFICATION DI KANAN ATAS
        // Dashboard sudah menampilkan celebration overlay
    });
});