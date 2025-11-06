<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Instructor Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="instructor">
  <div class="box">
    <h2>Welcome, Instructor!</h2>
    <p>You have successfully logged in.</p>
    <button id="logoutBtn">Logout</button>
  </div>

  <script type="module">
    import { auth } from "./firebase.php";
    import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.14.0/firebase-auth.js";

    onAuthStateChanged(auth, (user) => {
      if (!user) {
        window.location.href = "login.php";
      }
    });

    document.getElementById("logoutBtn").addEventListener("click", () => {
      signOut(auth).then(() => {
        window.location.href = "login.php";
      });
    });
  </script>
</body>
</html>

