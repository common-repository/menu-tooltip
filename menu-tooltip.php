<?php
/*
Plugin Name: Menu Tooltip
Plugin URI: https://wppluginsforyou.com
Description: Add text and Dashicons icons or Dashicons as tooltips to WordPress menu items with custom positioning.
Version: 1.2
Author: pkthakur
Author URI: https://isaas.in
License: GPL v2 or later
Text Domain: menu-tooltip
Domain Path: /languages
*/

// Register settings for the plugin
function menu_tooltip_register_settings() {
    register_setting('menu_tooltip_options_group', 'menu_tooltip_data');  // Register the settings
}
add_action('admin_init', 'menu_tooltip_register_settings');

// Add the plugin's settings page to the admin menu
function menu_tooltip_add_admin_menu() {
    add_menu_page(
        'Menu Tooltip Settings',    // Page title
        'Menu Tooltip',             // Menu title
        'manage_options',           // Capability
        'menu-tooltip',             // Menu slug
        'menu_tooltip_settings_page', // Function to display the settings page
        'dashicons-admin-generic',  // Dashicon for the menu
        80                          // Position in the admin menu
    );
}
add_action('admin_menu', 'menu_tooltip_add_admin_menu');

// Display the settings page
function menu_tooltip_settings_page() {
    ?>
    <div class="wrap">
        <h1>Menu Tooltip Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('menu_tooltip_options_group');
            do_settings_sections('menu_tooltip_options_group');
            ?>

            <?php menu_tooltip_field_render(); ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
//render fields for tooltip

function menu_tooltip_field_render() {
    $menus = wp_get_nav_menus();
    $data = get_option('menu_tooltip_data', []);
    $submenu_count = isset($data['submenus']) ? count($data['submenus']) : 1;

    // Dashicons list with text options
    $dashicons = [
        'dashicons-admin-users' => 'User/Profile',
        'dashicons-admin-settings' => 'Settings/Gear',
        'dashicons-search' => 'Search/Magnifying Glass',
        'dashicons-menu' => 'Menu/Hamburger',
        'dashicons-cart' => 'Shopping Cart',
        'dashicons-email' => 'Envelope/Mail',
        'dashicons-bell' => 'Bell/Notifications',
        'dashicons-calendar' => 'Calendar',
        'dashicons-flag' => 'Bookmark/Flag',
        'text-new' => 'New',
        'text-hurry' => 'Hurry!',
        'text-offers' => 'Offers!'
    ];

    ?>
    <div id="menu-tooltip-fields">
        <?php for ($i = 0; $i < $submenu_count; $i++) : ?>
            <?php menu_tooltip_render_item($i, $menus, $dashicons, $data); ?>
        <?php endfor; ?>
    </div>
    <button type="button" id="add-more-submenu">Add More</button>

    <!-- Template for new items -->
    <script type="text/template" id="menu-tooltip-template">
        <?php menu_tooltip_render_item(0, $menus, $dashicons, []); ?>
    </script>
    <?php
}

// Function to render a single menu tooltip item
function menu_tooltip_render_item($index, $menus, $dashicons, $data) {
    ?>
    <div class="menu-tooltip-item" data-index="<?php echo esc_attr($index); ?>">
        <label>Select Menu:</label>
        <select name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][menu]" class="menu-tooltip-menu-select">
            <option value="">Select a Menu</option>
            <?php foreach ($menus as $menu) : ?>
                <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($data['submenus'][$index]['menu'] ?? '', $menu->term_id); ?>>
                    <?php echo esc_html($menu->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Select Sub-menu:</label>
        <select name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][submenu]" class="menu-tooltip-submenu-select">
            <option value="">Select a Sub-menu</option>
            <?php if (!empty($data['submenus'][$index]['menu'])): ?>
                <?php
                $menu_items = wp_get_nav_menu_items($data['submenus'][$index]['menu']);
                foreach ($menu_items as $item):
                    if ($item->menu_item_parent == 0):
                        ?>
                        <option value="<?php echo esc_attr($item->ID); ?>" <?php selected($data['submenus'][$index]['submenu'] ?? '', $item->ID); ?>>
                            <?php echo esc_html($item->title); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <label>Select Dashicon or Text:</label>
        <select name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][icon]" class="menu-tooltip-icon-select">
            <option value="">Select an Icon or Text</option>
            <?php foreach ($dashicons as $class => $label) : ?>
                <option value="<?php echo esc_attr($class); ?>" <?php selected($data['submenus'][$index]['icon'] ?? '', $class); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="icon_color">Icon Color:</label>
        <input type="text" name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][icon_color]" value="<?php echo esc_attr($data['submenus'][$index]['icon_color'] ?? ''); ?>" class="menu-tooltip-color-picker" />

        <label for="blink">Enable Blinking:</label>
        <input type="checkbox" name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][blink]" 
            value="1" <?php checked(isset($data['submenus'][$index]['blink']) ? $data['submenus'][$index]['blink'] : 0, 1); ?> />

        <label for="icon_alignment">Icon Alignment:</label>
        <select name="menu_tooltip_data[submenus][<?php echo esc_attr($index); ?>][icon_alignment]">
            <option value="top" <?php selected($data['submenus'][$index]['icon_alignment'] ?? 'top', 'top'); ?>>Top</option>
            <option value="bottom" <?php selected($data['submenus'][$index]['icon_alignment'] ?? 'bottom', 'bottom'); ?>>Bottom</option>
        </select>

        <button type="button" class="menu-tooltip-delete">Delete</button>
    </div>
    <?php
}

