document.addEventListener('DOMContentLoaded',()=>{
    const products=document.querySelectorAll('.product-cart');
    const orderItemsContainer=document.getElementById('order-items');
    const emptyOrder=document.getElementById('empty-order');
    const totoalAmount=document.getElementById('total-amount');
    const btnConfirm=document.getElementById('btn-confirm');
    const roomSelect=document.getElementById('room-select');
    const orderNotes=document.getElementById('order-notes-input');
    const totalMessage=document.getElementById('toast');
    const toastMessage=document.getElementById("toast-message");

    let cart=[];
    function showToast(message){
        toastMessage.textContent=message;
        toastMessage.className="show";
        setTimeout(()=>{
            toastMessage.className=toastMessage.className.replace("show"," ");
        },3000)

    }
    function updateTotal(){
        const total=cart.reduce((acc,item)=>sum+(item.price*item.quantity),0);
        totoalAmount.textContent=`${total} EGP`;
    }
    function renderCart(){
        if(cart.length===0){
            orderItemsContainer.innerHTML="";
            orderItemsContainer.appendChild(emptyOrder);
            emptyOrder.style.display="block";
            updateTotal();
            return;
        }
        emptyOrder.style.display="none";
        orderItemsContainer.innerHTML="";
        cart.forEach((item, index) => {
            const itemEl = document.createElement('div');
            itemEl.className = 'order-item';
            itemEl.innerHTML = `
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <span class="item-price">${item.price} EGP</span>
                </div>
                <div class="item-quantity">
                    <button class="btn-qty btn-minus" data-index="${index}"><i class="fas fa-minus"></i></button>
                    <span>${item.quantity}</span>
                    <button class="btn-qty btn-plus" data-index="${index}"><i class="fas fa-plus"></i></button>
                    <button class="btn-remove" data-index="${index}"><i class="fas fa-trash"></i></button>
                </div>
            `;
            orderItemsContainer.appendChild(itemEl);
        });

        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = e.currentTarget.closest('button').dataset.index;
                cart[idx].quantity++;
                renderCart();
            });
        });

        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = e.currentTarget.closest('button').dataset.index;
                if (cart[idx].quantity > 1) {
                    cart[idx].quantity--;
                } else {
                    cart.splice(idx, 1);
                }
                renderCart();
            });
        });

        document.querySelectorAll('.btn-remove').forEach(btn=>{
            btn.addEventListener('click',(e)=>{
                const index=e.currentTarget.closest('button').dataset.index;
                cart.splice(index,1);
                renderCart();
            });
        });
        updateTotal();
    }
        products.forEach(card=>{
            const btn =card.querySelector('.btn-add-to-cart');
            btn.addEventListener('click',()=>{
                const product={
                    id:card.dataset.id,
                    name:card.dataset.name,
                    price:card.dataset.price,
                    quantity:1
                }
                const existingProduct=cart.find(item=>item.id===product.id);
                if(existingProduct){
                    existingProduct.quantity++;
                }else{
                    cart.push(product);
                }
                renderCart();
                showToast(`${product.name} added to cart`);
             
            });

        });
        btnConfirm.addEventListener('click'),()=>{
            if(cart.Length==0){
                showToast("Please add a product to your cart");
                return;
            }
            if (!roomSelect.value) {
            showToast("Please select a room");
            return;
            }
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
            
})