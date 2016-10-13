$(document).ready(function () {
	$.ajax({
		url: 'blocks/main.php',
		type: 'post',
		success: function (answer) {
			$('#main').append(answer);
		}
	});
});
