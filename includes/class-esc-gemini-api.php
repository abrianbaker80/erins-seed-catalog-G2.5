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
     * Get available Gemini models for the dropdown.
     *
     * @return array Associative array of model IDs and names.
     */
    public static function get_available_models() {
        // Apply filter to allow model updater to modify this list
        $models = apply_filters('esc_gemini_available_models', [
            // Free models
            'gemini-1.0-pro' => [
                'name' => 'Gemini 1.0 Pro',
                'type' => 'free',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-1.0-pro-latest' => [
                'name' => 'Gemini 1.0 Pro (Latest)',
                'type' => 'free',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-1.5-flash' => [
                'name' => 'Gemini 1.5 Flash',
                'type' => 'free',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-1.5-flash-latest' => [
                'name' => 'Gemini 1.5 Flash (Latest)',
                'type' => 'free',
                'available' => true,
                'recommended' => true,
            ],
            'gemini-1.5-pro' => [
                'name' => 'Gemini 1.5 Pro',
                'type' => 'free',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-1.5-pro-latest' => [
                'name' => 'Gemini 1.5 Pro (Latest)',
                'type' => 'free',
                'available' => true,
                'recommended' => false,
            ],
            // Advanced models
            'gemini-1.5-pro-vision' => [
                'name' => 'Gemini 1.5 Pro Vision',
                'type' => 'advanced',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-1.5-pro-vision-latest' => [
                'name' => 'Gemini 1.5 Pro Vision (Latest)',
                'type' => 'advanced',
                'available' => true,
                'recommended' => false,
            ],
            // Experimental models
            'gemini-2.0-flash-lite' => [
                'name' => 'Gemini 2.0 Flash Lite',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-flash-lite-latest' => [
                'name' => 'Gemini 2.0 Flash Lite (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-flash' => [
                'name' => 'Gemini 2.0 Flash',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-flash-latest' => [
                'name' => 'Gemini 2.0 Flash (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-pro' => [
                'name' => 'Gemini 2.0 Pro',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-pro-latest' => [
                'name' => 'Gemini 2.0 Pro (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-pro-vision' => [
                'name' => 'Gemini 2.0 Pro Vision',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-pro-vision-latest' => [
                'name' => 'Gemini 2.0 Pro Vision (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-ultra' => [
                'name' => 'Gemini 2.0 Ultra',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-ultra-latest' => [
                'name' => 'Gemini 2.0 Ultra (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-ultra-vision' => [
                'name' => 'Gemini 2.0 Ultra Vision',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
            'gemini-2.0-ultra-vision-latest' => [
                'name' => 'Gemini 2.0 Ultra Vision (Latest)',
                'type' => 'experimental',
                'available' => true,
                'recommended' => false,
            ],
        ]);

        return $models;
    }

    /**
     * Get models for dropdown in settings.
     *
     * @return array Associative array for dropdown.
     */
    public static function get_models_for_dropdown() {
        $models = self::get_available_models();
        $dropdown_options = [];

        // Group models by type
        $grouped_models = [];
        foreach ($models as $model_id => $model_data) {
            if (!isset($model_data['name'])) {
                continue;
            }

            $type = $model_data['type'] ?? 'free';
            if (!isset($grouped_models[$type])) {
                $grouped_models[$type] = [];
            }
            $grouped_models[$type][$model_id] = $model_data;
        }

        // Add free models first
        if (!empty($grouped_models['free'])) {
            $dropdown_options['free_header'] = __('--- Free Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['free'] as $model_id => $model_data) {
                $name = $model_data['name'] ?? $model_id;

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

        // Add advanced models
        if (!empty($grouped_models['advanced'])) {
            $dropdown_options['advanced_header'] = __('--- Advanced Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['advanced'] as $model_id => $model_data) {
                $name = $model_data['name'] ?? $model_id;

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
                $name = $model_data['name'] ?? $model_id;

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

        // Add legacy models
        if (!empty($grouped_models['legacy'])) {
            $dropdown_options['legacy_header'] = __('--- Legacy Models ---', 'erins-seed-catalog');
            foreach ($grouped_models['legacy'] as $model_id => $model_data) {
                $name = $model_data['name'] ?? $model_id;

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

        // Construct the API endpoint URL
        $api_url = add_query_arg(
            'key',
            $api_key,
            self::get_api_endpoint()
        );

        // Make the API request
        $response = wp_remote_post(
            $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body'    => json_encode( $request_body ),
                'timeout' => 30, // Increase timeout for potentially longer responses
            ]
        );

        // Check for HTTP errors
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'api_http_error', __( 'Error communicating with the Gemini API.', 'erins-seed-catalog' ), $response->get_error_message() );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Log response body before JSON decode
        error_log('Gemini API Response Body: ' . $response_body);

        $result_data = json_decode( $response_body, true );

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
            return new WP_Error(
                'api_error',
                sprintf( __( 'Gemini API Error (Code: %d):', 'erins-seed-catalog' ), $response_code ),
                $error_message
            );
        }

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

        // Try to parse the JSON from the response
        $extracted_data = json_decode( $generated_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // If Gemini didn't return valid JSON despite the prompt/config
            error_log("Gemini API Response - Not Valid JSON: " . $generated_text); // Log the raw response
            return new WP_Error( 'api_json_error', __( 'Could not parse the JSON data from the Gemini API response.', 'erins-seed-catalog' ), json_last_error_msg() . ' | Raw text: ' . substr($generated_text, 0, 200) . '...' );
        }

        // Process the data to handle string "null" values and other formatting
        $processed_data = self::process_api_data( $extracted_data );

        // Add suggested term IDs based on category suggestions
        $processed_data = self::add_suggested_term_ids( $processed_data );

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
        $prompt .= "If Seed Name and Variety are given, find the most accurate information for that specific variety. ";
        $prompt .= "If only Seed Name is given, find general information for that type of seed.\n\n";
        $prompt .= "Extract and return the following information ONLY in JSON format. Use snake_case for keys matching the database fields. ";
        $prompt .= "If a specific piece of information cannot be found or is not applicable, return `null` for that field's value (do not omit the key).\n\n";
        $prompt .= "{\n";
        $prompt .= "  \"variety_name\": \"string|null - The accurate variety name. If not specified or found, use the input variety or derive from name.\",\n";
        $prompt .= "  \"image_url\": \"string|null - Suggest one high-quality image URL (HTTPS required) of the mature plant, fruit/flower, or seeds, suitable for display. IMPORTANT GUIDELINES FOR IMAGE URLS:\n1. For Wikimedia Commons images, use the DIRECT file URL format: https://upload.wikimedia.org/wikipedia/commons/[hash]/[filename] - DO NOT use /thumb/ versions or resized versions with dimensions in the filename\n2. Only provide URLs from public domains, Wikimedia Commons, or other reliable sources that allow direct image access\n3. Avoid URLs from commercial seed catalogs (like Burpee, Baker Creek, Johnny's Seeds, Stark Bros) as they often block direct access\n4. Verify the image is publicly accessible\n5. Provide only the direct image URL string that ends with an image extension like .jpg, .jpeg, .png, or .gif\n6. For Wikimedia Commons, prefer the original file URL over thumbnail versions\",\n";
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
        $prompt .= "  \"usda_zones\": \"string|null - Recommended USDA Hardiness Zones, especially for perennials. e.g., Zones 3-9.\",\n";
        $prompt .= "  \"pollinator_info\": \"string|null - Is it beneficial for pollinators? e.g., Attracts bees and butterflies.\",\n";
        $prompt .= "  \"container_suitability\": \"boolean|null - True if suitable for container growing, false or null otherwise.\",\n";
        $prompt .= "  \"cut_flower_potential\": \"boolean|null - True if suitable as a cut flower, false or null otherwise.\",\n";
        $prompt .= "  \"storage_recommendations\": \"string|null - How to store harvested produce, e.g., Store carrots in cool, damp conditions.\",\n";
        $prompt .= "  \"edible_parts\": \"string|null - Which parts of the plant are edible, e.g., Leaves and stems; Fruit; Root.\",\n";
        $prompt .= "  \"historical_background\": \"string|null - Brief origin or history of the variety.\",\n";
        $prompt .= "  \"companion_plants\": \"string|null - List a few suggested companion plants.\",\n";
        $prompt .= "  \"regional_tips\": \"string|null - Any specific growing tips for certain regions if known.\",\n";
        $prompt .= "  \"seed_saving_info\": \"string|null - If open-pollinated or heirloom, provide brief seed saving guidance (isolation distance, how to collect/dry).\",\n";
        $prompt .= "  \"esc_seed_category_suggestion\": \"string|null - Suggest the most relevant category and subcategory from this hierarchical list:\\n\\n- Field & Forage Crops: Cover Crops, Fiber Crops, Forage Crops, Oilseeds\\n- Fruits: Berries, Melons\\n- Grains & Cereals\\n- Grasses: Forage Grasses, Lawn/Turf Grasses, Ornamental Grasses\\n- Herbs: Aromatic Herbs, Culinary Herbs, Medicinal Herbs\\n- Ornamental Flowers: Cut Flowers, General Garden Flowers, Native/Wildflower Seeds\\n- Specialty Seeds: Sprouts/Microgreens\\n- Trees & Shrubs: Fruit Trees/Shrubs, Ornamental Trees, Ornamental Shrubs\\n- Vegetables: Allium (Onion family), Brassica (Cabbage family), Cucurbit (Gourd family), Leafy Greens, Legumes, Root Crops, Solanaceous\\n\\nReturn as a simple comma-separated string with parent category first, then subcategory if applicable, e.g., 'Vegetables, Solanaceous' or 'Herbs, Culinary Herbs'.\"\n";
        $prompt .= "}\n";
        return $prompt;
    }

    /**
     * Process data extracted from API response to handle string "null" values and array responses.
     *
     * @param array $data Raw data from API.
     * @return array Processed data.
     */
    private static function process_api_data(array $data): array {
        // Check if the data is an array of objects (which happens sometimes with the API)
        if (isset($data[0]) && is_array($data[0])) {
            error_log('Gemini API returned array of objects, using first item');
            $data = $data[0];
        }

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
     * Add suggested term IDs based on category suggestions.
     *
     * @param array $data Processed data from API.
     * @return array Data with suggested term IDs added.
     */
    private static function add_suggested_term_ids(array $data): array {
        if (empty($data['esc_seed_category_suggestion'])) {
            return $data;
        }

        $suggested_categories = explode(',', $data['esc_seed_category_suggestion']);
        $suggested_categories = array_map('trim', $suggested_categories);

        // Get all terms
        $all_terms = get_terms([
            'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
            'hide_empty' => false,
        ]);

        if (is_wp_error($all_terms) || empty($all_terms)) {
            return $data;
        }

        $suggested_term_ids = [];

        // First, try to find exact matches
        foreach ($suggested_categories as $category) {
            foreach ($all_terms as $term) {
                if (strtolower($term->name) === strtolower($category)) {
                    $suggested_term_ids[] = $term->term_id;
                    break;
                }
            }
        }

        // If no exact matches, try partial matches
        if (empty($suggested_term_ids)) {
            foreach ($suggested_categories as $category) {
                foreach ($all_terms as $term) {
                    if (stripos($term->name, $category) !== false || stripos($category, $term->name) !== false) {
                        $suggested_term_ids[] = $term->term_id;
                        break;
                    }
                }
            }
        }

        $data['suggested_term_ids'] = $suggested_term_ids;
        return $data;
    }
}
