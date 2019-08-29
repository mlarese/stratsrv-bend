$(document).ready(function(){
  //Init
  $('.formOne').hide();
  $('.formMailOne').hide();
  $('.formAbsStructureReservation').hide();
  $('.formAbsPortalReservation').hide();
  $('.formAbsEnquiry').hide();
  $('.formAbsStoreONE').hide();
  $('.advancedImporter').hide();
  //Select tipologia
  $('#selectType').change(function(){
    $('.formOne').hide();
    $('.formMailOne').hide();
    $('.formAbsStructureReservation').hide();
    $('.formAbsPortalReservation').hide();
    $('.formAbsEnquiry').hide();
    $('.formAbsStoreONE').hide();
    $('.advancedImporter').hide();
    if($(this).val()==1){
      $('.formOne').show();
    } else if ($(this).val() == 2) {
      $('.formMailOne').show();
    } else if ($(this).val() == 3) {
      $('.formAbsStructureReservation').show();
    } else if ($(this).val() == 4) {
      $('.formAbsPortalReservation').show();
    } else if ($(this).val() == 5) {
      $('.formAbsEnquiry').show();
    } else if ($(this).val() == 6) {
      $('.formAbsStoreONE').show();
    } else if ($(this).val() == 7) {
      $('.advancedImporter').show();
    }
  });
  //Submit form FormOne
  $('#formOne').on('submit', function (e) {
    e.preventDefault();
    var item = $(this).serializeArray();
    $.ajax({
      url: '/api/import/dataone/upgrade',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(item),
      dataType: "json",
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formOne'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result']=='welcome'){
          $('#formOne')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  //Submit form MailOne
  $('#formMailOne').on('submit', function (e) {
    e.preventDefault();
    var item = $(this).serializeArray();
    $.ajax({
      url: '/api/import/mailone/newsletter',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(item),
      dataType: "json",
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formMailOne'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result']=='welcome'){
          $('#formOne')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  //Submit form formAbs
  $('#formAbsStructureReservation').on('submit', function (e) {
    e.preventDefault();
    var item = new FormData();
    item.append('file', $('#csvreservation')[0].files[0]);
    var myData = $(this).serialize();
    item.append('myData', myData);
    $.ajax({
      url: '/api/import/abs/structure/reservation',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formAbsStructureReservation'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result'] == 'welcome') {
          $('#formAbsStructureReservation')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  //Submit form formAbs
  $('#formAbsPortalReservation').on('submit', function (e) {
    e.preventDefault();
    var item = new FormData();
    item.append('file', $('#csvportal')[0].files[0]);
    var myData = $(this).serialize();
    item.append('myData', myData);
    $.ajax({
      url: '/api/import/abs/portal/reservation',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formAbsPortalReservation'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result'] == 'welcome') {
          $('#formAbsPortalReservation')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  //Submit form formAbs
  $('#formAbsEnquiry').on('submit', function (e) {
    e.preventDefault();
    var item = new FormData();
    item.append('file', $('#csvenquiry')[0].files[0]);
    var myData = $(this).serialize();
    item.append('myData', myData);
    $.ajax({
      url: '/api/import/abs/enquiry',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formAbsEnquiry'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result'] == 'welcome') {
          $('#formAbsEnquiry')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  //Submit form StoreONE
  $('#formAbsStoreONE').on('submit', function (e) {
    e.preventDefault();
    var item = new FormData();
    item.append('file', $('#csvstoreone')[0].files[0]);
    var myData = $(this).serialize();
    item.append('myData', myData);
    $.ajax({
      url: '/api/import/abs/storeone',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#formAbsStoreONE'));
      },
      success: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (msg['result'] == 'welcome') {
          $('#formAbsStoreONE')[0].reset();
          swal({
            type: 'success',
            title: 'Dati inviati correttamente'
          });
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  // Advanced import CSV preset
  $('#advancedImporterPreset').on('submit', function (e) {
    e.preventDefault();
    var item = new FormData();
    item.append('file', $('#csvadvancedimporter')[0].files[0]);
    var myData = $(this).serialize();
    item.append('myData', myData);
    $.ajax({
      url: '/api/import/advancedimporter/preset',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#advancedImporterPreset'));
      },
      success: function (res) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (res) {
          swal({
            type: 'success',
            title: 'Sono stati recuperati i campi del file CSV'
          });
          setAndvancedImporter(res)
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.statusText
        })
      }
    });
  });
  // Populate advanced importer
  $('#advancedImporterImport').bind("DOMSubtreeModified", function () {
    $(".draggable").draggable({
      cursor: "move",
      revert: "valid"
    })
    $(".droppable").droppable({
      drop: function( event, ui ) {
        var obj = $(ui.draggable).first()
        var html = '<div id="'+ obj.attr('id') + '" import-column="' + obj.attr('import-column') + '" class="draggable-no-revert ui-widget-content">' + obj.html() + '<i class="far fa-trash-alt"></i></div>'
        $(this).append(html)
      }
    })
    $('.fa-trash-alt').click(function () {
      $(this).parent().remove()
    })
  })
  $( window ).scroll(function() {
    var elem = $('.draggable-container').first()
    var scrollTop     = $(window).scrollTop(),
      elementOffset = elem.offset().top - elem.css('margin-top').replace('px', ''),
      distance      = (elementOffset - scrollTop);
    if (distance <= 300) {
      distance = parseInt(distance) * -1
      if (distance > 0) {
        elem.css('margin-top', distance + 'px')
      }
    }
  });
  function setAndvancedImporter (res) {
    if (typeof res.result.tmp_csv === 'undefined' ||
        typeof res.result.header === 'undefined'
    ) {
      return;
    }

    $('#tmpcsv').val(res.result.tmp_csv)
    for (var id in res.result.header) {
      var html = '<div class="row"><div id="headerColumn' + id + '" import-column="' + id + '" class="draggable ui-widget-content">' + res.result.header[id] + '</div></div>'
      $('.draggable-container').first().append(html)
    }
  }
  $('#advancedImporterImport').on('submit', function (e) {
    e.preventDefault();

    var item = new FormData();
    var emitter = $(e.originalEvent.explicitOriginalTarget)
    var dryRun = true
    console.log(emitter)
    if (emitter.hasClass('dry-run-csv')) {
      item.append('action', 'dry-run')
    } else if (emitter.hasClass('submit-csv')) {
      item.append('action', 'submit')
      dryRun = false
    }

    // TMP csv
    item.append('file', $('#tmpcsv').val())

    // Column separator
    item.append('columnSeparator', $('#columnSeparator').val())

    // UTF8 encode
    item.append('utf8Encode', $('#utf8Encode:checked').length)

    // Add owner ID
    if ($('#ownerIdDefault').val() == '') {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo OwnerId non è settato'
      })
      return;
    }
    item.append('ownerId', $('#ownerIdDefault').val())

    // Add term ID
    if ($('#termIdDefault').val() == '') {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo TermID non è settato'
      })
      return;
    }
    item.append('termId', $('#termIdDefault').val())

    // Add external ID
    $('#advancedImporterImport div[name=externalId]').find('.draggable-no-revert').each(function () {
      item.append('externalId[]', $(this).attr('import-column'))
    })
    item.append('externalIdRequired', $('#externalIdRequired:checked').length)

    // Add email
    $('#advancedImporterImport div[name=email]').find('.draggable-no-revert').each(function () {
      item.append('email[]', $(this).attr('import-column'))
    })
    if (item.getAll('email[]').length == 0) {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo email non è settato'
      })
      return;
    }

    // Add name
    $('#advancedImporterImport div[name=name]').find('.draggable-no-revert').each(function () {
      item.append('name[]', $(this).attr('import-column'))
    })
    item.append('nameFormat', $('#nameFormat').val())
    if (item.getAll('name[]').length == 0) {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo name non è settato'
      })
      return;
    }

    // Add surname
    $('#advancedImporterImport div[name=surname]').find('.draggable-no-revert').each(function () {
      item.append('surname[]', $(this).attr('import-column'))
    })
    item.append('surnameFormat', $('#surnameFormat').val())
    if (item.getAll('surname[]').length == 0) {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo surname non è settato'
      })
      return;
    }

    // Add language
    $('#advancedImporterImport div[name=language]').find('.draggable-no-revert').each(function () {
      item.append('language[]', $(this).attr('import-column'))
    })
    item.append('languageFormat', $('#languageFormat').val())
    if ($('#languageDefault').val() == '') {
      swal({
        type: 'error',
        title: 'Errore',
        text: 'Il campo language default value non è settato'
      })
      return;
    }
    item.append('languageDefault', $('#languageDefault').val())

    // Add IP address
    $('#advancedImporterImport div[name=ipaddress]').find('.draggable-no-revert').each(function () {
      item.append('ipaddress[]', $(this).attr('import-column'))
    })
    item.append('ipaddressRequired', $('#ipaddressRequired:checked').length)

    // Add telephone
    $('#advancedImporterImport div[name=telephone]').find('.draggable-no-revert').each(function () {
      item.append('telephone[]', $(this).attr('import-column'))
    })
    item.append('telephoneRequired', $('#telephoneRequired:checked').length)

    // Add mobile
    $('#advancedImporterImport div[name=mobile]').find('.draggable-no-revert').each(function () {
      item.append('mobile[]', $(this).attr('import-column'))
    })
    item.append('mobileRequired', $('#mobileRequired:checked').length)

    // Add fax
    $('#advancedImporterImport div[name=fax]').find('.draggable-no-revert').each(function () {
      item.append('fax[]', $(this).attr('import-column'))
    })
    item.append('faxRequired', $('#faxRequired:checked').length)

    // Add address
    $('#advancedImporterImport div[name=address]').find('.draggable-no-revert').each(function () {
      item.append('address[]', $(this).attr('import-column'))
    })
    item.append('addressRequired', $('#addressRequired:checked').length)

    // Add city
    $('#advancedImporterImport div[name=city]').find('.draggable-no-revert').each(function () {
      item.append('city[]', $(this).attr('import-column'))
    })
    item.append('cityRequired', $('#cityRequired:checked').length)

    // Add zipcode
    $('#zipcode').find('.draggable-no-revert').each(function () {
      item.append('zipcode[]', $(this).attr('import-column'))
    })
    item.append('zipcodeRequired', $('#zipcodeRequired:checked').length)

    // Add country
    $('#advancedImporterImport div[name=country]').find('.draggable-no-revert').each(function () {
      item.append('country[]', $(this).attr('import-column'))
    })
    item.append('countryFormat', $('#countryFormat').val())
    item.append('countryDefault', $('#countryDefault').val())
    item.append('countryRequired', $('#countryRequired:checked').length)

    // Add registration date
    $('#advancedImporterImport div[name=registrationDate]').find('.draggable-no-revert').each(function () {
      item.append('registrationDate[]', $(this).attr('import-column'))
    })
    item.append('registrationDateFormat', $('#registrationDateFormat').val())
    item.append('registrationDateDefault', $('#registrationDateDefault').val())
    item.append('registrationDateRequired', $('#registrationDateRequired:checked').length)

    // Add registration URL
    $('#advancedImporterImport div[name=registrationUrl]').find('.draggable-no-revert').each(function () {
      item.append('registrationUrl[]', $(this).attr('import-column'))
    })
    item.append('registrationUrlDefault', $('#registrationUrlDefault').val())
    item.append('registrationUrlRequired', $('#registrationUrlRequired:checked').length)

    // Add persona data agreement
    $('#advancedImporterImport div[name=personalDataAgreement]').find('.draggable-no-revert').each(function () {
      item.append('personalDataAgreement[]', $(this).attr('import-column'))
    })
    item.append('personalDataAgreementDefault', $('#personalDataAgreementDefault:checked').length)
    item.append('personalDataAgreementRequired', $('#personalDataAgreementRequired:checked').length)

    // Add newsletter agreement
    $('#advancedImporterImport div[name=newsletterAgreement]').find('.draggable-no-revert').each(function () {
      item.append('newsletterAgreement[]', $(this).attr('import-column'))
    })
    item.append('newsletterAgreementDefault', $('#newsletterAgreementDefault:checked').length)
    item.append('newsletterAgreementRequired', $('#newsletterAgreementRequired:checked').length)

    $.ajax({
      url: '/api/import/advancedimporter/import',
      type: 'POST',
      contentType: false,
      processData: false,
      data: item,
      crossDomain: true,
      cache: false,
      timeout: (1000 * 60 * 60 * 24),
      beforeSend: function () {
        $('<div class="loadingOver"><span class="fas fa-spinner mm-spin"></span></div>').insertBefore($('#advancedImporterImport'));
      },
      success: function (res) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        if (res) {
          if (dryRun) {
            var dryRunExamples = 'Righe trovate: ' + res.result.total_counter + ' Righe che possono essere caricate: ' + res.result.imported_counter + ' '
            dryRunExamples += 'Esempio di riga decodificata: '
            dryRunExamples += JSON.stringify(res.result.examples[0]) + ' '
            swal({
              type: 'success',
              title: 'CSV Dry Run',
              text: dryRunExamples
            });
          } else {
            swal({
              type: 'success',
              title: 'CSV caricato'
            });
          }
        }
      },
      error: function (msg) {
        $('.loadingOver').fadeOut(100, function () {
          $(this).remove();
        });
        swal({
          type: 'error',
          title: 'Errore',
          text: msg.responseJSON.result
        })
      }
    });
  })
});