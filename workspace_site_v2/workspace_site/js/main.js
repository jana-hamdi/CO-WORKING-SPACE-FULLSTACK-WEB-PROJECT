// ── WorkSpace · Global JS ────────────────────────────────────────────

// 1. Live clock (used on Home page)
//gets the current date/time, formats it into a readable string (e.g., “Monday, January 1, 2025, 10:30:45 AM”), and inserts it into the DOM element.
function updateClock() {
  // Get the element with ID 'live-clock'. If it doesn't exist, exit the function.
  const el = document.getElementById('live-clock');
  if (!el) return;
  const now = new Date();
  // Format the date and time using toLocaleDateString with options for weekday, year, month, day, hour, minute, and second. This creates a string like "Monday, January 1, 2025, 10:30:45 AM".
  const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
  // Update the text content of the clock element with the formatted date/time string.
  el.textContent = now.toLocaleDateString('en-US', opts);
}
//calls the function every second, so the clock ticks live
setInterval(updateClock, 1000);
//initial call to set the clock immediately on page load
updateClock();

// 2. Smooth active nav link highlight
// This code listens for the DOMContentLoaded event, which fires when the initial HTML document has been completely loaded and parsed. It then checks the current page's URL path and compares it to the href attributes of the navigation links. If a match is found, it adds the 'active' class to that link, allowing for CSS styling to indicate which page is currently active.
//Simple client‑side check avoids server‑side logic; works with any static or PHP page structure.
document.addEventListener('DOMContentLoaded', () => {
  // Get the current page from the URL. If the path is empty, default to 'index.php'.
  const page = window.location.pathname.split('/').pop() || 'index.php';
  // Loop through all navigation links and add the 'active' class to the one that matches the current page.
  document.querySelectorAll('.nav-menu a').forEach(a => {
    if (a.getAttribute('href') === page) a.classList.add('active');
  });
});

// 3. Scroll-reveal animation
// This code uses the Intersection Observer API to create a scroll-reveal effect for elements with the class 'reveal'. When these elements come into view (with a threshold of 12%), their opacity is set to 1 and they are translated back to their original position, creating a smooth fade-in and slide-up animation. The initial styles for opacity and transform are set on page load, and the observer watches for when the elements enter the viewport.
const observer = new IntersectionObserver((entries) => {
  // Loop through the observed entries. If an entry is intersecting (i.e., it has come into view), set its opacity to 1 and transform to translateY(0) to trigger the reveal animation.
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.opacity = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.12 });

//Each .reveal element is initially made invisible (opacity:0) and shifted down (translateY(28px)). A CSS transition is added for smooth motion.
document.querySelectorAll('.reveal').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(28px)';
  el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
  observer.observe(el);
});

// 4. Counter animation (stats section)
// This function animates number counters by incrementing their displayed value from 0 to a target number specified in a data attribute. It uses setInterval to update the counter every 25 milliseconds, creating a smooth counting effect. The step size is calculated based on the target value to ensure the animation completes in a reasonable time frame (around 1.5 seconds). Once the counter reaches the target, the interval is cleared to stop the animation.
function animateCounters() {
  // Loop through all elements with the class 'counter'. For each element, get the target number from the data attribute, initialize the current count to 0, and calculate the step size for the animation. Use setInterval to update the counter every 25 milliseconds until it reaches the target value.
  document.querySelectorAll('.counter').forEach(el => {
    const target = parseInt(el.dataset.target, 10);
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current + (el.dataset.suffix || '');
      if (current >= target) clearInterval(timer);
    }, 25);
  });
}

// The stats section is observed using the Intersection Observer API. When the section comes into view (with a threshold of 30%), the animateCounters function is called to start the counter animations, and the observer is disconnected to prevent it from triggering again.
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
  const statsObs = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) { animateCounters(); statsObs.disconnect(); }
  }, { threshold: 0.3 });
  statsObs.observe(statsSection);
}

// 5. Mobile nav toggle
// This code enables a mobile navigation menu toggle. When the element with the class 'burger' is clicked, it toggles the 'open' class on the navigation menu (with class 'nav-menu') and toggles the 'active' class on the burger icon itself. This allows for CSS to show or hide the menu and change the appearance of the burger icon when active.
const burger = document.querySelector('.burger');
const navMenu = document.querySelector('.nav-menu');
// Check if the burger element exists before adding the event listener to avoid errors on pages that don't have a mobile menu.
if (burger) {
  burger.addEventListener('click', () => {
    navMenu.classList.toggle('open');
    burger.classList.toggle('active');
  });
}

