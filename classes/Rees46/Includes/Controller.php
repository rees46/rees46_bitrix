<?php

namespace Rees46\Includes;
use Rees46\Component\YmlRenderer;
use Rees46\Component\YmlRendererExtended;

class Controller
{
	public static function route()
	{
		switch($_REQUEST['action']) {

			case 'recommend':
                // Removed outdated recommenders, but kept the action so as not to break the site
				break;

			case 'yml':

				$yml = new YmlRenderer();
				$yml->render();
				break;
			
			case 'yml_extended':
				$yml = new YmlRendererExtended();
				$yml->render();
				break;


			default:
				die();
		}
	}

}
