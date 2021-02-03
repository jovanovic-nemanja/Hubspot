const targets = document.querySelectorAll("[data-lazy]");
const lazyLoad = (target) => {
  const io = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        let img = entry.target;
        let src = img.getAttribute("data-lazy");
        let srcTwo = img.getAttribute("data-lazy-2x");
        img.style.width = "auto";
		img.setAttribute("src", `${src}`);
        img.setAttribute("srcset", `${src} 1x, ${srcTwo} 2x`);
        observer.disconnect();
      }
    });
  });
  io.observe(target);
};
targets.forEach(lazyLoad);