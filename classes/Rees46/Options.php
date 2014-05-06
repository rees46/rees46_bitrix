<?php

namespace Rees46;

class Options
{
	public static function getRecommenderCSS()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'css', <<<'CSS'
.rees46-recommend {
}
.rees46-recommend .recommender-block-title {
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #dc6e00;
}
.rees46-recommend .recommended-items {}
.rees46-recommend .recommended-item {
    display: inline-block;
    width: 180px;
    height: 290px;
    margin-right: 15px;
    margin-bottom: 15px;
    overflow: hidden;
}
.rees46-recommend .recommended-item .recommended-item-photo {
    margin-bottom: 20px;
}
.rees46-recommend .recommended-item .recommended-item-photo img {
    max-width: 180px;
    max-height: 180px;
}
.rees46-recommend .recommended-item .recommended-item-title {
    margin-bottom: 20px;
    font-size: 16px;
    height: 38px;
    overflow: hidden;
}
.rees46-recommend .recommended-item .recommended-item-title a {
    color: #5580F0;
}
.rees46-recommend .recommended-item .recommended-item-price {
    color: #FF7500;
    font-weight: bold;
    font-size: 17px;
}
.rees46-recommend .recommended-item .recommended-item-action { display: none; }
.rees46-recommend .recommended-item .recommended-item-action a {}
CSS
		);
	}

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
