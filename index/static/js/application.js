var app = function() {

    var init = function() {

        tooltips();
        toggleMenuLeft();
        switcheryToggle();
        menu();
        togglePanel();
        closePanel();
	reloadTable();
    };

    var reloadTable = function () {
      var reload = function () {
	console.log('reload');
        var $exploitedlistgrouped = $('#exploitedlistgrouped tbody');
        var $hitslistgrouped = $('#hitslistgrouped tbody');
        var path = '/new/ajax.php?f=';

        $.ajax({
          url: path+'hits'
        }).done(function(result) {
          $hitslistgrouped.html(result);
          //$('#hitslistgrouped').dataTable().fnDestroy();
          //$('#hitslistgrouped').dataTable();
        });

        $.ajax({
          url: path+'exploited'
        }).done(function(result) {
          $exploitedlistgrouped.html(result);
          //$('#exploitedlistgrouped').dataTable().fnDestroy();
          //$('#exploitedlistgrouped').dataTable();
        });
      };
      var myVar = setInterval(reload, 16000);
    };

    var tooltips = function() {
        $('#toggle-left').tooltip();
    };

    var togglePanel = function() {
        $('.actions > .fa-chevron-down').click(function() {
            $(this).parent().parent().next().slideToggle('fast');
            $(this).toggleClass('fa-chevron-down fa-chevron-up');
        });

    };

    var toggleMenuLeft = function() {
        $('#toggle-left').bind('click', function(e) {
           $('body').removeClass('off-canvas-open')    
            var bodyEl = $('#container');
            ($(window).width() > 768) ? $(bodyEl).toggleClass('sidebar-mini'): $(bodyEl).toggleClass('sidebar-opened');
        });
    };

    var switcheryToggle = function() {
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        elems.forEach(function(html) {
            var switchery = new Switchery(html, { size: 'small' });
        });
    };

    var closePanel = function() {
        $('.actions > .fa-times').click(function() {
            $(this).parent().parent().parent().fadeOut();
        });

    }

    var menu = function() {
        var subMenu = $('.sidebar .nav');
        $(subMenu).navgoco({
            caretHtml: false,
            accordion: true,
            slide: {
                  duration: 400,
                  easing: 'swing'
              }
          });

    };
    //End functions

    //Dashboard functions
    var timer = function() {
        $('.timer').countTo();
    };
    

    //Sliders
    var sliders = function() {
        $('.slider-span').slider()
    };


    //return functions
    return {
        init: init,
        timer: timer,
        sliders: sliders,
        morrisPie: morrisPie
    };
}();

//Load global functions
$(document).ready(function() {
    app.init();

});
