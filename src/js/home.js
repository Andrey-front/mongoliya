import syotimer from './libs/syoTimer/jquery.syotimer';
import './libs/syoTimer/syotimer.lang';

var countdown = $('.countdown');
var countdown_time = countdown.text().split('/');
countdown.text('');
$('.countdown').syotimer({
  year: countdown_time[0],
  month: countdown_time[1],
  day: countdown_time[2],
  hour: 0,
  minute: 0,
  layout: 'dhm',
  lang: 'rus',
});

const reviewsCarousel = $('.reviews-carousel');
if (reviewsCarousel.length > 0) {
  $.ajax({
    url: 'index.php?route=extension/module/reviews/getLatestReviews',
    type: 'post',
    dataType: "json",
    data: { limit: 10 },
  }).done((res) => {
    const { reviews } = res;
    if (reviews) {

      let output = '';
      reviews.forEach(element => {
        let date = element.created_at.split(' ')[0].split('-');
        let photo = element.photo ? `<img src="/image/catalog/reviews/${element.photo}" alt="${ element.name }" class="reviews-carousel__photo"/>` : ''
        let mark = '';
        for(var i = 1; i <= 5; i++){
          if(element.mark >= i){
            mark += '<i class="fa fa-star reviews-carousel__star reviews-carousel__star_orange" aria-hidden="true"></i>';
          }else{
            mark += '<i class="fa fa-star reviews-carousel__star reviews" aria-hidden="true"></i>';
          }
        }

        output += `
        <div class="reviews-carousel__item swiper-slide">
          <div class="reviews-carousel__wrap">
            <div class="reviews-carousel-top">
              ${photo}
              <div class="reviews-carousel-block">
                <ul class="reviews-carousel-info list-inline">
                  <li class="reviews-carousel__name">${element.name} \/</li>
                  <li class="reviews-carousel__city">${element.city}</li>
                  <li class="reviews-carousel__date">${date[2]}.${date[1]}.${date[0]}</li>
                  <li class="reviews-carousel__mark">${mark}</li>
                </ul>
                <p class="reviews-carousel__text">${element.msg.slice(0, 200)}</p>
              </div>
            </div>
          </div>
        </div>
        `;
      });

      $('.reviews-carousel .swiper-wrapper').html(output);
      $('.reviews-carousel .swiper-container').swiper({
        mode: 'horizontal',
        speed: 2000,
        loop: true,
        autoplayDisableOnInteraction: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
      });
    
    }
    })
    .fail((err) => {
      console.log(err);
    });
 
}
