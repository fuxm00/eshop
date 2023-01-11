const filterOpener = document.getElementById('form-filter-open');
const filters = document.querySelector('.products-filter');

filterOpener.addEventListener('click', () => {
   filters.classList.toggle('open');
   filterOpener.classList.toggle('open');
});