<?php

namespace FrontendModule\EshopModule;

/**
 * This presenter - RESTful API for eshop module.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class RestApiPresenter extends BasePresenter{
	
	public function startup() {
		parent::startup();
	}
	
	/**
	 * JSON list with links and version of the API
	 */
	public function renderDefault(){}
	
	
	
}