// Enqueue Dashicons, color picker script, and our custom admin script
function menu_tooltip_enqueue_dashicons() {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('menu-tooltip-color-picker', plugin_dir_url(__FILE__) . 'menu-tooltip-color-picker.js', ['wp-color-picker'], false, true);
    wp_enqueue_script('menu-tooltip-admin', plugin_dir_url(__FILE__) . 'menu-tooltip-admin.js', ['jquery', 'wp-color-picker'], false, true);
}
add_action('admin_enqueue_scripts', 'menu_tooltip_enqueue_dashicons');

// AJAX action to get sub-menu items
function get_sub_menu_items() {
    $menu_id = $_POST['menu_id'];
    $menu_items = wp_get_nav_menu_items($menu_id);
    
    $output = '<option value="">Select a Sub-menu</option>';
    foreach ($menu_items as $item) {
        if ($item->menu_item_parent == 0) {
            $output .= '<option value="' . esc_attr($item->ID) . '">' . esc_html($item->title) . '</option>';
        }
    }
    
    echo $output;
    wp_die();
}
add_action('wp_ajax_get_sub_menu_items', 'get_sub_menu_items');

// Display icons or text in the menu
function menu_tooltip_display_icons($items, $args) {
    $data = get_option('menu_tooltip_data');
    if (!empty($data['submenus'])) {
        foreach ($data['submenus'] as $submenu) {
            if (!empty($submenu['menu']) && !empty($submenu['submenu']) && $args->menu->term_id == $submenu['menu']) {
                foreach ($items as &$item) {
                    if ($item->ID == $submenu['submenu']) {
                        $icon = '';
                        $style = !empty($submenu['icon_color']) ? 'style="color:' . esc_attr($submenu['icon_color']) . ';"' : '';
                        
                        // Check if it's text or dashicon
                        if (!empty($submenu['icon']) && strpos($submenu['icon'], 'dashicons') !== false) {
                            $icon = '<span class="dashicons ' . esc_attr($submenu['icon']) . '" ' . $style . '></span>';
                        } else if (strpos($submenu['icon'], 'text-') !== false) {
                            $icon = '<span class="menu-tooltip-text ' . esc_attr($submenu['icon']) . '" ' . $style . '>' . str_replace('text-', '', $submenu['icon']) . '</span>';
                        }

                        if (!empty($icon)) {
                            if (isset($submenu['blink']) && $submenu['blink']) {
                                $icon = '<span class="menu-icon blink">' . $icon . '</span>';
                            }
                            $alignment = isset($submenu['icon_alignment']) ? esc_attr($submenu['icon_alignment']) : 'top';
                            $item->title = '<span class="menu-text">' . esc_html($item->title) . '</span>' 
                                            . '<span class="menu-icon-wrapper ' . $alignment . '">' 
                                            . $icon 
                                            . '</span>';
                        }
                    }
                }
            }
        }
    }
    return $items;
}
add_filter('wp_nav_menu_objects', 'menu_tooltip_display_icons', 10, 2);

// CSS for alignment and blinking
function menu_tooltip_custom_css() {
    ?>
    <style>
        .menu-icon-wrapper.top {
            position: relative;
            display: inline-block;
            top: -10px;  /* Position icon above the last letter */
            right: -10px; /* Adjust over the last letter */
        }

        .menu-icon-wrapper.bottom {
            position: relative;
            display: inline-block;
            bottom: -10px;  /* Position icon below */
            right: -10px;  /* Adjust under the last letter */
        }

        .menu-icon.blink {
            animation: blinker 1s linear infinite;
        }

        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'menu_tooltip_custom_css');
