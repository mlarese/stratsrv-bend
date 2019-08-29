<?php

?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4"
    crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt"
    crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
  <!--script src="https://cdn.jsdelivr.net/npm/sweetalert2"></script-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.0.8/sweetalert.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <script src="js/function.js"></script>
  <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <style>
      .container {
          max-width: 90%;
      }
      .form-group.map {
          border-left: 1px solid rgba(0,0,0,.1);
      }
      .draggable-container {
          margin: 20px;
          padding: 15px;
      }
      .draggable-container .row {
          margin-top: 5px !important;
          margin-bottom: 5px !important;
      }
      .draggable,
      .draggable-no-revert {
          padding: 5px 10px;
          border: solid 1px #aaa;
          border-radius: 5px;
          background-color: #fff;
          z-index: 1;
      }
      .draggable {
          cursor: grab;
      }
      .draggable-no-revert {
          display: inline-flex;
          margin-left: 5px;
          margin-right: 5px;
      }
      .droppable {
          padding: 10px 15px;
          width: 100%;
          min-height: 50px;
          border: solid 1px #aaa;
          border-radius: 5px;
          z-index: 0;
      }
      .fa-trash-alt {
          margin-left: 15px;
          cursor: pointer;
      }
  </style>
</head>
<body>
  <div class="container-fluid py-3">
    <div class="container">
      <form>
        <div class="row">
          <div class="form-group col-sm-6 col-md-4">
            <label for="exampleFormControlSelect1">Scegli:</label>
            <select class="form-control" id="selectType">
              <option value="0"></option>
              <option value="1">DATAONE</option>
              <option value="2">MAILONE</option>
              <option value="3">ABS STRUCTURE RESERVATION</option>
              <option value="4">ABS PORTAL RESERVATION</option>
              <option value="5">ABS ENQUIRY</option>
              <option value="6">ABS STORE ONE</option>
              <option value="7">ADVANCED IMPORTER</option>
            </select>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="container-fluid formOne py-5">
    <div class="container">
      <h3>Compila il seguente form:</h3>
      <hr>
      <form id="formOne">
        <div class="row">
          <div class="form-group col-sm-4">
            <label for="ownerId">Owner Id</label>
            <input name="ownerId" type="text" class="form-control" id="ownerId">
          </div>
          <div class="form-group col-sm-4">
            <label for="termId">Term Id</label>
            <input name="termId" type="text" class="form-control" id="termId">
          </div>
          <div class="form-group col-sm-4">
            <label for="host">Host</label>
            <input name="host" type="text" class="form-control" id="host">
          </div>
          <div class="form-group col-sm-4">
            <label for="dbname">db name</label>
            <input name="dbname" type="text" class="form-control" id="dbname">
          </div>
          <div class="form-group col-sm-4">
            <label for="user">User</label>
            <input name="user" type="text" class="form-control" id="user">
          </div>
          <div class="form-group col-sm-4">
            <label for="password">Password</label>
            <input name="password" type="password" class="form-control" id="password">
          </div>
          <div class="form-group col-sm-4">
            <label for="domain">Upgrade Domain ID</label>
            <input name="domain" type="text" class="form-control" id="domain">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Invia</button>
      </form>
    </div>
  </div>
  <div class="container-fluid formMailOne py-5">
      <div class="container">
          <h3>Compila il seguente form:</h3>
          <hr>
          <form id="formMailOne">
              <div class="row">
                  <div class="form-group col-sm-4">
                      <label for="ownerId">Owner Id</label>
                      <input name="ownerId" type="text" class="form-control" id="ownerId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="termId">Term Id</label>
                      <input name="termId" type="text" class="form-control" id="termId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="host">Host</label>
                      <input name="host" type="text" class="form-control" id="host">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="dbname">db name</label>
                      <input name="dbname" type="text" class="form-control" id="dbname">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="user">User</label>
                      <input name="user" type="text" class="form-control" id="user">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="password">Password</label>
                      <input name="password" type="password" class="form-control" id="password">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="listid">List id</label>
                      <input name="listid" type="text" class="form-control" id="listid">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="domain">Domain</label>
                      <input name="domain" type="text" class="form-control" id="domain">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="language">Language</label>
                      <input name="language" type="text" class="form-control" id="language">
                  </div>
              </div>
              <button type="submit" class="btn btn-primary">Invia</button>
          </form>
      </div>
  </div>
  <div class="container-fluid formAbsStructureReservation py-5">
    <div class="container">
      <h3>Compila il seguente form:</h3>
      <hr>
      <form id="formAbsStructureReservation" enctype="multipart/form-data">
        <div class="row">
          <div class="form-group col-sm-4">
            <label for="ownerId">Owner Id</label>
            <input name="ownerId" type="text" class="form-control" id="ownerId">
          </div>
            <div class="form-group col-sm-4">
                <label for="structureId">Structure Id</label>
                <input name="structureId" type="text" class="form-control" id="structureId">
            </div>
          <div class="form-group col-sm-4">
            <label for="termId">Term Id</label>
            <input name="termId" type="text" class="form-control" id="termId">
          </div>
          <div class="form-group col-sm-4">
            <label for="csv">Inserisci il file csv:</label>
            <input name="csv" type="file" class="form-control-file" id="csvreservation">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Invia</button>
      </form>
    </div>
  </div>
  <div class="container-fluid formAbsPortalReservation py-5">
      <div class="container">
          <h3>Compila il seguente form:</h3>
          <hr>
          <form id="formAbsPortalReservation" enctype="multipart/form-data">
              <div class="row">
                  <div class="form-group col-sm-4">
                      <label for="ownerId">Owner Id</label>
                      <input name="ownerId" type="text" class="form-control" id="ownerId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="termId">Term Id</label>
                      <input name="termId" type="text" class="form-control" id="termId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="csv">Inserisci il file csv:</label>
                      <input name="csv" type="file" class="form-control-file" id="csvportal">
                  </div>
              </div>
              <button type="submit" class="btn btn-primary">Invia</button>
          </form>
      </div>
  </div>
  <div class="container-fluid formAbsEnquiry py-5">
      <div class="container">
          <h3>Compila il seguente form:</h3>
          <hr>
          <form id="formAbsEnquiry" enctype="multipart/form-data">
              <div class="row">
                  <div class="form-group col-sm-4">
                      <label for="ownerId">Owner Id</label>
                      <input name="ownerId" type="text" class="form-control" id="ownerId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="termId">Term Id</label>
                      <input name="termId" type="text" class="form-control" id="termId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="enquiryUrl">Enquiry URL</label>
                      <input name="enquiryUrl" type="text" class="form-control" id="enquiryUrl">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="csv">Inserisci il file csv:</label>
                      <input name="csv" type="file" class="form-control-file" id="csvenquiry">
                  </div>
              </div>
              <button type="submit" class="btn btn-primary">Invia</button>
          </form>
      </div>
  </div>
  <div class="container-fluid formAbsStoreONE py-5">
      <div class="container">
          <h3>Compila il seguente form:</h3>
          <hr>
          <form id="formAbsStoreONE" enctype="multipart/form-data">
              <div class="row">
                  <div class="form-group col-sm-4">
                      <label for="ownerId">Owner Id</label>
                      <input name="ownerId" type="text" class="form-control" id="ownerId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="termId">Term Id</label>
                      <input name="termId" type="text" class="form-control" id="termId">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="storeONERegistrationUrl">StoreONE registration URL</label>
                      <input name="storeONERegistrationUrl" type="text" class="form-control" id="storeONERegistrationUrl">
                  </div>
                  <div class="form-group col-sm-4">
                      <label for="csv">Inserisci il file csv:</label>
                      <input name="csv" type="file" class="form-control-file" id="csvstoreone">
                  </div>
              </div>
              <button type="submit" class="btn btn-primary">Invia</button>
          </form>
      </div>
  </div>
  <div class="container-fluid advancedImporter py-5">
      <div class="container">
          <h3>Carica prima il file CSV</h3>
          <hr>
          <form id="advancedImporterPreset" name="advancedImporterPreset" enctype="multipart/form-data">
              <div class="row">
                  <div class="form-group col-sm-6">
                      <div class="row">
                          <div class="form-group col-sm-3">
                              <label for="columnSeparator">Carattere separatore</label>
                              <input name="columnSeparator" type="text" class="form-control" id="columnSeparator">
                          </div>
                          <div class="form-group col-sm-6">
                              <label for="csv">Inserisci il file csv:</label>
                              <input name="csv" type="file" class="form-control-file" id="csvadvancedimporter">
                          </div>
                      </div>
                  </div>
              </div>
              <button type="submit" class="btn btn-primary">Recupera i campi del file CSV</button>
          </form>
          <hr>
          <h3>Mappa le tabelle del file CSV</h3>
          <form id="advancedImporterImport" name="advancedImporterImport" enctype="multipart/form-data">
              <input name="tmpcsv" type="hidden" class="form-control" id="tmpcsv">
              <div class="row">
                  <div class="form-group col-sm-4">
                      <div class="draggable-container">
                      </div>
                  </div>
                  <div class="form-group col-sm-8 map">
                      <div class="row">
                          <div class="form-group col-sm-2">
                              <label for="utf8Encode">UTF8 Encode</label>
                              <input name="utf8Encode" type="checkbox" class="form-control" id="utf8Encode">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="ownerIdDefault">Owner ID</label>
                              <input name="ownerIdDefault" type="text" class="form-control" id="ownerIdDefault">
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="ownerIdRequired">Required</label>
                              <input name="ownerIdRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="ownerIdRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="termIdDefault">Term ID</label>
                              <input name="termIdDefault" type="text" class="form-control" id="termIdDefault">
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="termIdRequired">Required</label>
                              <input name="termIdRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="termIdRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="externalId">External ID</label>
                              <div name="externalId" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="externalIdRequired">Required</label>
                              <input name="externalIdRequired" type="checkbox" class="form-control" id="externalIdRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="email">Email</label>
                              <div name="email" class="droppable ui-widget-header" type="text"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="emailRequired">Required</label>
                              <input name="emailRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="emailRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="name">Name</label>
                              <div name="name" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="nameRequired">Required</label>
                              <input name="nameRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="nameRequired">
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="nameFormat">Format</label>
                              <select name="nameFormat" class="form-control" id="nameFormat">
                                  <option value="Name">Name</option>
                                  <option value="XXXX Name">XXXX Name</option>
                                  <option value="Name XXXX">Name XXXX</option>
                              </select>
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="surname">Surname</label>
                              <div name="surname" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="surnameRequired">Required</label>
                              <input name="surnameRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="surnameRequired">
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="surnameFormat">Format</label>
                              <select name="surnameFormat" class="form-control" id="surnameFormat">
                                  <option value="Surname">Surname</option>
                                  <option value="XXXX Surname">XXXX Surname</option>
                                  <option value="Surname XXXX">Surname XXXX</option>
                              </select>
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="language">Language</label>
                              <div name="language" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="languageRequired">Required</label>
                              <input name="languageRequired" type="checkbox" checked="checked" readonly disabled class="form-control" id="languageRequired">
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="languageFormat">Format</label>
                              <select name="languageFormat" class="form-control">
                                  <option value="XX">XX</option>
                                  <option value="Xxxxx">Xxxxx</option>
                                  <option value="XX_XX">XX_XX</option>
                              </select>
                          </div>
                          <div class="form-group col-sm-2">
                              <label for="languageDefault">Default value</label>
                              <input name="languageDefault" type="text" class="form-control" id="languageDefault">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="ipaddress">IP Address</label>
                              <div name="ipaddress" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="ipaddressRequired">Required</label>
                              <input name="ipaddressRequired" type="checkbox" class="form-control" id="ipaddressRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="telephone">Telephone</label>
                              <div name="telephone" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="telephoneRequired">Required</label>
                              <input name="telephoneRequired" type="checkbox" class="form-control" id="telephoneRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="mobile">Mobile</label>
                              <div name="mobile" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="mobileRequired">Required</label>
                              <input name="mobileRequired" type="checkbox" class="form-control" id="mobileRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="fax">Fax</label>
                              <div name="fax" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="faxRequired">Required</label>
                              <input name="faxRequired" type="checkbox" class="form-control" id="faxRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="address">Address</label>
                              <div name="address" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="addressRequired">Required</label>
                              <input name="addressRequired" type="checkbox" class="form-control" id="addressRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="zipcode">ZIP Code</label>
                              <div name="zipcode" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="zipcodeRequired">Required</label>
                              <input name="zipcodeRequired" type="checkbox" class="form-control" id="zipcodeRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="city">City</label>
                              <div name="city" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="cityRequired">Required</label>
                              <input name="cityRequired" type="checkbox" class="form-control" id="cityRequired">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="country">Country</label>
                              <div name="country" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="countryRequired">Required</label>
                              <input name="countryRequired" type="checkbox" class="form-control" id="countryRequired">
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="countryFormat">Format</label>
                              <select name="countryFormat" class="form-control" id="countryFormat">
                                  <option value="XX">XX</option>
                                  <option value="Xxxxxxx">Xxxxxxx</option>
                              </select>
                          </div>
                          <div class="form-group col-sm-2">
                              <label for="countryDefault">Default value</label>
                              <input name="countryDefault" type="text" class="form-control" id="countryDefault">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="registrationDate">Registration Date</label>
                              <div name="registrationDate" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="registrationDateRequired">Required</label>
                              <input name="registrationDateRequired" type="checkbox" class="form-control" id="registrationDateRequired">
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="registrationDateFormat">Format</label>
                              <select name="registrationDateFormat" class="form-control" id="registrationDateFormat">
                                  <option value="YYYY/MM/DD">YYYY/MM/DD</option>
                                  <option value="YYYY/MM/DD HH:MM:SS">YYYY/MM/DD HH:MM:SS</option>
                                  <option value="YY/MM/DD">YY/MM/DD</option>
                                  <option value="YY/MM/DD HH:MM:SS">YY/MM/DD HH:MM:SS</option>
                                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                  <option value="YYYY-MM-DD HH:MM:SS">YYYY-MM-DD HH:MM:SS</option>
                                  <option value="YY-MM-DD">YY-MM-DD</option>
                                  <option value="YY-MM-DD HH:MM:SS">YY-MM-DD HH:MM:SS</option>
                              </select>
                          </div>
                          <div class="form-group col-sm-3">
                              <label for="registrationDateDefault">Default value</label>
                              <input name="registrationDateDefault" type="text" class="form-control" id="registrationDateDefault">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="registrationUrl">Registration URL</label>
                              <div name="registrationUrl" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="registrationUrlRequired">Required</label>
                              <input name="registrationUrlRequired" type="checkbox" class="form-control" id="registrationUrlRequired">
                          </div>
                          <div class="form-group col-sm-6">
                              <label for="registrationUrlDefault">Default value</label>
                              <input name="registrationUrlDefault" type="text" class="form-control" id="registrationUrlDefault">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="personalDataAgreement">Persona Data Agreement</label>
                              <div name="personalDataAgreement" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="personalDataAgreementRequired">Required</label>
                              <input name="personalDataAgreementRequired" type="checkbox" class="form-control" id="personalDataAgreementRequired">
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="personalDataAgreementDefault">Default</label>
                              <input name="personalDataAgreementDefault" type="checkbox" checked="checked" class="form-control" id="personalDataAgreementDefault">
                          </div>
                      </div>
                      <div class="row">
                          <div class="form-group col-sm-5">
                              <label for="newsletterAgreement">Newsletter Agreement</label>
                              <div name="newsletterAgreement" class="droppable ui-widget-header"></div>
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="newsletterAgreementRequired">Required</label>
                              <input name="newsletterAgreementRequired" type="checkbox" class="form-control" id="newsletterAgreementRequired">
                          </div>
                          <div class="form-group col-sm-1">
                              <label for="newsletterAgreementDefault">Default</label>
                              <input name="newsletterAgreementDefault" type="checkbox" class="form-control" id="newsletterAgreementDefault">
                          </div>
                      </div>
                      <hr>
                      <button type="submit" class="dry-run-csv btn btn-primary">Dry run CSV</button>
                      <button type="submit" class="submit-csv btn btn-primary">Invia CSV</button>
                  </div>
              </div>
          </form>
      </div>
  </div>
</body>
</html>