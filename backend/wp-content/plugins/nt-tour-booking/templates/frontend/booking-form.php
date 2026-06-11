<?php
/**
 * Booking Form Template
 *
 * Frontend booking form.
 *
 * @since 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="nt-tour-booking-form" id="nt-tour-booking-form" data-departure-id="<?php echo esc_attr($departure_id); ?>">
    <form id="bookingForm">
        <input type="hidden" name="action" value="nt_create_booking">
        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('nt_tour_booking')); ?>">

        <!-- Tour Info Section -->
        <div class="nt-booking-section" id="tour-info-section">
            <h3>Thông tin tour</h3>
            <div id="tour-info-loading">Đang tải thông tin tour...</div>
            <div id="tour-info" style="display: none;">
                <div class="nt-booking-tour-card">
                    <h4 id="tour-title"></h4>
                    <p id="tour-date"></p>
                    <p id="tour-price"></p>
                </div>
            </div>
        </div>

        <!-- Passenger Count Section -->
        <div class="nt-booking-section" id="passenger-section">
            <h3>Số lượng khách</h3>
            <div class="nt-form-row">
                <div class="nt-form-group">
                    <label for="adult_count">Người lớn (12+)</label>
                    <input type="number" name="adult_count" id="adult_count" min="1" max="20" value="1" required>
                    <span class="nt-price-display" id="adult-price"></span>
                </div>
                <div class="nt-form-group">
                    <label for="child_count">Trẻ em (2-11)</label>
                    <input type="number" name="child_count" id="child_count" min="0" max="20" value="0">
                    <span class="nt-price-display" id="child-price"></span>
                </div>
                <div class="nt-form-group">
                    <label for="infant_count">Em bé (<2)</label>
                    <input type="number" name="infant_count" id="infant_count" min="0" max="20" value="0">
                    <span class="nt-price-display" id="infant-price"></span>
                </div>
            </div>
            <div class="nt-total-price">
                <strong>Tổng cộng: </strong>
                <span id="total-price">0</span> VNĐ
            </div>
        </div>

        <!-- Pickup Point Section -->
        <div class="nt-booking-section" id="pickup-section">
            <h3>Điểm đón</h3>
            <select name="pickup_point_id" id="pickup_point_id" required>
                <option value="">-- Chọn điểm đón --</option>
            </select>
        </div>

        <!-- Seat Selection Section -->
        <div class="nt-booking-section" id="seat-section" style="display: none;">
            <h3>Chọn ghế</h3>
            <p class="nt-seat-mode-info">Vui lòng chọn đủ số ghế cho số khách</p>
            <div id="seat-map-container">
                <div id="seat-map"></div>
            </div>
            <div class="nt-selected-seats">
                <p>Ghế đã chọn: <span id="selected-seats-display"></span></p>
            </div>
        </div>

        <!-- Customer Info Section -->
        <div class="nt-booking-section" id="customer-section">
            <h3>Thông tin người đặt</h3>
            <div class="nt-form-group">
                <label for="customer_name">Họ tên *</label>
                <input type="text" name="customer_name" id="customer_name" required>
            </div>
            <div class="nt-form-group">
                <label for="customer_phone">Số điện thoại *</label>
                <input type="tel" name="customer_phone" id="customer_phone" required>
            </div>
            <div class="nt-form-group">
                <label for="customer_email">Email</label>
                <input type="email" name="customer_email" id="customer_email">
            </div>
        </div>

        <!-- Submit Section -->
        <div class="nt-booking-section" id="submit-section">
            <button type="submit" id="booking-submit" class="nt-btn nt-btn-primary">
                Đặt tour
            </button>
        </div>
    </form>

    <!-- Loading Overlay -->
    <div id="nt-loading" style="display: none;">
        <div class="nt-loading-spinner"></div>
        <p>Đang xử lý...</p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var departureId = <?php echo json_encode($departure_id); ?>;
    var apiUrl = <?php echo json_encode(rest_url('nt-tour/v1')); ?>;
    var nonce = <?php echo json_encode(wp_create_nonce('wp_rest')); ?>;

    // Load departure info
    $.ajax({
        url: apiUrl + '/departures/' + departureId,
        method: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success: function(response) {
            if (response.success && response.data) {
                var departure = response.data;
                $('#tour-title').text('Tour ID: ' + departure.tour_id);
                $('#tour-date').text('Ngày: ' + departure.start_date_formatted);
                $('#adult-price').text(departure.adult_price_formatted);
                $('#child-price').text(departure.child_price_formatted);
                $('#infant-price').text(departure.infant_price_formatted);
                $('#tour-info').show();
                $('#tour-info-loading').hide();
            }
        }
    });

    // Load pickup points
    $.ajax({
        url: apiUrl + '/departures/' + departureId + '/pickup-points',
        method: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success: function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(point) {
                    $('#pickup_point_id').append(
                        $('<option></option>').val(point.id).text(point.name)
                    );
                });
            }
        }
    });

    // Calculate total price
    function calculateTotal() {
        var adult = parseInt($('#adult_count').val()) || 0;
        var child = parseInt($('#child_count').val()) || 0;
        var infant = parseInt($('#infant_count').val()) || 0;

        // Get prices from data attributes or AJAX
        var adultPrice = parseFloat($('#adult-price').data('price')) || 0;
        var childPrice = parseFloat($('#child-price').data('price')) || 0;

        var total = (adult * adultPrice) + (child * childPrice);
        $('#total-price').text(total.toLocaleString('vi-VN'));
    }

    $('#adult_count, #child_count, #infant_count').on('change', calculateTotal);

    // Form submission
    $('#bookingForm').on('submit', function(e) {
        e.preventDefault();

        $('#nt-loading').show();

        var formData = {
            tour_departure_id: departureId,
            customer_name: $('#customer_name').val(),
            customer_phone: $('#customer_phone').val(),
            customer_email: $('#customer_email').val(),
            adult_count: parseInt($('#adult_count').val()) || 1,
            child_count: parseInt($('#child_count').val()) || 0,
            infant_count: parseInt($('#infant_count').val()) || 0,
            pickup_point_id: $('#pickup_point_id').val(),
        };

        // Include selected seats if any
        var selectedSeats = [];
        $('#seat-map .nt-seat.selected').each(function() {
            selectedSeats.push($(this).data('seat'));
        });
        if (selectedSeats.length > 0) {
            formData.seat_codes = selectedSeats;
            formData.seat_selection_mode = 'customer_select';
        }

        $.ajax({
            url: apiUrl + '/bookings',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function(response) {
                $('#nt-loading').hide();

                if (response.success) {
                    alert('Đặt tour thành công! Mã booking: ' + response.data.booking.code);
                    // Redirect to success page or show confirmation
                } else {
                    alert('Có lỗi xảy ra: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#nt-loading').hide();
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    });
});
</script>
