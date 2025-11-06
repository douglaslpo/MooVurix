// This file is part of MooVurix - Based on Moodle
//
// Tubaron Gamification System - Charts Module (AMD)
//
// JavaScript module wrapper para Chart.js
//
// @package    local_tubaron
// @copyright  2025 Tubaron Telecomunicações
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

define(['jquery'], function($) {
    
    return {
        /**
         * Inicializar line chart (pontuação ao longo do tempo)
         */
        initLineChart: function(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        borderColor: ds.color || '#3b82f6',
                        backgroundColor: ds.color + '20' || '#3b82f620',
                        tension: 0.4,
                        fill: true
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        },
        
        /**
         * Atualizar charts com novos dados (AJAX)
         */
        updateCharts: function(chartIds, newData) {
            chartIds.forEach((id, index) => {
                const chart = Chart.getChart(id);
                if (chart && newData[index]) {
                    chart.data = newData[index];
                    chart.update();
                }
            });
        },
        
        /**
         * Refresh automático charts a cada X segundos
         */
        autoRefresh: function(ajaxUrl, interval) {
            setInterval(function() {
                $.ajax({
                    url: ajaxUrl,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.chartData) {
                            // Update KPIs
                            $('.tubaron-kpi-value').each(function(i) {
                                if (response.kpis && response.kpis[i]) {
                                    $(this).text(response.kpis[i]);
                                }
                            });
                        }
                    }
                });
            }, interval * 1000);
        }
    };
});

