//var url = 'https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/examples/learning/helloworld.pdf';
//var pdfjsLib = window['pdfjs-dist/build/pdf'];
//pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

//pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo sc_url_library('prj', 'pdfjs', 'build/pdf.worker.js'); ?>';
var pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 0.6,
    zoomRange =0.25,
    canvas = document.getElementById('the-canvas'),
    ctx = canvas.getContext('2d');

/**
  * Get information from the document page, resize the canvas accordingly and render the page.
  * @param num Page number.
  */

function renderPage(num) {
    pageRendering = true;
    // Using promise to go to the page
    pdfDoc.getPage(num).then(function(page) {
        var viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Render the PDF page in context of the canvas
        var renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        var renderTask = page.render(renderContext);

        // Wait until the rendering is done
        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) {
                // New page representation is pending
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });

    // Update page counters
    document.getElementById('page_num').textContent = num;
}


/**
 * If another page is being processed, wait until it is
  * finished. Otherwise, execute the display immediately.
 */
function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

/**
 * Shows the previous page.
 */
function onPrevPage() {
    if (pageNum <= 1) {
        return;
    }
    pageNum--;
    queueRenderPage(pageNum);
}
document.getElementById('prev').addEventListener('click', onPrevPage);

/**
 * Displays the next page.
 */
function onNextPage() {
    if (pageNum >= pdfDoc.numPages) {
        return;
    }
    pageNum++;
    queueRenderPage(pageNum);
}
document.getElementById('next').addEventListener('click', onNextPage);


/*** Zoom in on the page. */

function onZoomIn() {
    if (scale >= pdfDoc.scale) {
        return;
    }
    scale += zoomRange;
    var num = pageNum;
    renderPage(num, scale)
}
document.getElementById('zoomin').addEventListener('click', onZoomIn);

/*** Zoom out. 	 */

function onZoomOut() {
    if (scale >= pdfDoc.scale) {
        return;
    }
    scale -= zoomRange;
    var num = pageNum;
    queueRenderPage(num, scale);
}
document.getElementById('zoomout').addEventListener('click', onZoomOut);

/*** Zoom adjustment page. 	 */

function onZoomFit() {
    if (scale >= pdfDoc.scale) {
        return;
    }
    scale = 0.9;
    var num = pageNum;
    queueRenderPage(num, scale);
}
document.getElementById('zoomfit').addEventListener('click', onZoomFit);


function go() {
    var myFliles = document.getElementById('idFile').files,
        myReader;
    if (!myFliles) {
        alert('Смените браузер на более новую версию, или на современный.');
        return false;
    }

    var file = myFliles[0];
    //проверка на тип загружаемого файла!
    if (file['type'] !== "application/pdf") {
        alert('Загрузка файлов с таким расширением запрещена!');
        return false;
    }

    url = URL.createObjectURL(file); //window.URL.revokeObjectURL(this.src);
    console.log(url);

    //renderPage(1, 0.5);
    pageNum = 1;
    //zoomRange = 0.5;

    /**
     * Download asynchronous PDF.
     */
    pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page_count').textContent = pdfDoc.numPages;
        // Initial representation / first page
        renderPage(pageNum);
    })
}