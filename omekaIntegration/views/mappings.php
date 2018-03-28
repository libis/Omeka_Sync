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

    <link rel="STYLESHEET" type="text/css" href="https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/css/style.css">

</head>
<body>

<div id="recinfoArea"></div>
<div id="gridbox" style="width:700px;height:400px;resize: none;"></div>
<div id="pagingArea"></div>

<script type="text/javascript">
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath("https://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/dhtmlgrid/codebase/imgs/");          //the path to images required by grid
    mygrid.setHeader("#master_checkbox,Mapping File");//the headers of columns
    mygrid.attachHeader(",#text_filter");
    mygrid.setColTypes("ch,link");                //types of columns
    mygrid.setInitWidths("50,600");               //initial widths of columns
    mygrid.setColAlign("center,left");            //columns alignments
    mygrid.setColSorting(",str");
    mygrid.enableAutoWidth(true);

    mygrid.init();      //finishes initialization and renders the grid on the page
    mygrid.load("Mapping_Data", "json");

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
        var columnsInd = new Array(1,2);

        var selectedRows = mygrid.getCheckedRows(0);
        if(selectedRows){
            var rowsArray = selectedRows.split(",");
            for (var val of rowsArray) {
                if (!isNaN(val)){
                    sets.push(
                        {
                            'Mapping File': mygrid.cells(val, 1).getValue()
                        }
                    );
                }
            }
        }

        $.post("Delete_Mapping", {selected_files : sets}, function(data){
            mygrid.clearAll();
            mygrid.load("Mapping_Data", "json");
            $('#fileUploadMessage').empty();
            if (data.length>0){
                $("#integration_results").html(data);
            }
        })
    }


</script>

<form id="searchform" method="post">
        <div style="text-align: center">
            <input type="submit" value="Remove Selected Files" id="set_submit_button"  />
        </div>
</form>
<div id="integration_results" style="font-size: 12px"></div>
<br>
<br>

<div class="bgColor">
    <form id="uploadForm" action="Upload_Mapping" method="post">
        <div id="uploadFormLayer" style="width: 500px">
        <label>Upload a mapping file:</label><br/>
        <input name="file" id="fileupload" type="file" class="inputFile" accept=".csv" />
        <input type="submit" value="Submit" class="btnSubmit" />
        </div>
    </form>
</div>


<div id="fileUploadMessage"></div>

<script type="text/javascript">
    $(document).ready(function (e){
        $("#uploadForm").on('submit',(function(e){
            e.preventDefault();
            $('#fileUploadMessage').empty();
            $('#integration_results').empty();
            $.ajax({
                url: "Upload_Mapping",
                type: "POST",
                data:  new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
                success: function(data){

                    mygrid.clearAll();
                    mygrid.load("Mapping_Data", "json");

                    var parsedJson = $.parseJSON(data);
                    var successMsgs = parsedJson['success'];
                    var errorMsgs = parsedJson['errors'];
                    if(errorMsgs.length > 0){
                        $.each(parsedJson['errors'], function(i, msg) {
                            $('#fileUploadMessage').append('*' + msg+'<br/>');
                        });

                   }
                    else{
                        $("#fileupload").val('');
                        $.each(parsedJson['success'], function(i, msg) {
                            $('#fileUploadMessage').append('*' + msg+'<br/>');
                        });
                    }
                },
                error: function(request, status, error){
                    $("#fileUploadMessage").html("Http error:" +error + ", code:" + request.status);
                }
            });
        }));
    });
</script>
</body>
</html>
