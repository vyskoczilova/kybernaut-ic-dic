<?php

// Source: https://github.com/sudar/wp-plugin-test-tools/blob/master/src/Tests/WPMock/wp-function-mocks.php

/**
 * WordPress mock functions.
 */

if (!function_exists('plugin_basename')) {
    function plugin_basename()
    {
        return \WP_Mock\Handler::predefined_return_function_helper(__FUNCTION__, func_get_args());
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url()
    {
        return \WP_Mock\Handler::predefined_return_function_helper(__FUNCTION__, func_get_args());
    }
}

if (!function_exists('_n_noop')) {
    function _n_noop()
    {
        return \WP_Mock\Handler::predefined_return_function_helper(__FUNCTION__, func_get_args());
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return \WP_Mock\Handler::predefined_return_function_helper(__FUNCTION__, func_get_args());
    }
}

if (!function_exists('plugin_dir_path')) {
    /**
     * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
     *
     * @since 2.8.0
     *
     * @param string $file The filename of the plugin (__FILE__).
     *
     * @return string the filesystem path of the directory that contains the plugin.
     */
    function plugin_dir_path($file)
    {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('trailingslashit')) {
    /**
     * Appends a trailing slash.
     *
     * Will remove trailing forward and backslashes if it exists already before adding
     * a trailing forward slash. This prevents double slashing a string or path.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @since 1.2.0
     *
     * @param string $string What to add the trailing slash to.
     *
     * @return string String with trailing slash added.
     */
    function trailingslashit($string)
    {
        return untrailingslashit($string) . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    /**
     * Removes trailing forward slashes and backslashes if they exist.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @since 2.2.0
     *
     * @param string $string What to remove the trailing slashes from.
     *
     * @return string String without the trailing slashes.
     */
    function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('wp_parse_args')) {
    /**
    * Merges user defined arguments into defaults array.
    *
    * This function is used throughout WordPress to allow for both string or array
    * to be merged into another array.
    *
    * @since 2.2.0
    * @since 2.3.0 `$args` can now also be an object.
    *
    * @param string|array|object $args     Value to merge with $defaults.
    * @param array               $defaults Optional. Array that serves as the defaults.
    *                                      Default empty array.
    * @return array Merged user defined values with defaults.
    */
    function wp_parse_args($args, $defaults = array())
    {
        if (is_object($args)) {
            $parsed_args = get_object_vars($args);
        } elseif (is_array($args)) {
            $parsed_args = &$args;
        } else {
            wp_parse_str($args, $parsed_args);
        }

        if (is_array($defaults) && $defaults) {
            return array_merge($defaults, $parsed_args);
        }
        return $parsed_args;
    }
}

if(!function_exists('wp_json_encode')) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode($data, $options, $depth );
    }
}

if(!function_exists('wp_remote_get')) {
    function wp_remote_get( $url, $args = array() ) {
        // Initialize a new cURL session
        $ch = curl_init();
    
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
    
        // Set the option to return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        // Execute the cURL session
        $output = curl_exec($ch);
    
        // Close the cURL session
        curl_close($ch);
    
        // Return the output
        return $output;
    }
}