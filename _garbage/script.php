<script>
  /** @OnlyCurrentDoc */

  function CreaPrezzi() {
    var spreadsheet = SpreadsheetApp.getActive();
    spreadsheet.getRange('a1').activate();
    spreadsheet.setActiveSheet(spreadsheet.getSheetByName('Spalla_Base_Prezzi'), true);
    var sheet = spreadsheet.getActiveSheet();
    sheet.getRange(1, 1, sheet.getMaxRows(), sheet.getMaxColumns()).activate();
    spreadsheet.setActiveSheet(spreadsheet.getSheetByName('Prezzi_Channel'), true);
    sheet = spreadsheet.getActiveSheet();
    var spreadsheet = SpreadsheetApp.getActive();
    spreadsheet.getRange('Simulatore!NH402').activate()
      .setValue(getSheetUrl());
    sheet.getRange(1, 1, sheet.getMaxRows(), sheet.getMaxColumns()).activate();
    spreadsheet.getRange('Spalla_Base_Prezzi!b2:ll368').copyTo(spreadsheet.getActiveRange(), SpreadsheetApp.CopyPasteType.PASTE_VALUES, false);
    spreadsheet.getRange('D6').activate();
    spreadsheet.setActiveSheet(spreadsheet.getSheetByName('Simulatore'), true);
    spreadsheet.getRange('NH402').activate();
    spreadsheet.getRange('Simulatore!NH402').copyTo(spreadsheet.getActiveRange(), SpreadsheetApp.CopyPasteType.PASTE_VALUES, false);
    spreadsheet.setActiveSheet(spreadsheet.getSheetByName('Prezzi_Channel'), true);
    spreadsheet.getRange('A1').activate();
  };
  function getSheetUrl() {
    var SS = SpreadsheetApp.getActiveSpreadsheet();
    var ss = SS.getActiveSheet();
    var url = '';
    url += SS.getUrl();
    url += '#gid=';
    url += ss.getSheetId();
    return url;
  }

  // invii channel

  function postPrices () {
    var shprz = getSheetPrezziChannel ();
    var ua = getUa();
    var data = shprz.getRange('A1:ID6').getValues();
    var config = getConfigData();
    var payload = {data: data,config:config, description: 'channel data', ua:ua};
    var options = {
      'method' : 'post',
      'payload' : payload
    };

    var resp = UrlFetchApp.fetch('https://stratservicemanager.scalingo.io/api/channels/prices', options);
    var st=JSON.stringify( payload );
    Logger.log({st:st});
  }

  function getConfigData() {
    var sheet = getSheetImpostazioni();
    var data = sheet.getRange('H1:H2').getValues();

    data = {
      propertyCode:data[0][0],
      apyKey:data[1][0]
    };
    //Logger.log(data);
    return data;
  }

  function getUa() {
    var sheet = getSheetUA();
    var data = sheet.getRange('G4:G43').getValues();
    var dataElab = [];

    for(var i=0; i<data.length;i++) {
      var cur = data[i][0];
      if(cur+''!='')
        dataElab.push(cur);
    }
    return dataElab;
  }

  function get_sheet(name) {
    var spreadsheet = SpreadsheetApp.getActive();
    spreadsheet.setActiveSheet(spreadsheet.getSheetByName(name), true);
    var sheet = spreadsheet.getActiveSheet();

    return spreadsheet.getActiveSheet();
  }

  function getSheetImpostazioni() {
    return get_sheet('Impostazioni');
  }

  function getSheetUA() {
    return get_sheet('ins_UA');
  }

  function getSheetPrezziChannel() {
    return get_sheet('Prezzi_Channel');
  }


</script>
