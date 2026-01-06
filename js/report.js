// Report Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Hide loading screen
    setTimeout(() => {
        document.getElementById('loading').style.display = 'none';
    }, 1000);

    // Initialize Chart
    initChart();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize date inputs
    initDateInputs();
    
    // Show/hide custom date inputs based on report type
    updateDateInputVisibility();
    
    // Set up table rows for mobile
    setupResponsiveTable();
});

// Chart instance
let reportChart = null;

// Initialize the financial chart
function initChart() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    const chartType = document.getElementById('chartType').value;
    
    // Destroy existing chart if exists
    if (reportChart) {
        reportChart.destroy();
    }
    
    // Configure chart options based on type
    let config = {
        type: chartType === 'pie' ? 'pie' : (chartType === 'bar' ? 'bar' : 'line'),
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: getComputedStyle(document.body).getPropertyValue('--bs-body-color'),
                        font: {
                            size: 12
                        },
                        padding: 20
                    }
                },
                title: {
                    display: true,
                    text: 'Grafik Keuangan - ' + document.getElementById('reportPeriod').textContent,
                    color: getComputedStyle(document.body).getPropertyValue('--bs-body-color'),
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    };
    
    // Add scales for non-pie charts
    if (chartType !== 'pie') {
        config.options.scales = {
            x: {
                grid: {
                    display: true,
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    color: getComputedStyle(document.body).getPropertyValue('--bs-body-color'),
                    font: {
                        size: 11
                    },
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    color: getComputedStyle(document.body).getPropertyValue('--bs-body-color'),
                    font: {
                        size: 11
                    },
                    callback: function(value) {
                        if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                        } else if (value >= 1000) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                        }
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        };
    }
    
    reportChart = new Chart(ctx, config);
}

// Setup all event listeners
function setupEventListeners() {
    // Report type change
    document.getElementById('reportType').addEventListener('change', function() {
        updateDateInputVisibility();
        updateDateValues(this.value);
    });
    
    // Chart type change
    document.getElementById('chartType').addEventListener('change', function() {
        initChart();
    });
    
    // Refresh button
    document.getElementById('refreshReport').addEventListener('click', function() {
        refreshReport();
    });
    
    // Print button
    document.getElementById('printReport').addEventListener('click', function() {
        printReport();
    });
    
    // Export PDF button
    document.getElementById('exportPDF').addEventListener('click', function() {
        exportToPDF();
    });
    
    // Reset filter button
    document.getElementById('resetFilter').addEventListener('click', function() {
        resetFilter();
    });
    
    // Form submission
    document.getElementById('reportFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        filterReport();
    });
    
    // Start date change
    document.getElementById('startDate').addEventListener('change', function() {
        const endDate = document.getElementById('endDate');
        if (this.value > endDate.value) {
            endDate.value = this.value;
        }
    });
    
    // End date change
    document.getElementById('endDate').addEventListener('change', function() {
        const startDate = document.getElementById('startDate');
        if (this.value < startDate.value) {
            startDate.value = this.value;
        }
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + P untuk print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printReport();
        }
        
        // Ctrl + R untuk refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshReport();
        }
        
        // Ctrl + E untuk export PDF
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            exportToPDF();
        }
        
        // Escape untuk reset
        if (e.key === 'Escape') {
            resetFilter();
        }
    });
}

// Show loading screen
function showLoading() {
    document.getElementById('loading').style.display = 'flex';
}

// Hide loading screen
function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

// Update date input visibility based on report type
function updateDateInputVisibility() {
    const reportType = document.getElementById('reportType').value;
    const startDateGroup = document.getElementById('startDateGroup');
    const endDateGroup = document.getElementById('endDateGroup');
    
    if (reportType === 'custom') {
        startDateGroup.style.display = 'block';
        endDateGroup.style.display = 'block';
    } else {
        startDateGroup.style.display = 'none';
        endDateGroup.style.display = 'none';
    }
}

