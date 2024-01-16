
/**
 * Sorts the posts based on the selected sort value and updates the URL with the sort parameter.
 * @param {Event} el - The object that triggered the event.
 */
function postSort(el) {
    const sortValue = el.getAttribute('data-sort');
    const currentUrl = window.location.href;
    const urlWithSortParam = new URL(currentUrl);
    urlWithSortParam.searchParams.set('sort', sortValue);
    window.location.href = urlWithSortParam.toString();
}

/**
 * Redirects the user to the post page with the specified post ID.
 * @param {number} post_id - The ID of the post to navigate to.
 */
function goToPost(post_id) {
    window.location.href = site_url + "?mode=post&post_id=" + post_id;
}

/**
 * Redirects the user to the "post.php" page.
 */
function createPost() {
    window.location.href = site_url + "?mode=createPost";
}

/**
 * Searches for posts based on the provided ID.
 * @param {string} id - The ID of the element to search.
 */
function searchPosts(id) {
    element = document.getElementById(id);

    if (element.value == "") {
        return;
    } else {
        window.location.href = "?search=" + sanitizeSQLInput(element.value);
    }
}

/**
 * Copies the link to a post to the clipboard and displays an alert message.
 * If the browser does not support automatic clipboard copying, it opens the post in a new tab.
 * @param {number} post_id - The ID of the post.
 */
function sharePost(post_id) {
    if (!navigator.clipboard) {
        sendAlert('error', 'Twoja przeglądarka nie obsługuje automatycznego kopiowania do schowka. Otwieram nową kartę...');
        
        // delay 2 seconds
        setTimeout(function() {
            window.open(site_url + "?mode=post&post_id=" + post_id, '_blank');
        }, 2000);
        return;
    }
    navigator.clipboard.writeText(site_url + "?mode=post&post_id=" + post_id);
    
    sendAlert('info', 'Skopiowano link do posta do schowka.');
}