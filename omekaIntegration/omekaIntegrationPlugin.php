<?php
/* ----------------------------------------------------------------------
 * omekaIntegrationPlugin.php : provides functionality to show a dedicatd menu for Libis Integration System
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2013 Whirl-i-Gig
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
 
	class omekaIntegrationPlugin extends BaseApplicationPlugin {
		# -------------------------------------------------------
		public function __construct($ps_plugin_path) {
			$this->description = _t('Shows a list of operations provided by the Libis INtegration system - LibIN');
			parent::__construct();
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true - the contentDeliveryMenu plugin always initializes ok
		 */
		public function checkStatus() {
			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => true
			);
		}

		# -------------------------------------------------------
		/**
		 * Insert activity menu
		 */
		public function hookRenderMenuBar($pa_menu_bar) {
			
			$menu_1 = _t('Omeka Integration');
			$va_menu_items[$menu_1] = array(
				'displayName' => _t($menu_1),
				"default" => array(
					'module' => 'omekaIntegration',
					'controller' => 'OmekaIntegration',
					'action' => 'Index/universe/'.$menu_1
				)
			);				
			
			$menu_2 = _t('Help');
			$va_menu_items[$menu_2] = array(
				'displayName' => _t($menu_2),
				"default" => array(
					'module' => 'omekaIntegration',
					'controller' => 'OmekaIntegration',
					'action' => 'Index/universe/'.$menu_2
				)
			);							


            $pa_menu_bar['libisin_menu'] = array(
				'displayName' => _t('LibisIN'),
				'navigation' => $va_menu_items
			);			

			return $pa_menu_bar;
		}
		# -------------------------------------------------------
		/**
		 * Get plugin user actions
		 */
		static public function getRoleActionList() {
            return array(
                'can_use_libisin_plugin' => array(
                    'label' => _t('Can use Libisin plugin'),
                    'description' => _t('User can use Libisin functionality.')
                )
            );
		}
		# -------------------------------------------------------

	}


?>