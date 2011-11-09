<?php
/**
 * OSRelease object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class OSRelease extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $major = "";
  public $u_date = "";
  public $u_number = -1;

  public $a_files = array();

  /* Files */
  function fetchFiles($all=1) {

    $this->a_files = array();
    $table = "`jt_patches_files`";
    $index = "`fileid`, `size`, `md5`, `sha1`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new File($t['fileid']);
        $k->fetchFromId();
        $k->size = $t['size'];
        $k->md5 = $t['md5'];
        $k->sha1 = $t['sha1'];
        array_push($this->a_files, $k);
      }
    }
    return 0;
  }

  function addFile($k) {

    $table = "`jt_patches_files`";
    $names = "`fileid`, `patchid`, `revision`";
    $values = "'$k->id', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_files, $k);
    return 0;
  }

  function setFileAttr($k, $size = 0, $md5 = "", $sha1 = "") {

    $file = null;
    foreach ($this->a_files as $ak => $v) {
      if (!strcmp($k, $v->name)) {
        $file = $v;
        break;
      }
    }
    if (!$file)
      return -1;

    $file->md5 = $md5;
    $file->sha1 = $sha1;
    $file->size = $size;

    $table = "jt_patches_files";
    $set = "`size`='".$file->size."', `md5`='".$file->md5."', `sha1`='".$file->sha1."'";
    $where = " WHERE `fileid`='".$file->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->update($table, $set, $where)) {
      return -1;
    }
    return 0;
  }

  function delFile($k) {

    $table = "`jt_patches_files`";
    $where = " WHERE `fileid`='".$k->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_files as $ak => $v) {
      if (!strcmp($k->name, $v->name)) {
        unset($this->a_files[$ak]);
      }
    }
    return 0;
  }

  function isFile($k) {
    foreach($this->a_files as $ko)
      if (!strcasecmp($ko->name, $k))
        return TRUE;
    return FALSE;
  }

 

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "osrelease";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "major" => SQL_PROPE,
                        "u_date" => SQL_PROPE,
                        "u_number" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "major" => "major",
                        "u_date" => "u_date",
                        "u_number" => "u_number"
                 );
  }

}
?>
