<!DOCTYPE HTML>
<?php
  include "/srv/www/portal/ini.php";
  include $rootd . "lib.php";
  include $rootd . "global/MIFDivisionSelector.php";
  include $rootd . "global/MIFCalendar.php";
  include $rootd . "global/MIFSearchField.php";
  include $rootd . "global/MIFUserSelector.php";

  $currentModule = "req";
  $currentTitle = "Заявки";
  $requiredRoles = '["ADMIN","REQ_ADMIN","REQ_EDITOR","REQ_VIEWER"]';
?>
<html lang="ru">
<head>
  <title><?php echo $currentTitle; ?></title>
  <meta charset="utf-8">
  <link rel="icon" type="image/png" href="/favicon.png" />
  <LINK REL="shortcut icon" HREF="/favicon.png">
  <link rel="stylesheet" href="/dijit/themes/claro/claro.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="/dgrid/css/dgrid.css<?php echo randomPage(); ?>">
  <link rel="stylesheet" href="/dgrid/css/skins/claro.css<?php echo randomPage(); ?>">
  <link rel="stylesheet" href="/css/edits.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="/css/menu.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="/css/global.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="/css/buttons.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="/css/panels.css<?php echo randomPage(); ?>" media="screen">
  <link rel="stylesheet" href="pickmeup.css">
  <link rel="stylesheet" href="style.css<?php echo randomPage(); ?>" media="screen">
  <script type="text/javascript" src="pickmeup.js"></script>
  <script>
    var dojoConfig = (function () {
      var base = location.href.split("/");
      base.pop();
      base = base.join("/");
      return {
        async: true,
        isDebug: true,
        cacheBust: true,
        parseOnLoad: false,
        packages: [{
          name: "KSReqMain",
          location: base + "/"
        }]
      };
    })();
  </script>
</head>

<body class="claro" style="cursor:default">
<?php include_once $rootd . "global/logo.php"; ?>
<?php include_once $rootd . "global/loadingOverlay.php"; ?>

