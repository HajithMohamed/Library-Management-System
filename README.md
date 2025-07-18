# 📚 Integrated Library System (ILS)

🚀 A full-stack web-based **Integrated Library System (ILS)** built entirely from scratch using HTML, CSS, JavaScript, PHP, and MySQL. This system offers role-based access for Admins, Students, and Faculty to simulate real-world library operations such as inventory control, book issuing, returning, fine calculation, and member management.

---

## 🌐 Live Preview

🔗 **Live App:** [https://debrup-ils.infinityfreeapp.com](https://debrup-ils.infinityfreeapp.com)  
📁 **GitHub Repository:** [Integrated-Library-System](https://github.com/Debrup-Chatterjee/Integrated-Library-System.git)

---

## 📸 Preview

<img src="https://github.com/user-attachments/assets/01c74982-c945-4f56-b8c4-cc1eb84fa667" width="100%" />

---

## 🧰 Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript, jQuery  
- **Backend:** Core PHP (no frameworks), AJAX  
- **Database:** MySQL  
- **Libraries & Tools:** Font Awesome, phpMyAdmin  
- **Deployment:** [InfinityFree](https://www.infinityfree.net/)

---

## 🛠️ How to Setup and Run

### 1. Install and Set Up XAMPP
- Download and install [XAMPP](https://www.apachefriends.org/) on your local machine.

- Start the **Apache** and **MySQL** modules from the XAMPP Control Panel and make sure they are running without errors.

### 2. Clone the Repository
- Open the `htdocs` folder inside your XAMPP installation directory.
- Right-click and select **Git Bash Here** (or open terminal/command prompt in this directory).
- Run the following command to clone the repository:

     ```bash
     git clone https://github.com/Debrup-Chatterjee/Integrated-Library-System.git
### 3. Configure the Project (Optional)
- This step is only required if:

     - Your MySQL server runs on a port other than 3306, or

     - You want to change existing project configurations.

- Open the following file in a code editor:
     `/xampp/htdocs/Integrated-Library-System/src/config/config.php`
- Modify the following constants only if necessary:

     - `DB_HOST`

     - `DB_PORT`

     - `DB_USER`

     - `DB_PASSWORD`

     - `DB_NAME`

     - ***Default time zone*** (set to India by default)

     - ***Admin registration Code*** ( 'hello_world' by default)

     ⚠️ **Warning: Do not change anything else unless required. Misconfiguration may break the project.** 

### 4. Launch the Application
- Open your browser and navigate to:
http://localhost/Integrated-Library-System/

 🎉 There you go — your library system is live locally!


---

## 🧩 Features

### 🔐 Role-Based Authentication

- Secure session-based login system
- Three access roles:
  - ✅ Admin
  - ✅ Student
  - ✅ Faculty

### 📚 Admin Functionalities

- Add, remove, and manage books
- View all registered users
- Live inventory tracking
- Monitor system activity from dashboard

### 🎓 Student / Faculty Functionalities

- Smart search for books
- Real-time borrow/return system
- Fine calculation and payment
- Profile overview with due books and fines

---

## ✅ What I Learned

- PHP session and access control
- Role-based user authentication and secure redirect handling
- Dynamic UI updates with AJAX (no reload)
- SQL database design and optimization
- Modular PHP structure for scalable maintenance
- Custom form validation and error handling
- Deployment using free hosting (InfinityFree)

---

## 📱 Responsive & Lightweight

- Fully responsive layout and seamless experience across mobiles, tablets, and desktops
- No heavy frontend frameworks – just clean, semantic HTML & CSS

---

## 💬 Feedback & Contributions

Have ideas, improvements, or suggestions?  
Feel free to open an [Issue](https://github.com/Debrup-Chatterjee/Integrated-Library-System/issues) or submit a [Pull Request](https://github.com/Debrup-Chatterjee/Integrated-Library-System/pulls).

---

## 🔗 Connect with Me

- 💼 [LinkedIn](https://www.linkedin.com/in/debrup-chatterjee/)
- 📧 [Email](mailto:debrupchatterjee31@gmail.com)

---

**<p align="center"> Built with ❤️ by Debrup Chatterjee </p>**
