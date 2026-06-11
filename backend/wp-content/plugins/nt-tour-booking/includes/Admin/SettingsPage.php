<?php
/**
 * Settings Page
 *
 * Plugin settings page with all configuration options.
 *
 * @since 0.1.0
 */

namespace TourBooking\Admin;

class SettingsPage
{
    /**
     * Options group name
     */
    const OPTIONS_GROUP = 'nt_tour_settings';

    /**
     * Options page slug
     */
    const OPTIONS_PAGE = 'nt-tour-settings';

    /**
     * Initialize settings
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('admin_init', [self::class, 'register_settings']);
    }

    /**
     * Register all settings
     *
     * @return void
     */
    public static function register_settings(): void
    {
        // Company Information
        register_setting(self::OPTIONS_GROUP, 'nt_tour_company_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_hotline', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_support_email', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ]);

        // Bank Information
        register_setting(self::OPTIONS_GROUP, 'nt_tour_bank_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_bank_account_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_bank_account_number', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_bank_branch', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_transfer_content_template', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'NT{booking_code}',
        ]);

        // Booking Settings
        register_setting(self::OPTIONS_GROUP, 'nt_tour_seat_hold_minutes', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 120,
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_allow_customer_seat_selection', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_allow_admin_assign_seat', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1',
        ]);

