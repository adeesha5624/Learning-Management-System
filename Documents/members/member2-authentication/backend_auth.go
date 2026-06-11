package main

import (
	"encoding/json"
	"net/http"
	"strings"
)

type User struct {
	ID       int    `json:"id"`
	Username string `json:"username"`
	Role     string `json:"role"` // "admin" or "user"
	Token    string `json:"token,omitempty"`
}

type LoginRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}

// Get user info from token (simplified - in production use JWT)
func getUserFromToken(token string) *User {
	// Token format: "admin:admin" or "user:user" (base case for demo)
	parts := strings.Split(token, ":")
	if len(parts) != 2 {
		return nil
	}
	role, username := parts[0], parts[1]
	if (role == "admin" && username == "admin") || (role == "user" && username == "user") {
		return &User{ID: 1, Username: username, Role: role}
	}
	return nil
}

func handleLogin(w http.ResponseWriter, r *http.Request) {
	corsHeaders(w)
	w.Header().Set("Content-Type", "application/json")

	if r.Method == "OPTIONS" {
		w.WriteHeader(http.StatusOK)
		return
	}

	if r.Method != "POST" {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var loginReq LoginRequest
	err := json.NewDecoder(r.Body).Decode(&loginReq)
	if err != nil {
		http.Error(w, "Invalid request", http.StatusBadRequest)
		return
	}

	// Simple demo authentication
	var user *User
	if loginReq.Username == "admin" && loginReq.Password == "admin123" {
		user = &User{ID: 1, Username: "admin", Role: "admin", Token: "admin:admin"}
	} else if loginReq.Username == "user" && loginReq.Password == "user123" {
		user = &User{ID: 2, Username: "user", Role: "user", Token: "user:user"}
	} else {
		http.Error(w, "Invalid credentials", http.StatusUnauthorized)
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(user)
}
