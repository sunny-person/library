let djvuState = {}

djvuState.Init = function(djvu, currentPage, zoom, userInfoController) {
    this.djvu = djvu;
    this.currentPage = currentPage;
    this.zoom = zoom;
    this.userInfoController = userInfoController;
}

const ENTER_KEY_CODE = 13;

window.onload = async function() {
    //переход на предыдущую страницу
    document.getElementById('go_previous').addEventListener('click', (e) => {
        if (djvuState.djvu == null || djvuState.currentPage === 1) return;

        djvuState.currentPage -= 1;
        let button = e.currentTarget;
        render_save(djvuState.currentPage, button);
    });

    //переход на следующую страницу
    document.getElementById('go_next').addEventListener('click', (e) => {
        if (djvuState.djvu == null || djvuState.currentPage >= djvuState.djvu.getPagesQuantity())
            return;

        djvuState.currentPage += 1;
        let button = e.currentTarget;
        render_save(djvuState.currentPage, button);

    });

    //для набора номера страницы вручную
    document.getElementById('current_page').addEventListener('keypress', (e) => {
        if (djvuState.djvu == null) return;

        var code = (e.keyCode ? e.keyCode : e.which);

        if (code === ENTER_KEY_CODE) {
            var desiredPage = document.getElementById('current_page').valueAsNumber;

            if (desiredPage >= 1 && desiredPage <= djvuState.djvu.getPagesQuantity()) {
                djvuState.currentPage = desiredPage;
                document.getElementById("current_page").value = desiredPage;

                let button = e.currentTarget;
                render_save(djvuState.currentPage, button);
            }
        }
    });

    //масштабирование
    document.getElementById('zoom_in').addEventListener('click', (e) => {
        if (djvuState.djvu == null) return;
        djvuState.zoom += 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => { console.warn(reason.message); });
    });

    document.getElementById('zoom_out').addEventListener('click', (e) => {
        if (djvuState.djvu == null) return;
        djvuState.zoom -= 0.25;
        let button = e.currentTarget;
        button.disabled = true;
        render().then(response => {
            button.disabled = false;
        }).catch(reason => { console.warn(reason.message); });
    });

    await go();
}

function render_save(page, button) {
    document.getElementById("current_page").value = djvuState.currentPage;
    button.disabled = true;
    render().then(async () => {
        await djvuState.userInfoController.saveUserInformation(djvuState.currentPage);
        button.disabled = false;
    }).catch(reason => { console.warn(reason.message); });
}

async function render() {
    const page = await djvuState.djvu.getPage(djvuState.currentPage);

    const canvas = document.getElementById('djvu_renderer');
    const ctx = canvas.getContext('2d');

    const imageData = page.getImageData();

    ctx.textAlign = 'center';
    canvas.width = imageData.width / djvuState.zoom;
    canvas.height = imageData.height / djvuState.zoom;

    canvas.style.width = '800px';
    canvas.style.height = '900px';

    ctx.putImageData(imageData, 0, 0);

    document.querySelector('#page_num').textContent = djvuState.currentPage;
}

async function go() {
    const url = document.getElementById("url").value;
    console.log(url);

    djvuState.zoom = 1;
    djvuState.currentPage = await djvuState.userInfoController.getUserInformation();
    document.getElementById('current_page').value = djvuState.currentPage;

    const bookUrl = document.getElementById('url').value;
    const bookContent = await fetch(bookUrl).then(r => r.arrayBuffer());

    djvuState.djvu = new DjVu.Document(bookContent);
    document.querySelector('#page_count').textContent = String(djvuState.djvu.getPagesQuantity());
    await render();
}