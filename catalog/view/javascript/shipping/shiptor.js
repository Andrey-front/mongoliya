if($(window).width()<=1024){ var width  = ($(window).width() * 19 / 20); }
else{ var	width  = ($(window).width() * 3 / 4); }
if($(window).height()<1024){ var height = ($(window).height() * 14 / 20); }
else{ var height = 900; }
$(document).ready(function(){
	$('[name="shipping_method"]').removeAttr('checked');

	if (width < 1024) {
		$('#shiptor-modal .shiptor-modal-dialog').removeAttr('style');
	}
});

$(window).resize(function() {
	if($(window).width()<=1024){ var width  = ($(window).width() * 19 / 20); }
	else{ var	width  = ($(window).width() * 3 / 4); }
	if($(window).height()<1024){ var height = ($(window).height() * 14 / 20); }
	else{ var height = 900; }
	if (width < 1024) {
		$('#shiptor-modal .shiptor-modal-dialog').removeAttr('style');
	} else {
		$('#shiptor-modal .shiptor-modal-dialog').css('width', width + 'px');
	}
});

/* действие при выборе метода доставки */
var check_validation = '';
var address_courier = '';

$(document).on('click', '[name="shipping_method"]', function(){
	$(this).prop('checked', true);
	shipping_method = $("input[name='shipping_method']:checked").val();
	$.post("index.php?route=extension/shipping/shiptor/save", {
        shipping_method: shipping_method
    });
	
	if(check_validation !== ''){
		if($(this).hasClass('reload-courier')){
			$('[name="shipping_address[address_1]"], [name="address_1"]').val(address_courier);
			if($('input[name=address_same]').is(':checked')){
				$('[name="payment_address[address_1]"]').val(address_courier);
			}
		}
	}
	
	if(!$(this).hasClass('reload-courier')){
		check_validation = 'yes';
		address_courier = $('input[name=\'address_1\']').val();
	}else{
		check_validation = '';
	}
	
	if(typeof(reloadAll) == 'function'){
	reloadAll()
	}

	Shiptor.points();
});

var shiptorModal = '<div class="shiptor-modal shiptor-fade" id="shiptor-modal" tabindex="-1" role="dialog">'
	+ '<div class="shiptor-modal-dialog shiptor-modal-lg" style="width: '+ width + 'px">'
	+ '<div class="shiptor-modal-content">'
	+ '<div class="shiptor-modal-header">'
	+	'<button type="button" class="shiptor-close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>'
	+	'<h4 class="shiptor-modal-title"></h4>'
	+ '</div>'
	+ '<div class="shiptor-modal-body">'
	+ '<div class="shiptor-container-fluid">'
	+ '<div class="shiptor-row">'
	+ '<div class="shiptor-hidden-xs shiptor-hidden-sm shiptor-col-md-3" style="overflow-x: hidden; overflow-y: auto; height: '+height+'px;" id="shiptor-filter_content"></div>'
	+ '<div class="shiptor-col-sm-12 shiptor-col-md-9">'
	+ '<div id="shiptor_map" style="height: '+ height + 'px;"></div>'
	+ '</div></div></div></div></div></div></div>';

var shiptorMap;

