$(document).ready(function(){
	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
			'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		monthNamesShort: ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
			'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
		dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
		dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
		dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		weekHeader: 'Не',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: '',
		showOn: 'button',
		buttonImage: 'images/bCalendar.png',
		buttonImageOnly: true,
		dateFormat: 'd M yy',
		showAnim: 'blind'
	};
	$.datepicker.setDefaults($.datepicker.regional['ru']);

	$('button').live('click', function () {
		$(this).blur();
	});
	$('.button').each(function () {
		$(this).html('<div class="hover"></div><div class="text"><span>' + $(this).html() + '</span></div>');
	});


	$('#loader').remove();
	$('#main').css({display: 'block'});
});
