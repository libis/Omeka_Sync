<?php
    print _t("<h1>Libis Integration System - LibisIN</h1>\n");
    print _t("<h2>Omeka Integration</h2>\n");

    $root_dir_url = $_SERVER['HTTP_HOST'].$this->request->getBaseUrlPath();
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="stylesheet" type="text/css" media="screen" href="http://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/jqgrid/css/ui-lightness/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="http://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/jqgrid/css/ui.jqgrid.css" />

    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-size: 75%;
        }
    </style>

    <script src="http://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="http://<?php echo $root_dir_url; ?>/app/plugins/omekaIntegration/helpers/jqgrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(function () {
            jQuery("#list9").jqGrid({
                url: "Grid_Data",
                datatype: "xml",
				height: 250,
                colNames:['Set Name','Set ID', 'Number of Records', 'Record Type','User', 'Display Template'],
                colModel:[
                    {name:'set_name',index:'set_name', search:true, styp:'text'},
                    {name:'set_id',index:'set_id', width:92, search:true, stype:'text'},
                    {name:'number_of_records',index:'number_of_records'},
                    {name:'record_type',index:'record_type'},
                    {name:'user',index:'user'},
                    {name:'display_template',index:'display_template'}
                ],
                rowNum:10,
                rowList:[10,20,30],
                pager: '#pager9',
                recordpos: 'left',
                viewrecords: true,
                multiselect: true,
                autowidth:true,
                shrinkToFit:false,
                loadonce: true,
                caption: "Public Sets"
            });

            jQuery("#list9").jqGrid('navGrid','#pager9',{add:false,del:false,edit:false,position:'right'});

        });

        $(document).ready(function(){
            $("#integration_results").slideUp();
            $("#set_submit_button").click(function(e){
                e.preventDefault();
                ajax_search();
            });
        });

        function ajax_search(){
            $("#integration_results").show();
            var sel_id; sel_id = jQuery("#list9").jqGrid('getGridParam','selarrrow');
            var set_ids = [];
            var selected_data = [];
            for(var a=0;a < sel_id.length;a++)
            {
                selected_data.push(
                    {'set_id' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'set_id')},
                    {'set_name' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'set_name')},
                    {'record_type' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'record_type')},
                    {'display_template' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'display_template')}
                );

                set_ids.push(
                    {
                        'set_id' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'set_id'),
                        'set_name' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'set_name'),
                        'record_type' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'record_type'),
                        'display_template' : jQuery("#list9").jqGrid('getCell', sel_id[a], 'display_template')
                    }
                );

            }
            $.post("Push_Data", {selected_sets : set_ids.sort()}, function(data){
                if (data.length>0){
                    $("#integration_results").html(data);
                }
            })
        }

    </script>

</head>
<body>
    <table id="list9"></table>
    <div id="pager9"></div>
    <br/>

    <form id="searchform" method="post">
        <div style="text-align: center">
            <input type="submit" value="Send to Omeka" id="set_submit_button"  />
        </div>
    </form>

    <div id="integration_results" style="font-size: 12px"></div>

</body>
</html>
