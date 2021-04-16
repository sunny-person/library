const FavoriteBooksController = function(userId) {
    this.userId = userId;

    const favoriteFlags = document.querySelectorAll('.favorite-flag__value');
    if (favoriteFlags !== null) {
        for (const favoriteFlag of favoriteFlags) {
            if (favoriteFlag.dataset.value === 'N') {
                favoriteFlag.onclick = this.addToFavorites.bind(this);
            } else {
                favoriteFlag.onclick = this.removeFromFavorites.bind(this);
            }
        }
    }
}

FavoriteBooksController.prototype.addToFavorites = async function(e) {
    const favoriteFlag = e.currentTarget;
    const userId = this.userId;

    try {
        const body = {
            bookId: favoriteFlag.dataset.bookId,
            userId: userId
        };

        const result = await fetch(
            '/book/favorites/add',
            {
                method: 'POST',
                body: JSON.stringify(body)
            }
        );

        const icon = favoriteFlag.querySelector('.fa.fa-star-o');
        icon.classList.remove('fa-star-o');
        icon.classList.add('fa-star');
    } catch (e) {
        console.warn(e.message);
    }
}

FavoriteBooksController.prototype.removeFromFavorites = async function(e) {
    const favoriteFlag = e.currentTarget;
    const userId = this.userId;

    try {
        const body = {
            bookId: favoriteFlag.dataset.bookId,
            userId: userId
        };

        const result = await fetch(
            '/book/favorites/remove',
            {
                method: 'POST',
                body: JSON.stringify(body)
            }
        );

        const icon = favoriteFlag.querySelector('.fa.fa-star');
        icon.classList.remove('fa-star');
        icon.classList.add('fa-star-o');
    } catch (e) {
        console.warn(e.message);
    }
}