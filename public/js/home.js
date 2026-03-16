document.addEventListener("DOMContentLoaded", () => {
  const products = document.querySelectorAll(".product-card");
  const orderItemsContainer = document.getElementById("order-items");
  const emptyOrder = document.getElementById("empty-order");
  const totalAmount = document.getElementById("total-amount");
  const btnConfirm = document.getElementById("btn-confirm");
  const roomSelect = document.getElementById("room-select");
  const orderNotes = document.getElementById("order-notes-input");
  const toast = document.getElementById("toast");
  const toastMessage = document.getElementById("toast-message");

  if (
    !orderItemsContainer ||
    !emptyOrder ||
    !totalAmount ||
    !btnConfirm ||
    !roomSelect ||
    !orderNotes
  ) {
    return;
  }

  const basePath = window.location.pathname.replace(/\/index\.php$/, "");
  let cart = [];

  function showToast(message) {
    if (!toast || !toastMessage) {
      return;
    }
    toastMessage.textContent = message;
    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
    }, 2500);
  }

  function calculateTotal() {
    return cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
  }

  function updateTotal() {
    const total = calculateTotal();
    totalAmount.textContent = `${total.toFixed(2)} EGP`;
  }

  function setConfirmState() {
    btnConfirm.disabled = cart.length === 0;
  }

  function renderCart() {
    if (cart.length === 0) {
      orderItemsContainer.innerHTML = "";
      emptyOrder.style.display = "block";
      orderItemsContainer.appendChild(emptyOrder);
      updateTotal();
      setConfirmState();
      return;
    }

    emptyOrder.style.display = "none";
    orderItemsContainer.innerHTML = "";

    cart.forEach((item, index) => {
      const itemEl = document.createElement("div");
      itemEl.className = "order-item";
      itemEl.innerHTML = `
                <div class="order-item-name">${item.name}</div>
                <div class="order-item-price">${item.price.toFixed(2)} EGP</div>
                <div class="qty-controls">
                    <button type="button" class="qty-btn btn-minus" data-index="${index}">-</button>
                    <span class="qty-value">${item.quantity}</span>
                    <button type="button" class="qty-btn btn-plus" data-index="${index}">+</button>
                    <button type="button" class="btn-remove" data-index="${index}" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
      orderItemsContainer.appendChild(itemEl);
    });

    orderItemsContainer.querySelectorAll(".btn-plus").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const idx = Number(e.currentTarget.dataset.index);
        cart[idx].quantity += 1;
        renderCart();
      });
    });

    orderItemsContainer.querySelectorAll(".btn-minus").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const idx = Number(e.currentTarget.dataset.index);
        if (cart[idx].quantity > 1) {
          cart[idx].quantity -= 1;
        } else {
          cart.splice(idx, 1);
        }
        renderCart();
      });
    });

    orderItemsContainer.querySelectorAll(".btn-remove").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const idx = Number(e.currentTarget.dataset.index);
        cart.splice(idx, 1);
        renderCart();
      });
    });

    updateTotal();
    setConfirmState();
  }

  function addToCart(product) {
    const existing = cart.find((item) => item.id === product.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push(product);
    }
    renderCart();
    showToast(`${product.name} added to cart`);
  }

  products.forEach((card) => {
    const btn = card.querySelector(".btn-add-to-cart");
    if (!btn) {
      return;
    }

    btn.addEventListener("click", () => {
      const product = {
        id: Number(card.dataset.id),
        name: card.dataset.name,
        price: Number(card.dataset.price),
        quantity: 1,
      };
      if (!product.id || !product.name || Number.isNaN(product.price)) {
        showToast("Invalid product data");
        return;
      }
      addToCart(product);
    });
  });

  btnConfirm.addEventListener("click", async () => {
    if (cart.length === 0) {
      showToast("Please add products to your cart");
      return;
    }

    const roomId = Number(roomSelect.value);
    if (!roomId) {
      showToast("Please select a room");
      return;
    }

    btnConfirm.disabled = true;
    const oldBtnHtml = btnConfirm.innerHTML;
    btnConfirm.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
      const payload = {
        room_id: roomId,
        notes: orderNotes.value.trim(),
        items: cart.map((item) => ({
          id: item.id,
          quantity: item.quantity,
        })),
      };

      const response = await fetch(`${basePath}/index.php?page=checkout`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();
      if (response.ok && result.success) {
        showToast(result.message || "Order placed successfully");
        cart = [];
        roomSelect.value = "";
        orderNotes.value = "";
        renderCart();
      } else {
        showToast(result.message || "Failed to place order");
      }
    } catch (error) {
      showToast("Network error while placing order");
    } finally {
      btnConfirm.innerHTML = oldBtnHtml;
      setConfirmState();
    }
  });

  renderCart();
});
