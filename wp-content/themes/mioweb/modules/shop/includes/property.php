<?php
/**
 * Class and routines for product properties.
 * User: kuba
 * Date: 26.08.16
 * Time: 12:31
 */


/**
 * Helper class to safely access product property attributes.
 *
 * @class MwsProperty
 *
 * @property int $id ID of post.
 * @property string $name Name/title of post.
 *
 * @property string|MwsPropertyType $type Type of the property.
 * @property string $unit Optional unit string appended to the value in print outs.
 * @property string $excerpt Optional text describing the property.
 *
 * @property array $values Array of {@link MwsPropertyValue} values.
 */
class MwsProperty {
	/** @var WP_Post Post object. */
	public $post;
	/** @var null|array Cached settings of product. */
	public $meta = null;

	/**
	 * Get instance by its ID.
	 * @param int $postId Unique ID pro a property. This can be call as "property ID" too.
	 * @return MwsProperty Existing instance or null
	 */
	public static function getById($postId) {
		$post = get_post($postId);
		if($post)
			try {
				return static::createNew($post);
			} catch (MwsException $e) {
				return null;
			}
		else
			return null;
	}

	/**
	 * Get all instances of {@link MwsProperty} as an array.
	 * @param array $queryArgs Optional argument for {@link WP_Query}. Default will filter only published instances.
	 * @return array List of <a href='psi_element://MwsProperty'>MwsProperty</a> instances.
	 * instances.
	 */
	public static function getAll($queryArgs = array('post_status' => 'publish')) {
		//TODO Caching needed!!!!
		$res = array();
		$args = array_merge(
			array(
				'post_type' => MWS_PROPERTY_SLUG,
			),
			$queryArgs,
			array('posts_per_page' => -1)
		);
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			/** @var WP_Post $post */
			foreach ($query->posts as $post) {
				try {
					$res[] = MwsProperty::createNew($post);
				} catch (Exception $e) {
				}
			}
		}
		return $res;
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 * @param $post WP_Post Instance of post with custom-post-type {@link MWS_PROPERTY_SLUG}.
	 * @return MwsProperty
	 * @throws MwsException If passed post is not of product post type.
	 */
	public static function createNew($post) {
		if(get_post_type($post) != MWS_PROPERTY_SLUG)
			throw new MwsException('Passed post is not of product property type.');
		//Is created already?
		$obj = MwsObjectCache::get($class = get_class(), $post->ID);
		if(!$obj) {
//			mwshoplog("{$class}[{$post->ID}] -- not in cache", MWLL_DEBUG, 'cache');
			$obj = new MwsProperty($post);
			MwsObjectCache::add($obj, $obj->id);
//			mwshoplog("{$class}[{$post->ID}] -- added to cache", MWLL_DEBUG, 'cache');
		} else {
//			mwshoplog("{$class}[{$post->ID}] -- cached instance used", MWLL_DEBUG, 'cache');
		}
		return $obj;
	}

	/**
	 * Creates new instance of class as a wrapper of WP_Post object.
	 * @param $post WP_Post
	 */
	function __construct($post) {
		$this->post = $post;
		$this->loadMeta();

		$this->type = (isset($this->meta['type'])
			? MwsPropertyType::checkedValue($this->meta['type'], MwsPropertyType::Text)
			: MwsPropertyType::Text);
		$this->unit = isset($this->meta['unit']) ? (string)$this->meta['unit'] : '';
	}

	function __get($name) {
		if(false) {
		} elseif ($name === 'id') {
			return $this->post->ID;
		} elseif ($name === 'name') {
			return $this->post->post_title;
		} elseif ($name === 'excerpt') {
			return $this->post->post_excerpt;
		} elseif ($name === 'values') {
			$res = array();
			if(isset($this->meta['values']) && is_array($this->meta['values'])) {
				foreach ($this->meta['values'] as $val) {
					if(!empty($val) && is_array($val) && isset($val['name'])) {
						$newValue = new MwsPropertyValue(
							$val['name'],
							isset($val['id']) ? sanitize_key($val['id']) : ''
						);
						$newValue->propertyDef = $this;
						$res[] = $newValue;
					}
				}
			}
			$this->__set($name, $res);
			return $res;
		}
		return null;
	}

	function __set($name, $value) {
		if(false) {
		} elseif(!empty($name)) {
			$this->$name = $value;
		}
	}

	/** Load metadata of the class. Uses cached data if present. */
	public function loadMeta() {
		if(is_null($this->meta)) {
			$meta = get_post_meta($this->id, MWS_PROPERTY_META_KEY);
			if (isset($meta[0]))
				$this->meta = $meta[0];
		}
		return $this->meta;
	}

