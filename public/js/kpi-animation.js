document.addEventListener("DOMContentLoaded", function () {

    const counters = document.querySelectorAll(".kpi-counter");

    counters.forEach(function (counter) {

        const target = parseFloat(
            counter.getAttribute("data-target") ||
            counter.textContent.replace(/,/g, "") ||
            "0"
        );

        if (isNaN(target)) {
            counter.classList.add("kpi-visible");
            return;
        }

        const duration = 900;
        const startTime = performance.now();

        function animateCounter(currentTime) {

            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Smooth ease-out
            const eased =
                1 - Math.pow(1 - progress, 3);

            const currentValue =
                Math.floor(target * eased);

            counter.textContent =
                currentValue.toLocaleString();

            counter.classList.add("kpi-visible");

            if (progress < 1) {
                requestAnimationFrame(animateCounter);
            } else {
                counter.textContent =
                    target.toLocaleString();
            }
        }

        requestAnimationFrame(animateCounter);
    });

    /* Animate progress rings */
    const rings =
        document.querySelectorAll(".kpi-progress-ring");

    rings.forEach(function (ring) {

        let percentage =
            parseFloat(
                ring.getAttribute("data-progress") || "0"
            );

        percentage =
            Math.max(0, Math.min(100, percentage));

        ring.style.setProperty(
            "--progress",
            "0deg"
        );

        setTimeout(function () {

            ring.style.setProperty(
                "--progress",
                (percentage * 3.6) + "deg"
            );

            const label =
                ring.querySelector("span");

            if (label) {
                label.textContent =
                    Math.round(percentage) + "%";
            }

        }, 150);
    });

});
