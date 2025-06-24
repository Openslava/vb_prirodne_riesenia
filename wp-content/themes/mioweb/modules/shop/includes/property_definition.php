<?php
/**
 * Classes and routines to handle extensible properties of custom post types.
 * A post-type can have several property groups, each group can contain
 * several property fields.
 *
 * There is mechanism to:
 *   - render groups as post's metaboxes containing fields UI
 *   - render fields UI for editing according to field type
 *   - save values of group of fields as post's custom fields
 *
 * Date: 08.02.16
 * Time: 16:23
 *
 * @since 1.0.0
 */

/**
 * Basic class for hierarchical structures of definitions.
 *
 * @class MwsPropDef
 */
class MwsPropDef {
	/** @var string Unique name of item. Must be usable as custom a field name for saving and as an array index. */
	public $name = '';
	/** @var string Title of item to distinguish it in UI. Localizable. */
	public $caption= '';
	/** @var array List of additional options.
	 *  placeholder, showAdminTableColumn
	 */
	public $options=null;
	/** @var MwsPropDef|null Pointer to parent object. */
	protected $parent=null;
	/** @var array Holder of all child definitions within this item. */
	public $items = array();
//	/** @var bool Whether field should be visible as showAdminTableColumn in admin table listings. */
//	public $showAdminTableColumn = false;

	function __construct($name, $caption='', $options = null, $items=null) {
		if(!$name)
			throw new MwsException('Name can not be empty');
		$this->name = $name;
		$this->caption = $caption ? $caption : $name;
		$this->updateOptions($options);
		if(!is_null($items)) {
			if (is_array($items)) {
				foreach ($items as $item)
					$this->addItem($item);
			} elseif ($items instanceof MwsPropDef)
				$this->addItem($items);
		}
	}

