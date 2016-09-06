<?php  



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Slim\Middleware;

 /**
  * Flash
  *
  * This is middleware for a Slim application that enables
  * Flash messaging between HTTP requests. This allows you
  * set Flash messages for the current request, for the next request,
  * or to retain messages from the previous request through to
  * the next request.
  *
  * @package    Slim
  * @author     Josh Lockhart
  * @since      1.6.0
  */
class EasyUIParamsControler extends \Slim\Middleware implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $messages;
    
    /**
     *
     * @var array
     */
    protected $easyUIParams;

    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct($settings = array())
    {
        
        
    }

    /**
     * Call
     */
    public function call()
    {
        //print_r('EasyUIParamsControler middleware call method------');
        //print_r($this->app->request->params());
        $this->next->call();
    }

    public function count($mode = 'COUNT_NORMAL') {
        
    }

    public function getIterator() {
        
    }

    public function offsetExists($offset) {
        
    }

    public function offsetGet($offset) {
        
    }

    public function offsetSet($offset,
            $value) {
        
    }

    public function offsetUnset($offset) {
        
    }

}

