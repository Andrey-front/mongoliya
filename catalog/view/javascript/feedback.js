jQuery(function ($) {
  "use strict";
  
  const result = $('.feedback-modal__response');
  const form = $('.modal-form');

  form.on('submit', function (e) {
    result.text('');
    $.ajax({
      url: 'index.php?route=extension/module/feedback/ajaxContact',
      type: 'post',
      dataType: 'json',
      data: $(this).serializeArray(),
      beforeSend: function() {
        $('.modal-form__submit').prop("disabled", true);
        $("<span class='wait'></span>").insertAfter(form);
      }
    }).done(function(res) {
      if (res.success === true) {
        $.magnificPopup.close();
        window.location.href = 'index.php?route=extension/module/feedback/success';
      } else {
        result.text('Произошла ошибка. Попробуйте еще раз');
      }
      $('.modal-form__submit').prop("disabled", false);
      $('.wait').remove();
    })
    .fail((err) => {
      console.log(err);
      $('.wait').remove();
      result.text('Произошла ошибка. Попробуйте еще раз');
      $('.modal-form__submit').prop("disabled", false);
    });
    $(form)[0].reset();
    e.preventDefault();

  })
});