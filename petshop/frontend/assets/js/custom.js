document.addEventListener("DOMContentLoaded", function () {
  const app = document.getElementById("app");
  const links = document.querySelectorAll(".spa-link");

  
  async function loadPage(page) {
    try {
      const res = await fetch(`tpl/${page}.html`);
      const html = await res.text();
      app.innerHTML = html;

    
      if (page === "products") setupAddToCartButtons();
      if (page === "orders") setupOrderForm();
      if (page === "home") setupHomeButton();
    } catch (err) {
      app.innerHTML = "<p class='text-danger text-center mt-5'>Page not found.</p>";
    }
  }

  
  links.forEach(link => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const target = link.getAttribute("href").replace("#", "");
      loadPage(target);
      links.forEach(l => l.classList.remove("active"));
      link.classList.add("active");
    });
  });

  
  function setupAddToCartButtons() {
    document.querySelectorAll(".add-to-cart").forEach(btn => {
      btn.addEventListener("click", () => {
        alert("Product added to cart! Redirecting to order form...");
        loadPage("orders");
      });
    });
  }

  
  function setupHomeButton() {
    const shopBtn = document.querySelector("#shop-now"); 
    if (shopBtn) {
      shopBtn.addEventListener("click", (e) => {
        e.preventDefault();
        loadPage("products");
        links.forEach(l => l.classList.remove("active"));
        const productsLink = document.querySelector('a[href="#products"]');
        if (productsLink) productsLink.classList.add("active");
      });
    }
  }

  
  function setupOrderForm() {
    const form = document.getElementById("orderForm");
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        alert("Thank you! Your order has been placed successfully!");
        loadPage("home");
      });
    }
  }

 
  loadPage("home");
});
