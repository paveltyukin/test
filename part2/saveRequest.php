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

  $id=$g->reqId;
  $reqNumTrans=0;
  $reqStatusId=1;
  if(isset($g->reqTransId)){
    $reqTransId=$g->reqTransId;
  }else{
    $g->reqTransId=-1;
  }
  $isTrans=0;
  $logId=-1;

  if ($id==-1){

    $q=OCIParse($os,"INSERT INTO REQUEST.REQUESTID (REQID)VALUES(-1) RETURNING REQID INTO :REQID");
    OCIBindByName($q,"REQID",$id,10);
    OCIExecute($q);
    OCIFreestatement($q);

    $q2=OCIParse($os,"BEGIN REQUEST.CREATE_REQIDPRO($id); END;");
    OCIExecute($q2);
    OCIFreestatement($q2);

    $q3=OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
    OCIBindByName($q3,":REQID",$id,-1);
    OCIExecute($q3);
    OCIFetch($q3);
    $reqNum=OCIResult($q3,"REQNUM");
    OCIFreeStatement($q3);

    $q4=OCIParse($os,"INSERT INTO REQUEST.REQUESTS (REQID, REQNUM)VALUES(:REQID, :REQNUM)");
    OCIBindByName($q4,"REQID",$id,-1);
    OCIBindByName($q4,"REQNUM",$reqNum,-1);
    OCIExecute($q4);
    OCIFreeStatement($q4);
  }

  $q=OCIParse($os,"UPDATE REQUEST.REQUESTS SET 
                REQCATID=:REQCATID, 
                REQTSUB=TO_DATE(:REQTSUB,'YYYY-MM-DD HH24:MI:SS'),
                REQTREPB=TO_DATE(:REQTREPB,'YYYY-MM-DD HH24:MI:SS'),
                REQTREPE=TO_DATE(:REQTREPE,'YYYY-MM-DD HH24:MI:SS'),
                REQTIME=SYSDATE,
                REQCONTW=:REQCONTW,
                REQAGR=:REQAGR,
                REQCRASH=:REQCRASH,
                USERID=:USERID,
                REQSTATUSID=:REQSTATUSID,
                ISNSS=:ISNSS,
                EQUIPID=:EQUIPID,
                ADMINID=:ADMINID,
                ISTRANS=:ISTRANS
                WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"REQCATID",$g->reqCatId,-1);
  OCIBindByName($q,"REQTSUB",$g->reqTSub,-1);
  OCIBindByName($q,"REQTREPB",$g->reqTRepB,-1);
  OCIBindByName($q,"REQTREPE",$g->reqTRepE,-1);
  OCIBindByName($q,"REQCONTW",$g->reqContW,-1);
  OCIBindByName($q,"REQAGR",$g->reqArg,-1);
  OCIBindByName($q,"REQCRASH",$g->reqCrash,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIBindByName($q,"REQSTATUSID",$reqStatusId,-1);
  OCIBindByName($q,"ISNSS",$g->IsNSS,-1);
  OCIBindByName($q,"EQUIPID",$g->equipId,-1);
  OCIBindByName($q,"ADMINID",$g->AdminId,-1);
  OCIBindByName($q,"ISTRANS",$isTrans,-1);
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
    'reqId'=>$id,
    'reqNumTans'=>$reqNumTrans,
    'reqIdTrans'=>$g->reqTransId
  );

  echo json_encode($jsonArr);
?>