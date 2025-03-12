<?php if (!defined('ABSPATH')) exit ?>
<!--ADD THEMES -->

<div id="<?php echo esc_html($prefix); ?>qr-container<?php echo esc_html($sufix); ?>" class="qr-container" data-public-donors="<?php echo esc_attr($public_donors); ?>">
    <div class="close-popup">×</div>
    <div id="<?php echo esc_html($prefix); ?>public-donor-popup<?php echo esc_html($sufix); ?>" class="public-donor-popup">
        <h3 style="margin-bottom: 24px; font-weight: bold">Donor Information</h3>
        <form class="public-donor-form">
            <label for="<?php echo esc_html($prefix); ?>first-name<?php echo esc_html($sufix); ?>">First Name</label>
            <input <?php echo $first_name ? 'required' : ''; ?> type="text" id="<?php echo esc_html($prefix); ?>first-name<?php echo esc_html($sufix); ?>" placeholder="<?php echo $first_name ? 'Required' : 'Optional'; ?>">

            <label for="<?php echo esc_html($prefix); ?>last-name<?php echo esc_html($sufix); ?>">Last Name</label>
            <input <?php echo $last_name ? 'required' : ''; ?> type="text" id="<?php echo esc_html($prefix); ?>last-name<?php echo esc_html($sufix); ?>" placeholder="<?php echo $last_name ? 'Required' : 'Optional'; ?>">

            <label for="<?php echo esc_html($prefix); ?>donor-email<?php echo esc_html($sufix); ?>">Email</label>
            <input <?php echo $email ? 'required' : ''; ?> type="text" id="<?php echo esc_html($prefix); ?>donor-email<?php echo esc_html($sufix); ?>" placeholder="<?php echo $email ? 'Required' : 'Optional'; ?>">

            <label for="<?php echo esc_html($prefix); ?>address<?php echo esc_html($sufix); ?>">Address</label>
            <input <?php echo $address ? 'required' : ''; ?> type="text" id="<?php echo esc_html($prefix); ?>address<?php echo esc_html($sufix); ?>" placeholder="<?php echo $address ? 'Required' : 'Optional'; ?>">

            <div class="opt-out-wrapper">
                <label for="<?php echo esc_html($prefix); ?>opt-out<?php echo esc_html($sufix); ?>">Don't display my information</label>
                <input type="checkbox" id="<?php echo esc_html($prefix); ?>opt-out<?php echo esc_html($sufix); ?>">
            </div>


            <button type="submit" id="<?php echo esc_html($prefix); ?>public-donors-pay<?php echo esc_html($sufix); ?>">Pay</button>
        </form>

    </div>
    <div id="<?php echo esc_html($prefix); ?>payment-loading<?php echo esc_html($sufix); ?>" class="payment-loading">
        <div id="<?php echo esc_html($prefix); ?>qr-spinner<?php echo esc_html($sufix); ?>" class="loader qr-spinner"></div>
    </div>
    <div id="<?php echo esc_html($prefix); ?>payment-popup<?php echo esc_html($sufix); ?>" class="payment-popup">
        <h4 class="qr-amount" id="<?php echo esc_html($prefix); ?>qr-amount<?php echo esc_html($sufix); ?>"></h4>
        <p class="qr-fiat" id="<?php echo esc_html($prefix); ?>qr-fiat<?php echo esc_html($sufix); ?>"></p>
        <div style="position: relative;">
            <img class="qr-code" style="display: none;" id="<?php echo esc_html($prefix); ?>qrCode<?php echo esc_html($sufix); ?>" alt="QR Code">
            <img class="qr-code-btc" id="<?php echo esc_html($prefix); ?>qrCodeBtc<?php echo esc_html($sufix); ?>" alt="QR Code Btc" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/bitcoinqr.svg'; ?>">
        </div>
        <details open class="qr-details">
            <summary id="<?php echo esc_html($prefix); ?>qr-summary<?php echo esc_html($sufix); ?>" class="qr-summary">Details <span class="qr-dropdown">&#9660;</span></summary>
            <div class="qr-address-wrapper" id="<?php echo esc_html($prefix); ?>lightning-wrapper<?php echo esc_html($sufix); ?>" style="display: none; margin-top:8px">
                <div class="qr-address-title">
                    Lightning:
                </div>
                <div id="<?php echo esc_html($prefix); ?>qr-lightning-container<?php echo esc_html($sufix); ?>" style="display: none;" class="qr-lightning-container">
                    <div class="qr-lightning" id="<?php echo esc_html($prefix); ?>qr-lightning<?php echo esc_html($sufix); ?>"></div>
                    <svg class="qr-copy-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8f979e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </div>
            </div>
            <div id="<?php echo esc_html($prefix); ?>btc-wrapper<?php echo esc_html($sufix); ?>" style="display: none; margin-top:12px" class="qr-address-wrapper">
                <div class="qr-address-title">
                    Address:
                </div>
                <div id="<?php echo esc_html($prefix); ?>qr-btc-container<?php echo esc_html($sufix); ?>" style="display: none;" class="qr-lightning-container">
                    <div class="qr-lightning" id="<?php echo esc_html($prefix); ?>qr-btc<?php echo esc_html($sufix); ?>"></div>
                    <svg class="qr-copy-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8f979e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </div>
            </div>
        </details>
        <button id="<?php echo esc_html($prefix); ?>pay-in-wallet<?php echo esc_html($sufix); ?>" class="qr-pay-in-wallet">Pay in wallet</button>
    </div>
    <div id="<?php echo esc_html($prefix); ?>thank-you-popup<?php echo esc_html($sufix); ?>" class="thank-you-popup">
        <img class="checkmark-img" id="checkmark" alt="Checkmark" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/checkmark.svg'; ?>">
        <h3 style="margin: 10px 0 0 0;">Your payment was successful.</h3>
    </div>
</div>