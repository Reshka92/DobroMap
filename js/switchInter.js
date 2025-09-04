document.addEventListener('DOMContentLoaded', () => {
    if (window.userStatus && window.userStatus.isLoggedIn === true) {
        // Скрываем гостевое меню, показываем меню пользователя
        document.getElementById('btnLogIn').style.display = 'none';

        
    } else {
        // Гость    
        document.getElementById('userImg').style.display = 'none';
    }
});
