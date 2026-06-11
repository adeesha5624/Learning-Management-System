<!-- HTML Template part for Auth -->
<div v-if="!isLoggedIn" class="login-container">
    <div class="login-panel glass-panel">
    <div class="login-header">
        <h2>CRM Complaint Management</h2>
        <p class="subtitle">Sign in to your account</p>
    </div>

    <form @submit.prevent="login" class="login-form">
        <div class="form-group">
        <label>Username</label>
        <div class="input-wrapper">
            <input v-model="loginForm.username" type="text" required placeholder="admin or user" />
        </div>
        </div>

        <div class="form-group">
        <label>Password</label>
        <div class="input-wrapper">
            <input v-model="loginForm.password" type="password" required placeholder="Enter password" />
        </div>
        </div>

        <button type="submit" class="submit-btn" :disabled="isLoggingIn">
        <span v-if="!isLoggingIn">Sign In</span>
        <span v-else class="loader"></span>
        </button>
    </form>
    </div>
</div>

<!-- Script part for Auth -->
<script>
export default {
  data() {
    return {
      isLoggedIn: false,
      isLoggingIn: false,
      currentUser: { username: "", role: "", token: "" },
      loginForm: { username: "", password: "" },
    };
  },
  methods: {
    async login() {
      if (this.isLoggingIn) return;
      this.isLoggingIn = true;
      try {
        const res = await fetch("http://localhost:8080/api/auth/login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            username: this.loginForm.username,
            password: this.loginForm.password,
          }),
        });
        if (res.ok) {
          const user = await res.json();
          this.currentUser = user;
          this.isLoggedIn = true;
          localStorage.setItem("auth_token", user.token);
          localStorage.setItem("auth_user", JSON.stringify(user));
          this.loginForm = { username: "", password: "" };
        } else {
          alert("Invalid credentials");
        }
      } catch (err) {
        console.error("Error logging in:", err);
      } finally {
        this.isLoggingIn = false;
      }
    },
    logout() {
      this.isLoggedIn = false;
      this.currentUser = { username: "", role: "", token: "" };
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");
    }
  }
}
</script>
