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

	public static function getImageWidth()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_width', \mk_rees46::IMAGE_WIDTH_DEFAULT);
	}

	public static function getImageHeight()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_height', \mk_rees46::IMAGE_HEIGHT_DEFAULT);
	}

	public static function getRecommendCount()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_count', \mk_rees46::RECOMMEND_COUNT_DEFAULT);
	}

	public static function getRecommendNonAvailable()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_nonavailable', false) ? true : false;
	}
}
