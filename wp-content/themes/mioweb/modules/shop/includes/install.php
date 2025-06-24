<?php
/**
 * Routines for package installation, uninstallation, upgrage.
 *
 * Date: 04.02.16
 * Time: 13:29
 *
 * @since 1.0.0
 *
 */

/**
 * Handles installation and uninstallation procedures.
 * @class MwsInstall
 */
class MwsInstall {
	/**
	 * Perform installation or upgrade of MioShop.
	 * After successful installation global options are set, necessary tables are created.
	 */
	public static function autoInstall() {
		mwshoplog(__METHOD__, MWLL_DEBUG, 'install');
		//Make sure custom types are registered.
		MwsTypesRegistration::registerAll();

		$versionInstalled = get_option('mwshop_version', null);
		$versionFiles = MioShop::version;

		if (is_null($versionInstalled)) {
			// Not installed.
			mwshoplog('MioShop not installed. Installing...', MWLL_INFO, 'install');
			update_option('mwshop_version', MioShop::version);
			update_option('mwshop_first_install', time());
			mwshoplog('MioShop installed as version ' . MioShop::version, MWLL_INFO, 'install');
		} elseif (version_compare($versionInstalled, $versionFiles, '<')) {
			// Upgrade needed
			mwshoplog('File version of MioShop is '. MioShop::version .', older version ' . $versionInstalled
				. ' of configuration found. Upgrade is needed.', MWLL_INFO, 'install');

			/**
			 * Array of upgrade functions. Functions are executed sequentially from the $versionInstalled up to the current version.
			 * As value use anonymous functions with code to be executed when upgradin from $key version.
			 */
			$upgradeFunc = array(
				'1.0.2' =>
					function() {
						// Permalink structure changed - hooked bellow eshop home.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.3' =>
					function() {
						// Permalink structure modified - separate URIs.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.6' =>
					function() {
						// Permalink structure settings changed. Moved to another option.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
        '1.0.9' =>
					function() {
						// Content on end of order added to visual editor.
						$eshop_set=get_option(MWS_OPTION_SHOP_SETTING);

						if($eshop_set['order_text']) {

							global $vePage;

							$new_post = array(
								'post_title' => __('Text na konci objednávky','mw_shop'),
								'post_status' => 'publish',
								'post_type'=>'weditor',
								'post_author' => 1,
							);

							$content = array(
								0 => array(
									'class' => '',
									'style'=>array(
										'background_color'=>array('color1'=>'#fff','color2'=>'','transparency'=>'100'),
										'font' => array()
									),
									'content' => array(
										0 => array(
											'type' => 'col-one',
											'class' => '',
											'content' => array(
												0 => Array(
													'type' => 'text',
													'content' => $eshop_set['order_text'],
													'style' => array(
														'font' => array(
															'font-size' => '',
															'font-family' => '',
															'weight' => '',
															'line-height' => '',
															'color' => '',
														),
														'li' => '',
													),
													'config' => Array('margin_top' => 0, 'margin_bottom' => 20)
												)
											)
										)
									)
								)
							);

							$post_id=$vePage->save_new_window_post($new_post, '', $vePage->code($content), 'weditor');

							$eshop_set['thanks_content']=$post_id;
							update_option(MWS_OPTION_SHOP_SETTING,$eshop_set);
						}
					},
				'1.0.10' =>
					function() {
						// Set all product types to SINGLE.
						mwshoplog('Setting all old products as SINGLE types', MWLL_INFO, 'install');
						$qry = new WP_Query(array(
							'post_type' => MWS_PRODUCT_SLUG,
							'posts_per_page' => -1,
						));
						if($qry->have_posts()) {
							$updatedCnt = 0;
							foreach ($qry->posts as $post) {
								$res = add_post_meta($post->ID, MWS_PRODUCT_META_KEY_STRUCTURE, MwsProductStructureType::Single);
								if($res)
									$updatedCnt++;
							}
							mwshoplog($updatedCnt . ' product(s) defined in eshop version prior 1.0.8 set as SINGLE product type.',
								MWLL_INFO, 'install');
						}
					},
				'1.0.11' =>
					function() {
							// Permalink structure settings changed. Moved to another option.
							mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
							flush_rewrite_rules(true);
					},
			);
			$saveUpgraded = function($from, $to) {
				update_option('mwshop_version', $to);
				update_option('mwshop_updated', time());
				mwshoplog('MioShop was upgraded from version '. $from .' to version ' . $to, MWLL_INFO, 'install');
			};
			$execute = false;
			$idx = 0;
			$versions = array_keys($upgradeFunc);
			$errorOccured = false;
			foreach ($upgradeFunc as $key=>$val) {
				$idx++;
				$nextVersion = ($idx >= count($upgradeFunc))
					? $nextVersion = MioShop::version
					: $nextVersion = $versions[$idx];
				$execute = $execute || version_compare($key, $versionInstalled, '>=');
				if($execute) {
					try {

						if(is_callable($val)) {
							mwshoplog("Running inline upgrade script from version $key up", MWLL_INFO, 'install');
							$val();
						} elseif(is_string($val) && function_exists($val)) {
							mwshoplog("Running external upgrade script from version $key up", MWLL_INFO, 'install');
							call_user_func($val);
						} else {
							mwshoplog("No upgrade actions for version $key", MWLL_INFO, 'install');
						}

						$saveUpgraded($key, $nextVersion);
					} catch (Exception $e) {
						$errorOccured = true;
						mwshoplog("Upgrade of version \"$versionInstalled\" failed. ". $e->getMessage(), MWLL_ERROR, 'install');
						break;
					}
				}
			}
			//All passed without errors? Works for case when no update scripts were necessary.
			if(!$errorOccured) {
				$versionInstalled = get_option('mwshop_version', null);
				if(version_compare($versionInstalled, $versionFiles, '<')) {
					$saveUpgraded($versionInstalled, $versionFiles);
				}
			}
		}
	}

	/** Perform uninstallation of MioShop */
	public static function uninstall() { }

}
