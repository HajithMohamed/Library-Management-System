// Wishlist functionality for guest users
function addToWishlist(isbn) {
    fetch('/index.php?route=guest-wishlist&action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ isbn: isbn })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Added to wishlist! Login to save permanently.');
            location.reload();
        } else {
            alert(data.message || 'Error adding to wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    });
}

function removeFromWishlist(isbn) {
    if (!confirm('Remove this book from your wishlist?')) {
        return;
    }
    
    fetch('/index.php?route=guest-wishlist&action=remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ isbn: isbn })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error removing from wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
