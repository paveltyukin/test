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
  $statusId=4;
  $arrApr=array();
  $logId=-1;

  $sql = "SELECT REQID FROM REQUEST.REQUESTOFF WHERE REQID=:REQID";
  $qId = OCIParse($os,$sql);
  OCIBindByName($qId,"REQID",$id);
  OCIExecute($qId);
  OCIFetch($qId);
  $isId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$isId){
    $q=OCIParse($os,"INSERT INTO REQUEST.REQUESTOFF (REQID)VALUES(:REQID)");
    OCIBindByName($q,"REQID",$id,10);
    OCIExecute($q);
    OCIFreestatement($q);
  }

  $q=OCIParse($os,
    "UPDATE REQUEST.REQUESTOFF 
              SET 
                OFFTIME=TO_DATE(:REQOFFTIME,'YYYY-MM-DD HH24:MI:SS'),
                OFFTINS=SYSDATE,
                USERID=:USERID
              WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"REQOFFTIME",$g->reqOffTime,-1);
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

  $q10=OCIParse($os,"SELECT TRANSIDLIST FROM REQUEST.REQUESTS WHERE REQID=:REQID");
  OCIBindByName($q10,"REQID",$g->reqId,-1);
  OCIExecute($q10);
  OCIFetch($q10);
  $transList=ClobToStr(OCIResult($q10,"TRANSIDLIST"));
  OCIFreeStatement($q10);

  $arrTransReqId=array();
  if($transList){
    $arrTransReqId=explode(",",$transList);

    foreach($arrTransReqId as $lkey=>$lval){
      if($lval!=$g->reqId){
        $sql2 = "SELECT REQID FROM REQUEST.REQUESTOFF WHERE REQID=:REQID";
        $qId2 = OCIParse($os,$sql2);
        OCIBindByName($qId2,"REQID",$lval);
        OCIExecute($qId2);
        OCIFetch($qId2);
        $isId2 = OCIResult($qId2,'REQID');
        OCIFreeStatement($qId2);

        if(!$isId2){
          $q23=OCIParse($os,"INSERT INTO REQUEST.REQUESTOFF (REQID)VALUES(:REQID)");
          OCIBindByName($q23,"REQID",$lval,10);
          OCIExecute($q23);
          OCIFreestatement($q23);
        }

        $q=OCIParse($os,
          "UPDATE REQUEST.REQUESTOFF 
              SET 
                OFFTIME=TO_DATE(:REQOFFTIME,'YYYY-MM-DD HH24:MI:SS'),
                OFFTINS=SYSDATE,
                USERID=:USERID
              WHERE REQID=:REQID");
        OCIBindByName($q,"REQID",$lval,-1);
        OCIBindByName($q,"REQOFFTIME",$g->reqOffTime,-1);
        OCIBindByName($q,"USERID",$g->userId,-1);
        OCIExecute($q);
        OCIFreeStatement($q);

        $q2=OCIParse($os,
          "UPDATE REQUEST.REQUESTS 
            SET 
              REQSTATUSID=:REQSTATUSID
            WHERE REQID=:REQID");
        OCIBindByName($q2,"REQID",$lval,-1);
        OCIBindByName($q2,"REQSTATUSID",$statusId,-1);
        OCIExecute($q2);
        OCIFreeStatement($q2);



        $qLog2=OCIParse($os,
          "INSERT INTO REQUEST.REQUESTLOG (REQLOGID)VALUES(-1) RETURNING REQLOGID INTO :REQLOGID");
        OCIBindByName($qLog2,"REQLOGID",$logId,100);
        OCIExecute($qLog2);
        OCIFreeStatement($qLog2);

        $qUpdLog2=OCIParse($os,
          "UPDATE REQUEST.REQUESTLOG 
              SET 
                REQID=:REQID,
                REQLOG=:REQLOG
              WHERE REQLOGID=:REQLOGID");

        OCIBindByName($qUpdLog2,"REQLOGID",$logId,-1);
        OCIBindByName($qUpdLog2,"REQLOG",$g->reqLog,-1);
        OCIBindByName($qUpdLog2,"REQID",$lval,-1);
        OCIExecute($qUpdLog2);
        OCIFreeStatement($qUpdLog2);
      }
    }
  }
  

  OCICommit($os);

  $arrApr['reqNum']=$reqNum;
  $arrApr['reqId']=$id;

  echo json_encode($arrApr);
?>