<?php
/**
 * Variety Suggestions Class
 *
 * @package    Erins_Seed_Catalog
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Variety_Suggestions
 *
 * Handles variety suggestions for seed types.
 */
class ESC_Variety_Suggestions {
    /**
     * Get variety suggestions for a seed type.
     *
     * @param string $seed_type The seed type to get varieties for.
     * @return array|WP_Error Array of variety suggestions or an error.
     */
    public static function get_variety_suggestions( $seed_type ) {
        $api_key = get_option( ESC_API_KEY_OPTION );
        $model = get_option( ESC_GEMINI_MODEL_OPTION, 'gemini-1.5-flash-latest' );
        
        if ( empty( $api_key ) ) {
            return new WP_Error( 'api_key_missing', __( 'Gemini API Key is missing. Please configure it in the plugin settings.', 'erins-seed-catalog' ) );
        }

        // Build the API endpoint URL
        $api_url = add_query_arg(
            'key',
            $api_key,
            'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent'
        );

        // Build the prompt for variety suggestions
        $prompt = sprintf(
            "Please provide a list of 15 common varieties of %s for gardening. "
            . "Return the response as a JSON array of strings containing only the variety names. "
            . "For example: [\"Variety 1\", \"Variety 2\", ...]. "
            . "Do not include any explanations or additional text outside the JSON array.",
            $seed_type
        );

        // Build the request body
        $request_body = [
            'contents' => [
                [
                    'parts' => [
                        [ 'text' => $prompt ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 512,
            ]
        ];

        // Make the API request
        $response = wp_remote_post(
            $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode( $request_body ),
                'timeout' => 15, // Shorter timeout for variety suggestions
            ]
        );

        // Check for errors
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Check the response code
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            $body = wp_remote_retrieve_body( $response );
            $error_data = json_decode( $body, true );
            $error_message = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Unknown API error.', 'erins-seed-catalog' );
            return new WP_Error( 'api_error', sprintf( __( 'API Error (%d): %s', 'erins-seed-catalog' ), $response_code, $error_message ) );
        }

        // Parse the response
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Check if we got a valid response
        if ( ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid response from API.', 'erins-seed-catalog' ) );
        }

        // Get the generated text
        $generated_text = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Try to parse the JSON response
        $varieties = self::parse_variety_response( $generated_text );
        
        if ( is_wp_error( $varieties ) ) {
            return $varieties;
        }
        
        return $varieties;
    }
    
    /**
     * Parse the variety response from the API.
     *
     * @param string $response_text The response text from the API.
     * @return array|WP_Error Array of varieties or an error.
     */
    private static function parse_variety_response( $response_text ) {
        // Try to extract JSON from the response
        $json_pattern = '/\[.*\]/s';
        if ( preg_match( $json_pattern, $response_text, $matches ) ) {
            $json_text = $matches[0];
            $varieties = json_decode( $json_text, true );
            
            if ( json_last_error() === JSON_ERROR_NONE && is_array( $varieties ) ) {
                // Filter out any non-string values and limit to 15 varieties
                $varieties = array_filter( $varieties, 'is_string' );
                $varieties = array_slice( $varieties, 0, 15 );
                
                return $varieties;
            }
        }
        
        // If we couldn't parse JSON, try to extract a list
        $lines = explode( "\n", $response_text );
        $varieties = [];
        
        foreach ( $lines as $line ) {
            // Look for lines that start with numbers, dashes, or quotes
            if ( preg_match( '/^\s*(?:\d+\.|-|\*|"|\')\s*([\w\s\-\']+)/', $line, $matches ) ) {
                $variety = trim( $matches[1] );
                if ( ! empty( $variety ) ) {
                    $varieties[] = $variety;
                }
            }
        }
        
        if ( ! empty( $varieties ) ) {
            return array_slice( $varieties, 0, 15 ); // Limit to 15 varieties
        }
        
        return new WP_Error( 'parsing_error', __( 'Could not parse variety suggestions.', 'erins-seed-catalog' ) );
    }
}
