document.addEventListener("DOMContentLoaded", function(event) {
	console.log("Prototype Mode")
	function getBgImgs (doc) {
		const srcChecker = /url\(\s*?['"]?\s*?(\S+?)\s*?["']?\s*?\)/i
		return Array.from(
			Array.from(doc.querySelectorAll('*'))
			.reduce((collection, node) => {
				let prop = window.getComputedStyle(node, null).getPropertyValue('background-image')
				// match `url(...)`
				let match = srcChecker.exec(prop)
				if (match) {
					collection.add(match[1])
					node.style.backgroundImage = `url('https://placehold.it/${node.clientWidth}x${node.clientHeight}.jpg')`;
				}
				return collection
			}, new Set())
		)
	}

	getBgImgs(document);

	var imgs = document.getElementsByTagName("img");
	for (var i = 0; i < imgs.length; i++) {
		imgs[i].src = `https://placehold.it/${imgs[i].width}x${imgs[i].height}.jpg`
		imgs[i].srcset = `https://placehold.it/${imgs[i].width}x${imgs[i].height}.jpg 1x, https://placehold.it/${imgs[i].width*2}x${imgs[i].height*2}.jpg 2x`
	}
});