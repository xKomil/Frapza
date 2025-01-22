///////////////////////////////////////////////////////////
// Set current year
const yearEl = document.querySelector(".year");
const currentYear = new Date().getFullYear();
yearEl.textContent = currentYear;

///////////////////////////////////////////////////////////
// Make mobile navigation work
const btnNavEl = document.querySelector(".btn-mobile-nav");
const headerEl = document.querySelector(".header");
btnNavEl.addEventListener("click", function () {
  headerEl.classList.toggle("nav-open");
});

///////////////////////////////////////////////////////////
// Smooth scrolling animation

const allLinks = document.querySelectorAll("a:link");

allLinks.forEach(function (link) {
  link.addEventListener("click", function (e) {
    const href = link.getAttribute("href");

    // Pomijamy linki do kategorii
    if (link.classList.contains("menu-link")) return;

    // Wyjątek dla linków prowadzących do innej strony
    if (href && !href.startsWith("#")) {
      return; // Pozwalamy nawigacji działać normalnie
    }

    e.preventDefault(); // Zapobiegamy domyślnemu zachowaniu dla reszty linków

    // Scroll back to top
    if (href === "#") {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    }

    // Scroll to other links
    if (href && href.startsWith("#")) {
      const sectionEl = document.querySelector(href);
      sectionEl.scrollIntoView({ behavior: "smooth" });
    }

    // Close mobile navigation
    if (link.classList.contains("main-nav-link"))
      headerEl.classList.toggle("nav-open");
  });
});

///////////////////////////////////////////////////////////
// Sticky navigation
const sectionHeroEl = document.querySelector(".section-hero");
const obss = new IntersectionObserver(
  function (entries) {
    const ent = entries[0];
    if (!ent.isIntersecting) {
      document.body.classList.add("sticky");
    } else {
      document.body.classList.remove("sticky");
    }
  },
  {
    root: null,
    threshold: 1.0,
    rootMargin: "700px",
  }
);
obss.observe(sectionHeroEl);

///////////////////////////////////////////////////////////
// Fixing flexbox gap property missing in some Safari versions
function checkFlexGap() {
  const flex = document.createElement("div");
  flex.style.display = "flex";
  flex.style.flexDirection = "column";
  flex.style.rowGap = "1px";
  flex.appendChild(document.createElement("div"));
  flex.appendChild(document.createElement("div"));
  document.body.appendChild(flex);
  const isSupported = flex.scrollHeight === 1;
  flex.parentNode.removeChild(flex);
  if (!isSupported) document.body.classList.add("no-flexbox-gap");
}
checkFlexGap();

///////////////////////////////////////////////////////////
// Menu underline animation
const underline = document.getElementById("underline");
const links = document.querySelectorAll(".menu-position-types a");

function moveUnderline(link) {
  links.forEach(item => item.classList.remove("active"));
  link.classList.add("active");
  underline.style.left = link.offsetLeft + "px";
  underline.style.width = link.offsetWidth + "px";
}

window.onload = () => {
  const activeLink = document.querySelector(".menu-position-types a.active");
  if (activeLink) {
    underline.style.left = activeLink.offsetLeft + "px";
    underline.style.width = activeLink.offsetWidth + "px";
  }
};

///////////////////////////////////////////////////////////
// Menu category switching
document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll(".menu-link");
  const menuItemsContainer = document.getElementById("menu-items");

  const loadCategory = (category) => {
    fetch(`/public_html/backend/fetch_dishes.php?category=${encodeURIComponent(category)}`)
      .then(response => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then(data => {
        menuItemsContainer.innerHTML = "";
        if (data.length === 0) {
          menuItemsContainer.innerHTML = '<p>Brak dostępnych potraw w tej kategorii.</p>';
        } else {
          data.forEach(dish => {
            const dishElement = document.createElement("div");
            dishElement.classList.add("menu-item");
            dishElement.innerHTML = `
              <div class="menu-main-box">
                <div class="menu-item-img-container">
                  <img src="${dish.Zdjecie}" alt="${dish.Nazwa}" class="menu-item-img">
                </div>
                <div class="menu-description-box">
                  <h4 class="menu-item-name">${dish.Nazwa}</h4>
                  <p class="menu-item-description">${dish.Opis}</p>
                  <p class="menu-item-price">${parseFloat(dish.Cena).toFixed(2)} zł</p>
                </div>
              </div>
            `;
            menuItemsContainer.appendChild(dishElement);
          });
        }
      })
      .catch(error => {
        console.error("Błąd podczas ładowania danych:", error);
        menuItemsContainer.innerHTML = '<p>Błąd podczas ładowania potraw. Spróbuj ponownie później.</p>';
      });
  };

  // Default category
  loadCategory("Ciasta");

  links.forEach(link => {
    link.addEventListener("click", event => {
      event.preventDefault();
      const category = link.getAttribute("data-category");

      links.forEach(l => l.classList.remove("active"));
      link.classList.add("active");

      loadCategory(category);
      moveUnderline(link);
    });
  });
});

///////////////////////////////////////////////////////////
// Navigation to Profile section
document.querySelector("#profile-link").addEventListener("click", function (e) {
  e.preventDefault();
  const profileSection = document.getElementById("profile");
  if (profileSection) {
    profileSection.scrollIntoView({ behavior: "smooth" });
  }
});

document.getElementById("menu").addEventListener("change", function() {
  const customMenuSection = document.getElementById("custom_menu");
  if (this.value === "custom") {
      customMenuSection.style.display = "flex";
  } else {
      customMenuSection.style.display = "none";
  }
});



