<?php

require_once("../libs/config.inc.php");
require_once("../libs/autoload.lib.php");
require_once("../libs/functions.lib.php");

class sunsolve extends module {

	public $title = "SunSolve mod";
	public $author = "wildcat";
	public $version = "0.1";

	public $channels = array("#sunsolve", "#opensolaris-fr", "#solaris");
        public $admchan = "#sunsolve";
        public $first = 0;

        public function init() {
		$this->timerClass->addTimer("sunsolve_do_np", $this, "do_np", "", 60, false);
		$this->timerClass->addTimer("sunsolve_do_msg", $this, "do_msg", "", 10, false);
	}

        public function destroy()
        {
                $this->timerClass->removeTimer("sunsolve_do_np");
                $this->timerClass->removeTimer("sunsolve_do_msg");
        }


	public function do_msg() {
		if (!$this->first) { $this->first++; return true; }
		$m = mysqlCM::getInstance();
		if ($m->connect()) {
			$this->ircClass->privMsg($this->admchan, "[ERROR] Unable to connect to MySQL server...");
			return true;
		}
		  $msgs = array();
		  $table = "`irc_log`";
		  $index = "`id`";
		  $where = " WHERE `done`='0' ORDER BY `added` ASC LIMIT 0,5";

		  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
		  {
		    foreach($idx as $t) {
		      $g = new IrcMsg($t['id']);
		      $g->fetchFromId();
  		      $g->done = 1;
		      $g->update();
		      array_push($msgs, $g);
		    }
		  }
		foreach($msgs as $msg) {
			$this->ircClass->privMsg($this->admchan, $msg->msg);
		}
	        $m->disconnect();
		return true;
	}

	public function do_np() {
		if (!$this->first) { $this->first++; return true; }
		$m = mysqlCM::getInstance();
		if ($m->connect()) {
			return true;
		}
		  $patches = array();
		  $table = "`irc_npatchs`";
		  $index = "`p`, `r`";
		  $where = " LIMIT 0,5";

		  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
		  {
		    foreach($idx as $t) {
		      $g = new Patch($t['p'], $t['r']);
		      $g->fetchFromId();
		      $n = new Ircnp();
		      $n->r = $t['r'];
		      $n->p = $t['p'];
  		      if (empty($g->synopsis)) 
                        continue;
		      $n->delete();
		      array_push($patches, $g);
		    }
		  }
		foreach($patches as $p) {
			foreach($this->channels as $channel) {
				$this->ircClass->privMsg($channel, $this->announce_patch($p, 1));
			}
		}
	        $m->disconnect();
		return true;
	}

        private function announce_patch($p, $new=0) {
         
	  $msg = "";
          if ($new) $msg = "[NEW]";
          $msg .= "[".$p->name()."] ";
 	  if ($p->pca_sec) {
 	    $msg .= "[S]";
	  }
 	  if ($p->pca_rec) {
 	    $msg .= "[R]";
	  }
 	  if ($p->pca_bad) {
 	    $msg .= "[W]";
	  }
	  if ($p->o_latest) {
 	    $msg .= "[> ".$p->o_latest->name()."]";
	  }
	  if ($p->releasedate) {
	    $msg .= " ".date('d/m/Y', $p->releasedate);
	  }
  	  if (!empty($p->synopsis)) {
	    $msg .= " - ".$p->synopsis;
	  }
	  return $msg;
	}

	public function priv_help($line, $args)
	{
                $channel = $line['to'];
                if ($channel == $this->ircClass->getNick())
                {
                        return;
                }

                if ($args['nargs'] == 0)
                {
			$this->ircClass->privMsg($channel, "Commands available:");
			$this->ircClass->privMsg($channel, "  !status - Database status");
			$this->ircClass->privMsg($channel, "  !last5 - Last 5 released patches");
			$this->ircClass->privMsg($channel, "  !last5s - Last 5 released security patches");
			$this->ircClass->privMsg($channel, "  !patch - Query database about patch");
			$this->ircClass->privMsg($channel, "  !bug - Query database about bug");
			$this->ircClass->privMsg($channel, "  -- join #sunsolve for comments and suggestions...");
		}
		return;
	}

