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
  $isTrans=0;
  $newReqId=-1;
  $newReqStatusId=1;
  $transReqId=$g->reqId;
  $transReqStatusId=$g->reqStatusId;
  $arrTransReqId=array();

  $q=OCIParse($os,"INSERT INTO REQUEST.REQUESTID (REQID)VALUES(-1) RETURNING REQID INTO :REQID");
  OCIBindByName($q,"REQID",$newReqId,10);
  OCIExecute($q);
  OCIFreestatement($q);

  $q2=OCIParse($os,"BEGIN REQUEST.CREATE_REQIDPRO($newReqId); END;");
  OCIExecute($q2);
  OCIFreestatement($q2);

  $q3=OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
  OCIBindByName($q3,":REQID",$newReqId,-1);
  OCIExecute($q3);
  OCIFetch($q3);
  $newReqNum=OCIResult($q3,"REQNUM");
  OCIFreeStatement($q3);

  $q4=OCIParse($os,"INSERT INTO REQUEST.REQUESTS (REQID, REQNUM, REQSTATUSID)VALUES(:REQID, :REQNUM, :REQSTATUSID)");
  OCIBindByName($q4,"REQID",$newReqId,-1);
  OCIBindByName($q4,"REQNUM",$newReqNum,-1);
  OCIBindByName($q4,"REQSTATUSID",$newReqStatusId,-1);
  OCIExecute($q4);
  OCIFreeStatement($q4);

  $q=OCIParse($os,"UPDATE REQUEST.REQUESTS SET 
                REQCATID=:REQCATID, 
                REQTSUB=TO_DATE(:REQTSUB,'HH24:MI DD.MM.YYYY'),
                REQTREPB=TO_DATE(:REQTREPB,'HH24:MI DD.MM.YYYY'),
                REQTREPE=TO_DATE(:REQTREPE,'HH24:MI DD.MM.YYYY'),
                REQTIME=SYSDATE,
                REQCONTW=:REQCONTW,
                REQAGR=:REQAGR,
                REQCRASH=:REQCRASH,
                USERID=:USERID,
                REQSTATUSID=:REQSTATUSID,
                ISNSS=:ISNSS,
                EQUIPID=:EQUIPID,
                ADMINID=:ADMINID
                WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$newReqId,-1);
  OCIBindByName($q,"REQCATID",$g->reqCatId,-1);
  OCIBindByName($q,"REQTSUB",$g->reqTSub,-1);
  OCIBindByName($q,"REQTREPB",$g->reqTRepB,-1);
  OCIBindByName($q,"REQTREPE",$g->reqTRepE,-1);
  OCIBindByName($q,"REQCONTW",$g->reqContW,-1);
  OCIBindByName($q,"REQAGR",$g->reqArg,-1);
  OCIBindByName($q,"REQCRASH",$g->reqCrash,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIBindByName($q,"REQSTATUSID",$newReqStatusId,-1);
  OCIBindByName($q,"ISNSS",$g->IsNSS,-1);
  OCIBindByName($q,"EQUIPID",$g->equipId,-1);
  OCIBindByName($q,"ADMINID",$g->AdminId,-1);
  OCIExecute($q);
  OCIFreeStatement($q);

  if($g->reqArg>0){
    $reqArgArr = split(",",$g->reqArgDivId);
    foreach ($reqArgArr as $keyAgr=>$valAgr){
      if($valAgr){
        $reqArg=OCIParse($os,"INSERT INTO REQUEST.REQUESTAGR (REQID,DIVID,USERID,ISNSS)VALUES(:REQID,:DIVID,:USERID,:ISNSS)");
        OCIBindByName($reqArg,"REQID",$newReqId,-1);
        OCIBindByName($reqArg,"DIVID",$valAgr,-1);
        OCIBindByName($reqArg,"USERID",$g->userId,-1);
        OCIBindByName($reqArg,"ISNSS",$g->IsNSS,-1);
        OCIExecute($reqArg);
        OCIFreeStatement($reqArg);
      }
    }
  }

  $newLogTr=$g->reqLog.$newReqNum."\n".$g->reqLog2;

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
  OCIBindByName($qUpdLog,"REQLOG",$newLogTr,-1);
  OCIBindByName($qUpdLog,"REQID",$transReqId,-1);
  OCIExecute($qUpdLog);
  OCIFreeStatement($qUpdLog);

  //// TRANSFER
  $qId = OCIParse($os,"SELECT REQID FROM REQUEST.REQUESTTRANSFER WHERE REQID=:REQID");
  OCIBindByName($qId,"REQID",$transReqId);
  OCIExecute($qId);
  OCIFetch($qId);
  $transReqId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$transReqId){
    $transReqId=$g->reqId;
    $q5=OCIParse($os,"INSERT INTO REQUEST.REQUESTTRANSFER (REQID)VALUES(:REQID)");
    OCIBindByName($q5,"REQID",$transReqId,10);
    OCIExecute($q5);
    OCIFreestatement($q5);
  }

  $q6=OCIParse($os,
    "UPDATE REQUEST.REQUESTTRANSFER SET
              TRANSNOTE=:TRANSNOTE,
              TRANSTIME=SYSDATE,
              USERID=:USERID,
              REQTRANSID=:REQTRANSID,
              REQTRANSNUM=:REQTRANSNUM
            WHERE REQID=:REQID");
  OCIBindByName($q6,"REQID",$transReqId,-1);
  OCIBindByName($q6,"USERID",$g->userId,-1);
  OCIBindByName($q6,"TRANSNOTE",$g->reqTransNote,-1);
  OCIBindByName($q6,"REQTRANSID",$newReqId,-1);
  OCIBindByName($q6,"REQTRANSNUM",$newReqNum,-1);
  OCIExecute($q6);
  OCIFreeStatement($q6);

  $q7=OCIParse($os,"UPDATE REQUEST.REQUESTS SET
              REQSTATUSID=:REQSTATUSID
              WHERE REQID=:REQID");
  OCIBindByName($q7,"REQID",$transReqId,-1);
  OCIBindByName($q7,"REQSTATUSID",$transReqStatusId,-1);
  OCIExecute($q7);
  OCIFreeStatement($q7);

  $q9=OCIParse($os,"SELECT REQNUM FROM REQUEST.REQUESTID WHERE REQID=:REQID");
  OCIBindByName($q9,"REQID",$transReqId,-1);
  OCIExecute($q9);
  OCIFetch($q9);
  $transReqNum=OCIResult($q9,"REQNUM");
  OCIFreeStatement($q9);

  $q8=OCIParse($os,"UPDATE REQUEST.REQUESTS SET
              ISTRANS=:ISTRANS
              WHERE REQID=:REQID");
  OCIBindByName($q8,"REQID",$transReqId,-1);
  OCIBindByName($q8,"ISTRANS",$newReqNum,-1);
  OCIExecute($q8);
  OCIFreeStatement($q8);

  $qLog2=OCIParse($os,
    "INSERT INTO REQUEST.REQUESTLOG (REQLOGID)VALUES(-1) RETURNING REQLOGID INTO :REQLOGID");
  OCIBindByName($qLog2,"REQLOGID",$logId2,100);
  OCIExecute($qLog2);
  OCIFreeStatement($qLog2);

  $qUpdLog2=OCIParse($os,
    "UPDATE REQUEST.REQUESTLOG 
              SET 
                REQID=:REQID,
                REQLOG=:REQLOG
              WHERE REQLOGID=:REQLOGID");

  $newLog=$g->reqLogNewP1.$newReqNum."\n".$g->reqLogNewP2;

  OCIBindByName($qUpdLog2,"REQLOGID",$logId2,-1);
  OCIBindByName($qUpdLog2,"REQLOG",$newLog,-1);
  OCIBindByName($qUpdLog2,"REQID",$newReqId,-1);
  OCIExecute($qUpdLog2);
  OCIFreeStatement($qUpdLog2);

  //// SAVE TRANS LIST
  $transIdList=$transReqId.",".$newReqId;
  $newTransReqList="";

  $qId3 = OCIParse($os,"SELECT REQID,REQTRANSID FROM REQUEST.REQUESTTRANSFER WHERE REQTRANSID=:REQTRANSID");
  OCIBindByName($qId3,"REQTRANSID",$transReqId);
  OCIExecute($qId3);
  OCIFetch($qId3);
  $transReqIdReqId = OCIResult($qId3,'REQID');
  $transReqIdReqTransId = OCIResult($qId3,'REQTRANSID');

  if($transReqIdReqTransId && $transReqIdReqId){
    $q10 = OCIParse($os,"SELECT TRANSIDLIST FROM REQUEST.REQUESTS WHERE REQID=:REQID");
    OCIBindByName($q10,"REQID",$transReqId);
    OCIExecute($q10);
    OCIFetch($q10);
    $transReqList = ClobToStr(OCIResult($q10,'TRANSIDLIST'));
    $newTransReqList.=$transReqList.",".$transIdList;
    OCIFreestatement($q10);
  }else{
    $newTransReqList.=$transIdList;
  }
  OCIFreeStatement($qId3);

  $arrTransReqId=array();
  $arrTransReqId=explode(",",$newTransReqList);

  $arrNewTransReqId=array();
  $arrNewTransReqId=array_unique($arrTransReqId);

  $newSetTransList=implode(",",$arrNewTransReqId);

  $q11=OCIParse($os,"UPDATE REQUEST.REQUESTS SET
              TRANSIDLIST=:TRANSIDLIST
              WHERE REQID=:REQID");
  OCIBindByName($q11,"REQID",$newReqId,-1);
  OCIBindByName($q11,"TRANSIDLIST",$newSetTransList,-1);
  OCIExecute($q11);
  OCIFreeStatement($q11);

  OCICommit($os);

  $arr=array(
    'transReqId'=>$transReqId,
    'transReqNum'=>$transReqNum,
    'newReqId'=>$newReqId,
    'newReqNum'=>$newReqNum
  );

  echo json_encode($arr);
?>