<?php defined('BASEPATH') or exit('No direct script access allowed');

function get_custom_dashboard_widgets()
{
    return [
        [
            'path'      => 'admin/custom_widgets/team_task_summary',
            'container' => 'top-12', // or left-8/right-4 depending on layout
        ],
    ];
}

function render_custom_dashboard_widgets($container)
{
    $CI = &get_instance();
    $widgets = get_custom_dashboard_widgets();

    $staff_id = get_staff_user_id();
    $meta_key = 'custom_dashboard_widgets_order';

    $order = json_decode(get_staff_meta($staff_id, $meta_key), true);

    $ordered_widgets = [];
    $unassigned = [];

    if (is_array($order)) {
        foreach ($order as $widget_id) {
            foreach ($widgets as $widget) {
                $id = md5($widget['path']);
                if ($id === $widget_id && $widget['container'] === $container) {
                    $ordered_widgets[] = $widget;
                }
            }
        }

        foreach ($widgets as $widget) {
            $id = md5($widget['path']);
            if (!in_array($id, $order) && $widget['container'] === $container) {
                $unassigned[] = $widget;
            }
        }
    } else {
        foreach ($widgets as $widget) {
            if ($widget['container'] === $container) {
                $ordered_widgets[] = $widget;
            }
        }
    }

    $widgets_to_render = array_merge($ordered_widgets, $unassigned);

    foreach ($widgets_to_render as $widget) {
        $widget_id = md5($widget['path']);
        echo '<div class="widget" id="' . $widget_id . '" data-widget-id="' . $widget_id . '">';
        $CI->load->view($widget['path']);
        echo '</div>';
    }

    
}



