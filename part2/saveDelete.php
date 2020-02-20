<?php
  include "/srv/www/portal/ini.php";
  include $rootd."connectBase.php";
  include $rootd."lib.php";

  if (!isset($_POST["jdata"])){
    echo "PORTAL_USER_LOGOUT";
    return;
  }

  $g=json_decode($_POST["jdata"]);
  $arr=array();
  $logId=-1;

  // Check Delete?

  $qId = OCIParse($os,"SELECT REQID FROM REQUEST.REQUESTDELETE WHERE REQID=:REQID");
  OCIBindByName($qId,"REQID",$g->reqId);
  OCIExecute($qId);
  OCIFetch($qId);
  $isId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$isId){
    $q2=OCIParse($os,"INSERT INTO REQUEST.REQUESTDELETE (REQID)VALUES(:REQID)");
    OCIBindByName($q2,"REQID",$g->reqId,10);
    OCIExecute($q2);
    OCIFreestatement($q2);
  }

  $q=OCIParse($os,"UPDATE REQUEST.REQUESTDELETE SET
                USERID=:USERID,
                DELTIME=SYSDATE,
                DELETENOTE=:DELETENOTE
                WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$g->reqId,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIBindByName($q,"DELETENOTE",$g->deleteNote,-1);
  OCIExecute($q);
  OCIFreeStatement($q);

  $q4=OCIParse($os,
    "UPDATE REQUEST.REQUESTS 
              SET 
                REQSTATUSID=:REQSTATUSID
              WHERE REQID=:REQID");
  OCIBindByName($q4,"REQID",$g->reqId,-1);
  OCIBindByName($q4,"REQSTATUSID",$g->reqStatusId,-1);
  OCIExecute($q4);
  OCIFreeStatement($q4);

  $q3=OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
  OCIBindByName($q3,"REQID",$g->reqId,-1);
  OCIExecute($q3);
  OCIFetch($q3);
  $reqNum=OCIResult($q3,"REQNUM");
  OCIFreeStatement($q3);

  $qLog=OCIParse($os,
    "INSERT INTO REQUEST.REQUESTLOG (REQLOGID)VALUES(-1) RETURNING REQLOGID INTO :REQLOGID");
  OCIBindByName($qLog,"REQLOGID",$logId,100);
  OCIExecute($qLog);
  OCIFreeStatement($qLog);

  $qUpdLog=OCIParse($os,
    "UPDATE REQUEST.REQUESTLOG 
              SET 
                REQID=:REQID,
                REQLOG=:REQLOG
              WHERE REQLOGID=:REQLOGID");

  OCIBindByName($qUpdLog,"REQLOGID",$logId,-1);
  OCIBindByName($qUpdLog,"REQLOG",$g->reqLog,-1);
  OCIBindByName($qUpdLog,"REQID",$g->reqId,-1);
  OCIExecute($qUpdLog);
  OCIFreeStatement($qUpdLog);

  OCICommit($os);

  echo $reqNum;
?>