// 6. Form validation helper
// This function validates a form by checking all input fields that have the 'required' attribute. If any required field is empty, it sets the border color to red and marks the form as invalid. If the field is filled, it resets the border color. The function returns true if all required fields are valid, and false otherwise. This can be used to prevent form submission if validation fails.
function validateForm(formId) {
  // Get the form element by ID. If it doesn't exist, return true (no validation needed).
  const form = document.getElementById(formId);
  // If the form is not found, we assume there's nothing to validate, so we return true.
  if (!form) return true;
  let valid = true;
  // Loop through all required inputs in the form. If any are empty, mark the form as invalid and set the border color to red. If they are filled, reset the border color.
  form.querySelectorAll('[required]').forEach(input => {
    // Trim the input value to check for empty strings. If it's empty, mark as invalid.
    if (!input.value.trim()) {
      input.style.borderColor = '#dc3545';
      valid = false;
    } 
    // If the input is valid, reset the border color to default.
    else {
      input.style.borderColor = '';
    }
  });
  return valid;
}

// 7. Room filter (Rooms page)
// This function filters room cards based on the selected type. It iterates through all elements with the class 'room-card' and checks if their data-type attribute matches the selected type or if the selected type is 'all'. If it matches, the card is displayed; otherwise, it is hidden. Additionally, it updates the active state of the filter buttons by toggling the 'active' class based on whether their data-filter attribute matches the selected type.
function filterRooms(type) {
  // Show all cards if type is 'all', otherwise show only matching cards
  document.querySelectorAll('.room-card').forEach(card => {
    // Check if the card's data-type matches the selected type or if the selected type is 'all'. If it matches, set display to 'block'; otherwise, set it to 'none'.
    const match = type === 'all' || card.dataset.type === type;
    // Update the display style of the card based on whether it matches the filter criteria.
    card.style.display = match ? 'block' : 'none';
  });
  // Update active state of filter buttons
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === type);
  });
}

// 8. Cart counter (Store page)
// This code manages a simple shopping cart using localStorage. The cart is stored as a JSON string under the key 'ws_cart'. The addToCart function adds items to the cart, updates the localStorage, and shows a toast notification. The updateCartBadge function updates the cart badge with the total quantity of items in the cart. The showToast function creates a temporary notification that appears at the bottom right of the screen when an item is added to the cart.
let cart = JSON.parse(localStorage.getItem('ws_cart') || '[]');

// The addToCart function checks if the item already exists in the cart. If it does, it increments the quantity; if not, it adds a new item with a quantity of 1. After updating the cart, it saves the updated cart back to localStorage, updates the cart badge, and shows a toast notification confirming the addition of the item.
function addToCart(id, name, price) {
  const existing = cart.find(i => i.id === id);
  if (existing) { existing.qty++; }
  else { cart.push({ id, name, price, qty: 1 }); }
  localStorage.setItem('ws_cart', JSON.stringify(cart));
  updateCartBadge();
  showToast(`"${name}" added to cart!`);
}
// The updateCartBadge function calculates the total quantity of items in the cart and updates the text content of the cart badge. If the total is greater than 0, it displays the badge; otherwise, it hides it.
function updateCartBadge() {
  // Get the cart badge element. If it exists, calculate the total quantity of items in the cart and update the badge's text content. Show the badge if there are items in the cart, or hide it if the cart is empty.
  const badge = document.querySelector('.cart-badge');
  if (badge) {
    // Use the reduce method to sum up the quantity of all items in the cart. The initial value of the accumulator (s) is 0, and for each item (i), we add its quantity (i.qty) to the accumulator.
    const total = cart.reduce((s, i) => s + i.qty, 0);
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
  }
}
// The showToast function creates a temporary notification element if it doesn't already exist, sets its text content to the provided message, and displays it. The toast will automatically hide after 2.5 seconds. The toast is styled to appear fixed at the bottom right of the screen with a dark background and white text.
function showToast(msg) {
  // Check if a toast element already exists. If not, create one and append it to the body. Then set the text content of the toast to the provided message, display it, and set a timeout to hide it after 2.5 seconds.
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.style.cssText = `
      position:fixed; bottom:32px; right:32px; background:#2c3e50; color:white;
      padding:14px 24px; border-radius:10px; font-size:14px; font-weight:500;
      z-index:9999; animation:fadeUp 0.3s ease; box-shadow:0 8px 24px rgba(0,0,0,0.2);
    `;
    document.body.appendChild(toast);
  }
  // Set the text content of the toast to the provided message, display it, and set a timeout to hide it after 2.5 seconds.
  toast.textContent = msg;
  toast.style.display = 'block';
  setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

