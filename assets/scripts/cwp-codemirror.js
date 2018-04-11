/**
 * CodeMirror JS
 */

(function ($, wp) {
    var e1 = wp.CodeMirror.fromTextArea(document.getElementById('cwp_template'), {
        lineNumbers: true,
        matchBrackets: true,
        mode: 'text/html'
    });
    var e2 = wp.CodeMirror.fromTextArea(document.getElementById('cwp_css'), {
        lineNumbers: true,
        matchBrackets: true,
        mode: 'text/css'
    });
})(window.jQuery, window.wp);