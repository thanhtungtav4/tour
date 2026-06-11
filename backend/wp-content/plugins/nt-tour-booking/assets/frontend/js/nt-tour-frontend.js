/**
 * NT Tour Booking Frontend JavaScript
 *
 * @since 0.1.0
 */

(function($) {
    'use strict';

    var NTour = {
        apiUrl: ntTourApiUrl || '',
        nonce: ntTourNonce || '',
        selectedSeats: [],
        departureData: null,

        init: function() {
            this.bindEvents();
            this.initSeatMap();
        },

        bindEvents: function() {
            // Price calculation
            $('#adult_count, #child_count, #infant_count').on('input', this.calculateTotal.bind(this));

            // Seat selection
            $(document).on('click', '.nt-seat--available', this.selectSeat.bind(this));

            // Form submission
            $('#bookingForm').on('submit', this.submitBooking.bind(this));
        },

        initSeatMap: function() {
            var $container = $('#seat-map');
            if (!$container.length) return;

            // Generate seat map from layout data
            var layout = this.departureData && this.departureData.layout;
            if (!layout) return;

            $container.empty();

            layout.forEach(function(row) {
                var $row = $('<div class="nt-seat-row"></div>');
                row.forEach(function(seat) {
                    if (seat === null || seat === 'aisle' || seat === 'toilet') {
                        $row.append('<div class="nt-seat nt-seat--blocked"></div>');
                    } else if (seat === 'driver') {
                        $row.append('<div class="nt-seat nt-seat--driver">Tài xế</div>');
                    } else {
                        var $seat = $('<div class="nt-seat nt-seat--available" data-seat="' + seat + '">' + seat + '</div>');
                        $row.append($seat);
                    }
                });
                $container.append($row);
            });
        },

        selectSeat: function(e) {
            var $seat = $(e.currentTarget);
            var seatCode = $seat.data('seat');
            var $selectedContainer = $('#selected-seats-display');

            if ($seat.hasClass('nt-seat--selected')) {
                $seat.removeClass('nt-seat--selected');
                this.selectedSeats = this.selectedSeats.filter(function(s) { return s !== seatCode; });
            } else {
                // Check max seats
                var maxSeats = this.getTotalPassengers();
                if (this.selectedSeats.length >= maxSeats) {
                    alert('Bạn chỉ được chọn tối đa ' + maxSeats + ' ghế.');
                    return;
                }
                $seat.addClass('nt-seat--selected');
                this.selectedSeats.push(seatCode);
            }

            $selectedContainer.text(this.selectedSeats.join(', ') || 'Chưa chọn');
        },

        getTotalPassengers: function() {
            var adult = parseInt($('#adult_count').val()) || 0;
            var child = parseInt($('#child_count').val()) || 0;
            var infant = parseInt($('#infant_count').val()) || 0;
            return adult + child + infant;
        },

        calculateTotal: function() {
            var adult = parseInt($('#adult_count').val()) || 0;
            var child = parseInt($('#child_count').val()) || 0;
            var infant = parseInt($('#infant_count').val()) || 0;

            var adultPrice = parseFloat($('#adult-price').data('price') || 0);
            var childPrice = parseFloat($('#child-price').data('price') || 0);

            var total = (adult * adultPrice) + (child * childPrice);

            $('#total-price').text(this.formatMoney(total));
            $('#seat-section').toggle(adult + child > 0);
        },

        formatMoney: function(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        },

        submitBooking: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $submitBtn = $('#booking-submit');
            var $loading = $('#nt-loading');

            // Validate
            if (!this.validateForm()) {
                return;
            }

            $loading.show();
            $submitBtn.prop('disabled', true);

            var formData = {
                tour_departure_id: this.departureData ? this.departureData.id : 0,
                customer_name: $('#customer_name').val(),
                customer_phone: $('#customer_phone').val(),
                customer_email: $('#customer_email').val() || null,
                adult_count: parseInt($('#adult_count').val()) || 1,
                child_count: parseInt($('#child_count').val()) || 0,
                infant_count: parseInt($('#infant_count').val()) || 0,
                pickup_point_id: parseInt($('#pickup_point_id').val()) || null,
                seat_selection_mode: this.selectedSeats.length > 0 ? 'customer_select' : 'admin_assign',
                seat_codes: this.selectedSeats,
            };

            $.ajax({
                url: this.apiUrl + '/bookings',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', this.nonce);
                }.bind(this),
                success: function(response) {
                    $loading.hide();

                    if (response.success) {
                        // Redirect to success page or show confirmation
                        var bookingCode = response.data.booking.code;
                        alert('Đặt tour thành công!\n\nMã booking: ' + bookingCode);
                        $form[0].reset();
                        this.selectedSeats = [];
                        $('#selected-seats-display').text('Chưa chọn');
                        $('.nt-seat--selected').removeClass('nt-seat--selected');
                    } else {
                        alert('Có lỗi xảy ra: ' + response.message);
                    }
                }.bind(this),
                error: function(xhr) {
                    $loading.hide();
                    $submitBtn.prop('disabled', false);
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            });
        },

        validateForm: function() {
            var isValid = true;
            var errors = [];

            if (!$('#customer_name').val().trim()) {
                errors.push('Vui lòng nhập họ tên');
                isValid = false;
            }

            if (!$('#customer_phone').val().trim()) {
                errors.push('Vui lòng nhập số điện thoại');
                isValid = false;
            }

            var totalSeats = this.getTotalPassengers();
            if (this.selectedSeats.length > 0 && this.selectedSeats.length !== totalSeats) {
                errors.push('Vui lòng chọn đủ số ghế cho ' + totalSeats + ' khách');
                isValid = false;
            }

            if (errors.length > 0) {
                alert(errors.join('\n'));
            }

            return isValid;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        NTour.init();
    });

    // Expose to global scope
    window.NTour = NTour;

})(jQuery);
