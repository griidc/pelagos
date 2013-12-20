function showLoginOptions(udi) {
    var url_redir = "/cas?destination=" + escape("{{pageName}}/download_redirect/" + udi + "?final_destination=" + location.pathname);
    $('a.redir_url').attr("href",url_redir);
    $('#pre_login').show();
}
