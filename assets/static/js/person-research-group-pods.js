var deleteButton = document.querySelector('.field-collection-delete-button');

// add event listener to the delete button
if (deleteButton) {
    deleteButton.addEventListener('click', function(e) {
        e.preventDefault();
        var confirmation = confirm('Are you sure you want to delete this item?');
        if (!confirmation) {
            e.stopImmediatePropagation();
        }
    });
}
