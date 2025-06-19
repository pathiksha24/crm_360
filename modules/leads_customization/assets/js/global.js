function new_lead_service_inline() {
    lead_add_inline_on_select_field("service");
}

function new_lead_language_inline() {
    lead_add_inline_on_select_field("language");
}

function lead_add_inline_on_select_field(type) {
    var html = "";
    if ($("body").hasClass("leads-email-integration") || $("body").hasClass("web-to-lead-form")) {
        type = "lead_" + type;
    }
    html =
        '<div id="new_lead_' +
        type +
        '_inline" class="form-group"><label for="new_' +
        type +
        '_name">' +
        $('label[for="' + type + '"]')
            .html()
            .trim() +
        '</label><div class="input-group"><input type="text" id="new_' +
        type +
        '_name" name="new_' +
        type +
        '_name" class="form-control"><div class="input-group-addon"><a href="#" onclick="lead_add_inline_select_submit(\'' +
        type +
        '\'); return false;" class="lead-add-inline-submit-' +
        type +
        '"><i class="fa fa-check"></i></a></div></div></div>';
    $(".form-group-select-input-" + type).after(html);
    $("body").find("#new_" + type + "_name").focus();
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop("disabled", true);
    $(".inline-field-new").addClass("disabled").css("opacity", 0.5);
    $(".form-group-select-input-" + type).addClass("hide");
}

function validate_lead_customization_form() {
    var validationObject = {
        name: "required",
        source: "required",
        service: "required",
        language: "required",
        phonenumber: "required",
        whatsapp_number: "required",
        status: {
            required: {
                depends: function (element) {
                    if ($("[lead-is-junk-or-lost]").length > 0) {
                        return false;
                    } else {
                        return true;
                    }
                },
            },
        },
    };
    var messages = {};
    app.lang['whatsapp_number_exists'] = 'Whatsapp Number already exists';
    $.each(leadUniqueValidationFields, function (key, field) {
        validationObject[field] = {};

        if (field == "email") {
            validationObject[field].email = true;
        }
	validationObject[field].required = true;
        validationObject[field].remote = {
            url: admin_url + "leads_customization/admin_leads/validate_unique_field",
            type: "post",
            data: {
                field: field,
                lead_id: function () {
                    return $("#lead-modal").find('input[name="leadid"]').val();
                },
            },
        };
        console.log(app.lang[field + "_exists"])
        if (typeof app.lang[field + "_exists"] != "undefined") {
            messages[field] = {
                remote: app.lang[field + "_exists"],
            };
        }
    });
    appValidateForm(
        $("#lead_form"),
        validationObject,
        lead_custom_profile_form_handler,
        messages
    );
}

function lead_custom_profile_form_handler(form) {
    form = $(form);
    let table_leads = $("table.table-leads_new");
    var data = form.serialize();
    var leadid = $("#lead-modal").find('input[name="leadid"]').val();
    $(".lead-save-btn").addClass("disabled");
    $.post(form.attr("action"), data)
        .done(function (response) {
            response = JSON.parse(response);
            if (response.message !== "") {
                alert_float("success", response.message);
            }
            if (response.proposal_warning && response.proposal_warning != false) {
                $("body").find("#lead_proposal_warning").removeClass("hide");
                $("body").find("#lead-modal").animate(
                    {
                        scrollTop: 0,
                    },
                    800
                );
            } else {
                _lead_init_data(response, response.id);
            }
            if ($.fn.DataTable.isDataTable(".table-leads_new")) {
                table_leads.DataTable().ajax.reload(null, false);
            } else if ($("body").hasClass("kan-ban-body")) {
                leads_kanban();
            }
        })
        .fail(function (data) {
            alert_float("danger", data.responseText);
            return false;
        });
    return false;
}