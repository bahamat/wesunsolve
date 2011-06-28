<?php
 /**
  * mysqlObj management
  * @author Gouverneur Thomas <tgo@ians.be>
  * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage mysql
  * @category classes
  * @filesource
  */

/**
 * Base class for all object that use mysql
 */
class mysqlObj
{
  protected $_my = array();
  protected $_myc = array();
  protected $_table = "";
  protected $_nfotable = "";
  protected $_datas = array();

  /* additionnal datas */
 
  /**
   *
   */
  public function dataCount() {

    if (!$this->_nfotable)
      return NULL;

    return count($this->_datas);
  }

  /**
   *
   */
  public function dataKeys() {

    if (!$this->_nfotable)
      return NULL;

    return array_keys($this->_datas);
  }

  /**
   *
   */
  public function data($name) {

    if (!$this->_nfotable)
      return NULL;

    if (isset($this->_datas[$name])) {
      return $this->_datas[$name];
    } else {
      return NULL;
    }
  }

  /**
   *
   */
  public function delData($name) {

    if (!$this->_nfotable)
      return NULL;

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;

    $my = mysqlCM::getInstance();

    $where = "";
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
      $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";
      $w++;
    }
    if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
    $where .= "`name`='$name'";
    
    return $my->delete($this->_nfotable, $where);
  }

  /**
   *
   */
  public function setData($name, $value) {

    if (!$this->_nfotable)
      return NULL;

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;

    $my = mysqlCM::getInstance();

    if (isset($this->_datas[$name])) {

      if ($value === $this->_datas[$name])
        return 0;

      /* Update */

      $where = "";
      $w=0;
      foreach ($ids as $id) {
        if ($id === FALSE)
          continue; /* no index in obj */

        if ($w) { $where .= " AND "; } else { $where .= "WHERE "; $w++; } 
        $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";
        
      }
      $where .= " AND `name`='".$name."'";
      $set = "`value`=".$my->quote($value).", `u`='".time()."'";
      if ($my->update($this->_nfotable, $set, $where)) {
        return -1;
      }

    } else {
      /* Insert */
      $w=3;
      $fields = "`name`, `value`, `u`";
      $values = "'$name',".$my->quote($value).",".time();
      foreach ($ids as $id) {
        if ($id === FALSE)
          continue; /* no index in obj */

        if ($w) { $fields .= " , "; $values .= " , "; }
        $fields .= "`".$id."`";
        $values .= $my->quote($this->{$this->_myc[$id]});

      }
      if ($my->insert($fields, $values, $this->_nfotable)) {
        return -1;
      }
    }
    $this->_datas[$name] = $value;
    return 0;
  }

  /**
   *
   */
  public function fetchData() {

    if (!$this->_nfotable)
      return NULL;

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;

    $my = mysqlCM::getInstance();

    $where = "";
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; $w++;}
      $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";

    }
    $fields = "`name`,`value`";

    if (($data = $my->select($fields, $this->_nfotable, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
	foreach ($data as $datum) {
          $name = $datum['name'];
          $value = $datum['value'];
          $this->_datas[$name] = $value;
	}
      } else return -1;
    }

 
  }

   /* mysql common functions */

  /**
   * Fetch object's index in the table
   * @return -1 on error
   */
  function fetchId()
  {
    $id = array_search(SQL_INDEX, $this->_my);
    if ($id === FALSE) 
      return -1; /* no index in obj */

    $where = "WHERE ";
    $i=0;
    foreach ($this->_my as $k => $v) {
      if ($v & SQL_WHERE)
      {
        if ($i && $i < count($this->_my)) $where .= " AND ";

        $where .= "`".$k."`='".$this->{$this->_myc[$k]}."'";
        $i++;
      }
    }
    
    $my = mysqlCM::getInstance();
    if (($data = $my->select("`".$id."`", $this->_table, $where)))
    {
      if ($my->getNR() == 1)
      {
        $this->{$this->_myc[$id]} = $data[0][$id];
      }
      else return -1;
    } else return -1;
  }

  /**
   * insert object in database
   * @return -1 on error
   */
  function insert()
  {
    $values = "";
    $names = "";
    $i=0;
    $my = mysqlCM::getInstance();
    foreach ($this->_my as $k => $v) {

      if (($v & SQL_INDEX) && (empty($this->{$this->_myc[$k]}) || $this->{$this->_myc[$k]} == -1)) {
        continue; /* skip index */
      }

      if ($i && $i < count($this->_my)) { 
        $names .= ","; $values .= ","; 
      }
      $names .= "`".$k."`";
      $values .= $my->quote($this->{$this->_myc[$k]});
      $i++;
    }

    $r = $my->insert($names, $values, $this->_table);
    $id = array_search(SQL_INDEX, $this->_my);
    $vid = $this->_myc[$id];

    if ($vid !== FALSE && (empty($this->{$vid}) || $this->{$vid} == -1)) 
      $this->{$vid} = $my->getNR();

    return $r;
  }

  /**
   * Update the object into database
   * @return -1 on error
   */
  function update()
  {
    //$id = array_search(SQL_INDEX, $this->_my);
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;
    $w=0;
    $my = mysqlCM::getInstance();
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if (!$w) {
        $where = "WHERE `".$id."`=".$my->quote($this->{$this->_myc[$id]});
        $w++;
      } else {
	$where .= " AND `".$id."`=".$my->quote($this->{$this->_myc[$id]});
      }
    }
    $set = "";
    $i = 0;
    foreach ($this->_my as $k => $v) {

      if ($v == SQL_INDEX) continue; /* skip index */

      if ($i && $i < count($this->_my)) { 
        $set .= ","; 
      }
      $set .= "`".$k."`=".$my->quote($this->{$this->_myc[$k]});
      $i++;
    }
    return $my->update($this->_table, $set, $where);

  }

  /**
   * Does the object exists in database ?
   * @return 0 = no, 1 = yes
   */
  function existsDb()
  {
    $where = " WHERE ";
    $i = 0;
    $my = mysqlCM::getInstance();
    foreach ($this->_my as $k => $v) {
      
      if ($v == SQL_INDEX) continue; /* skip index */
      if (!($v & SQL_EXIST)) continue; /* skip properties that shouldn't define unicity of object */
      if ($i && $i < count($this->_my)) $where .= " AND ";

      $where .= "`".$k."`=".$my->quote($this->{$this->_myc[$k]});
      $i++;
    }
    
    $id = array_search(SQL_INDEX, $this->_my);

    if ($id === FALSE)
    {
      $id = array_keys($this->_my); /* if no index, take the first field of the table */
      $id = $id[0];
    } 

    if (($data = $my->select("`".$id."`", $this->_table, $where)) == FALSE)
      return 0;
    else {
      if ($my->getNR()) {
        if ($this->{$this->_myc[$id]} != -1 && $data[0][$id] == $this->{$this->_myc[$id]}) { return 1; }
        if ($this->{$this->_myc[$id]} == -1) return 1;
      } else
        return 0;
    }
  }

  /**
   * Has the object changed ?
   * @return 0 = no; 1 = yes
   */
  function isChanged()
  {
    $where = " WHERE ";
    $i = 0;
    
    if (!$this->existsDb()) return 0;

    foreach ($this->_my as $k => $v) {
      
      if ($v == SQL_INDEX) continue; /* skip index */
      if (!($v & SQL_PROPE)) continue;
      if ($i && $i < count($this->_my)) $where .= " AND ";

      $where .= "`".$k."`='".$this->{$this->_myc[$k]}."'";
      $i++;
    }
    
    $id = array_search(SQL_INDEX, $this->_my);

    if ($id !== FALSE) {
      
      if ($this->{$this->_myc[$id]} != -1) $where .= " AND `".$id."`='".$this->{$this->_myc[$id]}."'";
      
      $my = mysqlCM::getInstance();
      if (($data = $my->select("`".$id."`", $this->_table, $where)) == FALSE)
        return 1;
      else {
        if ($my->getNR()) {
          return 0;
        } else
	  return 1;
      }
    }
   
  }

  /**
   * Fetch object with XXX
   * @return -1 on error
   */
  function fetchFromFields($on_fields)
  {
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($i && $i < count($this->_my)) $fields .= ",";

      $fields .= "`".$k."`";
      $i++;
    }    
     
    $my = mysqlCM::getInstance();
    $i=0;
    foreach ($on_fields as $field) {
      if ($i) 
        $where .= " AND ";
      else
        $where = "WHERE ";
    
      $where .= "`".$field."`=".$my->quote($this->{$this->_myc[$field]});
      $i++;
    }

    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }
  }


  /**
   * Fetch object with XXX
   * @return -1 on error
   */
  function fetchFromField($field)
  {
    $my = mysqlCM::getInstance();
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($i && $i < count($this->_my)) $fields .= ",";

      $fields .= "`".$k."`";
      $i++;
    }    

    $where = "WHERE `".$field."`=".$my->quote($this->{$this->_myc[$field]});

    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }
  }


  /**
   * Fetch object with INDEX
   * @return -1 on error
   */
  function fetchFromId()
  {
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($v != SQL_INDEX)
      {
        if ($i && $i < count($this->_my)) $fields .= ",";

        $fields .= "`".$k."`";
        $i++;
      }
    }    
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if (!$w) {
        $where = "WHERE `".$id."`='".$this->{$this->_myc[$id]}."'";
        $w++;
      } else {
        $where .= " AND `".$id."`='".$this->{$this->_myc[$id]}."'";
      }
    }

    //$where = "WHERE `".$id."`='".$this->{$this->_myc[$id]}."'";
    $my = mysqlCM::getInstance();
    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }

    return 0;
  }

  /**
   * delete object in db
   * @return -1 on error, 0 on success
   */
  function delete()
  {
    $where = "";
    $w = 0;

    $my = mysqlCM::getInstance();
    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;

    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
      $where .= "`".$id."`=".$my->quote($this->{$this->_myc[$id]});
      $w++;
    }
    return $my->delete($this->_table, $where);
  }

  public function copyToTable($table) {
    $oldtable = $this->_table;
    $this->_table = $table;
    $rc = $this->insert(1);
    $this->_table = $oldtable;
    return $rc;
  }

}
?>
