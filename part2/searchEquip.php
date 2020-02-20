<?php
	include "/srv/www/portal/ini.php";
	include $rootd."connectBase.php";
	include $rootd."lib.php";

	$userId=getUserFromSession($os);
	if ($userId < 0){
		echo "PORTAL_USER_LOGOUT";
		return;
	}

	$os2 = @oci_connect("SYSTEM","guard",$NSDIP.":1521/ntd.bil");
	if (!$os2){
		print_r("SERVER_ERROR");
		return;
	}
	$ss='';

	$systemId=-1;
	$eId=-1;
	if (isset($_POST["elementId"])){
		$eId=$_POST["elementId"];
	}
	if (isset($_POST["systemId"])){
		$systemId=$_POST["systemId"];
	}
	if (!isset($_POST["noAll"])){
		$ss='*';		
	}
	
	if (isset($_POST["searchTerms"])&&$_POST["searchTerms"]){
			$ss=$_POST["searchTerms"];
	}
	else{
		if (isset($_POST["noAll"])){
			return ;
		}		
	}

	$divisionId=-1;
	if (isset($_POST["filterDivisionId"])){
		$divisionId=$_POST["filterDivisionId"];
	}

	if ($eId!=-1){
		$sql="SELECT EL.ELEMENTID, EL.POSITION, EL.NAME, DI.DIVISIONSHORTNAME, SD.SYSTEMNAME, 
						UU.UNITENAME, EL.ISCHECKED, DEFECT.FULLSYSTEMNAME(EL.SYSTEMID)FULLSYSTEMNAME,
						DEFECT.CURRENTEQUIPMENTSERIAL(EL.ELEMENTID)SERIAL, DEFECT.CURRENTEQUIPMENTMODEL(EL.ELEMENTID)MODEL,
						TO_CHAR(DEFECT.CURRENTEQUIPMENTSTARTDATE(EL.ELEMENTID),'DD.MM.YYYY')STARTDATE, 
						EL.DIVISIONID, EL.ISCHECKED2, DEFECT.ELEMENTDEFECTCOUNT(EL.ELEMENTID)DEFCNTD 
					FROM DEFECT.ELEMENT EL, DEFECT.SYSTEM SD, DEFECT.DIVISION DI,DEFECT.UNITUNITE UU 
					WHERE EL.SYSTEMID=SD.SYSTEMID(+) 
						AND EL.DIVISIONID=DI.DIVISIONID(+) 
						AND EL.UNITUNITEID=UU.UNITUNITEID(+) 
						AND EL.DELETED=0 
						AND EL.ELEMENTID=:ELEMENTID";
	    $psql=ociparse($os2,$sql);

		ocibindbyname($psql,"ELEMENTID",$eId,-1);
	}
	else{ 
		if ($ss=='*'){
			$sql="SELECT EL.ELEMENTID,EL.POSITION,EL.NAME,DI.DIVISIONSHORTNAME,SD.SYSTEMNAME,UU.UNITENAME,
							EL.ISCHECKED,DEFECT.FULLSYSTEMNAME(EL.SYSTEMID)FULLSYSTEMNAME,DEFECT.CURRENTEQUIPMENTSERIAL(EL.ELEMENTID)SERIAL,
							DEFECT.CURRENTEQUIPMENTMODEL(EL.ELEMENTID)MODEL,TO_CHAR(DEFECT.CURRENTEQUIPMENTSTARTDATE(EL.ELEMENTID),'DD.MM.YYYY')STARTDATE,
							EL.DIVISIONID,EL.ISCHECKED2,DEFECT.ELEMENTDEFECTCOUNT(EL.ELEMENTID)DEFCNTD FROM DEFECT.ELEMENT EL, DEFECT.SYSTEM SD, 
							DEFECT.DIVISION DI,DEFECT.UNITUNITE UU 
						WHERE EL.SYSTEMID=SD.SYSTEMID(+) 
						AND EL.DIVISIONID=DI.DIVISIONID(+) 
						AND EL.UNITUNITEID=UU.UNITUNITEID(+) 
						AND EL.DELETED=0";
		}
		else if ($ss!=''){
       $sql="SELECT EL.ELEMENTID,EL.POSITION,EL.NAME,DI.DIVISIONSHORTNAME,SD.SYSTEMNAME,
							UU.UNITENAME,EL.ISCHECKED,DEFECT.FULLSYSTEMNAME(EL.SYSTEMID)FULLSYSTEMNAME,
							DEFECT.CURRENTEQUIPMENTSERIAL(EL.ELEMENTID)SERIAL,DEFECT.CURRENTEQUIPMENTMODEL(EL.ELEMENTID)MODEL,
							TO_CHAR(DEFECT.CURRENTEQUIPMENTSTARTDATE(EL.ELEMENTID),'DD.MM.YYYY')STARTDATE,EL.DIVISIONID,
							EL.ISCHECKED2,DEFECT.ELEMENTDEFECTCOUNT(EL.ELEMENTID)DEFCNTD FROM DEFECT.ELEMENT EL, DEFECT.SYSTEM SD, 
							DEFECT.DIVISION DI,DEFECT.UNITUNITE UU 
						WHERE
							EL.SYSTEMID=SD.SYSTEMID(+) 
						AND EL.DIVISIONID=DI.DIVISIONID(+) 
						AND EL.UNITUNITEID=UU.UNITUNITEID(+) 
						AND (NLS_UPPER(EL.POSITION) LIKE '%'||NLS_UPPER(:ST)||'%' OR NLS_UPPER(EL.NAME) LIKE '%'||NLS_UPPER(:ST)||'%')  
						AND EL.DELETED=0";

		}

		if ($systemId!=-1){
			$sql.=' AND el.SYSTEMID in (select systemid from defect.system start with systemid=:SYSTEMID connect by prior systemid=systemparentid) ';		
		}
		$sql.=' ORDER BY el.POSITION ';

    	$psql=ociparse($os2,$sql);

		if ($systemId!=-1){
				
			ocibindbyname($psql,"SYSTEMID",$systemId,-1);
		}
	
		if ($ss!=''&&$ss!='*'){
			ocibindbyname($psql,"ST",$ss,-1);
		}
	}
	ociexecute($psql);
	$i=0;
	$nRow=0;
	$arr=array();
	while (ocifetch($psql)){
			$qE=ociparse($os2,"SELECT ELEMENTDICID,VALUEDICID FROM DEFECT.ELEMENTCLASSY WHERE ELEMENTID=:ELEMENTID");
			ocibindbyname($qE,'ELEMENTID',ociresult($psql,"ELEMENTID"),-1);
			ociexecute($qE);
			$a=array();
			$j=0;
			while (ocifetch($qE)){
				$a[$j]=array('id'=>ociresult($qE,"ELEMENTDICID"),'vid'=>ociresult($qE,"VALUEDICID"));
				$j++;
			}	
	
			ocifreestatement($qE);

    $arr[$i]=array(
    	'id'=>ociresult($psql,"ELEMENTID"),
			'rn'=>$i + 1,
			'ps'=>ociresult($psql,"POSITION"),
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
			'ec'=>$a,'defC'=>ociresult($psql,"DEFCNTD")
		);
		$i++;
	}
	 
	  ocifreestatement($psql);
	echo json_encode($arr);
?>
