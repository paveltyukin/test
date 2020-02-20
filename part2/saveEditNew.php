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
  $logId=-1;
  $jsonArr=array();

  $q=OCIParse($os,"UPDATE REQUEST.REQUESTS SET
                REQAGR=:REQAGR
                WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"REQAGR",$g->reqArg,-1);
  OCIExecute($q);
  OCIFreeStatement($q);
  // Согласование заявки сохранить. Приходит строка с запятыми

  if($g->reqArg>0){
    $reqArgArr = split(",",$g->reqArgDivId);
    foreach ($reqArgArr as $keyAgr=>$valAgr){
      if($valAgr){
        $reqArg=OCIParse($os,"INSERT INTO REQUEST.REQUESTAGR (REQID,DIVID,USERID,ISNSS)VALUES(:REQID,:DIVID,:USERID,:ISNSS)");
        OCIBindByName($reqArg,"REQID",$id,-1);
        OCIBindByName($reqArg,"DIVID",$valAgr,-1);
        OCIBindByName($reqArg,"USERID",$g->userId,-1);
        OCIBindByName($reqArg,"ISNSS",$g->IsNSS,-1);
        OCIExecute($reqArg);
        OCIFreeStatement($reqArg);
      }
    }
  }

  $q3=OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
  OCIBindByName($q3,":REQID",$id,-1);
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
  OCIBindByName($qUpdLog,"REQID",$id,-1);
  OCIExecute($qUpdLog);
  OCIFreeStatement($qUpdLog);

  OCICommit($os);

  $jsonArr=array(
    'reqNum'=>$reqNum,
    'reqId'=>$id
  );

  echo json_encode($jsonArr);
?>