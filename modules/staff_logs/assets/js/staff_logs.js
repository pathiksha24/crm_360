let waitingTime = 60000*5; // 5 minute

var internal_navigation = false;
    
// Initialize the number of open tabs in localStorage
if (!localStorage.getItem('openTabsCount')) {
    localStorage.setItem('openTabsCount', 0);
}

let __openTabsCount = localStorage.getItem('openTabsCount');

if (!localStorage.getItem('currentStatus')) {
    localStorage.setItem('currentStatus', 0);
}

var __currentStatus = localStorage.getItem('currentStatus');

updateStatus(1);

// Increment tab count when this tab is opened
localStorage.setItem('openTabsCount', parseInt(__openTabsCount) + 1);
localStorage.setItem('currentActivity', Date.now());

let __currentActivity = localStorage.getItem('currentActivity');

 // Function to send status to the server
 function updateStatus(status) {

    if (parseInt(__currentStatus) != status) {
        __currentStatus = status;
        localStorage.setItem('currentStatus', status); // Update local status
        $.ajax({
            url: admin_url + 'staff_logs/update_status/' + status,
        });
    }
}

// $(function () {

    // Track mouse movement and reset activity timer
    window.onmousemove = function () {
        __currentActivity = Date.now();
        localStorage.setItem('currentActivity',__currentActivity);
        if (parseInt(__currentStatus) != 1) {
            updateStatus(1); // Set status to online
        }
    };

    // Periodically check for inactivity
    setInterval(function () {

        let idleTime = Date.now() - __currentActivity;

        if ( idleTime > waitingTime) {
            
            updateStatus(0); // Set offline if no active tabs or activity

        }
    }, 5000); // Check every 5 seconds

    $(document).find('a[href*="'+site_url+'"]').on('click',(function() {
        internal_navigation = true;
    }));
    // Before closing the tab, decrease the openTabsCount and set offline if necessary
    window.addEventListener('beforeunload', function (event) {

        
        // Check if the page is being reloaded (not closed)
        console.log(performance.getEntriesByType('navigation')[0]);
        let _openTabsCount = parseInt(__openTabsCount) - 1;
        _openTabsCount = isNumeric(_openTabsCount) && _openTabsCount > 0 ? _openTabsCount : 0;
       
        localStorage.setItem('openTabsCount', _openTabsCount);
        localStorage.removeItem('currentActivity');

        if (performance.getEntriesByType('navigation')[0].type == 'reload' || performance.getEntriesByType('navigation')[0].type == 'back_forward') {
            // If it's a reload, do nothing and return
            return;
        }

        if(internal_navigation){
            return;
        }

        internal_navigation = false;
        if (_openTabsCount === 0) {
            updateStatus(0); // Set offline if last tab is closing
        }
        

    });
    function isNumeric(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    window.addEventListener('storage', function(event) {

        if (event.key === 'currentActivity') {
            __currentActivity = event.newValue;
        }else if (event.key === 'currentStatus') {
            __currentStatus = event.newValue;
        }else if (event.key === 'openTabsCount') {
            __openTabsCount = event.newValue;
        }
    });

   

    if (admin_url + 'staff' == window.location.href) {
        setInterval(function () {
            $('.table-staff').DataTable().ajax.reload(null, false);
        }, 5000);
    }

    $("body").on("change", 'input[name="staff_logs_date"]', function () {


        $('.table-staff_logs').DataTable().destroy();

        var ActivityLogServerParams = [];
        ActivityLogServerParams["staff_logs_date"] = '[name="staff_logs_date"]';

        initDataTable('.table-staff_logs', admin_url + 'staff_logs/table', [], [], ActivityLogServerParams, [1, 'desc']);
    });

// });



