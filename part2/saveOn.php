<?php
  include "/srv/www/portal/ini.php";
  include $rootd."connectBase.php";
  include $rootd."lib.php";

  if (!isset($_POST["jdata"])){
    echo "PORTAL_USER_LOGOUT";
    return;
  }

  $g=json_decode($_POST["jdata"]);
  $id=$g->reqId;
  $statusId=3;
  $arrApr=array();
  $logId=-1;

  $sql = "SELECT REQID FROM REQUEST.REQUESTON WHERE REQID=:REQID";
  $qId = OCIParse($os,$sql);
  OCIBindByName($qId,"REQID",$id);
  OCIExecute($qId);
  OCIFetch($qId);
  $isId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$isId){
    $qIsId=OCIParse($os,"INSERT INTO REQUEST.REQUESTON (REQID)VALUES(:REQID)");
    OCIBindByName($qIsId,"REQID",$id,10);
    OCIExecute($qIsId);
    OCIFreestatement($qIsId);
  }

  $q=OCIParse($os,
    "UPDATE REQUEST.REQUESTON 
              SET 
                ONTINS=SYSDATE,
                ONTIME=TO_DATE(:REQONTIME,'YYYY-MM-DD HH24:MI:SS'),
                USERID=:USERID
              WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"REQONTIME",$g->reqOnTime,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIExecute($q);
  OCIFreeStatement($q);
  //

  if(isset($g->statusId)&&$g->statusId==0){

  }else{
    $q2=OCIParse($os,
      "UPDATE REQUEST.REQUESTS 
              SET 
                REQSTATUSID=:REQSTATUSID
              WHERE REQID=:REQID");
    OCIBindByName($q2,"REQID",$id,-1);
    OCIBindByName($q2,"REQSTATUSID",$statusId,-1);
    OCIExecute($q2);
    OCIFreeStatement($q2);
  }


  $q3 = OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
  OCIBindByName($q3,"REQID",$id);
  OCIExecute($q3);
  OCIFetch($q3);
  $reqNum = OCIResult($q3,'REQNUM');
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

  $arrApr['reqNum']=$reqNum;
  $arrApr['reqId']=$id;

  echo json_encode($arrApr);
?>