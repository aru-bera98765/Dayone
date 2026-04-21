$(document).ready(function () {
  $(".stellarnav").stellarNav({
    breakpoint: 991,
    position: "left",
  });


  $('.testimonial .owl-carousel').owlCarousel({
    loop: true,
    margin: 30,
    autoHeight: true,
    nav: true,
    dots: false,
    navText: [
      "<img src='images/pegination arrow.png' alt='prev'>",
      "<img src='images/pegination arrow.png' alt='next'>"
    ],
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 3
      },
      1000: {
        items: 4
      }
    }
  });



  // 2. FAQ Toggle
  //-------------------------------------------------------
  $(".faq_box").each(function () {
    $(this).on("click", ".acc_trigger", function (e) {
      e.preventDefault();

      const $trigger = $(this);
      const $item = $trigger.closest(".faq_item");
      const $container = $item.find(".acc_container");
      const $box = $trigger.closest(".faq_box");

      if ($container.is(":visible")) {
        $container.slideUp(300);
        $trigger.removeClass("active closed");
        $item.removeClass("main_active");
      } else {
        $box.find(".acc_container").slideUp(300);
        $box.find(".acc_trigger").removeClass("active closed");
        $box.find(".faq_item").removeClass("main_active");

        $container.slideDown(300);
        $trigger.addClass("active closed");
        $item.addClass("main_active");
      }
    });
  });



});

document.addEventListener("DOMContentLoaded", function () {
  // 1. Find ALL sections with the class zipSec2
  const allSections = document.querySelectorAll('.zipSec2');

  // 2. Loop through each section independently
  allSections.forEach((section, index) => {

    // Find the columns ONLY inside this specific section
    const columns = section.querySelectorAll('.row > [class*="col-"]');

    // Find the span ONLY inside this specific section
    const spanIndicator = section.querySelector('.container > span');

    if (spanIndicator) {
      const isEven = columns.length % 2 === 0;

      // Apply the classes
      if (isEven) {
        spanIndicator.className = "even";
      } else {
        spanIndicator.className = "odd";
      }

      // Check your console: It will now list every section individually
      console.log(`Section ${index + 1}: Found ${columns.length} columns -> span is now .${isEven ? 'even' : 'odd'}`);
    }
  });
});