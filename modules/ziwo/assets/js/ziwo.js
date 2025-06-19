function initiate_ziwo_call(leadid){
    if (confirm('Are you sure you want to initial call in Ziwo.io')){
        requestGetJSON(admin_url+'ziwo/make_call/'+leadid).done(function(response) {
            if (response.success == true) {
                alert_float('success', response.message)
            }else{
                alert_float('danger', response.message)
            }
        });
    }
}