/**
 * NT Tour Booking Admin JavaScript
 *
 * @since 0.1.0
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(document).ready(function() {
        initModals();
        initToast();
        initDataTables();
    });

    /**
     * Initialize modal functionality
     */
    function initModals() {
        // Open modal
        $(document).on('click', '.nt-modal-open', function(e) {
            e.preventDefault();
            var target = $(this).data('target') || $(this).attr('href');
            if (target && target !== '#') {
                $(target).removeClass('hidden');
            }
        });

        // Close modal on backdrop click
        $(document).on('click', '.nt-modal', function(e) {
            if ($(e.target).hasClass('nt-modal')) {
                $(this).addClass('hidden');
            }
        });

        // Close modal on close button
        $(document).on('click', '.nt-modal-close', function() {
            $(this).closest('.nt-modal').addClass('hidden');
        });

        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.nt-modal:not(.hidden)').addClass('hidden');
            }
        });
    }

    /**
     * Initialize toast notifications
     */
    function initToast() {
        // Auto-hide toast after 3 seconds
        window.showToast = function(type, message) {
            var toast = $('#nt-toast');
            if (!toast.length) return;

            var icon = type === 'success'
                ? '<i data-lucide="check-circle" class="text-green-500"></i>'
                : '<i data-lucide="x-circle" class="text-red-500"></i>';

            toast.find('.nt-toast-icon').html(icon);
            toast.find('.nt-toast-message').text(message);
            toast.removeClass('hidden');

            // Re-init Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Auto hide
            setTimeout(function() {
                toast.addClass('hidden');
            }, 3000);
        };
    }

    /**
     * Initialize DataTables with defaults
     */
    function initDataTables() {
        // Set DataTables defaults
        if ($.fn.DataTable) {
            $.extend(true, $.fn.DataTable.defaults, {
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                },
                drawCallback: function() {
                    // Re-init Lucide icons after table redraw
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            });
        }
    }

    /**
     * Serialize form to JSON
     */
    $.fn.serializeJSON = function() {
        var json = {};
        $.each(this.serializeArray(), function(i, field) {
            // Handle nested fields like field[subfield]
            var name = field.name;
            var value = field.value;

            if (name.indexOf('[') !== -1) {
                var parts = name.match(/([^\[]+)\[([^\]]+)\]/);
                if (parts) {
                    var key = parts[1];
                    var subkey = parts[2];
                    if (!json[key]) json[key] = {};
                    json[key][subkey] = value;
                }
            } else {
                json[name] = value;
            }
        });
        return json;
    };

    /**
     * Confirm delete action
     */
    window.ntConfirmDelete = function(message) {
        return confirm(message || ntAdmin.strings.confirm_delete || 'Bạn có chắc chắn muốn xóa?');
    };

    /**
     * Format currency
     */
    window.ntFormatCurrency = function(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    };

    /**
     * Format date
     */
    window.ntFormatDate = function(date, format) {
        format = format || 'dd/mm/yyyy';
        var d = new Date(date);
        var day = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year = d.getFullYear();

        return format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year);
    };

    /**
     * Debounce function
     */
    window.ntDebounce = function(func, wait) {
        var timeout;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    /**
     * API request helper
     */
    window.ntApiRequest = function(endpoint, method, data) {
        method = method || 'GET';
        var url = ntAdmin.apiUrl + '/' + endpoint.replace(/^\//, '');

        return $.ajax({
            url: url,
            method: method,
            data: data ? JSON.stringify(data) : null,
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', ntAdmin.nonce);
            }
        });
    };

    /**
     * Get URL parameters
     */
    window.ntGetUrlParams = function() {
        var params = {};
        var searchParams = new URLSearchParams(window.location.search);
        for (var pair of searchParams.entries()) {
            params[pair[0]] = pair[1];
        }
        return params;
    };

    /**
     * Set URL parameters (without pushing history state to avoid cross-origin issues)
     */
    window.ntSetUrlParams = function(params) {
        // Note: We don't use pushState here to avoid SecurityError with cross-origin iframes
        // This function can be extended if needed for URL state management
        var url = new URL(window.location);
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                url.searchParams.set(key, params[key]);
            }
        }
        // Only update URL if same origin, otherwise just return the URL string
        try {
            if (window.location.origin === url.origin) {
                window.history.replaceState({}, '', url);
            }
        } catch (e) {
            // Silently ignore cross-origin errors
        }
    };

    /**
     * Loading state for buttons
     */
    $(document).on('click', '.nt-btn-loading', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="nt-spinner"></span> ' + ntAdmin.strings.saving);
        return {
            done: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        };
    });

    /**
     * Copy to clipboard
     */
    window.ntCopyToClipboard = function(text, successMsg) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('success', successMsg || 'Đã copy!');
        }).catch(function() {
            // Fallback
            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToast('success', successMsg || 'Đã copy!');
        });
    };

    /**
     * Generate random string
     */
    window.ntRandomString = function(length) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = '';
        for (var i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    };

    /**
     * Validate email
     */
    window.ntValidateEmail = function(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    };

    /**
     * Validate phone (Vietnamese)
     */
    window.ntValidatePhone = function(phone) {
        var re = /^(0[0-9]{9,10})$/;
        return re.test(phone.replace(/\s/g, ''));
    };

})(jQuery);