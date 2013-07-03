$(document).ready(function() {
  $('.deleteSetting').on('click',deleteSetting);
});


function deleteSetting(ev) {
  var target = $(ev.target);
  var ID = target.attr('data-value');

  $.post('/home/settings/delete',{id: ID},function() {


    // add option: (#newDateSelect).
    var opt = $('<option>').text($('#setting_' + ID).attr('data-datetext')).attr('label',$('#setting_' + ID).attr('data-datetext')).attr('value',$('#setting_' + ID).attr('data-datevalue'));
    $('#newDateSelect').append(opt);
    $('#newDateSelect option').sort(NASort).appendTo('#newDateSelect');
    $('#setting_' + ID).remove();

  });
}

function NASort(a, b) {
    var partsA = $(a).val().split('-');
    var partsB = $(b).val().split('-');

    var dateA = new Date(partsA[0], partsA[1]-1, partsA[2]); // months are 0-based
    var dateB = new Date(partsB[0], partsB[1]-1, partsB[2]); // months are 0-based

    if (dateA < dateB) {
        return -1;
    }
    else if (dateA > dateB) {
        return 1;
    }
    return 0;
};


/**
 *
  // new Date(year, month [, date [, hours[, minutes[, seconds[, ms]]]]])
  return new Date(parts[0], parts[1]-1, parts[2]); // months are 0-based




$('select option').sort(NASort).appendTo('select');
 */