<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace BLL;

/**
 * CRUD operations intarefce for common usage
 */
interface DalInterface {
    public function getAll($params = array());
    public function update($params = array());
    public function delete ($params = array());
    public function insert($params = array());
}

