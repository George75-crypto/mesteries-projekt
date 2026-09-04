const registerForm = document.getElementById("registerForm");
const message = document.getElementById("registerMessage");

registerForm.addEventListener("submit", function(e) {
    e.preventDefault();

    const username = document.getElementById("regUsername").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("regPassword").value;

    if(username.length < 4){
        message.style.color = "red";
        message.textContent = "A felhasználónév legalább 4 karakter legyen!";
        return;
    }

    if(password.length < 6){
        message.style.color = "red";
        message.textContent = "A jelszó legalább 6 karakter legyen!";
        return;
    }

    const user = {
        username,
        email,
        password
    };

    localStorage.setItem("user", JSON.stringify(user));

    message.style.color = "lime";
    message.textContent = "Sikeres regisztráció!";

    setTimeout(() => {
        window.location.href = "index.html";
    }, 1500);
});