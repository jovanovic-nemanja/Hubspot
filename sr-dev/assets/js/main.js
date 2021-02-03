$(window).on('load', function () {

  var pathname = window.location.pathname;

  if (pathname == '/portals-start.html') {
    $('#welcomeModal').modal('show');
  }

  $('.welcome-modal').on('hide.bs.modal', function (e) {
    var $if = $(e.delegateTarget).find('iframe');
    var src = $if.attr("src");
    $if.attr("src", '/empty.html');
    $if.attr("src", src);
  });

  if ($(window).width() > 990) {
    $(".dropdown").mouseover(function () {
      $(this).addClass('show').attr('aria-expanded', "true");
      $(this).find('.dropdown-menu').addClass('show');
    }).mouseout(function () {
      $(this).removeClass('show').attr('aria-expanded', "false");
      $(this).find('.dropdown-menu').removeClass('show');
    });
  }

  $('.main-navigation .navbar-nav a').on('click', function () {
    $('.main-navigation .navbar-nav').find('li.active').removeClass('active');
    $(this).parent('li').addClass('active');
  });

  $(".sidebar.activated ul.nav").mouseover(function () {
    $(".sidebar-sub-menu").addClass("active");
  });

  $(".sidebar.activated ul.nav").click(function () {
    $(".sidebar-sub-menu").addClass("active");
  });

  $(".main-navigation").mouseover(function () {
    $(".sidebar-sub-menu").removeClass("active");
    $(".sidebar ul li").removeClass("toggled");
  });

  $(".right-panel").mouseover(function () {
    $(".sidebar-sub-menu").removeClass("active");
    $(".sidebar ul li").removeClass("toggled");
  });

  $("#sidebar-search").click(function () {
    showMenuItems(this);
    $(".sidebar-sub-menu").addClass("active");
    if ($('#sidebar-search').val().length == 0) {
      $('ul#search-results').empty();
    }
  });

  $("#sidebar-search").focus(function () {
    showMenuItems(this);
    $(".sidebar-sub-menu").addClass("active");
    $(".sidebar ul li").removeClass("toggled");

    if ($('#sidebar-search').val().length == 0) {
      $('ul#search-results').empty();
    }
  });

  $("#sidebar-search").focusout(function () {
    $(".sidebar-sub-menu").removeClass("active");
  });

  $(".sidebar ul li").mouseover(function () {
    $(this).addClass("toggled");
    $(this).siblings().removeClass("toggled");
  })

  $(".nav-item").mouseover(function (e) {
    showMenuItems(this);
  });

  function showMenuItems(menu) {
    var menuToShow = $(menu).attr('data-item')
    $('ul.show').removeClass("show");
    $('ul#' + menuToShow).addClass("show");
  }

  $("#sidebar-search").keyup(function () {
    $('ul#search-results').empty();
    var filter = $.trim(($(this).val()));
    if ($(this).val().length > 0) {
      $(".sidebar-sub-menu ul li").each(function () {
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
          console.log("No Sprocket Rocket items found");
        } else {
          $(this).clone().appendTo('ul#search-results').hide().fadeIn(250);
        }
      });
    }
  });

  /* Ajax action panel */

  $("#modules").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/modules.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#website").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/website-pages.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#landing").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/landing-pages.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#blog").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/blogs.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#account").click(function () {
    // $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    // $('#action-panel-target').load('./views/account-details.html', function(){
    //     $(this).hide().fadeIn(200);
    // });
  });

  $("#agencies").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/agencies.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#billing").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/billing-information.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#plans").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/plans.html', function () {
      $(this).hide().fadeIn(200);
    });
  });

  $("#users").click(function () {
    $('#action-panel-target').empty();

    if ($("body").hasClass("modal-open")) {
      console.log("action panel is open already");
    } else {
      $('#action-panel').modal('show');
    }
    $('#action-panel-target').load('./views/users.html', function () {
      $(this).hide().fadeIn(200);
    });
  });






});
