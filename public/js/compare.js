google.load("visualization", "1", {packages: ["table","corechart"]});
google.setOnLoadCallback(updateCompare);

$(document).ready(function() {
  $('#updateCompare').on('click', updateCompare);
});

var basicTable, basicChart,catTable,budTable;

function updateCompare() {
  var holder = $('#compareContent');
  //holder.empty();
  makeTitle();
  var dates = getDates();

  // first, we get some basic information for a table:
  $.getJSON('/home/compare/basictable', {base: dates[0], compare: dates[1]}, function(data) {
    if (basicTable === undefined) {
      basicTable = new google.visualization.Table(document.getElementById('basicTable'));
    }
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      // money format everything:
      money.format(gdata, i);
      var amount = gdata.getValue(0, i);
      var arrow = new google.visualization.ArrowFormat({base: amount});
      arrow.format(gdata, i);
    }
    $('#basicTable').removeClass('loading');
    basicTable.draw(gdata, {allowHTML: true});
  });

  // get the big chart
  $.getJSON('/home/compare/basicchart', {base: dates[0], compare: dates[1], account: $('select[name="account"]').val()}, function(data) {
    if (basicChart === undefined) {
      basicChart = new google.visualization.LineChart(document.getElementById('basicChart'));
    }

    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      // money format everything:
      money.format(gdata, i);
      var amount = gdata.getValue(0, i);
      var arrow = new google.visualization.ArrowFormat({base: amount});
      arrow.format(gdata, i);
    }
    $('#basicChart').removeClass('loading');
    basicChart.draw(gdata,{
      legend: {position:'bottom'},
      lineWidth: 1
    });
  });
  // category table
  $.getJSON('/home/compare/categories', {base: dates[0], compare: dates[1]}, function(data) {
    if (catTable === undefined) {
      catTable = new google.visualization.Table(document.getElementById('categories'));
    }
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      // money format everything:
      money.format(gdata, i);
    }
    $('#categories').removeClass('loading');
    catTable.draw(gdata, {allowHTML: true});
  });

  // budget table
  $.getJSON('/home/compare/budgets', {base: dates[0], compare: dates[1]}, function(data) {
    if (budTable === undefined) {
      budTable = new google.visualization.Table(document.getElementById('budgets'));
    }
    var gdata = new google.visualization.DataTable(data);
    var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
    for (i = 1; i < gdata.getNumberOfColumns(); i++) {
      // money format everything:
      money.format(gdata, i);
    }
    $('#budgets').removeClass('loading');
    budTable.draw(gdata, {allowHTML: true});
  });
}

function getDates() {
  var baseMonth = $('#baseCompare').val();
  var compares = [];
  $('#compareWith :selected').each(function(i, selected) {
    compares[i] = $(selected).val();
  });
  return new Array(baseMonth, compares);
}

function makeTitle() {
  var holder = $('#compareContent');
  $('#compareContent h3').remove();
  // title:
  var baseMonth = $('#baseCompare option:selected').text();

  // comparing values:
  var compares = [];
  $('#compareWith :selected').each(function(i, selected) {
    compares[i] = $(selected).text();
  });
  var i = 0;
  var subset = [];
  while (i < compares.length - 1) {
    subset[i] = compares[i];
    i++;
  }
  // we reageren toch gewoon op compares:
  var title = 'X';
  if (compares.length === 1) {
    title = compares[0];
  } else if (compares.length === 2) {
    title = compares[1] + " and " + compares[2];
  } else if (compares.length > 2) {
    title = subset.join(', ');
    title = title + " and " + compares[i];
  }
  if (subset.length > 2) {
    title = title + " and " + compares[i];
  } else if (compares.length === 2) {
    title = compares[0] + " and " + compares[1];
  } else {

  }

  holder.prepend($('<h3>').text("Comparing " + baseMonth + " with " + title));

}