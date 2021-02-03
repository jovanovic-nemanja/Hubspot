$(document).ready(function () {
	$('a[href*="#"]')
		.not('.nav-tabs a')
		.not('.nav-pills a')
		.not('.sr-tab-labels a')
		.on("click", function (event) {
		if (this.hash !== "" && $(this.hash).length) {
			event.preventDefault();
			var hash = this.hash;
			$("html, body").animate(
				{
					scrollTop: $(hash).offset().top
				},
				800,
				function () {
					window.location.hash = hash;
				}
			);
		}
	});
});