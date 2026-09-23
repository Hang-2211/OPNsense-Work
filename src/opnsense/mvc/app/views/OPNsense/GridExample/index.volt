<script>
    $(document).ready(function() {
        $("#{{formGridAddress['table_id']}}").UIBootgrid(
            {   search:'/api/gridexample/settings/searchItem/',
                get:'/api/gridexample/settings/getItem/',
                set:'/api/gridexample/settings/setItem/',
                add:'/api/gridexample/settings/addItem/',
                del:'/api/gridexample/settings/delItem/',
                toggle:'/api/gridexample/settings/toggleItem/'
            }
        );

        $("#reconfigureAct").SimpleActionButton();
    });
</script>

<div class="content-box">
    {{ partial('layout_partials/base_bootgrid_table', formGridAddress) }}
</div>
{{ partial('layout_partials/base_apply_button', {'data_endpoint': '/api/gridexample/service/reconfigure'}) }}
{{ partial("layout_partials/base_dialog",['fields':formDialogAddress,'id':formGridAddress['edit_dialog_id'],'label':lang._('Edit address')])}}