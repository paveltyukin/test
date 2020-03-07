<?php 
	include "lib.php";

	$userId=getUserFromSession($os);
	if ($userId < 0){
		echo "USER_LOGOUT";
		return;
	}

  $os2 = @oci_connect("...","...");
  if (!$os2){
    print_r("SERVER_ERROR");
    return;
  }

  $g=json_decode($_POST["jdata"]);
  $where="";
  $whereDate="";
  $whereSearch="";

  if($g->date1!="" && $g->date2!=""){
    $whereDate.=" AND REQTIME BETWEEN TO_DATE(:DATE1,'dd.mm.yyyy') AND TO_DATE(:DATE2,'dd.mm.yyyy')+1 ";
  }
  if($g->lpId!=0){
    $where.=" AND REQSTATUSID=:REQSTATUSID ";
  }
  if($g->reqSearch!=""){
    $whereSearch.=" AND REQNUM LIKE '%$g->reqSearch%'";
  }

  $i=0;
  $arr = array();
  $reqTime=array();
  $reqTRepB=array();
  $reqTRepE=array();
  $reqTSub=array();
  $arrEqSearch = array();
  $arrEq = array();
  $arrApprove=array();
  $approveTime=array();
  $arrOn=array();
  $onTime=array();
  $onTimeI=array();
  $offTime=array();
  $offTimeI=array();
  $reqTransArr=array();
  $arrTrans=array();
  $transTime=array();
  $reqDel=array();
  $delTime=array();
  $noteTime=array();
  $noteTimeIns=array();
  $reqNote=array();

  $q=OCIParse($os,"SELECT REQID,REQNUM,REQCATID,EQUIPID,ADMINID,ISTRANS,
                              USERS.USERNAME(ADMINID) ADMINNAME,
                              USERS.USERNAME(USERID) USERNAME,
                              TO_CHAR(REQTSUB,'HH24:MI DD.MM.YYYY')REQTSUB,
                              TO_CHAR(REQTREPB,'HH24:MI DD.MM.YYYY')REQTREPB,
                              TO_CHAR(REQTREPE,'HH24:MI DD.MM.YYYY')REQTREPE,
                              TO_CHAR(REQTSUB,'HH24:MI')REQTSUBT,
                              TO_CHAR(REQTSUB,'YYYY-MM-DD')REQTSUBD,
                              TO_CHAR(REQTREPB,'HH24:MI')REQTREPBT,
                              TO_CHAR(REQTREPB,'YYYY-MM-DD')REQTREPBD,
                              TO_CHAR(REQTREPE,'HH24:MI')REQTREPET,
                              TO_CHAR(REQTREPE,'YYYY-MM-DD')REQTREPED,
                              TO_CHAR(REQTIME,'HH24:MI:SS DD.MM.YYYY')REQTIME,
                              TO_CHAR(REQTIME,'HH24:MI:SS')REQTIMET,
                              TO_CHAR(REQTIME,'YYYY-MM-DD')REQTIMED,
                              REQCONTW,REQAGR,REQCRASH,USERID,REQSTATUSID,ISNSS
                              FROM REQUEST.REQUESTS
                              WHERE REQID=REQID
                              ".$where.$whereDate.$whereSearch."
                              ORDER BY REQID DESC");
  if($g->lpId!=0){
    OCIBindByName($q,"REQSTATUSID",$g->lpId,-1);
  }
  if($g->date1!="" && $g->date2!=""){
    OCIBindByName($q,"DATE1",$g->date1,-1);
    OCIBindByName($q,"DATE2",$g->date2,-1);
  }

  OCIExecute($q);

	while (OCIFetch($q)){
	  // REQID
    $reqId = OCIResult($q,'REQID');
    // Оборудование для заявок в массив arrEq, затем в общий массив arr.
    $arrEq=split(",",OCIResult($q,"EQUIPID"));

    $j=0;
    foreach ($arrEq as $keyEq=>$valEq){
      if($valEq){
        $sql="SELECT EL.ELEMENTID,EL.POSITION,EL.NAME,DI.DIVISIONSHORTNAME,SD.SYSTEMNAME,
                UU.UNITENAME,EL.ISCHECKED,DEFECT.FULLSYSTEMNAME(EL.SYSTEMID)FULLSYSTEMNAME,
                DEFECT.CURRENTEQUIPMENTSERIAL(EL.ELEMENTID)SERIAL,DEFECT.CURRENTEQUIPMENTMODEL(EL.ELEMENTID)MODEL,
                TO_CHAR(DEFECT.CURRENTEQUIPMENTSTARTDATE(EL.ELEMENTID),'DD.MM.YYYY')STARTDATE,EL.DIVISIONID,EL.ISCHECKED2,
                DEFECT.ELEMENTDEFECTCOUNT(EL.ELEMENTID)DEFCNTD FROM DEFECT.ELEMENT EL, DEFECT.SYSTEM SD, 
                DEFECT.DIVISION DI,DEFECT.UNITUNITE UU 
              WHERE EL.SYSTEMID=SD.SYSTEMID(+) 
              AND EL.DIVISIONID=DI.DIVISIONID(+)
              AND EL.UNITUNITEID=UU.UNITUNITEID(+) 
              AND EL.DELETED=0 
              AND EL.ELEMENTID=:ELEMENTID";

        $psql=OCIParse($os2, $sql);
        OCIBindByName($psql,"ELEMENTID",$valEq,-1);
        OCIExecute($psql);

        while (OCIFetch($psql)){
          $arrEqSearch[$j]= array(
            'id'=>ociresult($psql,"ELEMENTID"),
            'rn'=>$i+1,
            'ps'=> ociresult($psql,"POSITION"),
            'nm'=>ociresult($psql,"NAME"),
            'dv'=>ociresult($psql,"DIVISIONSHORTNAME"),
            'sn'=>ociresult($psql,"SYSTEMNAME"),
            'un'=>ociresult($psql,"UNITENAME"),
            'ic'=>ociresult($psql,"ISCHECKED"),
            'fsn'=>ociresult($psql,"FULLSYSTEMNAME"),
            'es'=>ociresult($psql,"SERIAL"),
            'em'=>ociresult($psql,"MODEL"),
            'esd'=>ociresult($psql,"STARTDATE"),
            'did'=>ociresult($psql,"DIVISIONID"),
            "ic2"=>ociresult($psql,"ISCHECKED2"),
            'defC'=>ociresult($psql,"DEFCNTD"));
          $j++;
        }
        OCIFreeStatement($psql);
      }
    }

    // REQSTATUSID begin
    $reqStatus = OCIResult($q,"REQSTATUSID");
    $sqlStatus = "SELECT REQSTATUSID, REQSTATUSNAME FROM REQUEST.STATUSLIST WHERE REQSTATUSID=:REQSTATUSID";
    $qStatus = OCIParse($os,$sqlStatus);
    OCIBindByName($qStatus,"REQSTATUSID",$reqStatus);
    OCIExecute($qStatus);
    OCIFetch($qStatus);
    $reqStatusName = OCIResult($qStatus,'REQSTATUSNAME');
    OCIFreeStatement($qStatus);
    // REQSTATUSID end

    // REQCATID Begin
    $reqCatId = OCIResult($q,"REQCATID");
    $sqlCat = "SELECT REQCATNAME FROM REQUEST.CATEGORYLIST WHERE REQCATID=:REQCATID";
    $qCat = OCIParse($os,$sqlCat);
    OCIBindByName($qCat,"REQCATID",$reqCatId);
    OCIExecute($qCat);
    OCIFetch($qCat);
    $reqCatName = OCIResult($qCat,'REQCATNAME');
    OCIFreeStatement($qCat);
    // REQCATID End

    // REQAGREEMENT Begin
    $arrAgr=array();
    // если согласование есть
    if(OCIResult($q,'REQAGR')>0){
      $x=0;
      $sqlAgr = "SELECT DISTINCT DIVID FROM REQUEST.REQUESTAGR WHERE REQID=:REQID";
      $qAgr = OCIParse($os,$sqlAgr);
      OCIBindByName($qAgr,"REQID",$reqId);
      OCIExecute($qAgr);
      while(OCIFetch($qAgr)){
        $qAgrCheck = OCIResult($qAgr,'DIVID');
        $sqlGetAgr = "SELECT DIVISIONSHORTNAME,DIVISIONID FROM GLOBAL.DIVISION WHERE DIVISIONID=:DIVID";
        $qGetAgr = OCIParse($os,$sqlGetAgr);
        OCIBindByName($qGetAgr,"DIVID",$qAgrCheck);
        OCIExecute($qGetAgr);
        while(OCIFetch($qGetAgr)){
          $arrAgr[$x]=array(
            'DivName'=>OCIResult($qGetAgr,'DIVISIONSHORTNAME'),
            'DivId'=>OCIResult($qGetAgr,'DIVISIONID'),
          );
          $x++;
        }
        OCIFreeStatement($qGetAgr);
      }
      OCIFreeStatement($qAgr);
    }

    $sqlApprove = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    USERS.USERNAME(GISID) GISNAME,
                    ISAPPROVE,
                    TO_CHAR(APPROVETIME,'HH24:MI DD.MM.YYYY')APPROVETIME,
                    TO_CHAR(APPROVETIME,'HH24:MI')APPROVETIMET,
                    TO_CHAR(APPROVETIME,'YYYY-MM-DD')APPROVETIMED,
                    APPROVENOTE,
                    GISID 
                    FROM REQUEST.REQUESTAPPROVE  
                    WHERE REQID=:REQID";
    $qApprove = OCIParse($os,$sqlApprove);
    OCIBindByName($qApprove,"REQID",$reqId);
    OCIExecute($qApprove);

    if(OCIFetch($qApprove)){

      $approveTime[0]=OCIResult($qApprove,'APPROVETIMET').":00";
      $approveTime[1]=OCIResult($qApprove,'APPROVETIMED');
      $arrApprove=array(
        'isApprove' => OCIResult($qApprove,'ISAPPROVE'),
        'approveTime' => OCIResult($qApprove,'APPROVETIME'),
        'approveTimeArr'=>$approveTime,
        'approveNote' => ClobToStr(OCIResult($qApprove,'APPROVENOTE')),
        'approveGISId' => OCIResult($qApprove,'GISID'),
        'approveUserId' => OCIResult($qApprove,'USERID'),
        'approveUserName'=>OCIResult($qApprove,'USERNAME'),
        'approveGIS'=>OCIResult($qApprove,'GISNAME')
      );
    }else{
      $arrApprove=0;
    }
    OCIFreeStatement($qApprove);

    // Request ON Begin
    $sqlOn = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    TO_CHAR(ONTIME,'HH24:MI DD.MM.YYYY')ONTIME,
                    TO_CHAR(ONTIME,'HH24:MI')ONTIMET,
                    TO_CHAR(ONTIME,'YYYY-MM-DD')ONTIMED,
                    TO_CHAR(ONTINS,'HH24:MI DD.MM.YYYY')ONTINS,
                    TO_CHAR(ONTINS,'HH24:MI')ONTINST,
                    TO_CHAR(ONTINS,'YYYY-MM-DD')ONTINSD
                    FROM REQUEST.REQUESTON  
                    WHERE REQID=:REQID";
    $qOn = OCIParse($os,$sqlOn);
    OCIBindByName($qOn,"REQID",$reqId);
    OCIExecute($qOn);

    if(OCIFetch($qOn)){

      $onTime[0]=OCIResult($qOn,'ONTIMET').":00";
      $onTime[1]=OCIResult($qOn,'ONTIMED');
      $onTimeI[0]=OCIResult($qOn,'ONTINST').":00";
      $onTimeI[1]=OCIResult($qOn,'ONTINSD');
      $arrOn=array(
        'onTime' => OCIResult($qOn,'ONTIME'),
        'onTimeIns'=>OCIResult($qOn,'ONTINS'),
        'onTimeArr'=>$onTime,
        'onTimeInsArr'=>$onTimeI,
        'onUserId' => OCIResult($qOn,'USERID'),
        'onUserName'=>OCIResult($qOn,'USERNAME'),
      );
    }else{
      $arrOn=0;
    }
    OCIFreeStatement($qOn);
    // Request ON End

// Request OFF Begin
    $sqlOff = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    TO_CHAR(OFFTIME,'HH24:MI DD.MM.YYYY')OFFTIME,
                    TO_CHAR(OFFTIME,'HH24:MI')OFFTIMET,
                    TO_CHAR(OFFTIME,'YYYY-MM-DD')OFFTIMED,
                    TO_CHAR(OFFTINS,'HH24:MI DD.MM.YYYY')OFFTINS,
                    TO_CHAR(OFFTINS,'HH24:MI')OFFTINST,
                    TO_CHAR(OFFTINS,'YYYY-MM-DD')OFFTINSD
                    FROM REQUEST.REQUESTOFF
                    WHERE REQID=:REQID";
    $qOff = OCIParse($os,$sqlOff);
    OCIBindByName($qOff,"REQID",$reqId);
    OCIExecute($qOff);

    if(OCIFetch($qOff)){

      $offTime[0]=OCIResult($qOff,'OFFTIMET').":00";
      $offTime[1]=OCIResult($qOff,'OFFTIMED');
      $offTimeI[0]=OCIResult($qOff,'OFFTINST').":00";
      $offTimeI[1]=OCIResult($qOff,'OFFTINSD');
      $arrOff=array(
        'offTime' => OCIResult($qOff,'OFFTIME'),
        'offTimeIns'=>OCIResult($qOff,'OFFTINS'),
        'offTimeArr'=>$offTime,
        'offTimeInsArr'=>$offTimeI,
        'offUserId' => OCIResult($qOff,'USERID'),
        'offUserName'=>OCIResult($qOff,'USERNAME'),
      );
    }else{
      $arrOff=0;
    }
    OCIFreeStatement($qOff);
// Request OFF End

// Request Delete Begin

    $sqlDel = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    TO_CHAR(DELTIME,'HH24:MI DD.MM.YYYY')DELTIME,
                    TO_CHAR(DELTIME,'HH24:MI')DELTIMET,
                    TO_CHAR(DELTIME,'YYYY-MM-DD')DELTIMED,
                    DELETENOTE
                    FROM REQUEST.REQUESTDELETE
                    WHERE REQID=:REQID";
    $qDel = OCIParse($os,$sqlDel);
    OCIBindByName($qDel,"REQID",$reqId);
    OCIExecute($qDel);

    if(OCIFetch($qDel)){
      $delTime[0]=OCIResult($qDel,'DELTIMET').":00";
      $delTime[1]=OCIResult($qDel,'DELTIMED');

      $reqDel=array(
        'delTime' => OCIResult($qDel,'DELTIME'),
        'delTimeArr'=>$delTime,
        'delUserId' => OCIResult($qDel,'USERID'),
        'delUserName'=>OCIResult($qDel,'USERNAME'),
        'delNote'=>ClobToStr(OCIResult($qDel,'DELETENOTE'))
      );
    }else{
      $reqDel=0;
    }
    OCIFreeStatement($qDel);
// Request Delete End

// Request Note Begin
    $sqlNote = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    ADMINID,
                    USERS.USERNAME(ADMINID) ADMINNAME,
                    TO_CHAR(REQNOTETIME,'HH24:MI DD.MM.YYYY')REQNOTETIME,
                    TO_CHAR(REQNOTETIME,'HH24:MI:SS')REQNOTETIMET,
                    TO_CHAR(REQNOTETIME,'YYYY-MM-DD')REQNOTETIMED,
                    TO_CHAR(REQTIMEINS,'HH24:MI DD.MM.YYYY')REQTIMEINS,
                    TO_CHAR(REQTIMEINS,'HH24:MI:SS')REQTIMEINST,
                    TO_CHAR(REQTIMEINS,'YYYY-MM-DD')REQTIMEINSD,
                    REQNOTE
                    FROM REQUEST.REQUESTNOTE
                    WHERE REQID=:REQID";
    $qNote = OCIParse($os,$sqlNote);
    OCIBindByName($qNote,"REQID",$reqId);
    OCIExecute($qNote);

    if(OCIFetch($qNote)){
      $noteTime[0]=OCIResult($qNote,'REQNOTETIMET');
      $noteTime[1]=OCIResult($qNote,'REQNOTETIMED');
      $noteTimeIns[0]=OCIResult($qNote,'REQTIMEINST');
      $noteTimeIns[1]=OCIResult($qNote,'REQTIMEINSD');

      $reqNote=array(
        'noteTime' => OCIResult($qNote,'REQNOTETIME'),
        'noteTimeIns' => OCIResult($qNote,'REQTIMEINS'),
        'noteTimeArr'=>$noteTime,
        'noteTimeInsArr'=>$noteTimeIns,
        'noteUserId' => OCIResult($qNote,'USERID'),
        'noteUserName'=>OCIResult($qNote,'USERNAME'),
        'noteAdminId' => OCIResult($qNote,'ADMINID'),
        'noteAdminName'=>OCIResult($qNote,'ADMINNAME'),
        'reqNote'=>ClobToStr(OCIResult($qNote,'REQNOTE'))
      );
    }else{
      $reqNote=0;
    }
    OCIFreeStatement($qNote);
// Request Note End

// Request Transfer Begin
    $sqlTrans = "SELECT 
                    USERID,
                    USERS.USERNAME(USERID) USERNAME,
                    TO_CHAR(TRANSTIME,'HH24:MI DD.MM.YYYY')TRANSTIME,
                    TO_CHAR(TRANSTIME,'HH24:MI')TRANSTIMET,
                    TO_CHAR(TRANSTIME,'YYYY-MM-DD')TRANSTIMED,
                    TRANSNOTE,
                    REQTRANSID,
                    REQTRANSNUM
                    FROM REQUEST.REQUESTTRANSFER
                    WHERE REQID=:REQID";
    $qTrans = OCIParse($os,$sqlTrans);
    OCIBindByName($qTrans,"REQID",$reqId);
    OCIExecute($qTrans);

    if(OCIFetch($qTrans)){

      $transTime[0]=OCIResult($qTrans,'TRANSTIMET').":00";
      $transTime[1]=OCIResult($qTrans,'TRANSTIMED');

      $arrTrans=array(
        'transTime' => OCIResult($qTrans,'TRANSTIME'),
        'transTimeArr'=>$transTime,
        'transUserId' => OCIResult($qTrans,'USERID'),
        'transUserName'=>OCIResult($qTrans,'USERNAME'),
        'transNote'=>ClobToStr(OCIResult($qTrans,'TRANSNOTE')),
        'transReqId' => OCIResult($qTrans,'REQTRANSID'),
        'transReqNum'=>OCIResult($qTrans,'REQTRANSNUM')
      );
    }else{
      $arrTrans=0;
    }
    OCIFreeStatement($qTrans);
// Request Transfer End

    $reqTRepB[0]=OCIResult($q,'REQTREPBT').":00";
    $reqTRepB[1]=OCIResult($q,'REQTREPBD');
    $reqTRepE[0]=OCIResult($q,'REQTREPET').":00";
    $reqTRepE[1]=OCIResult($q,'REQTREPED');
    $reqTSub[0]=OCIResult($q,'REQTSUBT').":00";
    $reqTSub[1]=OCIResult($q,'REQTSUBD');
    $reqTime[0]=OCIResult($q,'REQTIMET');
    $reqTime[1]=OCIResult($q,'REQTIMED');

		$arr[$i]=array(
      'id'=>OCIResult($q,'REQID'),
      'reqId'=>OCIResult($q,'REQID'),
      'reqNum'=>OCIResult($q,'REQNUM'),
      'reqCatId'=>OCIResult($q,'REQCATID'),
      'reqCatName'=>$reqCatName,
      'reqTSub'=>OCIResult($q,'REQTSUB'),
      'reqTRepB'=>OCIResult($q,'REQTREPB'),
      'reqTRepE'=>OCIResult($q,'REQTREPE'),
      'reqTSubArr'=>$reqTSub,
      'reqTRepBArr'=>$reqTRepB,
      'reqTRepEArr'=>$reqTRepE,
      'reqTime'=>OCIResult($q,'REQTIME'),
      'reqTimeArr'=>$reqTime,
      'reqContW'=>ClobToStr(OCIResult($q,'REQCONTW')),
      'reqAgr'=>OCIResult($q,'REQAGR'),
      'reqAgrDiv'=>$arrAgr,
      'reqCrash'=>OCIResult($q,'REQCRASH'),
      'reqStatusId'=>OCIResult($q,'REQSTATUSID'),
      'reqStatusName'=>$reqStatusName,
      'isNss'=>OCIResult($q,'ISNSS'),
      'arrEq'=>$arrEqSearch,
      'userId'=>OCIResult($q,'USERID'),
      'userName'=>OCIResult($q,'USERNAME'),
      'adminName'=>OCIResult($q,'ADMINNAME'),
      'adminId'=>OCIResult($q,'ADMINID'),
      'approveArr'=>$arrApprove,
      'onArr'=>$arrOn,
      'offArr'=>$arrOff,
      'reqTrans'=>$arrTrans,
      'reqDel'=>$reqDel,
      'noteArr'=>$reqNote,
      'isTrans'=>OCIResult($q,'ISTRANS')
    );
		$i++;
	}

	OCIFreeStatement($q);
	echo json_encode($arr);
?>
