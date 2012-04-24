<?php
/**
 * SRV4Pkg object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class SRV4Pkg extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $description = "";
  public $category = "";
  public $added = 0;
  public $updated = 0;

  /* Attributes for jt_ tables */
  public $arch = "";
  public $version = "";

  public function __toString() {
    return $this->name;
  }

  public function fromFile($fp) {

    if (empty($fp) || !file_exists($fp)) {
      return -1;
    }
    $mod = 0;

    $lines = file($fp);
   
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
	continue;

      $tmp = explode('=', $line, 2);
      if (count($tmp) != 2)
	continue;

      $n = $tmp[0];
      $v = $tmp[1];

      switch ($n) {
        case 'NAME':
        case 'DESC':
          if (strlen($v) > strlen($this->description)) {
	    $this->description = $v;
            $mod++;
          }
          break;
	case 'ARCH':
	  $this->arch = $v;
	  break;
	case 'VERSION':
	  $this->version = $v;
	  break;
	case 'CATEGORY':
	  $this->category = $v;
        default:
          break;
      }
    }
    return 0;
  }

  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "srv4pkg";
    $this->_nfotable = null;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "description" => SQL_PROPE,
                        "category" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "description" => "description",
                        "category" => "category",
                        "added" => "added",
                        "updated" => "updated"
                 );


  }

}
?>
