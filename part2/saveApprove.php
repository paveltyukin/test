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
  $statusId=0;
  $arrApr=array();
  $logId=-1;

  $sql = "SELECT REQID FROM REQUEST.REQUESTAPPROVE WHERE REQID=:REQID";
  $qId = OCIParse($os,$sql);
  OCIBindByName($qId,"REQID",$id);
  OCIExecute($qId);
  OCIFetch($qId);
  $isId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$isId){
    $qIsId=OCIParse($os,"INSERT INTO REQUEST.REQUESTAPPROVE (REQID)VALUES(:REQID)");
    OCIBindByName($qIsId,"REQID",$id,10);
    OCIExecute($qIsId);
    OCIFreestatement($qIsId);
  }


  $q=OCIParse($os,
    "UPDATE REQUEST.REQUESTAPPROVE 
              SET 
                APPROVETIME=SYSDATE,
                ISAPPROVE=:ISAPPROVE,
                APPROVENOTE=:APPROVENOTE,
                GISID=:GISID,
                USERID=:USERID
              WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"ISAPPROVE",$g->isApprove,-1);
  OCIBindByName($q,"APPROVENOTE",$g->approveNote,-1);
  OCIBindByName($q,"GISID",$g->gisId,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIExecute($q);
  OCIFreeStatement($q);
  //


  if($g->isApprove>0){
    $statusId=2; // Утверждённая
  }else{
    $statusId=5; // Отклонённая
  }

  $q2=OCIParse($os,
    "UPDATE REQUEST.REQUESTS 
              SET 
                REQSTATUSID=:REQSTATUSID
              WHERE REQID=:REQID");
  OCIBindByName($q2,"REQID",$id,-1);
  OCIBindByName($q2,"REQSTATUSID",$statusId,-1);
  OCIExecute($q2);
  OCIFreeStatement($q2);


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
  $arrApr['isApprove']=$g->isApprove;

  echo json_encode($arrApr);
?>