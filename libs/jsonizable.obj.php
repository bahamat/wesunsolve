<?php
 /**
  * JSONIzable
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */

interface JSONizable
{
   public function toJSON();
   public function toJSONArray();
}

?>
