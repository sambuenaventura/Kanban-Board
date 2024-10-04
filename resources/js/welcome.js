function animateFeatures() {
    gsap.to(".feature-card", {
        duration: 1,
        x: 0,
        opacity: 1,
        stagger: 0.2,
        ease: "power2.out",
    });
}

document.addEventListener("DOMContentLoaded", (event) => {
    setTimeout(animateFeatures, 100);
});
