function handleCategoryChange() {
    const select = document.getElementById('category_select');
    const newCatContainer = document.getElementById('new_category_container');
    if (select.value === '__new__') {
        newCatContainer.style.display = 'block';
        document.getElementById('category_new').required = true;
    } else {
        newCatContainer.style.display = 'none';
        document.getElementById('category_new').required = false;
    }
}
// Initial call to handle pre-selected value (for edit form)
handleCategoryChange();