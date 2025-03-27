<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Gemini_API
 * Handles communication with the Google Gemini API.
 */
class ESC_Gemini_API {

    // Base API endpoint for Gemini models
    const API_BASE_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /**
     * Get the full API endpoint with the selected model.
     *
     * @return string The complete API endpoint with the selected model.
     */
    public static function get_api_endpoint() {
        $model = get_option(ESC_GEMINI_MODEL_OPTION, 'gemini-2.0-flash-lite');
        return self::API_BASE_ENDPOINT . $model . ':generateContent';
    }

    /**
     * Get available Gemini models.
     *
     * @return array Array of available models with model ID as key and model data as value.
     */
    public static function get_available_models() {
        // Define default models with their properties
        $default_models = [
            'gemini-2.0-flash-lite' => [
                'name' => __('Gemini 2.0 Flash Lite (Fastest)', 'erins-seed-catalog'),
                'type' => 'free',
                'description' => __('Fastest response, most cost-effective', 'erins-seed-catalog'),
                'recommended' => true
            ],
            'gemini-2.0-flash' => [
                'name' => __('Gemini 2.0 Flash (Fast)', 'erins-seed-catalog'),
                'type' => 'free',
                'description' => __('Fast response, good balance of speed and quality', 'erins-seed-catalog'),
                'recommended' => false
            ],
            'gemini-2.0-pro' => [
                'name' => __('Gemini 2.0 Pro (Most Accurate)', 'erins-seed-catalog'),
                'type' => 'free',
                'description' => __('Most detailed responses, slower but more accurate', 'erins-seed-catalog'),
                'recommended' => false
            ],
            'gemini-1.5-flash-latest' => [
                'name' => __('Gemini 1.5 Flash (Legacy)', 'erins-seed-catalog'),
                'type' => 'legacy',
                'description' => __('Legacy model, may be deprecated in the future', 'erins-seed-catalog'),
                'recommended' => false
            ],
            'gemini-1.5-pro-latest' => [
                'name' => __('Gemini 1.5 Pro (Legacy)', 'erins-seed-catalog'),
                'type' => 'legacy',
                'description' => __('Legacy model, may be deprecated in the future', 'erins-seed-catalog'),
                'recommended' => false
            ],
            // Experimental models can be added here
            'gemini-ultra-latest' => [
                'name' => __('Gemini Ultra (Experimental)', 'erins-seed-catalog'),
                'type' => 'experimental',
                'description' => __('Most powerful model, may require special access', 'erins-seed-catalog'),
                'recommended' => false
            ],
        ];

        // Allow other plugins or themes to modify the list of available models
        // This makes it easy to add new models or remove deprecated ones
        return apply_filters('esc_gemini_available_models', $default_models);
    }

    /**
     * Get a simplified list of available models for the settings dropdown.
     *
     * @return array Array of available models with model ID as key and display name as value.
     */
    public static function get_models_for_dropdown() {
        $models = self::get_available_models();
        $dropdown_options = [];

        // Group models by type
        $grouped_models = [
            'free' => [],
            'experimental' => [],
            'legacy' => []
        ];

        foreach ($models as $model_id => $model_data) {
            // Skip metadata entries (those starting with _)
            if (strpos($model_id, '_') === 0) {
                continue;
            }

            $type = isset($model_data['type']) ? $model_data['type'] : 'free';
            if (!isset($grouped_models[$type])) {
                $grouped_models[$type] = [];
            }
            $grouped_models[$type][$model_id] = $model_data;
        }

        // Add free models first
        if (!empty($grouped_models['free'])) {
            $dropdown_options['free_header'] = __('--- Free Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['free'] as $model_id => $model_data) {
                $name = isset($model_data['name']) ? $model_data['name'] : $model_id;

                // Check if the model is available with the current API key
                if (isset($model_data['available']) && $model_data['available'] === false) {
                    $name .= ' ' . __('(Not available with your API key)', 'erins-seed-catalog');
                }

                // Add a recommended indicator
                if (isset($model_data['recommended']) && $model_data['recommended']) {
                    $name .= ' ' . __('(Recommended)', 'erins-seed-catalog');
                }

                $dropdown_options[$model_id] = $name;
            }
        }

        // Add experimental models
        if (!empty($grouped_models['experimental'])) {
            $dropdown_options['experimental_header'] = __('--- Experimental Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['experimental'] as $model_id => $model_data) {
                $name = isset($model_data['name']) ? $model_data['name'] : $model_id;

                // Check if the model is available with the current API key
                if (isset($model_data['available']) && $model_data['available'] === false) {
                    $name .= ' ' . __('(Not available with your API key)', 'erins-seed-catalog');
                }

                $dropdown_options[$model_id] = $name;
            }
        }

        // Add legacy models
        if (!empty($grouped_models['legacy'])) {
            $dropdown_options['legacy_header'] = __('--- Legacy Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['legacy'] as $model_id => $model_data) {
                $name = isset($model_data['name']) ? $model_data['name'] : $model_id;

                // Check if the model is available with the current API key
                if (isset($model_data['available']) && $model_data['available'] === false) {
                    $name .= ' ' . __('(Not available with your API key)', 'erins-seed-catalog');
                }

                $dropdown_options[$model_id] = $name;
            }
        }

        return $dropdown_options;
    }


