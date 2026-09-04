const loginForm = document.getElementById("loginForm");
const message = document.getElementById("message");

loginForm.addEventListener("submit", function(e){

    e.preventDefault();

    const username =
    document.getElementById("username").value.trim();

    const password =
    document.getElementById("password").value;

    const savedUser =
    JSON.parse(localStorage.getItem("user"));

    if(!savedUser){
        message.style.color = "red";
        message.textContent =
        "Nincs regisztrált felhasználó!";
        return;
    }

    if(
        username === savedUser.username &&
        password === savedUser.password
    ){

        message.style.color = "lime";
        message.textContent =
        "Sikeres bejelentkezés!";

        setTimeout(() => {
            window.location.href = "razor.html";
        }, 1000);

    }else{

        message.style.color = "red";
        message.textContent =
        "Hibás felhasználónév vagy jelszó!";

    }

});