// This file is part of MooVurix - Based on Moodle
//
// Tubaron Gamification System - Filters Module (AMD)
//
// JavaScript module para filtros dinâmicos e interações
//
// @package    local_tubaron
// @copyright  2025 Tubaron Telecomunicações
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    
    return {
        /**
         * Inicializar filtros de data range
         */
        initDateRangeFilter: function(container) {
            const form = $(container).find('.tubaron-filter-form');
            const fromInput = form.find('input[name="from"]');
            const toInput = form.find('input[name="to"]');
            
            // Preset buttons
            form.find('.tubaron-preset-btn').on('click', function(e) {
                e.preventDefault();
                const preset = $(this).data('preset');
                const now = new Date();
                let from, to;
                
                switch(preset) {
                    case 'today':
                        from = new Date(now.setHours(0,0,0,0));
                        to = new Date(now.setHours(23,59,59,999));
                        break;
                    case 'last_7_days':
                        from = new Date(now.setDate(now.getDate() - 7));
                        to = new Date();
                        break;
                    case 'last_30_days':
                        from = new Date(now.setDate(now.getDate() - 30));
                        to = new Date();
                        break;
                    case 'this_month':
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                        to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        break;
                }
                
                fromInput.val(from.toISOString().split('T')[0]);
                toInput.val(to.toISOString().split('T')[0]);
                form.submit();
            });
        },
        
        /**
         * Filtros live search
         */
        initLiveSearch: function(searchInput, targetGrid) {
            let timeout = null;
            
            $(searchInput).on('keyup', function() {
                clearTimeout(timeout);
                const query = $(this).val().toLowerCase();
                
                timeout = setTimeout(function() {
                    $(targetGrid).find('.tubaron-task-card, .tubaron-team-card').each(function() {
                        const title = $(this).find('.tubaron-task-title, .tubaron-team-name').text().toLowerCase();
                        
                        if (title.includes(query) || query === '') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }, 300);
            });
        },
        
        /**
         * Aplicar múltiplos filtros
         */
        applyMultipleFilters: function(filters) {
            const params = new URLSearchParams(filters);
            window.location.href = window.location.pathname + '?' + params.toString();
        },
        
        /**
         * Persistir filtros em URL
         */
        persistFiltersToURL: function(filterData) {
            const url = new URL(window.location);
            Object.keys(filterData).forEach(key => {
                if (filterData[key]) {
                    url.searchParams.set(key, filterData[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.history.pushState({}, '', url);
        },
        
        /**
         * Restaurar filtros da URL
         */
        restoreFiltersFromURL: function() {
            const params = new URLSearchParams(window.location.search);
            const filters = {};
            
            for (let [key, value] of params) {
                filters[key] = value;
            }
            
            return filters;
        }
    };
});

