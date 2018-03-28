<?php
    print _t("<h1>Libis Integration System - LibisIN</h1>\n");
    print _t("<h2>Omeka Integration</h2>\n");

    $root_dir_url = $_SERVER['HTTP_HOST'].$this->request->getBaseUrlPath();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


    <link rel="STYLESHEET" type="text/css" href="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/dhtmlxgrid.css">
    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/dhtmlxgrid.js"></script>
    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/datastore.js"></script>

    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/ext/dhtmlxgrid_filter.js"></script>

    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/ext/dhtmlxgrid_form.js"></script>
    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/dhtmlx.js"></script>

    <script src="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/dhtmlxcombo.js"></script>

    <script type="text/javascript">


    </script>

</head>
<body>

<div id="recinfoArea"></div>
<div id="gridbox" style="width:900px;height:400px;resize: none"></div>
<div id="pagingArea"></div>

<script type="text/javascript">

    /* get display bundle data */
    var displayBundles = $.ajax({
        type: "GET",
        url: 'Display_Data',
        async: false
    }).responseText;

    /* get mapping files information */
    var mappingFiles = $.ajax({
        type: "GET",
        url: 'Mapping_Files',
        async: false
    }).responseText;

    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath("https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/imgs/");          //the path to images required by grid
    mygrid.setHeader("#master_checkbox,set_code,Set ID, Number of Records, Record Type, User, Display Template, Mapping");//the headers of columns
    mygrid.attachHeader(",#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,,");
    mygrid.setColTypes("ch,ro,ro,ro,ro,ro,coro,coro");                //types of columns
    mygrid.setInitWidths("50,120,0,100,100,0,185,185");                //initial widths of columns
    mygrid.setColAlign("center,left,left,center,left,left,center,center");                //columns alignments
    mygrid.setColSorting(",str,str,int,str,str");
    mygrid.enableAutoWidth(true);
    mygrid.setColumnHidden(2,true);     //hide set_id column
    mygrid.setColumnHidden(5,true);     //hide user column
    mygrid.enableColumnAutoSize(true);

    /* assign display bundle data to combobox */
    var combobox = mygrid.getCombo(6);
    var jsonDisplay = $.parseJSON(displayBundles);
    for (var key in jsonDisplay) {
        combobox.put(key, key);
    }

    /* assign mapping files informaiton to combobox */
    var comboboxMappings = mygrid.getCombo(7);
    var jsonMappings = $.parseJSON(mappingFiles);
    for (var key in jsonMappings) {
        comboboxMappings.put(key, key);
    }

    mygrid.init();      //finishes initialization and renders the grid on the page
    mygrid.load("Grid_Data", "json");

    $(document).ready(function(){
        $("#integration_results").slideUp();
        $("#set_submit_button").click(function(e){
            e.preventDefault();
            ajax_search();
        });
    });

    function ajax_search(){
        $("#integration_results").show();
        var sets = [];
        var columnsInd = new Array(1,2,3,4,5,6,7);

        var selectedRows = mygrid.getCheckedRows(0);
        if(selectedRows){
            var rowsArray = selectedRows.split(",");
            for (var val of rowsArray) {
                if (!isNaN(val)){
                    sets.push(
                        {
                            'set_code': mygrid.cells(val, 1).getValue(),
                            'set_id': mygrid.cells(val, 2).getValue(),
                            'total_records': mygrid.cells(val, 3).getValue(),
                            'record_type': mygrid.cells(val, 4).getValue(),
                            'set_owner': mygrid.cells(val, 5).getValue(),
                            'display_bundle': mygrid.cells(val, 6).getValue(),
                            'mapping_file': mygrid.cells(val, 7).getValue()
                        }
                    );
                }
            }
        }


        $.post("Push_Data", {selected_sets : sets}, function(data){
            if (data.length>0){
                $("#integration_results").html(data);
            }
        })
    }


</script>

<form id="searchform" method="post">
        <div style="text-align: center">
            <input type="submit" value="Send to Omeka" id="set_submit_button"/>
        </div>
    </form>

    <div id="integration_results" style="font-size: 12px"></div>
</body>
</html>
