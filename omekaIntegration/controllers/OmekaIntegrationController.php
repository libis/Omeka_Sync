<?php
/* ----------------------------------------------------------------------
 * plugins/contentDeliveryMenu/controllers/ContentDeliveryController.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */

 	require_once(__CA_LIB_DIR__.'/core/TaskQueue.php');
 	require_once(__CA_LIB_DIR__.'/core/Configuration.php');
 	require_once(__CA_MODELS_DIR__.'/ca_lists.php');
 	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
 	require_once(__CA_MODELS_DIR__.'/ca_object_representations.php');
 	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
 	require_once(__CA_APP_DIR__.'/plugins/statisticsViewer/lib/statisticsSQLHandler.php');
 	

 	class OmekaIntegrationController extends ActionController {
 		# -------------------------------------------------------
  		protected $opo_config;		// plugin configuration file
 		protected $opa_dir_list;	// list of available import directories
 		protected $opa_regexes;		// list of available regular expression packages for extracting object idno's from filenames
 		protected $opa_regex_patterns;
 		protected $opa_locales;
 		protected $opa_statistics_xml_files;
 		protected $opa_statistics;
 		protected $opa_stat;
 		protected $opa_id;
 		protected $pa_parameters;
 		protected $allowed_universes;


 		# -------------------------------------------------------
 		# Constructor
 		# -------------------------------------------------------

 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 			global $allowed_universes;
 			
 			parent::__construct($po_request, $po_response, $pa_view_paths);

 		}

 		# -------------------------------------------------------
 		# Local functions
 		# -------------------------------------------------------

 		 		
 		# -------------------------------------------------------
 		# Functions to render views
 		# -------------------------------------------------------
 		public function Index($type="") {
 			$universe=$this->request->getParameter('universe', pString);
 			if(!isset($universe)) {
 				_p("No corresponding table (or stat universe) declared.");
 			} else {
				switch($universe) {
                    //Controle:view for omeka integration controls
					case 'Omeka Integration':
						$view_to_rende = 'omeka_integration_html.php';
						break;				
                    case 'Help':
						//Controle:view for guide
						$view_to_rende = 'help_html.php';
						break;

                    case 'Grid_Data':
                        //Control for acquiring grid data
                        $view_to_rende = 'grid_data.php';
                        break;

                    case 'Push_Data':
                        //Control for posting selected sets/records
                        $view_to_rende = 'push_data.php';
                        break;

                    //Help view is default view
					default:
						$view_to_rende = 'help_html.php';
						break;
				}
				//Render the selected view
				$this->render($view_to_rende);
 			}
 		}

 		# ------------------------------------------------------- 				
 	}
 ?>