<?php
/**
 * Support to generate metaboxes, field groups with fields and to save field's values.
 *
 * Date: 09.02.16
 * Time: 11:38
 *
 * @since 1.0.0
 */

class MwsMetaboxes {
	public static function hookGenerator() {
		$postType = get_post_type();
		mwshoplog(__METHOD__."($postType)", MWLL_DEBUG);
		if (!($postType == MWS_PRODUCT_SLUG || $postType == MWS_ORDER_SLUG))
			return;

		$groups = MwsTypesRegistration::getPropPostDefs($postType);
		if (!$groups)
			return;
		$meta = get_post_meta(get_the_ID());

		/** @var  $def MwsPropGroupDef */
		foreach ($groups->items as $def) {
			$rawValues = isset($meta[MWS_NAME.$def->name]) ? $meta[MWS_NAME.$def->name] : null;
			$values = (is_array($rawValues) && count($rawValues)==1) ? maybe_unserialize($rawValues[0]) : null;
			add_meta_box('mwsbox_' . $def->name, $def->caption, array(__CLASS__, 'renderMetabox'), null, 'advanced', 'default',
				array('def' => $def, 'values' => $values));
		}
	}

	/**
	 * Renders content of a metabox with field editors according to passed definition.
	 * @param $post WP_Post		Post instance
	 * @param $args array		Data of callback. Item [args] contains group definition of the metabox.
	 */
	public static function renderMetabox($post, $args) {
		/** @var MwsPropGroupDef $group */
		$group = $args['args']['def'];
		$meta = $args['args']['values'];
		mwshoplog(__METHOD__."($post->ID, $group->name)", MWLL_DEBUG);

		$values = isset($meta) && is_array($meta) ? $meta : array();
		echo '<div class="mws_metabox mws_metabox_'.$group->asInputId().'">';
		wp_nonce_field(__FILE__.$group->name, 'mws_nonce_'.$group->name);
		self::renderGroupEditors($group, $values);
		echo '</div>';
	}

	/**
	 * Render editors of custom fields according to passed group definition. Current values of fields are stored
	 * as item in array.
	 * @param $groupDef MwsPropGroupDef	Definition of a group.
	 * @param $values array|null    	Name-indexed array with current values of the group fields. Values should be
	 *   stored as items indexed by field's name.
	 */
	public static function renderGroupEditors($groupDef, $values) {
		/** @var MwsPropFieldDef $field */
		foreach ($groupDef->items as $name => $field) {
			$value = isset($values) && is_array($values) && isset($values[$field->name])
				? $values[$field->name] : '';
			MwsFieldEditor::renderEditor($field, $value);
		}

	}

	public static function savePost($postId) {
		$postType = get_post_type();
		mwshoplog(__METHOD__."($postType)", MWLL_DEBUG);
		if (!($postType == MWS_PRODUCT_SLUG || $postType == MWS_ORDER_SLUG))
			return false;

		// Check if the user has permissions to save data.
		if (!MWS()->canEdit())
			return false;

		// Check if it's not an autosave or revision.
		if (wp_is_post_autosave($postId) || wp_is_post_revision($postId))
			return false;

		$groups = MwsTypesRegistration::getPropPostDefs($postType);

		/** @var  $def MwsPropGroupDef */
		// Save all groups with valid nonce.
		$result = false;
		foreach ($groups->items as $def) {
			if (isset($_POST[MWS_NAME.$def->name]) && wp_verify_nonce($_POST['mws_nonce_'.$def->name], __FILE__.$def->name)) {
				update_post_meta($postId, MWS_NAME.$def->name, $_POST[MWS_NAME.$def->name]);
				$result |= true;
			}
		}
		return $result;
	}
}