updateCartBadge();
// // ── WorkSpace · Global JS ────────────────────────────────────────────

// // 1. Live clock (used on Home page)
// function updateClock() {
//   const el = document.getElementById('live-clock');
//   if (!el) return;
//   const now = new Date();
//   const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
//   el.textContent = now.toLocaleDateString('en-US', opts);
// }
// setInterval(updateClock, 1000);
// updateClock();

// // 2. Smooth active nav link highlight
// document.addEventListener('DOMContentLoaded', () => {
//   const page = window.location.pathname.split('/').pop() || 'index.php';
//   document.querySelectorAll('.nav-menu a').forEach(a => {
//     if (a.getAttribute('href') === page) a.classList.add('active');
//   });
// });

// // 3. Scroll-reveal animation
// const observer = new IntersectionObserver((entries) => {
//   entries.forEach(e => {
//     if (e.isIntersecting) {
//       e.target.style.opacity = '1';
//       e.target.style.transform = 'translateY(0)';
//     }
//   });
// }, { threshold: 0.12 });

// document.querySelectorAll('.reveal').forEach(el => {
//   el.style.opacity = '0';
//   el.style.transform = 'translateY(28px)';
//   el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
//   observer.observe(el);
// });

// // 4. Counter animation (stats section)
// function animateCounters() {
//   document.querySelectorAll('.counter').forEach(el => {
//     const target = parseInt(el.dataset.target, 10);
//     let current = 0;
//     const step = Math.ceil(target / 60);
//     const timer = setInterval(() => {
//       current = Math.min(current + step, target);
//       el.textContent = current + (el.dataset.suffix || '');
//       if (current >= target) clearInterval(timer);
//     }, 25);
//   });
// }

// const statsSection = document.querySelector('.stats-section');
// if (statsSection) {
//   const statsObs = new IntersectionObserver(entries => {
//     if (entries[0].isIntersecting) { animateCounters(); statsObs.disconnect(); }
//   }, { threshold: 0.3 });
//   statsObs.observe(statsSection);
// }

// // 5. Mobile nav toggle
// const burger = document.querySelector('.burger');
// const navMenu = document.querySelector('.nav-menu');
// if (burger) {
//   burger.addEventListener('click', () => {
//     navMenu.classList.toggle('open');
//     burger.classList.toggle('active');
//   });
// }

// // 6. Form validation helper
// function validateForm(formId) {
//   const form = document.getElementById(formId);
//   if (!form) return true;
//   let valid = true;
//   form.querySelectorAll('[required]').forEach(input => {
//     if (!input.value.trim()) {
//       input.style.borderColor = '#dc3545';
//       valid = false;
//     } else {
//       input.style.borderColor = '';
//     }
//   });
//   return valid;
// }

// // 7. Room filter (Rooms page)
// function filterRooms(type) {
//   document.querySelectorAll('.room-card').forEach(card => {
//     const match = type === 'all' || card.dataset.type === type;
//     card.style.display = match ? 'block' : 'none';
//   });
//   document.querySelectorAll('.filter-btn').forEach(btn => {
//     btn.classList.toggle('active', btn.dataset.filter === type);
//   });
// }

// // 8. Cart counter (Store page)
// let cart = JSON.parse(localStorage.getItem('ws_cart') || '[]');

// function addToCart(id, name, price) {
//   const existing = cart.find(i => i.id === id);
//   if (existing) { existing.qty++; }
//   else { cart.push({ id, name, price, qty: 1 }); }
//   localStorage.setItem('ws_cart', JSON.stringify(cart));
//   updateCartBadge();
//   showToast(`"${name}" added to cart!`);
// }

// function updateCartBadge() {
//   const badge = document.querySelector('.cart-badge');
//   if (badge) {
//     const total = cart.reduce((s, i) => s + i.qty, 0);
//     badge.textContent = total;
//     badge.style.display = total > 0 ? 'flex' : 'none';
//   }
// }

// function showToast(msg) {
//   let toast = document.getElementById('toast');
//   if (!toast) {
//     toast = document.createElement('div');
//     toast.id = 'toast';
//     toast.style.cssText = `
//       position:fixed; bottom:32px; right:32px; background:#2c3e50; color:white;
//       padding:14px 24px; border-radius:10px; font-size:14px; font-weight:500;
//       z-index:9999; animation:fadeUp 0.3s ease; box-shadow:0 8px 24px rgba(0,0,0,0.2);
//     `;
//     document.body.appendChild(toast);
//   }
//   toast.textContent = msg;
//   toast.style.display = 'block';
//   setTimeout(() => { toast.style.display = 'none'; }, 2500);
// }

// updateCartBadge();
