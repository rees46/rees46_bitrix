<?php
	
	namespace Rees46;
	
	use Bitrix\Main\Config\Option;
	
	class Options
	{
		
		public static function getShopID()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'shop_id');
		}
		
		public static function getShopSecret()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'shop_secret');
		}
		
		public static function getStream()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'stream');
		}
		
		public static function getUserGroups()
		{
			return explode(',', Option::get(\mk_rees46::MODULE_ID, 'user_groups'));
		}
		
		public static function getInstantSearchEmbedded()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'instant_search_embedded', \mk_rees46::INSTANT_SEARCH_DEFAULT) ? true : false;
		}
		
		public static function getProductInfoBlock()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'product_info_block');
		}
		
		public static function getOfferInfoBlock()
		{
			return Option::get(\mk_rees46::MODULE_ID, 'offer_info_block');
		}
		
		public static function getParam(string $name)
		{
			return Option::get(\mk_rees46::MODULE_ID, $name);
		}
		
		public static function getProperties()
		{
			return explode(',', Option::get(\mk_rees46::MODULE_ID, 'properties'));
		}
	}
