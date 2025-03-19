<?php

namespace Supreme\ConditionalDiscounts\Views;

class View {
    /**
     * Render a template file with data
     * 
     * @param string $template Relative path to template file
     * @param array $data Data to expose to template
     * @return string Rendered HTML
     * @throws \Exception If template file not found
     */
    public static function render_template($template_path, array $context = []) {
        
        // Validate template path
        $resolved_path = realpath($template_path);
        $plugin_path = realpath(CDWC_PLUGIN_PATH);
        
        if (strpos($resolved_path, $plugin_path) !== 0) {
            _doing_it_wrong(__METHOD__, 'Template path must be within plugin directory', '1.0');
            return;
        }

        if (!file_exists($resolved_path)) {
            echo '<p>' . esc_html__('Template file not found.', 'conditional-discounts-for-woocommerce') . '</p>';
            return;
        }

        // Extract context variables with prefix for safety
        extract(array_merge(
            ['cdwc_template' => $context],
            $context
        ), EXTR_SKIP);

        // Buffer and require with limited scope
        ob_start();
        require $resolved_path;
        $output = ob_get_clean();

        // Allow filtering of output
        echo apply_filters('cdwc_template_output', $output, $template_path, $context);
    }
}