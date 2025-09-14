document.addEventListener('DOMContentLoaded', () => {
    if (window.userStatus && window.userStatus.isLoggedIn === true) {
        // Скрываем гостевое меню, показываем меню пользователя
        document.getElementById('btnLogIn').style.display = 'none';
        document.getElementById('btnLogOut').style.display = 'block';
        
    } else {
        // Гость  
        document.getElementById('userImg').style.display = 'none';
        document.getElementById('DelaImg').style.display = 'none';
        document.getElementById('btnLogOut').style.display = 'none';
    }
});