    /**
     * Fetch seed information from the Gemini API.
     *
     * @param string $seed_name The name of the seed.
     * @param string $variety Optional variety name.
     * @param string $brand Optional brand name.
     * @param string $sku_upc Optional SKU or UPC.
     * @return array|WP_Error Associative array of extracted data or WP_Error on failure.
     */
    public static function fetch_seed_info( string $seed_name, ?string $variety = null, ?string $brand = null, ?string $sku_upc = null ) {
        $api_key = get_option( ESC_API_KEY_OPTION );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'api_key_missing', __( 'Gemini API Key is missing. Please configure it in the plugin settings.', 'erins-seed-catalog' ) );
        }

        // --- Crucial: Prompt Engineering ---
        // Construct a detailed prompt asking Gemini to return JSON.
        $prompt = self::build_api_prompt( $seed_name, $variety, $brand, $sku_upc );

        $request_body = [
            'contents' => [
                [
                    'parts' => [
                        [ 'text' => $prompt ]
                    ]
                ]
            ],
             // Add safety settings and generation config if needed
             /*
            'safetySettings' => [
                [ 'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE' ],
                // Add other categories as needed
            ],
            'generationConfig' => [
                'temperature' => 0.7, // Adjust creativity vs. factuality
                'maxOutputTokens' => 8192, // Increase if needed, check model limits
                'responseMimeType' => 'application/json', // Explicitly ask for JSON output
            ]
            */
            // Ensure the model you use supports JSON output mode if using responseMimeType
             'generationConfig' => [
                 'response_mime_type' => 'application/json',
             ]
        ];


        $args = [
            'method'      => 'POST',
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'body'        => wp_json_encode( $request_body ),
            'timeout'     => 60, // Increase timeout for potentially long API calls
        ];

        // Append API key to the dynamic endpoint URL
        $api_url = add_query_arg( 'key', $api_key, self::get_api_endpoint() );

        $response = wp_remote_post( $api_url, $args );

        // Log raw response for debugging
        error_log('Gemini API Raw Response: ' . print_r($response, true));

        if ( is_wp_error( $response ) ) {
            // WordPress HTTP API error
            return new WP_Error( 'http_error', __( 'Error communicating with the Gemini API.', 'erins-seed-catalog' ), $response->get_error_message() );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Log response body before JSON decode
        error_log('Gemini API Response Body: ' . $response_body);

        $result_data = json_decode( $response_body, true );

        // Log parsed data
        error_log('Gemini API Parsed Data: ' . print_r($result_data, true));

        if ( $response_code !== 200 ) {
            // Gemini API returned an error
            $error_message = isset( $result_data['error']['message'] ) ? $result_data['error']['message'] : $response_body;
             // Check for specific common errors
            if (strpos($error_message, 'API key not valid') !== false) {
                 return new WP_Error('api_error_invalid_key', __('Invalid Gemini API Key.', 'erins-seed-catalog'), $error_message);
            }
            if (isset($result_data['promptFeedback']['blockReason'])) {
                 $error_message .= ' Reason: ' . $result_data['promptFeedback']['blockReason'];
                 return new WP_Error('api_error_blocked', __('Gemini API request blocked due to safety settings.', 'erins-seed-catalog'), $error_message);
            }

            return new WP_Error( 'api_error', sprintf( __( 'Gemini API Error (Code: %d):', 'erins-seed-catalog' ), $response_code ), $error_message );
        }


        // --- Extract Content ---
        // The exact structure depends on the Gemini API version and response format.
        // Adjust this based on actual API responses. We expect JSON directly in the 'text' part if responseMimeType worked.
        $generated_text = $result_data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        error_log('Gemini API Raw Response: ' . print_r($result_data, true));
        error_log('Gemini API Generated Text: ' . $generated_text);

        if ( empty( $generated_text ) ) {
            // Sometimes the content might be empty or in a finishReason state
             $finish_reason = $result_data['candidates'][0]['finishReason'] ?? 'UNKNOWN';
             if ($finish_reason !== 'STOP') {
                 return new WP_Error('api_no_content', __('Gemini API returned an empty or incomplete response.', 'erins-seed-catalog'), 'Finish Reason: ' . $finish_reason);
             }
            // It might just genuinely have no info
            return new WP_Error( 'api_no_content', __( 'Gemini API did not return usable content for this seed.', 'erins-seed-catalog' ), $response_body );
        }

        // Attempt to decode the JSON string returned by the API
        $extracted_data = json_decode( $generated_text, true );
        error_log('Gemini API Parsed Data: ' . print_r($extracted_data, true));

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // If Gemini didn't return valid JSON despite the prompt/config
            error_log("Gemini API Response - Not Valid JSON: " . $generated_text); // Log the raw response
            return new WP_Error( 'api_json_error', __( 'Could not parse the JSON data from the Gemini API response.', 'erins-seed-catalog' ), json_last_error_msg() . ' | Raw text: ' . substr($generated_text, 0, 200) . '...' );
        }

        // Process the extracted data to handle string "null" values
        $processed_data = self::process_api_data($extracted_data);
        error_log('Gemini API Processed Data: ' . print_r($processed_data, true));

        // Return processed data, sanitization happens during DB insert/update
        return $processed_data;

    }

    /**
     * Build the prompt for the Gemini API.
     * This is critical for getting structured, relevant data.
     *
     * @param string $seed_name
     * @param string|null $variety
     * @param string|null $brand
     * @param string|null $sku_upc
     * @return string The prompt text.
     */
    private static function build_api_prompt( string $seed_name, ?string $variety = null, ?string $brand = null, ?string $sku_upc = null ) : string {

        $prompt = "You are a helpful gardening assistant specialized in seed cataloging. ";
        $prompt .= "Analyze the following seed information:\n";
        $prompt .= "- Seed Name: \"{$seed_name}\"\n";
        if ( ! empty( $variety ) ) {
            $prompt .= "- Variety: \"{$variety}\"\n";
        }
        if ( ! empty( $brand ) ) {
            $prompt .= "- Brand: \"{$brand}\"\n";
        }
        if ( ! empty( $sku_upc ) ) {
            $prompt .= "- Item/SKU/UPC: \"{$sku_upc}\"\n";
        }
        $prompt .= "\n";

        $prompt .= "Prioritize finding information for the specific Brand and Item/SKU/UPC if provided. ";
        $prompt .= "If only Seed Name and Variety are given, find general, accurate information for that specific variety. ";
        $prompt .= "If only Seed Name is given, find general information for that type of plant.\n\n";

        $prompt .= "Extract and return the following information ONLY in JSON format. Use snake_case for keys matching the database fields. ";
        $prompt .= "If a specific piece of information cannot be found or is not applicable, return `null` for that field's value (do not omit the key).\n\n";

        $prompt .= "{\n";
        $prompt .= "  \"variety_name\": \"string|null - The accurate variety name. If not specified or found, use the input variety or derive from name.\",\n";
        $prompt .= "  \"image_url\": \"string|null - Suggest one high-quality image URL (HTTPS preferred) of the mature plant, fruit/flower, or seeds, suitable for display. Provide only the direct image URL string.\",\n";
        $prompt .= "  \"description\": \"string|null - A detailed description including plant type (e.g., determinate tomato, climbing bean, annual flower), growth habit (e.g., bush, vining, upright), typical plant size (height and spread), fruit/flower details (size, shape, color), flavor profile (for edibles), scent (for flowers/herbs), bloom time (for flowers), and special characteristics (e.g., disease resistance, heat tolerance).\",\n";
        $prompt .= "  \"plant_type\": \"string|null - e.g., Determinate Tomato, Pole Bean, Annual Flower, Perennial Herb.\",\n";
        $prompt .= "  \"growth_habit\": \"string|null - e.g., Bush, Vining, Upright, Spreading.\",\n";
        $prompt .= "  \"plant_size\": \"string|null - e.g., Height: 4-6 ft, Spread: 2-3 ft.\",\n";
        $prompt .= "  \"fruit_info\": \"string|null - e.g., 6-8 oz red globe tomato; 3-inch purple carrot; Yellow bell flower.\",\n";
        $prompt .= "  \"flavor_profile\": \"string|null - e.g., Sweet and tangy; Mildly spicy; Earthy.\",\n";
        $prompt .= "  \"scent\": \"string|null - e.g., Strong lavender scent; Fragrant citrus notes.\",\n";
        $prompt .= "  \"bloom_time\": \"string|null - e.g., Early Summer to Fall; Mid-Spring.\",\n";
        $prompt .= "  \"days_to_maturity\": \"string|null - e.g., 65-75 days from transplant; 90 days.\",\n";
        $prompt .= "  \"special_characteristics\": \"string|null - e.g., Good disease resistance (VFN); Heat tolerant; Attracts butterflies; Heirloom variety.\",\n";
        $prompt .= "  \"sowing_depth\": \"string|null - e.g., 1/4 inch; 1 inch.\",\n";
        $prompt .= "  \"sowing_spacing\": \"string|null - e.g., Thin to 6 inches apart; Space plants 18-24 inches apart.\",\n";
        $prompt .= "  \"germination_temp\": \"string|null - Optimal soil temperature range, e.g., 70-85°F (21-29°C).\",\n";
        $prompt .= "  \"sunlight\": \"string|null - e.g., Full Sun (6+ hours); Partial Shade (4-6 hours).\",\n";
        $prompt .= "  \"watering\": \"string|null - e.g., Keep consistently moist; Water deeply 1-2 times per week.\",\n";
        $prompt .= "  \"fertilizer\": \"string|null - e.g., Balanced fertilizer at planting; Side-dress with compost mid-season.\",\n";
        $prompt .= "  \"pest_disease_info\": \"string|null - Common pests/diseases and management tips, e.g., Susceptible to aphids, monitor regularly; Resistant to powdery mildew.\",\n";
        $prompt .= "  \"harvesting_tips\": \"string|null - When and how to harvest, e.g., Harvest tomatoes when fully colored; Pick beans when pods are tender.\",\n";
        $prompt .= "  \"sowing_method\": \"string|null - Indicate if best 'Direct Sow', 'Start Indoors', or 'Both'.\",\n";
        $prompt .= "  \"seed_quantity\": \"string|null - Seeds per packet or weight, if available from brand/sku info. e.g., Approx 25 seeds; 500mg.\",\n";
        $prompt .= "  \"seed_treatment\": \"string|null - Comma-separated list if applicable: treated (e.g., fungicide), organic, heirloom, hybrid, open-pollinated, pelleted.\",\n";
        $prompt .= "  \"usda_zones\": \"string|null - Recommended USDA Hardiness Zones, especially for perennials. e.g., Zones 3-9.\",\n";
        $prompt .= "  \"pollinator_info\": \"string|null - Is it beneficial for pollinators? e.g., Attracts bees and butterflies.\",\n";
        $prompt .= "  \"container_suitability\": \"boolean|null - True if suitable for container growing, false or null otherwise.\",\n";
        $prompt .= "  \"cut_flower_potential\": \"boolean|null - True if suitable as a cut flower, false or null otherwise.\",\n";
        $prompt .= "  \"storage_recommendations\": \"string|null - How to store harvested produce, e.g., Store carrots in cool, damp conditions.\",\n";
        $prompt .= "  \"edible_parts\": \"string|null - Which parts of the plant are edible, e.g., Leaves and stems; Fruit; Root.\",\n";
        $prompt .= "  \"price\": \"string|null - Price if available from brand/sku info. Note this might be outdated.\",\n";
        $prompt .= "  \"availability\": \"string|null - Availability status if available from brand/sku info. Note this might be outdated.\",\n";
        $prompt .= "  \"company_info\": \"string|null - Contact info (website URL primarily) if available from brand/sku info.\",\n";
        $prompt .= "  \"historical_background\": \"string|null - Brief origin or history of the variety.\",\n";
        $prompt .= "  \"recipes\": \"string|null - Suggestion for a simple recipe or use (avoid complex instructions).\",\n";
        $prompt .= "  \"companion_plants\": \"string|null - List a few suggested companion plants.\",\n";
        $prompt .= "  \"customer_reviews\": \"string|null - Summarize common review points or provide a URL to reviews if easily found for the specific brand/sku.\",\n";
        $prompt .= "  \"regional_tips\": \"string|null - Any specific growing tips for certain regions if known.\",\n";
        $prompt .= "  \"producer_info\": \"string|null - Information about who grows the seed, if available (e.g., grown on company farm).\",\n";
        $prompt .= "  \"seed_saving_info\": \"string|null - If open-pollinated or heirloom, provide brief seed saving guidance (isolation distance, how to collect/dry).\",\n";
        $prompt .= "  \"germination_rate\": \"string|null - Typical or tested germination rate if published by the brand.\",\n";
        $prompt .= "  \"esc_seed_category_suggestion\": \"string|null - Suggest one or two most relevant category names from this list: Vegetables, Herbs, Flowers, Fruits, Root, Leafy Green, Legume, Brassica, Solanaceous (Nightshade), Cucurbit (Gourd), Allium (Onion family), Culinary, Medicinal, Aromatic, Annual, Perennial, Cut Flower, Berries, Melons. Return as a simple comma-separated string, e.g., 'Vegetables, Solanaceous (Nightshade)'.\"\n";
        $prompt .= "}\n";

        return $prompt;
    }

    /**
     * Process data extracted from API response to handle string "null" values.
     *
     * @param array $data Raw data from API.
     * @return array Processed data.
     */
    private static function process_api_data(array $data): array {
        $processed_data = [];

        foreach ($data as $key => $value) {
            // Handle string "null" values
            if ($value === 'null' || $value === "null") {
                $processed_data[$key] = null;
            }
            // Handle boolean values that might be strings
            else if ($key === 'container_suitability' || $key === 'cut_flower_potential') {
                if (is_string($value)) {
                    $lower_value = strtolower($value);
                    if ($lower_value === 'true') {
                        $processed_data[$key] = true;
                    } else if ($lower_value === 'false') {
                        $processed_data[$key] = false;
                    } else {
                        $processed_data[$key] = null;
                    }
                } else {
                    $processed_data[$key] = $value;
                }
            }
            // Handle all other values
            else {
                $processed_data[$key] = $value;
            }
        }

        return $processed_data;
    }

    /**
     * Sanitize data extracted from API response.
     * Deprecated: Sanitization now happens in ESC_DB::add_seed/update_seed.
     * Kept here for potential future use or reference.
     *
     * @param array $data Raw data from API.
     * @return array Sanitized data.
     */
    /*
    private static function sanitize_extracted_data( array $data ) : array {
        $sanitized_data = [];
        $allowed_fields = ESC_DB::get_allowed_fields(); // Use the definition from DB class

        foreach ( $allowed_fields as $ field => $type ) {
            if ( isset( $data[$field] ) ) {
                // Apply basic sanitization based on expected type
                // More specific sanitization might be needed depending on the field
                 switch ( $type ) {
                    case 'string':
                        $sanitized_data[$field] = sanitize_text_field( $data[$field] );
                        break;
                    case 'text':
                        // Allow some basic HTML? Use wp_kses_post for more control
                        $sanitized_data[$field] = sanitize_textarea_field( $data[$field] );
                        break;
                    case 'int':
                        $sanitized_data[$field] = absint( $data[$field] );
                        break;
                    case 'bool':
                        // Ensure it's a proper boolean or null
                        if ($data[$field] === null) {
                            $sanitized_data[$field] = null;
                        } else {
                            $sanitized_data[$field] = filter_var( $data[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
                        }
                        break;
                    case 'url':
                        $sanitized_data[$field] = esc_url_raw( $data[$field] );
                        break;
                    case 'date': // Dates are usually string YYYY-MM-DD
                         if ( preg_match( "/^\d{4}-\d{2}-\d{2}$/", $data[$field] ) ) {
                             $sanitized_data[$field] = sanitize_text_field( $data[$field] );
                         } else {
                             $sanitized_data[$field] = null; // Invalid format
                         }
                         break;
                    default:
                        $sanitized_data[$field] = sanitize_text_field( $data[$field] );
                }
            } else {
                 // Ensure field exists in output array, even if null from API
                 $sanitized_data[$field] = null;
            }
        }

         // Handle category suggestion separately if needed
         if (isset($data['esc_seed_category_suggestion'])) {
             $sanitized_data['esc_seed_category_suggestion'] = sanitize_text_field($data['esc_seed_category_suggestion']);
         }


        return $sanitized_data;
    }
    */

}