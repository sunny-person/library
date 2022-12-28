class UserInfoController {
    constructor(userId, bookId) {
        this.userId = userId;
        this.bookId = bookId;
    }

    async saveUserInformation(page) {
        const data = {
            user: this.userId,
            page: page,
            book: this.bookId
        };

        try {
            const response = await fetch(
                `/reader/${this.bookId}/save_user_information`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(data)
                }
            );

            const responseData = await response.json();
            if (responseData.error !== undefined) {
                console.error(responseData.error);
                return;
            }
            console.log(responseData.message);
        } catch (e) {
            console.error(e.message);
        }
    }

    async getUserInformation() {
        const data = {
            user: this.userId,
            book: this.bookId,
        }

        try {
            const response = await fetch(
                `/reader/${this.bookId}/get_user_information`,
                {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(data)
                }
            );

            const responseData = await response.json();
            return Number(responseData.page);
        } catch (e) {
            console.error(e.message);
        }
    }
}