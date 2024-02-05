let isEverythingLoaded = false;
/**
 * Checks if everything is loaded by the user.
 * @returns {void}
 */
function checkIfLoaded() {
    isEverythingLoaded = true;
    console.log('[CMS] Załadowano!');
}
window.onload = checkIfLoaded; // Call the function when the page is loaded

const site_url = 'https://wafelowski.pl/cms';

// Check if the user is on a mobile device
if (/Mobi|Android/i.test(navigator.userAgent)) {
    alert("Strona nie jest jeszcze przystosowana do urządzeń mobilnych. Prosimy o skorzystanie z komputera.");
}

let alertQueue = [];
let isAlertShowing = false;
/**
 * Creates an alert with the specified type and message, and adds it to the alert queue.
 * @param {string} type - The type of alert:
 *                      - success
 *                      - info
 *                      - warning
 *                      - error
 * @param {string} message - The message to display in the alert.
 */
function sendAlert(type, message) {
    let alertDiv = document.createElement('div');

    switch(type) {
        case 'success':
            icon = 'fa-solid fa-check';
            break;
        case 'info':
            icon = 'fa-solid fa-info';
            break;
        case 'warning':
            icon = 'fa-solid fa-exclamation';
            break;
        case 'error':
            icon = 'fa-solid fa-xmark';
            break;
        default:
            icon = 'fa-solid fa-info';
            type = 'info';
            break;
    }
    alertDiv.classList.add('alert', 'btn', type);


    alertDiv.innerHTML = `
        <span class='btn_icon'>
            <i class='${icon}'></i>  
        </span>
        <span class='btn_text'>${message}</span>
    `;

    alertDiv.onclick = () => removeAlert(alertDiv);

    alertQueue.push(alertDiv);
    showNextAlert();
}

function showNextAlert() {
    if (alertQueue.length > 0 && !isAlertShowing) {
        if (!isEverythingLoaded) {
            setTimeout(() => showNextAlert(), 1000);
            return;
        }
        isAlertShowing = true;
        let currentAlert = alertQueue.shift();
        alertContainer = document.getElementById('alertContainer');
        if (alertContainer == null) {
            // Add to body
            alertContainer = document.createElement('div');
            alertContainer.id = 'alertContainer';
            document.body.appendChild(alertContainer);

            console.error('Alert container not found, but was successfully added!');
            return;
        }
        alertContainer.appendChild(currentAlert);
        currentAlert.style.display = 'inline-block';

        setTimeout(() => {
            removeAlert(currentAlert);
        }, 10000); // 10 seconds
    }
}
  
function removeAlert(alertDiv) {
    alertDiv.style.opacity = '0';
    setTimeout(() => {
        document.getElementById('alertContainer').removeChild(alertDiv);
        isAlertShowing = false;
        showNextAlert();
    }, 600); // 0.5 second
}


/**
 * Flips the card with the given ID and toggles the text of the button between two options.
 * @param {string} id - The ID of the card element to be flipped.
 * @param {Array<string>} buttonTexts - An array containing two button text options. First one is the default.
 */
function flipCard(id = 'flipCard', buttonTexts = Array('Zarejestruj się', 'Zaloguj się')) {
    const flipCardEl = document.getElementById(id);
    const cardInner = flipCardEl.querySelector('.flip-card-inner');
    cardInner.classList.toggle('flipped');

    const flipCardBtn = flipCardEl.querySelector('#flipCardBtn');
    const buttonText = flipCardBtn.querySelector('.btn_text');
    if (buttonText.innerHTML === buttonTexts[0]) {
        buttonText.innerHTML = buttonTexts[1];
    } else {
        buttonText.innerHTML = buttonTexts[0];
    }
}

/**
 * Creates an alert with the specified type and message, and adds it to the alert queue.
 * @param {string} mainElement - ID of the element that calls function.
 * @param {string} className - Class name that's assigned to the elements.
 * @param {...any} elements - Elements that are affected by the function, specified by their ID.
 * 
 * @return {void}
 */
function toggleElementsClass(mainElement, className, ...elements) {
    if (mainElement.value != "" && mainElement.value != null) {
        for (let element of elements) {
            element = document.getElementById(element);
            element.classList.add(className);
        }
    } else {
        for (let element of elements) {
            element = document.getElementById(element);
            element.classList.remove(className);
        }
    }
}


/**
 * Toggles the visibility of a dropdown element.
 * @param {string} id - The ID of the dropdown element.
 */
function toggleDropdown(id) {
    document.getElementById(id).classList.toggle("show");
}

/**
 * Sanitizes SQL input by escaping special characters.
 * 
 * @param {string|number|null|undefined} input - The input to be sanitized.
 * @returns {string|null} - The sanitized input.
 */
function sanitizeSQLInput(input) {
    if (input === null || input === undefined) {
      return null;
    }
  
    if (typeof input !== 'string') {
      input = input.toString();
    }
  
    const sanitizedInput = input.replace(/[\0\n\r\b\t\\'"\x1a]/g, function (char) {
      switch (char) {
        case '\0':
          return '\\0';
        case '\n':
          return '\\n';
        case '\r':
          return '\\r';
        case '\b':
          return '\\b';
        case '\t':
          return '\\t';
        case '\x1a':
          return '\\Z';
        case "'":
          return "''";
        case '"':
          return '\\"';
        case '\\':
          return '\\\\';
        default:
          return '\\' + char;
      }
    });
  
    return sanitizedInput;
}

/**
 * Navigates to the previous page in the browser history.
 */
function previousPage() {
    event.preventDefault();
    window.history.back();
}

function togglePopup(id, display = 'block') {
    document.getElementById(id).style.display = display;
}

function togglePassword(input_id, icon_id) {
    const passwordInput = document.getElementById(input_id);
    const passwordIcon = document.getElementById(icon_id);
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.add('fa-eye-slash');
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('active');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.add('fa-eye');
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.remove('active');
    }
}

function previewImage(input) {
    var fileInput = input;
    var avatarPreview = document.getElementById('avatar_preview');

    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            avatarPreview.src = e.target.result;
        };

        reader.readAsDataURL(fileInput.files[0]);
    } else {
        // data-default_file
        default_file = avatarPreview.getAttribute('data-default_file');
        avatarPreview.src = default_file;
    }
}