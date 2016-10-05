<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace Utill\Strip\Chain;

class StripChainer extends AbstractStripChainer implements \Services\Filter\FilterInterface {
    
    /**
     * value to be filtered
     * @var mixed any type
     */
    protected $filterValue;
    
    public function __construct($slimApp, $valueToFilter, $filters) {
        
        if(!$slimApp instanceof \Slim\Slim ) throw new \Exception ('no slim app found in StripChainer');
        $this->setSlimApp($slimApp);
        
        //if(!isset($valueToFilter)) throw new \Exception ('no value to filter in StripChainer class');
        $this->setFilterValue($valueToFilter);
        
        if(empty($filters)) throw new \Exception ('filter class name is empty in StripChainer class');
        
        foreach ($filters as $key =>$value) {
            $filter = $this->getSlimApp()->getServiceManager()->get($value);
            //print_r($filter);
            $this->setFilter(array($value => $filter));
        }
    }
    
    /**
     * excutes all filter operations
     * @throws \Exception
     */
    public function strip($baseKey = null) {
        $baseLocalKey = $baseKey;
        $this->rewind();
        $filterMessager = $this->slimApp->getServiceManager()->get('filterMessager');
        foreach ($this->chainer as $key => $value) {
          //print_r('-key-'.$key.'--');
          //print_r('-filter-'.$value.'--');
          $oldValue = $this->filterValue;
          if(method_exists($value, 'filter')) {
            $this->filterValue = $value->filter($this->filterValue);
            } else {
                throw new \Exception('invalid filter  method for \Zend\Filter\AbstractFilter');
            }
            $filterMessager->compareValue($this->filterValue, $oldValue, $key, $baseLocalKey);
            
        }
        //print_r($filterValidatorMessager->getFilterMessage());
        //print_r('--value filtered-->'.$this->filterValue);
    }

    /**
     * get filter class \Zend\Filter\AbstractFilter
     * @param mixed \Zend\Filter\AbstractFilter | null $name
     */
    public function getFilter($name = null) {
        if($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }
        return false;
    }
    
    /**
     * set \Zend\Filter\AbstractFilter type class
     * @param array $params
     * @return boolean
     */
    public function setFilter($params = null) {
        //print_r(key($params));
        $key = key($params);
        if(!$this->offsetExists($key)) {
            //print_r('--test--');
            $this->offsetSet($key, $params[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * set  value to be filtered
     * @param mixed $value
     */
    public function setFilterValue($value) {
        $this->filterValue = $value;
    }
    
    /**
     * get filtered value
     * @return mixed
     */
    public function getFilterValue() {
        return $this->filterValue;
    }

}