<div class="dijitHidden" id="appLayout" data-dojo-type="dijit/layout/BorderContainer" data-dojo-props="gutters:true, liveSplitters:false">

  <div data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region:'top', splitter:false" id="headerPanel" style="width:100%; height:50px; padding: 0; margin: 0; overflow: hidden;border: none; border-bottom: #E5E5E5 solid 1px;">

    <div class="MIF0px MINavigationBar" style="height: 50px; background-color: white;padding: 0;margin: 0; ">
      <div class="MINavBarItem" style="right:50%;margin-right:-050px;top:15px;">
        <a href="/<?php echo $currentModule; ?>/" style="color:#434343;font-size:14pt;font-weight:600;"><?php echo $currentTitle; ?></a>
        <img src="i.png" class="reqImgInfo" id="reqImgIconInfoIndex"/>
      </div>
      <div class="MINavBarItem" style="right:28px;top:15px;">
        <a id="fioField" href="#"></a>
      </div>
      <div class="MINavBarItem" style="right:11px;top:15px;">
        <button id="showUserPanel" class="MFDropDownButton"></button>
      </div>
    </div>

    <div class="dijitHidden">
      <?php include_once $rootd . "global/MIFUserPanel.php"; ?>
    </div>
  </div>

  <div data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region:'top', splitter:false" id="reqHeaderPanel"
       style="width:100%; height:50px; padding:0;margin:0;overflow: hidden;border:none; border-bottom:#E5E5E5 solid 1px;background-color:#f5f5f5;">
    <div style="display:table;width:100%;height:50px;">
      <div style="display:table-row;width:100%;height:50px;">
        <div style="display:table-cell;vertical-align: middle;width:10%;text-align: left;height: 50px;">
          <button id="addNewReqButton" style="background: url('/images/MIAdd.png') 0 0 no-repeat;margin-left: 10px;" class="MIToolBarButton"></button>
        </div>
        <div id="extSearchBtn">
          <div class="reqPanelCalendar">
            <div><span>Период:</span></div>
            <div id="reqCalSetTime">
              <span data-dojo-reqDate="1"></span><span>&nbsp;-&nbsp;</span><span data-dojo-reqDate="2"></span>
            </div>
            <div id="reqCalAllTime">
              <span>Всё время</span>
            </div>
          </div>
        </div>
        <div class="reqSearchDiv">
          <div class="reqSearchChDiv">
            <input type="text" name="reqSearchI" id="reqSearchInput" placeholder="Введите номер заявки"/>
            <div id="reqClearInput"></div>
          </div>
        </div>
        <div class="reqButtonDiv">
          <button id="reqSearchButton" class="MIButtonDefault reqGreenButton" type="button">Искать</button>
        </div>
        <div style="display:table-cell;width:10%;"></div>
        <div style="display:table-cell;width:50%;"></div>
        <div style="display:table-cell;width:10%;"></div>
      </div>
    </div>
    <div class="dijitHidden">
      <div data-dojo-type="dijit/TooltipDialog" id="extendedSearchDialog">
        <div>
          <input type="hidden" name="date1" value="" id="reqDate1"/>
          <input type="hidden" name="date2" value="" id="reqDate2"/>
          <div class="reqThreeCalendars"></div>
          <div align="right">
            <button id="reqClearFilterDate" class="MIButtonDefault reqRedButton" type="button">Очистить</button>
            <button id="reqSetFilterDate" class="MIButtonDefault reqGreenButton" type="button">Искать</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="MIMenu" data-dojo-type="dijit/layout/ContentPane" data-dojo-props="minSize:160, maxSize:180, region:'leading', splitter:true" id="leftPanel" style="width:<?php if (isset($_COOKIE["LeftPanelWidth"])) {
    echo $_COOKIE["LeftPanelWidth"]."px";
  } else {
    echo "213px";
  } ?>;padding:0;margin:0; border: none;border-right: #E5E5E5 solid 1px;">
    <div class="MIMenuItem" id="menuItem0">Все</div>
    <div class="MIMenuItem" id="menuItem1">Новые</div>
    <div class="MIMenuItem" id="menuItem2">Утверждённые</div>
    <div class="MIMenuItem" id="menuItem3">Открытые</div>
    <div class="MIMenuItem" id="menuItem6">Ремонт окончен</div>
    <div class="MIMenuItem" id="menuItem4">Закрытые</div>
    <div class="MIMenuItem" id="menuItem5">Отклонённые</div>
    <div class="MIMenuItem" id="menuItem7">Продлённые</div>
    <div class="MIMenuItem" id="menuItem8">Удалённые</div>
    <div style="border-top: 1px solid #E5E5E5"></div>
  </div>

  <div id="medPanel" data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region: 'center', splitter:true" style="padding: 0; margin: 0;border: none;"></div>

  <div id="medBottomPanel" data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region: 'bottom'" style="padding: 0; margin: 0;border: none;">
    <div class="dijitHidden">
      <div data-dojo-type="dijit/TooltipDialog" id="instPanel" style="width:120px;height:50px;">
        <div>
          <div id="selEdCont">
            <button class="MIInnerButton" id='selectEdit'>Изменить</button>
          </div>
          <div id="selDelCont">
            <button class="MIInnerDeleteButton" id='selectDelete'>Удалить</button>
          </div>
        </div>
      </div>
    </div>
    <?php include_once("REQDialogs.php"); ?>
  </div>

</div>
<script src="/dojo/dojo.js"></script>
<script>
  require(["<?php echo $currentModule; ?>/main/KSReqMain", "dojo/domReady!"], function (kmain, ready) {
    kmain.init("<?php echo $currentModule; ?>", '<?php echo $requiredRoles;?>');
  });
</script>

</body>
</html>