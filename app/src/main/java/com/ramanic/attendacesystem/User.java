package com.ramanic.attendacesystem;

public class User {

    private int id,admin;
    private String username, email;

    public User(int id, String username, String email, int admin) {
        this.id = id;
        this.username = username;
        this.email = email;
        this.admin = admin;
    }

    public int getId() {
        return id;
    }

    public String getUsername() {
        return username;
    }

    public String getEmail() {
        return email;
    }

    public int getAdmin() {
        return admin;
    }
}