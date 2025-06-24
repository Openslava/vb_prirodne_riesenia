<?php

/**
 * Basic support for synchronization of a custom post-type.
 * Date: 18.04.16
 * Time: 19:07
 */
class MwsSync {
	private $id;
	protected $_parent;

	/**
	 * MwsSync constructor.
	 * @param int $postId
	 * @param object $parent
	 */
	function __construct($postId, $parent) {
		$this->id = (int)$postId;
		$this->_parent = $parent;
	}

	/**
	 * Returns true, if instance should be synchronized into gateway. Ancestors can override this method to introduce
	 * custom behavior.
	 * @return bool
	 */
	public function shouldSync() {
		return true;
	}

	/**
	 * Return values that are synchronized into the gateway and should therefore reflect synchronization footprint.
	 * @return array Array of values that should be present in hash string.
	 */
	protected function doGetHashValuesArray() {
		return array();
	}

	/**
	 * Calculates hash of product values that are synchronized into gateways.
	 * @return string
	 */
	public function calcSyncHash() {
		$arr = $this->doGetHashValuesArray();
		if(is_array($arr))
			$s=implode('|',$arr);
		else
			$s=(string)$arr;
		return md5($s);
	}

	/**
	 * Get key name of option that contains synchronization data for a gate.
	 * @param string $gwId Optional ID of gateway. Usable when more simultaneous gateways are in game.
	 * @param string $gwSpecId Optional ID specific for the gateway. It is useful if multiple synchronization
	 *                         into gateway is possible.
	 * @return string Key name according to passed arguments.
	 */
	public function getOptionKey($gwId = '', $gwSpecId = '') {
		$keyName = MWS_OPTION_SYNC_KEY.(empty($gwId)?'':'_'.$gwId).(empty($gwSpecId)?'':'_'.$gwSpecId);
		return $keyName;
	}

	public function isSyncNeeded($gwId = '', $gwSpecId = '') {
		$shouldSync = $this->shouldSync();
		if(!$shouldSync) {
			return false;
		} else {
			$old = $this->getSyncData($gwId, $gwSpecId);
			if ($old['status'] === 'unsynced') {
				return true;
			} else {
				$hashNew = $this->calcSyncHash();
				$hashOld = isset($old['hash']) ? $old['hash'] : '';
				return ($hashNew != $hashOld);
			}
		}
	}

	/**
	 * Get array with status of synchronization.
	 * @param string $gwId Optional ID of gateway. Usable when more simultaneous gateways are in game.
	 * @param string $gwSpecId Optional ID specific for the gateway. It is useful if multiple synchronization
	 *                         into gateway is possible.
	 * @return array Status of synchronization with keys "status", "id", "when", "hash".
	 */
	public function getSyncData($gwId = '', $gwSpecId = '') {
		$meta = get_post_meta($this->id, $this->getOptionKey($gwId, $gwSpecId));
		if (isset($meta[0]))
			$meta = $meta[0];
		else
			$meta = array();

//		$syncName='sync'.(empty($gwId)?'':'_'.$gwId);
//		$data = isset($this->meta[$syncName])? $this->meta[$syncName] : array();
		$data = $meta;
		return array(
			'status'=> (isset($data['status']) ? $data['status'] : 'unsynced'),
			'id'=> (isset($data['id']) ? $data['id'] : null),
			'when'=> (isset($data['when']) ? $data['when'] : null),
			'hash'=> (isset($data['hash']) ? $data['hash'] : null),
		);
	}

	/**
	 * Save new synchronization status into DB.
	 * @param $syncData array Status of synchronization with keys "status", "id", "when", "hash".
	 * @param string $gwId Optional ID of gateway. Usable when more simultaneous gateways are in game.
	 * @param string $gwSpecId Optional ID specific for the gateway. It is useful if multiple synchronization
	 *                         into gateway is possible.
	 */
	public function setSyncData($syncData, $gwId = '', $gwSpecId = '') {
//		$syncName='sync'.(empty($gwId)?'':'_'.$gwId);
//		$data = isset($this->meta[$syncName])? $this->meta[$syncName] : array();
		$data=array();
		$data['status'] = (isset($syncData['status']) ? $syncData['status'] : 'unsynced');
		$data['id'] =  (isset($syncData['id']) ? $syncData['id'] : -1);
		$data['when'] = (isset($syncData['when']) ? $syncData['when'] : null);
		$data['hash'] = (isset($syncData['hash']) ? $syncData['hash'] : '');
//		$this->meta[$syncName] = $data;
		update_post_meta($this->id, $this->getOptionKey($gwId, $gwSpecId), $data);
	}

}