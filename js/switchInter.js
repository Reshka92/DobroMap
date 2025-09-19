// switchInter.js
document.addEventListener('DOMContentLoaded', () => {
    const btnLogIn = document.getElementById('btnLogIn');
    const btnLogOut = document.getElementById('btnLogOut'); // Этот элемент отсутствует на index.php
    const userImg = document.getElementById('userImg');
    const DelaImg = document.getElementById('DelaImg');
    const userAvatar = document.querySelector('.user-avatar'); // Этот элемент также отсутствует

    if (!window.userStatus) return;

    if (window.userStatus.isLoggedIn === true) {
        if (btnLogIn) btnLogIn.style.display = 'none';
        if (btnLogOut) btnLogOut.style.display = 'block'; // Ошибка здесь - btnLogOut = null
        
        if (userAvatar && window.userStatus.userName) {
            const initials = window.userStatus.userName.split(' ')
                .map(name => name[0])
                .join('')
                .toUpperCase();
            userAvatar.textContent = initials;
        }
    } else {
        if (userImg) userImg.style.display = 'none';
        if (DelaImg) DelaImg.style.display = 'none';
        if (btnLogOut) btnLogOut.style.display = 'none'; // И здесь
    }
});