// Update date values based on report type
function updateDateValues(reportType) {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const today = new Date();
    
    switch(reportType) {
        case 'daily':
            startDateInput.value = today.toISOString().split('T')[0];
            endDateInput.value = today.toISOString().split('T')[0];
            break;
        case 'weekly':
            // Mulai Senin ini
            const monday = new Date(today);
            monday.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1));
            // Akhir Minggu ini
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            
            startDateInput.value = monday.toISOString().split('T')[0];
            endDateInput.value = sunday.toISOString().split('T')[0];
            break;
        case 'monthly':
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            startDateInput.value = firstDay.toISOString().split('T')[0];
            endDateInput.value = lastDay.toISOString().split('T')[0];
            break;
        case 'yearly':
            const firstDayYear = new Date(today.getFullYear(), 0, 1);
            const lastDayYear = new Date(today.getFullYear(), 11, 31);
            startDateInput.value = firstDayYear.toISOString().split('T')[0];
            endDateInput.value = lastDayYear.toISOString().split('T')[0];
            break;
    }
}

// Initialize date inputs with current date
function initDateInputs() {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    if (!startDateInput.value) {
        startDateInput.value = yesterday.toISOString().split('T')[0];
    }
    
    if (!endDateInput.value) {
        endDateInput.value = today.toISOString().split('T')[0];
    }
    
    // Set max date to today
    const todayStr = today.toISOString().split('T')[0];
    startDateInput.max = todayStr;
    endDateInput.max = todayStr;
}

// Setup responsive table
function setupResponsiveTable() {
    const table = document.getElementById('transactionsTable');
    if (!table) return;
    
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
            if (headers[index]) {
                cell.setAttribute('data-label', headers[index]);
            }
        });
    });
}

// Filter report
function filterReport() {
    showLoading();
    
    // Submit form
    document.getElementById('reportFilterForm').submit();
}

// Refresh report
function refreshReport() {
    showLoading();
    setTimeout(() => {
        location.reload();
    }, 500);
}

