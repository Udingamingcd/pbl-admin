// dashboard.js - Versi tanpa welcome toast notification
class DashboardManager {
    constructor() {
        this.chart = null;
        this.currentPeriod = 'weekly';
        this.currentChartType = 'line';
        this.basePath = '';
        this.rawChartData = null;
        this.trendIndicators = [];
        this.currentTipIndex = 0;
        this.isTyping = false;
        this.tipTimer = null;
        this.tipDuration = 10000; // 10 detik
        this.tips = [
            {
                title: "Tips Menabung",
                description: "Mulailah dengan menyisihkan 10% dari pendapatan setiap bulan untuk membangun kebiasaan menabung yang konsisten."
            },
            {
                title: "Anggaran Bulanan", 
                description: "Buat anggaran bulanan yang realistis dan patuhi dengan disiplin untuk mengontrol pengeluaran Anda."
            },
            {
                title: "Investasi Masa Depan",
                description: "Investasikan minimal 20% dari pendapatan Anda untuk mempersiapkan masa depan yang lebih baik."
            },
            {
                title: "Hindari Hutang",
                description: "Hindari hutang konsumtif yang tidak produktif dan fokus pada hutang yang memberikan nilai tambah."
            },
            {
                title: "Diversifikasi",
                description: "Diversifikasi investasi Anda untuk mengurangi risiko dan meningkatkan potensi keuntungan."
            },
            {
                title: "Dana Darurat",
                description: "Siapkan dana darurat setara 6 bulan pengeluaran untuk menghadapi situasi tak terduga."
            },
            {
                title: "Evaluasi Rutin",
                description: "Tinjau keuangan Anda secara berkala untuk mengevaluasi progres dan melakukan penyesuaian."
            },
            {
                title: "Teknologi Finansial",
                description: "Manfaatkan teknologi untuk mengelola keuangan dengan lebih efisien dan akurat."
            },
            {
                title: "Kebutuhan vs Keinginan",
                description: "Belanjalah sesuai kebutuhan, bukan keinginan, untuk menghindari pengeluaran yang tidak perlu."
            },
            {
                title: "Investasi Dini",
                description: "Mulai investasi sedini mungkin untuk memanfaatkan kekuatan compounding dalam jangka panjang."
            },
            {
                title: "Review Biaya Bulanan",
                description: "Tinjau langganan dan biaya bulanan Anda, batalkan yang tidak diperlukan untuk menghemat pengeluaran."
            },
            {
                title: "Cashback dan Reward",
                description: "Manfaatkan program cashback dan reward dari kartu kredit atau aplikasi pembayaran untuk mendapatkan keuntungan tambahan."
            },
            {
                title: "Pendidikan Finansial",
                description: "Terus belajar tentang literasi keuangan untuk membuat keputusan investasi yang lebih baik."
            },
            {
                title: "Asuransi",
                description: "Miliki asuransi yang memadai untuk melindungi diri dari risiko finansial tak terduga."
            },
            {
                title: "Pensiun Dini",
                description: "Rencanakan pensiun sedini mungkin dengan menabung dan berinvestasi secara konsisten."
            },
            {
                title: "Hemat Energi",
                description: "Kurangi penggunaan listrik dan air untuk menghemat pengeluaran bulanan sekaligus menjaga lingkungan."
            },
            {
                title: "Bandingkan Harga",
                description: "Selalu bandingkan harga sebelum membeli produk atau layanan untuk mendapatkan nilai terbaik."
            },
            {
                title: "Tabungan Otomatis",
                description: "Atur transfer otomatis ke tabungan atau investasi setiap kali gaji masuk."
            },
            {
                title: "Kurangi Makan di Luar",
                description: "Masak di rumah lebih sering untuk menghemat pengeluaran makanan dan menjaga kesehatan."
            },
            {
                title: "Financial Check-up",
                description: "Lakukan financial check-up rutin setiap 6 bulan untuk mengevaluasi kesehatan keuangan Anda."
            }
        ];
        this.init();
    }

    init() {
        this.detectBasePath();
        this.updateDateTime();
        this.loadFinancialChart();
        this.setupEventListeners();
        this.setupTipsControls();
        this.animatePercentageChanges();
        this.startTipsRotation();
        this.handleThemeChange();
        // TIDAK MENAMPILKAN WELCOME TOAST
        setInterval(() => this.updateDateTime(), 60000);
    }

