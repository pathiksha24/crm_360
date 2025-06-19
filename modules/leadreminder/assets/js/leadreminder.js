$(function () {

    function checkReminders() {

        const now = new Date();
        const currentTime = now.getFullYear() + '-' +
            (now.getMonth() + 1).toString().padStart(2, '0') + '-' +
            now.getDate().toString().padStart(2, '0') + ' ' +
            now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0') + ':' + '00'

        if (!$('#notificationModal').hasClass('in')) {

            $.ajax({
                url: admin_url + '/leadreminder/fetch',
                success: function (response) {
                    response = JSON.parse(response);

                    var reminder = response.reminder;
                    if (reminder != null && (currentTime == reminder.date || currentTime >= reminder.date)) {
                        createModal(reminder.description, reminder.rel_id, reminder.id);
                        $('#notificationModal').modal('show');
                    }
                }
            });
        }

    }

    setInterval(checkReminders, 6000);

    function createModal(description, rel_id, reminder_id) {
        $('#notificationModal').remove();
        const modalHTML = `
        <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <i class="fa fa-bell modal-icon"></i> <!-- Icon added -->
                        <h3 class="modal-title" id="exampleModalLabel">Leads Reminder Notification</h3>
                        <button type="button" class="close" data-dismiss="modal" id="notificationModal_close"aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    <input type="hidden" id="reminder_id" name="reminder_id"value="${reminder_id}">
                    <input type="hidden" id="lead_id" name="lead_id"value="${rel_id}">
                        <p id="reminderDescription">${description}</p>
                    </div>
                    <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal" id="goToLeadBtn">Go to lead</button>
                     <button type="button" class="btn btn-secondary" data-dismiss="modal"id="reminder_close" >Close</button>
                    </div>
                </div>
            </div>
        </div>`;

        $('body').append(modalHTML);
    }

    function injectCSS() {
        const css = `

     
        #notificationModal.modal.show .modal-dialog {
            transform: translate(0, 0);
        }
    
        /* Custom modal styling */
       #notificationModal .modal-content {
            border-radius: 12px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            border: none;
            background: linear-gradient(to bottom right, #ffffff, #f0f0f0);
        }
    
       #notificationModal .modal-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    
        #notificationModal.modal-header .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
    
        #notificationModal.modal-header .modal-icon {
            font-size: 1.8rem;
            margin-right: 10px;
        }
    
        #notificationModal.modal-body {
            font-size: 1.2rem;
            padding: 30px;
            background-color: #f8f9fa;
            text-align: left;
            color: #333;
        }
    
        #notificationModal.modal-footer {
            justify-content: space-between;
            background-color: #f8f9fa;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
    
        #notificationModal.modal-footer .btn-primary {
            padding: 10px 20px;
            border-radius: 20px;
            background-color: #28a745;
            border-color: #28a745;
            font-weight: 600;
        }
    
       #notificationModal .modal-footer .btn-secondary {
            padding: 10px 20px;
            border-radius: 20px;
            border-color: #6c757d;
            font-weight: 600;
        }
    
       /* Button hover effect */
            #notificationModal .modal-footer .btn-secondary:hover {
                background-color: #007bff; /* Primary color */
                border-color: #007bff;     /* Primary border color */
                color: white;              /* Ensure the text color is white on hover */
            }

            #notificationModal .modal-footer .btn-secondary {
                background-color: #6c757d; /* Default secondary button color */
                border-color: #6c757d;     /* Default border color */
                color: white;              /* Ensure text is white */
            }

    
            #notificationModal.modal-icon {
                margin-bottom: 10px;
            }
        }
        `;


        const style = document.createElement('style');
        style.type = 'text/css';
        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }

        document.head.appendChild(style);
    }
    function send_popped_up_ajax(reminder_id) {
        console.log('reminder ID:', reminder_id);
        $.ajax({
            url: admin_url + '/leadreminder/update/' + reminder_id,
            type: 'POST',
            data: {
                reminderid: reminder_id,
            },
            success: function (response) {
                console.log('Reminder status updated successfully:', response);
                // window.location.reload();
            },
            error: function (xhr, status, error) {
                console.error('Error updating reminder status:', error);
            }
        });
    }

    injectCSS();
    $(document).on('click', '#reminder_close', function () {
        var reminder_id = $('#reminder_id').val();
        send_popped_up_ajax(reminder_id);
    });
    $(document).on('click', '#notificationModal_close', function () {
        var reminder_id = $('#reminder_id').val();
        send_popped_up_ajax(reminder_id);
    });

    $(document).on('click', '#goToLeadBtn', function () {

        var leadId = $('#lead_id').val();
        var reminder_id = $('#reminder_id').val();
        console.log('Lead ID:', leadId);
        $('#notificationModal').modal('hide');

        send_popped_up_ajax(reminder_id);

        window.location.href = admin_url + 'leads/index/' + leadId;

    });

});

