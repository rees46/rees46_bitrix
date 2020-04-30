<?php

namespace Rees46;

class Options
{

	public static function getShopID()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_id');
	}

	public static function getShopSecret()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_secret');
	}

	public static function getInstantSearchEmbedded()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'instant_search_embedded', \mk_rees46::INSTANT_SEARCH_DEFAULT) ? true : false;
	}

}
