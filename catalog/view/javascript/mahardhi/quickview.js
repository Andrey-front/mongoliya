//<![CDATA[

$(window).load(function () {
  quickView.initquickView();
});

var quickView = {

  'initquickView' : function () {
    $('body').append('<div class="quickview-container"></div>');
    $('.quickview-container').load('index.php?route=product/quickview/insertcontainer');
  },

  'addCloseButton' : function () {
    $('.quickview-wrapper').prepend("<a href='javascript:void(0);' class='quickview-btn' onclick='quickView.closeButton()'>&times;</a>");
  },

  'closeButton' : function () {
    $('.quickview-overlay').hide();
    $('.quickview-wrapper').hide().html('');
    $('.quickview-loader').hide();
  },

  ajaxView :function(product_id){
    
    let  url = 'index.php?route=product/quickview/index&product_id=' + product_id;
    

    $.ajax({
      url     : url,
      type    : 'get',
      beforeSend  : function() {
        $('.quickview-overlay').show();
        $('.quickview-loader').show();        
      },
      success   : function(json) {
        if(json['success'] == true) {
          $('.quickview-loader').hide();
          $('.quickview-wrapper').html(json['html']);
          quickView.addCloseButton();

          const additional = $('html').attr('dir'); 
          $('#quick-carousel').each(function () {
            const items = $(this).data('items') || 4;
            const sliderOptions = {
              loop: false,
              nav: true,
              navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
              dots: false,
              items: items,
              responsiveRefreshRate: 200,
              responsive: {
                0: { items:1 },
                320: { items: 3 },
                481: { items: 5 },
                768: { items: 3 },
                1200: { items: 2 },
                1441: { items: 3 },
                1700: { items: 4 }
              }
            };
            if (additional == 'rtl') sliderOptions['rtl'] = true;
            $(this).owlCarousel(sliderOptions);
          
          }); 
          
          $('.quickview-wrapper').show();

          $('#datetimepicker').datetimepicker({
            pickTime: false
          });                   
          $('#datetime').datetimepicker({
            pickDate: true,
            pickTime: true
          });
          
          $('#Time').datetimepicker({
            pickDate: false
          });

        }
      }
    });

  }
};
//]]>