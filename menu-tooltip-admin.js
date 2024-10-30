jQuery(document).ready(function($) {
    // Function to initialize color picker
    function initColorPicker(element) {
        element.wpColorPicker();
    }

    // Initialize color pickers for existing fields
    $('.menu-tooltip-color-picker').each(function() {
        initColorPicker($(this));
    });

    // Function to load sub-menu items
    function loadSubMenuItems(menuSelect) {
        var menuId = menuSelect.val();
        var subMenuSelect = menuSelect.siblings('.menu-tooltip-submenu-select');
        
        if (menuId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_sub_menu_items',
                    menu_id: menuId
                },
                success: function(response) {
                    subMenuSelect.html(response);
                }
            });
        } else {
            subMenuSelect.html('<option value="">Select a Sub-menu</option>');
        }
    }

    // Load sub-menu items when menu is selected
    $(document).on('change', '.menu-tooltip-menu-select', function() {
        loadSubMenuItems($(this));
    });

    // Add new row
    $('#add-more-submenu').on('click', function() {
        var index = $('.menu-tooltip-item').length;
        var template = $('#menu-tooltip-template').html();
        var newRow = $(template.replace(/\[0\]/g, '[' + index + ']'));
        
        $('#menu-tooltip-fields').append(newRow);
        
        // Initialize color picker for the new row
        newRow.find('.menu-tooltip-color-picker').each(function() {
            initColorPicker($(this));
        });
    });

    // Delete row
    $(document).on('click', '.menu-tooltip-delete', function() {
        if ($('.menu-tooltip-item').length > 1) {
            $(this).closest('.menu-tooltip-item').remove();
        }
    });
});