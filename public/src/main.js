const drawer = document.getElementById("cartDrawer");
const backdrop = document.getElementById("cartBackdrop");
const mobileOverlay = document.getElementById("mobileOverlay");
const sidebar = document.getElementById("sidebar");
const placeOrderModal = document.getElementById("placeOrderModal");

// :: START PLACE ORDER MODAL CONTROLS
function openPlaceOrderModal() {
    placeOrderModal.classList.remove("hidden");
    placeOrderModal.classList.add("flex");
    placeOrderModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");
    // :: START OPTIONAL CUSTOMER NAME FOCUS
    document.getElementById("customerName")?.focus();
    // :: END OPTIONAL CUSTOMER NAME FOCUS
}

function closePlaceOrderModal() {
    placeOrderModal.classList.add("hidden");
    placeOrderModal.classList.remove("flex");
    placeOrderModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");
}

document.getElementById("placeOrderOpen")?.addEventListener("click", openPlaceOrderModal);
document.getElementById("placeOrderClose").addEventListener("click", closePlaceOrderModal);
placeOrderModal.addEventListener("click", (event) => {
    if (event.target === placeOrderModal) closePlaceOrderModal();
});
// :: END PLACE ORDER MODAL CONTROLS

// Cart drawer open and close.
function openCart() {
    drawer.classList.remove("translate-x-full");
    backdrop.classList.remove("hidden");
    drawer.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");
}

function closeCart() {
    drawer.classList.add("translate-x-full");
    backdrop.classList.add("hidden");
    drawer.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");
}

// Mobile sidebar open and close.
function openSidebar() {
    sidebar.classList.remove("-translate-x-full");
    mobileOverlay.classList.remove("hidden");
}

function closeSidebar() {
    sidebar.classList.add("-translate-x-full");
    mobileOverlay.classList.add("hidden");
}

document.getElementById("cartToggle").addEventListener("click", openCart);
document.getElementById("closeCart").addEventListener("click", closeCart);
backdrop.addEventListener("click", closeCart);

document.getElementById("menuToggle").addEventListener("click", openSidebar);
mobileOverlay.addEventListener("click", closeSidebar);

// :: START QUANTITY BUTTON CONTROLS
document.querySelectorAll("[data-quantity-action]").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.quantityTarget);
        const current = Number(input.value) || Number(input.min) || 1;
        const change = button.dataset.quantityAction === "increase" ? 1 : -1;
        const next = current + change;
        input.value = Math.min(Number(input.max), Math.max(Number(input.min), next));
    });
});
// :: END QUANTITY BUTTON CONTROLS

// :: START OPEN CART AFTER ADD
if (new URLSearchParams(window.location.search).get("added") === "1" || new URLSearchParams(window.location.search).get("cart") === "updated") {
    openCart();
}

// <?php if ($showOrderModal): ?>
//     openPlaceOrderModal();
// <?php endif; ?>
// :: END OPEN CART AFTER ADD

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        // :: START CLOSE ORDER MODAL WITH ESCAPE
        if (!placeOrderModal.classList.contains("hidden")) {
            closePlaceOrderModal();
        }
        // :: END CLOSE ORDER MODAL WITH ESCAPE
        closeCart();
        closeSidebar();
    }
});
