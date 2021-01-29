jQuery(function ($) {
  "use strict";

  $('#reviews').magnificPopup({
    type: 'inline',
    midClick: true
  });

  const imageName = $('.form-review__file');

  $('#photo').on('change', function (e) {
    const files = this.files;
    imageName.empty();
    for (let index = 0; index < files.length; index++) {
      const element = files[index];
      const name = element.name;
      imageName.append(`<span>${name}</span>`)
    }
    
  })

  const result = $('.modal-response');
  const form = $('#form-review');
  const submit = $('#modal-submit');
  const alert = `<div class="alert alert-success alert-dismissible fade in"
    data-dismiss="alert" aria-label="Close"
  role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close">
  <span aria-hidden="true">×</span></button>Ваш отзыв успешно сохранен и находиться на модерации
  </div>`;
  form.on('submit', function (e) {
    
    result.text('');
    var formData = new FormData(this);

    $.ajax({
      url: 'index.php?route=extension/module/reviews/addReview',
      type: 'post',
      data:  formData,
      contentType: false,
      processData: false,
      beforeSend:  () => {
        submit.prop("disabled", true);
      }
    }).done( (res) => {
      if (res.success === true) {
        $.magnificPopup.close();
        $('.reviews-page-top').append(alert);
        $(form)[0].reset();
        $('.form-review__file').empty();
      } else if(res.error){
          result.text(res.error);
      }else{
        result.text('Произошла ошибка. Попробуйте еще раз');
      }
      submit.prop("disabled", false);
    })
      .fail((err) => {
        console.log(err);
        result.text('Произошла ошибка. Попробуйте еще раз');
        submit.prop("disabled", false);

      });
    
    e.preventDefault();

  })
});