	/**
	 * Set several options at once.
	 * @param $options array	Name-indexed array of options to be set. Options are merges with current options.
	 * @throws MwsException
	 */
	function updateOptions($options) {
		if(empty($options))
			return;
		if(!is_array($options))
			throw new MwsException('Options must be an named-array');
		if(is_null($this->options))
			$this->options = array();
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * Get value of an option. If option's value is not set or is null then default value is returned.
	 * @param $optionName string			Name of requested option.
	 * @param mixed|array $defaultValue	Value that is used as a merge value to option value. Can be simple value
	 *   of value in array with same $optionName.
	 * @return mixed	Value of the option.
	 */
	public function getOption($optionName, $defaultValue = '') {
		if (is_array($this->options) && isset($this->options[$optionName]) && !is_null($this->options[$optionName]))
			return $this->options[$optionName];
		else {
			if (is_array($defaultValue) && isset($defaultValue[$optionName]))
				return $defaultValue[$optionName];
			else
				return $defaultValue;
		}
	}

	/** Returns options array. Makes sure array is returned even in case no options have been set. */
	public function getOptionsArr() {
		return (is_array($this->options))?$this->options:array();
	}

	/**
	 * Get value of an option and merge value with passed value.. If both option and merge values are set, then join
	 * them using $separator string.
	 * @param $optionName             string            Name of requested option.
	 * @param mixed|array $mergeValue Value that is used as a merge value to option value. Can be simple value of value in array with same $optionName.
	 * @param string $separator       String to separate
	 * @return mixed Value of option merged with $mergeValue.
	 */
	public function getOptionMergeStr($optionName, $mergeValue, $separator = ' ') {
		if (is_array($this->options) && isset($this->options[$optionName]) && !is_null($this->options[$optionName]))
			$val1 = $this->options[$optionName];
		if (is_array($mergeValue) && isset($mergeValue[$optionName]))
			$val2 = $mergeValue[$optionName];
		else
			$val2 = $mergeValue;
		if (isset($val1))
			if (isset($val2))
				return $val1.$separator.$val2;
			else
				return $val1;
		else
			if (isset($val2))
				return $val2;
			else
				return null;
	}

	public function setParent($parent) {
		$this->parent = $parent;
	}

	/**
	 * Add a new item definition into the list of subitems. If item with the same name exists, it will be overwritten by
	 * added item.
	 * @param $item MwsPropDef		Definition of an item to be added. It can be a descendant class of {@link MwsPropDef}.
	 */
	function addItem($item) {
		if ($item instanceof MwsPropDef) {
			$this->items[$item->name] = $item;
			$item->parent = $this;
		}
	}

	/**
	 * Find an item by its simple ID. Search can be restricted by class-name of the item.
	 * @param $id string			ID/Name of the search item.
	 * @param string $className		Optional class name.
	 * @return MwsPropDef			Returns found item or null if no item fulfills search conditions.
	 */
	function getItemByIdSimple($id, $className = '') {
		/**
		 * @var MwsPropDef $item
		 */
		foreach ($this->items as $name => $item) {
			if ($item->asInputIdSimple() == $id) {
				if (!$className || ($className && $item instanceof $className))
					return $item;
			}
			$subitem = $item->getItemByIdSimple($id, $className);
			if ($subitem)
				return $subitem;
		}
		return null;
	}

	public function asInputIdSimple() {
		return $this->name;
	}

	/**
	 * Returns name of item in format usable for ID of a HTML element as concatenation of parent elements.
	 * @return string 	Html element ID.
	 */
	public function asInputId() {
		$upper = isset($this->parent) && $this->parent instanceof MwsPropDef
			? $this->parent->asInputId() : '';
		return $upper . (!empty($upper) ? '_' : '') . $this->name;
	}

	/**
	 * Returns name of item in format usable for NAME of a HTML INPUT element. It concatenates parent objects as array
	 * indexes.
	 * @param $firstArray bool|false	If false then first item in line of parents will not use array brackets.
	 * @return string 	Html element ID.
	 */
	public function asInputName($firstArray = false) {
		$upper = isset($this->parent) && $this->parent instanceof MwsPropDef
			? $this->parent->asInputName($firstArray) : '';
		return $upper . (empty($upper)
			? ($firstArray ? "[$this->name]" : $this->name)
			: ("[$this->name]")
		);
	}

	/**
	 * Should this field appear as column in table view in administration?
	 * @return bool True if the field should appear as column in table listings.
	 */
	public function showAdminTableColumn() {
		return ($this->getOption('showAdminTableColumn'));
	}

	/**
	 * Get fields that should appear as columns in post-type's table view.
	 */
	public function getColumnFields() {
		$arr = $this->iterate(function($item, $args, &$result ) {
			/** @var MwsPropDef $item */
			if ($item instanceof MwsPropFieldDef
				&& $item->showAdminTableColumn()
			)
				$result[] = $item;
		}, array());
		return $arr;
	}

	/** Iterate across hierarchy of definitions.
	 * @param $func callable    Callable function with signature "func($item, $args, &$result)". Function should
	 *   set/update $result.
	 * @param $initial mixed	Initial value to which the iterator's result value is set at the begining of iteration.
	 * @param null|mixed $args	Arguments passed to $func().
	 * @return mixed			Updated value of $initial by successive calls of $func().
	 */
	public function iterate($func, $initial, $args=null) {
		if (! is_callable($func))
			return $initial;
		$result = $initial;

		//TODO Change to ommit recursion. Use que-based iteration.
		$this->iterateRef($func, $result, $args);

		return $result;
	}

	private function iterateRef($func, &$result, $args) {
		call_user_func_array($func, array($this, $args, &$result));
		/** @var MwsPropDef $item */
		foreach ($this->items as $name => $item) {
			$item->iterateRef($func, $result, $args);
		}
	}

	/** Iterates through hierarchy and returns a parent of class {@link MwsPropGroupDef}. */
	public function getParentPropGroup() {
		return $this->getParentOfClass('MwsPropGroupDef');
	}

	/** Iterates through hierarchy and returns a parent of class {@link MwsPropGroupDef}. */
	public function getParentOfClass($className) {
		if (!$className)
			return null;
		$item = $this;
		while($item && !($item instanceof $className))
			$item = $item->parent;
		return $item;
	}
}

/**
 * Definition for one post type.
 */
class MwsPropPostDef extends MwsPropDef {

	/**
	 * @param $postSlug string	Slug of the post type.
	 * @param string $caption
	 * @param null $options
	 * @param $items null|MwsPropDef|array
	 * @throws MwsException
	 */
	function __construct($postSlug, $caption='', $options=null, $items=null) {
		parent::__construct($postSlug, $caption, $options, $items);
		$this->name = '';
	}

	public function postSlug() {
		return $this->name;
	}
}

/**
 * Definition of one group of fields. It describes the name, caption and list of field definitions,
 * that belongs into the group. Each group is saved as one meta field.
 */
class MwsPropGroupDef extends MwsPropDef {

	/**
	 * @param $name
	 * @param string $caption
	 * @param null $options
	 * @param $items null|MwsPropDef|array
	 * @throws MwsException
	 */
	function __construct($name, $caption='', $options=null, $items=null) {
		parent::__construct($name, $caption, $options, $items);
	}
}

/**
 * Definition of one field. Fields are custom field types, grouped into {@link MwsPropGroupDef}.
 * Each field definition prescribes name (used for saving), type,
 */
class MwsPropFieldDef extends MwsPropDef {
	/** @var string Type name. Must be name of a supported field editor. */
	public $type = null;

	function __construct($name, $caption, $type, $options = null) {
		parent::__construct($name, $caption, $options);
		if(!$type)
			throw new MwsException('Type of field definition can not be empty.');
		$this->type = $type;
	}
}

class MwsPropCompoundDef extends MwsPropDef {

}
