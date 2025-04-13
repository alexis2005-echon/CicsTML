document.addEventListener("DOMContentLoaded", function () {
    let logo = document.getElementById("logo");

    logo.addEventListener("mouseover", function () {
        this.src = "IMG/CicsTML_or.svg";
    });

    logo.addEventListener("mouseout", function () {
        this.src = "IMG/CicsTML_bl.svg";
    });
});