var Shiptor = {
	'points': function() {
		$('#shiptor-modal').remove();

		$('body').append(shiptorModal);

		ymaps.ready(init);
	
		function init() {
			shiptorMap = new ymaps.Map('shiptor_map', { center: [55.76, 37.64], controls: ['zoomControl'], zoom: 8 });

			BalloonContentLayout = ymaps.templateLayoutFactory.createClass(
				'<div style="line-height: 170%; margin: 10px;">' 
				+ '<h4>{{properties.address}}</h4>'  
				+ '<button id="shiptor_button"> Выбрать </button>'
				+ '[if properties.cod]<i class="shiptor-money" title="Оплата наличными"></i>[endif] ' 
				+ '[if properties.card]<i class="shiptor-card" title="Оплата картой"></i>[endif]<br />'
				+ '[if properties.no_pay]<i class="shiptor-no_pay" title="Не принимает платежи">Не принимает платежи</i>[endif]<br />' 
				+ '[if properties.phones]{{properties.phones}}<br />[endif]' 
				+ '[if properties.work]{{properties.work}}<br />[endif]' 
				+ '[if properties.description]<span style="font-size: 11px;">{{properties.description}}</span><br />[endif]'	
				+ '</div>', {
				build: function() {
					BalloonContentLayout.superclass.build.call(this);
					$('#shiptor_button').bind('click', this.onCounterClick);
				},
				clear: function() {
					$('#shiptor_button').unbind('click', this.onCounterClick);
					BalloonContentLayout.superclass.clear.call(this);
					
					$('[name="shipping_address[address_1]"], [name="address_1"]').val(this._data.object.properties.address);
					if($('input[name=address_same]').is(':checked')){
						$('[name="payment_address[address_1]"]').val(this._data.object.properties.address);
					}
	
					$.post('index.php?route=extension/shipping/shiptor/address', { kladr_id: this._data.object.properties.kladr_id, courier_id: this._data.object.properties.courier_id, id: this._data.object.properties.id });
				},
				onCounterClick: function() {
					$('#shiptor-modal').modal('hide');
//					$('#shiptor-modal .close-modal').click();
				}
			});

			objectManager = new ymaps.ObjectManager({clusterize: true, clusterHasBalloon: true});
			objectManager.clusters.options.set('preset', 'islands#redClusterIcons');
			objectManager.objects.options.set({balloonContentLayout: BalloonContentLayout, preset: 'islands#blueIcon'});
			
			shiptorMap.geoObjects.add(objectManager);
			
			/* действие при закрытии всплывающего окна */
			$('#shiptor-modal').on('hidden.bs.modal', function(e) {
				shiptorMap.destroy();
				
				$(this).remove();
				
				if(typeof(reloadAll) == 'function'){
					setTimeout(function() {
						reloadAll()
					}, 500);
				}
			});
			
			$.post('index.php?route=extension/shipping/shiptor/points', { shipping_method: $('[name="shipping_method"]:checked').val() }).done(function(data) {			
				if (data) {
					objectManager.add(data);

					if (data.position) {
						$('#shiptor-modal').modal('show');
						$('#shiptor-modal .shiptor-modal-title').text($('[name="shipping_method"]:checked').closest('label').text());
						shiptorMap.setCenter([data.position.location[0], data.position.location[1]], data.features.length==1?13:10);
					}
					
					/* создаем макет левого блока со списком адресов ПВЗ */
					if(($(window).width())>=767){
						/* создаем макет левого блока со списком адресов ПВЗ */
						var template = ''

						if (data.features) {
							$.each(data.features, function(i, e) {
								template += '<div class="shiptor-filter shiptor-list-group-item" data-point-id="' + e.properties.id + '" data-location="' + e.geometry.coordinates + '">';
								template += '<div>' + e.properties.address + '</div>';
								template += 'Оплата: ';

								if (e.properties.cod) { 
									template += '<i class="shiptor-money" title="Оплата наличными"></i>'; 
								}
								if (e.properties.card) { 
									template += '<i class="shiptor-card" title="Оплата картой"></i>'; 
								}
								if(e.properties.no_pay){
									template += '<i class="" title="Не принимает платежи">Не принимает платежи</i>';
								}
								template += '</div>';
							});
						}
						$('#shiptor-filter_content').html(template);
					}
					else{
						$('#shiptor-filter_content').attr('style','height:0px !important;');
					}

					/* действие по клику на элемент списка ПВЗ */
					$(document).on('click', '.shiptor-filter', function(event) {
						$('.shiptor-filter').removeClass('shiptor-point-active');
						
						$(this).addClass('shiptor-point-active');
						
						var objectId = $(this).attr('data-point-id'), location = $(this).attr('data-location').split(',');

						objectManager.objects.balloon.open(objectId);
						
						if (objectManager.objects.balloon.isOpen(objectId)) {
							shiptorMap.setCenter(location, 13);
						}
					});
					
					/* действие по клику на метку */
					objectManager.objects.events.add('click', function (e) {
						var objectId = e.get('objectId'), button = $('[data-point-id="' + objectId + '"]');
						
						$('.shiptor-filter').removeClass('shiptor-point-active');
						
						button.addClass('shiptor-point-active');
						if(($(window).width())>=767){
							$('#shiptor-filter_content').scrollTo(button, 300);
						}
						
					});
				}
			});
		};
	},

	time: function(terms) {
		var select = $('#shiptor-time').find('select');
		
		$.post('index.php?route=extension/shipping/shiptor/save', { terms: terms }).done(function(j) {
			select.css('border-color', 'green');
			$.ajax({
				url: 'index.php?route=checkout/payment_method/save',
				type: 'post',
				data: $('#collapse-payment-method input[type=\'radio\']:checked, #collapse-payment-method input[type=\'checkbox\']:checked, #collapse-payment-method textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-payment-method').button('loading');
				},
				success: function(json) {
					$('.alert, .text-danger').remove();

					if (json['redirect']) {
						location = json['redirect'];
					} else if (json['error']) {
						$('#button-payment-method').button('reset');
						
						if (json['error']['warning']) {
							$('#collapse-payment-method .panel-body').prepend('<div class="alert alert-danger">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
						}
					} else {
						$.ajax({
							url: 'index.php?route=checkout/confirm',
							dataType: 'html',
							complete: function() {
								$('#button-payment-method').button('reset');
							},
							success: function(html) {
								$('#collapse-checkout-confirm .panel-body').html(html);

								$('#collapse-checkout-confirm').parent().find('.panel-heading .panel-title').html('<a href="#collapse-checkout-confirm" data-toggle="collapse" data-parent="#accordion" class="accordion-toggle">Шаг 6: Подтверждение заказа <i class="fa fa-caret-down"></i></a>');

							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});

			setTimeout(function(){
				select.removeAttr('style');
			}, 500);
		});
	}
}

$(document).on("keyup", '[name="city"], [name="address[city]"], [name="payment_address[city]"], [name="shipping_address[city]"], [name="register[city]"]', function() {
    var e = $(this)
      , t = 0
      , n = 0;
	  e.removeAttr("data-onchange");
    e.parent().parent().parent().find("select").each(function() {
        -1 != $(this).attr("name").indexOf("zone_id") && 0 == n && (n = $(this)),
        -1 != $(this).attr("name").indexOf("country_id") && 0 == t && (t = $(this).val())
    }),
    console.log((e.not("[kladred]"))?1:0);
    e.is("[loading]") || e.not('[kladred]').kladr({
        type: $.kladr.type.city,
        withParents: !0,
        country: t,
        limit: 10,
		verify:true,
		check: function( obj ) {
			if(obj==null){
				if(typeof(e.attr("oldValue"))!='undefined')e.val(e.attr("oldValue"))
				else e.val(e.attr("value"))
			}
		},
        sendBefore: function() {
            e.attr("loading", !0)
        },
        receive: function() {
            e.removeAttr("loading")
        },
        select: function(t) {
            0 != n && n.val(t.zone_id),
            $.post("index.php?route=extension/shipping/shiptor/save", {
                kladr_id: t.kladr_id
            }),
            e.change(),
			e.attr("oldValue",t.name);
			if (typeof(reloadAll) == 'function') {
               setTimeout(function() {
					reloadAll()
				}, 500);
            }
        },
        labelFormat: function(e, t) {
            var n = ""
              , a = e.name.toLowerCase();
            t = t.name.toLowerCase();
            var i = a.indexOf(t);
            return i = i > 0 ? i : 0,
            e.type_short && (n += e.type_short + ". "),
            t.length < e.name.length ? (n += e.name.substr(0, i),
            n += "<strong>" + e.name.substr(i, t.length) + "</strong>",
            n += e.name.substr(i + t.length, e.name.length - t.length - i)) : n += "<strong>" + e.name + "</strong>",
            e.administrative_area && (n += " <small>" + e.readable_parents + ".</small>"),
            n
        }
    }).attr("kladred", !0);
});