	/**
	 * Print HTML input element to edit value of the property (not to define a property!).
	 * @param string $name        HTML attribute "name"
	 * @param string $id          HTML attribute "id"
	 * @param string $value       Currently assigned value into the editor. For TEXT it is the text. For ENUMERATION it is
	 *                            {@link MwsPropertyValue::id}.
	 * @param string $css         Optional CSS classes for the input element.
	 * @param string $placeholder Optional string when value is empty.
	 * @param string $hint
	 * @param bool $disabled      If the editor should be disabled, that is in read-only state.
	 * @param bool $enableEmpty   If true then selector allows an empty value to be selected.
	 * @return string HTML formatted input element to edit value of a property.
	 */
	public function htmlEditor($name, $id, $value, $css = '', $placeholder = '', $hint = '', $disabled = false, $enableEmpty = false) {
		$css = ' class="' . ($css ? $css.' ' : '') . 'mws_property_editor"';

		$res = '';
		switch($this->type) {
			case MwsPropertyType::Enumeration:
				$res .= '<select '
          . ($disabled ? ' disabled' : '')
					. $css
					. ' name="'.$name.'"'
					. ' id="'.$id.'"'
					. ($hint ? ' title="'.esc_attr($hint).'"' : '')
					. ' >';
				$hasSelection = false;

				if ($enableEmpty) {
					$selected = (empty($value)) ? ' selected="selected"' : '';
					if ($selected) {
						$hasSelection = true;
					}
					$res .= '<option value=""'
						. $selected
						. '>'
						. __('(bez hodnoty)', 'mwshop')
						. '</option>';
				}
				/** @var MwsPropertyValue $propValue */
				foreach ($this->values as $propValue) {
					$selected = ($propValue->id == $value) ? ' selected="selected"' : '';
					if($selected) {
						$hasSelection = true;
					}
					$res .= '<option value="'.$propValue->id.'"'
						. $selected
						. '>'
						. $propValue->name
						. '</option>';
				}
				if(!$hasSelection) {
					$res .= '<option value=""'
						. ' selected="selected" disabled="disabled"'
						. '>'
						. (empty($placeholder)
							? (empty($value)
								?  __('(vyberte)', 'mwshop')
								: sprintf(__('(vyberte — hodnota "%s" neexistuje)', 'mwshop'), $value)
							)
							: (empty($value)
								? $placeholder
								: $placeholder . ' — ' . sprintf(__('hodnota "%s" neexistuje', 'mwshop'), $value)
							)
						)
						. '</option>';
				}
				$res .= '</select>';
				break;
			default:
				$res .= '<input type="text"'
          . ($disabled ? ' disabled' : '')
					. $css
					. ' name="'.$name.'"'
					. ' id="'.$id.'"'
					. ' value="'. (isset($value) ? esc_attr($value) : '').'"'
					. (empty($placeholder) ? '' : ' placeholder="'. esc_attr($placeholder).'"')
					. ($hint ? ' title="'.esc_attr($hint).'"' : '')
					. ' />';
				break;
		}
		return $res;
	}

	/**
	 * Find corresponding value {@link MwsPropertyValue} instance.
	 * For ENUMERATION type the value is checked against defined values.
	 * For TEXT type value is simply created from the passed value.
	 * @param string $value Value to be found. For ENUMERATION this is value's id.
	 * @param bool $emptyAsNull If set to true, then empty value returns null.
	 * @return MwsPropertyValue|null On success value instance is return, null on failure.
	 */
	public function getValue($value, $emptyAsNull = false) {
		foreach ($this->values as $enumValue) {
			if ($enumValue->id == $value)
				return $enumValue;
		}
		switch ($this->type) {
			case MwsPropertyType::Enumeration:
				break;
			default:
				// Create automagically new value.
				$isEmpty = empty($value) && $value !== '0';
				if($emptyAsNull && $isEmpty) {
					return null;
				} else {
					$newValue = new MwsPropertyValue($value);
					$newValue->propertyDef = $this;
					$this->values[] = $newValue;
					return $newValue;
				}
		}
		return null;
	}

}

/**
 * Class MwsPropertyValue
 *
 * @property string $name Stored value as a text
 * @property string $id Id of the value (this is stored into DB). Basically sanitized $name.
 * @property MwsProperty $propertyDef Definition of property where the value belongs to.
 */
class MwsPropertyValue {
	public function __construct($name, $id = '') {
		$this->name = $name;
		$this->id = empty($id) ? sanitize_title($name, '', 'save') : $id;
	}

	/**
	 * Form serialized form of instance as array.
	 * @return array
	 */
	public function serialize() {
		$res = array(
			'property' => $this->propertyDef->id,
		);
		switch($this->propertyDef->type) {
			case MwsPropertyType::Enumeration:
				$res['valueId'] = $this->id;
				break;
			default:
				$res['value'] = $this->name;
				break;
		}
		return $res;
	}

	/**
	 * Get serialized form of instance.
	 * @param array $serialized Array with serialized value.
	 * @return MwsPropertyValue|null
	 */
	public static function unserialize($serialized) {
		$res = null;
		if(is_array($serialized)) {
			if(isset($serialized['property'])) {
				$property = MwsProperty::getById($serialized['property']);
				if($property) {
					switch($property->type) {
						case MwsPropertyType::Enumeration:
							if(isset($serialized['valueId']))
								$res = $property->getValue($serialized['valueId']);
							break;
						default:
							if(isset($serialized['value']))
								$res = $property->getValue($serialized['value']);
					}
				}
			}
		}
		return $res;
	}

	/**
	 * Serialize list of property values.
	 * @param array $values Array of {@link MwsPropertyValue} instances.
	 * @return array Simple array that can be directly used for PHP serialization.
	 */
	public static function serializeArray($values) {
		$res = array();
		if(is_array($values))
			/** @var MwsPropertyValue $value */
			foreach ($values as $value) {
				$res[] = $value->serialize();
			}
		return $res;
	}

	/**
	 * Load previously serialized values.
	 * @param array $serializedValues Array of serialized {@link MwsPropertyValue} instances.
	 * @return array Array of {@link MwsPropertyValue} instances.
	 */
	public static function unserializeArray($serializedValues) {
		$res = array();
		foreach ($serializedValues as $serialized) {
			$value = MwsPropertyValue::unserialize($serialized);
			if($value)
				$res[] = $value;
		}
			return $res;
	}

	function __set($name, $value) {
		if(false) {
		} elseif(!empty($name)) {
			$this->$name = $value;
		}
	}


}
