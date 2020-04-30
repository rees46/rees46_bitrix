<?php

namespace Rees46\Includes;
use Rees46\Component\YmlRenderer;

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


			default:
				die();
		}
	}

}
