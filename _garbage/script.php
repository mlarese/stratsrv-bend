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
    var data = shprz.getRange('A1:ID4').getValues();
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

  function columnToLetter(column){
    var temp, letter = '';
    while (column > 0){
      temp = (column - 1) % 26;
      letter = String.fromCharCode(temp + 65) + letter;
      column = (column - temp - 1) / 26;
    }
    return letter;
  }
  function getPrezziChannelHeader() {
    var shprz = getSheetPrezziChannel ();
    shprz.getRange('D1:ID1');

    var data = shprz.getRange('D1:ID1').getValues();
    var dataExport = [];

    data = data[0];
    for(var col = 0;col<data.length;col++) {
      curValue = data[col];
      var shiftedCol = col+1+3;
      if(curValue+''!='') {
        var type='dispo';
        var treatment='BB';
        var code='';
        var colName = columnToLetter(shiftedCol); //shift di 3 colonne

        var aCode = curValue.split('-');
        code=aCode[0];

        if(aCode.length>1){
          type = aCode[1];
          aType=code.split('FA');   if(aType.length==2) treatment='FA';
          aType=code.split('HB');   if(aType.length==2) treatment='HB';
          code=aType[0];
        }

        var newValue = {
          colIndex:(shiftedCol|0),
          completeName:curValue,
          type:type,
          colName:colName,
          code:code,
          treatment:treatment,
          data:[]
        };
        dataExport.push(newValue);

      }
    }
    // Logger.log(dataExport);
    return dataExport;

  }

  function addChannelData() {
    var heads = getPrezziChannelHeader();
    var shprz = getSheetPrezziChannel ();

    for(var i = 0;i<heads.length;i++) {
      var c = heads[i];
      var data=shprz.getRange(c.colName+'2:'+c.colName+'367').getValues();
      //Logger.log(data);
    }
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
