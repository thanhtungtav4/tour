<?php
/**
 * Seat map frontend template.
 *
 * @var int $departure_id
 */
?>
<div class="nt-tour-seat-map" data-departure-id="<?php echo esc_attr((string) $departure_id); ?>">
    <h3><?php echo esc_html__('Sơ đồ ghế', 'nt-tour-booking'); ?></h3>
    <p><?php echo esc_html__('Sơ đồ ghế sẽ được tải qua API.', 'nt-tour-booking'); ?></p>
    <div class="nt-tour-seat-map__content" aria-live="polite"></div>
</div>
