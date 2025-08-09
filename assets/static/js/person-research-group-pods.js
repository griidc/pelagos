var deleteButtons = document.querySelectorAll('.field-collection-delete-button');

// add event listener to each delete button
deleteButtons.forEach(function(deleteButton) {
    deleteButton.addEventListener('click', function(e) {
        var confirmation = confirm('Are you sure you want to delete this item?');
        if (!confirmation) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });
});
