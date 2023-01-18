const orderExpands = document.querySelectorAll('.order-expand');

orderExpands.forEach((orderExpand, index) => {
    orderExpand.addEventListener('click', () => {
        const expandedRow = document.querySelectorAll('.expanded-row')[index];
        expandedRow.classList.toggle('open');
        if (orderExpand.innerText === "⇩") {
            orderExpand.innerText = "⇧";
        } else {
            orderExpand.innerText = "⇩";
        }
    });
});