jQuery(function(){

	var updateCount = function()
	{
		jQuery.ajax({
			url: window.location.pathname + '/getPledgeCount',
			success: function(d) {
				count = (''+d).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
				jQuery('.pledge-count').html(count);
			},
			complete: function() {
				setTimeout(updateCount, 10000);
			}
		});
	};

	updateCount();
});
