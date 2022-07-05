function copyToClipboardCustomMsg(elem, msgElem, successMessage, errorMessage) {
    successMessage = successMessage || 'Copied to the clipboard.';
    errorMessage = errorMessage || 'Copy not supported.';

    if (msgElem =  typeof msgElem === "string" ? document.getElementById(msgElem) : msgElem) {
        // set the user feedback and hide after 2s
        msgElem.innerHTML = !copyToClipboard(elem) ? errorMessage : successMessage;
        setTimeout(function() {
            msgElem.innerHTML = "";
        }, 2000);
    }
}
function copyToClipboardMsg(elem, msgElem) {
    copyToClipboardCustomMsg(elem, msgElem, 'API key copied to the clipboard.', 'Copy not supported by this browser.');
}
function copyToClipboard(elem) {

    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {

        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }

    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);

    var succeed;
    try {
          succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }

    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    if (isInput) {

        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {

        target.textContent = "";
    }
    return succeed;
}

function copy_text_to_clipboard(text,message) {
    if (window.clipboardData && window.clipboardData.setData) {
        return window.clipboardData.setData("Text", text);

    }
    else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");
        }
        catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return false;
        }
        finally {
            document.body.removeChild(textarea);
            alert(message);
        }
    }
}
