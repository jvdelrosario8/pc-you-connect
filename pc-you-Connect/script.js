const loginModal = document.getElementById('loginModal');
const studentModal = document.getElementById('studentModal');
const adminModal = document.getElementById('adminModal');
const errorPopup = document.getElementById('errorPopup');


/* =========================
   MODAL FUNCTIONS
   ========================= */

function openModal(modal) {

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');

    document.body.classList.add('modal-open');

}


function closeModal(modal) {

    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');

    if (
        ![loginModal, studentModal, adminModal]
            .some(m => m.classList.contains('active'))
    ) {
        document.body.classList.remove('modal-open');
    }

}


/* =========================
   LOGIN SELECTION
   ========================= */

document.getElementById('openLogin').addEventListener('click', () => {
    openModal(loginModal);
});


document.getElementById('studentChoice').addEventListener('click', () => {

    closeModal(loginModal);
    openModal(studentModal);

});


document.getElementById('adminChoice').addEventListener('click', () => {

    closeModal(loginModal);
    openModal(adminModal);

});


/* =========================
   CLOSE BUTTONS
   ========================= */

document.querySelectorAll('[data-close]').forEach(btn => {

    btn.addEventListener('click', () => {

        closeModal(btn.closest('.modal-backdrop'));

    });

});


/* =========================
   BACK BUTTONS
   ========================= */

document.querySelectorAll('[data-back]').forEach(btn => {

    btn.addEventListener('click', () => {

        closeModal(btn.closest('.modal-backdrop'));

        openModal(loginModal);

    });

});


/* =========================
   CLICK OUTSIDE MODAL
   ========================= */

document.querySelectorAll('.modal-backdrop').forEach(backdrop => {

    backdrop.addEventListener('click', e => {

        if (e.target === backdrop) {
            closeModal(backdrop);
        }

    });

});


/* =========================
   SHOW / HIDE PASSWORD
   ========================= */

document.querySelectorAll('.eye-btn').forEach(btn => {

    btn.addEventListener('click', () => {

        const input = document.getElementById(btn.dataset.toggle);

        input.type = input.type === 'password'
            ? 'text'
            : 'password';

    });

});


/* =========================
   ERROR POPUP
   ========================= */

function showErrorPopup(title, message) {

    document.getElementById('errorTitle').textContent = title;

    document.getElementById('errorMessage').textContent = message;

    errorPopup.classList.add('show');

}


function closeErrorPopup() {

    errorPopup.classList.remove('show');

}


/* =========================
   CHECK LOGIN ERROR
   ========================= */

const params = new URLSearchParams(window.location.search);
const error = params.get('error');

if (error) {

    let title = 'Login Error';
    let message = 'Something went wrong. Please try again.';

    if (error === 'student_password') {

        title = 'Incorrect Password';
        message = 'The password you entered is incorrect.';

        openModal(studentModal);

    }

    else if (error === 'student_not_found') {

        title = 'Student Not Found';
        message = 'No student account was found with that Student Number.';

        openModal(studentModal);

    }

    else if (error === 'admin_password') {

        title = 'Incorrect Password';
        message = 'The password you entered is incorrect.';

        openModal(adminModal);

    }

    else if (error === 'admin_not_found') {

        title = 'Admin Not Found';
        message = 'No admin account was found with that User ID.';

        openModal(adminModal);

    }

    else if (error === 'invalid_login') {

        title = 'Invalid Login';
        message = 'Please try logging in again.';

    }

    showErrorPopup(title, message);

    /*
     * Remove ?error=... from URL
     * without refreshing the page.
     */
    window.history.replaceState(
        {},
        document.title,
        window.location.pathname
    );

}


/* =========================
   ESCAPE KEY
   ========================= */

document.addEventListener('keydown', e => {

    if (e.key === 'Escape') {

        document
            .querySelectorAll('.modal-backdrop.active')
            .forEach(closeModal);

        closeErrorPopup();

    }

});


/* =========================
   CLICK OUTSIDE ERROR POPUP
   ========================= */

errorPopup.addEventListener('click', e => {

    if (e.target === errorPopup) {
        closeErrorPopup();
    }

});