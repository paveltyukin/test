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
  $statusId=6;
  $arr=array();
  $logId=-1;

  $sql = "SELECT REQID FROM REQUEST.REQUESTNOTE WHERE REQID=:REQID";
  $qId = OCIParse($os,$sql);
  OCIBindByName($qId,"REQID",$id);
  OCIExecute($qId);
  OCIFetch($qId);
  $isId = OCIResult($qId,'REQID');
  OCIFreeStatement($qId);

  if(!$isId){
    $q=OCIParse($os,"INSERT INTO REQUEST.REQUESTNOTE (REQID)VALUES(:REQID)");
    OCIBindByName($q,"REQID",$id,10);
    OCIExecute($q);
    OCIFreestatement($q);
  }

  $q=OCIParse($os,
    "UPDATE REQUEST.REQUESTNOTE 
              SET 
                REQNOTE=:REQNOTE,
                REQNOTETIME=TO_DATE(:REQNOTETIME,'YYYY-MM-DD HH24:MI:SS'),
                REQTIMEINS=SYSDATE,
                USERID=:USERID,
                ADMINID=:ADMINID
              WHERE REQID=:REQID");
  OCIBindByName($q,"REQID",$id,-1);
  OCIBindByName($q,"REQNOTETIME",$g->reqNoteTime,-1);
  OCIBindByName($q,"REQNOTE",$g->reqNote,-1);
  OCIBindByName($q,"USERID",$g->userId,-1);
  OCIBindByName($q,"ADMINID",$g->adminId,-1);
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
  OCIBindByName($qUpdLog,"REQID",$g->reqId,-1);
  OCIExecute($qUpdLog);
  OCIFreeStatement($qUpdLog);


  $q10=OCIParse($os,"SELECT TRANSIDLIST FROM REQUEST.REQUESTS WHERE REQID=:REQID");
  OCIBindByName($q10,":REQID",$g->reqId,-1);
  OCIExecute($q10);
  OCIFetch($q10);
  $transList=ClobToStr(OCIResult($q10,"TRANSIDLIST"));
  OCIFreeStatement($q10);

  $arrTransReqId=array();
  if($transList){
    $arrTransReqId=explode(",",$transList);

    foreach($arrTransReqId as $lkey=>$lval){
      $sql2 = "SELECT REQID FROM REQUEST.REQUESTNOTE WHERE REQID=:REQID";
      $qId2 = OCIParse($os,$sql2);
      OCIBindByName($qId2,"REQID",$lval);
      OCIExecute($qId2);
      OCIFetch($qId2);
      $isId2 = OCIResult($qId2,'REQID');
      OCIFreeStatement($qId2);

      if(!$isId2){
        $q23=OCIParse($os,"INSERT INTO REQUEST.REQUESTNOTE (REQID)VALUES(:REQID)");
        OCIBindByName($q23,"REQID",$lval,10);
        OCIExecute($q23);
        OCIFreestatement($q23);
      }


      $qTrans2=OCIParse($os,
        "UPDATE REQUEST.REQUESTNOTE 
              SET 
                REQNOTE=:REQNOTE,
                REQNOTETIME=TO_DATE(:REQNOTETIME,'YYYY-MM-DD HH24:MI:SS'),
                REQTIMEINS=SYSDATE,
                USERID=:USERID,
                ADMINID=:ADMINID
              WHERE REQID=:REQID");
      OCIBindByName($qTrans2,"REQID",$lval,-1);
      OCIBindByName($qTrans2,"REQNOTETIME",$g->reqNoteTime,-1);
      OCIBindByName($qTrans2,"REQNOTE",$g->reqNote,-1);
      OCIBindByName($qTrans2,"USERID",$g->userId,-1);
      OCIBindByName($qTrans2,"ADMINID",$g->adminId,-1);
      OCIExecute($qTrans2);
      OCIFreeStatement($qTrans2);

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


  OCICommit($os);

  $arr['reqNum']=$reqNum;
  echo json_encode($arr);
?>