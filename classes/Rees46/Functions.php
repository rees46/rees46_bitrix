<?php
	
	namespace Rees46;
	
	use CUser;
	use Bitrix\Main\Engine\CurrentUser;
	use Rees46\Bitrix\Data;
	
	class Functions
	{
		const BASE_URL = 'https://api.rees46.ru';
		
		private static $jsIncluded = false;
		
		/**
		 * insert script tags for Rees46
		 */
		public static function includeJs()
		{
			global $USER;
			
			$shop_id = Options::getShopID();
			$stream  = Options::getStream();
			if (!$shop_id) {
				return;
			}
			
			$instantSearch = Options::getInstantSearchEmbedded();
			
			$currentUser = CUser::GetByID(CurrentUser::get()->getId());
			$currentUserData = $currentUser->Fetch();
			
			?>
			
			<script>
				(function(){
					if (window.REES46Initialized) return;
					window.REES46Initialized = true;
					
					<?php if ($instantSearch == 1): ?>
					let instantSearch = () => {
						document.querySelectorAll('form').forEach(form => {
							if (
									typeof form.action == "string"
									&& /(catalog|search)/.test(form.action.replace(document.location.origin, ''))
							) {
								let inputs = [].filter.call(form.elements, evt => {
									return /^input$/i.test(evt.tagName) && /q/.test(evt.name);
								});
								inputs.forEach(input => {
									if (!input.classList.contains('rees46-instant-search'))
									{
										input.classList.add('rees46-instant-search');
									}
								})
							}
						});
						if (typeof r46 != 'undefined') {
							r46('search_init', '.rees46-instant-search')
						}
					};
					if (document.readyState === 'complete') {
						instantSearch();
					} else {
						document.addEventListener('DOMContentLoaded', () => {
							instantSearch();
						});
					};
					<?php endif; ?>
					
					window.r46 = window.r46||function() {
						(window.r46.q=window.r46.q||[]).push(arguments)};
					let c = "//cdn.rees46.ru",
							v = "/v3.js",
							s = {
								link: [
									{
										href:c,
										rel: "dns-prefetch"
									},
									{
										href: c,
										rel: "preconnect"
									},
									{
										href: c+v,
										rel: "preload",
										as: "script"
									}
								],
								script: [
									{
										src:c+v,
										async:""
									}
								]
							};
					
					Object.keys(s).forEach(
							function(c) {
								s[c].forEach(
										function(d){
											var e = document.createElement(c),
													a;
											for (a in d) e.setAttribute(a,d[a]);
											document.head.appendChild(e)
										});
							}
					);
					r46('init', '<?= $shop_id ?>', '<?= $stream ?>');
					
					document.cookie = "rees46_segment="+localStorage.r46_segment+"; path=/;"
					
					<?php if ( CurrentUser::get()->getId() != null ): ?>
					let ud = {
						id:           '<?= $currentUserData['ID'] ?>',
						email:        '<?= $currentUserData['EMAIL'] ?>',
						first_name:   '<?= $currentUserData['NAME'] ?>',
						middle_name:  '<?= $currentUserData['SECOND_NAME'] ?>',
						last_name:    '<?= $currentUserData['LAST_NAME'] ?>',
						phone:        '<?= $currentUserData['PERSONAL_PHONE'] || $currentUserData['WORK_PHONE'] ;?>',
						gender:       '<?= $currentUserData['PERSONAL_GENDER'] ? mb_strtolower($currentUserData['PERSONAL_GENDER']) : null; ?>',
						birthday:     '<?= $currentUserData['PERSONAL_BIRTHDAY'] ? date('Y-m-d', strtotime($currentUserData['PERSONAL_BIRTHDAY'])) : null ;?>',
					};

					ud = Object.fromEntries(Object.entries(ud).filter(([key, value]) => value !== '' && value !== null));

					if (Object.keys(ud).length > 0) {
            			r46('profile', 'set', ud);
        			}
					<?php endif; ?>
				})();
			</script>
			
			<?php
			
			if (!empty($_GET['q'])) {
				self::jsPushData('search', $_GET['q']);
			}
			
			self::$jsIncluded = true;
		}
		
		/**
		 * push data via javascript (insert corresponding script tag)
		 *
		 * @param $action
		 * @param $data
		 * @param $order_id
		 */
		public static function jsPushData($action, $data)
		{
			$params = json_encode($data);
			if ($params === false && is_string($data)) {
				$params = json_encode(mb_convert_encoding($data, 'utf-8', 'cp-1251'));
			}
			echo "<script>typeof r46 != 'undefined' && r46('track', '{$action}', {$params});</script>";
		}
		
		public static function cookiePushData($action, $data)
		{
			$events_array = self::getR46Cookie();
			switch ($action) {
				case 'cart':
					$events_array['cart'] = $data;
					break;
				
				case 'purchase':
					$events_array['purchase'] = $data;
					break;
				
				default:
					return;
			}
			setcookie('r46_events_track', json_encode($events_array), strtotime('+1 hour'), '/');
		}
		
		public static function getR46Cookie()
		{
			$data = (!empty($_COOKIE['r46_events_track']) && !is_null(json_decode($_COOKIE['r46_events_track'], true))) ? json_decode($_COOKIE['r46_events_track'], true) : [];
			return $data;
		}
	}