google.load('visualization', '1.0', {'packages': ['controls', 'corechart', 'table']});
google.setOnLoadCallback(drawTarget);

$(document).ready(function() {
  $('.deleteTarget').on('click',deleteTarget);
});

var opt = {
        lineWidth: 1,
        height: 180,
        hAxis: {
          gridlines: {
            count: 2
          }
        }, legend: {position: 'none'}, chartArea: {left: 40, width: '100%'}, colors: ['blue', 'green']}

function deleteTarget(ev) {
  var target = $(ev.target);
  if(target.hasClass('btn')) {
    var row = target.parent().parent();
  } else {
    var row = target.parent().parent().parent();

  }
  $('#delTargetName').text($('td:nth-child(1) a',row).text())

  var ID = $(ev.target).attr('data-value');
  $('#modal form').attr('action','/home/target/delete/' + ID);
  $('#modal').modal();
}


function drawTarget() {
  $.getJSON('/home/target/overviewChart/' + ID, function(data) {
      var chart = new google.visualization.AreaChart(document.getElementById('chart'));
      var gdata = new google.visualization.DataTable(data);
      var money = new google.visualization.NumberFormat({decimalSymbol: ',', groupingSymbol: '.', prefix: '€ '});
      for (i = 1; i < gdata.getNumberOfColumns(); i++) {
        money.format(gdata, i);
      }
      chart.draw(gdata,opt);
    });
}