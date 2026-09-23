<script>
    $( document ).ready(function() {
        mapDataToFormUI({'frm_GeneralSettings':"/api/helloworld/settings/get"}).done(function(data){
            // place actions to run after load
        });

        // link save button to API set action
        $("#saveAct").click(function(){
            saveFormToEndpoint("/api/helloworld/settings/set",'frm_GeneralSettings',function(){
                ajaxCall(url="/api/helloworld/service/reload", sendData={},callback=function(data,status) {
            $("#responseMsg").removeClass("hidden").html("Saved successfully!");
        });
    });
});
        // use a SimpleActionButton() to call /api/helloworld/service/test
        $("#testAct").SimpleActionButton({
            onAction: function(data) {
                $("#responseMsg").removeClass("hidden").html(data['message']);
            }
        });
    });
</script>

<div class="alert alert-info hidden" role="alert" id="responseMsg">

</div>

<div class="col-md-12">
    {{ partial("layout_partials/base_form",['fields':generalForm,'id':'frm_GeneralSettings'])}}
</div>

<div class="col-md-12">
    <button class="btn btn-primary" id="saveAct" type="button"><b>{{ lang._('Save') }}</b></button>
    <button class="btn btn-primary" id="testAct" data-endpoint="/api/helloworld/service/test" data-label="{{ lang._('Test Connection') }}"></button>
</div>