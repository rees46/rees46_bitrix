<?php

namespace Rees46\Includes;

use Rees46\Component\RecommendRenderer;
use Rees46\Component\YmlRenderer;

class Controller
{
	public static function route()
	{
		switch($_REQUEST['action']) {

			case 'recommend':
				RecommendRenderer::run();
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
