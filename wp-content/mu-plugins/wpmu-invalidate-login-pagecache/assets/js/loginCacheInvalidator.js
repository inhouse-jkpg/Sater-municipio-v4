class LoginCacheInvalidator {
  constructor() {
    this.loginForm = null;
    this.randomQueryParam = null;
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.loginForm = document.querySelector("form#loginform");
      this.randomQueryParam = "cacheBust=" + this.generateHash();

      this.updateActionAttribute();
    });
  }

  updateActionAttribute() {
    if (this.loginForm) {
      const action = this.loginForm.getAttribute("action");
      const updatedAction = action + (action.includes("?") ? "&" : "?") + this.randomQueryParam;
      this.loginForm.setAttribute("action", updatedAction);
    }
  }

  generateHash() {
    return Date.now().toString();
  }
}

const loginCacheInvalidator = new LoginCacheInvalidator();
loginCacheInvalidator.init();