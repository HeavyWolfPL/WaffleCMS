const sidebar = document.querySelector('.sidebar');
const sidebarOpenTab = document.getElementById('sidebarOpenTab');
const openButton = document.getElementById('sidebarOpen');
const closeButton = document.getElementById('sidebarClose');
let isOpen = sidebar.dataset.open === 'true';

sidebarOpenTab.style = isOpen ? "display: none; width: 0;" : "";

closeButton.addEventListener('click', () => {
    if (isOpen) {
        closeSidebar(sidebar);
        sidebar.dataset.open = false;

        openSidebar(sidebarOpenTab);
        isOpen = false;
        return;
    } else {
        openSidebar(sidebar);
        sidebar.dataset.open = true;

        closeSidebar(sidebarOpenTab);
        isOpen = true;
        return;
    }
});

openButton.addEventListener('click', () => {
    if (isOpen) {
        closeSidebar(sidebar);
        sidebar.dataset.open = false;

        openSidebar(sidebarOpenTab);
        isOpen = false;
        return;
    } else {
        openSidebar(sidebar);
        sidebar.dataset.open = true;

        closeSidebar(sidebarOpenTab);
        isOpen = true;
        return;
    }
});

function openSidebar(element) {
    setTimeout(() => {
        element.style.display = "";
    }, 500);
    setTimeout(() => {
        element.style.width = "";
    }, 500);
}

function closeSidebar(element) {
    setTimeout(() => {
        element.style.width = "0";
    }, 200);
    setTimeout(() => {
        element.style.display = "none";
    }, 500);
}

