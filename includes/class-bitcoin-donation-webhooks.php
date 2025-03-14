<?php
class Bitcoin_Donation_Webhooks
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_webhook_endpoint']);
        add_action('rest_api_init', [$this, 'register_poll_check_endpoint']);
        add_action('rest_api_init', [$this, 'register_poll_results_endpoint']);
    }

    public function register_poll_results_endpoint()
    {
        register_rest_route('my-plugin/v1', '/voting_results/(?P<poll_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_results'],
            'permission_callback' => '__return_true', // TODO: Add proper permissions later
            'args' => [
                'poll_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ]
        ]);
    }

    public function register_poll_check_endpoint()
    {
        register_rest_route('my-plugin/v1', '/payment-status-long-poll/(?P<payment_id>[a-zA-Z0-9]+)/(?P<poll_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_payment_status_long_poll'],
            'permission_callback' => '__return_true', // TODO: Add proper permissions later
            'args' => [
                'payment_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return !empty($param);
                    }
                ],
                'poll_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ]
        ]);
    }

    function get_results($request)
    {
        $poll_id = $request['poll_id'];

        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}voting_payments WHERE status = 'completed' AND poll_id = %d",
            $poll_id
        );
        $results = $wpdb->get_results($query);

        return ['results' => $results];
    }

    function get_payment_status_long_poll($request)
    {
        $payment_id = $request['payment_id'];
        $poll_id = $request['poll_id'];
        $start_time = time();
        $timeout = 5;

        while (time() - $start_time < $timeout) {
            global $wpdb;
            $status = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM {$wpdb->prefix}voting_payments WHERE payment_id = %s",
                $payment_id
            ));
            if ($status === 'completed') {
                $query = $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}voting_payments WHERE status = 'completed' AND poll_id = %d",
                    $poll_id
                );
                $results = $wpdb->get_results($query);

                return ['status' => 'completed', 'results' => $results];
            }
            sleep(1);
        }
        // Timeout
        return ['status' => 'pending'];
    }

    private function get_webhook_secret()
    {
        $option_name = 'coinsnap_webhook_secret';
        $secret = get_option($option_name);

        if (!$secret) {
            $secret = bin2hex(random_bytes(16));
            add_option($option_name, $secret, '', false);
        }

        return $secret;
    }

    public function register_webhook_endpoint()
    {
        register_rest_route('bitcoin-donation/v1', 'webhook', [
            'methods'  => ['POST'],
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => [$this, 'verify_webhook_request']
        ]);
    }

    function verify_webhook_request($request)
    {
        $secret = $this->get_webhook_secret();

        $coinsnap_sig = $request->get_header('X-Coinsnap-Sig');
        $btcpay_sig = $request->get_header('btcpay_sig');
        $signature_header = !empty($coinsnap_sig) ? $coinsnap_sig : $btcpay_sig;
        if (empty($signature_header)) {
            return false;
        }

        $payload = $request->get_body();

        $computed_signature = hash_hmac('sha256', $payload, $secret);
        $computed_signature = 'sha256=' . $computed_signature; // Prefix the computed_signature with 'sha256='
        if (!hash_equals($computed_signature, $signature_header)) {
            return false;
        }
        return true;
    }

    public function handle_webhook(WP_REST_Request $request)
    {
        $payload_data = $request->get_json_params();

        if (isset($payload_data['type']) && ($payload_data['type'] === 'Settled' || $payload_data['type'] === 'InvoiceSettled')) {
            // Get the invoiceId from the payload
            $invoiceId = $payload_data['invoiceId'];
            $args = array(
                'post_type'      => 'bitcoin-shoutouts',
                'post_status'    => 'pending',
                'meta_query'     => array(
                    array(
                        'key'   => '_bitcoin_donation_shoutouts_invoice_id',
                        'value' => $invoiceId,
                    ),
                ),
                'posts_per_page' => 1,
            );
            
            if (isset($payload_data['metadata']['type']) && $payload_data['metadata']['type'] == "Bitcoin Voting") {
                global $wpdb;
                $invoiceId = $payload_data['invoiceId'];
                $optionId = $payload_data['metadata']['optionId'];
                $optionTitle = $payload_data['metadata']['option'];
                $pollId = $payload_data['metadata']['pollId'];

                $wpdb->insert(
                    "{$wpdb->prefix}voting_payments",
                    [
                        'payment_id' => $invoiceId,
                        'option_id' => $optionId,
                        'option_title' => $optionTitle,
                        'poll_id' => $pollId,
                        'status'     => 'completed'
                    ],
                    [
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%s'
                    ]
                );
            } else {

                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_id = get_the_ID();

                        // Update the post status to 'publish'
                        $updated_post = array(
                            'ID'          => $post_id,
                            'post_status' => 'publish'
                        );

                        $result = wp_update_post($updated_post, true);

                        if (is_wp_error($result)) {
                            return new WP_REST_Response('Error updating post.', 500);
                        }
                    }
                    wp_reset_postdata();

                    return new WP_REST_Response('Post updated successfully.', 200);
                } else {
                    return new WP_REST_Response('No matching post found.', 404);
                }
            }
        }

        return new WP_REST_Response('Webhook type not handled.', 200);
    }
}
new Bitcoin_Donation_Webhooks();
