jQuery(document).ready(function ($) {
    $('.wpmf-defile').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var href = $(this).attr('href');
        if (href.indexOf('docs.google.com') != -1) {
            window.open(href);
        } else {
            window.location.href = wpmf_single.vars.site_url + '?act=wpmf_download_file&id=' + id + '&wpmf_nonce=' + wpmf_single.vars.wpmf_nonce;
        }
    });
});