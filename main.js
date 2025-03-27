const loginText = document.querySelector(".title-text .login");
const loginForm = document.querySelector("form.login");
const loginBtn = document.querySelector("label.login");
const signupBtn = document.querySelector("label.signup");
const signupLink = document.querySelector("form .signup-link a");

signupBtn.onclick = (()=>{
  loginForm.style.marginLeft = "-50%";
  loginText.style.marginLeft = "-50%";
});

loginBtn.onclick = (()=>{
  loginForm.style.marginLeft = "0%";
  loginText.style.marginLeft = "0%";
});

signupLink.onclick = (()=>{
  signupBtn.click();
  return false;
});

// Add form submission handling
document.querySelector("form.login").addEventListener("submit", function(e) {
  e.preventDefault();
  window.location.href = "index.html";
});

document.querySelector("form.signup").addEventListener("submit", function(e) {
  e.preventDefault();
  window.location.href = "index.html";
});
