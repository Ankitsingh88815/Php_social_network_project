# PHP Social Network Project

This is a **PHP-based social network project** where users can **sign up**, **login**, and **manage their profiles**, along with posting content and reacting to posts.

---

## 📋 Pages Overview

### 1️⃣ Signup Page
![Signup Page](photo/Screenshot%202025-10-05%20134236.png)

---

### 2️⃣ Login Page
![Login Page](photo/Screenshot%202025-10-05%20134210.png)

---

### 3️⃣ Profile Page
![Profile Page](photo/Screenshot%202025-10-05%20134026.png)

---

## 🚀 Features

- **User Registration (Signup)**
- **User Authentication (Login)**
- **Profile Management**
- **Image Upload**
- **Post Creation**
- **Reaction to Posts (like/dislike)**
- **Session Management**: Users stay logged in securely until they logout.
- **AJAX Integration**: For smooth interactions without page reloads.

---

## 💻 Technologies Used

- **PHP**: Server-side scripting  
- **MySQL**: Database management  
- **jQuery**: A lightweight JavaScript library that simplifies **DOM manipulation, event handling, animations, and AJAX operations**.  
- **HTML / CSS**: Frontend design  

---

## 🗄 Database Structure

The project uses **three main tables**:

1. **`user`**  
   - Stores user information such as `id`, `name`, `email`, `password`, and `profile_picture`.

2. **`post`**  
   - Stores posts created by users with fields like `id`, `user_id`, `content`, `image`, and `timestamp`.

3. **`reaction`**  
   - Stores reactions on posts such as `id`, `post_id`, `user_id`, and `reaction_type` (like/dislike).

---

## 🔧 Installation & Setup

1. Clone the repository:

```bash
git clone https://github.com/Ankitsingh88815/Php_social_network_project.git










