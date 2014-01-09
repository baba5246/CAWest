(function() {
	$.fn.dragScroll = function() {
	var target = this;
	$(this).mousedown(function (event) {
		$(this)
		.data('down', true)
		.data('x', event.clientX)
		.data('y', event.clientY)
		.data('scrollLeft', this.scrollLeft)
		.data('scrollTop', this.scrollTop);

			return false;
	}).css({
		'overflow': 'hidden', // �X�N���[���o�[��\��
		'cursor': 'move'
	});


	// �E�B���h�E����O��Ă��C�x���g���s
	$(document).mousemove(function (event) {
		if ($(target).data('down') == true) {
		// �X�N���[��
		target.scrollLeft($(target).data('scrollLeft') + $(target).data('x') - event.clientX);
		target.scrollTop($(target).data('scrollTop') + $(target).data('y') - event.clientY);
		return false; // ������I����}�~
		}
	}).mouseup(function (event) {
		$(target).data('down', false);
	});

		return this;
	}
})(jQuery);