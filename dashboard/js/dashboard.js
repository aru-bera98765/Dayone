$(document).ready(function () {

  // $('.collapse').on('click', function (e) {
  //   e.preventDefault();

  //   if ($(window).width() > 991) {
  //     $('.dashboard_wrapper').toggleClass('sidebar_collapsed');
  //   } else {
  //     console.log("Collapse disabled on mobile/tablet");
  //   }
  // });

  $('.collapse').on('click', function (e) {

    e.preventDefault();

    // Toggle the class on the main wrapper

    $('.dashboard_wrapper').toggleClass('sidebar_collapsed');
    $('.sidebar ').removeClass('hide');
  });

  $(".sidebar .close").click(function () {
    $(".sidebar").toggleClass("hide");

    $('.dashboard_wrapper').removeClass('sidebar_collapsed');
  });

  $('.copy_btn').on('click', function () {
    var $btn = $(this);
    var textToCopy = $btn.data('copy');

    navigator.clipboard.writeText(textToCopy).then(function () {
      // Change button state visually
      var originalText = $btn.text();
      $btn.addClass('copied');

      // Reset button after 1.5 seconds
      setTimeout(function () {
        $btn.removeClass('copied');
      }, 1500);
    });
  });






  $('.toggle_view').on('click', function () {
    // 1. Find the input and the icon inside this specific group
    var $btn = $(this);
    var $input = $btn.siblings('.pass_input');
    var $icon = $btn.find('i');

    // 2. Toggle the input type
    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');

      // 3. Change icon to "eye-slash"
      $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      $input.attr('type', 'password');

      // 4. Change icon back to "eye"
      $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });


  // function checkLayout() {
  //   if (window.matchMedia("(max-width: 991px)").matches) {
  //     $('.dashboard_wrapper').addClass('sidebar_collapsed');
  //   } else {
  //     $('.dashboard_wrapper').removeClass('sidebar_collapsed');
  //   }
  // }

  // // Run on page load
  // checkLayout();

});
