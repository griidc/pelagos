function showLoginOptions(udi) {
    var url_redir = "<a href=\"/cas?destination=" + escape("{{pageName}}/download_redirect/" + udi + "?final_destination=" + location.pathname) + "\"> GoMRI Login </a>";
    $('#redir_url').replaceWith(url_redir);
    $('#pre_login').show();
}
