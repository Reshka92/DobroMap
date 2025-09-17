document.addEventListener('DOMContentLoaded', () => {
    if (window.userStatus && window.userStatus.isLoggedIn === true) {
        // Скрываем гостевое меню, показываем меню пользователя
        document.getElementById('btnLogIn').style.display = 'none';
        document.getElementById('btnLogOut').style.display = 'block';
        
        // Обновляем информацию о пользователе
        const userAvatar = document.querySelector('.user-avatar');
        if (userAvatar && window.userStatus.userName) {
            // Генерируем инициалы из имени
            const initials = window.userStatus.userName.split(' ')
                .map(name => name[0])
                .join('')
                .toUpperCase();
            userAvatar.textContent = initials;
        }
    } else {
        // Гость  
        document.getElementById('userImg').style.display = 'none';
        document.getElementById('DelaImg').style.display = 'none';
        document.getElementById('btnLogOut').style.display = 'none';
    }
});