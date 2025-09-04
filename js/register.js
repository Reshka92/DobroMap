var form = document.getElementById('registerForm');

form.addEventListener("submit", async function(event){
    event.preventDefault();

    var first_name = document.getElementById('name').value;
    var last_name = document.getElementById('lastname').value;
    var age = document.getElementById('age').value;
    var phone = document.getElementById('number').value;
    var password = document.getElementById('password').value;
    var email = document.getElementById('email').value;
    var repeatPassword = document.getElementById('repeatPassword').value;
    console.log(age);
    let adult;
    if (age >= 18) {
     adult = true;
} else {
     adult = false;
}
console.log(adult);


    

    if(password !== repeatPassword){
        alert("Пароли не совпадают");
        return;
    }
    if(password.length < 8){
        alert("Пароль должен содержать минимум 8 символов");
        return;
    }
    try{

        const response = await fetch('../processes/register.php',{
            method: "POST",
            headers:{"Content-type": "application/json"},
            body:JSON.stringify({first_name,last_name,adult,age,phone,email,password}),
        });

        if(!response.ok){
            const errorText = await response.text();
            throw new Error(`Ошибка сервера: ${response.status} - ${errorText}`);
        }
        const result = await response.json();

        if(result.success){
            window.location.href = result.redirect;
            localStorage.setItem('registered', true);
            console.log("Успех");
            
        }
        else{
            alert(result.message || 'Ошибка регистрации ');
            localStorage.setItem('registered', false);
        }

    }
    catch(error){
        console.log('Ошибка', error);
        
    }
    
    
    
    
    
})
