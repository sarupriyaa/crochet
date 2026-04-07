// mobile menu
const hamburger = document.getElementById("hamburger");
const navLinks = document.getElementById("nav-links");

hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("show");
});


// dropdown menus (supports multiple)
const dropdownBtns = document.querySelectorAll(".dropdown-btn");

dropdownBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
        e.stopPropagation();

        // close other dropdowns
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            if (menu !== btn.nextElementSibling) {
                menu.classList.remove("show");
            }
        });

        // toggle current dropdown
        btn.nextElementSibling.classList.toggle("show");
    });
});


// close dropdown when clicking outside
document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach(menu => {
        menu.classList.remove("show");
    });
});


// navbar scroll shadow
const navbar = document.getElementById("navbar");

window.addEventListener("scroll", () => {
    if (window.scrollY > 20) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});