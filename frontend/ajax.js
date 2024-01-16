// import { sendAlert } from "./functions";

/**
 * Updates the theme by sending a POST request to the server, toggling the current theme and updating the theme toggle button
 * @function updateTheme
 *
 * @param {HTMLElement} el - Element that triggered the event
 */
function updateTheme(el) { // BUG: Not all theme switch buttons are updated
    const currentTheme = el.getAttribute('data-current_theme');
    const icon_span = el.querySelector('.btn_icon');
    const text_span = el.querySelector('.btn_text');
    const cssHref = document.getElementById('theme-mode');

    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('updateTheme', 'true');
    formData.append('currentTheme', currentTheme);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                console.log('[CMS] Motyw zaktualizowany');
                el.setAttribute('data-current_theme', currentTheme === 'light' ? 'dark' : 'light');
                icon_span.innerHTML = currentTheme === 'light' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
                if (text_span != null || text_span != undefined) {
                    text_span.innerHTML = currentTheme === 'light' ? 'Jasny' : 'Ciemny';
                }
                cssHref.setAttribute('href', currentTheme === 'light' ? 'css/colors_light_mode.css' : 'css/colors_dark_mode.css');
                sendAlert('success', 'Motyw zaktualizowany!');
            } else {
                console.error('[CMS] Błąd aktualizacji motywu:', xhr.statusText);
                sendAlert('error', 'Błąd aktualizacji motywu. Spróbuj ponownie później.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/process.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('updateTheme=true&currentTheme=' + currentTheme);
}

function likePost(post_id, user_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('likePost', 'true');
    formData.append('post_id', post_id);
    formData.append('user_id', user_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let post = document.querySelector('[data-id="' + post_id + '"]');
                // find postFooterLeft in post
                let footer = post.querySelector('.postFooterLeft');
                likes = footer.querySelector('.postFooterLeftText');

                footer.classList.toggle('active');
                if (footer.classList.contains('active')) {
                    likes.innerHTML = Number(likes.innerHTML) + 1;
                } else {
                    likes.innerHTML = Number(likes.innerHTML) - 1;
                }

                console.log('[CMS] Zmieniono polubienie posta!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas zmiany polubienia posta.');
                console.log('[CMS] Wystąpił błąd podczas zmiany polubienia posta.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/post.php', true);
    // xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(formData);
}

function publishPost(post_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('publishPost', 'true');
    formData.append('post_id', post_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let post = document.querySelector('[data-id="' + post_id + '"]');
                post.classList.remove('unpublished');

                let publishButton = post.querySelector('[data-action="publish"]');
                publishButton.style = 'display: none;';

                let hideButton = post.querySelector('[data-action="hide"]');
                hideButton.style = '';

                sendAlert('success', 'Opublikowano post!');
                console.log('[CMS Admin] Post został opublikowany!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas publikowania postu.');
                console.log('[CMS Admin] Wystąpił błąd podczas publikowania postu.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/post.php', true);
    xhr.send(formData);
}

function hidePost(post_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('hidePost', 'true');
    formData.append('post_id', post_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let post = document.querySelector('[data-id="' + post_id + '"]');
                post.classList.add('unpublished');

                let publishButton = post.querySelector('[data-action="publish"]');
                publishButton.style = '';

                let hideButton = post.querySelector('[data-action="hide"]');
                hideButton.style = 'display: none;';

                sendAlert('warning', 'Ukryto post!');
                console.log('[CMS Admin] Post został ukryty!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas ukrywania postu.');
                console.log('[CMS Admin] Wystąpił błąd podczas ukrywania postu.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/post.php', true);
    xhr.send(formData);
}

function removePost(post_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('removePost', 'true');
    formData.append('post_id', post_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let post = document.querySelector('[data-id="' + post_id + '"]');
                post.remove();

                sendAlert('warning', 'Usunięto post!');
                console.log('[CMS Admin] Post został usunięty!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas usuwania postu.');
                console.log('[CMS Admin] Wystąpił błąd podczas usuwania postu.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/post.php', true);
    xhr.send(formData);
}

function addPost() {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData(document.getElementById('postForm'));
    
    

    formData.append('ajax', 'true');
    formData.append('addPost', 'true');

    let fileInput = document.getElementById('postImageFile');
    let files = fileInput.files;

    if (files.length != 0) {
        let file = files[0];
        let fileSize = file.size;

        let maxSizeInBytes = 5242880; // 5MB 
        if (fileSize > maxSizeInBytes) {
            sendAlert('error', 'Rozmiar pliku jest zbyt duży. Maksymalny rozmiar pliku to 5MB.');

            fileInput.value = '';
            return false;
        }
    }

    formData.append('postImageFile', fileInput.files[0]);

    let postTitle = document.getElementById('postTitle');
    let postContent = document.getElementById('postContent');

    formData.append('postTitle', postTitle.value);
    formData.append('postContent', postContent.value);

    if (postTitle.value == '' || postTitle.value == undefined || postTitle.value == 'undefined' || postTitle.value == false || postTitle.value == 'false') {
        sendAlert('error', 'Tytuł postu nie może być pusty.');
        return;
    }

    if (postContent.value == '' || postContent.value == undefined || postContent.value == 'undefined' || postContent.value == false || postContent.value == 'false') {
        sendAlert('error', 'Treść postu nie może być pusta.');
        return;
    }

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let newPostID = document.getElementById('newPostID');

                // if (newPostID == null || newPostID.value == '' || newPostID.value == undefined || newPostID.value == 'undefined' || newPostID.value == false || newPostID.value == 'false') {
                //     sendAlert('error', 'Wystąpił błąd podczas dodawania postu.');
                //     console.error('[CMS] Wystąpił błąd podczas dodawania postu.');
                //     console.error('[CMS] newPostID.value: ' + newPostID.value); // DEBUG
                //     return; // BUG newPostID doesnt exist even though it is echoed, cant check if visible
                // }
                // goToPost(newPostID.value);

                sendAlert('success', 'Dodano post! Przekierowuję...');
                console.log('[CMS] Post został dodany!');

                setTimeout(function() {
                    window.location.href = site_url;
                }, 2000);
            } else {
                sendAlert('error', 'Wystąpił błąd podczas dodawania postu.');
                console.error('[CMS] Wystąpił błąd podczas dodawania postu. [!= 200]');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/createPost.php', true);
    xhr.send(formData);
}

function addComment(post_id, user_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();

    formData.append('ajax', 'true');
    formData.append('addComment', 'true');
    formData.append('post_id', post_id);
    formData.append('user_id', user_id);

    let commentContent = document.getElementById('commentContentInput');
    if (commentContent.value == '' || commentContent.value == undefined || commentContent.value == 'undefined' || commentContent.value == false || commentContent.value == 'false') {
        sendAlert('error', 'Treść komentarza nie może być pusta.');
        return;
    }
    if (commentContent.value.length > 500) {
        sendAlert('error', 'Treść komentarza nie może być dłuższa niż 500 znaków.');
        return;
    }
    formData.append('commentContent', commentContent.value);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // TODO Add comment, clear HTML if no comments existed before
                // let comments = document.getElementById('commentsList');
                // let newComment = document.createElement('div');
                // newComment.classList.add('comment');
                // newComment.innerHTML = xhr.responseText;
                // comments.appendChild(newComment);

                // commentContent.value = '';

                sendAlert('success', 'Dodano komentarz!');
                console.log('[CMS] Komentarz został dodany!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas dodawania komentarza.');
                console.log('[CMS] Wystąpił błąd podczas dodawania komentarza.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/comments.php', true);
    xhr.send(formData);
}

function removeComment(comment_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('removeComment', 'true');
    formData.append('comment_id', comment_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let comment = document.querySelector('[data-comment_id="' + comment_id + '"]');
                comment.remove();

                sendAlert('warning', 'Usunięto komentarz!');
                console.log('[CMS Admin] Komentarz został usunięty!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas usuwania komentarza.');
                console.log('[CMS Admin] Wystąpił błąd podczas usuwania komentarza.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/comments.php', true);
    xhr.send(formData);
}

function publishComment(comment_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('publishComment', 'true');
    formData.append('comment_id', comment_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let comment = document.querySelector('[data-comment_id="' + comment_id + '"]');
                let publishButton = comment.querySelector('[data-comment_action="publish"]');
                publishButton.style = 'display: none;';

                let hideButton = comment.querySelector('[data-comment_action="hide"]');
                hideButton.style = '';

                comment.classList.remove('unpublished');

                sendAlert('success', 'Opublikowano komentarz!');
                console.log('[CMS Admin] Komentarz został opublikowany!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas publikowania komentarza.');
                console.log('[CMS Admin] Wystąpił błąd podczas publikowania komentarza.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/comments.php', true);
    xhr.send(formData);
}

function hideComment(comment_id) {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('hideComment', 'true');
    formData.append('comment_id', comment_id);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let comment = document.querySelector('[data-comment_id="' + comment_id + '"]');
                let publishButton = comment.querySelector('[data-comment_action="publish"]');
                publishButton.style = '';

                let hideButton = comment.querySelector('[data-comment_action="hide"]');
                hideButton.style = 'display: none;';

                comment.classList.add('unpublished');

                sendAlert('warning', 'Ukryto komentarz!');
                console.log('[CMS Admin] Komentarz został ukryty!');
            } else {
                sendAlert('error', 'Wystąpił błąd podczas ukrywania komentarza.');
                console.log('[CMS Admin] Wystąpił błąd podczas ukrywania komentarza.');
            }
        }
    };

    xhr.open('POST', site_url + '/modules/comments.php', true);
    xhr.send(formData);
}


function contactForm() {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData(document.getElementById('contactForm'));
    formData.append('ajax', 'true');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // get every input and textarea in the form
                let inputs = document.querySelectorAll('#contactForm input, #contactForm textarea');

                // reset the values
                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].value = '';
                }

                sendAlert('success', 'Wysłano wiadomość!');
                console.log('Wysłano wiadomość!');
            } else {
                document.getElementById('contactForm').innerHTML = 'Wystąpił błąd podczas wysyłania wiadomości.';
                console.log('Wystąpił błąd.');
            }
        }
    };

    xhr.open('POST', site_url + '/contact.php', true);
    xhr.send(formData);
}

function logoutUser() {
    event.preventDefault();
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    formData.append('ajax', 'true');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                sendAlert('success', 'Wylogowanie za 3 sekundy...');

                setTimeout(function() {
                    window.location.href = site_url;
                }, 3000);
                console.log('Wylogowano!');
            } else {
                console.log('Wystąpił błąd.');
            }
        }
    };

    xhr.open('POST', site_url + '/backend/logout.php', true);
    xhr.send(formData);
}