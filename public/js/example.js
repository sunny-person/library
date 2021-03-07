let myState = {}

myState.Init = function(pdf, currentPage, zoom, user, book) {
    this.pdf = pdf;
    this.currentPage = currentPage;
    this.zoom = zoom;
    this.user = user;
    this.book = book;
}

const ENTER_KEY_CODE = 13;

window.onload = function() {
    //переход на предыдущую страницу
    document.getElementById('go_previous').addEventListener('click', (e) => {
        if (myState.pdf == null || myState.currentPage == 1) return;

        myState.currentPage -= 1;
        let button = e.currentTarget;
        render_save(myState.currentPage, button);
    });

    //переход на следующую страницу
    document.getElementById('go_next').addEventListener('click', (e) => {
        if (myState.pdf == null || myState.currentPage >= myState.pdf._pdfInfo.numPages)
            return;

        myState.currentPage += 1;
        let button = e.currentTarget;
        render_save(myState.currentPage, button);

    });

    //для набора номера страницы вручную
    document.getElementById('current_page').addEventListener('keypress', (e) => {
        if (myState.pdf == null) return;

        var code = (e.keyCode ? e.keyCode : e.which);

        if (code === ENTER_KEY_CODE) {
            var desiredPage = document.getElementById('current_page').valueAsNumber;

            if (desiredPage >= 1 && desiredPage <= myState.pdf._pdfInfo.numPages) {
                myState.currentPage = desiredPage;
                document.getElementById("current_page").value = desiredPage;

                let button = e.currentTarget;
                render_save(myState.currentPage, button);
            }
        }
    });

    //масштабирование
    document.getElementById('zoom_in').addEventListener('click', (e) => {
        if (myState.pdf == null) return;
        myState.zoom += 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => { console.warn(reason.message); });
    });

    document.getElementById('zoom_out').addEventListener('click', (e) => {
        if (myState.pdf == null) return;
        myState.zoom -= 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => { console.warn(reason.message); });
    });

    go();
}

function render_save(page, button) {
    document.getElementById("current_page").value = myState.currentPage;
    button.disabled = true;
    render().then(response => {
        saveUserInfo(myState.user, myState.currentPage, myState.book);
        button.disabled = false;
    }).catch(reason => { console.warn(reason.message); });
}

async function render() {
    let page = await myState.pdf.getPage(myState.currentPage);

    let canvas = document.getElementById('pdf_renderer');
    let ctx = canvas.getContext('2d');
    let viewport = await page.getViewport(myState.zoom);

    ctx.textAlign = 'center';
    canvas.width = viewport.width;
    canvas.height = viewport.height;

    await page.render({
        canvasContext: ctx,
        viewport: viewport
    })

    // Вывод текущей страницы в <span>
    document.querySelector('#page_num').textContent = myState.currentPage;

}

function go() {
    let url = document.getElementById("url").value;
    console.log(url);

    myState.currentPage = 1;
    myState.zoom = 1;
    document.getElementById("current_page").value = 1;

    //передаем наш полученный url и работает с документом
    pdfjsLib.getDocument(url).then(async (pdf) => {
        myState.currentPage = await getUserInformation(myState.user, myState.book);
        myState.pdf = pdf;
        //общее количество страниц в документе
        document.querySelector('#page_count').textContent = myState.pdf.numPages;
        document.getElementById('current_page').value = myState.currentPage;
        await render();
    });
}

function saveUserInfo(user, page, book) {
    let data = {
        user: user,
        page: page,
        book: book
    };

    fetch(
        `/reader/${book}/save_user_information`,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(data)
        }
    ).then(async (response) => {
        let responseData = await response.json();
        if (responseData.error !== undefined) {
            console.warn(responseData.error);
            return;
        }
        console.log(responseData.message);
    }).catch((error) => { console.warn(error.message); });
}

async function getUserInformation(user, book) {
    let data = {
        user: user,
        book: book
    };

    try {
        let response = await fetch(
            `/reader/${book}/get_user_information`,
            {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify(data)
            }
        );

        let responseData = await response.json();
        return Number(responseData.page);
    } catch (e) {
        console.warn(e.message);
    }
    return 1;
}