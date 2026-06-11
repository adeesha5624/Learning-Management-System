# Member 2: Authentication System

## 📌 Module Overview
This module handles user login and authentication verification. It issues an authorization token required by all protected actions in the system.

## 🔗 Connections & Dependencies
- **Frontend Action**: Submitting the login form calls the `login()` method.
- **API Route**: Hits `POST http://localhost:8080/api/auth/login` (Mounted by Member 6).
- **Emits Token**: The received token is saved to the browser's `localStorage` and sent in the `Authorization` header for all requests made by **Member 3** (Add) and **Member 4** (Manage).

## 💻 Core Code Explanation

### 1. Backend Authentication Logic (`backend_auth.go`)
Validates user credentials and issues a simple token representation containing their role.
```go
func handleLogin(w http.ResponseWriter, r *http.Request) {
    // ... parse request body ...
    var user *User
    if loginReq.Username == "admin" && loginReq.Password == "admin123" {
        user = &User{ID: 1, Username: "admin", Role: "admin", Token: "admin:admin"}
    } else if loginReq.Username == "user" && loginReq.Password == "user123" {
        user = &User{ID: 2, Username: "user", Role: "user", Token: "user:user"}
    }
    // send back JSON object with the Token attached
    w.WriteHeader(http.StatusOK)
    json.NewEncoder(w).Encode(user)
}
```

### 2. Frontend Login Function (`frontend_auth.vue`)
This method makes a REST API request to the backend with the username and password. On success, it persists the user data.
```javascript
async login() {
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
        this.currentUser = user; // updates the app's global state
        localStorage.setItem("auth_token", user.token);
    }
}
```

## 🚀 Viva Presentation Notes
During the viva, explain how state management is handled. Show that when a user logs in, their credentials determine their `role` (Admin vs User), which conditionally changes the frontend UI rendering. Highlight how the generated token is used in subsequent headers.
