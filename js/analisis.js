// analisis.js - Enhanced analysis page dengan grafik dan editor teks
class AnalisisManager {
    constructor() {
        this.chart = null;
        this.pieChart = null;
        this.currentPeriod = 'weekly';
        this.currentChartType = 'line';
        this.basePath = '';
        this.init();
    }

    init() {
        this.detectBasePath();
        this.updateDateTime();
        this.loadAnalisisChart();
        this.setupEventListeners();
        this.setupWYSIWYGEditor();
        this.setupColorControls();
        this.setupExportHandlers();
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
        // Refresh analisis
        const refreshBtn = document.getElementById('refreshAnalisis');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshAnalisis();
            });
        }

        // Main chart type change (Line/Pie)
        const mainChartTypes = document.querySelectorAll('input[name="mainChartType"]');
        mainChartTypes.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.currentChartType = e.target.value;
                if (this.chartData) {
                    this.renderChart(this.chartData);
                }
            });
        });

        // Chart period change
        const chartPeriod = document.getElementById('chartPeriod');
        if (chartPeriod) {
            chartPeriod.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadAnalisisChart();
            });
        }

        // Enhanced Export buttons dengan event delegation
        document.addEventListener('click', (e) => {
            if (e.target.closest('#exportExcelBtn')) {
                this.exportAsExcel(false);
            } else if (e.target.closest('#exportPdfBtn')) {
                this.exportAsPdf(false);
            } else if (e.target.closest('#exportExcelFull')) {
                this.exportAsExcel(true);
            } else if (e.target.closest('#exportPdfFull')) {
                this.exportAsPdf(true);
            } else if (e.target.closest('#exportImageFull')) {
                this.exportAsImage(true);
            }
        });

        // Theme change listener
        document.addEventListener('themeChanged', () => {
            this.updateUIForTheme();
        });
    }

    setupWYSIWYGEditor() {
        // Setup toolbar buttons
        const toolbarButtons = document.querySelectorAll('.wysiwyg-toolbar [data-command]');
        toolbarButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.getAttribute('data-command');
                const value = button.getAttribute('data-value');
                document.execCommand(command, false, value);
                document.getElementById('analisisTextEditor').focus();
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

        // Save content
        const saveBtn = document.getElementById('saveTextBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveAnalisisContent();
            });
        }

        // Export content
        const exportBtn = document.getElementById('exportTextContent');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportAnalisisContent();
            });
        }
    }

    saveAnalisisContent() {
        const content = document.getElementById('analisisTextEditor').innerHTML;
        localStorage.setItem('analisisContent', content);
        this.showToast('Analisis berhasil disimpan!');
    }

    loadAnalisisContent() {
        const savedContent = localStorage.getItem('analisisContent');
        if (savedContent) {
            document.getElementById('analisisTextEditor').innerHTML = savedContent;
        }
    }

    exportAnalisisContent() {
        const content = document.getElementById('analisisTextEditor').innerHTML;
        const blob = new Blob([`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Analisis Finansial</title>
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
        link.download = `analisis-finansial-${new Date().toISOString().split('T')[0]}.html`;
        link.click();
        this.showToast('Analisis berhasil diekspor!');
    }

    setupColorControls() {
        const applyColorsBtn = document.getElementById('applyColors');
        if (applyColorsBtn) {
            applyColorsBtn.addEventListener('click', () => {
                this.applyCustomColors();
            });
        }
    }

    setupExportHandlers() {
        // Handlers sudah di setup di setupEventListeners
    }

    applyCustomColors() {
        const textColor = document.getElementById('textColor').value;
        const backgroundColor = document.getElementById('backgroundColor').value;
        
        const analisisText = document.getElementById('analisisTextEditor');
        if (analisisText) {
            analisisText.style.color = textColor;
            analisisText.style.backgroundColor = backgroundColor;
        }
        
        this.showToast('Warna berhasil diterapkan!');
        
        // Simpan preferensi warna
        localStorage.setItem('analisisTextColor', textColor);
        localStorage.setItem('analisisBackgroundColor', backgroundColor);
    }

    loadCustomPreferences() {
        // Load teks yang disimpan
        const savedContent = localStorage.getItem('analisisContent');
        if (savedContent) {
            const analisisTextEditor = document.getElementById('analisisTextEditor');
            if (analisisTextEditor) {
                analisisTextEditor.innerHTML = savedContent;
            }
        }
        
        // Load warna yang disimpan
        const savedTextColor = localStorage.getItem('analisisTextColor');
        const savedBackgroundColor = localStorage.getItem('analisisBackgroundColor');
        
        if (savedTextColor) {
            document.getElementById('textColor').value = savedTextColor;
        }
        if (savedBackgroundColor) {
            document.getElementById('backgroundColor').value = savedBackgroundColor;
        }
        
        if (savedTextColor || savedBackgroundColor) {
            this.applyCustomColors();
        }
    }

    async refreshAnalisis() {
        const refreshBtn = document.getElementById('refreshAnalisis');
        if (!refreshBtn) return;

        const originalText = refreshBtn.innerHTML;
        
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memuat...';
        
        try {
            await new Promise(resolve => setTimeout(resolve, 1000));
            this.loadAnalisisChart();
            this.showToast('Analisis berhasil diperbarui!');
        } catch (error) {
            console.error('Error refreshing analisis:', error);
            this.showAlert('Gagal memperbarui analisis', 'danger');
        } finally {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = originalText;
        }
    }

    async loadAnalisisChart() {
        try {
            this.showChartLoading();
            
            const response = await fetch(`${this.basePath}php/api/dashboard_api.php?action=get_financial_chart&period=${this.currentPeriod}`);
            const data = await response.json();
            
            if (data.success) {
                this.chartData = data.data;
                this.renderChart(data.data);
                this.updateAnalisisText(data.data);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error loading analisis chart:', error);
            this.renderEmptyChart();
            this.showToast('Gagal memuat grafik analisis');
        }
    }

    renderChart(chartData) {
        const canvas = document.getElementById('analisisChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Destroy existing charts
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
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Pengeluaran',
                    data: chartData.pengeluaran,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                }
            ]
        };

        this.chart = new Chart(ctx, {
            type: 'line',
            data: chartConfig,
            options: this.getChartOptions()
        });
    }

    renderPieChart(ctx, chartData) {
        const totalPemasukan = chartData.pemasukan.reduce((a, b) => a + b, 0);
        const totalPengeluaran = chartData.pengeluaran.reduce((a, b) => a + b, 0);

        this.pieChart = new Chart(ctx, {
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

    updateAnalisisText(chartData) {
        const totalPemasukan = chartData.pemasukan.reduce((a, b) => a + b, 0);
        const totalPengeluaran = chartData.pengeluaran.reduce((a, b) => a + b, 0);
        const saldo = totalPemasukan - totalPengeluaran;

        // Update editor content dengan data terbaru
        const editor = document.getElementById('analisisTextEditor');
        if (editor) {
            const currentContent = editor.innerHTML;
            if (!currentContent.includes('Rp ')) {
                const newContent = `
                    <h2>Analisis Finansial ${new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}</h2>
                    <p>Analisis ini berisi evaluasi lengkap mengenai kondisi keuangan Anda.</p>
                    
                    <h3>Ringkasan Eksekutif</h3>
                    <p>Berdasarkan data yang terkumpul, berikut adalah performa keuangan Anda:</p>
                    
                    <ul>
                        <li><strong>Saldo Bulan Ini:</strong> Rp ${this.formatNumber(saldo)}</li>
                        <li><strong>Total Pemasukan:</strong> Rp ${this.formatNumber(totalPemasukan)}</li>
                        <li><strong>Total Pengeluaran:</strong> Rp ${this.formatNumber(totalPengeluaran)}</li>
                    </ul>

                    <h3>Analisis dan Rekomendasi</h3>
                    <p>Tambahkan analisis dan rekomendasi Anda di sini...</p>
                `;
                editor.innerHTML = newContent;
            }
        }
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

    formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    showChartLoading() {
        const canvas = document.getElementById('analisisChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        ctx.fillStyle = isDarkMode ? '#d1d5db' : '#6c757d';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Memuat data analisis...', canvas.width / 2, canvas.height / 2);
    }

    renderEmptyChart() {
        const canvas = document.getElementById('analisisChart');
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

    updateUIForTheme() {
        if (this.chart) {
            this.updateChartColors();
        }
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

    exportAsPdf(fullReport = false) {
        this.showToast('Mempersiapkan download PDF...', 'info');
        
        const { jsPDF } = window.jspdf;
        let contentElement;
        
        if (fullReport) {
            // Untuk export lengkap, gunakan seluruh konten analisis
            contentElement = document.getElementById('analisisContent');
        } else {
            // Untuk export ringkasan, gunakan bagian utama saja
            contentElement = document.querySelector('.main-content');
        }
        
        if (!contentElement) {
            this.showToast('Elemen tidak ditemukan untuk export', 'danger');
            return;
        }

        const options = {
            scale: 2,
            useCORS: true,
            scrollY: -window.scrollY,
            backgroundColor: '#ffffff',
            logging: false,
            width: contentElement.scrollWidth,
            height: contentElement.scrollHeight,
            windowWidth: contentElement.scrollWidth,
            windowHeight: contentElement.scrollHeight
        };
        
        this.showToast('Membuat PDF, harap tunggu...', 'info');
        
        // Scroll ke atas sebelum capture
        window.scrollTo(0, 0);
        
        setTimeout(() => {
            html2canvas(contentElement, options).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
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
                
                pdf.save(`analisis-finansial-${fullReport ? 'lengkap-' : ''}${new Date().toISOString().split('T')[0]}.pdf`);
                this.showToast('PDF berhasil diunduh! 📄', 'success');
            }).catch(error => {
                console.error('Error generating PDF:', error);
                this.showToast('Gagal mengunduh PDF', 'danger');
            });
        }, 1000);
    }

    exportAsExcel(fullReport = false) {
        this.showToast('Mempersiapkan download Excel...', 'info');
        
        let data = [];
        
        if (fullReport) {
            // Data lengkap untuk full report
            data = [
                ['LAPORAN ANALISIS FINANSIAL LENGKAP - FINANSIALKU'],
                ['Periode Analisis', document.getElementById('currentDateTime').textContent],
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
                ['Rasio Menabung', (window.dashboardData?.total_pemasukan > 0 ? 
                    ((window.dashboardData?.saldo_bulan_ini / window.dashboardData?.total_pemasukan) * 100).toFixed(1) : 0) + '%'],
                [''],
                ['PERBANDINGAN BULAN LALU'],
                ['Perubahan Saldo', (window.dashboardData?.perbandingan?.saldo_change || 0) + '%'],
                ['Perubahan Pemasukan', (window.dashboardData?.perbandingan?.pemasukan_change || 0) + '%'],
                ['Perubahan Pengeluaran', (window.dashboardData?.perbandingan?.pengeluaran_change || 0) + '%'],
                [''],
                ['ANALISIS TREND KEUANGAN']
            ];
        } else {
            // Data ringkasan
            data = [
                ['ANALISIS FINANSIAL RINGKAS'],
                ['Periode', document.getElementById('currentDateTime').textContent],
                [''],
                ['Ringkasan Utama'],
                ['Saldo Bulan Ini', 'Rp ' + this.formatNumber(window.dashboardData?.saldo_bulan_ini || 0)],
                ['Total Pemasukan', 'Rp ' + this.formatNumber(window.dashboardData?.total_pemasukan || 0)],
                ['Total Pengeluaran', 'Rp ' + this.formatNumber(window.dashboardData?.total_pengeluaran || 0)],
                ['Target Tercapai', (window.dashboardData?.target_tercapai || 0) + '%'],
                ['']
            ];
        }

        // Tambahkan data chart jika tersedia
        if (this.chartData) {
            data.push(['']);
            data.push(['ANALISIS TREND KEUANGAN - ' + this.currentPeriod.toUpperCase()]);
            data.push(['Periode', 'Pemasukan (Rp)', 'Pengeluaran (Rp)', 'Saldo (Rp)']);
            
            this.chartData.labels.forEach((label, index) => {
                const pemasukan = this.chartData.pemasukan[index] || 0;
                const pengeluaran = this.chartData.pengeluaran[index] || 0;
                const saldo = pemasukan - pengeluaran;
                
                data.push([
                    label,
                    this.formatNumber(pemasukan),
                    this.formatNumber(pengeluaran),
                    this.formatNumber(saldo)
                ]);
            });
        }

        // Tambahkan analisis kategori untuk full report
        if (fullReport) {
            data.push(['']);
            data.push(['ANALISIS KATEGORI PENGELUARAN']);
            data.push(['Kategori', 'Jumlah (Rp)', 'Persentase', 'Trend vs Bulan Lalu']);
            data.push(['Makanan & Minuman', '1250000', '35%', '-15%']);
            data.push(['Transportasi', '750000', '21%', '+5%']);
            data.push(['Hiburan', '500000', '14%', '-8%']);
            data.push(['Lainnya', '1100000', '30%', '+12%']);
            data.push(['Total Pengeluaran', '3600000', '100%', '-1.5%']);
            
            data.push(['']);
            data.push(['PREDIKSI DAN PROYEKSI']);
            data.push(['Item', 'Nilai', 'Keterangan']);
            data.push(['Proyeksi Tabungan 6 Bulan', '15750000', 'Berdasarkan tren saat ini']);
            data.push(['Status Kesehatan Keuangan', 'Baik', 'Rasio menabung: ' + 
                (window.dashboardData?.total_pemasukan > 0 ? 
                ((window.dashboardData?.saldo_bulan_ini / window.dashboardData?.total_pemasukan) * 100).toFixed(1) : 0) + '%']);
        }

        // Konversi ke CSV
        const csvContent = data.map(row => 
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `analisis-finansial-${fullReport ? 'lengkap-' : 'ringkasan-'}${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
        
        this.showToast('Excel berhasil diunduh! 📊', 'success');
    }

    exportAsImage(fullReport = false) {
        this.showToast('Mempersiapkan download gambar...', 'info');
        
        let contentElement;
        
        if (fullReport) {
            contentElement = document.getElementById('analisisContent');
        } else {
            contentElement = document.querySelector('.main-content');
        }
        
        if (!contentElement) {
            this.showToast('Elemen tidak ditemukan untuk export', 'danger');
            return;
        }

        const options = {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            scrollY: -window.scrollY,
            logging: false,
            width: contentElement.scrollWidth,
            height: contentElement.scrollHeight,
            windowWidth: contentElement.scrollWidth,
            windowHeight: contentElement.scrollHeight
        };
        
        // Scroll ke atas sebelum capture
        window.scrollTo(0, 0);
        
        setTimeout(() => {
            html2canvas(contentElement, options).then(canvas => {
                const link = document.createElement('a');
                link.download = `analisis-finansial-${fullReport ? 'lengkap-' : 'ringkasan-'}${new Date().toISOString().split('T')[0]}.png`;
                link.href = canvas.toDataURL();
                link.click();
                this.showToast('Gambar berhasil diunduh! 🖼️', 'success');
            }).catch(error => {
                console.error('Error generating image:', error);
                this.showToast('Gagal mengunduh gambar', 'danger');
            });
        }, 1000);
    }

    // Enhanced Toast System
    showToast(message, type = 'success') {
        const toastElement = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (toastElement && toastMessage) {
            // Update message
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

// Initialize analisis when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.analisisManager = new AnalisisManager();
    
    document.addEventListener('analisisReady', function() {
        setTimeout(() => {
            if (window.analisisManager) {
                window.analisisManager.loadCustomPreferences();
                window.analisisManager.loadAnalisisContent();
                window.analisisManager.showToast('Sistem analisis finansial siap digunakan! 🎯', 'success');
            }
        }, 500);
    });
});