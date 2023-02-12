function checkSubmit() {
    var username = document.forms["form"]["username"];
    var password = document.forms["form"]["password"];
    if (username.value && password.value) { 
        return true;
    }
    alert("Please enter username and password.");
    return false;
}