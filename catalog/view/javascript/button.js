document.querySelectorAll('[button-letmeknow]').forEach((el) =>
    el.addEventListener('click', letMeKnowLoadModal)
)

function letMeKnowLoadModal(ev) {
    fetch('/index.php?route=extension/LetMeKnowWheItsAvailable/product/modal&product_id=' + ev.target.dataset.productId)
        .then(res => res.text())
        .then(res => {
            document.querySelectorAll('[id^="letmeknow"]').forEach(el => el.remove())
            const fragment = document.createRange().createContextualFragment(res);
            document.body.appendChild(fragment);
        })
}