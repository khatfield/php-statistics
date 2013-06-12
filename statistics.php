<?php
/**
 * Statistics Library
 *
 * Basic statistical analysis of a dataset
 *
 * @license     MIT License
 * @author      Keith Hatfield
 */
class Statistics
{
    /**
     * Stores the dataset
     * @var array
     */
    private $set           = array();
    
    /**
     * Stores the frequencies of each number in the dataset, indexed by frequency
     * @var array
     */
    private $freqs         = array();
    
    /**
     * Stores the frequencies of each number in the dataset, indexed by set value
     * @var array
     */
    private $freqs_by_num  = array();

    /**
     * Stores the analysis data
     * @var array
     */
    private $set_data      = array();

    /**
     * Magic Get
     *
     * Returns the value of the named property from the set_data array
     *
     * @ignore
     * @param   string $name Name of property to look for
     * @return  mixed
     */
    public function __get($name)
    {
        if($name == 'average'){
            $name = 'mean';
        }

        if($name == 'set_count'){
            $name = 'set_size';
        }
        
        if($name == 'std_dev'){
            $name = 'std_deviation';
        }
        
        if($name == 'pop_std_dev'){
            $name = 'pop_std_deviation';
        }

        if(array_key_exists($name, $this->set_data)){
            $return  = $this->set_data[$name];
        } else {
            $trace  = debug_backtrace();
            trigger_error('Undefined property: ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
            $return = null;
        }

        return $return;
    }

    /**
     * Magic Call
     *
     * Calls special methods.
     *
     * @ignore
     * @param   string $method Method name
     * @param   array $arguments Arguments to method
     * @return  mixed
     */
    public function __call($name, $args)
    {
        if(!method_exists($this, $name) && substr($name, 0, 3) == 'get'){
            $underscore = strpos($name, '_');
            if($underscore !== false){
                $var = substr($name, ($underscore + 1));
            } else {
                $matches = array();
                preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
                unset($matches[1][0]);
                foreach($matches[1] as &$val){
                    $val = strtolower($val);
                }
                $var = implode('_', $matches[1]);
            }

            return $this->$var;
        }
    }

    /**
     * Adds and analyzes a dataset
     *
     * @param   array $set The dataset
     */
    public function addSet($set)
    {
        $this->clearAll();
        sort($set, SORT_NUMERIC);
        $this->set              = array_values($set);
        $this->set_data['set_size'] = count($this->set);
        $this->calcDeviations()
             ->calcMean()
             ->calcMedian()
             ->setFreqs();
    }

    /**
     * Adds and analyzes a dataset organized by frequency.
     * The passed set should be an array with the set value as the key and the number of occurences as the value
     *
     * @param   array $set The dataset
     */
    public function addMultiSet($set)
    {
        $new_set = array();
        foreach($set as $num => $occur){
            $new_set = array_merge($new_set, array_fill(0, $occur, $num));
        }
        $this->addSet($new_set);
    }

    /**
     * Gets the frequency of a value in the dataset
     *
     * @param   numeric $value The value to search for
     * @param   bool    $pct   If true returns the frequency as a percentage
     * @return  numeric Frequency (either count or percentage) 
     */ 
    public function getFrequency($value, $pct = false)
    {
        $count = 0;
        if(isset($this->freqs_by_num[$value])){
            $count = $this->freqs_by_num[$value];
        }
        
        if($pct){
            $count = $count / $this->set_data['set_size'];
        }
        
        return $count;
    }

    /**
     * Gets the most/least frequent value(s) in the dataset
     * Returns an array with the frequency as the key and an array of set items with that frequeny as the value
     *
     * @param   integer $count The number of values to return
     * @param   string  $sort  The direction to sort (used internally)
     * @return  array   Frequency array 
     */
    public function getMostFrequent($count = 1, $sort = 'desc')
    {
        if($sort == 'asc'){
            ksort($this->freqs, SORT_NUMERIC);
        } else {
            krsort($this->freqs, SORT_NUMERIC);
        }
        $length = 0;
        $tmp    = 0;
        foreach($this->freqs as $nums){
            $tmp += count($nums);
            $length++;
            if($tmp >= $count){
                break;
            }
        }
        
        return array_slice($this->freqs, 0, $length, true);
    }

    /**
     * Gets the least frequent value(s) in the dataset
     * Returns an array with the frequency as the key and an array of set items with that frequeny as the value
     *
     * @param   integer $count The number of values to return
     * @return  array   Frequency array 
     */
    public function getLeastFrequent($count = 1)
    {
        return $this->getMostFrequent($count, 'asc');
    }

    /**
     * Gets the minimum value in the dataset
     *
     * @return  numeric   The minimum value in dataset 
     */
    public function getMin()
    {
        return min($this->set);
    }
    
    /**
     * Gets the maximum value in the dataset
     *
     * @return  numeric   The maximum value in dataset 
     */
    public function getMax()
    {
        return max($this->set);
    }
    
    /**
     * Sets the frequency arrays from the dataset 
     *
     * @return  Statistics Returns self for method chaining 
     */
    private function setFreqs()
    {
        $old   = null;
        $count = 0;
        foreach($this->set as $value){
            if($value != $old){
                if(!is_null($old)){
                    if(!isset($this->freqs[$count])){
                        $this->freqs[$count]  = array();
                    }
                    $this->freqs[$count][]    = $old;
                    
                    $this->freqs_by_num[$old] = $count;
                }
                $old   = $value;
                $count = 0;
            }
            $count++;
        }

        if(!isset($this->freqs[$count])){
            $this->freqs[$count]  = array();
        }
        $this->freqs[$count][]    = $old;
        
        $this->freqs_by_num[$old] = $count;

        ksort($this->freqs);
        asort($this->freqs_by_num);

        return $this;
    }

    /**
     * Calculates the standard deviations 
     *
     * @return  Statistics Returns self for method chaining 
     */
    private function calcDeviations()
    {
        $squares = 0;
        foreach($this->set as $item){
            $squares += pow($item, 2);
        }
        
        $num  = $squares - (pow(array_sum($this->set),2)/$this->set_data['set_size']);

        $this->set_data['variance']     = $num / ($this->set_data['set_size'] - 1);
        $this->set_data['pop_variance'] = $num / $this->set_data['set_size'];

        $this->set_data['std_deviation']     = pow($this->set_data['variance'], .5);
        $this->set_data['pop_std_deviation'] = pow($this->set_data['pop_variance'], .5);

        return $this;
    }

    /**
     * Calculates the mean from the dataset 
     *
     * @return  Statistics Returns self for method chaining 
     */
    private function calcMean()
    {
        $this->set_data['mean'] = array_sum($this->set) / $this->set_data['set_size'];

        return $this;
    }

    /**
     * Calculates the median from the dataset 
     *
     * @return  Statistics Returns self for method chaining 
     */
    private function calcMedian()
    {
        if($this->set_count % 2 == 0){
            //even number of elements
            $mid = ($this->set_data['set_size'] / 2) - 1;
            $this->set_data['median'] = (($this->set[$mid] + $this->set[($mid + 1)]) / 2);
        } else {
            //odd number of elements
            $mid = floor($this->set_data['set_size'] / 2);
            $this->set_data['median'] = $this->set[$mid];
        }

        return $this;
    }

    /**
     * Clears all set data; 
     *
     * @return  Statistics Returns self for method chaining 
     */
    private function clearAll()
    {
        $this->set          = array();
        $this->set_data     = array();
        $this->freqs        = array();
        $this->freqs_by_num = array();
        
        return $this;
    }
}