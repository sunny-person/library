let myState = {}

myState.Init = function (pdf, currentPage, zoom, userInfoController) {
    this.pdf = pdf;
    this.currentPage = currentPage;
    this.zoom = zoom;
    this.userInfoController = userInfoController;
}

const ENTER_KEY_CODE = 13;
const LEFT_KEY_CODE = 37;
const RIGHT_KEY_CODE = 39;

window.onload = function () {
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

    $(document).on('keyup', function (e) {
        if (e.keyCode === RIGHT_KEY_CODE) {
            if (myState.pdf == null || myState.currentPage >= myState.pdf._pdfInfo.numPages)
                return;

            myState.currentPage += 1;

            document.getElementById("current_page").value = myState.currentPage;
            e.preventDefault();
            render().then(async () => {
                await myState.userInfoController.saveUserInformation(myState.currentPage);
            }).catch(reason => {
                console.warn(reason.message);
            });

        }
        if (e.keyCode === LEFT_KEY_CODE) {
            if (myState.pdf == null || myState.currentPage == 1) return;

            myState.currentPage -= 1;
            document.getElementById("current_page").value = myState.currentPage;
            e.preventDefault();
            render().then(async () => {
                await myState.userInfoController.saveUserInformation(myState.currentPage);
            }).catch(reason => {
                console.warn(reason.message);
            });
        }
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
        if (myState.zoom >= 3) {
            return;
        }
        myState.zoom += 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => {
            console.warn(reason.message);
        });
    });

    document.getElementById('zoom_out').addEventListener('click', (e) => {
        if (myState.pdf == null) return;

        if (myState.zoom <= 0.25) {
            return;
        }
        myState.zoom -= 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => {
            console.warn(reason.message);
        });
    });

    go();
}

function render_save(page, button) {
    document.getElementById("current_page").value = myState.currentPage;
    button.disabled = true;
    render().then(async () => {
        await myState.userInfoController.saveUserInformation(myState.currentPage);
        button.disabled = false;
    }).catch(reason => {
        console.warn(reason.message);
    });
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
        myState.currentPage = await myState.userInfoController.getUserInformation();
        myState.pdf = pdf;
        //общее количество страниц в документе
        document.querySelector('#page_count').textContent = myState.pdf.numPages;
        document.getElementById('current_page').value = myState.currentPage;
        await render();
    });
}