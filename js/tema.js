// tema.js - Enhanced report page dengan download dan print functionality
// Versi dengan pengecekan elemen untuk menghindari error di dashboard

class ReportManager {
    constructor() {
        this.chart = null;
        this.pieChart = null;
        this.currentChartType = 'line';
        this.currentPageSize = 'a4';
        this.basePath = '';
        this.chartData = null;
        this.isReportPage = false; // Tambah flag untuk pengecekan
        this.init();
    }

    init() {
        // PERBAIKAN UTAMA: Cek apakah ini halaman report
        const currentPath = window.location.pathname;
        this.isReportPage = currentPath.includes('report.php');
        
        // Jika bukan halaman report, cek apakah ada elemen yang diperlukan
        if (!this.isReportPage) {
            const hasReportElements = document.getElementById('reportChart') || 
                                      document.getElementById('reportPeriodText') || 
                                      document.getElementById('reportTransactions');
            
            if (!hasReportElements) {
                console.log('ReportManager: Tidak diinisialisasi karena ini bukan halaman report dan elemen tidak ditemukan');
                return; // Jangan lanjutkan inisialisasi
            }
        }
        
        this.detectBasePath();
        this.updateDateTime();
        this.loadReportData();
        this.setupEventListeners();
        this.setupWYSIWYGEditor();
        this.setupColorControls();
        this.setupDownloadHandlers();
        setInterval(() => this.updateDateTime(), 60000);
        
        // Set dashboard data untuk akses global
        if (typeof window.dashboardData !== 'undefined') {
            window.dashboardData = window.dashboardData;
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

    updateDateTime() {
        // PERBAIKAN: Fungsi ini bisa digunakan di dashboard juga
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
        // Tidak perlu console.warn karena elemen ini bisa ada di dashboard
    }

    setupWYSIWYGEditor() {
        // PERBAIKAN: Cek elemen editor sebelum setup
        const editor = document.getElementById('reportTextEditor');
        if (!editor) {
            // Ini mungkin halaman dashboard, tidak ada editor
            return;
        }

        const toolbarButtons = document.querySelectorAll('.wysiwyg-toolbar [data-command]');
        toolbarButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.getAttribute('data-command');
                const value = button.getAttribute('data-value');
                document.execCommand(command, false, value);
                editor.focus();
            });
        });

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

        const saveBtn = document.getElementById('saveReportTextBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveReportContent();
            });
        }

        const exportBtn = document.getElementById('exportReportContent');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportReportContent();
            });
        }
    }

    saveReportContent() {
        const editor = document.getElementById('reportTextEditor');
        if (!editor) {
            console.error('ReportManager: Editor tidak ditemukan');
            return;
        }
        
        const content = editor.innerHTML;
        localStorage.setItem('reportContent', content);
        this.showToast('Laporan berhasil disimpan!');
    }

    loadReportContent() {
        const savedContent = localStorage.getItem('reportContent');
        if (savedContent) {
            const editor = document.getElementById('reportTextEditor');
            if (editor) {
                editor.innerHTML = savedContent;
            }
        }
    }

    exportReportContent() {
        const editor = document.getElementById('reportTextEditor');
        if (!editor) {
            console.error('ReportManager: Editor tidak ditemukan');
            return;
        }
        
        const content = editor.innerHTML;
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

    setupEventListeners() {
        // PERBAIKAN: Cek elemen sebelum menambahkan event listener
        const refreshBtn = document.getElementById('refreshReport');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshReport();
            });
        }

        const mainChartTypes = document.querySelectorAll('input[name="mainChartType"]');
        mainChartTypes.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.currentChartType = e.target.value;
                if (this.chartData) {
                    this.renderChart(this.chartData);
                }
            });
        });

        const chartPeriod = document.getElementById('chartPeriod');
        if (chartPeriod) {
            chartPeriod.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadReportData();
            });
        }

        const pageSize = document.getElementById('pageSize');
        if (pageSize) {
            pageSize.addEventListener('change', (e) => {
                this.currentPageSize = e.target.value;
                this.updatePageSize();
            });
        }

        const printBtn = document.getElementById('printReport');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                this.printReport();
            });
        }

        const downloadExcel = document.getElementById('downloadExcel');
        if (downloadExcel) {
            downloadExcel.addEventListener('click', (e) => {
                e.preventDefault();
                this.downloadAsExcel();
            });
        }

        const downloadPdf = document.getElementById('downloadPdf');
        if (downloadPdf) {
            downloadPdf.addEventListener('click', (e) => {
                e.preventDefault();
                this.downloadAsPdf();
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

    applyCustomColors() {
        const textColor = document.getElementById('reportTextColor').value;
        const backgroundColor = document.getElementById('reportBackgroundColor').value;
        
        const reportCanvas = document.getElementById('reportCanvas');
        if (reportCanvas) {
            reportCanvas.style.color = textColor;
            reportCanvas.style.backgroundColor = backgroundColor;
        }
        
        this.showToast('Warna berhasil diterapkan!');
        
        localStorage.setItem('reportTextColor', textColor);
        localStorage.setItem('reportBackgroundColor', backgroundColor);
    }

    loadCustomPreferences() {
        const savedContent = localStorage.getItem('reportContent');
        if (savedContent) {
            const reportTextEditor = document.getElementById('reportTextEditor');
            if (reportTextEditor) {
                reportTextEditor.innerHTML = savedContent;
            }
        }
        
        const savedTextColor = localStorage.getItem('reportTextColor');
        const savedBackgroundColor = localStorage.getItem('reportBackgroundColor');
        
        if (savedTextColor) {
            const textColorInput = document.getElementById('reportTextColor');
            if (textColorInput) {
                textColorInput.value = savedTextColor;
            }
        }
        
        if (savedBackgroundColor) {
            const bgColorInput = document.getElementById('reportBackgroundColor');
            if (bgColorInput) {
                bgColorInput.value = savedBackgroundColor;
            }
        }
        
        if (savedTextColor || savedBackgroundColor) {
            this.applyCustomColors();
        }
    }

    updatePageSize() {
        const reportCanvas = document.getElementById('reportCanvas');
        if (reportCanvas) {
            reportCanvas.classList.remove('a4', 'a3', 'letter', 'legal');
            reportCanvas.classList.add(this.currentPageSize);
        }
    }

    async loadReportData() {
        // PERBAIKAN: Cek apakah ada elemen chart sebelum load data
        const chartCanvas = document.getElementById('reportChart');
        if (!chartCanvas) {
            console.log('ReportManager: Tidak memuat data report karena chart tidak ditemukan');
            return;
        }

        try {
            const response = await fetch(`${this.basePath}php/api/dashboard_api.php?action=get_financial_chart&period=monthly`);
            const data = await response.json();
            
            if (data.success) {
                this.chartData = data.data;
                this.renderChart(data.data);
                
                if (data.data) {
                    this.updateReportContent(data.data);
                }
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
        if (!canvas) {
            console.warn('ReportManager: Canvas #reportChart tidak ditemukan');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }
        if (this.pieChart) {
            this.pieChart.destroy();
        }

        if (this.currentChartType === 'pie') {
            this.renderPieChart(ctx, chartData);
        } else {
            this.renderLineChart(ctx, chartData);
        }
    }

    renderLineChart(ctx, chartData) {
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

        this.chart = new Chart(ctx, {
            type: 'line',
            data: chartConfig,
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
                                return `${context.dataset.label}: Rp ${this.formatNumber(context.raw)}`;
                            }
                        }
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

    renderPieChart(ctx, chartData) {
        const totalPemasukan = chartData.pemasukan.reduce((a, b) => a + b, 0);
        const totalPengeluaran = chartData.pengeluaran.reduce((a, b) => a + b, 0);
        const totalSaldo = totalPemasukan - totalPengeluaran;

        this.pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Total Pemasukan', 'Total Pengeluaran', 'Saldo'],
                datasets: [{
                    data: [totalPemasukan, totalPengeluaran, totalSaldo],
                    backgroundColor: [
                        '#10b981',
                        '#ef4444',
                        '#3b82f6'
                    ],
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

        this.updatePieChartLegend(totalPemasukan, totalPengeluaran, totalSaldo);
    }

    updatePieChartLegend(pemasukan, pengeluaran, saldo) {
        const legend = document.getElementById('pieChartLegend');
        if (!legend) {
            console.warn('ReportManager: Element #pieChartLegend tidak ditemukan');
            return;
        }

        const total = pemasukan + pengeluaran + Math.abs(saldo);
        const pemasukanPercent = total > 0 ? ((pemasukan/total)*100).toFixed(1) : 0;
        const pengeluaranPercent = total > 0 ? ((pengeluaran/total)*100).toFixed(1) : 0;
        const saldoPercent = total > 0 ? ((Math.abs(saldo)/total)*100).toFixed(1) : 0;
        
        legend.innerHTML = `
            <div class="legend-item">
                <div class="legend-color" style="background-color: #10b981;"></div>
                <span class="legend-label">Pemasukan</span>
                <span class="legend-value">Rp ${this.formatNumber(pemasukan)} (${pemasukanPercent}%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ef4444;"></div>
                <span class="legend-label">Pengeluaran</span>
                <span class="legend-value">Rp ${this.formatNumber(pengeluaran)} (${pengeluaranPercent}%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #3b82f6;"></div>
                <span class="legend-label">Saldo</span>
                <span class="legend-value">Rp ${this.formatNumber(saldo)} (${saldoPercent}%)</span>
            </div>
        `;
    }

    updateReportContent(chartData) {
        const reportPeriodElement = document.getElementById('reportPeriodText');
        if (reportPeriodElement) {
            const now = new Date();
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            reportPeriodElement.textContent = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;
        } else {
            // Tidak perlu console.warn jika di dashboard
            if (this.isReportPage) {
                console.warn('ReportManager: Element #reportPeriodText tidak ditemukan');
            }
        }

        this.loadRecentTransactions();
    }

    async loadRecentTransactions() {
        try {
            const response = await fetch(`${this.basePath}php/api/dashboard_api.php?action=get_recent_transactions&limit=10`);
            const data = await response.json();
            
            if (data.success) {
                this.renderTransactions(data.data);
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
            this.renderEmptyTransactions();
        }
    }

    renderTransactions(transactions) {
        const container = document.getElementById('reportTransactions');
        if (!container) {
            console.warn('ReportManager: Element #reportTransactions tidak ditemukan');
            return;
        }

        if (transactions && transactions.length > 0) {
            let html = '';

            transactions.forEach(transaction => {
                html += `
                    <tr>
                        <td>${new Date(transaction.tanggal).toLocaleDateString('id-ID')}</td>
                        <td>${transaction.kategori}</td>
                        <td>${transaction.deskripsi}</td>
                        <td>
                            <span class="badge bg-${transaction.jenis === 'pemasukan' ? 'success' : 'danger'}">
                                ${transaction.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}
                            </span>
                        </td>
                        <td class="text-${transaction.jenis === 'pemasukan' ? 'success' : 'danger'}">
                            ${transaction.jenis === 'pemasukan' ? '+' : '-'} 
                            Rp ${this.formatNumber(transaction.jumlah)}
                        </td>
                    </tr>
                `;
            });

            container.innerHTML = html;
        } else {
            this.renderEmptyTransactions();
        }
    }

    renderEmptyTransactions() {
        const container = document.getElementById('reportTransactions');
        if (container) {
            container.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada transaksi untuk ditampilkan</td></tr>';
        }
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
        if (!canvas) {
            console.warn('ReportManager: Canvas #reportChart tidak ditemukan');
            return;
        }
        
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
        if (!refreshBtn) {
            console.warn('ReportManager: Element #refreshReport tidak ditemukan');
            return;
        }

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
        this.showToast('Mempersiapkan download Excel...');
        
        const reportPeriodElement = document.getElementById('reportPeriodText');
        const periodText = reportPeriodElement ? reportPeriodElement.textContent : 'Tidak diketahui';
        
        const data = [
            ['LAPORAN FINANSIAL'],
            ['Periode', periodText],
            ['Dibuat pada', new Date().toLocaleDateString('id-ID')],
            [''],
            ['Ringkasan Eksekutif'],
            ['Saldo Bulan Ini', 'Rp ' + this.formatNumber(window.dashboardData?.saldo_bulan_ini || 0)],
            ['Total Pemasukan', 'Rp ' + this.formatNumber(window.dashboardData?.total_pemasukan || 0)],
            ['Total Pengeluaran', 'Rp ' + this.formatNumber(window.dashboardData?.total_pengeluaran || 0)],
            ['Target Tercapai', (window.dashboardData?.target_tercapai || 0) + '%'],
            [''],
            ['Distribusi Transaksi'],
            ['Total Pemasukan', 'Rp ' + this.formatNumber(this.chartData?.pemasukan?.reduce((a, b) => a + b, 0) || 0)],
            ['Total Pengeluaran', 'Rp ' + this.formatNumber(this.chartData?.pengeluaran?.reduce((a, b) => a + b, 0) || 0)],
            ['Saldo', 'Rp ' + this.formatNumber((this.chartData?.pemasukan?.reduce((a, b) => a + b, 0) || 0) - (this.chartData?.pengeluaran?.reduce((a, b) => a + b, 0) || 0))],
            [''],
            ['Detail Transaksi']
        ];

        data.push(['Tanggal', 'Kategori', 'Deskripsi', 'Jenis', 'Jumlah']);

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

        const csvContent = data.map(row => 
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `laporan-finansial-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
        
        this.showToast('Excel berhasil diunduh!');
    }

    downloadAsPdf() {
        this.showToast('Mempersiapkan download PDF...');
        
        const { jsPDF } = window.jspdf;
        const reportCanvas = document.getElementById('reportCanvas');
        
        if (reportCanvas) {
            if (typeof html2canvas !== 'undefined') {
                html2canvas(reportCanvas).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('p', 'mm', this.currentPageSize);
                    const imgWidth = pdf.internal.pageSize.getWidth();
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    
                    pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                    pdf.save('laporan-finansial.pdf');
                    this.showToast('PDF berhasil diunduh!');
                }).catch(error => {
                    console.error('Error generating PDF:', error);
                    this.showToast('Gagal mengunduh PDF', 'danger');
                });
            } else {
                console.error('html2canvas library tidak dimuat');
                this.showToast('Library PDF tidak tersedia', 'danger');
            }
        } else {
            console.error('ReportManager: Element #reportCanvas tidak ditemukan');
            this.showToast('Tidak dapat membuat PDF', 'danger');
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
            
            const toastHeader = toastElement.querySelector('.toast-header');
            if (toastHeader) {
                const currentClass = toastHeader.className;
                toastHeader.className = currentClass.replace(/(bg-\w+)/g, '') + ` ${type === 'danger' ? 'bg-danger text-white' : 'bg-success text-white'}`;
            }
            
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            }
        } else {
            // Fallback jika toast tidak ada
            console.log(`Toast [${type}]: ${message}`);
        }
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) {
            console.log(`Alert [${type}]: ${message}`);
            return;
        }
        
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
    // Hanya inisialisasi jika ada elemen report atau ini halaman report
    const hasReportElements = document.getElementById('reportChart') || 
                              document.getElementById('reportPeriodText') || 
                              document.getElementById('reportTransactions');
    const isReportPage = window.location.pathname.includes('report.php');
    
    if (hasReportElements || isReportPage) {
        window.reportManager = new ReportManager();
        
        document.addEventListener('reportReady', function() {
            setTimeout(() => {
                if (window.reportManager) {
                    window.reportManager.loadCustomPreferences();
                    window.reportManager.loadReportContent();
                    window.reportManager.updatePageSize();
                }
            }, 500);
        });
    }
});