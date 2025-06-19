$(document).ready(function(){
    "use strict";

    toggleExternalInternalLink();
    toggleMainSetupFields();
    toggleNewWindow();

    const $addForm = $("#custom_links_form");

    if($addForm.length > 0) {
        _validate_form($($addForm),
            {
                title: "required",
                href: "required",
                position: "number",
            }
        );
    }

    if($("[name='users[]'].ajax-search").length > 0) {
        init_ajax_search('staff', "[name='users[]'].ajax-search");
    }

    $('.icon-picker').iconpicker();

    $("[name=main_setup]").change(function(){
        toggleMainSetupFields();
    });

    $("[name=external_internal]").change(function(){
        toggleExternalInternalLink();
    });

    $("[name=http_protocol]").change(function(){
        toggleHttpProtocol();
    });

    $("[name=show_in_iframe]").change(function(){
        toggleNewWindow();
    });

    $("[name=href]").on('change keyup', function(){
        var link = $(this).val();
        var result = link.replace(/(^\w+:|^)\/\//, '');
        $(this).val(result);
    });

    if($("#custom-links-iframe").length > 0){
        var height = $("#wrapper").innerHeight();
        $("#custom-links-iframe").attr("height", height+'px');
    }
});

function toggleExternalInternalLink(){
    var link = $("[name=external_internal]:checked").val();
    if(link == "0"){
        $("#internal_link_prefix").removeClass('hide');
        $("#external_link_prefix").addClass('hide');
        $(".form_link").removeClass("hide");
        var initial_value = $(".form_link").find("input").attr("value");
        if(initial_value == "")
            $(".form_link").find("input").val("");
    }
    else if(link == "1"){
        $("#internal_link_prefix").addClass('hide');
        $("#external_link_prefix").removeClass('hide');
        $(".form_link").removeClass("hide");
        var initial_value = $(".form_link").find("input").attr("value");
        if(initial_value == "")
            $(".form_link").find("input").val("");
    }
    else{
        $(".form_link").addClass("hide");
        $(".form_link").find("input").val("#");
    }
    toggleHttpProtocol();
}

function toggleHttpProtocol(){
    var link = $("[name=external_internal]:checked").val();
    var http_protocol = $("[name=http_protocol]:checked").val();
    if(link == "0" || link == "2"){
        $(".http_protocol").addClass('hide');
    }
    else if(link == "1"){
        $(".http_protocol").removeClass('hide');
    }
    if(http_protocol == "0"){
        $("#external_link_prefix").text('http://');
    }
    else{
        $("#external_link_prefix").text('https://');
    }
}

function toggleNewWindow(){
    var show_in_iframe = $("[name=show_in_iframe]:checked").val();
    if(show_in_iframe == "1"){
        $(".form-blank").addClass('hide');
    }
    else{
        $(".form-blank").removeClass('hide');
    }
}

function toggleMainSetupFields(){
    var main_setup = $("[name=main_setup]:checked").val();
    var show_in_iframe = $("[name=show_in_iframe]:checked").val();
    var $icon_fields = $(".form-icon");
    var $blank_fields = $(".form-blank");
    var $form_field_users = $(".form-field-users");
    var $form_field_badge = $(".form-field-badge");
    var $form_require_login = $(".form-require-login");
    var $main_menu_items = $(".main_menu_items");
    var $setup_menu_items = $(".setup_menu_items");

    if(main_setup == "0" || main_setup == "2")
        $icon_fields.removeClass('hide');
    else{
        $icon_fields.addClass('hide');
        $("#icon-new").val('').trigger('change');
    }

    if((main_setup == "0" || main_setup == "2") && show_in_iframe == "0")
        $blank_fields.removeClass('hide');
    else{
        $blank_fields.addClass('hide');
        $("#open_in_blank0").trigger('click');
    }

    if(main_setup == "0" || main_setup == "1")
        $form_field_users.removeClass('hide');
    else{
        $form_field_users.addClass('hide');
        $form_field_users.find("select").selectpicker("val", "");
    }

    if(main_setup == "0" || main_setup == "1")
        $form_field_badge.removeClass('hide');
    else{
        $form_field_badge.addClass('hide');
        $form_field_badge.find('input').val('');
    }

    if(main_setup == "0" || main_setup == "1"){
        $form_require_login.addClass('hide');
        $("#require_login0").trigger('click');
    }
    else{
        $form_require_login.removeClass('hide');
    }

    if(main_setup == "0" || main_setup == "1"){
        $form_field_badge.removeClass('hide');
    }
    else{
        $form_field_badge.addClass('hide');
        $form_field_badge.find('input').val('');
    }

    if(main_setup == "0"){
        $main_menu_items.removeClass('hide');
        $main_menu_items.find("select").removeAttr("disabled").selectpicker('refresh');
        $setup_menu_items.addClass('hide');
        $setup_menu_items.find("select").attr("disabled", "true").selectpicker('refresh');
    }
    else if(main_setup == "1"){
        $main_menu_items.addClass('hide');
        $main_menu_items.find("select").attr("disabled", "true").selectpicker('refresh');
        $setup_menu_items.removeClass('hide');
        $setup_menu_items.find("select").removeAttr("disabled").selectpicker('refresh');
    }
    else{
        $main_menu_items.addClass('hide');
        $main_menu_items.find("select").attr("disabled", "true").selectpicker('refresh');
        $setup_menu_items.addClass('hide');
        $setup_menu_items.find("select").attr("disabled", "true").selectpicker('refresh');
    }
}