    setupTipsControls() {
        const nextTipBtn = document.getElementById('nextTipBtn');
        const nextTipManualBtn = document.getElementById('nextTipManualBtn');
        const prevTipBtn = document.getElementById('prevTipBtn');
        const tipsCard = document.querySelector('.financial-tips-card');

        if (nextTipBtn) {
            nextTipBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showNextTip();
            });
        }

        if (nextTipManualBtn) {
            nextTipManualBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showNextTip();
            });
        }

        if (prevTipBtn) {
            prevTipBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showPreviousTip();
            });
        }

        // Klik pada card tips juga memicu next tip
        if (tipsCard) {
            tipsCard.addEventListener('click', (e) => {
                // Hanya trigger jika bukan klik pada tombol control
                if (!e.target.closest('.tip-controls') && !e.target.closest('#nextTipBtn')) {
                    this.showNextTip();
                }
            });
        }
    }

    detectBasePath() {
        const currentPath = window.location.pathname;
        if (currentPath.includes('/php/crud/')) {
            this.basePath = '../../';
        } else if (currentPath.includes('/php/')) {
            this.basePath = '../';
        } else {
            this.basePath = '';
        }
    }

    handleThemeChange() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                // Beri waktu untuk transisi tema
                setTimeout(() => {
                    this.updateChartColors();
                    this.updateUIForTheme();
                }, 100);
            });
        }

        // Juga handle perubahan tema melalui sistem
        const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');
        themeMedia.addEventListener('change', () => {
            setTimeout(() => {
                this.updateChartColors();
                this.updateUIForTheme();
            }, 100);
        });
    }

    updateChartColors() {
        if (this.chart) {
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const textColor = isDarkMode ? '#fff' : '#000';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            this.chart.options.scales.x.ticks.color = textColor;
            this.chart.options.scales.y.ticks.color = textColor;
            this.chart.options.scales.x.grid.color = gridColor;
            this.chart.options.scales.y.grid.color = gridColor;
            this.chart.options.plugins.legend.labels.color = textColor;
            
            this.chart.update();
        }
    }

    updateUIForTheme() {
        // Update semua elemen teks untuk memastikan kontras yang tepat
        const textElements = document.querySelectorAll('.card, .stat-card, .transaction-item, .goal-item, .budget-item, .btn, .form-control, .form-select');
        textElements.forEach(el => {
            el.style.transition = 'all 0.3s ease';
        });
        
        // Update chart jika ada
        this.updateChartColors();
    }

    updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            dateTimeElement.textContent = now.toLocaleDateString('id-ID', options);
        }
    }

    // HAPUS METODE showWelcomeNotification()

    showToast(message) {
        const toastElement = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (toastElement && toastMessage) {
            toastMessage.textContent = message;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }

    startTipsRotation() {
        // Show initial tip
        this.showNextTip();
    }

    showNextTip() {
        if (this.isTyping) return;
        
        this.currentTipIndex = (this.currentTipIndex + 1) % this.tips.length;
        const tip = this.tips[this.currentTipIndex];
        
        this.typeTip(tip.title, tip.description);
    }

    showPreviousTip() {
        if (this.isTyping) return;
        
        this.currentTipIndex = (this.currentTipIndex - 1 + this.tips.length) % this.tips.length;
        const tip = this.tips[this.currentTipIndex];
        
        this.typeTip(tip.title, tip.description);
    }

    typeTip(title, description) {
        this.isTyping = true;
        
        const tipTitle = document.getElementById('tipTitle');
        const tipDescription = document.getElementById('tipDescription');
        const tipProgress = document.getElementById('tipProgress');
        const tipTimer = document.getElementById('tipTimer');
        
        if (!tipTitle || !tipDescription) return;
        
        // Reset content and progress
        tipTitle.textContent = '';
        tipDescription.textContent = '';
        tipProgress.style.width = '0%';
        tipTimer.textContent = '10s';
        
        // Clear existing timer
        if (this.tipTimer) {
            clearInterval(this.tipTimer);
        }
        
        // Type title
        this.typeText(tipTitle, title, 50).then(() => {
            // Wait a bit before typing description
            setTimeout(() => {
                this.typeText(tipDescription, description, 30).then(() => {
                    this.isTyping = false;
                    this.startTipTimer();
                });
            }, 500);
        });
    }

    startTipTimer() {
        const tipProgress = document.getElementById('tipProgress');
        const tipTimer = document.getElementById('tipTimer');
        let timeLeft = 10;
        
        this.tipTimer = setInterval(() => {
            timeLeft--;
            const progress = ((10 - timeLeft) / 10) * 100;
            
            tipProgress.style.width = `${progress}%`;
            tipTimer.textContent = `${timeLeft}s`;
            
            if (timeLeft <= 0) {
                clearInterval(this.tipTimer);
                this.showNextTip();
            }
        }, 1000);
    }

    typeText(element, text, speed) {
        return new Promise((resolve) => {
            let i = 0;
            element.textContent = '';
            element.style.opacity = '1';
            
            const timer = setInterval(() => {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                } else {
                    clearInterval(timer);
                    resolve();
                }
            }, speed);
        });
    }

    animatePercentageChanges() {
        const percentageElements = document.querySelectorAll('.percentage-change');
        
        percentageElements.forEach(element => {
            const targetValue = parseFloat(element.getAttribute('data-value'));
            const duration = 2000;
            const steps = 60;
            const stepValue = targetValue / steps;
            let currentValue = 0;
            let step = 0;
            
            const timer = setInterval(() => {
                step++;
                currentValue += stepValue;
                
                if (step >= steps) {
                    currentValue = targetValue;
                    clearInterval(timer);
                }
                
                // Format persentase dengan tanda +/-
                const formattedValue = currentValue >= 0 ? 
                    `+${Math.abs(currentValue).toFixed(1)}` : 
                    `-${Math.abs(currentValue).toFixed(1)}`;
                
                const arrow = currentValue >= 0 ? 
                    '<i class="fas fa-arrow-up text-success me-1"></i>' : 
                    '<i class="fas fa-arrow-down text-danger me-1"></i>';
                
                element.innerHTML = `${arrow} ${formattedValue}% dari bulan lalu`;
                
            }, duration / steps);
        });
    }

    setupEventListeners() {
        // Refresh dashboard
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }

        // Chart period change
        const chartPeriod = document.getElementById('chartPeriod');
        if (chartPeriod) {
            chartPeriod.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadFinancialChart();
            });
        }

        // Chart type change
        const chartTypes = document.querySelectorAll('input[name="chartType"]');
        chartTypes.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.currentChartType = e.target.value;
                if (this.rawChartData) {
                    this.renderChart(this.rawChartData);
                }
            });
        });

        // Add hover effects to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('hover-active');
            });
            
            card.addEventListener('mouseleave', () => {
                card.classList.remove('hover-active');
            });
        });

        // Add click effects to transaction items
        const transactionItems = document.querySelectorAll('.transaction-item');
        transactionItems.forEach(item => {
            item.addEventListener('click', () => {
                item.classList.add('click-active');
                setTimeout(() => {
                    item.classList.remove('click-active');
                }, 300);
            });
        });

        // Theme change listener for dynamic updates
        document.addEventListener('themeChanged', () => {
            this.updateUIForTheme();
        });
    }

    async refreshDashboard() {
        const refreshBtn = document.getElementById('refreshDashboard');
        if (!refreshBtn) return;

        const originalText = refreshBtn.innerHTML;
        
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memuat...';
        
        try {
            this.animateStatsRefresh();
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Show refresh notification
            this.showToast('Dashboard berhasil diperbarui!');
            
            // Simulate data refresh
            location.reload();
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            this.showAlert('Gagal memperbarui dashboard', 'danger');
        } finally {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = originalText;
        }
    }

    animateStatsRefresh() {
        const stats = [
            'saldoBulanIni',
            'totalPemasukan', 
            'totalPengeluaran',
            'targetTercapai'
        ];
        
        stats.forEach(statId => {
            const element = document.getElementById(statId);
            if (element) {
                element.style.opacity = '0.5';
                element.style.transition = 'opacity 0.3s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                }, 1500);
            }
        });
    }

    async loadFinancialChart() {
        try {
            this.showChartLoading();
            
            const response = await fetch(`${this.basePath}php/api/dashboard_api.php?action=get_financial_chart&period=${this.currentPeriod}`);
            const data = await response.json();
            
            if (data.success) {
                this.rawChartData = data.data;
                this.renderChart(data.data);
                this.showChartTrends(data.data);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error loading financial chart:', error);
            this.renderEmptyChart();
            this.showToast('Gagal memuat grafik keuangan');
        }
    }

    showChartTrends(chartData) {
        const trendsContainer = document.getElementById('chartTrends');
        if (!trendsContainer) return;
        
        const incomeTrend = this.calculateTrend(chartData.pemasukan);
        const expenseTrend = this.calculateTrend(chartData.pengeluaran);
        
        trendsContainer.innerHTML = `
            <div class="row text-center">
                <div class="col-6">
                    <div class="trend-indicator-small ${incomeTrend.direction}">
                        <i class="fas fa-arrow-${incomeTrend.direction} me-1"></i>
                        <strong>Pemasukan:</strong> ${incomeTrend.percentage}%
                    </div>
                </div>
                <div class="col-6">
                    <div class="trend-indicator-small ${expenseTrend.direction}">
                        <i class="fas fa-arrow-${expenseTrend.direction} me-1"></i>
                        <strong>Pengeluaran:</strong> ${expenseTrend.percentage}%
                    </div>
                </div>
            </div>
        `;
    }

    calculateTrend(data) {
        if (data.length < 2) return { direction: 'right', percentage: '0' };
        
        const last = data[data.length - 1];
        const previous = data[data.length - 2];
        const percentage = previous !== 0 ? ((last - previous) / previous * 100).toFixed(1) : '0';
        
        let direction = 'right';
        if (last > previous) direction = 'up';
        else if (last < previous) direction = 'down';
        
        return { direction, percentage };
    }

    showChartLoading() {
        const canvas = document.getElementById('financeChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Use theme-aware colors
        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        ctx.fillStyle = isDarkMode ? '#d1d5db' : '#6c757d';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Memuat data grafik...', canvas.width / 2, canvas.height / 2);
    }

    renderChart(chartData) {
        switch (this.currentChartType) {
            case 'bar':
                this.renderBarChart(chartData);
                break;
            default:
                this.renderLineChart(chartData);
                break;
        }
    }

    renderLineChart(chartData) {
        const canvas = document.getElementById('financeChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: chartData.pemasukan,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: isDarkMode ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartData.pengeluaran,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: isDarkMode ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: this.getChartOptions()
        });
    }

    renderBarChart(chartData) {
        const canvas = document.getElementById('financeChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: chartData.pemasukan,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartData.pengeluaran,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: this.getChartOptions()
        });
    }

    getChartOptions() {
        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        
        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        const textColor = isDarkMode ? '#fff' : '#000';

        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: textColor,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: isDarkMode ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                    titleColor: textColor,
                    bodyColor: textColor,
                    borderColor: isDarkMode ? '#495057' : '#dee2e6',
                    borderWidth: 1,
                    callbacks: {
                        label: (context) => {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                const value = context.parsed.y;
                                label += this.formatCurrency(value);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: (value) => {
                            return this.formatYAxis(value);
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            }
        };
    }

    formatYAxis(value) {
        const absValue = Math.abs(value);
        
        if (absValue >= 1000000000000) {
            return 'Rp' + (value / 1000000000000).toFixed(1) + 'T';
        } else if (absValue >= 1000000000) {
            return 'Rp' + (value / 1000000000).toFixed(1) + 'M';
        } else if (absValue >= 1000000) {
            return 'Rp' + (value / 1000000).toFixed(1) + 'Jt';
        } else if (absValue >= 1000) {
            return 'Rp' + (value / 1000).toFixed(0) + 'Rb';
        }
        return 'Rp' + value;
    }

    formatCurrency(amount) {
        const absAmount = Math.abs(amount);
        let formatted = '';
        
        if (absAmount >= 1000000000000) {
            formatted = 'Rp' + (amount / 1000000000000).toFixed(2) + ' Triliun';
        } else if (absAmount >= 1000000000) {
            formatted = 'Rp' + (amount / 1000000000).toFixed(2) + ' Miliar';
        } else if (absAmount >= 1000000) {
            formatted = 'Rp' + (amount / 1000000).toFixed(2) + ' Juta';
        } else if (absAmount >= 1000) {
            formatted = 'Rp' + (amount / 1000).toFixed(0) + ' Ribu';
        } else {
            formatted = 'Rp' + Math.round(amount);
        }
        
        return amount < 0 ? '-' + formatted : formatted;
    }

    renderEmptyChart() {
        const canvas = document.getElementById('financeChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDarkMode ? '#fff' : '#000';

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: [0, 0, 0, 0],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Pengeluaran',
                        data: [0, 0, 0, 0],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        ticks: {
                            color: textColor,
                            callback: (value) => this.formatYAxis(value)
                        }
                    }
                }
            }
        });
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;
        
        const alertId = 'alert-' + Date.now();
        
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        const alertHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${icons[type] || 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
    
    // Add scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for scroll animations
    document.querySelectorAll('.card, .stat-card').forEach(el => {
        observer.observe(el);
    });
});