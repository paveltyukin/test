// v. 1
define([
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/dom-style",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/string",
    "dojo/on",
    "dojo/aspect",
    "dojo/keys",
    "dojo/_base/config",
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dijit/registry",
    "dojo/parser",
    "dijit/layout/ContentPane",
    "dojo/store/Memory",
    "dojo/store/JsonRest",
    "dojo/store/Cache",
    "dojo/request",
    "dojo/request/iframe",
    "dojo/json",
    "dojo/dom-form",
    "dojo/cookie",
    "dijit/TooltipDialog",
    "dijit/popup",
    "dijit/Menu",
    "dijit/MenuItem",
    "dojo/_base/declare",
    "dgrid/extensions/DijitRegistry",
    "dgrid/OnDemandGrid",
    "dgrid/Keyboard",
    "dgrid/Selection",
    "dgrid/extensions/ColumnResizer",
    "dijit/focus",
    "dojo/store/Observable",
    "dojo/_base/array",
    "dojo/query",
    "dstore/Memory",
    "dstore/Trackable",
    "global/MIFDivisionSelector",
    "global/MIFCalendar",
    "global/MIFSearchField",
    "global/MIFUserSelector",
    "components/js/MIFDateField",
    "dijit/form/Select",
    "dijit/form/Button",
    "components/js/MIFDocSearch",
    "components/js/MIFFileSelect",
    "actions/js/MIFEditAction",
    "js/MIFGlobal",
    "dojo/date",
    "dojo/date/locale",
    "dojo/date/stamp",
    "dojo/mouse",
    "dojo/when",
    "cob/main/KSLayout",
    "dojo/NodeList",
    "dojo/NodeList-dom",
    "dojo/NodeList-fx",
    "dojo/NodeList-html",
    "dojo/NodeList-manipulate",
    "dojo/NodeList-traverse"
  ],
  function(dom,domAttr,domStyle,domClass,domConstruct,domGeometry,string,on,aspect,keys,config,lang,baseFx,registry,parser,ContentPane,memory,
    jsonrest,cache,request,iframe,JSON,domForm,cookie,tooltipDialog,popup,Menu,menuItem,declare,DijitRegistry,OnDemandGrid,
    Keyboard,Selection,ColumnResizer,focus,Observable,array,query,DstoreMemory,Trackable,MIFDivisionSelector,MIFCalendar,
    MIFSearchField,MIFUserSelector,MIFDateField,Select,Button,MIFDocSearch,MIFFileSelect,MIFEditAction,globals,date,locale,stamp,mouse,when,KSLayout){

    let
      prefix = "",
      baseName = "",
      reqRo = "",
      store = null,
      sysdate = null,
      firstdate = null,
      divName = '',
      paletteShown = false,
      sessionId = '',
      userFio = '',
      grid = null,
      role = 0,
      isAdmin = 0,
      isEditor = 0,
      isViewer = 0,
      isNSS = 0,
      isGuest = 1,
      selectInstId = -1,
      fullFio = '',
      userId = -1,
      menu = null,
      menuItemEdit = null,
      currentActiveMenu = null,
      menuItemDelete = null,
      menuItemTrans = null,
      currentMode = 0,
      rowMenu = null,
      divisionId = -1,
      isLoaded = false,
      subDiv = null,
      addEqSearchGrid,
      addNewReqGrid,
      addEqSearchLayout = [
        {
          field:'ps',
          label:'Позиция:',
          sortable:false
        },
        {
          field:'nm',
          label:'Название:',
          sortable:false
        },
        {
          field:'un',
          label:'Блок:',
          sortable:false
        },
        {
          field:'fsn',
          label:'Система:',
          sortable:false
        }
      ],
      eqFullSearch = {},
      addNewReqDivAddAgrId = 0,
      trim = lang.trim,
      storeFilAgr = {},
      reqDateTT = false,
      storeFilUnite = {},
      storeFilDiv = {},
      infoPanelBoolean = false,
      crutchSaveAppr = 0,
      reqRow,
      reqCrutch = 0;


    startup = function(){
      parser.parse().then(
        function(){
          initUi();
          aspect.after(registry.byId("leftPanel"),"resize",storeWidth);
        }
      );
    };

    initUi = function(){
      setNullParams();

      on(dom.byId("leftPanel"),"click",menuClick);
      on(dom.byId("logoutBtn"),"click",doLogout);
      on(dom.byId('fioField'),"click",showHideUserPanel);

      // Button +
      on(dom.byId("addNewReqButton"),"click",function(e){
        initNewRequest();
      });

      // Button Cancel
      on(dom.byId("addEqSearchButtonC"),"click",function(){
        hideDialog('addEqSearchDialog')
      });
      on(dom.byId("addNewReqButtonC"),"click",function(){
        hideDialog('addNewReqDialog')
      });

      // Button Search
      on(dom.byId("addEqSearchButtonS"),"click",buildAddEqSearchGrid);

      // Button Save
      on(dom.byId("addNewReqButtonS"),"click",saveReq);
      on(dom.byId("addNewEqS"),"click",saveNewEq);
      on(dom.byId("reqOffButtonS"),'click',saveFinishReqOff);
      on(dom.byId('reqOnButtonS'),'click',saveFinishReqOn);
      on(dom.byId('reqTransferSave'),'click',saveReqTransNote);
      on(query('#reqDeleteSave'),'click',saveDeleteReq);

      // Button Add
      on(dom.byId("addEqSearchButtonA"),"click",function(e){
        changeGlobalDialog("addEqSearchDialog","addNewEqDialog")
      });
      on(dom.byId("addNewReqButtonAY"),"click",function(e){
        changeGlobalDialog("addNewReqDialog","addEqSearchDialog")
      });

      // Button Delete
      on(dom.byId("addNewReqButtonD"),"click",deleteEqFromNewReq);

      checkLogin();
      createAddEqSearchGrid();
      createAddNewRequestGrid();
      buildReqSelect();
      buildReqCheckBoxCrash();
      buildReqCheckBoxAgr();
      buildReqRepTimeBox();
      buildReqRepDateBox("addNewReqRepBD");
      buildReqRepDateBox("addNewReqRepED");
      buildReqRepDateBox("addNewReqSubD");
      buildReqTextBox();

      buildReqRepDateBox("reqOnDTB");
      buildReqRepDateBox("reqOffDTB");
      setStyleWidget("reqOnTTB");
      setStyleWidget("reqOffTTB");


      getFilterAgreement();
      getUnitUnite();
      getDivisions();
      setReqGridParams();
      initReqCalendar();
      setReqParamsConfirmDialog();


      on(dom.byId('reqDiffAddB'),'click',function(){
        buildReqDiffAddDiv();
      });
    };

    initNewRequest = function(){
      clearMemHide();
      showDialog("addEqSearchDialog");
    };

    setNullParams = function(){
      clearMemHide();

      // Div add Agreement
      addNewReqDivAddAgrId += 1;
      on(dom.byId("addNewReqDivAddAgr"),"click",function(evt){
        addNewReqDivAddAgrId += 1;
        domConstruct.place(templateAddAgr(addNewReqDivAddAgrId,0),dom.byId("addNewReqDivAddAgrConstr"),"last");

        parser.parse(dom.byId("addNewReqDivAddAgrConstr")).then(function(){
          fillDivisions("agrSelect" + addNewReqDivAddAgrId,0);
        });

        on(query("div.addNewReqDeleteDiv"),"click",function(e){
          domConstruct.destroy(e.target.parentNode.parentNode.parentNode);
        });
      });
    };

    newRequest = function(){
      query('#reqTrans').val('');
      buildDialogReqAdd();
    };

    editRequest = function(row){

    };

    setParamsStyleReqAdd = function(){
      // Show Part
      reqToShow(
        'addNewReqAgrCBDiv',
        'addNewReqDivAddAgrParent',
        'addNewReqSecT',
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'addNewReqPeriodDivCB',
        'addNewReqPeriodDiv'
      );
      // Hide Part
      reqToHide(
        'viewReqPart',
        'viewNewReqTable'
      );
    };

    setNullsParamsReqAdd = function(){
      registry.byId('addNeqReqCategory').set('value',1);
      registry.byId('addNewReqContentWork').set('value','');
      registry.byId('addNewReqPeriodCB').set('checked',false);
      registry.byId('reqCrashReadyHId').set('value','00');
      registry.byId('reqCrashReadyMId').set('value','00');

      query('#addNewReqDivAddAgrConstr').empty();
      registry.byId('addNewReqAgrCB').set('checked',false);
      query('reqDiffUserId').attr('data-req-userid','');
      query('reqDiffUserId').html('');
    };

    setNullsParamsReqAddDisabled = function(){
      registry.byId('addNewReqAgrCB').set('disabled',false);
      registry.byId('reqCrashReadyMId').set('disabled',false);
      registry.byId('reqCrashReadyHId').set('disabled',false);
      registry.byId('addNewReqPeriodCB').set('disabled',false);
      registry.byId('addNewReqContentWork').set('disabled',false);
      registry.byId('addNeqReqCategory').set('disabled',false);
    };

    /*
        1	Новая - New
        2	Утверждённая - Approve
        3	Открытая - On
        4	Закрытая - Off
        5	Отклонённая - Decline
        6	Примечание - Note
        7	Продлённая - Transfer
        8	Удалённая - Delete
    */

    viewRequestGrid = function(row){

      switch(parseInt(row.data.reqStatusId)){
        case 1:
          buildDialogReqNew(row);
          break;
        case 2:
          buildDialogReqApprove(row);
          break;
        case 3:
          buildDialogReqOn(row);
          break;
        case 4:
          buildDialogReqOff(row);
          break;
        case 5:
          buildDialogReqDecline(row);
          break;
        case 6:
          buildDialogReqNote(row);
          break;
        case 7:
          buildDialogReqTransfer(row);
          break;
        case 8:
          buildDialogReqDelete(row);
          break;
        default:
          myAlert("Пожалуйста, перезагрузите страницу");
          break;
      }

      query('#addNewReqDialog_title').html(
        'Заявка №' + row.data.reqNum +
        ' (' + row.data.reqStatusName + ')'
      );
      eqFullSearch.items = row.data.arrEq;
      buildAddNewReqGrid(eqFullSearch.items);
    };

    buildDialogReqAdd = function(){

      if(isAdmin || isNSS){
        reqToShow('reqDiffLabel');
        reqToShow('reqDiffAddB');
        query('#reqDiffAddB').style({display:'inline-block'});
      }else{
        reqToHide('reqDiffLabel');
      }

      buildReqRepTimeBox();
      query('#addNewReqDialog_title').html('Добавить новую заявку');
      query('#reqDiffSpan').html('Подать заявку от лица:');
      setParamsStyleReqAdd();
      setNullsParamsReqAdd();
      setNullsParamsReqAddDisabled();
      showDialog("addNewReqDialog");
    };

    buildDialogReqNew = function(row){
      reqToHide(
        'addNewReqSecT',
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'reqAppViewTable',
        'reqOnEditViewTable',
        'reqOnEditTable',
        'reqOnViewTable',
        'reqOffEditViewTable',
        'reqOffEditTable',
        'reqOffViewTable'
      );
      reqToShow('reqAppEditTable','viewReqPart','viewNewReqTable');

      // Set Params Row
      setParamsReqNew(row);

      registry.byId("reqAppEditCB").set('checked',false);

      let strApproveReqNote = "";
      crutchSaveAppr = 0;
      on(query("#reqApproveButtonS"),'click',function(){
        if(registry.byId("reqAppEditCB").checked){
          if(registry.byId("reqAppEditSelect").get('value').toString() === "0"){
            registry.byId('approveReqNote').set('value','');
            showDialog('approveNoteDialog');
            query('#approveReqNote').style({height:'100px'});
            query('#approveReqNote').attr({rows:'5'});
            on(query("#userSelectorApply"),'click',function(){
              if(trim(registry.byId('approveReqNote').get('value')) !== ''){
                strApproveReqNote = registry.byId('approveReqNote').get('value');
                hideDialog('approveNoteDialog');
                saveApproveRequest(row,strApproveReqNote,crutchSaveAppr);
                crutchSaveAppr++;
              }else{
                myAlert('Введите причину');
                return false;
              }
            });
          }else{
            saveApproveRequest(row,"",crutchSaveAppr);
            crutchSaveAppr++
          }
        }else{
          myAlert("Подтвердите информацию");
          return false;
        }
      });

      showDialog("addNewReqDialog");
    };

    buildDialogReqApprove = function(row){
      reqToHide(
        'addNewReqSecT',
        'reqAppEditTable',
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'reqOnEditTable',
        'reqOnViewTable',
        'reqOffEditViewTable',
        'reqOffEditTable',
        'reqOffViewTable'
      );
      reqToShow(
        'viewNewReqTable',
        'viewReqPart',
        'reqOnEditViewTable',
        'reqAppViewTable',
        'reqOnEditTable'
      );

      registry.byId("reqOnConfirm").set('checked',false);

      setParamsReqNew(row);
      setParamsReqApprove(row);


      showDialog("addNewReqDialog");
    };

    saveFinishReqOn = function(){
      if(registry.byId("reqOnConfirm").checked){
        saveOnRequest(reqRow);
      }else{
        myAlert("Подтвердите информацию");
      }
    };

    buildDialogReqOn = function(row){

      reqToHide(
        'addNewReqSecT',
        'reqAppEditTable',
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'reqOnEditTable',
        'reqOffViewTable'
      );

      reqToShow(
        'reqOnEditViewTable',
        'reqOffEditViewTable',
        'viewNewReqTable',
        'viewReqPart',
        'reqAppViewTable',
        'reqOnViewTable',
        'reqOffEditTable'
      );

      setParamsReqNew(row);
      setParamsReqApprove(row);
      setParamsReqOn(row);
      registry.byId("reqOffConfirm").set('checked',false);

      showDialog("addNewReqDialog");
    };

    saveFinishReqOff = function(){
      if(registry.byId("reqOffConfirm").checked){
        saveOffRequest(reqRow);
      }else{
        myAlert("Подтвердите информацию");
      }
    };

    buildDialogReqOff = function(row){
      reqToHide(
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'addNewReqSecT',
        'reqOffEditTable',
        'reqOnEditTable',
        'reqAppEditTable'
      );

      reqToShow(
        'reqOnEditViewTable',
        'reqOnViewTable',
        'reqOffEditViewTable',
        'reqOffViewTable',
        'viewReqPart',
        'viewNewReqTable',
        'reqAppViewTable'
      );

      // Set Params for Request New
      setParamsReqNew(row);
      // Set Params for Request Approve
      setParamsReqApprove(row);
      // Set Params for Request On
      setParamsReqOn(row);
      // Set Params for Request Off
      setParamsReqOff(row);

      showDialog("addNewReqDialog");
    };

    buildDialogReqDecline = function(row){
      reqToHide(
        'addNewReqButtonCS',
        'addNewReqButtonAYD',
        'addNewReqSecT',
        'reqOffEditTable',
        'reqOnEditTable',
        'reqAppEditTable',
        'reqOnEditViewTable',
        'reqOnViewTable',
        'reqOffEditViewTable',
        'reqOffViewTable',
      );

      reqToShow(
        'viewReqPart',
        'viewNewReqTable',
        'reqAppViewTable'
      );

      // Set Params for Request New
      setParamsReqNew(row);
      // Set Params for Request Approve
      setParamsReqDecline(row);

      showDialog("addNewReqDialog");
    };

    buildDialogReqNote = function(row){

      showDialog("addNewReqDialog");
    };

    buildDialogReqTransfer = function(row){
      query('#reqDiffUserId').attr('data-req-userId',row);

      showDialog("addNewReqDialog");
    };

    buildDialogReqDelete = function(row){

      showDialog("addNewReqDialog");
    };

    /*
    build Different Params
     */

    buildReqDiffAddDiv = function(){
      let str = "";
      array.forEach(storeFilDiv['data'],function(item){
        if(parseInt(item.id) !== -1){
          str += '<div><span data-req-divId="' + item.id + '" data-req-divName="' + item.nm + '">' + item.nm + '</span></div>';
        }
      });
      query('#reqDiffPanel div.reqDiffDivFill').html(str);
      on(query('#reqDiffPanel div.reqDiffDivFill span'),'click',function(e){
        let userFromDiv = {
          divId:domAttr.get(e.target,'data-req-divId'),
          divName:domAttr.get(e.target,'data-req-divName')
        };
        myPost(prefix + "/dictionaries/userFromDiv.php",{jdata:JSON.stringify(userFromDiv)},function(adata){


          buildReqDiffAddUser(JSON.parse(adata));
          hideDialog('reqDiffAddDialog');
        });
      });
      showDialog('reqDiffAddDialog');
    };

    buildReqDiffAddUser = function(data){
      let str = "";
      array.forEach(data,function(item){
        str += '<div><span data-req-userId="' + item.userId + '" data-req-userName="' + item.userName + '">' + item.userFIO + '</span></div>';
      });
      query('#reqDiffUserPanel div.reqDiffUserFill').html(str);
      on(query('#reqDiffUserPanel div.reqDiffUserFill span'),'click',function(e){
        query('#reqDiffUserId').attr('data-req-userId',domAttr.get(e.target,'data-req-userId'));
        query('#reqDiffUserId').html(domAttr.get(e.target,'data-req-userName'));
        hideDialog('reqDiffAddUserDialog');
        showDialog('addNewReqDialog');
      });
      showDialog('reqDiffAddUserDialog');
    };

    /*
    Set Dialog Params
     */

    setParamsReqAdd = function(row,act){
      let reqAct = 0;
      if(act === "copy"){
        reqAct = 0;
      }else{
        reqAct = 1;
      }
      query('#reqTrans').val('');
      query('#addNewReqButtonAYD').style({"display":"none"});
      // Вставить из row в новую заявку
      registry.byId('addNeqReqCategory').set('value',row.data.reqCatId);
      registry.byId('addNeqReqCategory').set('disabled',true);
      registry.byId('addNewReqContentWork').set('value',row.data.reqContW);
      registry.byId('addNewReqContentWork').set('disabled',true);

      if(row.data.reqCrash === 'REQUEST'){
        query('#addNewReqPeriodDiv').style({"display":"none"});
        query('#addNewReqPeriodDivCB').style({"display":"block"});
        registry.byId('addNewReqPeriodCB').set('checked',true);
        registry.byId('addNewReqPeriodCB').set('disabled',true);
        registry.byId('reqCrashReadyMId').set('disabled',false);
        registry.byId('reqCrashReadyHId').set('disabled',false);
      }else{
        query('#addNewReqPeriodDiv').style({"display":"block"});
        query('#addNewReqPeriodDivCB').style({"display":"none"});
        registry.byId("reqCrashReadyHId").set('value',row.data.reqCrash.split(':')[0]);
        registry.byId("reqCrashReadyMId").set('value',row.data.reqCrash.split(':')[1]);
        registry.byId('reqCrashReadyMId').set('disabled',true);
        registry.byId('reqCrashReadyHId').set('disabled',true);
        registry.byId('addNewReqPeriodCB').set('checked',false);
        registry.byId('addNewReqPeriodCB').set('disabled',false);
      }

      eqFullSearch.items = row.data.arrEq;
      buildAddNewReqGrid(eqFullSearch.items);

      if(parseInt(row.data.reqAgr) === 0){
        registry.byId('addNewReqAgrCB').set('checked',true);
        registry.byId('addNewReqAgrCB').set('disabled',true);
      }else{
        registry.byId('addNewReqAgrCB').set('checked',false);
        registry.byId('addNewReqAgrCB').set('disabled',false);
        query('#addNewReqDivAddAgrConstr').empty();
        addNewReqDivAddAgrId += 1;
        array.forEach(row.data.reqAgrDiv,function(item,idx,arr){

          addNewReqDivAddAgrId += 1;
          domConstruct.place(templateAddAgr(addNewReqDivAddAgrId,reqAct),dom.byId("addNewReqDivAddAgrConstr"),"last");
          parser.parse(dom.byId("addNewReqDivAddAgrConstr")).then(function(){
            fillDivisions("agrSelect" + addNewReqDivAddAgrId,reqAct,item.DivId);
          });
          registry.byId("agrSelect" + addNewReqDivAddAgrId).set('value',item.DivId);
          on(query("div.addNewReqDeleteDiv"),"click",function(e){
            domConstruct.destroy(e.target.parentNode.parentNode.parentNode);
          });
        });
      }

      if(isNSS || isAdmin){
        if(parseInt(reqAct) === 0){
          query('#reqDiffSpan').html('Подать заявку от лица:');
          reqToShow('reqDiffAddB');
          query('#reqDiffAddB').style({display:'inline-block'});
        }else{
          query('#reqDiffSpan').html('Продлить заявку от лица:');
          reqToHide('reqDiffAddB');
        }

        query('#reqDiffUserId').attr('data-req-userId',row.data['userId']);
        query('#reqDiffUserId').html(row.data['userName']);
      }
    };

    setParamsReqAddTrans = function(row){
      when(setParamsReqAdd(row,"trans"),function(){
        query('#reqTrans').val(row.id);

        query('#addNewReqDialog_title').html('Продлить заявку № ' + row.data['reqNum']);
      });
    };

    setParamsReqNew = function(row){
      query('#viewNewReqCategory').html(row.data.reqCatName);
      query('#viewNewReqContentWork').html(row.data.reqContW);
      if(row.data.reqCrash === "REQUEST"){
        query('#viewNewReqPeriod').html('Срок заявки');
      }else{
        query('#viewNewReqPeriod').html(
          '<span>Часы:&nbsp;</span>' +
          '<span>' + row.data.reqCrash.split(":")[0] + '</span><br/>' +
          '<span>Минуты:&nbsp;</span>' +
          '<span>' + row.data.reqCrash.split(":")[1] + '</span>'
        );
      }

      if(parseInt(row.data.reqAgr) === 1){
        let reqAgrArr = [];
        array.forEach(row.data.reqAgrDiv,function(item,idx,arr){
          reqAgrArr.push(item.DivName);
        });
        query('#viewNewReqAgr').html(reqAgrArr.join(", "));
      }else{
        query('#viewNewReqAgr').html('Согласование не требуется');
      }

      query('#viewNewReqRepB').html(
        setReqTimeParams(row.data.reqTRepB,'&nbsp;С&nbsp;')
      );
      query('#viewNewReqRepE').html(
        setReqTimeParams(row.data.reqTRepE,'До&nbsp;')
      );
      query('#viewNewReqSub').html(
        setReqTimeParams(row.data.reqTSub,'')
      );
      query('#viewNewReqTime').html(
        setReqTimeParams(row.data.reqTime,'')
      );
      query('#viewNewReqFIO').html(
        '<span>' + row.data.userName + '</span>'
      );

      on(registry.byId("reqAppEditSelectGIS"),"change",function(value){
        if(parseInt(value) === 58986){
          query("#reqAppEditIoGIS").style({display:"none"});
        }else{
          query("#reqAppEditIoGIS").style({display:"inline"});
        }
      });

      if(parseInt(row.data['isNss']) === 1){
        reqToShow('reqViewDiffLabel');
        query('#reqViewDiffDivId').html(row.data['adminName']);
      }else{
        reqToHide('reqViewDiffLabel');
      }
    };

    setParamsReqDecline = function(row){
      query('#reqAppViewSelect').html("отклонил");
      query('#reqAppViewSelectGIS').html(
        getUserName(row.data.approveArr.approveGIS)
      );
      if(parseInt(row.data.approveArr.approveGISId) !== 58986){
        query('#reqAppViewGIS').html('и.о. ');
      }else{
        query('#reqAppViewGIS').html('');
      }
      query('#reqAppViewUserFIO').html(
        row.data.approveArr.approveUserName
      );
      query('#reqAppViewTime').html(
        setReqTimeParams(row.data.approveArr.approveTime,'')
      );
    };

    setParamsReqApprove = function(row){
      query('#reqAppViewSelect').html("утвердил");
      query('#reqAppViewSelectGIS').html(
        getUserName(row.data.approveArr.approveGIS)
      );
      if(parseInt(row.data.approveArr.approveGISId) !== 58986){
        query('#reqAppViewGIS').html('и.о. ');
      }else{
        query('#reqAppViewGIS').html('');
      }
      query('#reqAppViewUserFIO').html(
        row.data.approveArr.approveUserName
      );
      query('#reqAppViewTime').html(
        setReqTimeParams(row.data.approveArr.approveTime,'')
      );
    };

    setParamsReqOn = function(row){
      query('#reqOnViewTime').html(
        setReqTimeParams(row.data.onArr.onTime,'')
      );
      query('#reqOnViewTimeIns').html(
        setReqTimeParams(row.data.onArr.onTimeIns,'')
      );
      query('#reqOnViewFIO').html(
        row.data.onArr.onUserName
      );
    };

    setParamsReqOff = function(row){

      query('#reqOffViewTime').html(
        setReqTimeParams(row.data.offArr.offTime,'')
      );
      query('#reqOffViewTimeIns').html(
        setReqTimeParams(row.data.offArr.offTimeIns,'')
      );
      query('#reqOffViewFIO').html(
        row.data.offArr.offUserName
      );
    };

    /*
    Save Delete Request
     */

    saveDeleteReq = function(){
      if(trim(registry.byId("reqDelete").value) !== ""){
        hideDialog("reqDeleteDialog");
        globals.showHideWaitPanel(true);
        myPost(prefix + "/main/saveDelete.php",{
          jdata:JSON.stringify({
            reqId:rowMenu.id,
            deleteNote:registry.byId("reqDelete").value,
            userId:userId,
            reqStatusId:8
          })
        },function(adata){

          when(filterOutput(currentMode),function(){
            confirmDialog(adata,"удалена");
          });
        });
      }else{
        myAlert('Введите причину "Удаления" заявки');
        query('#reqDelete').addClass('reqError');
        return false;
      }
    };

    /*
    save New Equipment
     */

    saveNewEq = function(){
      if(trim(registry.byId('edElPosition').get('value')) === ""){
        myAlert("Вы не указали позицию оборудования");
        return false;
      }
      if(trim(registry.byId('edElName').get('value')) === ""){
        myAlert("Вы не указали наименование оборудования");
        return false;
      }
      if(parseInt(registry.byId('edElUnitUniteId').get('value')) === -1){
        myAlert("Вы не выбрали энергоблок");
        return false;
      }
      if(parseInt(registry.byId('edElDivisionId').get('value')) === -1){
        myAlert("Вы не выбрали владельца оборудования");
        return false;
      }

      let newEq = {
        "elementId":-1,
        "edElPosition":registry.byId('edElPosition').get('value'),
        "edElName":registry.byId('edElName').get('value'),
        "edElUnitUniteId":registry.byId('edElUnitUniteId').get('value'),
        "edElDivisionId":registry.byId('edElDivisionId').get('value'),
      };

      myPost(prefix + "/main/saveNewEq.php",{jdata:JSON.stringify(newEq)},function(adata){


        checkLogout(adata);
        myAlert("Оборудование успешно добавлено");
      });
    };

    /*
    save Off Request
     */

    saveOffRequest = function(row){

      reqCrutch = 0;
      let reqOffTimeT,reqOffTimeD;
      if(query('input[name="reqOffTTB"]')[0].value.charAt(0) === "T"){
        reqOffTimeT = query('input[name="reqOffTTB"]')[0].value.substr(1);
      }

      if(query('input[name="reqOffDTB"]')[0].value){
        reqOffTimeD = query('input[name="reqOffDTB"]')[0].value;
      }

      let reqSaveOff = {};
      reqSaveOff.reqId = reqRow['id'];
      reqSaveOff.userId = userId;
      if(reqOffTimeT && reqOffTimeD){
        reqSaveOff.reqOffTime = reqOffTimeD.toString() + " " + reqOffTimeT.toString();
      }else{
        myAlert("Вы не заполнили Время и дату");
        return;
      }

      if(reqCrutch > 0){
        return
      }
      myPost(prefix + "/main/saveOff.php",{jdata:JSON.stringify(reqSaveOff)},function(adata){
        reqCrutch++;


        filterOutput(currentMode);
        hideDialog("addNewReqDialog");
        clearMemHide();

        let dataParse = JSON.parse(adata);
        let dataStr = 'Заявка № ' + dataParse.reqNum + ' успешно открыта';
        query('#reqContentPane').html(dataStr);
        showDialog('reqCommonDialog');
      });
    };

    /*
    save Note Request
     */

    saveNoteRequest = function(row){
      let reqNoteTimeT,reqNoteTimeD;
      if(query('input[name="reqNoteTTB"]')[0].value.charAt(0) === "T"){
        reqNoteTimeT = query('input[name="reqNoteTTB"]')[0].value.substr(1);
      }

      if(query('input[name="reqNoteDTB"]')[0].value){
        reqNoteTimeD = query('input[name="reqNoteDTB"]')[0].value;
      }

      let reqSaveNote = {};
      reqSaveNote.reqId = row.id;
      reqSaveNote.userId = userId;
      reqSaveNote.reqNote = userId;
      if(reqNoteTimeT && reqNoteTimeD){
        reqSaveNote.reqNoteTime = reqNoteTimeD.toString() + " " + reqNoteTimeT.toString();
      }else{
        myAlert("Вы не заполнили Время и дату");
        return false;
      }


      hideDialog("addNewReqDialog");
      myPost(prefix + "/main/saveNote.php",{jdata:JSON.stringify(reqSaveNote)},function(adata){

        confirmDialog(adata,'изменена');
        filterOutput(currentMode);
      });
    };

    /*
    save Approve Request
     */

    saveApproveRequest = function(row,str,crutchSaveApr){
      if(crutchSaveApr > 0){
        return false
      }

      let reqSaveApprove = {};
      reqSaveApprove.reqId = reqRow['id'];
      reqSaveApprove.userId = userId;
      reqSaveApprove.isApprove = query('input[name="reqApprove"]').val();
      reqSaveApprove.gisId = query('input[name="reqGISId"]').val();
      reqSaveApprove.approveNote = str;

      myPost(prefix + "/main/saveApprove.php",{jdata:JSON.stringify(reqSaveApprove)},function(adata){

        filterOutput(currentMode);
        hideDialog("addNewReqDialog");
        clearMemHide();

        let dataParse = JSON.parse(adata);
        let dataStr = 'Заявка № ' + dataParse.reqNum + ' успешно ';
        if(parseInt(dataParse['isApprove']) === 1){
          dataStr += 'утверждена';
        }else{
          dataStr += 'отклонена';
        }
        query('#reqContentPane').html(dataStr);
        showDialog('reqCommonDialog');
      });
    };

    /*
    save On Request
     */

    saveOnRequest = function(row){

      reqCrutch = 0;
      let reqOnTimeT,reqOnTimeD;
      if(query('input[name="reqOnTTB"]')[0].value.charAt(0) === "T"){
        reqOnTimeT = query('input[name="reqOnTTB"]')[0].value.substr(1);
      }

      if(query('input[name="reqOnDTB"]')[0].value){
        reqOnTimeD = query('input[name="reqOnDTB"]')[0].value;
      }

      let reqSaveOn = {};
      reqSaveOn.reqId = reqRow['id'];
      reqSaveOn.userId = userId;
      if(reqOnTimeT && reqOnTimeD){
        reqSaveOn.reqOnTime = reqOnTimeD.toString() + " " + reqOnTimeT.toString();
      }else{
        myAlert("Вы не заполнили Время и дату");
        return false;
      }


      if(reqCrutch > 0){
        return false
      }
      myPost(prefix + "/main/saveOn.php",{jdata:JSON.stringify(reqSaveOn)},function(adata){
        reqCrutch++;


        filterOutput(currentMode);
        hideDialog("addNewReqDialog");
        clearMemHide();

        let dataParse = JSON.parse(adata);
        let dataStr = 'Заявка № ' + dataParse.reqNum + ' успешно открыта';
        query('#reqContentPane').html(dataStr);
        showDialog('reqCommonDialog');
      });
    };

    /*
    save New & Transfer Request
     */

    saveReq = function(){
      let eqIdArr = [],reqSaveObj = {},linkSaveReq = "",reqArgArr = [];
      reqSaveObj.reqArgDivId = [];

      if(isAdmin || isNSS){
        if(domAttr.get('reqDiffUserId','data-req-userid') === ""){
          myAlert('Вы не заполнили "Подать заявку от лица"');
          return false;
        }
      }

      if(query("#addNewReqDataGrid div.dgrid-content div[role='row']").length > 0){
        query("#addNewReqDataGrid div.dgrid-content div[role='row']").attr('id').forEach(function(item,idx,arr){
          eqIdArr.push(item.replace("addNewReqDataGrid-row-",""));
        });
        reqSaveObj.equipId = eqIdArr.join(",");
      }else{
        myAlert('Вы не добавили оборудование в заявку');
        return;
      }
      eqIdArr = null;

      if(domAttr.get(query('input[name="reqRepBT"]')[0],"value").toString() === "" &&
        domAttr.get(query('input[name="reqRepET"]')[0],"value").toString() === "" &&
        domAttr.get(query('input[name="reqRepBD"]')[0],"value").toString() === "" &&
        domAttr.get(query('input[name="reqRepED"]')[0],"value").toString() === ""){
        myAlert('Вы не заполнили "Сроки ремонта"');
        return;
      }

      if(domAttr.get(query('input[name="reqRepBT"]')[0],"value").toString() === "" && domAttr.get(query('input[name="reqRepET"]')[0],"value").toString() === ""){
        myAlert('Вы не заполнили "Сроки ремонта"');
        return false;
      }else{

      }

      // save eqId
      if(eqFullSearch.checkId === ""){

      }else{
        reqSaveObj.equipId = eqFullSearch.checkId;
      }

      // save req Category
      reqSaveObj.reqCatId = domAttr.get(query('input[name="reqCat"]')[0],"value");
      // save req Content Work
      if(trim(dom.byId("addNewReqContentWork").value) === ""){
        myAlert('Вы не заполнили "Содержание работ"');
        return;
      }else{
        reqSaveObj.reqContW = dom.byId("addNewReqContentWork").value;
      }

      // Save req REQUEST Period
      if(registry.byId("addNewReqPeriodCB").checked === true){
        reqSaveObj.reqPeriod = "REQUEST"
      }else{
        if(domAttr.get(query('input[name="reqCrashReadyM"]')[0],"value").toString() === '00' && domAttr.get(query('input[name="reqCrashReadyH"]')[0],"value").toString() === '00'){
          myAlert('Вы не заполнили "Аварийная готовность"');
          return;
        }else{
          reqSaveObj.reqPeriod = domAttr.get(query('input[name="reqCrashReadyH"]')[0],"value").toString() + ":" + domAttr.get(query('input[name="reqCrashReadyM"]')[0],"value").toString();
        }
      }
      //req Agreement
      if(registry.byId("addNewReqAgrCB").checked === true){
        reqSaveObj.reqArg = 0;
      }else{
        reqSaveObj.reqArg = 1;
        if(query("div.secTableDDTChild input[type='hidden']").length > 0){
          array.forEach(query("div.secTableDDTChild input[type='hidden']"),function(item,idx,arr){
            if(item.value.toString() !== "-1"){
              reqArgArr.push(item.value);
            }
          });

          if(reqArgArr.length > 0){
            reqSaveObj.reqArgDivId = reqArgArr.join(",");
          }else{
            myAlert('Вы не заполнили "Согласование заявки"');
            return false;
          }
        }else{
          myAlert('Вы не заполнили "Согласование заявки"');
          return false;
        }
      }


      if(query('input[name="reqRepBT"]')[0].value.charAt(0) === "T"){
        reqSaveObj.reqRepBT = query('input[name="reqRepBT"]')[0].value.substr(1);
      }

      if(query('input[name="reqRepET"]')[0].value.charAt(0) === "T"){
        reqSaveObj.reqRepET = query('input[name="reqRepET"]')[0].value.substr(1);
      }

      if(query('input[name="reqSubT"]')[0].value.charAt(0) === "T"){
        reqSaveObj.reqSubT = query('input[name="reqSubT"]')[0].value.substr(1);
      }

      if(query('input[name="reqRepBD"]')[0].value){
        reqSaveObj.reqRepBD = query('input[name="reqRepBD"]')[0].value;
      }

      if(query('input[name="reqRepED"]')[0].value){
        reqSaveObj.reqRepED = query('input[name="reqRepED"]')[0].value;
      }

      if(query('input[name="reqSubD"]')[0].value){
        reqSaveObj.reqSubD = query('input[name="reqSubD"]')[0].value;
      }

      reqSaveObj.reqId = "-1";
      if(isNSS || isAdmin){
        reqSaveObj.IsNSS = (isNSS || isAdmin)?1:0;
        reqSaveObj.AdminId = isAdmin?userId:0;
        reqSaveObj.userId = domAttr.get('reqDiffUserId','data-req-userid');
      }else{
        reqSaveObj.IsNSS = (isNSS || isAdmin)?isNSS:0;
        reqSaveObj.AdminId = isAdmin?userId:0;
        reqSaveObj.userId = userId;
      }

      reqSaveObj.reqStatusId = -1;
      reqSaveObj.reqNum = "";
      reqSaveObj.reqTSub = reqSaveObj.reqSubD.toString() + " " + reqSaveObj.reqSubT.toString();
      reqSaveObj.reqTRepB = reqSaveObj.reqRepBD.toString() + " " + reqSaveObj.reqRepBT.toString();
      reqSaveObj.reqTRepE = reqSaveObj.reqRepED.toString() + " " + reqSaveObj.reqRepET.toString();
      reqSaveObj.reqCrash = reqSaveObj.reqPeriod;
      reqSaveObj.isEditor = isEditor;
      reqSaveObj.isViewer = isViewer;

      if(query('#reqTrans').val() === ""){
        reqSaveObj.reqTransId = "";
      }else{
        reqSaveObj.reqTransId = query('#reqTrans').val();
        if(registry.byId("reqTransfer").value === ""){
          registry.byId("reqTransfer").set('value','');
          query('#reqTransfer').removeClass('reqError');
          showDialog("reqTransferDialog");
          registry.byId("reqTransfer").set('rows','6');
          return false;
        }
      }

      reqSaveObj.reqTransNote = registry.byId("reqTransfer").value;
      registry.byId("reqTransfer").set('value','');


      saveFinishReq(reqSaveObj);
    };

    saveFinishRequest = function(){
      saveReq();
    };

    saveReqTransNote = function(){
      if(trim(registry.byId("reqTransfer").value) === ""){
        myAlert('Введите причину "Продления" заявки"');
        query('#reqTransfer').addClass('reqError');
      }else{
        hideDialog("reqTransferDialog");
        saveFinishRequest();
      }
    };

    saveFinishReq = function(reqSaveObj,crutchSaveReq){
      if(crutchSaveReq > 0){
        return false;
      }
      hideDialog("addNewReqDialog");
      myPost(prefix + "/main/saveRequest.php",{jdata:JSON.stringify(reqSaveObj)},function(adata){

        filterOutput(currentMode);
        clearMemHide();
        hideDialog('reqCommonDialog');
        let dataParse = JSON.parse(adata),dataStr = "";
        if(parseInt(dataParse.reqNumTans) !== 0){
          dataStr = 'Заявка № ' + dataParse.reqNumTans + ' успешно продлена заявкой № ' + dataParse.reqNum;
        }else{
          dataStr = 'Заявка № ' + dataParse.reqNum + ' успешно добавлена';
        }
        query('#reqContentPane').html(dataStr);
        showDialog('reqCommonDialog');
      });
    };

    /*
    build Dijit Widgets
     */

    setStyleWidget = function(id){
      query('#' + id.toString()).attr('disabled',"disabled");
      query('#' + id.toString()).attr('style',"text-align: center;");
      query('#widget_' + id.toString()).attr('style',"width: 60px; margin-right: 5px;");
    };

    buildReqTextBox = function(){
      query('div[widgetid="edElPosition"]').style({width:'99%'});
      query('div[widgetid="edElName"]').style({width:'99%'});
    };

    buildReqRepTimeBox = function(){
      let tomD = (new Date()).getDate() + 1;
      if(parseInt(tomD) < 10){
        tomD = "0" + tomD;
      }

      let todD = (new Date()).getDate();
      if(parseInt(todD) < 10){
        todD = "0" + todD;
      }

      let tomM = (new Date()).getMonth() + 1;
      if(parseInt(tomM) < 10){
        tomM = "0" + tomM;
      }

      let tomY = (new Date()).getFullYear();

      let tomFull = tomY + "-" + tomM + "-" + tomD;
      registry.byId("addNewReqRepBD").set("value",tomFull,true);
      registry.byId("addNewReqRepED").set("value",tomFull,true);
      registry.byId("addNewReqSubD").set("value",tomFull,true);

      setStyleWidget("addNewReqRepBT");
      setStyleWidget("addNewReqRepET");
      setStyleWidget("addNewReqSubT");
    };

    buildReqRepDateBox = function(id){
      domAttr.set(dom.byId(id.toString()),{disabled:"disabled"});
      domAttr.set(dom.byId("widget_" + id.toString()),{style:"width: 130px;"});
    };

    buildReqCheckBoxCrash = function(){
      on(registry.byId("addNewReqPeriodCB"),"change",function(e){
        if(registry.byId("addNewReqPeriodCB").checked === true){
          domStyle.set(dom.byId("addNewReqPeriodDiv"),{"display":"none"});
        }else{
          domStyle.set(dom.byId("addNewReqPeriodDiv"),{"display":"block"});
        }
      });
    };

    buildReqCheckBoxAgr = function(){
      on(registry.byId("addNewReqAgrCB"),"change",function(e){
        if(registry.byId("addNewReqAgrCB").checked === true){
          domStyle.set("addNewReqDivAddAgr",{"display":"none"});
          domStyle.set("addNewReqDivAddAgrConstr",{"display":"none"});
          domConstruct.empty(dom.byId("addNewReqDivAddAgrConstr"));
        }else{
          query('#addNewReqDivAddAgr').style({display:'block'});
          domStyle.set("addNewReqDivAddAgrConstr",{"display":"block"});
        }
      });
    };

    buildReqSelect = function(){
      let reqCrashReadyH,reqCrashReadyM,storeH,storeM;

      reqCrashReadyH = buildLoopSelect().reqCrashReadyH;
      reqCrashReadyM = buildLoopSelect().reqCrashReadyM;

      storeH = new memory({
        idProperty:"id",
        labelAttr:"hours",
        data:reqCrashReadyH
      });
      registry.byId("reqCrashReadyHId").set('store',storeH);

      storeM = new memory({
        idProperty:"id",
        labelAttr:"minutes",
        data:reqCrashReadyM
      });

      registry.byId("reqCrashReadyMId").set('store',storeM);

    };

    buildLoopSelect = function(){
      let reqCrashReadyH = [],reqCrashReadyM = [],x = 0,y = 0;
      for(x = 0; x < 24; x++){
        if(x < 10){
          reqCrashReadyH.push({id:"0" + x,hours:"0" + x});
        }else{
          reqCrashReadyH.push({id:"" + x,hours:"" + x.toString()});
        }
      }

      for(y = 0; y < 60; y++){
        if(y < 10){
          reqCrashReadyM.push({id:"0" + y,minutes:"0" + y});
        }else{
          reqCrashReadyM.push({id:"" + y,minutes:"" + y.toString()});
        }
      }
      return {reqCrashReadyH:reqCrashReadyH,reqCrashReadyM:reqCrashReadyM}
    };

    /*
    createAddEqSearchGrid
     */

    createAddEqSearchGrid = function(){
      if(addEqSearchGrid){
        return;
      }

      addEqSearchGrid = new (declare([OnDemandGrid,DijitRegistry,Keyboard,Selection]))({
        columns:addEqSearchLayout,
        id:"addEqSearchDataGrid",
        loadingMessage:'Загрузка данных...',
        noDataMessage:'Нет данных.'
      });
      addEqSearchGrid.startup();
      registry.byId("addEqSearchPanel").addChild(addEqSearchGrid);
      addEqSearchGrid.resize();

      let id,_itemArr = [];
      addEqSearchGrid.on(".dgrid-row:dblclick",function(evt){
        for(id in addEqSearchGrid.selection){
          if(addEqSearchGrid.selection[id]){
            array.forEach(addEqSearchGrid.collection.data,function(item,idx,arr){
              if(item.id === id){
                if(eqFullSearch.items.length > 0){
                  if(eqFullSearch.checkId.indexOf(id) === -1){
                    eqFullSearch.items.push({
                      id:id,un:item.un,fsn:item.fsn,ps:item.ps,nm:item.nm,es:item.es,em:item.em,esd:item.esd,did:item.did,dv:item.dv,
                      ic2:item.ic2,ec:item.ec
                    });
                    eqFullSearch.checkId += "" + id + ",";
                  }
                }else{
                  eqFullSearch.items.push({
                    id:id,un:item.un,fsn:item.fsn,ps:item.ps,nm:item.nm,es:item.es,em:item.em,esd:item.esd,did:item.did,dv:item.dv,
                    ic2:item.ic2,ec:item.ec
                  });
                  eqFullSearch.checkId += "" + id + ",";
                }
              }
            });
          }
        }
        hideDialog('addEqSearchDialog');
        newRequest();
        buildAddNewReqGrid(eqFullSearch.items);
      });
    };

    buildAddEqSearchGrid = function(e){
      if(trim(dom.byId("addEqSearchText").value) === ""){
        myAlert("Введите позицию или название оборудования");
        return false;
      }

      request.post(prefix + "/main/searchEquip.php",{
        handleAs:"json",
        data:{searchTerms:dom.byId("addEqSearchText").value,noAll:'1'}
      }).then(function(adata){
          if(adata.length > 0){
            let collection = new DstoreMemory({data:adata});
            addEqSearchGrid.set('collection',collection);
          }else{
            myAlert("Указанное оборудование не найдено");
          }
        },function(err){
          myAlert(err);
        }
      );
    };

    /*
    createAddNewRequestGrid
     */

    createAddNewRequestGrid = function(){
      if(addNewReqGrid){
        return;
      }

      addNewReqGrid = new (declare([OnDemandGrid,DijitRegistry,Keyboard,Selection]))({
        columns:addEqSearchLayout,
        id:"addNewReqDataGrid",
        loadingMessage:'Загрузка данных...',
        noDataMessage:'Нет данных.'
      });
      addNewReqGrid.startup();
      registry.byId("addNewReqPanel").addChild(addNewReqGrid);
      addNewReqGrid.resize();
    };

    buildAddNewReqGrid = function(data){
      if(data){
        let collection = new DstoreMemory({data:data});
        addNewReqGrid.set('collection',collection);
      }else{
        let timeArr = eqFullSearch.items;
        let collection = new DstoreMemory({data:timeArr});
        addNewReqGrid.set('collection',collection);
      }

      resizeAddNewReqGrid();
    };

    filterRequest = function(id,date){
      let vDate = date === undefined?["",""]:date;
      let fRequest = {
        "role":role,
        "divId":divisionId,
        "lpId":id,
        "date1":vDate[0],
        "date2":vDate[1]
      };

      myPost(prefix + "/main/filterRequest.php",{jdata:JSON.stringify(fRequest)},function(adata){
        //

        checkLogout(adata);
        let collection = new DstoreMemory({data:JSON.parse(adata)});
        grid.set('collection',collection);
      });
    };

    /*
    createMainReqGrid
     */

    createMainReqGrid = function(){
      if(grid != null){
        return;
      }
      let columns = getColumns();
      //
      grid = new (declare([OnDemandGrid,DijitRegistry,ColumnResizer,Keyboard,Selection]))({
        columns:columns,
        selectionMode:'single',
        allowTextSelection:true,
        selectable:true,
        id:"dataGrid",
        loadingMessage:'Загрузка данных...',
        noDataMessage:'Нет данных.'
      });
      grid.startup();
      registry.byId("medPanel").addChild(grid);
      grid.resize();

      aspect.after(grid,'resize',function(){

      });

      grid.on(".dgrid-column-noteCol .reqImgIcon:click",function(e){
        let cell = grid.cell(e),reqInc = 0;
        query('#reqShowDel').style({display:'none'});
        query('#reqShowTrans').style({display:'none'});
        query('#reqShowApprove').style({display:'none'});
        query('#reqShowIsTrans').style({display:'none'});

        if(parseInt(cell.row.data.reqTrans) !== 0){
          reqInc++;
          query('#reqShowTrans').style({display:'block'});
          query('#reqShowTrans div').text(
            cell.row.data.reqTrans.transNote
          );
        }
        if(parseInt(cell.row.data.reqDel) !== 0){
          reqInc++;
          query('#reqShowDel').style({display:'block'});
          query('#reqShowDel div').text(
            cell.row.data.reqDel.delNote
          );
        }
        if(parseInt(cell.row.data.approveArr) !== 0){
          if(parseInt(cell.row.data.approveArr.isApprove) === 0){
            reqInc++;
            query('#reqShowApprove').style({display:'block'});
            query('#reqShowApprove div').text(
              cell.row.data.approveArr.approveNote
            );
          }
        }
        if(parseInt(cell.row.data.isTrans) !== 0){
          reqInc++;
          query('#reqShowIsTrans').style({display:'block'});
          query('#reqShowIsTrans span').html(
            'Дополнительная информация'
          );
          query('#reqShowIsTrans div').text(
            'Заявка № ' + cell.row.data['reqNum'] + ' - продлённая от заявки № ' + cell.row.data['isTrans']
          );
        }
        if(reqInc > 0){
          query('#reqShowIconDialog_title').html("Примечание для заявки № " + cell.row.data['reqNum']);
          showDialog('reqShowIconDialog');
        }else{
          hideDialog('reqShowIconDialog');
        }
      });

      grid.on(".dgrid-content .dgrid-row:dblclick",function(e){
        if(isViewer || isEditor){
          return false
        }
        let row = grid.row(e);
        if(parseInt(row.data.reqStatusId) === 8){
          return false
        }
        reqRow = row;
        viewRequestGrid(row);
      });

      grid.on(".dgrid-row:contextmenu",function(evt){
        if(isViewer){
          return false;
        }
        evt.stopPropagation();
        evt.preventDefault();
        rowMenu = grid.row(evt);
        let menu = registry.byId("menu");


        menuItemTrans.set('label',"Продлить заявку № " + rowMenu.data.reqNum);
        menuItemDelete.set('label',"Удалить заявку № " + rowMenu.data.reqNum);
        menuItemEdit.set('label',"Скопировать в новую");

        menu.addChild(menuItemEdit);
        menu.addChild(menuItemDelete);
        menu.addChild(menuItemTrans);

        if((parseInt(rowMenu.data.userId) === parseInt(userId)) || (parseInt(isAdmin) === 1)){
          menuItemTrans.set('disabled',false);
          menuItemDelete.set('disabled',false);
        }else{
          menuItemTrans.set('disabled',true);
          menuItemDelete.set('disabled',true);
        }
        if(parseInt(rowMenu.data.reqStatusId) !== 3){
          menuItemTrans.set('disabled',true);
        }
        if(parseInt(rowMenu.data.reqStatusId) === 8){
          menuItemDelete.set('disabled',true);
        }
        popup.open({popup:menu,x:evt.pageX,y:evt.pageY});
        menu.startup();

      });

      aspect.after(grid,'refresh',function(){
        query('#dataGrid .dgrid-content .reqNewGridRow').parents('div.dgrid-row').addClass('reqNewGridRow');
        query('#dataGrid .dgrid-content .reqApproveGridRow').parents('div.dgrid-row').addClass('reqApproveGridRow');
        query('#dataGrid .dgrid-content .reqOnGridRow').parents('div.dgrid-row').addClass('reqOnGridRow');
        query('#dataGrid .dgrid-content .reqOffGridRow').parents('div.dgrid-row').addClass('reqOffGridRow');
        query('#dataGrid .dgrid-content .reqDeclineGridRow').parents('div.dgrid-row').addClass('reqDeclineGridRow');
        query('#dataGrid .dgrid-content .reqTransferGridRow').parents('div.dgrid-row').addClass('reqTransferGridRow');
        query('#dataGrid .dgrid-content .reqDeleteGridRow').parents('div.dgrid-row').addClass('reqDeleteGridRow');
      });
    };

    formatNum = function(item){
      let str = item.reqNum + "<br/>";
      str += item.reqCatName + "<br/>";
      str += setReqTimeParams(item.reqTime,'') + "<br/>";
      str += item.reqStatusName + "<br/>" + item.reqId;
      return str;
    };

    formatEq = function(item){
      let fEq = "",arrEq = [];
      item.arrEq.forEach(function(item2,idx,arr){
        arrEq.push(item2.ps);
      });

      if(arrEq.length > 0){
        fEq = arrEq.join(", ");
      }else{
        fEq = arrEq[0];
      }
      fEq += "<br />" + item.reqContW;
      fEq += "<br />&nbsp;С: " + setReqTimeParams(item.reqTRepB,'');
      fEq += "<br />До: " + setReqTimeParams(item.reqTRepE,'');
      return fEq;
    };

    formatAgr = function(item){
      let fAgr = "",arrFAgr = [],sp;

      sp = (item.reqCrash === "REQUEST")?"Срок заявки":setReqAgrTime(item.reqCrash,"");
      fAgr += "<span style='color:darkred'>" + sp + "</span>";
      fAgr += "<br/>";
      if(parseInt(item.reqAgr) > 0){
        item.reqAgrDiv.forEach(function(item2,idx2,arr2){
          arrFAgr.push(item2.DivName);
        });
        fAgr += arrFAgr.join(", ");
      }else{
        fAgr += "Согласование не требуется";
      }
      return fAgr;
    };

    formatSub = function(item){
      let str = setReqTimeParams(item.reqTSub,'');
      if(parseInt(item.isNss) === 1){
        str += "<br/>" + getUserPos(item.userName) + "<br/>" + getUserName(item.userName);
        str += "<br/>Записал по телефону:<br/>" + getUserPos(item.adminName) + "<br/>" + getUserName(item.adminName);
      }else{
        str += "<br/>" + getUserPos(item.userName) + "<br/>" + getUserName(item.userName);
      }
      return str;
    };

    renderApprove = function(item){
      let apprStr = "";
      if(parseInt(item.approveArr) !== 0){
        apprStr += "Заявку ";
        apprStr += (parseInt(item.approveArr.isApprove) === 1)?"утверждаю,":"отклоняю,";
        apprStr += "<br/>";
        apprStr += (parseInt(item.approveArr.approveGISId) === 58986)?" ГИС ":"и.о. ГИС ";
        apprStr += getUserName(item.approveArr.approveGIS);
        apprStr += "<br/>";
        apprStr += "Информацию подтвердил: " + getUserName(item.approveArr.approveUserName);
        apprStr += "<br/>";
        apprStr += setReqTimeParams(item.approveArr.approveTime,'');
      }
      return apprStr;
    };

    formatOn = function(item){
      let strOn = "";
      if(parseInt(item.onArr) !== 0){
        strOn += setReqTimeParams(item.onArr.onTime,'');
        strOn += "<br/>";
        strOn += getUserPos(item.onArr.onUserName);
        strOn += "<br/>";
        strOn += getUserName(item.onArr.onUserName);
      }
      return strOn;
    };

    formatOff = function(item){
      let strOff = "";
      if(parseInt(item.offArr) !== 0){
        strOff += setReqTimeParams(item.offArr.offTime,'');
        strOff += "<br/>";
        strOff += getUserPos(item.offArr.offUserName);
        strOff += "<br/>";
        strOff += getUserName(item.offArr.offUserName);
      }
      return strOff;
    };

    renderIcon = function(object,data,cell){
      let reqInc = 0;
      if(parseInt(data.reqDel) !== 0){
        reqInc++;
      }
      if(parseInt(data.reqTrans) !== 0 || parseInt(data.isTrans) !== 0){
        reqInc++;
      }
      if(parseInt(data.approveArr) !== 0){
        if(parseInt(data.approveArr.isApprove) === 0){
          reqInc++;
        }
      }
      if(reqInc > 0){
        query(cell).append('<img src="i.png" height="20" width="20" class="reqImgIcon"/>');
      }else{
        query(cell).append('');
      }
      switch(parseInt(data.reqStatusId)){
        case 1:
          domClass.add(cell,'reqNewGridRow');
          break;
        case 2:
          domClass.add(cell,'reqApproveGridRow');
          break;
        case 3:
          domClass.add(cell,'reqOnGridRow');
          break;
        case 4:
          domClass.add(cell,'reqOffGridRow');
          break;
        case 5:
          domClass.add(cell,'reqDeclineGridRow');
          break;
        case 7:
          domClass.add(cell,'reqTransferGridRow');
          break;
        case 8:
          domClass.add(cell,'reqDeleteGridRow');
          break;
        default:
          break;
      }
    };
    /*
        1	Новая - New
        2	Утверждённая - Approve
        3	Открытая - On
        4	Закрытая - Off
        5	Отклонённая - Decline
        6	Примечание - Note
        7	Продлённая - Transfer
        8	Удалённая - Delete
    */
    getColumns = function(){
      return {
        numCol:{
          field:"_item",
          label:'Номер заявки, категория заявки, дата и время подачи заявки',
          formatter:formatNum
        },
        eqCol:{
          field:"_item",
          label:'Отключаемое оборудование (выводимое из резерва). Содержание работ. Сроки ремонта.',
          formatter:formatEq
        },
        agrCol:{
          field:"_item",
          label:'Аварийная готовность. Согласование заявки',
          formatter:formatAgr
        },
        subTimeCol:{
          field:"_item",
          label:'Заявленная дата и время отключения оборудования (вывода из резерва). Должность, фамилия, инициалы лица, подавшего заявку',
          formatter:formatSub
        },
        approveCol:{
          field:"_item",
          label:'Заявку утверждаю / отклоняю, должность, фамилия, инициалы, дата',
          formatter:renderApprove
        },
        eqOffCol:{
          field:"_item",
          label:'Дата и время отключения оборудования (вывода из резерва), должность, фамилия, инициалы',
          formatter:formatOn
        },
        eqOnCol:{
          field:"_item",
          label:'Дата и время включения оборудования (ввода в резерв), должность, фамилия, инициалы',
          formatter:formatOff
        },
        noteCol:{
          field:"_item",
          label:' ',
          renderCell:renderIcon
        }
      };
    };

    setReqGridParams = function(){
      on(query('#addNewReqDataGrid th.dgrid-cell'),'mousedown',function(evt){
        evt.preventDefault();
        evt.stopPropagation();
        return false;
      });
      on(query('#addNewReqDataGrid th.dgrid-cell'),'click',function(evt){
        evt.preventDefault();
        evt.stopPropagation();
        return false;
      });

      on(query('#addEqSearchDataGrid th.dgrid-cell'),'mousedown',function(evt){
        evt.preventDefault();
        evt.stopPropagation();
        return false;
      });
      on(query('#addEqSearchDataGrid th.dgrid-cell'),'click',function(evt){
        evt.preventDefault();
        evt.stopPropagation();
        return false;
      });
    };

    /*
    createMenus
     */

    createMenus = function(){
      if(isViewer){
        return false
      }

      menu = new Menu({id:"menu"});

      menuItemEdit = new menuItem({
        label:"Скопировать в новую"
      });
      on(menuItemEdit,'click',function(){
        buildDialogReqAdd();
        setParamsReqAdd(rowMenu,"copy");
        setNullsParamsReqAddDisabled();
        popup.hide(menu);
      });

      menu.addChild(menuItemEdit);

      menuItemDelete = new menuItem({
        label:"Удалить"
      });

      on(menuItemDelete,'click',function(evt){
        registry.byId("reqDelete").set('value','');
        query('#reqDelete').removeClass('reqError');
        showDialog("reqDeleteDialog");
        registry.byId("reqDelete").set('rows','6');
        let menu = registry.byId("menu");
        popup.hide(menu);
      });
      menu.addChild(menuItemDelete);

      menuItemTrans = new menuItem({
        label:"Продлить заявку"
      });
      on(menuItemTrans,'click',function(){
        buildDialogReqAdd();
        setParamsReqAddTrans(rowMenu);
        popup.hide(menu);
      });
      menu.addChild(menuItemTrans);

      menu.startup();
      on(query('body'),'click',function(){
        popup.hide(menu);
      });
    };

    menuClick = function(evt){
      if(evt.target.id !== 'leftPanel'){
        selectMenuItem(evt.target.id);
      }
    };

    selectMenuItem = function(iid){
      currentMode = iid.replace('menuItem','');

      currentActiveMenu = iid;
      let q = query(".MIMenuItemActive");
      if(q[0]){
        domClass.remove(q[0],"MIMenuItemActive");
      }

      domClass.add(dom.byId(currentActiveMenu),"MIMenuItemActive");
      if(grid != null){
        registry.byId("medPanel").removeChild(grid);
        grid.destroy();
        grid = null;
      }

      createMainReqGrid();
      filterOutput(currentMode);

    };

/*
Global Functions
 */

    resizeAddNewReqGrid = function(){
      let fullHeight = 30;
      if(query("#addNewReqDataGrid div.dgrid-content div[role='row']").length > 0){
        query("#addNewReqDataGrid div.dgrid-content div[role='row']").marginBox().forEach(function(item){
          fullHeight += item.h;
        });
      }else{
        fullHeight += 35;
      }
      query("#addNewReqDataGrid").style({height:fullHeight + "px"});
      query("#addNewReqPanel").style({height:fullHeight - 4 + "px"});
    };

    deleteEqFromNewReq = function(e){
      let eqSelectedRow = query("#addNewReqPanel div.dgrid-selected"),
        eqIdForDelete,
        timeEqArr = [];
      if(eqSelectedRow.length === 1){
        eqIdForDelete = (domAttr.get(eqSelectedRow[0],"id")).replace('addNewReqDataGrid-row-','');
        eqFullSearch.checkId = eqFullSearch.checkId.replace(eqIdForDelete + ",",'');
        array.forEach(eqFullSearch.items,function(item,idx,arr){
          if(parseInt(item.id) !== parseInt(eqIdForDelete)){
            timeEqArr.push(item);
          }
        });
        eqFullSearch.items = timeEqArr;
        buildAddNewReqGrid(eqFullSearch.items);

      }else if(eqSelectedRow.length > 1){
        myAlert("Выберите одно оборудование для удаления");
      }else{
        myAlert("Выберите оборудование для удаления");
      }
    };

    myAlert = function(message){
      hideDialog('reqCommonDialog');
      query('#reqContentPane').html(message);
      showDialog('reqCommonDialog');
    };

    confirmDialogErr = function(title,message){
      dom.byId("dialogTitle").innerHTML = '<H1>' + title + '</H1>';
      dom.byId("contentPane").innerHTML = message;
      query('#cancelDialogButton').html('Ок');

      domClass.add(dom.byId("cancelDialogButton"),"reqGreenButton");
      domClass.add(dom.byId("deleteDialogButton"),"dijitHidden");
      domClass.add(dom.byId("saveDialogButton"),"dijitHidden");
      showDialog("commonDialog");
    };

    confirmDialog = function(reqNum,message){
      hideDialog('reqCommonDialog');
      query('#reqContentPane').html(
        'Заявка № ' + reqNum + ' успешно ' + message
      );
      showDialog('reqCommonDialog');

    };

    setReqParamsConfirmDialog = function(){
      on(query('#reqMyAlertSave'),'click',function(){
        hideDialog('reqCommonDialog');
      });
      on(query('#cancelDialogButton'),'click',function(){
        hideDialog("commonDialog");
      });
      on(query('#reqMyAlertSave'),'click',function(){
        hideDialog('reqCommonDialog');
      });
      on(query('#reqShowIconSave'),'click',function(){
        hideDialog('reqShowIconDialog');
      });
    };

    templateAddAgr = function(id,num){
      let strChild,strDel,fullStr;
      if(parseInt(num) === 1){
        strChild = 'style="border:none;"';
        strDel = '';
      }else{
        strChild = '';
        strDel = 'class="addNewReqDeleteDiv"';
      }
      fullStr = '' +
        '<div class="secTableDDTChild" ' + strChild + '>\n' +
        '<div>\n' +
        '<div>\n' +
        '<select data-dojo-type="dijit/form/Select" ' +
        'id="agrSelect' + id + '" ' +
        'data-dojo-props="value: \'\', sortByLabel:false, placeHolder:' +
        ' \'...\', labelAttr: \'nm\', maxHeight:-1" ' +
        'style="text-align: left;">\n' +
        '<option value=""> => </option>\n' +
        '</select>\n' +
        '</div>\n' +
        '<div>\n' +
        '<div ' + strDel + ' id="agrDivDelete' + id + '"></div>\n' +
        '</div>\n' +
        '</div>\n' +
        '</div>';

      return fullStr;
    };

    setReqAgrTime = function(time){
      if(time === null){
        return false;
      }
      return '' +
        '<span style="color:darkred">' + time.split(":")[0] + '</span>' +
        '<sup style="border-bottom:1px solid rgb(187, 187, 187);font-size:0.8em;color:darkred">' + time.split(":")[1] + '</sup>'
    };

    setReqTimeParams = function(time,str){
      if(time === null){
        return false;
      }
      return '' +
        '<span>' + str + time.split(" ")[1] + '</span>&nbsp;' +
        '<span>' + time.split(" ")[0].split(":")[0] + '</span>' +
        '<sup style="border-bottom:1px solid rgb(187, 187, 187);font-size:0.8em;">' + time.split(" ")[0].split(":")[1] + '</sup>'
    };

    myPost = function(myURL,myData,myCallback){
      showDialog('waitPanelReq',1);
      request.post(myURL,{handleAs:"text",data:myData}).then(function(adata){
          hideDialog('waitPanelReq');
          checkLogout(adata);
          myCallback(adata);
        },
        function(error){

        });
    };

    showHideUserPanel = function(){
      if(infoPanelBoolean === false){
        popup.open({
          popup:registry.byId("infoPanel"),
          around:dom.byId('fioField')
        });
        infoPanelBoolean = true;
      }else{
        popup.close(registry.byId('infoPanel'));
        infoPanelBoolean = false;
      }
    };

    /*
    Login&Logout
     */

    doLogout = function(){
      request.post("/login/doLogout.php",{handleAs:"text",preventCache:true}).then(
        function(adata){
          checkLogin();
        },
        function(error){
          confirmDialogErr(
            "Ой...",'<div style="margin:4px;"><div>Что-то пошло не так. Перезагрузите страницу и попробуйте снова.</div><div>Данные ошибки:' +
            error + '.</div></div>');
        }
      )
    };

    checkLogin = function(){
      request.post("/login/newLogin.php",{handleAs:"json",data:{roles:reqRo}}).then(
        function(adata){

          sessionId = adata.csid;
          isAdmin = adata.ADMIN;
          isNSS = adata.REQ_ADMIN;
          isEditor = adata.REQ_EDITOR;
          isViewer = adata.REQ_VIEW;
          subDiv = adata.subDiv;

          if(sessionId !== ""){
            if(isAdmin === 1 || isEditor === 1 || isViewer === 1 || isNSS === 1){
              isGuest = 0;
              if(isAdmin === 1){
                role = "ADMIN";
              }else if(isEditor === 1){
                role = "EDITOR";
              }else if(isViewer === 1){
                role = "VIEWER";
              }else if(isNSS === 1){
                role = "ADMIN";
              }
            }else{
              window.open("/login/noAuth.php?place=" + baseName,"_self");
              return;
            }
          }else{
            window.open("/login/noAuth.php?place=" + baseName,"_self");
            return;
          }

          userFio = adata.fio;
          divName = adata.din;
          fullFio = adata.ffio;
          userId = adata.uid;
          sysdate = adata.now;
          firstdate = adata.firstDay;
          divisionId = adata.did;

          if(!isGuest){
            globals.showIt(dom.byId("instrumentsCover"));
          }

          dom.byId("fioField").innerHTML = userFio;

          createMenus();
          endCheckLogin();
          selectMenuItem("menuItem0");
          endLoading();
        },
        function(error){
          myAlert(error);
        }
      );
    };

    endCheckLogin = function(){
      query('#reqNoteConfirmFIO').html(fullFio);
      query('#reqOnConfirmFIO').html(fullFio);
      query('#reqOffConfirmFIO').html(fullFio);
      query("#reqAppEditUserFIO").html(fullFio);
    };

    checkLogout = function(adata){
      if(adata === 'PORTAL_USER_LOGOUT'){
        window.open("/login/noAuth.php?place=" + baseName,"_self");
        return false;
      }
      if(adata === 'SERVER_ERROR'){
        myAlert('Пожалуйста, перезагрузите страницу');
        return false;
      }
    };

    filterOutput = function(dtId){
      //hidePallette();
      filterRequest(dtId);
    };

    endLoading = function(){
      if(isLoaded){
        return;
      }
      isLoaded = true;
      baseFx.fadeOut({
        node:dom.byId("loadingOverlay"),
        onEnd:function(node){
          domClass.remove(dom.byId("appLayout"),"dijitHidden");
          domClass.add(node,"dijitHidden");
        }
      }).play();
    };

    startLoading = function(targetNode){
      domClass.add(dom.byId("appLayout"),"dijitHidden");
    };

    storeWidth = function(){
      let val = dom.byId("leftPanel").clientWidth;
      cookie("LeftPanelWidth",val,{
        expires:365365
      });
    };

    /*
    serialize
     */

    serializeDics = function(_node,item){
      registry.byId(_node).set('store',store);

      if(item){
        registry.byId(_node).set('value',item.DivId);
        registry.byId(_node).set('disabled',true);
      }else{
        registry.byId(_node).set('disabled',false);
      }
    };

    fillDivisions = function(_node,item,divId){
      //serializeDics(id,item);
      registry.byId(_node).set('store',storeFilAgr);

      if(parseInt(item) === 1){
        registry.byId(_node).set('value',divId);
        registry.byId(_node).set('disabled',true);
        query('#addNewReqDivAddAgrParent').style({display:'none'});
        query('#addNewReqAgrCBDiv').style({display:'none'});
      }else{
        registry.byId(_node).set('disabled',false);
        query('#addNewReqDivAddAgrParent').style({display:'block'});
        query('#addNewReqAgrCBDiv').style({display:'block'});
      }
    };

    /*
    initReqCalendar
     */

    initReqCalendar = function(){
      pickmeup('.reqThreeCalendars',{
        flat:true,
        date:[
          "",
          ""
        ],
        mode:'range',
        format:'d.m.Y',
        calendars:3,
        locale:"ru"
      });
      setPanelCalendar(["",""]);

      on(query('.reqThreeCalendars'),'pickmeup-change',function(e){
        setPanelCalendar(e.detail.formatted_date);
      });

      on(query('#reqClearFilterDate'),'click',function(){
        pickmeup('.reqThreeCalendars').set_date([
          "",
          ""
        ]);
        setPanelCalendar(["",""]);

        popup.close(registry.byId('extendedSearchDialog'));
        reqDateTT = false;
        let reqDate = [query('#reqDate1').val(),query('#reqDate2').val()];
        filterRequest(currentMode,reqDate);
      });

      on(query('#reqSetFilterDate'),'click',function(){
        popup.close(registry.byId('extendedSearchDialog'));
        reqDateTT = false;
        let reqDate = [query('#reqDate1').val(),query('#reqDate2').val()];
        filterRequest(currentMode,reqDate);
      });

      on(query('#extSearchBtn'),'click',function(){
        if(reqDateTT === false){
          popup.open({
            popup:registry.byId('extendedSearchDialog'),
            around:dom.byId('extSearchBtn')
          });
          reqDateTT = true;
        }else{
          popup.close(registry.byId('extendedSearchDialog'));
          reqDateTT = false;
        }
      });
    };

    setPanelCalendar = function(value){
      if((value[0] !== "") && (value[1] !== "")){
        reqToShow('reqCalSetTime');
        reqToHide('reqCalAllTime');
      }else{
        reqToHide('reqCalSetTime');
        reqToShow('reqCalAllTime');
      }
      query('#reqDate1').val(value[0]);
      query('#reqDate2').val(value[1]);
      query('span[data-dojo-reqDate="1"]').html(value[0]);
      query('span[data-dojo-reqDate="2"]').html(value[1]);
    };

    /*
    Help Functions
     */

    reqToHide = function(id){
      array.forEach(arguments,function(item){
        domClass.add(item,'dijitHidden');
        domStyle.set(item,"display","none");
      });
    };

    reqToShow = function(id){
      array.forEach(arguments,function(item){
        domClass.remove(item,'dijitHidden');
        domStyle.set(item,"display","block");
      });
    };

    hideDialog = function(did){
      registry.byId(did).hide();
    };

    showDialog = function(did,item){
      let dia = registry.byId(did);
      dia.draggable = false;
      dia.closeButtonNode.style.right = "22px";

      domStyle.set(dia.titleBar,{
        display:(item === undefined)?"block":"none"
      });
      domStyle.set(dia.containerNode,{
        border:"0 #000000 solid"

      });

      domStyle.set(dia.domNode,{
        "border-radius":"8px",
        "background-color":"white",
        "box-shadow":"0px 0px 8px rgba(0,0,0,0.52)",
        border:"1px #D0D0D0 solid"

      });

      registry.byId(did).show();
    };

    clearMemHide = function(){
      eqFullSearch = {};
      eqFullSearch.items = [];
      eqFullSearch.checkId = "";
      query('#reqDiffUserId','data-req-userid','');
      query('#reqDiffUserId').html('');

    };

    changeGlobalDialog = function(toHide,toShow){
      hideDialog(toHide);
      showDialog(toShow);
      query('#viewReqPart').style({display:'none'});
    };

    getUserName = function(arr){
      if(arr === null){
        return false;
      }
      arr = arr.split(" ");
      return arr[arr.length - 2] + " " + arr[arr.length - 1];
    };

    getUserPos = function(arr){
      if(arr === null){
        return false;
      }
      arr = arr.split(" ");
      arr.length = arr.length - 2;
      return arr.join(" ");
    };

    getFilterAgreement = function(){
      request.post("/req/dictionaries/filterAgreement.php").then(function(adata){
        storeFilAgr = new memory({
          idProperty:"id",
          labelAttr:"nm",
          data:JSON.parse(adata)
        });
      },function(error){

      });
    };

    getUnitUnite = function(){
      request.post("/req/dictionaries/unitUnite.php").then(function(adata){
        storeFilUnite = new memory({
          idProperty:"id",
          labelAttr:"nm",
          data:JSON.parse(adata)
        });
        registry.byId('edElUnitUniteId').set('store',storeFilUnite);
      },function(error){

      });
    };

    getDivisions = function(){
      request.post("/req/dictionaries/division.php").then(function(adata){
        storeFilDiv = new memory({
          idProperty:"id",
          labelAttr:"nm",
          data:JSON.parse(adata)
        });
        registry.byId('edElDivisionId').set('store',storeFilDiv);
      },function(error){

      });
    };

    /*
    Dop
     */

    hidePallette = function(){
      paletteShown = false;
      selectInstId = -1;
      popup.hide(registry.byId("instPanel"));
    };

    sharedInstance = {
      init:function(_baseName,reqR){
        reqRo = reqR;
        prefix = "/" + _baseName;
        baseName = _baseName;
        startLoading();
        startup();
      }
    };

    return sharedInstance;

  });
// Март 2019