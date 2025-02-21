//
// Author: -
// Date: -
// LastAuthor: Sebastian Schmittner
// LastDate: 2015.01.13 11:01:52 (+01:00)
// Version: 0.1.0
//

$(function(){
	var $write = $('#write'),
		$proposalWrite = $('#proposalWrite'),
		shift = false,
		capslock = false,
		deleteTimeout = 200,
		deleteTimeoutId = 0;
	
	$('#keyboard li.delete').mousedown(function() {
		deleteTimeoutId = setInterval(handleDelete, deleteTimeout);
	}).bind('mouseup mouseleave', function () {
		clearInterval(deleteTimeoutId);
	});
	
	$('#keyboard li').click(function(){
		var $this = $(this),
			character = $this.html(); // If it's a lowercase letter, nothing happens to this variable
		
		if ($this.hasClass('unused')) {
			return false;
		}
		
		// Delete
		if ($this.hasClass('delete')) {
			var value = $write.val();
			
			$write.val(value.substr(0, value.length - 1));
			return false;
		}
		
		// Special characters
		if ($this.hasClass('symbol')) character = $('span:visible', $this).html();
		if ($this.hasClass('space')) character = ' ';
		if ($this.hasClass('return')) character = "\n";
		
		// Add the character
		$write.val($write.val() + character);
		
		onEmailChange();
	});
});

function handleDelete() {
	var $write = $('#write');
	var value = $write.val();
	$write.val(value.substr(0, value.length - 1));
	onEmailChange();
}
