jQuery(function ($) {
  "use strict";
  $(".carousel-mongoliya .swiper-container").swiper({
  	mode: 'horizontal',
    slidesPerView: 1,
    pagination: '.slideshow-mongoliya',
    paginationClickable: true,
    nextButton: '.swiper-button-next',
    prevButton: '.swiper-button-prev',
    spaceBetween: 0,
    //autoplay: 4000,
    autoplayDisableOnInteraction: true,
    loop: true
  });
});