        public function priv_status($line, $args)
	{
		$channel = $line['to'];

                if ($channel == $this->ircClass->getNick())
                {
                        return;
                }

                if ($args['nargs'] == 0)
                {
                        $m = mysqlCM::getInstance();
                        if ($m->connect()) {
                                $this->ircClass->privMsg($channel, "Unable to connect to MySQL server...");
                                return;
                        }
			$nbpatches = MysqlCM::getInstance()->count("patches");
			$obso = MysqlCM::getInstance()->count("patches", "WHERE `status`='OBSOLETE'");
			$unresolved = MysqlCM::getInstance()->count("patches", "WHERE `synopsis`=''");
			$bugids = MysqlCM::getInstance()->count("bugids");
			$lastw = MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*7)));
			$lastm = MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*31))); 
			$lasty = MysqlCM::getInstance()->count("patches", "WHERE `releasedate` > ".(time() - (3600*24*31*12)));
			$msg = "[Patches] $nbpatches Released / $obso Obsoleted / $unresolved Unresolved";
			$this->ircClass->privMsg($channel, $msg);
			$msg = "[Bugs] $bugids bugs registered";
			$this->ircClass->privMsg($channel, $msg);
			$msg = "[Stats] $lastw Patches last week | $lastm Patches last month | $lasty Patches last year";
			$this->ircClass->privMsg($channel, $msg);
			$m->disconnect();

		} else {
			$this->ircClass->privMsg($channel, "!status without args");
			return;
		}
		return;
	}

        public function priv_last5s($line, $args)
        {
                $channel = $line['to'];

                if ($channel == $this->ircClass->getNick())
                {
                        return;
                }

                if ($args['nargs'] != 0)
                {
                        $msg = "Commands don't take arguments";
                        $this->ircClass->privMsg($channel, $msg);
                }
                else
                {
                        $m = mysqlCM::getInstance();
                        if ($m->connect()) {
                                $this->ircClass->privMsg($channel, "Unable to connect to MySQL server...");
                                return;
                        }
			  $patches = array();
			  $table = "`patches`";
			  $index = "`patch`, `revision`";
			  $where = " WHERE `pca_sec`='1' AND `releasedate`!='' ORDER BY `releasedate` DESC LIMIT 0,5";

			  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
			  {
			    foreach($idx as $t) {
			      $g = new Patch($t['patch'], $t['revision']);
			      $g->fetchFromId();
			      array_push($patches, $g);
			    }
			  }
			foreach($patches as $p) {
                          $p->o_latest = Patch::pLatest($p->patch);
                          if ($p->o_latest &&
                              $p->o_latest->patch == $p->patch &&
                              $p->o_latest->revision == $p->revision) {
                                    $p->o_latest = false;
                          }

				$this->ircClass->privMsg($channel, $this->announce_patch($p));
			}
			$m->disconnect();
		}
	}


        public function priv_last5($line, $args)
        {
                $channel = $line['to'];

                if ($channel == $this->ircClass->getNick())
                {
                        return;
                }

                if ($args['nargs'] != 0)
                {
                        $msg = "Commands don't take arguments";
                        $this->ircClass->privMsg($channel, $msg);
                }
                else
                {
                        $m = mysqlCM::getInstance();
                        if ($m->connect()) {
                                $this->ircClass->privMsg($channel, "Unable to connect to MySQL server...");
                                return;
                        }
			  $patches = array();
			  $table = "`patches`";
			  $index = "`patch`, `revision`";
			  $where = " WHERE `releasedate`!='' ORDER BY `releasedate` DESC LIMIT 0,5";

			  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
			  {
			    foreach($idx as $t) {
			      $g = new Patch($t['patch'], $t['revision']);
			      $g->fetchFromId();
			      array_push($patches, $g);
			    }
			  }
			foreach($patches as $p) {
			  $p->o_latest = Patch::pLatest($p->patch);
                          if ($p->o_latest &&
                              $p->o_latest->patch == $p->patch &&
                              $p->o_latest->revision == $p->revision) {
                                    $p->o_latest = false;
                          }

                                $this->ircClass->privMsg($channel, $this->announce_patch($p));
			}
			$m->disconnect();
		}
	}

	public function priv_patch($line, $args)
	{
		$channel = $line['to'];

		if ($channel == $this->ircClass->getNick())
		{
			return;
		}

		if ($args['nargs'] == 0)
		{
			$msg = "!patch <id>[-<rev>]";
			$this->ircClass->privMsg($channel, $msg);
		}
		else
		{
			$m = mysqlCM::getInstance();
			if ($m->connect()) {
				$this->ircClass->privMsg($channel, "Unable to connect to MySQL server...");
				$m->disconnect();
				return;
			}
			$pid = trim($args['arg1']);
			 if (!preg_match("/[0-9]{6}-[0-9]{2}/", $pid) && !preg_match("/[0-9]{6}/", $pid)) {
				$this->ircClass->privMsg($channel, "Malformed patch id");
				$m->disconnect();
				return;
			 }

      			$pid = explode("-", $pid);
    			if (count($pid) == 2) {
				$p = new Patch($pid[0],$pid[1]);
				$rev = 1;
			} else if (count($pid) == 1) {
				$p = new Patch($pid[0]);
  			} else {
				$msg = "!patch <id>[-<rev>]";
				$this->ircClass->privMsg($channel, $msg);
				$m->disconnect();
				return;
			}

			if ($rev) {
			  $p->o_latest = Patch::pLatest($p->patch);
			    if ($p->o_latest &&
				$p->o_latest->patch == $p->patch &&
				$p->o_latest->revision == $p->revision) {
				      $p->o_latest = false;
			    }

			  if ($p->fetchFromId()) {
                                $this->ircClass->privMsg($channel, "Patch not found in database");
				$m->disconnect();
				return;
			  }
			} else {
			  $p = Patch::pLatest($pid);
			  if(!$p) {
                                $this->ircClass->privMsg($channel, "Patch not found in database");
				$m->disconnect();
				return;
			  }
    			  $p->fetchFromId();
			$m->disconnect();
			}
                        $this->ircClass->privMsg($channel, $this->announce_patch($p));

			$m->disconnect();
		}
	}


	public function priv_bug($line, $args)
	{
		$channel = $line['to'];

		if ($channel == $this->ircClass->getNick())
		{
			return;
		}

		if ($args['nargs'] == 0)
		{
			$msg = "!bug <id>]";
			$this->ircClass->privMsg($channel, $msg);
		}
		else
		{
			$m = mysqlCM::getInstance();
			if ($m->connect()) {
				$this->ircClass->privMsg($channel, "Unable to connect to MySQL server...");
				return;
			}

			$pid = intval($args['arg1']);

			$p = new Bugid($pid);
			if ($p->fetchFromId()) {
                          $this->ircClass->privMsg($channel, "Bug not found in database");
			  $m->disconnect();
			  return;
			}
			if (!empty($p->synopsis)) {
			  $msg = "Bug: ".$p->id." (http://sunsolve.espix.org/bugid/id/".$p->id.") ".$p->synopsis;
			} else {
			  $msg = "Bug: ".$p->id." - ".$p->synopsis;
			}
			$this->ircClass->privMsg($channel, $msg);
			$m->disconnect();

		}
	}


}

?>