// Print the report
function printReport() {
    showLoading();
    
    // Capture chart as image for print
    captureChartForPrint().then((chartDataURL) => {
        // Add print-specific styles
        const printStyles = `
            <style>
                @media print {
                    body {
                        background: white !important;
                        color: black !important;
                        font-size: 12pt !important;
                        line-height: 1.5 !important;
                        font-family: 'Arial', sans-serif !important;
                    }
                    
                    body * {
                        color: black !important;
                        background-color: transparent !important;
                        border-color: #000 !important;
                    }
                    
                    .navbar, .filter-section, .btn-toolbar, .footer, 
                    #printReport, #exportPDF, #refreshReport, .btn-group,
                    .btn-outline-light, .no-print {
                        display: none !important;
                    }
                    
                    .report-header {
                        background: white !important;
                        color: black !important;
                        border: 2px solid #000 !important;
                        padding: 20px !important;
                        margin-bottom: 30px !important;
                        border-radius: 8px !important;
                    }
                    
                    .report-header h1 {
                        color: black !important;
                        font-size: 24pt !important;
                        margin-bottom: 10px !important;
                    }
                    
                    .card {
                        border: 2px solid #000 !important;
                        break-inside: avoid;
                        box-shadow: none !important;
                        margin-bottom: 25px !important;
                        page-break-inside: avoid !important;
                        border-radius: 8px !important;
                        overflow: hidden !important;
                    }
                    
                    .card-header {
                        background: #f0f0f0 !important;
                        color: #000 !important;
                        border-bottom: 2px solid #000 !important;
                        font-weight: bold !important;
                        font-size: 14pt !important;
                        padding: 15px !important;
                    }
                    
                    .card-body {
                        padding: 20px !important;
                    }
                    
                    .table {
                        border: 2px solid #000 !important;
                        font-size: 10pt !important;
                        width: 100% !important;
                        page-break-inside: avoid !important;
                        border-collapse: collapse !important;
                    }
                    
                    .table th, .table td {
                        border: 1px solid #000 !important;
                        padding: 10px !important;
                        color: #000 !important;
                        background: white !important;
                        text-align: left !important;
                    }
                    
                    .table th {
                        background-color: #f0f0f0 !important;
                        font-weight: bold !important;
                        border-bottom: 2px solid #000 !important;
                    }
                    
                    .table tfoot td {
                        font-weight: bold !important;
                        background-color: #f8f9fa !important;
                        border-top: 2px solid #000 !important;
                    }
                    
                    .badge {
                        border: 1px solid #000 !important;
                        color: #000 !important;
                        background: white !important;
                        font-weight: bold !important;
                        padding: 4px 8px !important;
                        border-radius: 4px !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    .progress {
                        border: 1px solid #000 !important;
                        background-color: #e9ecef !important;
                        height: 10px !important;
                        border-radius: 5px !important;
                        overflow: hidden !important;
                    }
                    
                    .progress-bar {
                        background-color: #000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        height: 100% !important;
                    }
                    
                    h1, h2, h3, h4, h5, h6 {
                        color: #000 !important;
                        font-weight: bold !important;
                        page-break-after: avoid !important;
                    }
                    
                    .chart-container {
                        height: 300px !important;
                        border: 1px solid #000 !important;
                        padding: 15px !important;
                        background: white !important;
                        page-break-inside: avoid !important;
                        margin-bottom: 20px !important;
                    }
                    
                    canvas {
                        display: none !important;
                    }
                    
                    .chart-image {
                        display: block !important;
                        width: 100% !important;
                        height: auto !important;
                        max-height: 280px !important;
                        object-fit: contain !important;
                        border: 1px solid #ddd !important;
                        padding: 10px !important;
                        background: white !important;
                    }
                    
                    .text-success {
                        color: #006400 !important;
                        font-weight: bold !important;
                    }
                    
                    .text-danger {
                        color: #8B0000 !important;
                        font-weight: bold !important;
                    }
                    
                    .bg-success {
                        background-color: #90EE90 !important;
                        color: #000 !important;
                        border: 1px solid #006400 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    .bg-danger {
                        background-color: #FFB6C1 !important;
                        color: #000 !important;
                        border: 1px solid #8B0000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    .report-footer {
                        background: #f9f9f9 !important;
                        border: 2px solid #000 !important;
                        margin-top: 40px !important;
                        padding: 20px !important;
                        page-break-inside: avoid !important;
                        border-radius: 8px !important;
                    }
                    
                    .summary-card {
                        border: 2px solid #000 !important;
                        background: white !important;
                        page-break-inside: avoid !important;
                        padding: 15px !important;
                        margin-bottom: 20px !important;
                    }
                    
                    .text-muted {
                        color: #666 !important;
                    }
                    
                    .text-print-dark {
                        color: #000 !important;
                        font-weight: normal !important;
                    }
                    
                    .summary-icon {
                        background: #f0f0f0 !important;
                        border: 1px solid #000 !important;
                        border-radius: 50% !important;
                        width: 50px !important;
                        height: 50px !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        font-size: 20px !important;
                    }
                    
                    .row {
                        display: flex !important;
                        flex-wrap: wrap !important;
                        margin-right: -15px !important;
                        margin-left: -15px !important;
                    }
                    
                    .col-md-3, .col-md-6, .col-12 {
                        position: relative !important;
                        width: 100% !important;
                        padding-right: 15px !important;
                        padding-left: 15px !important;
                    }
                    
                    @media print {
                        .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                        }
                        .col-md-6 {
                            flex: 0 0 50% !important;
                            max-width: 50% !important;
                        }
                    }
                    
                    @page {
                        margin: 20mm;
                        size: A4 portrait;
                    }
                    
                    @page :first {
                        margin-top: 30mm;
                    }
                }
            </style>
        `;
        
        // Clone report content
        const reportContent = document.getElementById('reportContent').cloneNode(true);
        
        // Replace chart with image
        const chartContainer = reportContent.querySelector('.chart-container');
        if (chartContainer && chartDataURL) {
            const canvas = chartContainer.querySelector('canvas');
            if (canvas) {
                const img = document.createElement('img');
                img.src = chartDataURL;
                img.className = 'chart-image';
                img.alt = 'Grafik Keuangan';
                canvas.parentNode.replaceChild(img, canvas);
            }
        }
        
        // Remove unnecessary elements
        const elementsToRemove = reportContent.querySelectorAll('.no-print, .btn, .form-control, .form-select');
        elementsToRemove.forEach(el => el.remove());
        
        // Create print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan Keuangan - ${document.getElementById('reportPeriod').textContent}</title>
                ${printStyles}
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container-fluid">
                    <div class="report-header">
                        <h1><i class="fas fa-chart-bar"></i> LAPORAN KEUANGAN</h1>
                        <p style="margin: 10px 0 5px 0; font-size: 16px;">
                            <i class="fas fa-calendar-alt"></i> Periode: ${document.getElementById('reportPeriod').textContent}
                        </p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <i class="fas fa-user"></i> Oleh: ${document.querySelector('.user-name')?.textContent || 'User'}
                        </p>
                        <p style="margin: 5px 0 0 0; font-size: 12px;">
                            <i class="fas fa-clock"></i> Dicetak: ${new Date().toLocaleDateString('id-ID', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </p>
                    </div>
                    
                    ${reportContent.innerHTML}
                </div>
                <script>
                    window.onload = function() {
                        // Ensure images are loaded
                        const images = document.querySelectorAll('img');
                        let loadedCount = 0;
                        const totalImages = images.length;
                        
                        if (totalImages === 0) {
                            triggerPrint();
                            return;
                        }
                        
                        images.forEach(img => {
                            if (img.complete) {
                                loadedCount++;
                            } else {
                                img.onload = function() {
                                    loadedCount++;
                                    if (loadedCount === totalImages) {
                                        triggerPrint();
                                    }
                                };
                                img.onerror = function() {
                                    loadedCount++;
                                    if (loadedCount === totalImages) {
                                        triggerPrint();
                                    }
                                };
                            }
                        });
                        
                        if (loadedCount === totalImages) {
                            triggerPrint();
                        }
                        
                        function triggerPrint() {
                            setTimeout(() => {
                                window.print();
                                setTimeout(() => {
                                    window.close();
                                }, 1000);
                            }, 500);
                        }
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
        
        hideLoading();
    }).catch(error => {
        console.error('Error capturing chart:', error);
        hideLoading();
        showToast('Gagal menangkap grafik. Silakan coba lagi.', 'danger');
    });
}

// Capture chart as image for print/PDF
function captureChartForPrint() {
    return new Promise((resolve, reject) => {
        if (!reportChart) {
            reject('Chart not initialized');
            return;
        }
        
        // Get chart canvas
        const canvas = document.getElementById('reportChart');
        
        // Create a temporary canvas with higher resolution for print
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width * 2; // Higher resolution for print
        tempCanvas.height = canvas.height * 2;
        const tempCtx = tempCanvas.getContext('2d');
        
        // Fill with white background
        tempCtx.fillStyle = 'white';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        // Scale and draw original chart
        tempCtx.scale(2, 2);
        tempCtx.drawImage(canvas, 0, 0);
        
        // Convert to data URL
        const chartDataURL = tempCanvas.toDataURL('image/png', 1.0);
        
        resolve(chartDataURL);
    });
}

// Export report to PDF
function exportToPDF() {
    showLoading();
    
    // First capture the chart as image
    captureChartForPrint().then((chartDataURL) => {
        // Create a copy of report content
        const reportContent = document.getElementById('reportContent').cloneNode(true);
        
        // Replace chart with image for PDF
        const chartContainer = reportContent.querySelector('.chart-container');
        if (chartContainer && chartDataURL) {
            const canvas = chartContainer.querySelector('canvas');
            if (canvas) {
                const img = document.createElement('img');
                img.src = chartDataURL;
                img.style.width = '100%';
                img.style.height = 'auto';
                img.style.maxHeight = '300px';
                img.style.objectFit = 'contain';
                img.alt = 'Grafik Keuangan';
                img.className = 'chart-image-export';
                canvas.parentNode.replaceChild(img, canvas);
            }
        }
        
        // Remove unnecessary elements
        const elementsToRemove = reportContent.querySelectorAll('.no-print, .btn, .form-control, .form-select');
        elementsToRemove.forEach(el => el.remove());
        
        // Create PDF content
        const pdfContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan Keuangan - ${document.getElementById('reportPeriod').textContent}</title>
                <meta charset="UTF-8">
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                    
                    body {
                        font-family: 'Inter', 'Arial', sans-serif;
                        background: white;
                        color: #333;
                        padding: 25px;
                        font-size: 12px;
                        line-height: 1.6;
                        margin: 0;
                    }
                    
                    .report-header {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 25px;
                        border-radius: 12px;
                        margin-bottom: 30px;
                        text-align: center;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    }
                    
                    .report-header h1 {
                        color: white;
                        margin: 0 0 15px 0;
                        font-size: 28px;
                        font-weight: 700;
                    }
                    
                    .report-header p {
                        margin: 8px 0;
                        font-size: 14px;
                        opacity: 0.9;
                    }
                    
                    .card {
                        border: 2px solid #e0e0e0;
                        border-radius: 10px;
                        margin-bottom: 25px;
                        page-break-inside: avoid;
                        background: white;
                        overflow: hidden;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                    }
                    
                    .card-header {
                        background: #f8f9fa;
                        color: #2c3e50;
                        padding: 15px 20px;
                        border-bottom: 2px solid #e0e0e0;
                        font-weight: 600;
                        font-size: 16px;
                    }
                    
                    .card-body {
                        padding: 20px;
                    }
                    
                    .table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                        font-size: 11px;
                    }
                    
                    .table th {
                        background-color: #f8f9fa;
                        border: 1px solid #dee2e6;
                        padding: 12px 8px;
                        text-align: left;
                        font-weight: 600;
                        color: #2c3e50;
                        font-size: 11px;
                    }
                    
                    .table td {
                        border: 1px solid #dee2e6;
                        padding: 10px 8px;
                        color: #495057;
                        font-size: 11px;
                    }
                    
                    .table tfoot td {
                        font-weight: 600;
                        background-color: #f8f9fa;
                        border-top: 2px solid #dee2e6;
                    }
                    
                    .summary-card {
                        border: 2px solid #e0e0e0;
                        border-radius: 8px;
                        padding: 20px;
                        margin-bottom: 20px;
                        background: white;
                        border-left: 4px solid;
                    }
                    
                    .summary-card.border-start-primary {
                        border-left-color: #667eea;
                    }
                    
                    .summary-card.border-start-success {
                        border-left-color: #28a745;
                    }
                    
                    .summary-card.border-start-danger {
                        border-left-color: #dc3545;
                    }
                    
                    .summary-card.border-start-info {
                        border-left-color: #17a2b8;
                    }
                    
                    .summary-card.border-start-warning {
                        border-left-color: #ffc107;
                    }
                    
                    .badge {
                        padding: 5px 10px;
                        border-radius: 4px;
                        font-size: 11px;
                        font-weight: 500;
                        display: inline-block;
                    }
                    
                    .bg-success {
                        background-color: #d4edda !important;
                        color: #155724 !important;
                        border: 1px solid #c3e6cb;
                    }
                    
                    .bg-danger {
                        background-color: #f8d7da !important;
                        color: #721c24 !important;
                        border: 1px solid #f5c6cb;
                    }
                    
                    .progress {
                        height: 8px;
                        background-color: #e9ecef;
                        border-radius: 4px;
                        overflow: hidden;
                        border: 1px solid #dee2e6;
                        margin: 8px 0;
                    }
                    
                    .progress-bar {
                        height: 100%;
                    }
                    
                    .progress-bar.bg-success {
                        background-color: #28a745 !important;
                    }
                    
                    .progress-bar.bg-danger {
                        background-color: #dc3545 !important;
                    }
                    
                    .chart-container {
                        height: 320px;
                        margin-bottom: 25px;
                        border: 1px solid #e0e0e0;
                        padding: 15px;
                        background: white;
                        border-radius: 8px;
                    }
                    
                    .chart-image-export {
                        width: 100%;
                        height: auto;
                        max-height: 280px;
                        object-fit: contain;
                        display: block;
                        margin: 0 auto;
                    }
                    
                    .report-footer {
                        background-color: #f8f9fa;
                        padding: 25px;
                        border-radius: 10px;
                        margin-top: 40px;
                        border: 2px solid #e0e0e0;
                        page-break-inside: avoid;
                    }
                    
                    .text-success {
                        color: #155724 !important;
                        font-weight: 600;
                    }
                    
                    .text-danger {
                        color: #721c24 !important;
                        font-weight: 600;
                    }
                    
                    .text-print-dark {
                        color: #212529 !important;
                    }
                    
                    h1, h2, h3, h4, h5, h6 {
                        color: #2c3e50 !important;
                        font-weight: 600;
                        page-break-after: avoid;
                    }
                    
                    .text-center {
                        text-align: center;
                    }
                    
                    .text-end {
                        text-align: right;
                    }
                    
                    .fw-bold {
                        font-weight: 600 !important;
                    }
                    
                    .mb-0 {
                        margin-bottom: 0 !important;
                    }
                    
                    .mb-1 {
                        margin-bottom: 0.5rem !important;
                    }
                    
                    .mb-2 {
                        margin-bottom: 1rem !important;
                    }
                    
                    .mb-3 {
                        margin-bottom: 1.5rem !important;
                    }
                    
                    .mb-4 {
                        margin-bottom: 2rem !important;
                    }
                    
                    .mt-1 {
                        margin-top: 0.5rem !important;
                    }
                    
                    .mt-2 {
                        margin-top: 1rem !important;
                    }
                    
                    .mt-3 {
                        margin-top: 1.5rem !important;
                    }
                    
                    .mt-4 {
                        margin-top: 2rem !important;
                    }
                    
                    .row {
                        display: flex;
                        flex-wrap: wrap;
                        margin-right: -15px;
                        margin-left: -15px;
                    }
                    
                    .col-md-3, .col-md-6, .col-12 {
                        position: relative;
                        width: 100%;
                        padding-right: 15px;
                        padding-left: 15px;
                        box-sizing: border-box;
                    }
                    
                    @media (min-width: 768px) {
                        .col-md-3 {
                            flex: 0 0 25%;
                            max-width: 25%;
                        }
                        .col-md-6 {
                            flex: 0 0 50%;
                            max-width: 50%;
                        }
                    }
                    
                    .d-flex {
                        display: flex !important;
                    }
                    
                    .align-items-center {
                        align-items: center !important;
                    }
                    
                    .justify-content-between {
                        justify-content: space-between !important;
                    }
                    
                    .flex-wrap {
                        flex-wrap: wrap !important;
                    }
                    
                    .flex-md-nowrap {
                        flex-wrap: nowrap !important;
                    }
                    
                    .opacity-75 {
                        opacity: 0.75 !important;
                    }
                    
                    .text-light {
                        color: #f8f9fa !important;
                    }
                    
                    .animate__animated {
                        animation-duration: 0.5s;
                    }
                    
                    .animate__fadeInDown {
                        animation-name: fadeInDown;
                    }
                    
                    .animate__fadeIn {
                        animation-name: fadeIn;
                    }
                    
                    .animate__fadeInUp {
                        animation-name: fadeInUp;
                    }
                    
                    @keyframes fadeInDown {
                        from {
                            opacity: 0;
                            transform: translateY(-20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                        }
                        to {
                            opacity: 1;
                        }
                    }
                    
                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    @page {
                        margin: 20mm;
                        size: A4 portrait;
                    }
                    
                    @media print {
                        body {
                            padding: 0;
                        }
                        
                        .report-header {
                            box-shadow: none;
                            margin-bottom: 25px;
                        }
                        
                        .card {
                            box-shadow: none;
                            margin-bottom: 20px;
                        }
                        
                        .page-break {
                            page-break-before: always;
                        }
                        
                        .avoid-break {
                            page-break-inside: avoid;
                        }
                    }
                    
                    /* Utility classes for PDF */
                    .w-100 {
                        width: 100% !important;
                    }
                    
                    .h-100 {
                        height: 100% !important;
                    }
                    
                    .border-bottom {
                        border-bottom: 1px solid #dee2e6 !important;
                    }
                    
                    .table-active {
                        background-color: rgba(0, 0, 0, 0.05) !important;
                    }
                    
                    .table-primary {
                        background-color: rgba(102, 126, 234, 0.1) !important;
                    }
                    
                    .table-hover tbody tr:hover {
                        background-color: rgba(0, 0, 0, 0.02) !important;
                    }
                    
                    .list-unstyled {
                        list-style: none !important;
                        padding-left: 0 !important;
                    }
                    
                    .rounded {
                        border-radius: 6px !important;
                    }
                    
                    .shadow {
                        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05) !important;
                    }
                    
                    .p-0 {
                        padding: 0 !important;
                    }
                    
                    .p-2 {
                        padding: 0.5rem !important;
                    }
                    
                    .p-3 {
                        padding: 1rem !important;
                    }
                    
                    .me-1 {
                        margin-right: 0.25rem !important;
                    }
                    
                    .me-2 {
                        margin-right: 0.5rem !important;
                    }
                    
                    .me-3 {
                        margin-right: 1rem !important;
                    }
                    
                    .ms-1 {
                        margin-left: 0.25rem !important;
                    }
                    
                    .ms-2 {
                        margin-left: 0.5rem !important;
                    }
                    
                    .text-xs {
                        font-size: 0.75rem !important;
                    }
                    
                    .text-uppercase {
                        text-transform: uppercase !important;
                    }
                    
                    .h5 {
                        font-size: 1.25rem !important;
                    }
                    
                    .h2 {
                        font-size: 2rem !important;
                    }
                    
                    .h6 {
                        font-size: 1rem !important;
                    }
                    
                    .small {
                        font-size: 0.875rem !important;
                    }
                    
                    .table-responsive {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                    }
                    
                    /* Ensure proper page breaks */
                    .summary-grid {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        gap: 20px;
                        margin-bottom: 30px;
                    }
                    
                    @media (max-width: 768px) {
                        .summary-grid {
                            grid-template-columns: repeat(2, 1fr);
                        }
                    }
                    
                    @media print {
                        .summary-grid {
                            grid-template-columns: repeat(2, 1fr) !important;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <h1><i class="fas fa-chart-bar"></i> LAPORAN KEUANGAN</h1>
                    <p style="margin: 10px 0 5px 0; font-size: 16px;">
                        <i class="fas fa-calendar-alt"></i> Periode: ${document.getElementById('reportPeriod').textContent}
                    </p>
                    <p style="margin: 5px 0; font-size: 14px;">
                        <i class="fas fa-user"></i> Oleh: ${document.querySelector('.user-name')?.textContent || 'User'}
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 12px;">
                        <i class="fas fa-clock"></i> Dicetak: ${new Date().toLocaleDateString('id-ID', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </p>
                </div>
                
                ${reportContent.innerHTML}
            </body>
            </html>
        `;
        
        // Create temporary element for PDF generation
        const element = document.createElement('div');
        element.innerHTML = pdfContent;
        
        // Wait for images to load
        const images = element.querySelectorAll('img');
        let imagesLoaded = 0;
        
        if (images.length === 0) {
            generatePDF(element);
            return;
        }
        
        images.forEach(img => {
            if (img.complete) {
                imagesLoaded++;
            } else {
                img.onload = () => {
                    imagesLoaded++;
                    if (imagesLoaded === images.length) {
                        generatePDF(element);
                    }
                };
                img.onerror = () => {
                    imagesLoaded++;
                    if (imagesLoaded === images.length) {
                        generatePDF(element);
                    }
                };
            }
        });
        
        if (imagesLoaded === images.length) {
            generatePDF(element);
        }
        
    }).catch(error => {
        console.error('Error capturing chart:', error);
        hideLoading();
        showToast('Gagal menangkap grafik. Silakan coba lagi.', 'danger');
    });
}

// Generate PDF from element
function generatePDF(element) {
    // PDF options
    const opt = {
        margin: [15, 15, 15, 15],
        filename: `Laporan-Keuangan-${new Date().toISOString().split('T')[0]}.pdf`,
        image: { 
            type: 'jpeg', 
            quality: 0.98 
        },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: false,
            letterRendering: true,
            backgroundColor: '#FFFFFF',
            windowWidth: 1200
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait',
            compress: true,
            hotfixes: ["px_scaling"]
        },
        pagebreak: { 
            mode: ['avoid-all', 'css', 'legacy'],
            before: '.page-break',
            after: '.page-break'
        }
    };
    
    // Generate and save PDF
    html2pdf().set(opt).from(element).save().then(() => {
        hideLoading();
        showToast('PDF berhasil diekspor!', 'success');
    }).catch((error) => {
        console.error('PDF export error:', error);
        hideLoading();
        showToast('Gagal mengekspor PDF. Silakan coba lagi.', 'danger');
    });
}

// Reset filter to default values
function resetFilter() {
    document.getElementById('reportType').value = 'monthly';
    document.getElementById('chartType').value = 'line';
    updateDateValues('monthly');
    updateDateInputVisibility();
    
    // Reset form
    document.getElementById('reportFilterForm').reset();
    
    showToast('Filter telah direset', 'info');
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Update report period display
function updateReportPeriodDisplay(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    const formatDate = (date) => {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    };
    
    document.getElementById('reportPeriod').textContent = 
        `${formatDate(start)} - ${formatDate(end)}`;
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.id = toastId;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set icon based on type
    let icon = 'info-circle';
    switch(type) {
        case 'success': icon = 'check-circle'; break;
        case 'danger': icon = 'exclamation-circle'; break;
        case 'warning': icon = 'exclamation-triangle'; break;
        case 'info': icon = 'info-circle'; break;
    }
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast, { 
        delay: 3000,
        animation: true
    });
    bsToast.show();
    
    // Remove toast after hide
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

// Add keyboard shortcut hints
function addKeyboardHints() {
    const shortcuts = [
        { key: 'Ctrl+P', action: 'Cetak Laporan' },
        { key: 'Ctrl+E', action: 'Ekspor PDF' },
        { key: 'Ctrl+R', action: 'Refresh' },
        { key: 'Esc', action: 'Reset Filter' }
    ];
    
    const hintContainer = document.createElement('div');
    hintContainer.className = 'keyboard-hints position-fixed bottom-0 end-0 p-3 d-none d-md-block';
    hintContainer.innerHTML = `
        <div class="card bg-dark bg-opacity-75 border-0">
            <div class="card-body p-2">
                <small class="text-muted d-block mb-1">Keyboard Shortcuts:</small>
                ${shortcuts.map(s => `
                    <div class="d-flex align-items-center mb-1">
                        <kbd class="bg-dark border border-dark me-2">${s.key}</kbd>
                        <small class="text-light">${s.action}</small>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    document.body.appendChild(hintContainer);
}

// Initialize everything when page loads
window.addEventListener('load', function() {
    initTooltips();
    addKeyboardHints();
    
    // Show welcome message if first visit
    if (!localStorage.getItem('reportVisited')) {
        setTimeout(() => {
            showToast('Selamat datang di Laporan Keuangan! Gunakan filter untuk melihat data.', 'info');
            localStorage.setItem('reportVisited', 'true');
        }, 1500);
    }
});

// Add this at the end to initialize everything
document.addEventListener('DOMContentLoaded', function() {
    // Your existing initialization code
    setTimeout(() => {
        initTooltips();
        addKeyboardHints();
    }, 100);
});