        // Check-in Settings
        register_setting(self::OPTIONS_GROUP, 'nt_tour_checkin_allow_incomplete_profile', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1',
        ]);

        // Webhook Settings
        register_setting(self::OPTIONS_GROUP, 'nt_tour_webhook_secret', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        // Email Settings
        register_setting(self::OPTIONS_GROUP, 'nt_tour_email_from_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting(self::OPTIONS_GROUP, 'nt_tour_email_from_address', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ]);

        // Add settings sections
        add_settings_section('nt_tour_company_section', __('Company Information', 'nt-tour-booking'), [self::class, 'render_company_section'], self::OPTIONS_PAGE);
        add_settings_section('nt_tour_bank_section', __('Bank Information', 'nt-tour-booking'), [self::class, 'render_bank_section'], self::OPTIONS_PAGE);
        add_settings_section('nt_tour_booking_section', __('Booking Settings', 'nt-tour-booking'), [self::class, 'render_booking_section'], self::OPTIONS_PAGE);
        add_settings_section('nt_tour_checkin_section', __('Check-in Settings', 'nt-tour-booking'), [self::class, 'render_checkin_section'], self::OPTIONS_PAGE);
        add_settings_section('nt_tour_webhook_section', __('Webhook Settings', 'nt-tour-booking'), [self::class, 'render_webhook_section'], self::OPTIONS_PAGE);
        add_settings_section('nt_tour_email_section', __('Email Settings', 'nt-tour-booking'), [self::class, 'render_email_section'], self::OPTIONS_PAGE);

        // Add settings fields
        self::add_settings_fields();
    }

    /**
     * Add all settings fields
     *
     * @return void
     */
    private static function add_settings_fields(): void
    {
        // Company fields
        add_settings_field('nt_tour_company_name', __('Company Name', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_company_section', ['name' => 'nt_tour_company_name']);
        add_settings_field('nt_tour_hotline', __('Hotline', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_company_section', ['name' => 'nt_tour_hotline']);
        add_settings_field('nt_tour_support_email', __('Support Email', 'nt-tour-booking'), [self::class, 'render_email_field'], self::OPTIONS_PAGE, 'nt_tour_company_section', ['name' => 'nt_tour_support_email']);

        // Bank fields
        add_settings_field('nt_tour_bank_name', __('Bank Name', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_bank_section', ['name' => 'nt_tour_bank_name']);
        add_settings_field('nt_tour_bank_account_name', __('Account Name', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_bank_section', ['name' => 'nt_tour_bank_account_name']);
        add_settings_field('nt_tour_bank_account_number', __('Account Number', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_bank_section', ['name' => 'nt_tour_bank_account_number']);
        add_settings_field('nt_tour_bank_branch', __('Branch', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_bank_section', ['name' => 'nt_tour_bank_branch']);
        add_settings_field('nt_tour_transfer_content_template', __('Transfer Content Template', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_bank_section', ['name' => 'nt_tour_transfer_content_template', 'description' => 'Use {booking_code} as placeholder. Example: NT{booking_code}']);

        // Booking fields
        add_settings_field('nt_tour_seat_hold_minutes', __('Seat Hold Duration (minutes)', 'nt-tour-booking'), [self::class, 'render_number_field'], self::OPTIONS_PAGE, 'nt_tour_booking_section', ['name' => 'nt_tour_seat_hold_minutes', 'description' => 'Default: 120 minutes (2 hours)']);
        add_settings_field('nt_tour_allow_customer_seat_selection', __('Allow Customer Seat Selection', 'nt-tour-booking'), [self::class, 'render_checkbox_field'], self::OPTIONS_PAGE, 'nt_tour_booking_section', ['name' => 'nt_tour_allow_customer_seat_selection', 'description' => 'Allow customers to select their own seats']);
        add_settings_field('nt_tour_allow_admin_assign_seat', __('Allow Admin Seat Assignment', 'nt-tour-booking'), [self::class, 'render_checkbox_field'], self::OPTIONS_PAGE, 'nt_tour_booking_section', ['name' => 'nt_tour_allow_admin_assign_seat', 'description' => 'Allow admin to assign seats on behalf of customers']);

        // Check-in fields
        add_settings_field('nt_tour_checkin_allow_incomplete_profile', __('Allow Check-in with Incomplete Profile', 'nt-tour-booking'), [self::class, 'render_checkbox_field'], self::OPTIONS_PAGE, 'nt_tour_checkin_section', ['name' => 'nt_tour_checkin_allow_incomplete_profile', 'description' => 'Allow passengers to check-in even if profile information is incomplete']);

        // Webhook fields
        add_settings_field('nt_tour_webhook_secret', __('Webhook Secret', 'nt-tour-booking'), [self::class, 'render_password_field'], self::OPTIONS_PAGE, 'nt_tour_webhook_section', ['name' => 'nt_tour_webhook_secret', 'description' => 'Secret key for webhook signature verification']);

        // Email fields
        add_settings_field('nt_tour_email_from_name', __('Email From Name', 'nt-tour-booking'), [self::class, 'render_text_field'], self::OPTIONS_PAGE, 'nt_tour_email_section', ['name' => 'nt_tour_email_from_name']);
        add_settings_field('nt_tour_email_from_address', __('Email From Address', 'nt-tour-booking'), [self::class, 'render_email_field'], self::OPTIONS_PAGE, 'nt_tour_email_section', ['name' => 'nt_tour_email_from_address']);
    }

    /**
     * Render section descriptions
     */
    public static function render_company_section(): void
    {
        echo '<p>' . esc_html__('Enter your company information for booking confirmations and emails.', 'nt-tour-booking') . '</p>';
    }

    public static function render_bank_section(): void
    {
        echo '<p>' . esc_html__('Enter your bank account details for payment instructions.', 'nt-tour-booking') . '</p>';
    }

    public static function render_booking_section(): void
    {
        echo '<p>' . esc_html__('Configure booking behavior and seat selection options.', 'nt-tour-booking') . '</p>';
    }

    public static function render_checkin_section(): void
    {
        echo '<p>' . esc_html__('Configure check-in behavior.', 'nt-tour-booking') . '</p>';
    }

    public static function render_webhook_section(): void
    {
        echo '<p>' . esc_html__('Configure webhook settings for automatic payment verification.', 'nt-tour-booking') . '</p>';
    }

    public static function render_email_section(): void
    {
        echo '<p>' . esc_html__('Configure email sender settings.', 'nt-tour-booking') . '</p>';
    }

    /**
     * Render input fields
     */
    public static function render_text_field(array $args): void
    {
        $name = $args['name'];
        $value = get_option($name, '');
        $description = $args['description'] ?? '';
        ?>
        <input type="text" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <?php if ($description): ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif;
    }

    public static function render_email_field(array $args): void
    {
        $name = $args['name'];
        $value = get_option($name, '');
        ?>
        <input type="email" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <?php
    }

    public static function render_number_field(array $args): void
    {
        $name = $args['name'];
        $value = get_option($name, 0);
        $description = $args['description'] ?? '';
        ?>
        <input type="number" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="small-text">
        <?php if ($description): ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif;
    }

    public static function render_password_field(array $args): void
    {
        $name = $args['name'];
        $value = get_option($name, '');
        $description = $args['description'] ?? '';
        ?>
        <input type="password" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" autocomplete="new-password">
        <?php if ($description): ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif;
    }

    public static function render_checkbox_field(array $args): void
    {
        $name = $args['name'];
        $value = get_option($name, '1');
        $description = $args['description'] ?? '';
        ?>
        <label for="<?php echo esc_attr($name); ?>">
            <input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="1" <?php checked($value, '1'); ?>>
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }

    /**
     * Get setting value
     *
     * @param string $name Setting name
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return get_option($name, $default);
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public static function get_all(): array
    {
        return [
            'company_name' => get_option('nt_tour_company_name', ''),
            'hotline' => get_option('nt_tour_hotline', ''),
            'support_email' => get_option('nt_tour_support_email', ''),
            'bank_name' => get_option('nt_tour_bank_name', ''),
            'bank_account_name' => get_option('nt_tour_bank_account_name', ''),
            'bank_account_number' => get_option('nt_tour_bank_account_number', ''),
            'bank_branch' => get_option('nt_tour_bank_branch', ''),
            'transfer_content_template' => get_option('nt_tour_transfer_content_template', 'NT{booking_code}'),
            'seat_hold_minutes' => (int) get_option('nt_tour_seat_hold_minutes', 120),
            'allow_customer_seat_selection' => get_option('nt_tour_allow_customer_seat_selection', '1') === '1',
            'allow_admin_assign_seat' => get_option('nt_tour_allow_admin_assign_seat', '1') === '1',
            'checkin_allow_incomplete_profile' => get_option('nt_tour_checkin_allow_incomplete_profile', '1') === '1',
            'webhook_secret' => get_option('nt_tour_webhook_secret', ''),
            'email_from_name' => get_option('nt_tour_email_from_name', ''),
            'email_from_address' => get_option('nt_tour_email_from_address', ''),
        ];
    }
}
