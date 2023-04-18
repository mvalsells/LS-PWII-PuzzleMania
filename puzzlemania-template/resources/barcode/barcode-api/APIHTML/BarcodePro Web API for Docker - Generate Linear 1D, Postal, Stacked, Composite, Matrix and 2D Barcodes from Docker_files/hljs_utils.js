$(function () {
    var pre = document.getElementsByTagName('pre');
    for (var i = 0; i < pre.length; i++) {
        if (pre[i].children && pre[i].children.length > 0 && pre[i].children[0].nodeName == 'CODE') {
            var button = document.createElement('button');
            button.className = 'btn btn-info btn-sm btn-clipboard-copy';
            button.innerHTML = '<span class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy To Clipboard';
            var s = pre[i].children[0].innerText;
            button.setAttribute("data-clipboard-text", s);
            pre[i].parentElement.insertBefore(button, pre[i]);
        }
    }

    var clipboard = new ClipboardJS('.btn-clipboard-copy');
    clipboard.on('success', function (e) {
        //console.info('Action:', e.action);
        //console.info('Text:', e.text);
        //console.info('Trigger:', e.trigger);
        e.trigger.innerHTML = '<span class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copied!';
        window.setTimeout(function () {
            e.trigger.innerHTML = '<span class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy To Clipboard';
        }, 2000);
        e.clearSelection();

    });
    clipboard.on('error', function (e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
})