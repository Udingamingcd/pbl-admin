// report.js - Enhanced report page dengan download dan print functionality
class ReportManager {
    constructor() {
        this.chart = null;
        this.currentChartType = 'line';
        this.currentPageSize = 'a4';
        this.basePath = '';
        this.isEditing = false;
        this.originalText = '';
        this.init();
    }

    init() {
        this.detectBasePath();
        this.updateDateTime();
        this.loadReportData();
        this.setupEventListeners();
        this.setupTextEditor();
        this.setupColorControls();
        this.setupDownloadHandlers();
        setInterval(() => this.updateDateTime(), 60000);
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

    setupEventListeners() {
        // Refresh report
        const refreshBtn = document.getElementById('refreshReport');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshReport();
            });
        }

        // Chart type change
        const chartTypes = document.querySelectorAll('input[name="mainChartType"]');
        chartTypes.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.currentChartType = e.target.value;
                if (this.chart) {
                    this.renderChart(this.chart.data);
                }
            });
        });

        // Page size change
        const pageSize = document.getElementById('pageSize');
        if (pageSize) {
            pageSize.addEventListener('change', (e) => {
                this.currentPageSize = e.target.value;
                this.updatePageSize();
            });
        }

        // Print report
        const printBtn = document.getElementById('printReport');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                this.printReport();
            });
        }

        // Enhanced Export buttons dengan event delegation
        document.addEventListener('click', (e) => {
            if (e.target.closest('#downloadExcel')) {
                this.downloadAsExcel();
            } else if (e.target.closest('#downloadPdf')) {
                this.downloadAsPdf();
            }
        });

        // Theme change listener
        document.addEventListener('themeChanged', () => {
            this.updateUIForTheme();
        });
    }

    setupTextEditor() {
        const saveBtn = document.getElementById('saveReportTextBtn');
        const exportBtn = document.getElementById('exportReportContent');
        const textEditor = document.getElementById('reportTextEditor');

        if (saveBtn && exportBtn && textEditor) {
            saveBtn.addEventListener('click', () => {
                this.saveReportContent();
            });

            exportBtn.addEventListener('click', () => {
                this.exportReportContent();
            });
        }

        // Setup WYSIWYG toolbar
        const toolbarButtons = document.querySelectorAll('.wysiwyg-toolbar [data-command]');
        toolbarButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.getAttribute('data-command');
                const value = button.getAttribute('data-value');
                document.execCommand(command, false, value);
                document.getElementById('reportTextEditor').focus();
            });
        });

        // Color pickers
        const textColorPicker = document.getElementById('textColorPicker');
        const bgColorPicker = document.getElementById('bgColorPicker');
        
        if (textColorPicker) {
            textColorPicker.addEventListener('change', (e) => {
                document.execCommand('foreColor', false, e.target.value);
            });
        }
        
        if (bgColorPicker) {
            bgColorPicker.addEventListener('change', (e) => {
                document.execCommand('hiliteColor', false, e.target.value);
            });
        }
    }

    setupColorControls() {
        const applyColorsBtn = document.getElementById('applyReportColors');
        if (applyColorsBtn) {
            applyColorsBtn.addEventListener('click', () => {
                this.applyCustomColors();
            });
        }
    }

    setupDownloadHandlers() {
        // Handlers sudah di setup di setupEventListeners
    }

    saveReportContent() {
        const content = document.getElementById('reportTextEditor').innerHTML;
        localStorage.setItem('reportContent', content);
        this.showToast('Laporan berhasil disimpan!');
    }

    exportReportContent() {
        const content = document.getElementById('reportTextEditor').innerHTML;
        const blob = new Blob([`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Laporan Finansial</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 2rem; }
                    h1, h2, h3 { color: #2c3e50; }
                    ul { margin-left: 2rem; }
                    .summary { background: #f8f9fa; padding: 1rem; border-radius: 8px; }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `], { type: 'text/html' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `laporan-finansial-${new Date().toISOString().split('T')[0]}.html`;
        link.click();
        this.showToast('Laporan berhasil diekspor!');
    }

    applyCustomColors() {
        const textColor = document.getElementById('reportTextColor').value;
        const backgroundColor = document.getElementById('reportBackgroundColor').value;
        
        const reportCanvas = document.getElementById('reportCanvas');
        if (reportCanvas) {
            reportCanvas.style.color = textColor;
            reportCanvas.style.backgroundColor = backgroundColor;
        }
        
        this.showToast('Warna berhasil diterapkan!');
        
        // Simpan preferensi warna
        localStorage.setItem('reportTextColor', textColor);
        localStorage.setItem('reportBackgroundColor', backgroundColor);
    }

    loadCustomPreferences() {
        // Load teks yang disimpan
        const savedContent = localStorage.getItem('reportContent');
        if (savedContent) {
            const reportTextEditor = document.getElementById('reportTextEditor');
            if (reportTextEditor) {
                reportTextEditor.innerHTML = savedContent;
            }
        }
        
        // Load warna yang disimpan
        const savedTextColor = localStorage.getItem('reportTextColor');
        const savedBackgroundColor = localStorage.getItem('reportBackgroundColor');
        
        if (savedTextColor) {
            document.getElementById('reportTextColor').value = savedTextColor;
        }
        if (savedBackgroundColor) {
            document.getElementById('reportBackgroundColor').value = savedBackgroundColor;
        }
        
        if (savedTextColor || savedBackgroundColor) {
            this.applyCustomColors();
        }
    }

    updatePageSize() {
        const reportCanvas = document.getElementById('reportCanvas');
        if (reportCanvas) {
            // Remove all size classes
            reportCanvas.classList.remove('a4', 'a3', 'letter', 'legal');
            // Add current size class
            reportCanvas.classList.add(this.currentPageSize);
        }
    }

    async loadReportData() {
        try {
            // Load chart data
            const response = await fetch(`${this.basePath}php/api/dashboard_api.php?action=get_financial_chart&period=monthly`);
            const data = await response.json();
            
            if (data.success) {
                this.renderChart(data.data);
                this.updateReportContent(data.data);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error loading report data:', error);
            this.renderEmptyChart();
            this.showToast('Gagal memuat data laporan');
        }
    }

    renderChart(chartData) {
        const canvas = document.getElementById('reportChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        // Data untuk grafik
        const chartConfig = {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: chartData.pemasukan,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Pengeluaran',
                    data: chartData.pengeluaran,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                }
            ]
        };

        // Jika tipe bar, ubah konfigurasi
        if (this.currentChartType === 'pie') {
            const totalPemasukan = chartData.pemasukan.reduce((a, b) => a + b, 0);
            const totalPengeluaran = chartData.pengeluaran.reduce((a, b) => a + b, 0);

            this.chart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Total Pemasukan', 'Total Pengeluaran'],
                    datasets: [{
                        data: [totalPemasukan, totalPengeluaran],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: Rp ${this.formatNumber(value)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            this.updatePieChartLegend(totalPemasukan, totalPengeluaran);
        } else {
            this.chart = new Chart(ctx, {
                type: this.currentChartType,
                data: chartConfig,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return this.formatYAxis(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    updatePieChartLegend(pemasukan, pengeluaran) {
        const legend = document.getElementById('pieChartLegend');
        if (!legend) return;

        const total = pemasukan + pengeluaran;
        
        legend.innerHTML = `
            <div class="legend-item">
                <div class="legend-color" style="background-color: #10b981;"></div>
                <span class="legend-label">Pemasukan</span>
                <span class="legend-value">Rp ${this.formatNumber(pemasukan)} (${((pemasukan/total)*100).toFixed(1)}%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ef4444;"></div>
                <span class="legend-label">Pengeluaran</span>
                <span class="legend-value">Rp ${this.formatNumber(pengeluaran)} (${((pengeluaran/total)*100).toFixed(1)}%)</span>
            </div>
        `;
    }

    updateReportContent(chartData) {
        const totalPemasukan = chartData.pemasukan.reduce((a, b) => a + b, 0);
        const totalPengeluaran = chartData.pengeluaran.reduce((a, b) => a + b, 0);
        const saldo = totalPemasukan - totalPengeluaran;

        // Update period
        const now = new Date();
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];
        document.getElementById('reportPeriodText').textContent = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;
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

    formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    renderEmptyChart() {
        const canvas = document.getElementById('reportChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Bulan 1', 'Bulan 2', 'Bulan 3', 'Bulan 4', 'Bulan 5', 'Bulan 6'],
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: [0, 0, 0, 0, 0, 0],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Pengeluaran',
                        data: [0, 0, 0, 0, 0, 0],
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
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => this.formatYAxis(value)
                        }
                    }
                }
            }
        });
    }

    async refreshReport() {
        const refreshBtn = document.getElementById('refreshReport');
        if (!refreshBtn) return;

        const originalText = refreshBtn.innerHTML;
        
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memuat...';
        
        try {
            await new Promise(resolve => setTimeout(resolve, 1000));
            this.loadReportData();
            this.showToast('Laporan berhasil diperbarui!');
        } catch (error) {
            console.error('Error refreshing report:', error);
            this.showAlert('Gagal memperbarui laporan', 'danger');
        } finally {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = originalText;
        }
    }

    printReport() {
        window.print();
    }

    downloadAsExcel() {
        this.showToast('Mempersiapkan download Excel...', 'info');
        
        // Buat data Excel
        const data = [
            ['LAPORAN FINANSIAL - FINANSIALKU'],
            ['Periode', document.getElementById('reportPeriodText').textContent],
            ['Dibuat pada', new Date().toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            })],
            [''],
            ['RINGKASAN EKSEKUTIF'],
            ['Saldo Bulan Ini', 'Rp ' + this.formatNumber(window.dashboardData?.saldo_bulan_ini || 0)],
            ['Total Pemasukan', 'Rp ' + this.formatNumber(window.dashboardData?.total_pemasukan || 0)],
            ['Total Pengeluaran', 'Rp ' + this.formatNumber(window.dashboardData?.total_pengeluaran || 0)],
            ['Target Tercapai', (window.dashboardData?.target_tercapai || 0) + '%'],
            [''],
            ['PERBANDINGAN BULAN LALU'],
            ['Perubahan Saldo', (window.dashboardData?.perbandingan?.saldo_change || 0) + '%'],
            ['Perubahan Pemasukan', (window.dashboardData?.perbandingan?.pemasukan_change || 0) + '%'],
            ['Perubahan Pengeluaran', (window.dashboardData?.perbandingan?.pengeluaran_change || 0) + '%'],
            [''],
            ['DETAIL TRANSAKSI']
        ];

        // Tambahkan header transaksi
        data.push(['Tanggal', 'Kategori', 'Deskripsi', 'Jenis', 'Jumlah']);

        // Tambahkan data transaksi dari window.dashboardData
        if (window.dashboardData?.recent_transactions && window.dashboardData.recent_transactions.length > 0) {
            window.dashboardData.recent_transactions.forEach(transaction => {
                data.push([
                    new Date(transaction.tanggal).toLocaleDateString('id-ID'),
                    transaction.kategori,
                    transaction.deskripsi,
                    transaction.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran',
                    (transaction.jenis === 'pemasukan' ? '+' : '-') + ' Rp ' + this.formatNumber(transaction.jumlah)
                ]);
            });
        }

        // Konversi ke CSV
        const csvContent = data.map(row => 
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'laporan-finansial.csv';
        link.click();
        URL.revokeObjectURL(link.href);
        
        this.showToast('Excel berhasil diunduh!', 'success');
    }

    downloadAsPdf() {
        this.showToast('Mempersiapkan download PDF...', 'info');
        
        const { jsPDF } = window.jspdf;
        const reportCanvas = document.getElementById('reportCanvas');
        
        if (reportCanvas) {
            const options = {
                scale: 2,
                useCORS: true,
                scrollY: -window.scrollY,
                backgroundColor: '#ffffff',
                logging: false,
                width: reportCanvas.scrollWidth,
                height: reportCanvas.scrollHeight,
                windowWidth: reportCanvas.scrollWidth,
                windowHeight: reportCanvas.scrollHeight
            };
            
            // Scroll ke atas sebelum capture
            window.scrollTo(0, 0);
            
            setTimeout(() => {
                html2canvas(reportCanvas, options).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('p', 'mm', this.currentPageSize);
                    const imgWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    
                    let heightLeft = imgHeight;
                    let position = 0;
                    
                    // Add first page
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    
                    // Add additional pages if content is too long
                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }
                    
                    pdf.save('laporan-finansial.pdf');
                    this.showToast('PDF berhasil diunduh!', 'success');
                }).catch(error => {
                    console.error('Error generating PDF:', error);
                    this.showToast('Gagal mengunduh PDF', 'danger');
                });
            }, 1000);
        }
    }

    updateUIForTheme() {
        if (this.chart) {
            this.chart.update();
        }
    }

    showToast(message, type = 'success') {
        const toastElement = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (toastElement && toastMessage) {
            toastMessage.textContent = message;
            
            // Update toast style based on type
            const toast = new bootstrap.Toast(toastElement);
            
            // Remove existing type classes
            toastElement.querySelector('.toast-header').className = 
                toastElement.querySelector('.toast-header').className
                .replace(/bg-\w+/g, '')
                .replace(/text-\w+/g, '');
                
            // Add new type classes
            const toastHeader = toastElement.querySelector('.toast-header');
            const icon = toastHeader.querySelector('i');
            
            switch(type) {
                case 'success':
                    toastHeader.classList.add('bg-success', 'text-white');
                    icon.className = 'fas fa-check-circle me-2';
                    break;
                case 'danger':
                    toastHeader.classList.add('bg-danger', 'text-white');
                    icon.className = 'fas fa-exclamation-triangle me-2';
                    break;
                case 'warning':
                    toastHeader.classList.add('bg-warning', 'text-dark');
                    icon.className = 'fas fa-exclamation-circle me-2';
                    break;
                case 'info':
                    toastHeader.classList.add('bg-info', 'text-white');
                    icon.className = 'fas fa-info-circle me-2';
                    break;
                default:
                    toastHeader.classList.add('bg-primary', 'text-white');
                    icon.className = 'fas fa-bell me-2';
            }
            
            toast.show();
        }
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

// Initialize report when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.reportManager = new ReportManager();
    
    // Load custom preferences setelah loading selesai
    document.addEventListener('reportReady', function() {
        setTimeout(() => {
            window.reportManager.loadCustomPreferences();
            window.reportManager.updatePageSize();
        }, 500);
    });
});