<?php
/**
 * Statistics Library
 *
 */
class Statistics {

/**
 * Stores the dataset.
 *
 * @var array
 */
	protected $_set = array();

/**
 * Stores the frequencies of each number in the dataset, indexed by frequency.
 *
 * @var array
 */
	protected $_freqs = array();

/**
 * Stores the frequencies of each number in the dataset, indexed by set value.
 *
 * @var array
 */
	protected $_freqsByNum = array();

/**
 * Stores the analysis data.
 *
 * @var array
 */
	protected $_setData = array();

/**
 * Magic Get.
 *
 * Returns the value of the named property from the set_data array.
 *
 * @ignore
 * @param string $name Name of property to look for
 * @return mixed
 */
	public function __get($name) {
		if ($name === 'average') {
			$name = 'mean';
		}

		if ($name === 'set_count') {
			$name = 'set_size';
		}

		if ($name === 'std_dev') {
			$name = 'std_deviation';
		}

		if ($name === 'pop_std_dev') {
			$name = 'pop_std_deviation';
		}

		if (array_key_exists($name, $this->_setData)) {
			$return = $this->_setData[$name];
		} else {
			$trace = debug_backtrace();
			trigger_error('Undefined property: ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
			$return = null;
		}

		return $return;
	}

/**
 * Magic Call.
 *
 * Calls special methods.
 *
 * @ignore
 * @param string $method Method name
 * @param array $arguments Arguments to method
 * @return mixed
 */
	public function __call($name, $args) {
		if (!method_exists($this, $name) && substr($name, 0, 3) === 'get') {
			$underscore = strpos($name, '_');
			if ($underscore !== false) {
				$var = substr($name, ($underscore + 1));
			} else {
				$matches = array();
				preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
				unset($matches[1][0]);
				foreach ($matches[1] as &$val) {
					$val = strtolower($val);
				}
				$var = implode('_', $matches[1]);
			}

			return $this->$var;
		}
	}

/**
 * Adds and analyzes a dataset.
 *
 * @param array $set The dataset
 */
	public function addSet($set) {
		$this->clearAll();
		sort($set, SORT_NUMERIC);
		$this->_set = array_values($set);
		$this->_setData['set_size'] = count($this->_set);
		$this->_calcDeviations()->_calcMean()->_calcMedian()->_setFreqs();
	}

/**
 * Adds and analyzes a dataset organized by frequency.
 * The passed set should be an array with the set value as the key and the number of occurences as the value.
 *
 * @param array $set The dataset
 */
	public function addMultiSet($set) {
		$newSet = array();
		foreach ($set as $num => $occur) {
			$newSet = array_merge($newSet, array_fill(0, $occur, $num));
		}
		$this->addSet($newSet);
	}

/**
 * Gets the frequency of a value in the dataset.
 *
 * @param numeric $value The value to search for
 * @param bool $pct If true returns the frequency as a percentage
 * @return numeric Frequency (either count or percentage)
 */
	public function getFrequency($value, $pct = false) {
		$count = 0;
		if (isset($this->_freqsByNum[$value])) {
			$count = $this->_freqsByNum[$value];
		}

		if ($pct) {
			$count = $count / $this->_setData['set_size'];
		}

		return $count;
	}

/**
 * Gets the most/least frequent value(s) in the dataset.
 * Returns an array with the frequency as the key and an array of set items with that frequeny as the value.
 *
 * @param integer $count The number of values to return
 * @param string $sort The direction to sort (used internally)
 * @return array Frequency array
 */
	public function getMostFrequent($count = 1, $sort = 'desc') {
		if ($sort === 'asc') {
			ksort($this->_freqs, SORT_NUMERIC);
		} else {
			krsort($this->_freqs, SORT_NUMERIC);
		}
		$length = 0;
		$tmp = 0;
		foreach ($this->_freqs as $nums) {
			$tmp += count($nums);
			$length += 1;
			if ($tmp >= $count) {
				break;
			}
		}

		return array_slice($this->_freqs, 0, $length, true);
	}

/**
 * Gets the least frequent value(s) in the dataset.
 * Returns an array with the frequency as the key and an array of set items with that frequeny as the value.
 *
 * @param integer $count The number of values to return
 * @return array Frequency array
 */
	public function getLeastFrequent($count = 1) {
		return $this->getMostFrequent($count, 'asc');
	}

/**
 * Gets the minimum value in the dataset.
 *
 * @return numeric The minimum value in dataset
 */
	public function getMin() {
		return min($this->_set);
	}

/**
 * Gets the maximum value in the dataset.
 *
 * @return numeric The maximum value in dataset
 */
	public function getMax() {
		return max($this->_set);
	}

/**
 * Sets the frequency arrays from the dataset.
 *
 * @return Statistics Returns self for method chaining
 */
	protected function _setFreqs() {
		$this->_freqsByNum = array_count_values($this->_set);

		foreach ($this->_freqsByNum as $value => $count) {
			if (!isset($this->_freqs[$count])) {
				$this->_freqs[$count] = array();
			}
			$this->_freqs[$count][] = $value;
		}

		ksort($this->_freqs);
		asort($this->_freqsByNum);

		return $this;
	}

/**
 * Calculates the standard deviations.
 *
 * @return Statistics Returns self for method chaining
 */
	protected function _calcDeviations() {
		$squares = 0;
		foreach ($this->_set as $item) {
			$squares += pow($item, 2);
		}

		$num = $squares - (pow(array_sum($this->_set), 2) / $this->_setData['set_size']);

		$this->_setData['variance'] = $num / ($this->_setData['set_size'] - 1);
		$this->_setData['pop_variance'] = $num / $this->_setData['set_size'];

		$this->_setData['std_deviation'] = pow($this->_setData['variance'], .5);
		$this->_setData['pop_std_deviation'] = pow($this->_setData['pop_variance'], .5);

		return $this;
	}

/**
 * Calculates the mean from the dataset.
 *
 * @return Statistics Returns self for method chaining
 */
	protected function _calcMean() {
		$this->_setData['mean'] = array_sum($this->_set) / $this->_setData['set_size'];

		return $this;
	}

/**
 * Calculates the median from the dataset.
 *
 * @return Statistics Returns self for method chaining
 */
	protected function _calcMedian() {
		if ($this->set_count % 2 === 0) {
			// Even number of elements
			$mid = ($this->_setData['set_size'] / 2) - 1;
			$this->_setData['median'] = (($this->_set[$mid] + $this->_set[($mid + 1)]) / 2);
		} else {
			// Odd number of elements
			$mid = floor($this->_setData['set_size'] / 2);
			$this->_setData['median'] = $this->_set[$mid];
		}

		return $this;
	}

/**
 * Clears all set data.
 *
 * @return Statistics Returns self for method chaining
 */
	protected function _clearAll() {
		$this->_set = array();
		$this->_setData = array();
		$this->_freqs = array();
		$this->_freqsByNum = array();

		return $this;
	}

}
